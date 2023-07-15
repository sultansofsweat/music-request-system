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
	//Import useful functions file, if it exists
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
    <title><?php echo system_name(); ?>Music Request System-Decline Request</title>
    
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
	//Change the timezone
	set_timezone();
	if(securitycheck() === true && isset($_POST['confirm']) && $_POST['confirm'] == "y")
	{
		//Sanitize the post number!
		$post=preg_replace("/[^0-9]/","",$_POST['p']);
		//Make sure file exists
		if($post != "" && does_post_exist($post))
		{
			//Update file
			$contents=get_request($post);
			if(isset($_POST['comment']) && $_POST['comment'] != "")
			{
				//Sanitize comment
				$comment=htmlspecialchars($_POST['comment']);
			}
			else
			{
				$comment="None";
			}
			$debug=write_request($post,$contents[1],$contents[2],$contents[3],$contents[4],1,$comment,$contents[7],$contents[8]);
			if($debug === false)
			{
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to update request $post");
				echo ("<script type=\"text/javascript\">window.location = \"index.php?decstatus=1\"</script>");
			}
			else
			{
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Successfully updated request $post");
				echo ("<script type=\"text/javascript\">window.location = \"index.php?decstatus=0\"</script>");
			}
		}
		else
		{
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to update request $post");
			echo ("<script type=\"text/javascript\">window.location = \"index.php?decstatus=2\"</script>");
		}
	}
	elseif(securitycheck() === true)
	{
		//Sanitize the post number!
		$post=preg_replace("/[^0-9]/","",$_GET['p']);
		//Get file info
		write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Visited post decline page for post $post");
		if($post != "" && does_post_exist($post))
		{
			$contents=get_request($post);
		}
		else
		{
			die("Failed to obtain request information for post #" . $post . ". Microwave the request file.");
		}
	}
	else
	{
		//Sanitize the post number!
		$post=preg_replace("/[^0-9]/","",$_GET['p']);
		//Nope.
		write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Visited post decline page for post $post");
		die("You are not authorized to decline this post. <a href=\"login.php?ref=decline\">Sign in</a> or <a href=\"index.php\">Cancel</a>.");
	}
  ?>
  <h1 style="text-align:center; text-decoration:underline;"><?php echo system_name(); ?>Music Request System-Decline Request In Post #<?php echo $post; ?></h1>
  <p><u>Post info</u><br>
  ID: <?php echo $contents[0]; ?><br>
  Username: <?php echo $contents[1]; ?><br>
  IP Address: <?php echo $contents[2]; ?><br>
  Date: <?php echo $contents[3]; ?><br>
  Request: <?php echo stripcslashes($contents[4]); ?><br>
  Current status: <?php echo $contents[5]; ?> (0-unseen, 1-declined, 2-in queue, 3-played)<br>
  Comment: <?php echo stripcslashes($contents[7]); ?><br>
  Response: <?php echo stripcslashes($contents[6]); ?></p>
  <p>Are you sure you want to decline this post?</p>
  <form method="post" action="decline.php">
  <input type="hidden" name="confirm" value="y">
  <input type="hidden" name="p" value="<?php echo $post; ?>">
  Comments:<br>
  <textarea name="comment" rows="10" cols="50"><?php if($contents[6] != "None"){ echo stripcslashes($contents[6]); } ?></textarea><br>
  <input type="submit" value="Confirm"> or <input type="button" value="Cancel" onclick="window.location.href='index.php'">
  </form>
  </body>
</html>