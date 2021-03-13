<?php
	//Change working directory to enable use of centralized functions
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
	//Version identifier class (to make things easy)
	class Version
	{
		public $major=0;
		public $minor=0;
		public $revision=0;
		public $beta=false;
		public $identifier="";
		public $built="January 1, 1970 at 12:00 AM Eastern Time";
	}
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
    <title> Music Request System-Check For Updates</title>
    
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
	$current=new Version;
	$new=new Version;
	if(is_logging_enabled() === true)
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
		//Logging enabled
		write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Viewed upgrade page");
		//Check for administrative permissions
		if(securitycheck() === false)
		{
			//No admin privileges, no page viewing privileges
			die("You are not an administrator. <a href=\"../login.php\">Sign in</a> or <a href=\"../index.php\">Cancel</a>.");
		}
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
	}
  ?>
  <?php
	//Version comparison
	if($current->major > $new->major || ($current->major == $new->major && $current->minor > $new->minor) || ($current->major == $new->major && $current->minor == $new->minor && $current->revision > $new->revision))
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
	}
  ?>
  <body>
  <h1 style="text-align:center; text-decoration:underline;"><?php echo $sysname; ?>Music Request System-Upgrade System</h1>
  <p><a href="#skip">Skip all the mumbo-jumbo and upgrade</a></p>
  <h2>Before Upgrading</h2>
  <p>MRS releases can be downloaded <a href="http://firealarms.mooo.com/mrs/#downloading">here</a> or via a <a href="http://firealarms.mooo.com/mrs/#mirrors">trusted mirror</a>. You may be required to have both the package and the associated MD5 checksum.<br>
  <b>Make sure you are downloading an <u>upgrade</u> pack!</b> Install packs are <b><u>not</u></b> supported by the updater!</p>
  <p>If you are running the latest release, you must download the files and place them in the appropriate location before an update can proceed. Otherwise, proceeding without downloading is fine; the system will download the appropriate files automatically.</p>
  <p><b>Take a backup.</b> No one wants data loss. While the upgrade scripts take backups of their own, they should not be trusted. And besides, a good backup is something you should have anyways in the event of other catastrophic failures.</p>
  <hr>
  <h2>Preparing for Upgrade</h2>
  <p>If you downloaded an upgrade package and/or MD5 hash file, place these in the "upgrade" subdirectory before proceeding. No extraction or folder creation is necessary.</p>
  <p>If you are at all unsure whether or not an upgrade will work, it is possible to do a "dry" run, where the system runs an upgrade procedure without touching any files.<br>
  There are two options: partial and full. A partial upgrade will retain all settings, while a full upgrade will remove them. A full upgrade also replaces the system background and favicon with the ones bundled with the release.<br>
  Note that neither upgrade option will erase requests from the system, or erase the song list if one is present. Neither option will reset the system password either.<br>
  Also note that while the partial upgrade doesn't erase settings that still apply to the system, it will remove obsolete settings. This is another reason why a backup is desirable.</p>
  <hr>
  <h2>During the Upgrade</h2>
  <p>...sit back and relax! There is nothing one needs to do, unless there are errors.</p>
  <h2>After the Upgrade</h2>
  <p>Check for any errors. If there are errors, <u>don't panic</u>. <a href="http://firealarms.mooo.com/mrs/#report">Report</a> errors to the software vendor.<br>
  The system should have taken a backup during the upgrade process, unless the backup did not succeed. You also took your own backup before proceeding with an upgrade...right?<br>
  Inside the upgrade directory are now two folders: "configback" and "sysback". All the system files are in sysback, while the configuration and background/favicon are in configback.<br>
  Move all files in configback to the "backend" folder, overwriting whatever is in there. Move all the single part filenames (those without dashes, for example "index.php") to the root of the MRS, again overwriting everything.<br>
  Other files in sysback (for example "api-autosys.php") will need to be renamed and moved accordingly. The first part of the filename corresponds to the folder it belongs in.</p>
  <p>If there are any questions about the recovery process, <a href="http://firealarms.mooo.com/mrs/#contact">contact the software vendor</a></p>
  <p>Otherwise, there is nothing to do. You may delete the sysback and configback folders if you wish.</p>
  <hr>
  <h2>Manual Upgrades</h2>
  <p>Manual upgrades are more difficult since there is potentially some automated processing that needs to take place before the system will work as intended.<br>
  These instructions assume you know what you are doing. If you don't, or are unsure, <b>don't bother</b> and <a href="#skip">skip them</a>.</p>
  <p>The first step is to extract the package you downloaded.<br>
  If you wish, take the background and favicon and place them in "backend", otherwise delete them.<br>
  Take the "version.txt" file and overwrite the existing one in backend.<br>
  <b>If there is a "preprocess.php"</b>, you MUST run the "preprocessor_run" function within (or read it and manually perform the actions it specifies) before doing anything else! Not doing so will probably break something.<br>
  If there is no pre-processing, or you have completed it, open the "core.txt" file. All the entries correspond to "core" system files. Each of them should be moved into the root of the MRS, overwriting what is already there, if necessary.<br>
  Remaining files should all be two-part files (for example, "api-autosys.php"). The first part corresponds to the containing folder. The second part is the name of the file. Remove the first part of the name, and move the file into the appropriate directory, overwriting if need be. If the directory doesn't exist, create it.<br>
  Open "config.txt". Inside is a list of configuration files, and their default settings. Most of these should already be present in the backend folder. For those that are not, create new text files, using the first part as the name and the second part as the contents. If you want to reset any settings, do so.<br>
  <b><u>DO NOT</u> touch ANYTHING ELSE</b> in the backend folder that is not listed! These are other files that may be needed for the system to function. Deleting them or otherwise changing them is at your own risk.<br>
  <b>If there is a "postprocess.php"</b>, you MUST run the "postprocessor_run" function within (or read it and manually perform the actions it specifies) before proceeding! Not doing so will probably break something.<br>
  At this point, the upgrade process should be completed.</p>
  <hr>
  <a name="skip"></a><h2>Alright, Let's Actually Get Upgrading!</h2>
  <p>The version of the MRS presently running is <?php echo $current->major . "." . $current->minor . ", revision " . $current->revision; ?>. <?php if ($current->beta === true) { echo "This is a beta release."; } ?><br>
  The latest version of the MRS is <?php echo $new->major . "." . $new->minor . ", revision " . $new->revision; ?>. <?php if ($new->beta === true) { echo "This is a beta release."; } ?></p>
  <p>
  <?php
	switch($check)
	{
		case 0:
		echo("There is a new MRS release available.");
		break;
		case 1:
		echo("There is a new revision to the presently running MRS release available.");
		break;
		case 2:
		echo("There is a new beta MRS release available, and you are set to be notified of beta releases.");
		break;
		case 3:
		echo("You are running the latest MRS software.");
		break;
		case 4:
		echo("You are running a newer release of the software than the mirror supplies.");
		break;
		case 5:
		echo("A new stable release of the MRS is available.");
		break;
		default:
		echo("It could not be determined whether you are running the latest release or not.");
		break;
	}
  ?>
  </p>
  <p>
  <?php
	chdir("upgrade");
	switch($check)
	{
		case 0:
		case 1:
		case 2:
		case 5:
		echo("Options: <a href=\"keep.php\">Partial upgrade</a> (settings are not changed) or <a href=\"destroy.php\">Full upgrade</a> (settings reset to factory defaults)");
		break;
		case 3:
		case 4:
		if(file_exists("latest.zip") && file_exists("latest-md5.txt"))
		{
			echo("An upgrade package and its associated checksum are present. An upgrade may be forced.<br>\r\n
			Options: <a href=\"keep.php\">Partial upgrade</a> (settings are not changed) or <a href=\"destroy.php\">Full upgrade</a> (settings reset to factory defaults)");
		}
		else
		{
			echo("An upgrade package and its associated checksum must be present before an upgrade may be forced. One or both is missing.");
		}
		break;
		default:
		echo("As the system is in an indeterminate state, you must upgrade it manually.");
		break;
	}
  ?>
  </p>
  <p>You may also do a <a href="dry.php">"dry" upgrade run</a> as a test (without actually upgrading anything). Even though it shouldn't delete anything, you should be sure to take a backup before doing this!</p>
  <p><a href="../admin.php">Go back</a></p>
  </body>
</html>