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
    <title><?php echo $sysname; ?>Music Request System-Downloading Update</title>
    
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
	//Get build code
	$buildcode="";
	if(!empty($_GET['pack']))
	{
		$buildcode=preg_replace("/[^0-9]/","",$_GET['pack']);
	}
	//Log page visit, if logging enabled
	date_default_timezone_set(get_system_setting("timezone"));
	write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Started downloading upgrade pack \"$buildcode\"");
	//Run security check
	if(securitycheck() === false)
	{
		//No admin privileges, no page viewing privileges
		die("You are not an administrator. <a href=\"../login.php?ref=admin\">Sign in</a> or <a href=\"../index.php\">Cancel</a>.");
	}
	//If build code is invalid, end process and get out of here
	if(empty($buildcode))
	{
		die("<script type=\"text/javascript\">window.location = \"index.php\"</script>");
	}
	$terminate=false;
	//Download upgrade pack
	$dfh=fopen("upgrade/$buildcode.zip",'w+');
	if($dfh)
	{
		write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Opened new ZIP archive");
		//Initialize curl
		$curl=curl_init();
		if($curl !== false)
		{
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Initialized CURL");
			//Set curl options
			curl_setopt($curl, CURLOPT_URL, get_system_setting("mirror") . "mrs24-upgrade/$buildcode.zip");
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_BINARYTRANSFER, true);
			curl_setopt($curl, CURLOPT_FILE,$dfh);
			curl_setopt($curl, CURLOPT_HEADER, false);
			curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Set curl options");
			//Execute curl
			curl_exec($curl);
			
			//Check and form the data
			if(!curl_errno($curl) && curl_getinfo($curl,CURLINFO_HTTP_CODE) == 200)
			{
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Successfully executed curl");
			}
			else
			{
				//Curl failed
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to execute curl");
				trigger_error("CURL failed with error code " . curl_errno($curl) . ", HTTP response code " . curl_getinfo($curl,CURLINFO_HTTP_CODE) . ".",E_USER_ERROR);
				fclose($dfh);
				unlink("upgrade/$buildcode.zip");
				$terminate=true;
			}
			//Close session
			curl_close($curl);
			fclose($dfh);
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Finished download");
		}
	}
	else
	{
		//Cannot open the file for writing.
		trigger_error("Cannot open file \"$buildcode.zip\" for writing.",E_USER_ERROR);
		write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to download package: save file cannot be written");
		$terminate=true;
	}
	//Verify SHA1 sum of downloaded upgrade pack
	if($terminate === false)
	{
		$sha1_expect="";
		$sha1_file="";
		//Find expected hash from list of updates
		if(file_exists("upgrade/packages.txt"))
		{
			$upgrades=array_filter(explode("\r\n",file_get_contents("upgrade/packages.txt")));
			foreach($upgrades as $upgrade)
			{
				$upgrade=explode("|",$upgrade);
				if(count($upgrade) == 3 && $upgrade[0] == $buildcode)
				{
					$sha1_expect=$upgrade[1];
					break;
				}
			}
		}
		//Get file hash
		if(file_exists("upgrade/$buildcode.zip"))
		{
			$sha1_file=sha1_file("upgrade/$buildcode.zip");
			if($sha1_file == $sha1_expect || $sha1_expect == "DONT_CHECK")
			{
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Successfully verified SHA1 hash of downloaded file");
			}
			else
			{
				trigger_error("SHA1 hash mismatch: got \"$sha1_file\", expected \"$sha1_expect\".",E_USER_ERROR);
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"SHA1 hash of file does not match");
				$terminate=true;
			}
		}
		else
		{
			trigger_error("Cannot compute SHA1 hash: file \"$buildcode.zip\" does not exist.",E_USER_ERROR);
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Cannot calculate SHA1 hash, file missing");
			$terminate=true;
		}
	}
	//Extract upgrade pack
	if($terminate === false)
	{
		//If directory does not already exist, make it
		if(!is_dir("upgrade/$buildcode"))
		{
			$debug=mkdir("upgrade/$buildcode");
			if($debug === true)
			{
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Created directory for update $buildcode");
			}
		}
		else
		{
			$debug=true;
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Directory $buildcode already exists, using that instead of recreating");
		}
		if($debug === true)
		{
			$arch=new ZipArchive;
			if($arch->open("upgrade/$buildcode.zip"))
			{
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Opened upgrade pack $buildcode");
				$debug=$arch->extractTo("upgrade/$buildcode");
				if($debug === true)
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Extracted upgrade pack $buildcode");
				}
				else
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to extract upgrade pack $buildcode");
					trigger_error("Failed to extract upgrade pack. Please re-download the MRS upgrade package, or contact the software vendor.",E_USER_ERROR);
					$terminate=true;
				}
				$arch->close();
			}
			else
			{
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to open upgrade pack");
				trigger_error("Failed to open upgrade pack. Please re-download the MRS upgrade package, or contact the software vendor.",E_USER_ERROR);
				$terminate=true;
			}
		}
		else
		{
			trigger_error("Failed to create directory \"upgrade/$buildcode\".",E_USER_ERROR);
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to create directory for update $buildcode");
			$terminate=true;
		}
	}
	//Update state of upgrade pack
	if($terminate === false)
	{
		if(file_exists("upgrade/packages.txt"))
		{
			$upgrades=array_filter(explode("\r\n",file_get_contents("upgrade/packages.txt")));
			for($i=0;$i<count($upgrades);$i++)
			{
				$upgrades[$i]=explode("|",$upgrades[$i]);
				if(count($upgrades[$i]) == 3 && $upgrades[$i][0] == $buildcode)
				{
					$upgrades[$i][2]=1;
				}
				$upgrades[$i]=implode("|",$upgrades[$i]);
			}
			$upgrades=implode("\r\n",$upgrades);
			$fh=fopen("upgrade/packages.txt",'w');
			if($fh)
			{
				fwrite($fh,$upgrades);
				fclose($fh);
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Updated list of available upgrades");
			}
			else
			{
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to update list of available upgrades");
				trigger_error("Failed to update list of available updates; file cannot be opened.",E_USER_ERROR);
				$terminate=true;
			}
		}
		else
		{
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to update list of available upgrades");
			trigger_error("Failed to update list of available updates; file cannot be found.",E_USER_ERROR);
			$terminate=true;
		}
	}
	//Redirect back out based on end result
	if($terminate === false)
	{
		write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Finished downloading update $buildcode");
		echo("<script type=\"text/javascript\">window.location = \"index.php?download=y\"</script>");
	}
	else
	{
		write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to download update $buildcode");
		echo("<script type=\"text/javascript\">window.location = \"index.php?download=n\"</script>");
	}
	//Change back to upgrader directory to avoid breaking everything else
	chdir("upgrade");
  ?>
  <body>
  <h1 style="text-align:center; text-decoration:underline;"><?php echo $sysname; ?>Music Request System-Downloading Update <?php echo $buildcode; ?></h1>
  <p>You should not see this page. If you do, it's likely something has been reduced to custard.</p>
  <p><a href="index.php">Go back</a></p>
  </body>
</html>