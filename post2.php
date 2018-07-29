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
	//Include functions page
	if(file_exists("backend/functions.php"))
	{
		include("backend/functions.php");
	}
	else
	{
		die("Failed to open file \"backend/functions.php\" in read mode. It should now be microwaved.");
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
	
	//Function for checking if a song exists on the list
	function on_list($req)
	{
		return true;
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
	//If username is not stored, set it
	if(!isset($_SESSION['uname']))
	{
		$_SESSION['uname']="";
	}
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
<?php
	if(is_logging_enabled() === true)
	{
		//Change the timezone
		set_timezone();
		//Logging enabled on system
		write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Visited posting page");
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
    <title><?php echo $sysname; ?>Music Request System-Make A Request</title>
    
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
  <h1 style="text-align:center; text-decoration:underline;"><?php echo $sysname; ?>MRS-Make A Request</h1>
  <?php
	//Change the timezone
	set_timezone();
	
	if(is_logging_enabled() === true)
	{
		//Logging enabled
		if(isset($_POST['submit']) && $_POST['submit'] == "y")
		{
			//Submission started
			/* Methodology:
			-Check if posting is enabled
			-Check if anonymous
			-Make sure user can post request
			-Filter username and request
			-Make sure request is not in system
			-Check for request overages
			-Write the file
			-Write to XML file */
			
			if(get_system_setting("posting") != "yes")
			{
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"System is closed");
				die("<script type=\"text/javascript\">window.location = \"index.php?status=1\"</script>");
			}
            if(system_in_overflow() === true)
            {
                write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"System is in overflow mode");
                die("<script type=\"text/javascript\">window.location = \"index.php?status=6\"</script>");
            }
			
			if(isset($_POST['comment']))
			{
				$comment=filter_var($_POST['comment'],FILTER_SANITIZE_STRING);
			}
			else
			{
				$comment="None";
			}
			
			if(isset($_POST['name']))
			{
				$name=trim(preg_replace("/[^A-Za-z0-9 ]/", "", $_POST['name']));
			}
			else
			{
				$name="";
			}
			
			if(isset($_POST['anon']) && $_POST['anon'] == "y")
			{
				$name="Anonymous";
			}
			
			if(get_system_setting("anon") == "no" && $name == "Anonymous")
			{
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Attempted to submit as anonymous when disallowed");
				die("<script type=\"text/javascript\">window.location = \"index.php?status=3\"</script>");
			}
			elseif(get_system_setting("anon") == "yes" && $name == "Anonymous")
			{
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Started submission as anonymous user");
			}
			elseif($name != "")
			{
				$name=preg_replace("/[^A-Za-z0-9 ]/", "", $_POST['name']);
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Started submission as $name");
				$_SESSION['uname']=$name;
			}
			else
			{
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Attempted to submit without a name");
				die("<script type=\"text/javascript\">window.location = \"index.php?status=3\"</script>");
			}
			
			if($name != "Anonymous")
			{
				$uban=is_user_banned($name);
			}
			else
			{
				$uban=array(false);
			}
			if(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] != "")
			{
				$iban=is_ip_banned($_SERVER['REMOTE_ADDR']);
			}
			else
			{
				$iban=array(false);
			}
			
			if($uban[0] === true || $iban[0] === true)
			{
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"User banned from system");
				die("<script type=\"text/javascript\">window.location = \"index.php?status=2\"</script>");
			}
			
			if($_POST['request'] != "" && isset($_POST['request']))
			{
				$request=htmlspecialchars($_POST['request']);
			}
			else
			{
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Request was empty");
				die("<script type=\"text/javascript\">window.location = \"index.php?status=3\"</script>");
			}
			
			if(currentrequest($request) === true)
			{
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Attempting to submit a request that has already been requested");
				die("<script type=\"text/javascript\">window.location = \"index.php?status=5\"</script>");
			}
			
			if(get_system_setting("open") == "no" && on_list($request) === false)
			{
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Attempting to submit a request that has already been requested");
				die("<script type=\"text/javascript\">window.location = \"index.php?status=8\"</script>");
			}
			
			if(user_lockout() === true)
			{
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Attempting to submit too many requests");
				die("<script type=\"text/javascript\">window.location = \"index.php?status=4\"</script>");
			}
			
			$postid=increment_post_count();
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Got and incremented post count");
			if(isset($_POST['filename']) && $_POST['filename'] != "")
			{
				$filename=filter_var(base64_decode($_POST['filename']),FILTER_SANITIZE_STRING);
			}
			else
			{
				$filename="";
			}
			
			$debug=write_request($postid,stripcslashes($name),$_SERVER['REMOTE_ADDR'],date("m/d/Y g:i A"),stripcslashes($request),0,"None",stripcslashes($comment),$filename);
			if($debug === false)
			{
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to write post $postid");
				echo ("<script type=\"text/javascript\">window.location = \"index.php?status=7\"</script>");
			}
			else
			{
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Successfully wrote post $postid");
			}
			
			$xml="<!-- insert here -->\n<entry>\n<author>" . stripcslashes($name) . "</author>\n<id>" . $postid . "</id>\n<link href=\"http://firealarms.redbat.ca/streamreq\"/>\n<title type=\"html\">Request # " . $postid . " at " . date("m/d/Y g:i A") . "</title>\n<content type=\"text\">" . stripcslashes($request) . " made by " . stripcslashes($name) . "</content>\n<filename>" . stripcslashes($filename) . "</filename>\n</entry>";
			$debug=add_to_feed($xml);
			if($debug === false)
			{
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to write to RSS feed");
				echo ("<script type=\"text/javascript\">window.location = \"index.php?status=7\"</script>");
			}
			else
			{
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Successfully wrote to RSS feed");
			}
			echo ("<script type=\"text/javascript\">window.location = \"index.php?status=0\"</script>");
		}
		else
		{
			//Trying to make request
			if(!isset($_SESSION['uname']))
			{
				$_SESSION['uname']="";
			}
			$posting=get_system_setting("posting");
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Obtained details regarding posting");
			
            if(system_in_overload() === true)
            {
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"System is in overflow mode");
                $posting="no";
            }
			if($posting == "yes")
			{
				$posting=true;
			}
			else
			{
				$posting=false;
			}
		
			if(get_system_setting("pdreq") == "yes" && isset($_SESSION['uname']) && $_SESSION['uname'] != "" && pendingrequest($_SESSION['uname']) === true)
			{
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"System is in overflow mode");
				$posting=false;
			}
			
            if(user_lockout() === true && $posting === true)
            {
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"User is locked out");
                $posting=false;
            }
			
			$anon=get_system_setting("anon");
			$open=get_system_setting("open");
			//If light mode enabled, open requests are automatically allowed
			if(get_system_setting("light") == "yes")
			{
				$open="yes";
			}
			$comments=get_system_setting("comments");
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Obtained system settings");
			
			$song="";
			$filename="";
			
			if(isset($_GET['pid']) && ($pid=preg_replace("/[^0-9]/","",$_GET['pid'])) != "")
			{
				$songs=get_song_list();
				$song=explode("|",$songs[$pid]);
				$fnpos=array_search("filename",explode("|",get_system_setting("songformat")));
				if($fnpos !== false)
				{
					$filename=$song[$fnpos];
				}
				$song=$song[0] . "-" . $song[1] . " (from the album \"" . $song[2] . "\", " . $song[3] . ")";
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Obtained song information");
			}
			elseif(isset($_GET['req']) && $_GET['req'] != "")
			{
				$song=filter_var($_GET['req'],FILTER_SANITIZE_STRING);
				if(isset($_GET['filename']) && $_GET['filename'] != "")
				{
					$filename=filter_var($_GET['filename'],FILTER_SANITIZE_STRING);
				}
				else
				{
					$filename="";
				}
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Obtained song information");
			}
		}
	}
	else
	{
		//Logging Disabled
		if(isset($_POST['submit']) && $_POST['submit'] == "y")
		{
			//Submission started
			/* Methodology:
			-Check if posting is enabled
			-Check if anonymous
			-Make sure user can post request
			-Filter username and request
			-Make sure request is not in system
			-Check for request overages
			-Write the file
			-Write to XML file */
			
			if(get_system_setting("posting") != "yes")
			{
				die("<script type=\"text/javascript\">window.location = \"index.php?status=1\"</script>");
			}
            if(system_in_overflow() === true)
            {
                die("<script type=\"text/javascript\">window.location = \"index.php?status=6\"</script>");
            }
			
			if(isset($_POST['name']))
			{
				$name=trim(preg_replace("/[^A-Za-z0-9 ]/", "", $_POST['name']));
			}
			else
			{
				$name="";
			}
			
			if(isset($_POST['anon']) && $_POST['anon'] == "y")
			{
				$name="Anonymous";
			}
			
			if(get_system_setting("anon") == "no" && $name == "Anonymous")
			{
				die("<script type=\"text/javascript\">window.location = \"index.php?status=3\"</script>");
			}
			elseif($name != "")
			{
				$name=preg_replace("/[^A-Za-z0-9 ]/", "", $_POST['name']);
				$_SESSION['uname']=$name;
			}
			else
			{
				die("<script type=\"text/javascript\">window.location = \"index.php?status=3\"</script>");
			}
			
			if(isset($_POST['comment']))
			{
				$comment=filter_var($_POST['comment'],FILTER_SANITIZE_STRING);
			}
			else
			{
				$comment="None";
			}
			$_SESSION['uname']=$name;
			
			if($name != "Anonymous")
			{
				$uban=is_user_banned($name);
			}
			else
			{
				$uban=array(false);
			}
			if(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] != "")
			{
				$iban=is_ip_banned($_SERVER['REMOTE_ADDR']);
			}
			else
			{
				$iban=array(false);
			}
			
			if($uban[0] === true || $iban[0] === true)
			{
				die("<script type=\"text/javascript\">window.location = \"index.php?status=2\"</script>");
			}
			
			if($_POST['request'] != "" && isset($_POST['request']))
			{
				$request=htmlspecialchars($_POST['request']);
			}
			else
			{
				die("<script type=\"text/javascript\">window.location = \"index.php?status=3\"</script>");
			}
			
			if(get_system_setting("open") == "no" && on_list($request) === false)
			{
				die("<script type=\"text/javascript\">window.location = \"index.php?status=8\"</script>");
			}
			
			if(currentrequest($request) === true)
			{
				die("<script type=\"text/javascript\">window.location = \"index.php?status=5\"</script>");
			}
			
			if(user_lockout() === true)
			{
				die("<script type=\"text/javascript\">window.location = \"index.php?status=4\"</script>");
			}
			
			$postid=increment_post_count();
			if(isset($_POST['filename']) && $_POST['filename'] != "")
			{
				$filename=filter_var(base64_decode($_POST['filename']),FILTER_SANITIZE_STRING);
			}
			else
			{
				$filename="";
			}
			
			$debug=write_request($postid,stripcslashes($name),$_SERVER['REMOTE_ADDR'],date("m/d/Y g:i A"),stripcslashes($request),0,"None",stripcslashes($comment),$filename);
			if($debug === false)
			{
				echo ("<script type=\"text/javascript\">window.location = \"index.php?status=7\"</script>");
			}
			
			$xml="<!-- insert here -->\n<entry>\n<author>" . stripcslashes($name) . "</author>\n<id>" . $postid . "</id>\n<link href=\"http://firealarms.redbat.ca/streamreq\"/>\n<title type=\"html\">Request # " . $postid . " at " . date("m/d/Y g:i A") . "</title>\n<content type=\"text\">" . stripcslashes($request) . " made by " . stripcslashes($name) . "</content>\n<filename>" . stripcslashes($filename) . "</filename>\n</entry>";
			$debug=add_to_feed($xml);
			if($debug === false)
			{
				echo ("<script type=\"text/javascript\">window.location = \"index.php?status=7\"</script>");
			}
			echo ("<script type=\"text/javascript\">window.location = \"index.php?status=0\"</script>");
		}
		else
		{
			//Trying to make request
			$posting=get_system_setting("posting");
			
			if(!isset($_SESSION['uname']))
			{
				$_SESSION['uname']="";
			}
			
            if(system_in_overload() === true)
            {
                $posting="no";
            }
			if($posting == "yes")
			{
				$posting=true;
			}
			else
			{
				$posting=false;
			}
		
			if(get_system_setting("pdreq") == "yes" && isset($_SESSION['uname']) && $_SESSION['uname'] != "" && pendingrequest($_SESSION['uname']) === true)
			{
				$posting=false;
			}
			
            if(user_lockout() === true && $posting === true)
            {
                $posting=false;
            }
			
			$anon=get_system_setting("anon");
			$open=get_system_setting("open");
			//If light mode enabled, open requests are automatically allowed
			if(get_system_setting("light") == "yes")
			{
				$open="yes";
			}
			$comments=get_system_setting("comments");
			
			$song="";
			$filename="";
			
			if(isset($_GET['pid']) && $_GET['pid'] != "")
			{
				$pid=preg_replace("/[^0-9]/","",$_GET['pid']);
			}
			else
			{
				$pid="";
			}
			
			if(isset($pid) && $pid != "")
			{
				$songs=get_song_list();
				$song=explode("|",$songs[$pid]);
				$fnpos=array_search("filename",explode("|",get_system_setting("songformat")));
				if($fnpos !== false)
				{
					$filename=$song[$fnpos];
				}
				$song=$song[0] . "-" . $song[1] . " (from the album \"" . $song[2] . "\", " . $song[3] . ")";
			}
			elseif(isset($_GET['req']) && $_GET['req'] != "")
			{
				$song=filter_var($_GET['req'],FILTER_SANITIZE_STRING);
				if(isset($_GET['filename']) && $_GET['filename'] != "")
				{
					$filename=filter_var($_GET['filename'],FILTER_SANITIZE_STRING);
				}
				else
				{
					$filename="";
				}
			}
		}
	}
  ?>
  <form action="post2.php" method="post">
  <input type="hidden" name="filename" value="<?php echo base64_encode($filename); ?>">
  Name: <input type="text" name="name" value="<?php echo $_SESSION['uname']; ?>"<?php if($anon == "no") { echo(" required=\"required\""); } ?>> OR <input type="checkbox" name="anon" value="y" <?php if($anon == "no") { echo("disabled=\"disabled\""); } ?>>Anonymous<br>
  IP Address: <?php echo $_SERVER['REMOTE_ADDR']; ?> (this WILL be submitted with your request!)<br>
  Request: <input type="text" size="50" name="request" required="required" <?php if($open != "yes") {echo ("readonly=\"readonly\"");} ?> value="<?php echo stripcslashes($song); ?>"><br>
  Comment (optional):<br>
  <textarea name="comment" <?php if($comments == "no") { echo "disabled=\"disabled\""; } ?> rows="10" cols="50"></textarea><br>
  <input type="hidden" name="submit" value="y">
  <input type="hidden" name="posting" value="<?php if($posting === true) { echo "yes"; } else { echo "no"; } ?>">
  <input type="submit" id="sbutton" value="Make request" <?php if($posting === false) { echo "disabled=\"disabled\""; } ?>><input type="button" value="Cancel" onclick="window.location.href='index.php'">
  </form>
  </body>
</html>