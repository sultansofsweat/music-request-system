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
    <title><?php echo $sysname; ?>Music Request System-Delete Request</title>
    
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
  	if(is_logging_enabled() === true)
	{
		//Change the timezone
		set_timezone();
		//Logging enabled
		if(securitycheck() === true && isset($_GET['s']) && isset($_GET['p']) && $_GET['s'] == "y" && $_GET['p'] != "")
		{
			//Sanitize the post number!
			$post=preg_replace("/[^0-9]/","",$_GET['p']);
			//Make sure file exists
			if(does_post_exist($post) === true)
			{
				//Delete the file
				$debug=delete_post($post);
				if($debug === true)
				{
					//Deleted
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Deleted post \"$post\"");
					echo ("<script type=\"text/javascript\">window.location = \"index.php?delstatus=0\"</script>");
				}
				else
				{
					//Not deleted
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to delete post \"$post\": error deleting file");
					echo ("<script type=\"text/javascript\">window.location = \"index.php?delstatus=1\"</script>");
				}
			}
			else
			{
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to delete post \"$post\": unauthorized or invalid data set supplied");
				echo ("<script type=\"text/javascript\">window.location = \"index.php?delstatus=2\"</script>");
			}
		}
		elseif(securitycheck() === true)
		{
			//Sanitize the post number!
			$post=preg_replace("/[^0-9]/","",$_GET['p']);
			//Get file info
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Visited delete page for post \"$post\"");
			if(does_post_exist($post) === true)
			{
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Obtained details for post \"$post\"");
				$contents=get_request($post);
			}
			else
			{
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Encountered error obtaining details for post \"$post\"");
				trigger_error("Failed to obtain request information for post #" . $post . ". Microwave the request file.",E_USER_ERROR);
				$contents=array(0,"Error","127.0.0.1","01/01/1970 12:00 AM","This request could not be displayed due to an internal error",3,"","Please microwave the system.","");
				$disabled=true;
			}
		}
		else
		{
			//Sanitize the post number!
			$post=preg_replace("/[^0-9]/","",$_GET['p']);
			//Nope.
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Visited delete page for post \"$post\"");
			die("You are not authorized to delete this post. <a href=\"login.php?ref=delete\">Sign in</a> or <a href=\"index.php\">Cancel</a>.");
		}
	}
	else
	{
		//Change the timezone
		set_timezone();
		//Logging disabled
		if(securitycheck() === true && isset($_GET['s']) && isset($_GET['p']) && $_GET['s'] == "y" && $_GET['p'] != "")
		{
			//Sanitize the post number!
			$post=preg_replace("/[^0-9]/","",$_GET['p']);
			//Make sure file exists
			if(does_post_exist($post) === true)
			{
				//Delete the file
				$debug=delete_post($post);
				if($debug === true)
				{
					//Deleted
					echo ("<script type=\"text/javascript\">window.location = \"index.php?delstatus=0\"</script>");
				}
				else
				{
					//Not deleted
					echo ("<script type=\"text/javascript\">window.location = \"index.php?delstatus=1\"</script>");
				}
			}
			else
			{
				echo ("<script type=\"text/javascript\">window.location = \"index.php?delstatus=2\"</script>");
			}
		}
		elseif(securitycheck() === true)
		{
			//Sanitize the post number!
			$post=preg_replace("/[^0-9]/","",$_GET['p']);
			//Get file info
			if(does_post_exist($post) === true)
			{
				$contents=get_request($post);
			}
			else
			{
				trigger_error("Failed to obtain request information for post #" . $post . ". Microwave the request file.",E_USER_ERROR);
				$contents=array(0,"Error","127.0.0.1","01/01/1970 12:00 AM","This request could not be displayed due to an internal error",3,"","Please microwave the system.","");
				$disabled=true;
			}
		}
		else
		{
			//Sanitize the post number!
			$post=preg_replace("/[^0-9]/","",$_GET['p']);
			//Nope.
			die("You are not authorized to delete this post. <a href=\"login.php?ref=delete\">Sign in</a> or <a href=\"index.php\">Cancel</a>.");
		}
	}
  ?>
  <h1 style="text-align:center; text-decoration:underline;"><?php echo $sysname; ?>Music Request System-Delete Request #<?php echo $post; ?></h1>
  <p>Post info:</p>
  <pre><?php var_dump($contents); ?></pre>
  <p><b>MAKE ABSOLUTE CERTAIN THIS IS THE FILE YOU WANT TO DELETE!</b> It is unrecoverable once the process has started!<br>
  <?php if(isset($disabled) && $disabled === true) { echo("<span style=\"text-decoration:line-through;\">Delete file</span>"); } else { echo("<a href=\"delete.php?p=$post&s=y\">Delete file</a>"); } ?> or <a href="index.php">Cancel</a>
  </form>
  </body>
</html>