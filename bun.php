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
    <title><?php echo $sysname; ?>Music Request System-Ban Username</title>
    
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
	date_default_timezone_set(get_system_setting("timezone"));
	if(securitycheck() === true)
	{
		if(isset($_POST['s']) && $_POST['s'] == "y" && isset($_POST['name']) && $_POST['name'] != "")
		{
			if(($uname=preg_replace("/[^A-Za-z0-9 ]/","",$_POST['name'])) != "")
			{
				//Valid
				if(isset($_POST['reason']))
				{
					$debug=ban_user($uname,htmlspecialchars($_POST['reason']));
				}
				else
				{
					$debug=ban_user($uname);
				}
				if($debug === true)
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Added username \"$uname\" to banlist");
					echo ("<script type=\"text/javascript\">window.location = \"index.php?unstatus=0\"</script>");
				}
				else
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Did not add username \"$uname\" to banlist");
					echo ("<script type=\"text/javascript\">window.location = \"index.php?unstatus=2\"</script>");
				}
			}
			else
			{
				//Invalid
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Did not add username \"$uname\" to banlist");
				echo ("<script type=\"text/javascript\">window.location = \"index.php?unstatus=1\"</script>");
			}
		}
		else
		{
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Visited username ban page");
			if(isset($_GET['p']) && $_GET['p'] != "")
			{
				$uname=preg_replace("/[^A-Za-z0-9 ]/","",$_GET['p']);
			}
			else
			{
				$uname="";
			}
		}
	}
	else
	{
		write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Visited username ban page");
		die("You are not an administrator. <a href=\"login.php?ref=bun\">Sign in</a> or <a href=\"index.php\">Cancel</a>.");
	}
?>
  <body>
  <h1 style="text-align:center; text-decoration:underline;"><?php echo $sysname; ?>Music Request System-Ban Username</h1>
  <form method="post" action="bun.php">
  <input type="hidden" name="s" value="y">
  Username to ban: <input type="text" name="name" value="<?php echo $uname; ?>"><br>
  Reason: <input type="text" name="reason"><br>
  <input type="submit" value="Ban"> or <input type="button" value="Cancel" onclick="window.location.href='index.php'">
  </form>
  </body>
</html>