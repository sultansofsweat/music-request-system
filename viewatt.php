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
    <title><?php echo $sysname; ?>Music Request System-View Login Attempts</title>
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
  <body>
  <h1 style="text-align:center; text-decoration:underline;"><?php echo $sysname; ?>MRS-View Login Attempts</h1>
<?php
	if(is_logging_enabled() === true)
	{
		//Logging enabled
		set_timezone();
		write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Visited login attempt log viewing page");
		if(securitycheck() === false)
		{
			//User is not administrator
			die("You are not an administrator. <a href=\"login.php?ref=viewatt\">Sign in</a> or <a href=\"index.php\">Cancel</a>.");
		}
		if(isset($_GET['markread']))
		{
			//Mark attempts as read
			$debug=mark_attempts_as_read();
			if($debug === true)
			{
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Marked login attempt log as read");
				trigger_error("Successfully marked all log entries as read.");
			}
			else
			{
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to mark login attempt log as read");
				trigger_error("Failed to mark all log entries as read. Something probably got dumped into a big pile of shaving cream.",E_USER_WARNING);
			}
		}
		elseif(isset($_GET['clear']))
		{
			//Clear attempt log
			$debug=clear_login_log();
			if($debug === true)
			{
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Cleared login attempt log");
				trigger_error("Successfully cleared log.");
			}
			else
			{
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to clear login attempt log");
				trigger_error("Failed to clear log. Something probably got dumped into a big pile of shaving cream.",E_USER_WARNING);
			}
		}
		//Get all logs
		$logins=get_login_attempts();
	}
	else
	{
		//Logging disabled
		if(securitycheck() === false)
		{
			//User is not administrator
			die("You are not an administrator. <a href=\"login.php?ref=viewatt\">Sign in</a> or <a href=\"index.php\">Cancel</a>.");
		}
		if(isset($_GET['markread']))
		{
			//Mark attempts as read
			$debug=mark_attempts_as_read();
			if($debug === true)
			{
				trigger_error("Successfully marked all log entries as read.");
			}
			else
			{
				trigger_error("Failed to mark all log entries as read. Something probably got dumped into a big pile of shaving cream.",E_USER_WARNING);
			}
		}
		elseif(isset($_GET['clear']))
		{
			//Clear attempt log
			$debug=clear_login_log();
			if($debug === true)
			{
				trigger_error("Successfully cleared log.");
			}
			else
			{
				trigger_error("Failed to clear log. Something probably got dumped into a big pile of shaving cream.",E_USER_WARNING);
			}
		}
		//Get all logs
		$logins=get_login_attempts();
	}
?>
  <table id="logtable" class="tablesorter">
	<thead>
		<tr>
			<th>IP Address</th>
			<th>Date (MM/DD/YYYY HH:MM:SS AM/PM)</th>
			<th>Result</th>
			<th>Attempt #</th>
			<th></th>
		</tr>
	</thead>
	<tbody>
	<?php
		//Loop through list of contents
		foreach($logins as $login)
		{
			//Create row
			echo ("<tr>\r\n");
			//Output information
			echo ("<td>" . $login[0] . "</td>\r\n");
			echo ("<td>" . $login[1] . "</td>\r\n");
			echo ("<td>" . $login[2] . "</td>\r\n");
			echo ("<td>" . $login[3] . "</td>\r\n");
			echo ("<td>" . $login[4] . "</td>\r\n");
			//Close row
			echo ("</tr>\r\n");
		}
	?>
	</tbody>
  </table>
  <p><a href="viewatt.php?markread=yes">Mark entries as read</a> | <a href="viewatt.php?clear=yes">Clear log</a> | <a href="admin.php">Exit</a></p>
  </body>
</html>