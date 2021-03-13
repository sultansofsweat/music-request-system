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
    <title><?php echo $sysname; ?>Music Request System-Check For Updates</title>
    
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
	write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Viewed upgrade page");
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
	//Get upgrade list
	$upgrades=array();
	if(file_exists("upgrade/packages.txt"))
	{
		$upgrades=array_filter(explode("\r\n",file_get_contents("upgrade/packages.txt")));
	}
	//Get rest of information
	$lastcheck=0;
	$lastinst=0;
	if(file_exists("upgrade/lastcheck.txt"))
	{
		$lastcheck=preg_replace("/[^0-9]/","",file_get_contents("upgrade/lastcheck.txt"));
	}
	if(file_exists("upgrade/lastinst.txt"))
	{
		$lastinst=preg_replace("/[^0-9]/","",file_get_contents("upgrade/lastinst.txt"));
	}
	//Change back to upgrader directory to avoid breaking everything else
	chdir("upgrade");
  ?>
  <?php
	$firstuse=false;
	if(file_exists("firstuse.txt"))
	{
		$firstuse=true;
	}
	if(isset($_POST['check']) && $_POST['check'] == "y")
	{
		trigger_error("Finished checking for updates.");
	}
	elseif(isset($_POST['check']) && $_POST['check'] == "n")
	{
		trigger_error("Failed to check for updates. Check your error log and convert the culprit to custard.",E_USER_WARNING);
	}
	if(isset($_POST['download']) && $_POST['download'] == "y")
	{
		trigger_error("Finished downloading updates.");
	}
	elseif(isset($_POST['download']) && $_POST['download'] == "n")
	{
		trigger_error("Failed to download updates. Check your error log and convert the culprit to custard.",E_USER_WARNING);
	}
	if(isset($_POST['prepare']) && $_POST['prepare'] == "y")
	{
		trigger_error("Finished preparing all downloaded updates for installation.");
	}
	elseif(isset($_POST['prepare']) && $_POST['prepare'] == "n")
	{
		trigger_error("Failed to preparing updates. Check your error log and convert the culprit to custard.",E_USER_WARNING);
	}
  ?>
  <?php
	/*$current=new Version;
	$new=new Version;
	if(is_logging_enabled() === true)
	{
		
		//Check for a version file
		if(file_exists("backend/version.txt"))
		{
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Obtained version information");
			//Read file
			$data=explode("\r\n",file_get_contents("backend/version.txt"));
			//Assign known parameters to the version object
			$current->identifier=$data[1];
			$current->built=$data[2];
			//Obtain and set further version information
			$data[0]=explode("|",$data[0]);
			$current->major=$data[0][0];
			$current->minor=$data[0][1];
			$current->revision=$data[0][2];
			switch($data[0][3])
			{
				case "1":
				$current->beta=true;
				break;
				default:
				$current->beta=false;
				break;
			}
		}
		else
		{
			//Cannot read file
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Could not obtain version information");
			echo ("Failed to open file \"backend/version.txt\" in read mode. It should now be microwaved.<br>\r\n");
		}
		//Initialize curl
		$curl=curl_init();
		$check=-1;
		if($curl !== false)
		{
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Initialized CURL to get latest version information");
			//Set curl options
			if(get_system_setting("stable") == "yes")
			{
				curl_setopt($curl, CURLOPT_URL, get_system_setting("mirror") . "mrs2-upgrade/latest-stable.txt");
			}
			else
			{
				curl_setopt($curl, CURLOPT_URL, get_system_setting("mirror") . "mrs2-upgrade/latest.txt");
			}
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_HEADER, false);
			curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Set curl options");
			//Execute curl
			$data = curl_exec($curl);
			
			//Check and form the data
			if($data != "" && !curl_errno($curl) && curl_getinfo($curl,CURLINFO_HTTP_CODE) == 200)
			{
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Successfully executed curl");
				$data=explode("|",$data);
				$new->major=$data[0];
				$new->minor=$data[1];
				$new->revision=$data[2];
				switch($data[3])
				{
					case "1":
					$new->beta=true;
					break;
					default:
					$new->beta=false;
					break;
				}
			}
			else
			{
				//Curl failed
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to execute curl, error: " . curl_errno($curl));
				echo ("Failed to open remote file \"latest.txt\" in read mode. The remote server should be submerged in pool water.<br>\r\n");
			}
			//Close session
			curl_close($curl);
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Closed curl session");
		}
	}
	else
	{
		//Change the timezone
		if(file_exists("backend/timezone.txt"))
		{
			date_default_timezone_set(file_get_contents("backend/timezone.txt"));
		}
		else
		{
			date_default_timezone_set("America/Toronto");
		}
		//Logging disabled
		//Check for administrative permissions
		if(securitycheck() === false)
		{
			//No admin privileges, no page viewing privileges
			die("You are not an administrator. <a href=\"../login.php\">Sign in</a> or <a href=\"../index.php\">Cancel</a>.");
		}
		//Check for a version file
		if(file_exists("backend/version.txt"))
		{
			//Read file
			$data=explode("\r\n",file_get_contents("backend/version.txt"));
			//Assign known parameters to the version object
			$current->identifier=$data[1];
			$current->built=$data[2];
			//Obtain and set further version information
			$data[0]=explode("|",$data[0]);
			$current->major=$data[0][0];
			$current->minor=$data[0][1];
			$current->revision=$data[0][2];
			switch($data[0][3])
			{
				case "1":
				$current->beta=true;
				break;
				default:
				$current->beta=false;
				break;
			}
		}
		else
		{
			//Cannot read file
			echo ("Failed to open file \"backend/version.txt\" in read mode. It should now be microwaved.<br>\r\n");
		}
		//Initialize curl
		$curl=curl_init();
		$check=-1;
		if($curl !== false)
		{
			//Set curl options
			if(get_system_setting("stable") == "yes")
			{
				curl_setopt($curl, CURLOPT_URL, get_system_setting("mirror") . "mrs2-upgrade/latest-stable.txt");
			}
			else
			{
				curl_setopt($curl, CURLOPT_URL, get_system_setting("mirror") . "mrs2-upgrade/latest.txt");
			}
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_HEADER, false);
			curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
			//Execute curl
			$data = curl_exec($curl);
			
			//Check and form the data
			if($data != "" && !curl_errno($curl) && curl_getinfo($curl,CURLINFO_HTTP_CODE) == 200)
			{
				$data=explode("|",$data);
				$new->major=$data[0];
				$new->minor=$data[1];
				$new->revision=$data[2];
				switch($data[3])
				{
					case "1":
					$new->beta=true;
					break;
					default:
					$new->beta=false;
					break;
				}
			}
			else
			{
				//Curl failed
				echo ("Failed to open remote file \"latest.txt\" in read mode. The remote server should be submerged in pool water.<br>\r\n");
			}
			//Close session
			curl_close($curl);
		}
	}*/
  ?>
  <?php
	//Version comparison
	/*if($current->major > $new->major || ($current->major == $new->major && $current->minor > $new->minor) || ($current->major == $new->major && $current->minor == $new->minor && $current->revision > $new->revision))
	{
		$check=4;
	}
	elseif($current->beta === true)
	{
		if($new->major > $current->major || ($current->major == $new->major && $current->minor < $new->minor))
		{
			$check=2;
		}
		elseif($current->major == $new->major && $current->minor == $new->minor && $current->revision < $new->revision)
		{
			$check=2;
		}
		elseif($new->beta === false)
		{
			$check=5;
		}
		else
		{
			$check=3;
		}
	}
	else
	{
		if($new->beta === true)
		{
			$check=2;
		}
		elseif($new->major > $current->major || ($current->major == $new->major && $current->minor < $new->minor))
		{
			$check=0;
		}
		elseif($current->major == $new->major && $current->minor == $new->minor && $current->revision < $new->revision)
		{
			$check=1;
		}
		else
		{
			$check=3;
		}
	}*/
  ?>
  <body>
  <h1 style="text-align:center; text-decoration:underline;"><?php echo $sysname; ?>Music Request System-Upgrade System</h1>
  <p>Although it is possible to manually add and change components of the MRS software, it is recommended to use the system upgrader to minimize the chance of human error rendering the MRS unusable, and to automatically get notifications when new updates are ready to be installed.</p>
  <?php
	if($firstuse === true)
	{
		echo("<p><b>You must read the MRS upgrader usage information to continue. <a href=\"usage.php\">Click here</a> to do so.</b></p>\r\n");
	}
	else
	{
		echo("<p><a href=\"usage.php\">Click here</a> to check the usage information for these upgrade scripts.</p>\r\n");
	}
  ?>
  <div <?php if($firstuse === true) { echo "style=\"display:none\""; } ?>>
  <hr>
  <p>Last check for updates took place <b><?php echo date("F j Y \a\\t g:i A",$lastcheck); ?>.</b><br>
  Updates were last installed <b><?php echo date("F j Y \a\\t g:i A",$lastinst); ?></b><br>
  There are presently <b><?php echo count($upgrades); ?></b> updates available.<br>
  For reference, you are running MRS build code <b><?php echo $buildcode; ?></b>.</p>
  <?php
	if(count($upgrades) > 0)
	{
		echo("<p>The following updates are available:<br>\r\n");
		foreach($upgrades as $upgrade)
		{
			$upgrade=explode("|",$upgrade);
			if(count($upgrade) == 3)
			{
				echo("<b>Build code " . $upgrade[0] . "</b><br>\r\n");
				switch($upgrade[2])
				{
					case 0:
					echo("Status: not downloaded<br>\r\n<a href=\"download.php?pack=" . $upgrade[0] . "\">Download</a><br>\r\n");
					break;
					
					case 1:
					echo("Status: downloaded<br>\r\n<a href=\"" . $upgrade[0] . "/changelog.txt\" target=\"_blank\">Changelog</a><a href=\"download.php?pack=" . $upgrade[0] . "\">Redownload</a><br>\r\n");
					break;
					
					case 2:
					echo("Status: prepared<br>\r\n<a href=\"" . $upgrade[0] . "/changelog.txt\" target=\"_blank\">Changelog</a><a href=\"download.php?pack=" . $upgrade[0] . "\">Redownload</a><br>\r\n");
					break;
					
					default:
					echo("Status: indeterminate<br>\r\n<a href=\"download.php?pack=" . $upgrade[0] . "\">Download</a><br>\r\n");
					break;
				}
			}
			echo("<br>\r\n");
		}
		echo("</p>\r\n");
	}
  ?>
  </div>
  <p><a href="check.php">Check for updates</a> | <a href="sideload.php">Sideload upgrade pack</a> | <a href="prepare.php">Prepare final upgrade</a> | <a href="install.php">Install prepared upgrades</a> | <a href="../admin.php">Go back</a></p>
  </body>
</html>