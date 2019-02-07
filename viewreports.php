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
		$("#reportstable").tablesorter();
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
	if(isset($_GET['removed']))
	{
		switch($_GET['removed'])
		{
			case "yes":
			trigger_error("Successfully removed report from list.");
			break;
			default:
			trigger_error("Failed to remove report from list. Microwave it and try again.",E_USER_WARNING);
			break;
		}
	}
	if(is_logging_enabled() === true)
	{
		set_timezone();
		write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Visited logfile viewing page");
		if(securitycheck() === false)
		{
			//User is not administrator
			die("You are not an administrator. <a href=\"login.php?ref=viewreports\">Sign in</a> or <a href=\"index.php\">Cancel</a>.");
		}
		if(isset($_GET['remove']))
		{
			//Sanitize!
			$rm=preg_replace("/[^0-9\-]/","",$_GET['remove']);
			$debug=remove_report($rm);
			if($debug === true)
			{
				//File removed
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Removed file $rm");
				$removed="yes";
			}
			else
			{
				//File not removed
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Did not remove file $rm");
				$removed="no";
			}
			echo ("<script type=\"text/javascript\">window.location = \"viewreports.php?removed=$removed\"</script>");
		}
	}
	else
	{
		if(securitycheck() === false)
		{
			//User is not administrator
			die("You are not an administrator. <a href=\"login.php?ref=viewreports\">Sign in</a> or <a href=\"index.php\">Cancel</a>.");
		}
		if(isset($_GET['remove']))
		{
			//Sanitize!
			$rm=preg_replace("/[^0-9\-]/","",$_GET['remove']);
			$debug=remove_report($rm);
			if($debug === true)
			{
				//File removed
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Removed file $rm");
				$removed="yes";
			}
			else
			{
				//File not removed
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Did not remove file $rm");
				$removed="no";
			}
			echo ("<script type=\"text/javascript\">window.location = \"viewreports.php?removed=$removed\"</script>");
		}
	}
?>
  <body>
  <h1 style="text-align:center; text-decoration:underline;"><?php echo $sysname; ?>MRS-View Reports</h1>
  <table id="reportstable" class="tablesorter">
  <thead>
  <tr>
  <th>Post ID</th>
  <th>Username</th>
  <th>Posted</th>
  <th>Request</th>
  <th>Reportee</th>
  <th>Reported</th>
  <th>Additional Comment</th>
  <th></th>
  </tr>
  </thead>
  <tbody>
  <?php
	$reports=get_reports();
	if(count($reports) > 0)
	{
		foreach($reports as $report)
		{
			while(count($report) < 8)
			{
				$report[]="";
			}
			echo ("<tr>\r\n<td>" . $report[0] . "</td>\r\n<td>" . $report[1] . "</td>\r\n<td>" . $report[2] . "</td>\r\n<td>" . $report[3] . "</td>\r\n<td>" . $report[4] . "</td>\r\n<td>" . $report[5] . "</td>\r\n<td>" . $report[6] . "</td>\r\n<td><a href=\"viewreports.php?remove=" . $report[7] . "\">Close</a></td>\r\n</tr>\r\n");
		}
	}
	else
	{
		echo ("<tr>\r\n<td colspan=\"8\">No reports at this time</td>\r\n</tr>\r\n");
	}
  ?>
  </tbody>
  </table>
  <p><a href="admin.php">Go home</a></p>
  </body>
</html>