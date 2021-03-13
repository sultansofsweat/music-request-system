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
<?php
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <meta name="generator" content="CoffeeCup HTML Editor (www.coffeecup.com)">
    <meta name="created" content="Wed, 17 Jun 2015 12:33:52 GMT">
    <meta name="description" content="Listening to a live stream? Got a song you have to hear? This is the place to request it!">
	<link rel="shortcut icon" href="../backend/favicon.ico">
    <title> Music Request System-Upgrade System</title>
    
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
  <body>
  <?php
	//Get all necessary information
	$logging = is_logging_enabled();
	$stable = get_system_setting("stable");
	$mirror = get_system_setting("mirror") . "mrs2-upgrade";
	//Change working directory back
	chdir("upgrade");
	//Set termination flag
	$terminate=0;
	$downloaded=array(false,false);
	if($logging === true)
	{
		write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Attempted system upgrade");
	}
	if(securitycheck() === false)
	{
		die("You are not an administrator. <a href=\"login.php?rel=admin\">Sign in</a> or <a href=\"index.php\">Cancel</a>.");
	}
  ?>
  <?php
	function recover_mrs()
	{
		$error=0;
		$files=array_filter(glob("configback/*"),'is_file');
		foreach($files as $file)
		{
			$name=basename($file);
			$debug=@copy($file,"../backend/$name");
			if($debug !== true)
			{
				$error++;
			}
		}
		$files=array_filter(glob("sysback/*"),'is_file');
		foreach($files as $file)
		{
			$name=str_replace("-","/",basename($file));
			$debug=@copy($file,"../$name");
			if($debug !== true)
			{
				$error++;
			}
		}
		if($error > 0)
		{
			return false;
		}
		else
		{
			return true;
		}
	}
  ?>
  <h1 style="text-align:center; text-decoration:underline;">MRS-Upgrade System</h1>
  <h3>Checking Upgrade Packages</h3>
  <p>
  <?php
	/* PROCESS:
	Check for upgrade package. If not exists, download it.
	Check for MD5. If not exists, download it.
	Check MD5 hashes. */
	if($logging === true)
	{
		write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Checking upgrade packages");
		echo("Checking for upgrade package...");
		if(!file_exists("latest.zip"))
		{
			//Download file
			echo ("FAILED. Package doesn't exist.<br>\r\nDownloading upgrade package...");
			//Open zip file
			$dfh=fopen("latest.zip",'w+');
			if($dfh)
			{
				//Initialize curl
				$curl=curl_init();
				if($curl !== false)
				{
					//Set curl options
					if($stable == "yes")
					{
						curl_setopt($curl, CURLOPT_URL, "$mirror/latest-stable.zip");
					}
					else
					{
						curl_setopt($curl, CURLOPT_URL, "$mirror/latest.zip");
					}
					curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($curl, CURLOPT_BINARYTRANSFER, true);
					curl_setopt($curl, CURLOPT_FILE,$dfh);
					curl_setopt($curl, CURLOPT_HEADER, false);
					curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
					//Execute curl
					curl_exec($curl);
					
					//Check and form the data
					if(!curl_errno($curl) && curl_getinfo($curl,CURLINFO_HTTP_CODE) == 200)
					{
						echo ("DONE.<br>\r\n");
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Downloaded upgrade package from mirror");
						$downloaded[0]=true;
					}
					else
					{
						//Curl failed
						echo ("FAILED, error code " . curl_errno($curl) . ", HTTP response code " . curl_getinfo($curl,CURLINFO_HTTP_CODE) . ". Change mirrors, or contact the software vendor and threaten to microwave their microphone.<br>\r\n");
						fclose($dfh);
						unlink("latest.zip");
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to download upgrade package");
						$terminate=1;
					}
					//Close session
					curl_close($curl);
					fclose($dfh);
				}
			}
			else
			{
				//Cannot open the file for writing.
				echo ("FAILED, unable to save file. Submerge the upgrade directory in pool water.<br>\r\n");
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to download upgrade package");
				$terminate=1;
			}
		}
		else
		{
			echo("DONE.<br>\r\n");
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Upgrade package exists");
		}
		echo("Checking for upgrade MD5 signature...");
		if(!file_exists("latest-md5.txt"))
		{
			//Download file
			echo ("FAILED. File doesn't exist.<br>\r\nDownloading upgrade MD5 signature file...");
			//Open zip file
			$dfh=fopen("latest-md5.txt",'w');
			if($dfh)
			{
				//Initialize curl
				$curl=curl_init();
				if($curl !== false)
				{
					//Set curl options
					if($stable == "yes")
					{
						curl_setopt($curl, CURLOPT_URL, "$mirror/latest-stable-md5.txt");
					}
					else
					{
						curl_setopt($curl, CURLOPT_URL, "$mirror/latest-md5.txt");
					}
					curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($curl, CURLOPT_BINARYTRANSFER, false);
					curl_setopt($curl, CURLOPT_FILE,$dfh);
					curl_setopt($curl, CURLOPT_HEADER, false);
					curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
					//Execute curl
					curl_exec($curl);
					
					//Check and form the data
					if(!curl_errno($curl) && curl_getinfo($curl,CURLINFO_HTTP_CODE) == 200)
					{
						echo ("DONE.<br>\r\n");
						$downloaded[1]=true;
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Downloaded checksum file from mirror");
					}
					else
					{
						//Curl failed
						echo ("FAILED, error code " . curl_errno($curl) . ", HTTP response code " . curl_getinfo($curl,CURLINFO_HTTP_CODE) . ". Change mirrors, or contact the software vendor and threaten to microwave their microphone.<br>\r\n");
						fclose($dfh);
						unlink("latest-md5.txt");
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to download checksum file");
						$terminate=1;
					}
					//Close session
					curl_close($curl);
					fclose($dfh);
				}
			}
			else
			{
				//Cannot open the file for writing.
				echo ("FAILED, unable to save file. Submerge the upgrade directory in pool water.<br>\r\n");
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to download checksum file");
				$terminate=1;
			}
		}
		else
		{
			echo("DONE.<br>\r\n");
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Checksum file exists");
		}
		if($terminate != 1)
		{
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Getting checksum of upgrade package");
			echo("Getting checksum of upgrade package...");
			$md5local=md5_file("latest.zip");
			echo("DONE. Checksum is $md5local.<br>\r\n");
			echo("Verifying checksum...");
			if(file_exists("latest-md5.txt"))
			{
				$md5remote=file_get_contents("latest-md5.txt");
			}
			else
			{
				$md5remote="";
			}
			if($md5local == $md5remote)
			{
				echo("DONE.\r\n");
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Checksum valid");
			}
			else
			{
				echo("FAILED. Expected hash is $md5remote.\r\n");
				if($downloaded[0] === true)
				{
					unlink("latest.zip");
				}
				if($downloaded[1] === true)
				{
					unlink("latest-md5.txt");
				}
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Invalid checksum, halting upgrade process");
				$terminate=1;
			}
		}
	}
	else
	{
		echo("Checking for upgrade package...");
		if(!file_exists("latest.zip"))
		{
			//Download file
			echo ("FAILED. Package doesn't exist.<br>\r\nDownloading upgrade package...");
			//Open zip file
			$dfh=fopen("latest.zip",'w+');
			if($dfh)
			{
				//Initialize curl
				$curl=curl_init();
				if($curl !== false)
				{
					//Set curl options
					if($stable == "yes")
					{
						curl_setopt($curl, CURLOPT_URL, "$mirror/latest-stable.zip");
					}
					else
					{
						curl_setopt($curl, CURLOPT_URL, "$mirror/latest.zip");
					}
					curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($curl, CURLOPT_BINARYTRANSFER, true);
					curl_setopt($curl, CURLOPT_FILE,$dfh);
					curl_setopt($curl, CURLOPT_HEADER, false);
					curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
					//Execute curl
					curl_exec($curl);
					
					//Check and form the data
					if(!curl_errno($curl) && curl_getinfo($curl,CURLINFO_HTTP_CODE) == 200)
					{
						echo ("DONE.<br>\r\n");
						$downloaded[0]=true;
					}
					else
					{
						//Curl failed
						echo ("FAILED, error code " . curl_errno($curl) . ", HTTP response code " . curl_getinfo($curl,CURLINFO_HTTP_CODE) . ". Change mirrors, or contact the software vendor and threaten to microwave their microphone.<br>\r\n");
						fclose($dfh);
						unlink("latest.zip");
					}
					//Close session
					curl_close($curl);
					fclose($dfh);
				}
			}
			else
			{
				//Cannot open the file for writing.
				echo ("FAILED, unable to save file. Submerge the upgrade directory in pool water.<br>\r\n");
			}
		}
		else
		{
			echo("DONE.<br>\r\n");
		}
		echo("Checking for upgrade MD5 signature...");
		if(!file_exists("latest-md5.txt"))
		{
			//Download file
			echo ("FAILED. File doesn't exist.<br>\r\nDownloading upgrade MD5 signature file...");
			//Open zip file
			$dfh=fopen("latest-md5.txt",'w');
			if($dfh)
			{
				//Initialize curl
				$curl=curl_init();
				$check=-1;
				if($curl !== false)
				{
					//Set curl options
					if($stable == "yes")
					{
						curl_setopt($curl, CURLOPT_URL, "$mirror/latest-stable-md5.txt");
					}
					else
					{
						curl_setopt($curl, CURLOPT_URL, "$mirror/latest-md5.txt");
					}
					curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($curl, CURLOPT_BINARYTRANSFER, false);
					curl_setopt($curl, CURLOPT_FILE,$dfh);
					curl_setopt($curl, CURLOPT_HEADER, false);
					curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
					//Execute curl
					curl_exec($curl);
					
					//Check and form the data
					if(!curl_errno($curl) && curl_getinfo($curl,CURLINFO_HTTP_CODE) == 200)
					{
						echo ("DONE.<br>\r\n");
						$downloaded[1]=true;
					}
					else
					{
						//Curl failed
						echo ("FAILED, error code " . curl_errno($curl) . ", HTTP response code " . curl_getinfo($curl,CURLINFO_HTTP_CODE) . ". Change mirrors, or contact the software vendor and threaten to microwave their microphone.<br>\r\n");
						fclose($dfh);
						unlink("latest-md5.txt");
					}
					//Close session
					curl_close($curl);
					fclose($dfh);
				}
			}
			else
			{
				//Cannot open the file for writing.
				echo ("FAILED, unable to save file. Submerge the upgrade directory in pool water.<br>\r\n");
			}
		}
		else
		{
			echo("DONE.<br>\r\n");
		}
		if($terminate != 1)
		{
			echo("Getting checksum of upgrade package...");
			$md5local=md5_file("latest.zip");
			echo("DONE. Checksum is $md5local.<br>\r\n");
			echo("Verifying checksum...");
			if(file_exists("latest-md5.txt"))
			{
				$md5remote=file_get_contents("latest-md5.txt");
			}
			else
			{
				$md5remote="";
			}
			if($md5local == $md5remote)
			{
				echo("DONE.\r\n");
			}
			else
			{
				echo("FAILED. Expected hash is $md5remote.\r\n");
				if($downloaded[0] === true)
				{
					unlink("latest.zip");
				}
				if($downloaded[1] === true)
				{
					unlink("latest-md5.txt");
				}
				$terminate=1;
			}
		}
	}
  ?>
  </p>
  <hr>
  <h3>Unpacking Upgrade Packages</h3>
  <p>
  <?php
	/* PROCESS:
	Create new directory ("files")
	Unpack into files directory */
	if($terminate != 1)
	{
		if($logging === true)
		{
			echo("Creating temporary directory to store upgrade files...");
			if(!file_exists("files") || !is_dir("files"))
			{
				$debug=mkdir("files");
			}
			else
			{
				$debug=true;
			}
			if($debug === true)
			{
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Created directory \"files\"");
				echo("DONE.<br>\r\nExtracting upgrade package...");
				$arch=new ZipArchive;
				if($arch->open("latest.zip"))
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Opened upgrade package");
					$debug=$arch->extractTo("files");
					if($debug === true)
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Extracted upgrade package to \"files\"");
						echo ("DONE.<br>\r\n");
					}
					else
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to extract upgrade package");
						echo("FAILED. Could not extract archive. Please re-download the MRS upgrade package, or contact the software vendor.<br>\r\n");
						$terminate=1;
						rmdir("files");
					}
					$arch->close();
				}
				else
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to open upgrade package");
					echo("FAILED. Could not open archive. Please re-download the MRS upgrade package, or contact the software vendor.<br>\r\n");
					$terminate=1;
					rmdir("files");
				}
			}
			else
			{
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to create directory \"files\"");
				echo("FAILED. Submerge the upgrade directory in pool water.\r\n");
				$terminate=1;
			}
		}
		else
		{
			echo("Creating temporary directory to store upgrade files...");
			if(!file_exists("files") || !is_dir("files"))
			{
				$debug=mkdir("files");
			}
			else
			{
				$debug=true;
			}
			if($debug === true)
			{
				echo("DONE.<br>\r\nExtracting upgrade package...");
				$arch=new ZipArchive;
				if($arch->open("latest.zip"))
				{
					$debug=$arch->extractTo("files");
					if($debug === true)
					{
						echo ("DONE.<br>\r\n");
					}
					else
					{
						echo("FAILED. Could not extract archive. Please re-download the MRS upgrade package, or contact the software vendor.<br>\r\n");
						$terminate=1;
						rmdir("files");
					}
					$arch->close();
				}
				else
				{
					echo("FAILED. Could not open archive. Please re-download the MRS upgrade package, or contact the software vendor.<br>\r\n");
					$terminate=1;
					rmdir("files");
				}
			}
			else
			{
				echo("FAILED. Submerge the upgrade directory in pool water.\r\n");
				$terminate=1;
			}
		}
	}
	else
	{
		echo("Upgrade process terminated by a previous unrecoverable error.");
	}
  ?>
  </p>
  <hr>
  <h3>Preprocessing</h3>
  <p>
  <?php
	/* PROCESS:
	Close system
	Create new directories for backups ("configback" and "sysback")
	Back up all configuration files and system files
	If preprocessing file exists, include it, otherwise skip */
	if($terminate != 1)
	{
		if($logging === true)
		{
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Checking upgrade packages");
			echo("Closing MRS...");
			chdir("..");
			$debug=save_system_setting("posting","no");
			chdir("upgrade");
			if($debug !== true)
			{
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"posting\" to \"no\"");
				echo("FAILED. Proceeding anyways, expect problems.<br>\r\n");
			}
			else
			{
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Successfully changed setting \"posting\" to \"no\"");
				echo("DONE.");
			}
			$backup=0;
			echo("Creating backup storage directories...");
			if(!file_exists("sysback"))
			{
				$debug=mkdir("sysback");
			}
			else
			{
				$debug=true;
			}
			if($debug === true)
			{
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Created directory \"sysback\"");
				if(!file_exists("configback"))
				{
					$debug=mkdir("configback");
				}
				else
				{
					$debug=true;
				}
				if($debug === true)
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Created directory \"configback\"");
					echo("DONE.<br>\r\n");
					$backup=1;
				}
				else
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to create directory \"configback\"");
					echo("FAILED. Proceeding anyways, no backup will be made, expect problems.<br>\r\n");
					rmdir("sysback");
				}
			}
			else
			{
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to create directory sysback");
				echo("FAILED. Proceeding anyways, no backup will be made, expect problems.<br>\r\n");
			}
			if($backup == 1)
			{
				echo("Backing up system files...");
				$errors=0;
				$filecount=0;
				$files=glob("../*.php");
				if(count($files) > 0)
				{
					foreach($files as $file)
					{
						$name=basename($file);
						$debug=@copy($file,"sysback/$name");
						if($debug === true)
						{
							write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Successfully backed up file \"$file\" to \"sysback/$name\"");
							$filecount++;
						}
						else
						{
							write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to back up file \"$file\" to \"sysback/$name\"");
							$errors++;
						}
					}
				}
				$files=glob("*.php");
				if(count($files) > 0)
				{
					foreach($files as $file)
					{
						$name=basename($file);
						$debug=@copy($file,"sysback/upgrade-$name");
						if($debug === true)
						{
							write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Successfully backed up file \"$file\" to \"sysback/upgrade-$name\"");
							$filecount++;
						}
						else
						{
							write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to back up file \"$file\" to \"sysback/upgrade-$name\"");
							$errors++;
						}
					}
				}
				$files=glob("../api/*.php");
				if(count($files) > 0)
				{
					foreach($files as $file)
					{
						$name=basename($file);
						$debug=@copy($file,"sysback/api-$name");
						if($debug === true)
						{
							write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Successfully backed up file \"$file\" to \"sysback/api-$name\"");
							$filecount++;
						}
						else
						{
							write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to back up file \"$file\" to \"sysback/api-$name\"");
							$errors++;
						}
					}
				}
				$files=glob("../backend/*.php");
				if(count($files) > 0)
				{
					foreach($files as $file)
					{
						$name=basename($file);
						$debug=@copy($file,"sysback/backend-$name");
						if($debug === true)
						{
							write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Successfully backed up file \"$file\" to \"sysback/backend-$name\"");
							$filecount++;
						}
						else
						{
							write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to back up file \"$file\" to \"sysback/backend-$name\"");
							$errors++;
						}
					}
				}
				echo("DONE. $filecount files backed up, $errors errors.<br>\r\nBacking up configuration...");
				$errors=0;
				$filecount=0;
				$files=glob("../backend/*.txt");
				if(count($files) > 0)
				{
					foreach($files as $file)
					{
						$name=basename($file);
						$debug=@copy($file,"configback/$name");
						if($debug === true)
						{
							write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Successfully backed up file \"$file\" to \"configback/$name\"");
							$filecount++;
						}
						else
						{
							write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to back up file \"$file\" to \"configback/$name\"");
							$errors++;
						}
					}
				}
				if(file_exists("../backend/favicon.ico"))
				{
					$debug=@copy("../backend/favicon.ico","configback/favicon.ico");
					if($debug === true)
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Successfully backed up file \"../backend/favicon.ico\" to \"configback/favicon.ico\"");
						$filecount++;
					}
					else
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to back up file \"../backend/favicon.ico\" to \"configback/favicon.ico\"");
						$errors++;
					}
				}
				if(file_exists("../backend/background.gif"))
				{
					$debug=@copy("../backend/background.gif","configback/background.gif");
					if($debug === true)
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Successfully backed up file \"../backend/background.gif\" to \"configback/background.gif\"");
						$filecount++;
					}
					else
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to back up file \"../backend/background.gif\" to \"configback/background.gif\"");
						$errors++;
					}
				}
				echo("DONE. $filecount files backed up, $errors errors.<br>\r\nRunning pre-processor script...");
				if(file_exists("files/preprocess.php"))
				{
					include("files/preprocess.php");
					if(function_exists("preprocessor_run"))
					{
						$debug=preprocessor_run();
						if($debug === true)
						{
							write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Successfully ran pre-processor");
							echo("DONE.");
						}
						else
						{
							write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to run pre-processor, error code $debug");
							echo("FAILED. Error code is $debug. Proceeding anyways, expect problems.");
						}
					}
					else
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to run pre-processor, error code -1");
						echo("FAILED. Invalid preprocessor. Proceeding anyways, expect problems.");
					}
				}
				else
				{
					echo("SKIPPED.");
				}
			}
		}
		else
		{
			echo("Closing MRS...");
			chdir("..");
			$debug=save_system_setting("posting","no");
			chdir("upgrade");
			if($debug !== true)
			{
				echo("FAILED. Proceeding anyways, expect problems.<br>\r\n");
			}
			else
			{
				echo("DONE.<br>\r\n");
			}
			$backup=0;
			echo("Creating backup storage directories...");
			if(!file_exists("sysback"))
			{
				$debug=mkdir("sysback");
			}
			else
			{
				$debug=true;
			}
			if($debug === true)
			{
				if(!file_exists("configback"))
				{
					$debug=mkdir("configback");
				}
				else
				{
					$debug=true;
				}
				if($debug === true)
				{
					echo("DONE.<br>\r\n");
					$backup=1;
				}
				else
				{
					echo("FAILED. Proceeding anyways, no backup will be made, expect problems.<br>\r\n");
					rmdir("sysback");
				}
			}
			else
			{
				echo("FAILED. Proceeding anyways, no backup will be made, expect problems.<br>\r\n");
			}
			if($backup == 1)
			{
				echo("Backing up system files...");
				$errors=0;
				$filecount=0;
				$files=glob("../*.php");
				if(count($files) > 0)
				{
					foreach($files as $file)
					{
						$name=basename($file);
						$debug=@copy($file,"sysback/$name");
						if($debug === true)
						{
							$filecount++;
						}
						else
						{
							$errors++;
						}
					}
				}
				$files=glob("*.php");
				if(count($files) > 0)
				{
					foreach($files as $file)
					{
						$name=basename($file);
						$debug=@copy($file,"sysback/upgrade-$name");
						if($debug === true)
						{
							$filecount++;
						}
						else
						{
							$errors++;
						}
					}
				}
				$files=glob("../api/*.php");
				if(count($files) > 0)
				{
					foreach($files as $file)
					{
						$name=basename($file);
						$debug=@copy($file,"sysback/api-$name");
						if($debug === true)
						{
							$filecount++;
						}
						else
						{
							$errors++;
						}
					}
				}
				$files=glob("../backend/*.php");
				if(count($files) > 0)
				{
					foreach($files as $file)
					{
						$name=basename($file);
						$debug=@copy($file,"sysback/backend-$name");
						if($debug === true)
						{
							$filecount++;
						}
						else
						{
							$errors++;
						}
					}
				}
				echo("DONE. $filecount files backed up, $errors errors.<br>\r\nBacking up configuration...");
				$errors=0;
				$filecount=0;
				$files=glob("../backend/*.txt");
				if(count($files) > 0)
				{
					foreach($files as $file)
					{
						$name=basename($file);
						$debug=@copy($file,"configback/$name");
						if($debug === true)
						{
							$filecount++;
						}
						else
						{
							$errors++;
						}
					}
				}
				if(file_exists("../backend/favicon.ico"))
				{
				$debug=@copy("../backend/favicon.ico","configback/favicon.ico");
				if($debug === true)
				{
					$filecount++;
				}
				else
				{
					$errors++;
				}
				}
				if(file_exists("../backend/background.gif"))
				{
					$debug=@copy("../backend/background.gif","configback/background.gif");
					if($debug === true)
					{
						$filecount++;
					}
					else
					{
						$errors++;
					}
				}
				echo("DONE. $filecount files backed up, $errors errors.<br>\r\nRunning pre-processor script...");
				if(file_exists("files/preprocess.php"))
				{
					include("files/preprocess.php");
					if(function_exists("preprocessor_run"))
					{
						$debug=preprocessor_run();
						if($debug === true)
						{
							echo("DONE.");
						}
						else
						{
							echo("FAILED. Error code is $debug. Proceeding anyways, expect problems.");
						}
					}
					else
					{
						echo("FAILED. Invalid preprocessor. Proceeding anyways, expect problems.");
					}
				}
				else
				{
					echo("SKIPPED.");
				}
			}
		}
	}
	else
	{
		echo("Upgrade process terminated by a previous unrecoverable error.");
	}
  ?>
  </p>
  <hr>
  <h3>Replacing Core Files</h3>
  <p>
  <?php
	/* PROCESS:
	Enumerate core files to replace
	Replace files. On error, stop process. */
	if($terminate != 1)
	{
		if($logging === true)
		{
			echo("Enumerating core files to replace...");
			if(file_exists("files/core.txt"))
			{
				$corefiles=explode("\r\n",file_get_contents("files/core.txt"));
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Enumerated list of files to replace");
				echo("DONE. There are " . count($corefiles) . " files to replace.<br>\r\nTesting core file replace...");
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Attempted to replace index.php");
				$error=rename("sysback/index.php","../index.php");
				if($error === true)
				{
					echo("DONE.");
				}
				else
				{
					echo("FAILED. This could be a problem...<br>\r\n");
					$terminate=1;
				}
			}
			else
			{
				echo("FAILED. Throw a GPX clock radio at the server.");
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to enumerate list of core files");
				$terminate=1;
			}
		}
		else
		{
			echo("Enumerating core files to replace...");
			if(file_exists("files/core.txt"))
			{
				$corefiles=explode("\r\n",file_get_contents("files/core.txt"));
				echo("DONE. There are " . count($corefiles) . " files to replace.<br>\r\nTesting core file replace...");
				$error=rename("sysback/index.php","../index.php");
				if($error === true)
				{
					echo("DONE.");
				}
				else
				{
					echo("FAILED. This could be a problem...<br>\r\n");
					$terminate=1;
				}
			}
			else
			{
				echo("FAILED. Throw a GPX clock radio at the server.");
				$terminate=1;
			}
		}
	}
	else
	{
		echo("Upgrade process terminated by a previous unrecoverable error.");
	}
  ?>
  </p>
  <hr>
  <h3>Replacing Additional Files</h3>
  <p>
  <?php
	/* PROCESS:
	Enumerate files to replace.
	Replace files. On error, issue warning. */
	if($terminate != 1)
	{
		if($logging === true)
		{
			echo("Enumerating additional files to replace...");
			if(file_exists("files/additional.txt"))
			{
				$corefiles=explode("\r\n",file_get_contents("files/additional.txt"));
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Enumerated list of files to replace");
				echo("DONE. There are " . count($corefiles) . " files to replace.<br>\r\nTesting additional file replace...");
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Attempted to replace upgrade-index.php");
				$error=rename("sysback/upgrade-index.php","../upgrade/index.php");
				if($error === true)
				{
					echo("DONE.");
				}
				else
				{
					echo("FAILED. This could be a problem...<br>\r\n");
				}
			}
			else
			{
				echo("FAILED. Throw a GPX clock radio at the server.");
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to enumerate list of additional files");
			}
		}
		else
		{
			echo("Enumerating additional files to replace...");
			if(file_exists("files/additional.txt"))
			{
				$corefiles=explode("\r\n",file_get_contents("files/additional.txt"));
				echo("DONE. There are " . count($corefiles) . " files to replace.<br>\r\nTesting additional file replace...");
				$error=rename("sysback/upgrade-index.php","../upgrade/index.php");
				if($error === true)
				{
					echo("DONE.");
				}
				else
				{
					echo("FAILED. This could be a problem...<br>\r\n");
					$terminate=1;
				}
			}
			else
			{
				echo("FAILED. Throw a GPX clock radio at the server.");
				$terminate=1;
			}
		}
	}
	else
	{
		echo("Upgrade process terminated by a previous unrecoverable error.");
	}
  ?>
  </p>
  <hr>
  <h3>Installing New Configuration Files</h3>
  <p>
  <?php
	/* PROCESS:
	Read in configuration file information.
	For all missing files, add new configuration file. */
	if($terminate != 1)
	{
		if($logging === true)
		{
			echo("Reading base configuration...");
			if(file_exists("files/config.txt"))
			{
				$configs=explode("\r\n",file_get_contents("files/config.txt"));
				for($i=0;$i<count($configs);$i++)
				{
					$configs[$i]=explode("|",$configs[$i]);
				}
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Enumerated list of configuration files");
				echo("DONE.<br>\r\nCreating test configuration...");
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Attempting to write config file \"dryrunconfigtest\"");
				$fh=fopen("../backend/dryrunconfigtest.txt",'w');
				if($fh)
				{
					fwrite($fh,"test!");
					fclose($fh);
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Installed file \"dryrunconfigtest\" with value \"test!\"");
					echo("DONE.<br>\r\nVerifying test config...");
					if(file_exists("../backend/dryrunconfigtest.txt") && file_get_contents("../backend/dryrunconfigtest.txt") == "test!")
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Sucessfully verified configuration writing");
						echo("DONE.<br>\r\nDeleting test file...");
						$debug=unlink("../backend/dryrunconfigtest.txt");
						if($debug === true)
						{
							write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Sucessfully cleaned up configuration writing test");
							echo("DONE.");
						}
						else
						{
							write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed clean up configuration writing test");
							echo("FAILED. You will have to clean up yourself.");
						}
					}
					else
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to verify configuration writing");
						echo("FAILED. This could be a problem.");
					}
				}
				else
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to install file \"dryrunconfigtest\"");
					echo("FAILED. This could be a problem.");
				}
			}
			else
			{
				echo("FAILED. The Russians probably got involved somehow.<br>\r\n");
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to enumerate configuration files");
			}
		}
		else
		{
			echo("Reading base configuration...");
			if(file_exists("files/config.txt"))
			{
				$configs=explode("\r\n",file_get_contents("files/config.txt"));
				for($i=0;$i<count($configs);$i++)
				{
					$configs[$i]=explode("|",$configs[$i]);
				}
				echo("DONE.<br>\r\nCreating test configuration...");
				$fh=fopen("../backend/dryrunconfigtest.txt",'w');
				if($fh)
				{
					fwrite($fh,"test!");
					fclose($fh);
					echo("DONE.<br>\r\nVerifying test config...");
					if(file_exists("../backend/dryrunconfigtest.txt") && file_get_contents("../backend/dryrunconfigtest.txt") == "test!")
					{
						echo("DONE.<br>\r\nDeleting test file...");
						$debug=unlink("../backend/dryrunconfigtest.txt");
						if($debug === true)
						{
							echo("DONE.");
						}
						else
						{
							echo("FAILED. You will have to clean up yourself.");
						}
					}
					else
					{
						echo("FAILED. This could be a problem.");
					}
				}
				else
				{
					echo("FAILED. This could be a problem.");
				}
			}
			else
			{
				echo("FAILED. The Russians probably got involved somehow.<br>\r\n");
			}
		}
	}
	else
	{
		echo("Upgrade process terminated by a previous unrecoverable error.");
	}
  ?>
  </p>
  <hr>
  <h3>Removing Obsolete Configuration Files</h3>
  <p>
  Process not required, skipping.
  </p>
  <hr>
  <h3>Removing Obsolete Core Files</h3>
  <p>
  Process not required, skipping.
  </p>
  <hr>
  <h3>Postprocessing</h3>
  <p>
  Process not required, skipping.
  </p>
  <hr>
  <h3>Removing Leftover Upgrade Files</h3>
  <p>
  <?php
	/* PROCESS:
	Remove all remaining text files from upgrade directory
	Remove all remaining PHP files from upgrade directory */
	if($terminate != 1)
	{
		if($logging === true)
		{
			echo("Removing all remaining temporary files...");
			$removed=0;
			$errors=0;
			foreach(array_filter(glob("files/*"),'is_file') as $file)
			{
				$debug=unlink($file);
				if($debug === true)
				{
					$removed++;
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Successfully removed file \"$file\"");
				}
				else
				{
					$errors++;
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to remove file \"$file\"");
				}
			}
			echo("DONE. Removed $removed files with $errors errors.<br>\r\nRemoving temporary directory...");
			$debug=rmdir("files");
			if($debug === true)
			{
				echo("DONE.");
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Successfully removed directory \"files\"");
			}
			else
			{
				echo("FAILED. You will have to do the cleanup yourself.");
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to remove directory \"files\"");
			}
		}
		else
		{
			echo("Removing all remaining temporary files...");
			$removed=0;
			$errors=0;
			foreach(array_filter(glob("files/*"),'is_file') as $file)
			{
				$debug=unlink($file);
				if($debug === true)
				{
					$removed++;
				}
				else
				{
					$errors++;
				}
			}
			echo("DONE. Removed $removed files with $errors errors.<br>\r\nRemoving temporary directory...");
			$debug=rmdir("files");
			if($debug === true)
			{
				echo("DONE.");
			}
			else
			{
				echo("FAILED. You will have to do the cleanup yourself.");
			}
		}
	}
	else
	{
		echo("Upgrade process terminated by a previous unrecoverable error.");
	}
  ?>
  </p>
  <hr>
  <h3>Removing Upgrade Packages</h3>
  <p>
  <?php
	/* PROCESS:
	Remove upgrade ZIP package.
	Remove MD5 file. */
	if($terminate != 1)
	{
		if($logging === true)
		{
			echo("Removing upgrade package...");
			if($downloaded[0] === true)
			{
				$debug=unlink("latest.zip");
				if($debug === true)
				{
					echo("DONE.<br>\r\n");
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Successfully removed upgrade package");
				}
				else
				{
					echo("FAILED. You will have to do the cleanup yourself.<br>\r\n");
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to remove upgrade package");
				}
			}
			else
			{
				echo("SKIPPED.<br>\r\n");
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Upgrade package not downloaded, skipping");
			}
			echo("Removing upgrade MD5 signature file...");
			if($downloaded[1] === true)
			{
				$debug=unlink("latest-md5.txt");
				if($debug === true)
				{
					echo("DONE.");
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Successfully removed upgrade MD5 file");
				}
				else
				{
					echo("FAILED. You will have to do the cleanup yourself.");
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to remove upgrade MD5 file");
				}
			}
			else
			{
				echo("SKIPPED.<br>\r\n");
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Upgrade package not downloaded, skipping");
			}
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Upgrade process completed");
		}
		else
		{
			echo("Removing upgrade package...");
			if($downloaded[0] === true)
			{
				$debug=unlink("latest.zip");
				if($debug === true)
				{
					echo("DONE.<br>\r\n");
				}
				else
				{
					echo("FAILED. You will have to do the cleanup yourself.<br>\r\n");
				}
			}
			else
			{
				echo("SKIPPED.<br>\r\n");
			}
			echo("Removing upgrade MD5 signature file...");
			if($downloaded[1] === true)
			{
				$debug=unlink("latest-md5.txt");
				if($debug === true)
				{
					echo("DONE.");
				}
				else
				{
					echo("FAILED. You will have to do the cleanup yourself.");
				}
			}
			else
			{
				echo("SKIPPED.<br>\r\n");
			}
		}
	}
	else
	{
		echo("Upgrade process terminated by a previous unrecoverable error.");
	}
  ?>
  </p>
  <h4>Process complete. Check above for errors.<br>
  <a href="index.php">Go back</a></h4>
  </body>
</html>