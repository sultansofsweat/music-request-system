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
    <meta name="description" content="Listening to a live show? Got a song you have to hear? This is the place to request it!">
	<link rel="shortcut icon" href="backend/favicon.ico">
    <title><?php echo $sysname; ?>Music Request System-Change Automation Settings</title>
    
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
<?php
	if(is_logging_enabled() === true)
	{
		//Logging enabled
		//Change timezone
		set_timezone();
		if(isset($_POST['s']) && $_POST['s'] == "y" && isset($_POST['sysid']) && ($sysid=preg_replace("/[^0-9]/","",$_POST['sysid'])) != "" && isset($_POST['key']) && $_POST['key'] != "" && isset($_POST['ckey']) && $_POST['ckey'] != "" && $_POST['key'] == $_POST['ckey'] && securitycheck() === true)
		{
			//Began submission
			if(isset($_POST['enable']) && $_POST['enable'] == "yes")
			{
				$enable="yes";
			}
			else
			{
				$enable="no";
			}
			//Hash key
			$key=password_hash($_POST['key'],PASSWORD_DEFAULT);
			//$key=hash("whirlpool",$_POST['key']);
			//Set new settings
			$error=false;
			$debug=save_system_setting("interface",$enable);
			if($debug === false)
			{
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"interface\"");
				$error=true;
			}
			$debug=save_system_setting("sysid",$sysid);
			if($debug === false)
			{
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"sysid\"");
				$error=true;
			}
			$debug=save_system_setting("autokey",$key);
			if($debug === false)
			{
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"autokey\"");
				$error=true;
			}
			//Exit
			if($error === false)
			{
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Successfully changed automation settings");
				echo("<script type=\"text/javascript\">window.location = \"admin.php?autoset=yes\"</script>");
			}
			else
			{
				echo("<script type=\"text/javascript\">window.location = \"admin.php?autoset=no\"</script>");
			}
		}
		elseif(securitycheck() === true)
		{
			if(isset($_POST['s']) && $_POST['s'] == "y")
			{
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Blank key or ID supplied");
				trigger_error("You must supply an ID and password you screwball!",E_USER_WARNING);
			}
			else
			{
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Visited automation settings page");
			}
			$enable=get_system_setting("interface");
			if(($sysid=get_system_setting("sysid")) == "")
			{
				$sysid=rand();
			}
		}
		else
		{
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Visited automation settings page");
			die("You are not an administrator. <a href=\"login.php?ref=automated\">Sign in</a> or <a href=\"index.php\">Cancel</a>.");
		}
	}
	else
	{
		//Logging disabled
		if(isset($_POST['s']) && $_POST['s'] == "y" && isset($_POST['sysid']) && ($sysid=preg_replace("/[^0-9]/","",$_POST['sysid'])) != "" && isset($_POST['key']) && $_POST['key'] != "" && isset($_POST['ckey']) && $_POST['ckey'] != "" && $_POST['key'] == $_POST['ckey'] && securitycheck() === true)
		{
			//Began submission
			if(isset($_POST['enable']) && $_POST['enable'] == "yes")
			{
				$enable="yes";
			}
			else
			{
				$enable="no";
			}
			//Hash key
			$key=password_hash($_POST['key'],PASSWORD_DEFAULT);
			//$key=hash("whirlpool",$_POST['key']);
			//Set new settings
			$error=false;
			$debug=save_system_setting("interface",$enable);
			if($debug === false)
			{
				$error=true;
			}
			$debug=save_system_setting("sysid",$sysid);
			if($debug === false)
			{
				$error=true;
			}
			$debug=save_system_setting("autokey",$key);
			if($debug === false)
			{
				$error=true;
			}
			//Exit
			if($error === false)
			{
				echo("<script type=\"text/javascript\">window.location = \"admin.php?autoset=yes\"</script>");
			}
			else
			{
				echo("<script type=\"text/javascript\">window.location = \"admin.php?autoset=no\"</script>");
			}
		}
		elseif(securitycheck() === true)
		{
			if(isset($_POST['s']) && $_POST['s'] == "y")
			{
				trigger_error("You must supply an ID and password you screwball!",E_USER_WARNING);
			}
			$enable=get_system_setting("interface");
			if(($sysid=get_system_setting("sysid")) == "")
			{
				$sysid=rand();
			}
		}
		else
		{
			die("You are not an administrator. <a href=\"login.php?ref=automated\">Sign in</a> or <a href=\"index.php\">Cancel</a>.");
		}
	}
?>
  <body>
  <h1 style="text-align:center; text-decoration:underline;"><?php echo $sysname; ?>Music Request System-Change Automation Settings</h1>
  <p>The system API will allow you to do the following:</p>
  <ul>
  <li>Query system status</li>
  <li>Change request states</li>
  <li>Open/close the system</li>
  </ul>
  <form method="post" action="automated.php">
  <input type="hidden" name="s" value="y">
  Enable system API: <input type="radio" name="enable" value="yes" <?php if($enable == "yes") { echo ("checked=\"checked\""); } ?>>Yes | <input type="radio" name="enable" value="no" <?php if($enable == "no") { echo ("checked=\"checked\""); } ?>>No<br>
  API ID: <input type="text" name="sysid" <?php if(isset($sysid)) { echo("value=\"$sysid\""); } ?>> (numbers only!)<br>
  API key: <input type="password" name="key" required="required"> (DO NOT USE THE ADMIN PASSWORD!)<br>
  Confirm key: <input type="password" name="ckey" required="required"><br>
  <input type="submit" value="Change"> or <input type="button" value="Cancel" onclick="window.location.href='admin.php'">
  </form>
  </body>
</html>