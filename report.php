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
<?php
	//Create reports directory if it doesn't exist
	if(!file_exists("reports"))
	{
		mkdir("reports");
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
    <title><?php echo $sysname; ?>Music Request System-Report Request</title>
    
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
	//Function for determining if user is banned
	function is_banned()
	{
		$iban=is_ip_banned($_SERVER['REMOTE_ADDR']);
		return $iban[0];
	}
	
  	if(is_logging_enabled() === true)
	{
		//Change the timezone
		set_timezone();
		//Logging enabled
		$fh=fopen("log/" . date("Ymd") . ".txt",'a') or die("Failed to open file \"log/" . date("Ymd") . ".txt\" in append mode. It should now be microwaved.");
		if(isset($_POST['confirm']) && $_POST['confirm'] == "y")
		{
			//Make sure IP address is valid
			/*if(!filter_var($_POST['uip'],FILTER_VALIDATE_IP))
			{
				echo ("<script type=\"text/javascript\">window.location = \"index.php?repstatus=3\"</script>");
			}*/
			//Sanitization work
			$id=preg_replace("/[^0-9]/", "", $_POST['id']);
			$username=preg_replace("/[^A-Za-z0-9 ]/", "", $_POST['name']);
			$pdate=preg_replace("/[^0-9\/: ]/", "", $_POST['date']);
			$request=filter_var($_POST['request'],FILTER_SANITIZE_STRING);
			$comment=filter_var($_POST['comment'],FILTER_SANITIZE_STRING);
			//Write report
			$debug=write_report($id,$username,$pdate,$request,$_SERVER['REMOTE_ADDR'],date("m/d/Y g:i A"),$comment);
			if($debug === true)
			{
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Successfully filed report for post $id");
				die("<script type=\"text/javascript\">window.location = \"index.php?repstatus=0\"</script>");
			}
			else
			{
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to file report for post $id");
				die("<script type=\"text/javascript\">window.location = \"index.php?repstatus=1\"</script>");
			}
			/*//Form report contents
			$contents=array($id,$username,$pdate,$request,$_POST['uip'],$rdate,$comment);
			$contents=implode("\r\n",$contents);
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Formed report for post $id");
			//Determine filename
			$i=0;
			while(file_exists("reports/" . $id . "-" . $i . ".txt"))
			{
				$i++;
			}
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Determined filename for report for post $id after $i iterations");
			//Open the file
			$fh2=fopen("reports/" . $id . "-" . $i . ".txt",'w');
			if($fh2)
			{
				//Success
				fwrite($fh2,base64_encode($contents));
				fclose($fh2);
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Successfully filed report for post $id");
			}
			else
			{
				//Failure
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to file report for post $id");
				die ("<script type=\"text/javascript\">window.location = \"index.php?repstatus=1\"</script>");
			}
			//Get out of here
			echo ("<script type=\"text/javascript\">window.location = \"index.php?repstatus=0\"</script>");*/
		}
		elseif(is_banned() === false)
		{
			//Sanitize post number!
			$post=preg_replace("/[^0-9]/", "", $_GET['p']);
			//Get file info
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Visited report page for post $post");
			if(file_exists("posts/" . $post . ".txt"))
			{
				//Post exists
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Obtained details for post $post");
				$contents=explode("\r\n",base64_decode(file_get_contents("posts/" . $post . ".txt")));
			}
			else
			{
				//Post does not exist
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Encountered error obtaining details for post $post");
				die ("<script type=\"text/javascript\">window.location = \"index.php?repstatus=5\"</script>");
			}
			fclose($fh);
		}
		else
		{
			//User is banned
			$post=preg_replace("/[^0-9]/", "", $_GET['p']);
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Visited report page for post $post");
			echo ("<script type=\"text/javascript\">window.location = \"index.php?repstatus=4\"</script>");
		}
	}
	else
	{
		//Change the timezone
		set_timezone();
		//Logging disabled
		if(isset($_POST['confirm']) && $_POST['confirm'] == "y")
		{
			//Sanitization work
			$id=preg_replace("/[^0-9]/", "", $_POST['id']);
			$username=preg_replace("/[^A-Za-z0-9 ]/", "", $_POST['name']);
			$pdate=preg_replace("/[^0-9\/: ]/", "", $_POST['date']);
			$request=filter_var($_POST['request'],FILTER_SANITIZE_STRING);
			$comment=filter_var($_POST['comment'],FILTER_SANITIZE_STRING);
			//Write report
			$debug=write_report($id,$username,$pdate,$request,$_SERVER['REMOTE_ADDR'],date("m/d/Y g:i A"),$comment);
			if($debug === true)
			{
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Successfully filed report for post $id");
				die("<script type=\"text/javascript\">window.location = \"index.php?repstatus=0\"</script>");
			}
			else
			{
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to file report for post $id");
				die("<script type=\"text/javascript\">window.location = \"index.php?repstatus=1\"</script>");
			}
		}
		elseif(is_banned() === false)
		{
			//Sanitize post number!
			$post=preg_replace("/[^0-9]/", "", $_GET['p']);
			//Get file info
			if(file_exists("posts/" . $post . ".txt"))
			{
				//Post exists
				$contents=explode("\r\n",base64_decode(file_get_contents("posts/" . $post . ".txt")));
			}
			else
			{
				//Post does not exist
				die ("<script type=\"text/javascript\">window.location = \"index.php?repstatus=5\"</script>");
			}
		}
		else
		{
			//User is banned
			echo ("<script type=\"text/javascript\">window.location = \"index.php?repstatus=4\"</script>");
		}
	}
  ?>
  <h1 style="text-align:center; text-decoration:underline;"><?php echo $sysname; ?>Music Request System-Report Post #<?php echo $contents[0]; ?></h1>
  <form method="post" action="report.php">
  <input type="hidden" name="confirm" value="y">
  <input type="hidden" name="id" value="<?php echo $contents[0]; ?>">
  <input type="hidden" name="name" value="<?php echo $contents[1]; ?>">
  <input type="hidden" name="ip" value="<?php echo $contents[2]; ?>">
  <input type="hidden" name="date" value="<?php echo $contents[3]; ?>">
  <!--<input type="hidden" name="uip" value="<?php echo $_SERVER['REMOTE_ADDR']; ?>">
  <input type="hidden" name="rdate" value="<?php echo date("m/d/Y g:i A"); ?>">-->
  Request to report: <input type="text" name="request" value="<?php echo $contents[4]; ?>" readonly="readonly"><br>
  Comments:<br>
  <textarea name="comment" required="required" rows="10" cols="50"></textarea><br>
  <input type="submit" value="Confirm"> or <input type="button" value="Cancel" onclick="window.location.href='index.php'">
  </form>
  </body>
</html>