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
    <title><?php echo $sysname; ?>Music Request System-Preparing Updates</title>
    
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
	write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Visited update install page");
	//Run security check
	if(securitycheck() === false)
	{
		//No admin privileges, no page viewing privileges
		die("You are not an administrator. <a href=\"../login.php?ref=admin\">Sign in</a> or <a href=\"../index.php\">Cancel</a>.");
	}
	$run=false;
	$d=-1;
	if(isset($_GET['d']))
	{
		$d=preg_replace("/[^0-1]/","",$_GET['d']);
		if($d == 0 || $d == 1)
		{
			$run=true;
		}
	}
	if($run === true && !file_exists("upgrade/prepare-complete.txt"))
	{
		die("<script type=\"text/javascript\">window.location = \"index.php\"</script>");
	}
  ?>
  <body>
  <h1 style="text-align:center; text-decoration:underline;"><?php echo $sysname; ?>Music Request System-Install Updates</h1>
  <div <?php if($run === true) { echo "style=\"display:none\""; } ?>><p>This will <b>irreversibly</b> install any and all prepared updates to the MRS. Did you take a backup yet?</p>
  <p>There are two installation methods:</p>
  <ul>
  <li><b>Partial</b>: this option will ignore any code file or setting file removals, and it will not execute any settings file changes.</li>
  <li><b>Full</b>: this option will perform all tasks, including overwriting the system background and icon. Note that it does not touch any settings that were changed from the defaults.</li>
  </ul>
  <p><a href="install.php?d=0">Partial Upgrade</a> | <a href="install.php?d=1">Full Upgrade</a> | <a href="index.php">Cancel</a></p></div>
  <div <?php if($run === false) { echo "style=\"display:none\""; } ?>><h2>Backing Up System</h2>
  <p>
  <?php
	if($run === true)
	{
		//Get all core files
		$core=glob("*.php");
		//Get all upgrade files
		$upgrade=glob("upgrade/*.php");
		//Get all API files
		$api=glob("api/*.php");
		//Get all backend files
		$backend=glob("backend/*.php");
		//Get all configuration files
		$configs=glob("backend/*.txt");
		//Backup all core files
		$count=array(0,0);
		echo("Backing up core files...");
		foreach($core as $file)
		{
			$debug=copy($file,"upgrade/backup/core-$file");
			if($debug === true)
			{
				$count[0]++;
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Backed up file \"$file\"");
			}
			else
			{
				$count[1]++;
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to back up file \"$file\"");
			}
		}
		echo("DONE. " . array_sum($count) . " files found, " . $count[0] . " files backed up, " . $count[1] . " errors.<br>\r\n");
		//Backup all upgrader files
		$count=array(0,0);
		echo("Backing up upgrader files...");
		foreach($upgrade as $file)
		{
			$debug=copy($file,"upgrade/backup/" . str_replace("/","-",$file));
			if($debug === true)
			{
				$count[0]++;
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Backed up file \"$file\"");
			}
			else
			{
				$count[1]++;
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to back up file \"$file\"");
			}
		}
		echo("DONE. " . array_sum($count) . " files found, " . $count[0] . " files backed up, " . $count[1] . " errors.<br>\r\n");
		//Backup all API files
		$count=array(0,0);
		echo("Backing up API files...");
		foreach($api as $file)
		{
			$debug=copy($file,"upgrade/backup/" . str_replace("/","-",$file));
			if($debug === true)
			{
				$count[0]++;
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Backed up file \"$file\"");
			}
			else
			{
				$count[1]++;
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to back up file \"$file\"");
			}
		}
		echo("DONE. " . array_sum($count) . " files found, " . $count[0] . " files backed up, " . $count[1] . " errors.<br>\r\n");
		//Backup all backend files
		$count=array(0,0);
		echo("Backing up backend files...");
		foreach($backend as $file)
		{
			$debug=copy($file,"upgrade/backup/" . str_replace("/","-",$file));
			if($debug === true)
			{
				$count[0]++;
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Backed up file \"$file\"");
			}
			else
			{
				$count[1]++;
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to back up file \"$file\"");
			}
		}
		echo("DONE. " . array_sum($count) . " files found, " . $count[0] . " files backed up, " . $count[1] . " errors.<br>\r\n");
		//Backup all configuration files
		$count=array(0,0);
		echo("Backing up settings...");
		foreach($configs as $file)
		{
			$debug=copy($file,"upgrade/backup/" . str_replace("backend/","",$file));
			if($debug === true)
			{
				$count[0]++;
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Backed up file \"$file\"");
			}
			else
			{
				$count[1]++;
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to back up file \"$file\"");
			}
		}
		echo("DONE. " . array_sum($count) . " files found, " . $count[0] . " files backed up, " . $count[1] . " errors.<br>\r\n");
		//Backup all system files
		$count=array(0,0);
		echo("Backing up system files...");
		$debug=copy("backend/version.txt","upgrade/backup/version.txt");
		if($debug === true)
		{
			$count[0]++;
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Backed up file \"version.txt\"");
		}
		else
		{
			$count[1]++;
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to back up file \"version.txt\"");
		}
		$debug=copy("backend/background.gif","upgrade/backup/background.gif");
		if($debug === true)
		{
			$count[0]++;
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Backed up file \"background.gif\"");
		}
		else
		{
			$count[1]++;
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to back up file \"background.gif\"");
		}
		$debug=copy("backend/favicon.ico","upgrade/backup/favicon.ico");
		if($debug === true)
		{
			$count[0]++;
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Backed up file \"favicon.ico\"");
		}
		else
		{
			$count[1]++;
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to back up file \"favicon.ico\"");
		}
		echo("DONE. " . array_sum($count) . " files found, " . $count[0] . " files backed up, " . $count[1] . " errors.<br>\r\n");
		echo("Closing MRS to requests...");
		$debug=save_system_setting("posting","no");
		if($debug === true)
		{
			echo("DONE.\r\n");
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Closed system");
		}
		else
		{
			echo("FAILED. Proceeding anyways, expect problems.\r\n");
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to close system");
		}
	}
  ?>
  </p>
  <hr>
  <h2>Updating Code Files</h2>
  <p>
  <?php
	if($run === true)
	{
		echo("Calculating changes...");
		$count=array(0,0);
		$codeadd=array();
		$coderem=array();
		if(file_exists("upgrade/prepare/code-add.txt"))
		{
			$codeadd=array_filter(explode("\r\n",file_get_contents("upgrade/prepare/code-add.txt")));
			$count[0]=count($codeadd);
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Calculated number of code files to add/change");
		}
		if(file_exists("upgrade/prepare/code-rem.txt"))
		{
			$coderem=array_filter(explode("\r\n",file_get_contents("upgrade/prepare/code-rem.txt")));
			$count[1]=count($coderem);
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Calculated number of code files to remove");
		}
		echo("DONE. " . $count[0] . " additions/modifications and " . $count[1] . " removals found.<br>\r\n");
		echo("Processing additions...");
		$count=array(0,0);
		foreach($codeadd as $file)
		{
			$newname=str_replace("core/","",str_replace("-","/",$file));
			$debug=rename("upgrade/prepare/$file.php","$newname.php");
			if($debug === true)
			{
				$count[0]++;
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Moved file \"$file\" to \"$newname\"");
			}
			else
			{
				$count[1]++;
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to move file \"$file\" to \"$newname\"");
			}
		}
		echo("DONE. Of " . array_sum($count) . " files, " . $count[0] . " were moved with " . $count[1] . " errors.<br>\r\n");
		echo("Processing removals...");
		if($d == 1)
		{
			$count=array(0,0);
			foreach($coderem as $file)
			{
				$name=str_replace("core/","",str_replace("-","/",$file));
				$debug=unlink("$name.php");
				if($debug === true)
				{
					$count[0]++;
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Removed file \"$name\"");
				}
				else
				{
					$count[1]++;
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to remove file \"$name\"");
				}
			}
			echo("DONE. Of " . array_sum($count) . " files, " . $count[0] . " were removed with " . $count[1] . " errors.\r\n");
		}
		else
		{
			echo("SKIPPED. Run a full upgrade to perform this step.\r\n");
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Skipping removal of obsolete code files");
		}
	}
  ?>
  </p>
  <hr>
  <h2>Updating Settings</h2>
  <p>
  <?php
	if($run === true)
	{
		echo("Calculating changes...");
		$count=array(0,0,0);
		$confadd=array();
		$confchg=array();
		$confrem=array();
		if(file_exists("upgrade/prepare/conf-add.txt"))
		{
			$confadd=array_filter(explode("\r\n",file_get_contents("upgrade/prepare/conf-add.txt")));
			$count[0]=count($confadd);
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Calculated number of settings to add");
		}
		if(file_exists("upgrade/prepare/conf-chg.txt"))
		{
			$confchg=array_filter(explode("\r\n",file_get_contents("upgrade/prepare/conf-chg.txt")));
			$count[1]=count($confchg);
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Calculated number of settings to change");
		}
		if(file_exists("upgrade/prepare/conf-rem.txt"))
		{
			$confrem=array_filter(explode("\r\n",file_get_contents("upgrade/prepare/conf-rem.txt")));
			$count[2]=count($confrem);
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Calculated number of settings to remove");
		}
		echo("DONE. " . $count[0] . " additions, " . $count[1] . " modifications and " . $count[2] . " removals found.<br>\r\n");
		echo("Processing additions...");
		$count=array(0,0);
		foreach($confadd as $setting)
		{
			$setting=explode("|",$setting);
			if(isset($setting[0]) && isset($setting[1]))
			{
				$fh=fopen("backend/" . $setting[0] . ".txt",'w');
				if($fh)
				{
					fwrite($fh,$setting[1]);
					fclose($fh);
					$count[0]++;
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Added setting \"" . $setting[0] . "\" with value \"" . $setting[1] . "\"");
				}
				else
				{
					$count[1]++;
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to add setting \"" . $setting[0] . "\"");
				}
			}
			else
			{
				$count[1]++;
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Malformed setting encountered");
			}
		}
		echo("DONE. Of " . array_sum($count) . " settings to add, " . $count[0] . " were added with " . $count[1] . " errors.<br>\r\n");
		echo("Processing modifications...");
		if($d == 1)
		{
			$count=array(0,0,0);
			foreach($confchg as $setting)
			{
				$setting=explode("|",$setting);
				if(isset($setting[0]) && isset($setting[1]) && isset($setting[2]))
				{
					if(file_exists("backend/" . $setting[0] . ".txt"))
					{
						$existing=file_get_contents("backend/" . $setting[0] . ".txt");
						if($existing != $setting[1])
						{
							$fh=fopen("backend/" . $setting[0] . ".txt",'w');
							if($fh)
							{
								fwrite($fh,$setting[2]);
								fclose($fh);
								$count[0]++;
								write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Changed setting \"" . $setting[0] . "\" from value \"" . $setting[1] . "\" to value \"" . $setting[2] . "\"");
							}
							else
							{
								$count[2]++;
								write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"" . $setting[0] . "\"");
							}
						}
						else
						{
							$count[1]++;
							write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Setting \"" . $setting[0] . "\" already at appropriate value, ignoring");
						}
					}
					else
					{
						$count[2]++;
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to read setting \"" . $setting[0] . "\"");
					}
				}
				else
				{
					$count[2]++;
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Malformed setting encountered");
				}
			}
			echo("DONE. Of " . array_sum($count) . " settings to change, " . $count[0] . " were changed and " . $count[1] . " were ignored with " . $count[2] . " errors.<br>\r\n");
		}
		else
		{
			echo("SKIPPED. Run a full upgrade to perform this step.<br>\r\n");
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Skipping modification of settings");
		}
		echo("Processing removals...");
		if($d == 1)
		{
			$count=array(0,0);
			foreach($confrem as $file)
			{
				$debug=unlink("backend/$file.txt");
				if($debug === true)
				{
					$count[0]++;
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Removed setting \"$file\"");
				}
				else
				{
					$count[1]++;
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to remove setting \"$file\"");
				}
			}
			echo("DONE. Of " . array_sum($count) . " settings, " . $count[0] . " were removed with " . $count[1] . " errors.\r\n");
		}
		else
		{
			echo("SKIPPED. Run a full upgrade to perform this step.\r\n");
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Skipping removal of obsolete settings");
		}
	}
  ?>
  </p>
  <hr>
  <h2>Running External Commands</h2>
  <p>
  <?php
	if($run === true)
	{
		echo("Loading list of updates being installed...");
		$updates=array();
		if(file_exists("upgrade/packages.txt"))
		{
			$allupdates=array_filter(explode("\r\n",file_get_contents("upgrade/packages.txt")));
			foreach($allupdates as $update)
			{
				$update=explode("|",$update);
				if(isset($update[0]) && isset($update[2]) && $update[2] == 2)
				{
					$updates[]=$update[0];
				}
			}
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Loaded list of installable updates");
			echo("DONE.<br>\r\n");
		}
		else
		{
			echo("FAILED. Proceeding anyway, expect problems.<br>\r\n");
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to load list of installable updates");
		}
		foreach($updates as $update)
		{
			echo("Loading processor script for upgrade pack \"$update\"...");
			if(file_exists("upgrade/prepare/externalchanges-$update.php"))
			{
				include("upgrade/prepare/externalchanges-$update.php");
				echo("DONE.<br>\r\n");
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Loaded processor script for upgrade pack \"$update\"");
			}
			else
			{
				echo("FAILED. Proceeding anyway, expect problems.<br>\r\n");
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to load processor script for upgrade pack \"$update\"");
			}
			echo("Executing processor function for upgrade pack \"$update\"...");
			$function="runprocessor_$update";
			if(function_exists($function))
			{
				$debug=$function();
				echo("DONE. Processor returned code $debug.<br>\r\n");
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Executed processor script for upgrade pack \"$update\"");
			}
			else
			{
				echo("SKIPPED. Nothing to be done.<br>\r\n");
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Processor script for upgrade pack \"$update\" contained nothing to run");
			}
		}
		echo("Completed external processing. Make sure no error codes appear above!\r\n");
	}
  ?>
  </p>
  <hr>
  <h2>Updating System Files</h2>
  <p>
  <?php
	if($run === true)
	{
		echo("Replacing version file...");
		if(file_exists("upgrade/prepare/version.txt"))
		{
			$debug=rename("upgrade/prepare/version.txt","backend/version.txt");
			if($debug === true)
			{
				echo("DONE.<br>\r\n");
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Updated version information");
			}
			else
			{
				echo("FAILED. Proceeding anyway, expect problems.<br>\r\n");
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to update version information: cannot save file");
			}
		}
		else
		{
			echo("FAILED. Proceeding anyway, expect problems.<br>\r\n");
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to update version information: cannot read file");		
		}
		echo("Replacing system background...");
		if($d == 1)
		{
			if(file_exists("upgrade/prepare/background.gif"))
			{
				$debug=rename("upgrade/prepare/background.gif","backend/background.gif");
				if($debug === true)
				{
					echo("DONE.<br>\r\n");
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Updated system background");
				}
				else
				{
					echo("FAILED. Proceeding anyway, expect problems.<br>\r\n");
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to update system background: cannot save file");
				}
			}
			else
			{
				echo("FAILED. Proceeding anyway, expect problems.<br>\r\n");
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to update system background: cannot read file");		
			}
		}
		else
		{
			echo("SKIPPED. Run a full upgrade to perform this step.<br>\r\n");
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Skipped updating system background");		
		}
		echo("Replacing system icon...");
		if($d == 1)
		{
			if(file_exists("upgrade/prepare/favicon.ico"))
			{
				$debug=rename("upgrade/prepare/favicon.ico","backend/favicon.ico");
				if($debug === true)
				{
					echo("DONE.<br>\r\n");
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Updated system icon");
				}
				else
				{
					echo("FAILED. Proceeding anyway, expect problems.\r\n");
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to update system icon: cannot save file");
				}
			}
			else
			{
				echo("FAILED. Proceeding anyway, expect problems.\r\n");
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to update system icon: cannot read file");		
			}
		}
		else
		{
			echo("SKIPPED. Run a full upgrade to perform this step.\r\n");
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Skipped updating system icon");		
		}
	}
  ?>
  </p>
  <hr>
  <h2>Cleaning Up</h2>
  <p>
  <?php
	if($run === true)
	{
		echo("Removing leftover files...");
		$files=array_merge(glob("upgrade/prepare/*.php"),glob("upgrade/prepare/*.txt"),glob("upgrade/prepare/*.gif"),glob("upgrade/prepare/*.ico"));
		$count=array(0,0);
		foreach($files as $file)
		{
			$debug=unlink($file);
			if($debug === true)
			{
				$count[0]++;
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Removed file \"$file\"");
			}
			else
			{
				$count[1]++;
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to remove file \"$file\"");
			}
		}
		echo("DONE. Of " . array_sum($count) . " files, " . $count[0] . " were removed with " . $count[1] . " errors.<br>\r\n");
		echo("Removing upgrade prepare flag...");
		$debug=unlink("upgrade/prepare-complete.txt");
		if($debug === true)
		{
			echo("DONE.<br>\r\n");
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Removed file \"upgrade/prepare-complete.txt\"");
		}
		else
		{
			echo("FAILED. Proceeding anyway, expect problems.<br>\r\n");
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to remove file \"upgrade/prepare-complete.txt\"");
		}
		foreach($updates as $update)
		{
			echo("Removing files in update directory \"$update\"...");
			$files=glob("upgrade/$update/*.{php,txt,gif,ico}",GLOB_BRACE);
			$count=array(0,0);
			foreach($files as $file)
			{
				$debug=unlink($file);
				if($debug === true)
				{
					$count[0]++;
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Removed file \"$file\"");
				}
				else
				{
					$count[1]++;
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to remove file \"$file\"");
				}
			}
			echo("DONE. Of " . array_sum($count) . " files, " . $count[0] . " were removed with " . $count[1] . " errors.<br>\r\n");
			echo("Removing directory for upgrade pack \"$update\"...");
			$debug=rmdir("upgrade/$update");
			if($debug === true)
			{
				echo("DONE.<br>\r\n");
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Removed directory \"$update\"");
			}
			else
			{
				echo("FAILED. Proceeding anyway, expect problems.<br>\r\n");
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to remove directory \"$update\"");
			}
			echo("Removing upgrade pack \"$update\"...");
			$debug=unlink("upgrade/$update.zip");
			if($debug === true)
			{
				echo("DONE.<br>\r\n");
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Removed file \"upgrade/$update.zip\"");
			}
			else
			{
				echo("FAILED. Proceeding anyway, expect problems.<br>\r\n");
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to remove file \"upgrade/$update.zip\"");
			}
		}
		echo("Marking upgrades performed...");
		if(file_exists("upgrade/packages.txt"))
		{
			$upgpacks=array_filter(explode("\r\n",file_get_contents("upgrade/packages.txt")));
			for($i=0;$i<count($upgpacks);$i++)
			{
				$upgpacks[$i]=explode("|",$upgpacks[$i]);
				if(count($upgpacks[$i]) == 3 && in_array($upgpacks[$i][0],$updates))
				{
					$upgpacks[$i][2]=3;
				}
				$upgpacks[$i]=implode("|",$upgpacks[$i]);
			}
			$upgpacks=implode("\r\n",$upgpacks);
			$fh=fopen("upgrade/packages.txt",'w');
			if($fh)
			{
				fwrite($fh,$upgpacks);
				fclose($fh);
				echo("DONE.<br>\r\n");
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Updated list of available upgrades");
			}
			else
			{
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to update list of available upgrades");
				echo("FAILED. Proceeding anyway, expect problems.<br>\r\n");
			}
		}
		else
		{
			echo("FAILED. Proceeding anyway, expect problems.\r\n");
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to update list of installed updates");
		}
		echo("Finalizing upgrade...");
		$fh=fopen("upgrade/lastinst.txt",'w');
		if($fh)
		{
			fwrite($fh,time());
			fclose($fh);
			echo("DONE.\r\n");
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Updated last install time");
		}
		else
		{
			echo("FAILED. Proceeding anyway, expect problems.\r\n");
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Updated last install time");
		}
	}
  ?>
  </p>
  <hr>
  <p>Update process completed. Check above for any errors before proceeding.</p>
  <p><a href="index.php">Finish</a></p></div>
  </body>
</html>