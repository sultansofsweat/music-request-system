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
    <title><?php echo $sysname; ?>Music Request System-Logout</title>
    
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
<?php
	$disabled=false;
	if(isset($_POST['s']) && $_POST['s'] == "y")
	{
		//Begin submission
		if(is_logging_enabled() === true)
		{
			//Change the timezone
			set_timezone();
			//Destroy admin flag and unique identifier
			unset($_SESSION['sradmin']);
			unset($_SESSION['identifier']);
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Exited admin mode");
			//Get out of here
			echo("<p><b>You have been logged out successfully.</b> Click <a href=\"index.php\">here</a> to continue.</p>\r\n");
			$disabled=true;
		}
		else
		{
			//Destroy admin flag and unique identifier
			unset($_SESSION['sradmin']);
			unset($_SESSION['identifier']);
			echo("<p><b>You have been logged out successfully.</b> Click <a href=\"index.php\">here</a> to continue.</p>\r\n");
			$disabled=true;
		}
	}
	else
	{
		if(is_logging_enabled() === true)
		{
			//Change the timezone
			set_timezone();
			//Logging enabled on system
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Visited admin logout page");
		}
		if(securitycheck() === false)
		{
			//User is not logged in
			echo ("<p>You are not logged in! What are you doing here?</p>\r\n");
			$disabled=true;
		}
	}
?>
  <h1 style="text-align:center; text-decoration:underline;"><?php echo $sysname; ?>MRS-Exit Administrative Mode</h1>
  <?php if(isset($disabled) && $disabled === true) { echo("<!--\r\n"); } ?>
  <form method="post" action="logout.php">
  <input type="hidden" name="s" value="y">
  Are you sure you wish to log out?<br>
  <input type="submit" value="Yes"><input type="button" value="No" onclick="window.location.href='index.php'">
  </form>
  <?php if(isset($disabled) && $disabled === true) { echo("\r\n-->\r\n"); } ?>
  </body>
</html>