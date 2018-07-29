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
		error_reporting(E_ALL & ~E_NOTICE & ~E_USER_NOTICE);
		break;
		case 2:
		error_reporting(E_ALL);
		break;
		case 1:
		default:
		error_reporting(E_ALL & ~E_NOTICE);
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
	<link rel="shortcut icon" href="backend/favicon.ico">
    <title><?php echo $sysname; ?>Music Request System-Purge Session Information</title>
    
    <style type="text/css">
    <!--
    body {
      color:#000000;
	  background-color:#FFFFFF;
      background-image:url('backend/background.gif');
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
  <h1 style="text-align:center; text-decoration:underline;"><?php echo $sysname; ?>MRS-Purge Session Information</h1>
  <?php
	if(is_logging_enabled() === true)
	{
		//Change the timezone
		set_timezone();
		//Logging enabled
		if(securitycheck() === true && isset($_GET['s']) && $_GET['s'] == "y" && get_system_setting("altsesstore") == "yes" && get_system_setting("altsesstorepath") != "")
		{
			//Delete sessions
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Began deleting sessions");
			$files=glob(get_system_setting("altsesstorepath") . "/*");
			foreach($files as $file)
			{
				echo ("Deleting file \"$file\"...");
				$debug=unlink($file);
				if($debug === true)
				{
					//Success
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Successfully deleted session \"$file\"");
					echo ("DONE.<br>\r\n");
				}
				else
				{
					//Failure
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to delete session \"$file\"");
					echo ("FAILED.<br>\r\n");
				}
			}
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Finished deleted all sessions");
			echo("Process finished. Check above for any errors, and microwave the appropriate file or the containing folder.<br>\r\n<a href=\"index.php\">Finish</a>\r\n");
		}
		else
		{
			//Just show page
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Viewed purge sessions page");
			if(securitycheck() === false)
			{
				die("You are not an administrator. <a href=\"login.php?ref=purgesess\">Sign in</a> or <a href=\"index.php\">Cancel</a>.");
			}
			echo ("<p>This will delete EVERY session on the system. Note that this should be done automatically, but may be required if it is not happening automatically. Also note that you may only do this if you have set an alternate session store!<br>Please be DEFINITELY sure this is what you want to do!</p>\r\n");
			if(get_system_setting("altsesstore") == "yes" && get_system_setting("altsesstorepath") != "")
			{
				echo ("<a href=\"purgesess.php?s=y\">Purge sessions</a> or <a href=\"admin.php\">Cancel</a>");
			}
			else
			{
				echo ("You may not use this functionality. The system is not set up for an alternate session store. <a href=\"admin.php\">Cancel</a>");
			}
		}
	}
	else
	{
		//Logging disabled
		if(securitycheck() === true && isset($_GET['s']) && $_GET['s'] == "y" && get_system_setting("altsesstore") == "yes" && get_system_setting("altsesstorepath") != "")
		{
			//Delete sessions
			$files=glob(get_system_setting("altsesstorepath") . "/*");
			foreach($files as $file)
			{
				echo ("Deleting file \"$file\"...");
				$debug=unlink($file);
				if($debug === true)
				{
					//Success
					echo ("DONE.<br>\r\n");
				}
				else
				{
					//Failure
					echo ("FAILED.<br>\r\n");
				}
			}
			echo("Process finished. Check above for any errors, and microwave the appropriate file or the containing folder.<br>\r\n<a href=\"index.php\">Finish</a>\r\n");
		}
		else
		{
			//Just show page
			if(securitycheck() === false)
			{
				die("You are not an administrator. <a href=\"login.php?ref=purgesess\">Sign in</a> or <a href=\"index.php\">Cancel</a>.");
			}
			echo ("<p>This will delete EVERY session on the system. Note that this should be done automatically, but may be required if it is not happening automatically. Also note that you may only do this if you have set an alternate session store!<br>Please be DEFINITELY sure this is what you want to do!</p>\r\n");
			if(get_system_setting("altsesstore") == "yes" && get_system_setting("altsesstorepath") != "")
			{
				echo ("<a href=\"purgesess.php?s=y\">Purge sessions</a> or <a href=\"admin.php\">Cancel</a>");
			}
			else
			{
				echo ("You may not use this functionality. The system is not set up for an alternate session store. <a href=\"admin.php\">Cancel</a>");
			}
		}
	}
?>
  </body>
</html>