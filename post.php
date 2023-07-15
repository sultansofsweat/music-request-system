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
	if(is_logging_enabled() === true)
	{
		set_timezone();
		if(isset($_POST['s']) && $_POST['s'] == "y")
		{
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Began submitting request");
			/* SUBMISSION STEPS
			-Sanitize everything
			-Get necessary settings
			-Check against system settings
			-Write request */
			
			//Initialize variables
			$name="";
			$ip="";
			$request="";
			$comment="";
			$filename="";
			
			//Get and sanitize name
			if(isset($_POST['name']) && $_POST['name'] != "")
			{
				$name=trim(preg_replace("/[^A-Za-z0-9 ]/", "", $_POST['name']));
				$_SESSION['uname']=$name;
			}
			else
			{
				$name=$_SESSION['uname'];
			}
			//If anonymous flag submitted and allowed, set name to anonymous
			if(isset($_POST['anon']) && $_POST['anon'] == "y" && get_system_setting("anon") == "yes")
			{
				$name="Anonymous";
			}
			
			//Set IP address
			$ip=$_SERVER['REMOTE_ADDR'];
			
			//Set song list
			if(!isset($_POST['list']) || ($list=preg_replace("/[^A-Za-z0-9]/","",$_POST['list'])) == "" || !file_exists("songs/$list.txt"))
			{
				$list="";
			}
			
			//Get request
			if($list != "" && isset($_POST['reqid']) && ($reqid=preg_replace("/[^0-9]/","",$_POST['reqid'])) != "")
			{
				$song=get_song($list,$reqid);
				foreach($song as $key=>$value)
				{
					if($key != "added_to_system" && $key != "request_count" && $key != "last_requested")
					{
						$request.="$key=$value|";
					}
				}
				$request=substr($request,0,-1);
			}
			else
			{
				$reqid="";
			}
			//If override submitted and allowed, set request to override instead
			if(isset($_POST['override']) && preg_replace("/[^A-Za-z0-9]/","",$_POST['override']) != "" && (get_system_setting("open") == "yes" || get_system_setting("light") == "yes"))
			{
				$override=htmlspecialchars($_POST['override']);
				$request="custom**=$override";
				$list="whocares";
				$reqid=false;
			}
			else
			{
				$override="";
			}
			
			//Get comment
			if(isset($_POST['comment']) && get_system_setting("comments") == "yes")
			{
				$comment=htmlspecialchars($_POST['comment']);
			}
			else
			{
				$comment="";
			}
			
			//Make sure input is valid
			if($name == "" || $ip == "" || $list == "" || $request == "")
			{
                write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Invalid and/or incomplete data set passed to make request");
                die("<script type=\"text/javascript\">window.location = \"index.php?status=3\"</script>");
			}
			//If system is closed, cancel request submission
			if(get_system_setting("posting") != "yes")
			{
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"System is closed");
				die("<script type=\"text/javascript\">window.location = \"index.php?status=1\"</script>");
			}
			//If system is in overload mode, cancel request submission
            if(system_in_overload() === true)
            {
                write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"System is in overflow mode");
                die("<script type=\"text/javascript\">window.location = \"index.php?status=6\"</script>");
            }
			//If user is banned, cancel submission
			if(is_user_banned($_SESSION['uname']) === true || is_ip_banned($_SERVER['REMOTE_ADDR']) === true)
			{
                write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"User is banned");
                die("<script type=\"text/javascript\">window.location = \"index.php?status=2\"</script>");
			}
			//If user has a pending request or is locked out, cancel submission
			if(pendingrequest() === true || user_lockout() === true)
			{
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"User attempting to submit too many requests");
				die("<script type=\"text/javascript\">window.location = \"index.php?status=4\"</script>");
			}
			//Check ONLY if no override has been set!
			if($reqid !== false)
			{
				//If request is already on the system, cancel submission
				if(current_request($list,$reqid) === true)
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"User attempting to submit request that is already on the list");
					die("<script type=\"text/javascript\">window.location = \"index.php?status=5\"</script>");
				}
			}
			//If an override was submitted (whether it was processed or not), cancel submission
			if($override != "" && get_system_setting("open") == "no" && get_system_setting("light") == "no")
			{
                write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"User attempting to submit custom request when not allowed");
                die("<script type=\"text/javascript\">window.location = \"index.php?status=8\"</script>");
			}
            
            //Check submitted password if required
            if(get_system_setting("passreq") == "yes")
            {
                if(!isset($_POST['password']) || validate_request_password($_POST['password']) !== true)
                {
                    //Wrong password submitted
                    write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Request password missing or incorrect");
                    if(get_system_setting("baninvpass") == "yes")
                    {
                        if(isset($_POST['autoban']))
                        {
                            $autoban=preg_replace("/[^0-9]/","",$_POST['autoban']);
                        }
                        else
                        {
                            $autoban=0;
                        }
                        //Log this abrogation of system laws
                        write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Auto ban caught user $ip submitting invalid password, blocked $autoban times previously");
                        //Increment count
                        $autoban++;
                        if($autoban >= get_system_setting("beforeban"))
                        {
                            //Ban the user by IP address
                            write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Auto ban banned user $ip");
                            ban_ip($ip,"Automatically banned by the MRS for repeated submissions with invalid password.");
                            die("<script type=\"text/javascript\">window.location = \"index.php?status=2\"</script>");
                        }
                        else
                        {
                            die("<script type=\"text/javascript\">window.location = \"post.php?list=" . $_POST['list'] . "&req=" . $_POST['reqid'] . "&autoban=$autoban&reason=2\"</script>");
                        }
                    }
                    else
                    {
                        die("<script type=\"text/javascript\">window.location = \"post.php?list=" . $_POST['list'] . "&req=" . $_POST['reqid'] . "&pass=wrong\"</script>");
                    }
                }
            }
			
			//Check auto banning status
			if(autoban($name) !== true)
			{
				//Get current number of blocked attempts
				if(isset($_POST['autoban']))
				{
					$autoban=preg_replace("/[^0-9]/","",$_POST['autoban']);
				}
				else
				{
					$autoban=0;
				}
				//Log this abrogation of system laws
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Auto ban caught user $ip attempting to use username $name, blocked $autoban times previously");
				//Increment count
				$autoban++;
				if($autoban >= get_system_setting("beforeban"))
				{
					//Ban the user by IP address
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Auto ban banned user $ip");
					ban_ip($ip,"Automatically banned by the MRS for repeated attempts at using prohibited words in username.");
					die("<script type=\"text/javascript\">window.location = \"index.php?status=2\"</script>");
				}
				else
				{
					die("<script type=\"text/javascript\">window.location = \"post.php?list=" . $_POST['list'] . "&req=" . $_POST['reqid'] . "&autoban=$autoban&reason=0\"</script>");
				}
			}
			
			//Get new post ID
			$postid=increment_post_count();
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Set new post ID");
			
			//Write request
			$debug=write_request($postid,$name,$ip,date(get_system_setting("datetime")),stripcslashes($request),0,"None",stripcslashes($comment),$filename);
			if($debug === false)
			{
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to write post $postid");
				die("<script type=\"text/javascript\">window.location = \"index.php?status=7\"</script>");
			}
			else
			{
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Successfully wrote post $postid");
				//Mark post last requested date
				if($override == "" && isset($list) && isset($reqid))
				{
					request_song($list,$reqid);
				}
                if(get_system_setting("rss") == "yes")
                {
                    $debug=write_rss_entry($postid,$name,date(get_system_setting("datetime")),stripcslashes($request));
                    if($debug === false)
                    {
                        trigger_error("Failed to write to RSS feed. Microwave the feed and try again.",E_USER_WARNING);
                        write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to add post $postid to RSS feed");
                    }
                    else
                    {
                        write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Added post $postid to RSS feed");
                    }
                }
				die("<script type=\"text/javascript\">window.location = \"index.php?status=0\"</script>");
			}
		}
		else
		{
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Visited posting page");
			$anon=get_system_setting("anon");
			$open=get_system_setting("open");
			$comments=get_system_setting("comments");
			$light=get_system_setting("light");
			
			$list="";
			$reqid="";
			if(isset($_GET['list']) && isset($_GET['req']))
			{
				$list=preg_replace("/[^A-Za-z0-9]/","",$_GET['list']);
				$reqid=preg_replace("/[^0-9]/","",$_GET['req']);
			}
			if(file_exists("songs/$list.txt") && $reqid != "")
			{
				$song=get_song($list,$reqid);
				$request=array("Artist" => "SystemHad","Title" => "OneJob");
				$rawfields=explode("|",get_system_setting("songformat"));
				$fields=array();
				foreach($rawfields as $field)
				{
					if(strpos($field,"*") === false)
					{
						$fields[]=$field;
					}
				}
				$fdisplay=explode("|",get_system_setting("songformathr"));
				if(count($fields) > 0)
				{
					for($i=0;$i<count($fields);$i++)
					{
						if(isset($song[$fields[$i]]) && $song[$fields[$i]] != "")
						{
							$request[$fdisplay[$i]]=$song[$fields[$i]];
						}
					}
				}
			}
			else
			{
				$reqid=false;
			}
			
			if(isset($_GET['autoban']))
			{
				$autoban=preg_replace("/[^0-9]/","",$_GET['autoban']);
				$reason="microwaving someone's microphone";
				if(isset($_GET['reason']))
				{
					switch(preg_replace("/[^0-9]/","",$_GET['reason']))
					{
						case 0:
						$reason="request contains one or more blocked words";
						break;
						
						case 1:
						$reason="user is attempting to stuff the ballot box";
						break;
						
						case 2:
						$reason="invalid password submitted";
						break;
					}
				}
				trigger_error("The MRS blocked your attempted request for the following reason: $reason. This has happened $autoban time(s). Double-check your submission or risk being microwaved.",E_USER_WARNING);
			}
            if(isset($_GET['pass']))
            {
                trigger_error("The MRS blocked your attempted request for the following reason: invalid password submitted. Double-check your submission.",E_USER_WARNING);
            }
			
			$posting=true;
			if(is_user_banned($_SESSION['uname']) === true || is_ip_banned($_SERVER['REMOTE_ADDR']) === true)
			{
				trigger_error("You are banned from this system and cannot submit a request. Be gone.",E_USER_ERROR);
				$posting=false;
			}
			elseif(get_system_setting("posting") == "no")
			{
				trigger_error("We're like, uh, closed or something, so you cannot submit a request. Go away.",E_USER_ERROR);
				$posting=false;
			}
			elseif(system_in_overload() === true)
			{
				trigger_error("This system is overloaded with requests and has shut down as a precaution. Throw your peers under the bus and try again later.",E_USER_ERROR);
				$posting=false;
			}
			elseif(pendingrequest() === true || user_lockout() === true)
			{
				trigger_error("Slow your role, breh! You can make more requests later!",E_USER_ERROR);
				$posting=false;
			}
			elseif($reqid === false && $open == "no" && $light == "no")
			{
				trigger_error("Attempt to stuff the ballet (sic) box detected and denied. Follow the rules!",E_USER_ERROR);
				$posting=false;
			}
		}
	}
	else
	{
		set_timezone();
		if(isset($_POST['s']) && $_POST['s'] == "y")
		{
			/* SUBMISSION STEPS
			-Sanitize everything
			-Get necessary settings
			-Check against system settings
			-Write request */
			
			//Initialize variables
			$name="";
			$ip="";
			$request="";
			$comment="";
			$filename="";
			
			//Get and sanitize name
			if(isset($_POST['name']) && $_POST['name'] != "")
			{
				$name=trim(preg_replace("/[^A-Za-z0-9 ]/", "", $_POST['name']));
				$_SESSION['uname']=$name;
			}
			else
			{
				$name=$_SESSION['uname'];
			}
			//If anonymous flag submitted and allowed, set name to anonymous
			if(isset($_POST['anon']) && $_POST['anon'] == "y" && get_system_setting("anon") == "yes")
			{
				$name="Anonymous";
			}
			
			//Set IP address
			$ip=$_SERVER['REMOTE_ADDR'];
			
			//Set song list
			if(!isset($_POST['list']) || ($list=preg_replace("/[^A-Za-z0-9]/","",$_POST['list'])) == "" || !file_exists("songs/$list.txt"))
			{
				$list="";
			}
			
			//Get request
			if($list != "" && isset($_POST['reqid']) && ($reqid=preg_replace("/[^0-9]/","",$_POST['reqid'])) != "")
			{
				$song=get_song($list,$reqid);
				foreach($song as $key=>$value)
				{
					if($key != "added_to_system" && $key != "request_count" && $key != "last_requested")
					{
						$request.="$key=$value|";
					}
				}
				$request=substr($request,0,-1);
			}
			else
			{
				$reqid="";
			}
			//If override submitted and allowed, set request to override instead
			if(isset($_POST['override']) && htmlspecialchars($_POST['override']) != "" && (get_system_setting("open") == "yes" || get_system_setting("light") == "yes"))
			{
				$override=htmlspecialchars($_POST['override']);
				$request="custom**=$override";
				$list="whocares";
				$reqid=false;
			}
			else
			{
				$override="";
			}
			
			//Get comment
			if(isset($_POST['comment']) && get_system_setting("comments") == "yes")
			{
				$comment=htmlspecialchars($_POST['comment']);
			}
			else
			{
				$comment="";
			}
			
			//Make sure input is valid
			if($name == "" || $ip == "" || $list == "" || $request == "")
			{
                die("<script type=\"text/javascript\">window.location = \"index.php?status=3\"</script>");
			}
			//If system is closed, cancel request submission
			if(get_system_setting("posting") != "yes")
			{
				die("<script type=\"text/javascript\">window.location = \"index.php?status=1\"</script>");
			}
			//If system is in overload mode, cancel request submission
            if(system_in_overload() === true)
            {
                die("<script type=\"text/javascript\">window.location = \"index.php?status=6\"</script>");
            }
			//If user is banned, cancel submission
			if(is_user_banned($_SESSION['uname']) === true || is_ip_banned($_SERVER['REMOTE_ADDR']) === true)
			{
                die("<script type=\"text/javascript\">window.location = \"index.php?status=2\"</script>");
			}
			//If user has a pending request or is locked out, cancel submission
			if(pendingrequest() === true || user_lockout() === true)
			{
				die("<script type=\"text/javascript\">window.location = \"index.php?status=4\"</script>");
			}
			//Check ONLY if no override has been set!
			if($reqid !== false)
			{
				//If request is already on the system, cancel submission
				if(current_request($list,$reqid) === true)
				{
					die("<script type=\"text/javascript\">window.location = \"index.php?status=5\"</script>");
				}
			}
			//If an override was submitted (whether it was processed or not), cancel submission
			if($override != "" && get_system_setting("open") == "no" && get_system_setting("light") == "no")
			{
                die("<script type=\"text/javascript\">window.location = \"index.php?status=8\"</script>");
			}
            
            //Check submitted password if required
            if(get_system_setting("passreq") == "yes")
            {
                if(!isset($_POST['password']) || validate_request_password($_POST['password']) !== true)
                {
                    //Wrong password submitted
                    if(get_system_setting("baninvpass") == "yes")
                    {
                        if(isset($_POST['autoban']))
                        {
                            $autoban=preg_replace("/[^0-9]/","",$_POST['autoban']);
                        }
                        else
                        {
                            $autoban=0;
                        }
                        //Increment count
                        $autoban++;
                        if($autoban >= get_system_setting("beforeban"))
                        {
                            //Ban the user by IP address
                            ban_ip($ip,"Automatically banned by the MRS for repeated submissions with invalid password.");
                            die("<script type=\"text/javascript\">window.location = \"index.php?status=2\"</script>");
                        }
                        else
                        {
                            die("<script type=\"text/javascript\">window.location = \"post.php?list=" . $_POST['list'] . "&req=" . $_POST['reqid'] . "&autoban=$autoban&reason=2\"</script>");
                        }
                    }
                    else
                    {
                        die("<script type=\"text/javascript\">window.location = \"post.php?list=" . $_POST['list'] . "&req=" . $_POST['reqid'] . "&pass=wrong\"</script>");
                    }
                }
            }
			
			//Check auto banning status
			if(autoban($name) !== true)
			{
				//Get current number of blocked attempts
				if(isset($_POST['autoban']))
				{
					$autoban=preg_replace("/[^0-9]/","",$_POST['autoban']);
				}
				else
				{
					$autoban=0;
				}
				//Increment count
				$autoban++;
				if($autoban >= get_system_setting("beforeban"))
				{
					//Ban the user by IP address
					ban_ip($ip,"Automatically banned by the MRS for repeated attempts at using prohibited words in username.");
					die("<script type=\"text/javascript\">window.location = \"index.php?status=2\"</script>");
					
				}
				else
				{
					die("<script type=\"text/javascript\">window.location = \"post.php?list=" . $_POST['list'] . "&req=" . $_POST['reqid'] . "&autoban=$autoban&reason=0\"</script>");
				}
			}
			
			//Get new post ID
			$postid=increment_post_count();
			
			//Write request
			$debug=write_request($postid,$name,$ip,date(get_system_setting("datetime")),stripcslashes($request),0,"None",stripcslashes($comment),$filename);
			if($debug === false)
			{
				die("<script type=\"text/javascript\">window.location = \"index.php?status=7\"</script>");
			}
			else
			{
				//Mark post last requested date
				if($override == "" && isset($list) && isset($reqid))
				{
					request_song($list,$reqid);
				}
                if(get_system_setting("rss") == "yes")
                {
                    $debug=write_rss_entry($postid,$name,date(get_system_setting("datetime")),stripcslashes($request));
                    if($debug === false)
                    {
                        trigger_error("Failed to write to RSS feed. Microwave the feed and try again.",E_USER_WARNING);
                    }
                }
				die("<script type=\"text/javascript\">window.location = \"index.php?status=0\"</script>");
			}
		}
		else
		{
			$anon=get_system_setting("anon");
			$open=get_system_setting("open");
			$comments=get_system_setting("comments");
			$light=get_system_setting("light");
			
			$list="";
			$reqid="";
			$request=false;
			
			if(isset($_GET['list']) && isset($_GET['req']))
			{
				$list=preg_replace("/[^A-Za-z0-9]/","",$_GET['list']);
				$reqid=preg_replace("/[^0-9]/","",$_GET['req']);
			}
			if(file_exists("songs/$list.txt") && $reqid != "")
			{
				$song=get_song($list,$reqid);
				$request=array("Artist" => "SystemHad","Title" => "OneJob");
				$rawfields=explode("|",get_system_setting("songformat"));
				$fields=array();
				foreach($rawfields as $field)
				{
					if(strpos($field,"*") === false)
					{
						$fields[]=$field;
					}
				}
				$fdisplay=explode("|",get_system_setting("songformathr"));
				if(count($fields) > 0)
				{
					for($i=0;$i<count($fields);$i++)
					{
						if(isset($song[$fields[$i]]) && $song[$fields[$i]] != "")
						{
							$request[$fdisplay[$i]]=$song[$fields[$i]];
						}
					}
				}
			}
			else
			{
				$reqid=false;
			}
			
			if(isset($_GET['autoban']))
			{
				$autoban=preg_replace("/[^0-9]/","",$_GET['autoban']);
				$reason="microwaving someone's microphone";
				if(isset($_GET['reason']))
				{
					switch(preg_replace("/[^0-9]/","",$_GET['reason']))
					{
						case 0:
						$reason="request contains one or more blocked words";
						break;
						
						case 1:
						$reason="user is attempting to stuff the ballot box";
						break;
						
						case 2:
						$reason="invalid password submitted";
						break;
					}
				}
				trigger_error("The MRS blocked your attempted request for the following reason: $reason. This has happened $autoban time(s). Double-check your submission or risk being microwaved.");
			}
            if(isset($_GET['pass']))
            {
                trigger_error("The MRS blocked your attempted request for the following reason: invalid password submitted. Double-check your submission.",E_USER_WARNING);
            }
			
			$posting=true;
			if(is_user_banned($_SESSION['uname']) === true || is_ip_banned($_SERVER['REMOTE_ADDR']) === true)
			{
				trigger_error("You are banned from this system and cannot submit a request. Be gone.",E_USER_ERROR);
				$posting=false;
			}
			elseif(get_system_setting("posting") == "no")
			{
				trigger_error("We're like, uh, closed or something, so you cannot submit a request. Go away.",E_USER_ERROR);
				$posting=false;
			}
			elseif(system_in_overload() === true)
			{
				trigger_error("This system is overloaded with requests and has shut down as a precaution. Throw your peers under the bus and try again later.",E_USER_ERROR);
				$posting=false;
			}
			elseif(pendingrequest() === true || user_lockout() === true)
			{
				trigger_error("Slow your role, breh! You can make more requests later!",E_USER_ERROR);
				$posting=false;
			}
			elseif($reqid === false && $open == "no" && $light == "no")
			{
				trigger_error("Attempt to stuff the ballet (sic) box detected and denied. Follow the rules!",E_USER_ERROR);
				$posting=false;
			}
		}
	}
  ?>
  <form action="post.php" method="post">
  <input type="hidden" name="s" value="y">
  Name: <input type="text" name="name" value="<?php echo $_SESSION['uname']; ?>"<?php if($anon == "no") { echo(" required=\"required\""); } ?>> OR <input type="checkbox" name="anon" value="y" <?php if($anon == "no") { echo("disabled=\"disabled\""); } ?>>Anonymous<br>
  IP Address: <?php echo $_SERVER['REMOTE_ADDR']; ?> (this WILL be submitted with your request!)<br>
  <input type="hidden" name="reqid" value="<?php echo $reqid; ?>">
  <input type="hidden" name="list" value="<?php echo $list; ?>">
  <input type="hidden" name="autoban" <?php if(isset($autoban)) { echo "value=\"$autoban\""; } else { echo "disabled=\"disabled\""; } ?>>
  Request:<br>
  <?php
	if(!empty($request))
	{
		foreach($request as $key=>$value)
		{
			echo("$key: $value<br>\r\n");
		}
	}
  ?>
  Request this instead: <input type="text" size="50" name="override" <?php if($open != "yes" && $light != "yes") { echo(" value=\"Action not allowed\" disabled=\"disabled\""); } elseif($light == "yes" || empty($request)) { echo ("required=\"required\""); } if(isset($override)) { echo ("value=\"$override\""); } ?>><br>
  Comment (optional):<br>
  <textarea name="comment" <?php if($comments == "no") { echo "disabled=\"disabled\""; } ?> rows="10" cols="50"></textarea><br>
  Submission password: <input type="password" name="password" <?php if(get_system_setting("passreq") == "no") { echo ("disabled=\"disabled\""); } else  { echo ("required=\"required\""); } ?>><br>
  <input type="submit" value="Make request" <?php if($posting === false) { echo "disabled=\"disabled\""; } ?>><input type="button" value="Back to search" onclick="window.location.href='select.php'"><input type="button" value="Cancel" onclick="window.location.href='index.php'">
  </form>
  </body>
</html>