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
    <title><?php echo $sysname; ?>Music Request System-View IP Address Banlist</title>
    
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
	if(is_logging_enabled() === true)
	{
		set_timezone();
		write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Visited IP banlist page");
		if(securitycheck() === false)
		{
			die("You are not an administrator. <a href=\"login.php?ref=vip\">Sign in</a> or <a href=\"index.php\">Cancel</a>.");
		}
		if(isset($_GET['unban']))
		{
			$ip=htmlspecialchars($_GET['unban']);
			if(filter_var($ip,FILTER_VALIDATE_IP))
			{
				echo "LOSER";
				$debug=unban_ip($ip);
				if($debug == 0)
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Successfully removed \"$ip\" from IP banlist");
					echo("<script type=\"text/javascript\">window.location = \"index.php?banip=yes\"</script>");
				}
				else
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to remove \"$ip\" from IP banlist");
					echo("<script type=\"text/javascript\">window.location = \"index.php?banip=no\"</script>");
				}
			}
		}
		$bans=get_all_ip_bans();
	}
	else
	{
		if(securitycheck() === false)
		{
			die("You are not an administrator. <a href=\"login.php?ref=vip\">Sign in</a> or <a href=\"index.php\">Cancel</a>.");
		}
		if(isset($_GET['unban']))
		{
			$ip=htmlspecialchars($_GET['unban']);
			if(filter_var($ip,FILTER_VALIDATE_IP))
			{
				$debug=unban_ip($ip);
				if($debug == 0)
				{
					echo("<script type=\"text/javascript\">window.location = \"index.php?banip=yes\"</script>");
				}
				else
				{
					echo("<script type=\"text/javascript\">window.location = \"index.php?banip=no\"</script>");
				}
			}
		}
		$bans=get_all_ip_bans();
	}
?>
  </head>
  <body>
  <h1 style="text-align:center; text-decoration:underline;"><?php echo $sysname; ?>MRS-View IP Address Banlist</h1>
  <p>
  <?php
	foreach($bans as $ban)
	{
		echo("<b>" . $ban[0] . "</b> for reason \"" . $ban[1] . "\" <a href=\"vip.php?unban=" . $ban[0] . "\">Unban this IP address</a><br>\r\n");
	}
  ?>
  </p>
  <p><a href="index.php">Go back</a></p>
  </form>
  </body>
</html>