<?php
	//Change directory to allow use of rest of MRS
	chdir("..");
?>
<?php
	//Set the system error handler
	if(file_exists("backend/errorhandler.php"))
	{
		include("backend/errorhandler.php");
	}
	else
	{
		trigger_error("Failed to invoke system error handler. Expect information leakage.",E_USER_WARNING);
	}
	//Include useful functions page, if it exists
	if(file_exists("backend/functions.php"))
	{
		include("backend/functions.php");
	}
	//Set error levels
	switch(get_system_setting("errlvl"))
	{
		case 0:
		ini_set("error_reporting",E_ALL & ~E_NOTICE & ~E_USER_NOTICE);
		break;
		case 2:
		ini_set("error_reporting",E_ALL);
		break;
		case 1:
		default:
		ini_set("error_reporting",E_ALL & ~E_NOTICE);
		break;
	}
?>
<?php
	//Get system name
	$sysname=system_name();
?>
<?php
	//Open session
	$altsesstore=alt_ses_store();
	if($altsesstore !== false)
	{
		session_save_path($altsesstore);
	}
	session_start();
?>
<?php
	//Administrative check function (on a separate page)
	if(file_exists("backend/securitycheck.php"))
	{
		include ("backend/securitycheck.php");
	}
	else
	{
		die("Failed to open file \"backend/securitycheck.php\" in read mode. It should now be microwaved.");
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <meta name="generator" content="CoffeeCup HTML Editor (www.coffeecup.com)">
    <meta name="created" content="Wed, 17 Jun 2015 12:33:52 GMT">
    <meta name="description" content="Listening to a live stream? Got a song you have to hear? This is the place to request it!">
	<link rel="shortcut icon" href="../backend/favicon.ico">
    <title><?php echo $sysname; ?>Music Request System-Checking For New Updates</title>
    
    <style type="text/css">
    <!--
    body {
      color:#000000;
	  background-color:#FFFFFF;
      background-image:url('../backend/background.gif');
      background-repeat:repeat;
    }
    a  { color:#FFFFFF; background-color:#0000FF; }
    a:visited { color:#FFFFFF; background-color:#800080; }
    a:hover { color:#000000; background-color:#00FF00; }
    a:active { color:#000000; background-color:#FF0000; }
    -->
    </style>
  </head>
  <?php
	//Log page visit, if logging enabled
	date_default_timezone_set(get_system_setting("timezone"));
	write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Started checking for updates");
	//Run security check
	if(securitycheck() === false)
	{
		//No admin privileges, no page viewing privileges
		die("You are not an administrator. <a href=\"../login.php?ref=admin\">Sign in</a> or <a href=\"../index.php\">Cancel</a>.");
	}
	//Get current build code
	$buildcode=0;
	if(file_exists("backend/version.txt"))
	{
		$verinfo=explode("\r\n",file_get_contents("backend/version.txt"));
		if(!empty($verinfo[1]))
		{
			$buildcode=$verinfo[1];
		}
	}
	write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Got system build code");
	//Initialize lists of packages
	$packages=array();
	$oldpackages=array();
	$newpackages=array();
	//Get old data
	if(file_exists("upgrade/packages.txt"))
	{
		$olddata=array_filter(explode("\r\n",file_get_contents("upgrade/packages.txt")));
		foreach($olddata as $data)
		{
			$data=explode("|",$data,2);
			if(count($data) == 2)
			{
				$oldpackages[$data[0]]=$data[1];
			}
		}
	}
	write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Got current upgrade pack list");
	//Remove everything marked as installed
	$op=$oldpackages;
	foreach($op as $code=>$info)
	{
		$info=explode("|",$info);
		if(isset($info[1]) && $info[1] >= 3)
		{
			unset($oldpackages[$code]);
		}
	}
	unset($op);
	//Initialize curl
	$curl=curl_init();
	$check=-1;
	if($curl !== false)
	{
		write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Initialized CURL to get latest upgrade pack list");
		//Set curl options
		curl_setopt($curl, CURLOPT_URL, get_system_setting("mirror") . "mrs24-upgrade/packages.txt");
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
		write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Set curl options");
		//Execute curl
		$newdata = curl_exec($curl);
		
		//Check and form the data
		if($newdata != "" && !curl_errno($curl) && curl_getinfo($curl,CURLINFO_HTTP_CODE) == 200)
		{
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Successfully executed curl");
			$newdata=array_filter(explode("\r\n",$newdata));
			foreach($newdata as $data)
			{
				$data=explode("|",$data,2);
				if(count($data) == 2)
				{
					$newpackages[$data[0]]=$data[1];
				}
			}
		}
		else
		{
			//Curl failed
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to execute curl, error: " . curl_errno($curl));
			echo ("Failed to open remote file \"packages.txt\" in read mode. The remote server should be submerged in pool water.<br>\r\n");
		}
		//Close session
		curl_close($curl);
		write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Closed curl session");
		write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Got latest upgrade pack list");
		
		//Loop through current list and re-add all items from before that are still applicable
		foreach(array_keys($oldpackages) as $package)
		{
			//If in latest list, add back
			if(in_array($package,array_keys($newpackages)))
			{
				$packages[$package]=$oldpackages[$package];
			}
		}
		//Loop through new list and add new items
		foreach(array_keys($newpackages) as $package)
		{
			//If package code is greater than current build code and it's not on the list, add it
			if($package > $buildcode && !in_array($package,array_keys($packages)))
			{
				$packages[$package]=$newpackages[$package] . "|0";
			}
		}
		write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Calculated new list of updates");
		
		//Reformat as string and write to list file
		$string="";
		foreach($packages as $code=>$info)
		{
			$string.="$code|$info\r\n";
		}
		$fh=fopen("upgrade/packages.txt",'w');
		if($fh)
		{
			fwrite($fh,$string);
			fclose($fh);
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Finished checking for upgrades");
			$fh=fopen("upgrade/lastcheck.txt",'w');
			if($fh)
			{
				fwrite($fh,time());
				fclose($fh);
			}
			echo("<script type=\"text/javascript\">window.location = \"index.php?check=y\"</script>");
		}
		else
		{
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to update list of upgrades");
			echo("<script type=\"text/javascript\">window.location = \"index.php?check=n\"</script>");
		}
	}
	//Change back to upgrader directory to avoid breaking everything else
	chdir("upgrade");
  ?>
  <body>
  <h1 style="text-align:center; text-decoration:underline;"><?php echo $sysname; ?>Music Request System-Checking For Updates</h1>
  <p>You should not see this page. If you do, it's likely something has been reduced to custard.</p>
  <p><a href="index.php">Go back</a></p>
  </body>
</html>