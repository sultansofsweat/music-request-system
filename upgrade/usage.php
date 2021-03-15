<?php
	//Set the system error handler
	if(file_exists("../backend/errorhandler.php"))
	{
		include("../backend/errorhandler.php");
	}
	else
	{
		trigger_error("Failed to invoke system error handler. Expect information leakage.",E_USER_WARNING);
	}
	//Include useful functions page, if it exists
	if(file_exists("../backend/functions.php"))
	{
		include("../backend/functions.php");
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
	if(file_exists("../backend/securitycheck.php"))
	{
		include ("../backend/securitycheck.php");
	}
	else
	{
		die("Failed to open file \"../backend/securitycheck.php\" in read mode. It should now be microwaved.");
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
    <title><?php echo $sysname; ?>Music Request System-Upgrade Script Usage</title>
    
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
	write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Viewed upgrader usage page");
	//Run security check
	if(securitycheck() === false)
	{
		//No admin privileges, no page viewing privileges
		die("You are not an administrator. <a href=\"../login.php?ref=admin\">Sign in</a> or <a href=\"../index.php\">Cancel</a>.");
	}
	if(isset($_GET['s']) && $_GET['s'] == "y")
	{
		unlink("firstuse.txt");
		echo("<script type=\"text/javascript\">window.location = \"index.php\"</script>");
	}
  ?>
  <body>
  <h1 style="text-align:center; text-decoration:underline;"><?php echo $sysname; ?>Music Request System-Using The Upgrader</h1>
  <h2>About The Upgrader</h2>
  <p>This release of the MRS (2.4) changes the way upgrade packs work, and as a result it uses revamped upgrade scripts that simplify some aspects of installation but require more steps to use.<br>
  Notably, the entire process of downloading and installing upgrade packs is automated and intervention isn't possible other than by use of the "sideloader" feature which will be explained later.</p>
  <p>This page attempts to document what you should do before using any of the upgrader components, each of the steps involved, how to "sideload" your own upgrade packs, and what to do after an upgrade.<br>
  <b>NOTE:</b> this page will NOT document how to create your own upgrade pack, just how to sideload one you have. Creating upgrade packs is not for the faint of heart and should only be attempted if you're willing to go the "extra mile" to figure out how to do it yourself.
  <h2>Before Upgrading</h2>
  <p><b>Take a backup.</b> No one wants data loss. While the upgrade scripts take backups of their own, they should not be trusted. And besides, a good backup is something you should have anyways in the event of other catastrophic failures.</p>
  <p>The new upgrader takes away pretty much all of the "before" steps. You should, however, read this page very carefully before assuming you know what the upgrader is doing. You may also want to check out the <a href="http://firealarms.mooo.com/mrs/#changelog">changelog</a> for information about upgrades.</p>
  <p>Before even thinking about upgrading, you need to make sure that the MRS has <b>write permissions</b> to the folder it is stored in. It's not likely this is a problem as most of the MRS won't work unless you have this, but if you're at all unsure (or if you don't know how to change this), <a href="http://firealarms.mooo.com/mrs/#contact">contact the software vendor</a></p>
  <hr>
  <h2>Steps To Upgrading</h2>
  <h3>Checking For Upgrades</h3>
  <p>This is the first step, and should be executed before performing any upgrades, even if you have previously checked for updates. This process is not automatic as it was in previous MRS releases.<br>
  Click on the "check for updates" link on the main page. This will download the latest list of upgrade packages. Upgrade packages that have been revoked will be removed from the list.</p>
  <h3>Downloading Upgrade Packs</h3>
  <p>The difference with previous MRS releases is that each individual upgrade pack that has been released since your last upgrade can be downloaded, rather than just the latest release, allowing the end user to pick and choose which upgrades to install. It is, of course, recommended to install them all unless directed not to do so.<br>
  For each update that requires downloading, there will be a "download" link to click. Once it is downloaded, there will be a link to a bundled "changelog" file for each update that shows what is added, modified and removed for each upgrade pack. You also have the option to redownload each upgrade pack.</p>
  <h3>Preparing Final Upgrade Pack</h3>
  <p>This step will combine all downloaded upgrade packs into a single upgrade pack, oldest to newest.<b>If nothing is downloaded, nothing is prepared.</b><br>
  Click the "prepare upgrade" link to run the preparation script. Other than making note of errors and correcting them (if possible), this is a one-click process that needs no intervention.</p>
  <h3>Install Upgrades</h3>
  <p>This is the last step, which will install the prepared "final" upgrade pack into the running system. <b>If a successful preparation run didn't take place, nothing will install.</b>. This process also automatically closes the MRS, so if it was open beforehand it will need to be reopened.<br>
  Similar to the prepare step, this step is a one-click process that needs no intervention other than the noting and correction of errors. It is, however, the only verbose portion of the process, allowing you to take note of what the upgrade has changed so that way if there are problems, they can be easily reversed.</p>
  <p>This step takes a backup of all files and all settings, which are stored in the "backup" subfolder. This can be cleaned with the "cleanup" script.</p>
  <hr>
  <h2>What To Do In The Event of Problems</h2>
  <p><u>Don't panic</u>. <a href="http://firealarms.mooo.com/mrs/#report">Report</a> errors to the software vendor.<br>
  The system should have taken a backup during the upgrade process, unless the backup did not succeed. You also took your own backup before proceeding with an upgrade...right?<br>
  There is a script that automates recovery in the event of a problem; click on the "restore to last backup" link on the main upgrade page.</p>
  <p>That script may fail to run however, and there may be a case where you only want to recover some parts of the previous MRS install. In these cases, you'll have to recover manually.<br>
  There are three types of files: "code" files (which have ".php" extensions), "settings" files (anything with ".txt"), and three "system" files: "background.gif" (the system background), "favicon.ico" (the system icon), and "version.txt" (the version information file).<br>
  The filenames of code files consist of two parts: the folder it is stored in (with "core" being the top level of the MRS, i.e. the root), followed by the filename.<br>
  All three system files, as well as the settings files, go into the "backend" subfolder, overwriting any and all contents. Code files need to be renamed and moved the the appropriate folder, following the above instructions</p>
  <p>If there are any questions about the recovery process, <a href="http://firealarms.mooo.com/mrs/#contact">contact the software vendor</a>.</p>
  <hr>
  <h2>Sideloading Upgrade Packs</h2>
  <p>Sideloading your own upgrade packs comes with risks and isn't for the faint of heart. Another reason for sideloading is an inability to download upgrade packs automatically for whatever reason.<br>
  MRS releases can be downloaded <a href="http://firealarms.mooo.com/mrs/#downloading">here</a> or via a <a href="http://firealarms.mooo.com/mrs/#mirrors">trusted mirror</a>. Beginning with MRS 2.4, MD5 hashing is taken care of automatically and you don't need to do anything.<br>
  <b>Make sure you are downloading an <u>upgrade</u> pack!</b> Install packs are <b><u>not</u></b> supported by the updater!<br>
  All you need to do is click "sideload" on the main page. It will have you upload the ZIP archive you ended up with, perform some basic tasks, and add it to the list of downloaded updates. You must then begin an upgrade at the "prepare" step.</p>
  <p><b><u>IMPORTANT:</u></b> downloading updates automatically ensures that the resulting file isn't corrupt in any way. Sideloading assumes that whatever file you submit isn't made of canned yams and won't just microwave the solar system or otherwise cause problems. Hence <b>you are using it at your own risk.</b></p>
  <hr>
  <p>That is all you need to know. Further questions should be directed to the <a href="http://firealarms.mooo.com/mrs/#contact">contact the software vendor</a>.</p>
  <p><a href="usage.php?s=y">I understand all this stuff</a> or just <a href="index.php">go back</a>.</p>
  </body>
</html>