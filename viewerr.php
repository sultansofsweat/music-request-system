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
	//Function for getting the error severity
	function get_error_severity($number)
	{
		switch($number)
		{
			case 4:
			return "Critical";
			break;
			
			case 1:
			case 16:
			case 64:
			case 256:
			case 4096:
			return "Error";
			break;
			
			case 2:
			case 32:
			case 128:
			case 512:
			case 2048:
			return "Warning";
			break;
			
			case 8:
			case 1024:
			return "Notice";
			break;
			
			case 8192:
			case 16384:
			return "Deprecation";
			break;
			
			default:
			return "Unknown";
			break;
		}
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
    <title><?php echo $sysname; ?>Music Request System-View Error Logs</title>
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
		if(is_logging_enabled())
		{
			set_timezone();
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Attempting to view error log $log");
			if(securitycheck() === false)
			{
				//User is not administrator
				die("You are not an administrator. <a href=\"login.php?ref=viewerr\">Sign in</a> or <a href=\"index.php\">Cancel</a>.");
			}
			//Get error log
			$logcontents=get_error_log($log);
			if($logcontents == "")
			{
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to get contents of error log $log; does not exist");
			}
			else
			{
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Got contents of error log $log");
			}
		}
		else
		{
			if(securitycheck() === false)
			{
				//User is not administrator
				die("You are not an administrator. <a href=\"login.php?ref=viewerr\">Sign in</a> or <a href=\"index.php\">Cancel</a>.");
			}
			//Get error log
			$logcontents=get_error_log($log);
		}
	}
	else
	{
		if(is_logging_enabled() === true)
		{
			set_timezone();
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Visited error log viewing page");
			if(securitycheck() === false)
			{
				//User is not administrator
				die("You are not an administrator. <a href=\"login.php?ref=viewerr\">Sign in</a> or <a href=\"index.php\">Cancel</a>.");
			}
			//Get list of all logs
			$logs=get_all_error_logs();
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Got list of all error logs");
		}
		else
		{
			if(securitycheck() === false)
			{
				//User is not administrator
				die("You are not an administrator. <a href=\"login.php?ref=viewerr\">Sign in</a> or <a href=\"index.php\">Cancel</a>.");
			}
			//Get list of all logs
			$logs=get_all_error_logs();
		}
	}
?>
  <body>
  <h1 style="text-align:center; text-decoration:underline;"><?php echo $sysname; ?>MRS-View Error Log</h1>
  <?php
	//Check if log file needs displaying
	if(isset($logcontents) && $logcontents != "")
	{
		//Split logfile into log entries
		$logcontents=explode("\r\n",$logcontents);
		//Begin outputting table
		echo ("<table id=\"logtable\" class=\"tablesorter\">\r\n<thead>\r\n<tr>\r\n<th>Time (hh:mm:ss)</th>\r\n<th>Severity [Code]</th>\r\n<th>File</th>\r\n<th>Line</th>\r\n<th>Message</th>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n");
		//Loop through list of contents
		foreach($logcontents as $entry)
		{
			//Verify that entry is non-blank
			if($entry != "")
			{
				//Split entry into parts
				$entry=explode("|",$entry);
				//Create row
				echo ("<tr>\r\n");
				//Output time into cell
				echo ("<td>" . $entry[0] . "</td>\r\n");
				//Get severity
				$severity=get_error_severity($entry[1]);
				//Output severity and error level
				echo ("<td>$severity [" . $entry[1] . "]</td>\r\n");
				//Output filename
				echo ("<td>" . $entry[2] . "</td>\r\n");
				//Output line number
				echo ("<td>" . $entry[3] . "</td>\r\n");
				//Output message
				echo ("<td>" . $entry[4] . "</td>\r\n");
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
		echo ("<form method=\"get\" action=\"viewerr.php\">\r\nSelect the error log you wish to view:&nbsp;\r\n<select name=\"log\">\r\n<option value=\"\">-Select one-</option>\r\n");
		foreach($logs as $log)
		{
			echo ("<option value=\"" . $log . "\">" . $log . "</option>\r\n");
		}
		echo ("</select><br>\r\n<input type=\"submit\"><input type=\"button\" value=\"Cancel\" onclick=\"window.location.href='admin.php'\">\r\n</form>\r\n");
	}
  ?>
  </body>
</html>