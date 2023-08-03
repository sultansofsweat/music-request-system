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
    <title><?php echo $sysname; ?>Music Request System-View Deprecation Message Log</title>
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
	$logcontents="";
	set_timezone();
	write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Attempting to view deprecation message log");
	if(securitycheck() === false)
	{
		//User is not administrator
		die("You are not an administrator. <a href=\"login.php?ref=viewdep\">Sign in</a> or <a href=\"index.php\">Cancel</a>.");
	}
	//If clear flag is set, clear the logs
	if(!empty($_GET['clear']))
	{
		clear_dep_log();
		write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Cleared deprecation message log");
		trigger_error("Cleared the deprecation message log.");
	}
	//Get error log
	$logcontents=get_dep_log();
	write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Got contents of deprecation message log");
?>
  <body>
  <h1 style="text-align:center; text-decoration:underline;"><?php echo $sysname; ?>MRS-View Error Log</h1>
  <?php
	//Split logfile into log entries
	$logcontents=explode("\r\n",$logcontents);
	//Begin outputting table
	echo ("<table id=\"logtable\" class=\"tablesorter\">\r\n<thead>\r\n<tr>\r\n<th>Time (hh:mm:ss)</th>\r\n<th>File</th>\r\n<th>Line</th>\r\n<th>Message</th>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n");
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
			echo ("<td>" . date("m/d/Y g:i:s A",$entry[0]) . "</td>\r\n");
			//Output filename
			echo ("<td>" . $entry[1] . "</td>\r\n");
			//Output line number
			echo ("<td>" . $entry[2] . "</td>\r\n");
			//Output message
			echo ("<td>" . $entry[3] . "</td>\r\n");
			//Close row
			echo ("</tr>\r\n");
		}
	}
	//End table output
	echo ("</tbody>\r\n</table>\r\n<p><a href=\"viewdep.php?clear=doit\">Clear log</a> <b>note: PERMANENT!!!</b> | <a href=\"admin.php\">Exit viewer</a></p>\r\n");
  ?>
  </body>
</html>