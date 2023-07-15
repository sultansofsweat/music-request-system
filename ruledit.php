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
    <title><?php echo $sysname; ?>Music Request System-Edit Rule List</title>
    
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
<?php
	if(isset($_POST['s']) && $_POST['s'] == "y")
	{
		//Begin submission
		if(is_logging_enabled() === true)
		{
			//Change the timezone
			set_timezone();
			//Sanitize rule list
			$list=htmlspecialchars($_POST['list']);
			//Write contents of submission to song list
			$debug=save_rules($list);
			if($debug === true)
			{
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Successfully changed rule list");
				echo("<script type=\"text/javascript\">window.location = \"admin.php?rl=0\"</script>");
			}
			else
			{
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change rule list");
				echo("<script type=\"text/javascript\">window.location = \"admin.php?rl=1\"</script>");
			}
		}
		else
		{
			//Sanitize rule list
			$list=htmlspecialchars($_POST['list']);
			//Write contents of submission to song list
			$debug=save_rules($list);
			if($debug === true)
			{
				echo("<script type=\"text/javascript\">window.location = \"admin.php?rl=0\"</script>");
			}
			else
			{
				echo("<script type=\"text/javascript\">window.location = \"admin.php?rl=1\"</script>");
			}
		}
	}
	else
	{
		if(is_logging_enabled() === true)
		{
			//Change the timezone
			set_timezone();
			//Logging enabled
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Visited rule editing page");
			if(securitycheck() === false)
			{
				//User is not administrator
				die("You are not an administrator. <a href=\"login.php?ref=ruledit\">Sign in</a> or <a href=\"index.php\">Cancel</a>.");
			}
			//Get list of rules
			$list=get_rules();
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Got rule list");
			//Make array of rules a list, if applicable
			if(is_array($list))
			{
				$list=implode("\r\n",$list);
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Processed rule list for input into form");
			}
		}
		else
		{
			//Logging disabled
			if(securitycheck() === false)
			{
				//User is not administrator
				die("You are not an administrator. <a href=\"login.php?ref=ruledit\">Sign in</a> or <a href=\"index.php\">Cancel</a>.");
			}
			//Get list of rules
			$list=get_rules();
			//Make array of rules a list, if applicable
			if(is_array($list))
			{
				$list=implode("\r\n",$list);
			}
		}
	}
?>
  </head>
  <body>
  <h1 style="text-align:center; text-decoration:underline;"><?php echo $sysname; ?>MRS-Edit Rule List</h1>
  <form method="post" action="ruledit.php">
  <input type="hidden" name="s" value="y">
  <textarea name="list" rows="30" cols="100" required="required"><?php echo stripcslashes($list); ?></textarea><br>
  <input type="submit"><input type="button" value="Cancel" onclick="window.location.href='admin.php'">
  </form>
  </body>
</html>