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
    <title><?php echo $sysname; ?>Music Request System-View Log</title>
	<script type="text/javascript" src="backend/jquery.js"></script>
	<script type="text/javascript" src="backend/tablesorter.js"></script>
	<link rel="stylesheet" href="backend/tsstyle/style.css" type="text/css" media="print, projection, screen" />
	<script type="text/javascript">
	$(function() {
		$("#logtable").tablesorter();
	});
	</script>
    
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
	
	table,th,td { border: 1px solid #000000; }
    -->
    </style>
  </head>
<?php
	if(isset($_GET['log']) && $_GET['log'] != "")
	{
		//Sanitize log number!
		$log=preg_replace("/[^0-9]/", "", $_GET['log']);
		//Display a log file
		if(is_logging_enabled() === true)
		{
			set_timezone();
			//Logging is enabled
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Attempting to view logfile $log");
			if(securitycheck() === false)
			{
				//User is not administrator
				die("You are not an administrator. <a href=\"login.php?ref=viewlog\">Sign in</a> or <a href=\"index.php\">Cancel</a>.");
			}
			//Get log contents
			$logcontents=get_log($log);
			if($logcontents != "")
			{
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Got contents of log file $log");
			}
			else
			{
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to get contents of log file $log: file does not exist");
			}
		}
		else
		{
			if(securitycheck() === false)
			{
				//User is not administrator
				die("You are not an administrator. <a href=\"login.php?ref=viewlog\">Sign in</a> or <a href=\"index.php\">Cancel</a>.");
			}
			//Get log contents
			$logcontents=get_log($log);
		}
	}
	else
	{
		if(is_logging_enabled() === true)
		{
			set_timezone();
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Visited logfile viewing page");
			if(securitycheck() === false)
			{
				//User is not administrator
				die("You are not an administrator. <a href=\"login.php?ref=viewlog\">Sign in</a> or <a href=\"index.php\">Cancel</a>.");
			}
			//Get list of all logs
			$logs=get_all_logs();
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Got list of all logs");
		}
		else
		{
			if(securitycheck() === false)
			{
				//User is not administrator
				die("You are not an administrator. <a href=\"login.php?ref=viewlog\">Sign in</a> or <a href=\"index.php\">Cancel</a>.");
			}
			//Get list of all logs
			$logs=get_all_logs();
		}
	}
?>
  <body>
  <h1 style="text-align:center; text-decoration:underline;"><?php echo $sysname; ?>MRS-View Logfile</h1>
  <?php
	//Check if log file needs displaying
	if(isset($logcontents) && $logcontents != "")
	{
		//Split logfile into log entries
		$logcontents=explode("\r\n",$logcontents);
		//Begin outputting table
		echo ("<table id=\"logtable\" class=\"tablesorter\">\r\n<thead>\r\n<tr>\r\n<th>IP Address</th>\r\n<th>Time (hh:mm:ss)</th>\r\n<th>Action</th>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n");
		//Loop through list of contents
		foreach($logcontents as $entry)
		{
			//Verify that entry is non-blank
			if($entry != "")
			{
				//Create row
				echo ("<tr>\r\n");
				//Output IP address into cell
				echo ("<td>" . substr($entry,0,strpos($entry,"at")-1) . "</td>\r\n");
				//Get time and format it
				$time=strtotime(substr($entry,strpos($entry,"at")+3,(strpos($entry,": ")-strpos($entry,"at")-3)));
				//Output time into cell
				echo ("<td>" . date("g:i:s A",$time) . "</td>\r\n");
				//Output action into cell
				echo ("<td>" . substr($entry,strpos($entry,": ")+2) . "</td>\r\n");
				//Close row
				echo ("</tr>\r\n");
			}
		}
		//End table output
		echo ("</tbody>\r\n</table>\r\n<p><a href=\"admin.php\">Exit viewer</a></p>\r\n");
	}
	else
	{
		//Display form for selecting logfile to display
		echo ("<form method=\"get\" action=\"viewlog.php\">\r\nSelect the log you wish to view:&nbsp;\r\n<select name=\"log\">\r\n<option value=\"\">-Select one-</option>\r\n");
		foreach($logs as $log)
		{
			echo ("<option value=\"" . $log . "\">" . $log . "</option>\r\n");
		}
		echo ("</select><br>\r\n<input type=\"submit\"><input type=\"button\" value=\"Cancel\" onclick=\"window.location.href='admin.php'\">\r\n</form>\r\n");
	}
  ?>
  </body>
</html>