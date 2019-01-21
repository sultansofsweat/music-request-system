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
    <title><?php echo $sysname; ?>Music Request System-Edit Copyright Information</title>
    
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
	 /*if(isset($_POST['s']) && $_POST['s'] == "y" && securitycheck() === true)
	 {
		 //Start submission
		 if(is_logging_enabled() === true)
		{
			//Change the timezone
			set_timezone();
			 //Make sure password file exists
			 if(file_exists("backend/password.txt"))
			 {
				 //Safe to proceed, verify the existing password and that the new passwords match
				 if(password_verify($_POST['old'],base64_decode(file_get_contents("backend/password.txt"))) === true && $_POST['new'] == $_POST['confirm'])
				 {
					//Everything is good, hash the password, write it, and get out of here
					$hash=base64_encode(password_hash($_POST['new'],PASSWORD_DEFAULT));
					$fh2=fopen("backend/password.txt",'w') or die("Failed to open file \"backend/password.txt\" in write mode. It should now be microwaved.");
					fwrite($fh2,$hash);
					fclose($fh2);
					//Destroy the "first use" flag file if it exists (no one cares if this fails, it'll just result in the system always saying the password needs to be changed)
					if(file_exists("backend/firstuse.txt"))
					{
						unlink("backend/firstuse.txt");
					}
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Changed password successfully");
					echo ("<script type=\"text/javascript\">window.location = \"admin.php?pchange=0\"</script>");
				 }
				 elseif(password_verify($_POST['old'],base64_decode(file_get_contents("backend/password.txt"))) !== true)
				 {
					//Old password given is not correct
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change password; old password incorrect");
					echo ("<script type=\"text/javascript\">window.location = \"admin.php?pchange=1\"</script>");
				 }
				 elseif($_POST['new'] != $_POST['confirm'])
				 {
					//Old password given is not correct
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change password; new passwords did not match");
					echo ("<script type=\"text/javascript\">window.location = \"admin.php?pchange=2\"</script>");
				 }
				 else
				 {
					//Some other problem happened, assume the file couldn't be opened
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change the password");
					echo ("<script type=\"text/javascript\">window.location = \"admin.php?pchange=3\"</script>");
				 }
			 }
			 else
			 {
				 //Unsafe to proceed
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change the password");
				echo ("<script type=\"text/javascript\">window.location = \"admin.php?pchange=3\"</script>");
			 }
		 }
		 else
		 {
			 //Make sure password file exists
			 if(file_exists("backend/password.txt"))
			 {
				 //Safe to proceed, verify the existing password and that the new passwords match
				 if(password_verify($_POST['old'],base64_decode(file_get_contents("backend/password.txt"))) === true && $_POST['new'] == $_POST['confirm'])
				 {
					//Everything is good, hash the password, write it, and get out of here
					$hash=base64_encode(password_hash($_POST['new'],PASSWORD_DEFAULT));
					$fh2=fopen("backend/password.txt",'w') or die("Failed to open file \"backend/password.txt\" in write mode. It should now be microwaved.");
					fwrite($fh2,$hash);
					fclose($fh2);
					//Destroy the "first use" flag file if it exists (no one cares if this fails, it'll just result in the system always saying the password needs to be changed)
					if(file_exists("backend/firstuse.txt"))
					{
						unlink("backend/firstuse.txt");
					}
					echo ("<script type=\"text/javascript\">window.location = \"admin.php?pchange=0\"</script>");
				 }
				 elseif(password_verify($_POST['old'],base64_decode(file_get_contents("backend/password.txt"))) !== true)
				 {
					//Old password given is not correct
					echo ("<script type=\"text/javascript\">window.location = \"admin.php?pchange=1\"</script>");
				 }
				 elseif($_POST['new'] != $_POST['confirm'])
				 {
					//Old password given is not correct
					echo ("<script type=\"text/javascript\">window.location = \"admin.php?pchange=2\"</script>");
				 }
				 else
				 {
					//Some other problem happened, assume the file couldn't be opened
					echo ("<script type=\"text/javascript\">window.location = \"admin.php?pchange=3\"</script>");
				 }
			 }
			 else
			 {
				 //Unsafe to proceed
				echo ("<script type=\"text/javascript\">window.location = \"admin.php?pchange=3\"</script>");
			 }
		 }
	 }
	 else
	 {
		 if(is_logging_enabled() === true)
		{
			//Change the timezone
			set_timezone();
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Visited password change page page");
			 if(securitycheck() === false)
			 {
				 die("You are not an administrator. <a href=\"login.php?ref=password\">Sign in</a> or <a href=\"index.php\">Cancel</a>.");
			 }
		 }
		 else
		 {
			 if(securitycheck() === false)
			 {
				die("You are not an administrator. <a href=\"login.php?ref=password\">Sign in</a> or <a href=\"index.php\">Cancel</a>.");
			 }
		 }
	 }*/
?>
<?php
	if(is_logging_enabled() === true)
	{
		if(isset($_POST['delinfo']) && securitycheck() === true)
		{
			//Delete copyright information
			$debug=clear_copyright_information();
			if($debug === true)
			{
				//Success
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Cleared copyright information");
				echo ("<script type=\"text/javascript\">window.location = \"admin.php?copyset=yes\"</script>");
			}
			else
			{
				//Failure
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to clear copyright information");
				echo ("<script type=\"text/javascript\">window.location = \"admin.php?copyset=no\"</script>");
			}
		}
		elseif(isset($_POST['copyinfo']) && $_POST['copyinfo'] != "" && securitycheck() === true)
		{
			//Write new copyright information
			$copyinfo=strip_tags(filter_var($_POST['copyinfo'],FILTER_SANITIZE_STRING));
			$debug=set_copyright_information($copyinfo);
			if($debug === true)
			{
				//Success
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Wrote new copyright information");
				echo ("<script type=\"text/javascript\">window.location = \"admin.php?copyset=yes\"</script>");
			}
			else
			{
				//Failure
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to write new copyright information");
				echo ("<script type=\"text/javascript\">window.location = \"admin.php?copyset=no\"</script>");
			}
		}
		else
		{
			if(isset($_POST['copyinfo']))
			{
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Improper attempt to clear info detected and stopped");
				trigger_error("Failed to submit. Deleting the copyright information is done using the checkbox, not by blanking out the input.",E_USER_ERROR);
			}
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Visited copyright info editing page");
			if(securitycheck() === false)
			{
				die("You are not an administrator. <a href=\"login.php?ref=copyright\">Sign in</a> or <a href=\"index.php\">Cancel</a>.");
			}
			$copyinfo=get_raw_copyright_information();
			if($copyinfo === false)
			{
				$copyinfo="";
			}
		}
	}
	else
	{
		if(isset($_POST['delinfo']) && securitycheck() === true)
		{
			//Delete copyright information
			$debug=clear_copyright_information();
			if($debug === true)
			{
				//Success
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Cleared copyright information");
				echo ("<script type=\"text/javascript\">window.location = \"admin.php?copyset=yes\"</script>");
			}
			else
			{
				//Failure
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to clear copyright information");
				echo ("<script type=\"text/javascript\">window.location = \"admin.php?copyset=no\"</script>");
			}
		}
		elseif(isset($_POST['copyinfo']) && $_POST['copyinfo'] != "" && securitycheck() === true)
		{
			//Write new copyright information
			$copyinfo=strip_tags(filter_var($_POST['copyinfo'],FILTER_SANITIZE_STRING));
			$debug=set_copyright_information($copyinfo);
			if($debug === true)
			{
				//Success
				echo ("<script type=\"text/javascript\">window.location = \"admin.php?copyset=yes\"</script>");
			}
			else
			{
				//Failure
				echo ("<script type=\"text/javascript\">window.location = \"admin.php?copyset=no\"</script>");
			}
		}
		else
		{
			if(isset($_POST['copyinfo']))
			{
				trigger_error("Failed to submit. Deleting the copyright information is done using the checkbox, not by blanking out the input.",E_USER_ERROR);
			}
			if(securitycheck() === false)
			{
				die("You are not an administrator. <a href=\"login.php?ref=copyright\">Sign in</a> or <a href=\"index.php\">Cancel</a>.");
			}
			$copyinfo=get_raw_copyright_information();
			if($copyinfo === false)
			{
				$copyinfo="";
			}
		}
	}
?>
  <body>
  <h1 style="text-align:center; text-decoration:underline;"><?php echo $sysname; ?>Music Request System-Edit Copyright Information</h1>
  <p>This form allows you to input your own copyright information displayed alongside the software copyright on the about page. Code is supported, albeit very minimally; you can use the following:</p>
  <ul>
  <li>Bold: [b]blah[/b]</li>
  <li>Italic: [i]whocares[/i]</li>
  <li>Underline: [u]boring[/u]</li>
  <li>URL with text: [url="www.microwave.com"]Microphonez![/url] (NOTE: the quotes are important!)</li>
  </ul>
  <p><b>Please don't use square brackets</b> ('[' or ']')! Doing so will have undefined behaviour and will probably demote your web server to "turd cluster" status.</p>
  <form method="post" action="copyright.php">
  <textarea name="copyinfo" rows="20" cols="100" required="required"><?php echo $copyinfo; ?></textarea><br>
  <input type="checkbox" name="delinfo" value="y">Clear copyright information (irreversible!)<br>
  <input type="submit" value="Edit copyright info"> or <input type="button" value="Cancel" onclick="window.location.href='admin.php'">
  </form>
  </body>
</html>