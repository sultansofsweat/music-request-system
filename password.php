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
	 if(isset($_POST['s']) && $_POST['s'] == "y" && securitycheck() === true)
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
	 }
?>
  <body>
  <h1 style="text-align:center; text-decoration:underline;"><?php echo $sysname; ?>Music Request System-Change System Password</h1>
  <form method="post" action="password.php">
  <input type="hidden" name="s" value="y">
  Current password: <input type="password" name="old" required="required"><br>
  New password: <input type="password" name="new" required="required"><br>
  Confirm: <input type="password" name="confirm" required="required"><br>
  <input type="submit" value="Change"> or <input type="button" value="Cancel" onclick="window.location.href='admin.php'">
  </form>
  </body>
</html>