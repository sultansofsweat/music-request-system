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
    <title><?php echo $sysname; ?>Music Request System-Archive Posts</title>
    
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
  <h1 style="text-align:center; text-decoration:underline;"><?php echo $sysname; ?>MRS-Archive Posts</h1>
<?php
	if(is_logging_enabled() === true)
	{
		//Change the timezone
		date_default_timezone_set(get_system_setting("timezone"));
		//Logging enabled
		if(isset($_GET['s']) && $_GET['s'] == "y")
		{
			//Archive posts
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Began archiving posts");
			$files=get_all_req_ids();
			foreach($files as $file)
			{
				echo("Saving file \"$file\" to \"archive/$file.txt\"...");
				$debug=write_archive($file);
				if($debug === true)
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Successfully wrote archive file \"archive/$file.txt\"");
					echo ("DONE.<br>\r\n");
				}
				else
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to write archive file \"" . $newfile . "\"");
					echo ("FAILED. Microwave the source file or the destination folder.<br>\r\n");
				}
			}
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Finished writing all archive files");
			echo("Completed process. Check above for errors.<br>\r\n<a href=\"admin.php\">Finish</a>\r\n");
		}
		else
		{
			//Just show page
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Viewed archive page");
			if(securitycheck() === false)
			{
				die("You are not an administrator. <a href=\"login.php?ref=archive\">Sign in</a> or <a href=\"index.php\">Cancel</a>.");
			}
			echo ("<p>Post archival will unencrypt all of the current posts into an identically named file in the archive subdirectory in the system. It will not delete any existing files.</p>\r\n<a href=\"archive.php?s=y\">Archive posts</a> or <a href=\"admin.php\">Cancel</a>");
		}
	}
	else
	{
		//Logging disabled
		if(isset($_GET['s']) && $_GET['s'] == "y")
		{
			//Archive posts
			$files=get_all_req_ids();
			foreach($files as $file)
			{
				echo("Saving file \"$file\" to \"archive/$file.txt\"...");
				$debug=write_archive($file);
				if($debug === true)
				{
					echo ("DONE.<br>\r\n");
				}
				else
				{
					echo ("FAILED. Microwave the source file or the destination folder.<br>\r\n");
				}
			}
			echo("Completed process. Check above for errors.<br>\r\n<a href=\"admin.php\">Finish</a>\r\n");
		}
		else
		{
			//Just show page
			if(securitycheck() === false)
			{
				die("You are not an administrator. <a href=\"login.php?ref=archive\">Sign in</a> or <a href=\"index.php\">Cancel</a>.");
			}
			echo ("<p>Post archival will unencrypt all of the current posts into an identically named file in the archive subdirectory in the system. It will not delete any existing files.</p>\r\n<a href=\"archive.php?s=y\">Archive posts</a> or <a href=\"admin.php\">Cancel</a>");
		}
	}
?>
  </body>
</html>