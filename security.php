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
    <title><?php echo $sysname; ?>Music Request System-Change Password</title>
    
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
		set_timezone();
		if(securitycheck() === true && isset($_POST['s']) && $_POST['s'] == "y" && isset($_POST['pass']) && password_verify($_POST['pass'],get_system_password()) === true)
		{
			//User began submission
			if(isset($_POST['level']) && $_POST['level'] != "")
			{
				$level=preg_replace("/[^0-9]/","",$_POST['level']);
				if($level == "")
				{
					$level=7;
				}
			}
			else
			{
				$level=7;
			}
			if(isset($_POST['timeout']) && $_POST['timeout'] != "")
			{
				$timeout=preg_replace("/[^0-9]/","",$_POST['timeout']);
				if($timeout == "")
				{
					$timeout=20;
				}
			}
			else
			{
				$timeout=20;
			}
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Started submission");
			
			$debug=save_system_setting("security",$level);
			if($debug === false)
			{
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to save security level of $level");
				$error=true;
			}
			else
			{
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Successfully saved security level of $level");
			}
			$debug=save_system_setting("timeout",$timeout);
			if($debug === false)
			{
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to save timeout time of $timeout minutes");
				$error=true;
			}
			else
			{
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Successfully saved timeout time of $timeout minutes");
			}
			if(isset($error) && $error === true)
			{
				trigger_error("Not all system settings were saved. Please check the output and try again.");
			}
			else
			{
				if(isset($_SESSION['sradmin']))
				{
					$_SESSION['sradmin']="n";
				}
				if(isset($_SESSION['identifier']))
				{
					$_SESSION['identifier']="";
				}
				$debug=session_destroy();
				if($debug === true)
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Destroyed session");
				}
				else
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Did not destroy session");
				}
				echo ("<script type=\"text/javascript\">window.location = \"index.php?sc=yes\"</script>");
			}
		}
		else
		{
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Visited security page");
			if(isset($_POST['s']) && $_POST['s'] == "y")
			{
				//User supplied blank or invalid password
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"User supplied invalid password");
				trigger_error("You entered a blank or invalid password, you goat!",E_USER_WARNING);
			}
			if(securitycheck() === false)
			{
				die("You are not an administrator. <a href=\"login.php?ref=security\">Sign in</a> or <a href=\"index.php\">Cancel</a>.");
			}
			$level=get_system_setting("security");
			$timeout=get_system_setting("timeout");
		}
	}
	else
	{
		//Logging disabled
		if(securitycheck() === true && isset($_POST['s']) && $_POST['s'] == "y" && isset($_POST['pass']) && password_verify($_POST['pass'],get_system_password()) === true)
		{
			//User began submission
			if(isset($_POST['level']) && $_POST['level'] != "")
			{
				$level=preg_replace("/[^0-9]/","",$_POST['level']);
				if($level == "")
				{
					$level=7;
				}
			}
			else
			{
				$level=7;
			}
			if(isset($_POST['timeout']) && $_POST['timeout'] != "")
			{
				$timeout=preg_replace("/[^0-9]/","",$_POST['timeout']);
				if($timeout == "")
				{
					$timeout=20;
				}
			}
			else
			{
				$timeout=20;
			}
			
			$debug=save_system_setting("security",$level);
			if($debug === false)
			{
				$error=true;
			}
			$debug=save_system_setting("timeout",$timeout);
			if($debug === false)
			{
				$error=true;
			}
			if(isset($error) && $error === true)
			{
				trigger_error("Not all system settings were saved. Please check the output and try again.");
			}
			else
			{
				if(isset($_SESSION['sradmin']))
				{
					$_SESSION['sradmin']="n";
				}
				if(isset($_SESSION['identifier']))
				{
					$_SESSION['identifier']="";
				}
				session_destroy();
				echo ("<script type=\"text/javascript\">window.location = \"index.php?sc=yes\"</script>");
			}
		}
		else
		{
			if(isset($_POST['s']) && $_POST['s'] == "y")
			{
				//User supplied blank or invalid password
				trigger_error("You entered a blank or invalid password, you goat!",E_USER_WARNING);
			}
			if(securitycheck() === false)
			{
				die("You are not an administrator. <a href=\"login.php?ref=security\">Sign in</a> or <a href=\"index.php\">Cancel</a>.");
			}
			$level=get_system_setting("security");
			$timeout=get_system_setting("timeout");
		}
	}
  ?>
  <body>
  <h1 style="text-align:center; text-decoration:underline;"><?php echo $sysname; ?>Music Request System-Change Security Level</h1>
  <p>There are seven different security levels. As part of its authentication mechanism, the MRS can store any combination of the following:</p>
  <ul>
  <li>The user's IP address</li>
  <li>The user agent string of the user's browser</li>
  <li>A unique "user identifier".</li>
  </ul>
  <p>in addition to a "switch" that tells the system you have logged in.<br>
  The system also features a "timeout" function, which automatically expires sessions after a certain period of inactivity, controllable below.</p>
  <p>Below is a chart of the various security levels, and what they mean.</p>
  <table width="60%" border="1px solid #000000">
  <tr>
  <th>Level</th>
  <th>IP Address</th>
  <th>User Agent</th>
  <th>Identifier</th>
  </tr>
  <tr>
  <th>0</th>
  <th>No</th>
  <th>No</th>
  <th>No</th>
  </tr>
  <tr>
  <th>1</th>
  <th>Yes</th>
  <th>No</th>
  <th>No</th>
  </tr>
  <tr>
  <th>2</th>
  <th>No</th>
  <th>Yes</th>
  <th>No</th>
  </tr>
  <tr>
  <th>3</th>
  <th>Yes</th>
  <th>Yes</th>
  <th>No</th>
  </tr>
  <tr>
  <th>4</th>
  <th>No</th>
  <th>No</th>
  <th>Yes</th>
  </tr>
  <tr>
  <th>5</th>
  <th>Yes</th>
  <th>No</th>
  <th>Yes</th>
  </tr>
  <tr>
  <th>6</th>
  <th>No</th>
  <th>Yes</th>
  <th>Yes</th>
  </tr>
  <tr>
  <th>7</th>
  <th>Yes</th>
  <th>Yes</th>
  <th>Yes</th>
  </tr>
  </table>
  <form method="post" action="security.php">
  <input type="hidden" name="s" value="y">
  Security level: <select name="level">
  <option value="">Select one</option>
  <option value="0" <?php if(isset($level) && $level == 0) {echo("selected=\"selected\"");} ?>>0</option>
  <option value="1" <?php if(isset($level) && $level == 1) {echo("selected=\"selected\"");} ?>>1</option>
  <option value="2" <?php if(isset($level) && $level == 2) {echo("selected=\"selected\"");} ?>>2</option>
  <option value="3" <?php if(isset($level) && $level == 3) {echo("selected=\"selected\"");} ?>>3</option>
  <option value="4" <?php if(isset($level) && $level == 4) {echo("selected=\"selected\"");} ?>>4</option>
  <option value="5" <?php if(isset($level) && $level == 5) {echo("selected=\"selected\"");} ?>>5</option>
  <option value="6" <?php if(isset($level) && $level == 6) {echo("selected=\"selected\"");} ?>>6</option>
  <option value="7" <?php if(isset($level) && $level == 7) {echo("selected=\"selected\"");} ?>>7</option>
  </select><br>
  Timeout: <select name="timeout">
  <option value="">Select one</option>
  <option value="5" <?php if(isset($timeout) && $timeout == 5) {echo("selected=\"selected\"");} ?>>5</option>
  <option value="10" <?php if(isset($timeout) && $timeout == 10) {echo("selected=\"selected\"");} ?>>10</option>
  <option value="20" <?php if(isset($timeout) && $timeout == 20) {echo("selected=\"selected\"");} ?>>20</option>
  <option value="25" <?php if(isset($timeout) && $timeout == 25) {echo("selected=\"selected\"");} ?>>25</option>
  <option value="30" <?php if(isset($timeout) && $timeout == 30) {echo("selected=\"selected\"");} ?>>30</option>
  <option value="45" <?php if(isset($timeout) && $timeout == 45) {echo("selected=\"selected\"");} ?>>45</option>
  <option value="60" <?php if(isset($timeout) && $timeout == 60) {echo("selected=\"selected\"");} ?>>60</option>
  <option value="0" <?php if(isset($timeout) && $timeout == 0) {echo("selected=\"selected\"");} ?>>Indefinite</option>
  </select> minutes<br>
  Re-enter password: <input type="password" name="pass" required="required"><br>
  <input type="submit" value="Change"> or <input type="button" value="Cancel" onclick="window.location.href='admin.php'">
  </form>
  </body>
</html>