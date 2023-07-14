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
    <title><?php echo $sysname; ?>Music Request System-Edit Request</title>
    
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
		if(securitycheck() === true && isset($_POST['confirm']) && $_POST['confirm'] == "y")
		{
			//Sanitize the appropriate elements
			$post=preg_replace("/[^0-9]/","",$_POST['p']);
			$username=preg_replace("/[^A-Za-z0-9 ]/", "", $_POST['username']);
			$request=htmlspecialchars($_POST['request']);
			//Update file
			if(does_post_exist($post) === true)
			{
				//Format: [id,name,ip,date,request,status,admincomment,usercomment,filename]
				$contents=get_request($post);
				$contents[1]=stripcslashes($username);
				$contents[4]=stripcslashes(htmlspecialchars($request));
				$debug=write_request($contents[0],$contents[1],$contents[2],$contents[3],$contents[4],$contents[5],$contents[6],$contents[7],$contents[8]);
				if($debug === true)
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Updated post \"$post\"");
					echo ("<script type=\"text/javascript\">window.location = \"index.php?editstatus=0\"</script>");
				}
				else
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to update post \"$post\": could not open file");
					echo ("<script type=\"text/javascript\">window.location = \"index.php?editstatus=1\"</script>");
				}
			}
			else
			{
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to update post \"$post\": not authorized or invalid data set supplied");
				echo ("<script type=\"text/javascript\">window.location = \"index.php?editstatus=2\"</script>");
			}
		}
		elseif(securitycheck() === true)
		{
			//Sanitize post number
			$post=preg_replace("/[^0-9]/","",$_GET['p']);
			//Get file info
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Visited edit page for post \"$post\"");
			if(does_post_exist($post) === true)
			{
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Obtained details for post \"$post\"");
				$contents=get_request($post);
			}
			else
			{
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Encountered error obtaining details for post \"$post\"");
				trigger_error("Failed to obtain request information for post #" . $post . ". Microwave the request file.",E_USER_ERROR);
				$disabled=true;
				$contents=array(0,"Error","127.0.0.1","01/01/1970 12:00 AM","This request could not be displayed due to an internal error",3,"","Please microwave the system.","");
			}
		}
		else
		{
			//Nope.
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Visited edit page for post \"$post\"");
			die("You are not authorized to edit this post. <a href=\"login.php?ref=edit\">Sign in</a> or <a href=\"index.php\">Cancel</a>.");
		}
	}
	else
	{
		//Change the timezone
		set_timezone();
		//Logging disabled
		if(securitycheck() === true && isset($_POST['confirm']) && $_POST['confirm'] == "y")
		{
			//Sanitize the appropriate elements
			$post=preg_replace("/[^0-9]/","",$_POST['p']);
			$username=preg_replace("/[^A-Za-z0-9 ]/", "", $_POST['username']);
			$request=htmlspecialchars($_POST['request']);
			//Update file
			if(does_post_exist($post) === true)
			{
				//Format: [id,name,ip,date,request,status,admincomment,usercomment,filename]
				$contents=get_request($post);
				$contents[1]=stripcslashes($username);
				$contents[4]=stripcslashes(htmlspecialchars($request));
				$debug=write_request($contents[0],$contents[1],$contents[2],$contents[3],$contents[4],$contents[5],$contents[6],$contents[7],$contents[8]);
				if($debug === true)
				{
					echo ("<script type=\"text/javascript\">window.location = \"index.php?editstatus=0\"</script>");
				}
				else
				{
					echo ("<script type=\"text/javascript\">window.location = \"index.php?editstatus=1\"</script>");
				}
			}
			else
			{
				echo ("<script type=\"text/javascript\">window.location = \"index.php?editstatus=2\"</script>");
			}
		}
		elseif(securitycheck() === true)
		{
			//Sanitize post number
			$post=preg_replace("/[^0-9]/","",$_GET['p']);
			//Get file info
			if(does_post_exist($post) === true)
			{
				$contents=get_request($post);
			}
			else
			{
				trigger_error("Failed to obtain request information for post #" . $post . ". Microwave the request file.",E_USER_ERROR);
				$disabled=true;
				$contents=array(0,"Error","127.0.0.1","01/01/1970 12:00 AM","This request could not be displayed due to an internal error",3,"","Please microwave the system.","");
			}
		}
		else
		{
			//Nope.
			die("You are not authorized to edit this post. <a href=\"login.php?ref=edit\">Sign in</a> or <a href=\"index.php\">Cancel</a>.");
		}
	}
  ?>
  <h1 style="text-align:center; text-decoration:underline;"><?php echo $sysname; ?>Music Request System-Edit Post #<?php echo $post; ?></h1>
  <p>Songs are formatted using the list fields. For example, "artist=Rough Trade|title=High School Confidential|album=Avoid Freud|year=1980". For custom requests, they are formatted with a "custom**" flag.<br>
  <b>You MUST keep this format!</b> Entries without any fields and without the "custom**" flag will summon nasal demons, the program director, and/or undefined behaviour!</p>
  <form method="post" action="edit.php">
  <input type="hidden" name="confirm" value="y">
  <input type="hidden" name="p" value="<?php echo $post; ?>">
  Username: <input type="text" name="username" value="<?php echo $contents[1]; ?>"><br>
  Request:<br>
  <textarea name="request" rows="10" cols="50"><?php echo $contents[4]; ?></textarea><br>
  <input type="submit" value="Confirm"<?php if(isset($disabled) && $disabled === true) { echo "disabled=\"disabled\""; } ?>> or <input type="button" value="Cancel" onclick="window.location.href='index.php'">
  </form>
  </body>
</html>