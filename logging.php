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
	if(is_logging_enabled() === true)
	{
		//Change the timezone
		set_timezone();
		write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Visited log information page");
	}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="generator" content="CoffeeCup HTML Editor (www.coffeecup.com)">
    <meta name="dcterms.created" content="Thu, 31 Jul 2014 03:23:24 GMT">
	<link rel="shortcut icon" href="backend/favicon.ico">
    <title><?php echo $sysname; ?>Music Request System-What Is Logging</title>
    
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
    <!--[if IE]>
    <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
  </head>
  <body>
  <h1 style="text-align:center;">Logging Is Enabled On This System: Frequently Asked Questions</h1>
  <hr>
  <?php
	//Change the timezone
	set_timezone();
	//Get IP and time
	$ip=$_SERVER['REMOTE_ADDR'];
	$time=date("g:i:s");
  ?>
  <h3>What does this mean?</h3>
  <p>System logging will write an entry to a central "log file" for each time each visitor does something on the MRS. This includes visiting a page, making a request, attempting to perform administrative duties, and other related actions.</p>
  <h3>What is logged?</h3>
  <p>Each log entry contains your IP address, the current time, and the action. For example, your IP address is "<?php echo $ip; ?>", and the present time is "<?php echo $time; ?>". When you visit this page, the log entry looks like this: "<?php echo ($ip . " at " . $time . ": Visited log information page"); ?>".</p>
  <h3>Where are these logs stored? How long are they stored?</h3>
  <p>Logs are stored within the MRS, the exact location being unmentioned for security purposes. A new file is created for each day. Logs will stay in place until they are removed by the system administrator.</p>
  <h3>Why is logging enabled?</h3>
  <p>Logging could be enabled for any reason by the system administrator. By default, a newly set up MRS has logging enabled, and the administrator could have forgotten to change this. Logging is useful for quality control purposes as well. The most popular reason for logging to be enabled is suspected abuse of the system.</p>
  <h3>Can I prevent my information from being logged?</h3>
  <p>No. There is no "anonymous" switch, if you do anything on this system while logging is enabled it will write logs. The only way to prevent yourself from being tracked by this system is to simply not use it.</p>
  <h3>How long does a system make logs for?</h3>
  <p>From the time logging is enabled to the time it is disabled. There is no set time limit.</p>
  <h3>How can I get logging disabled if I think it should be?</h3>
  <p>Contact the system administrator. Requests for such may or may not be honoured, this is up to the specific system administrator.</p>
  <h3>Can I access the logs?</h3>
  <p>Without administrator privileges, no. Attempting to break into the system to view and/or manipulate logs is prohibited.</p>
  <hr>
  <p><a href="index.php">Go back</a></p>
  </body>
</html>