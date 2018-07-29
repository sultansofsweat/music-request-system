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
	if(is_logging_enabled() === true)
	{
		//Change the timezone
		set_timezone();
		write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Visited rules page");
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
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="generator" content="CoffeeCup HTML Editor (www.coffeecup.com)">
    <meta name="dcterms.created" content="Thu, 31 Jul 2014 03:23:24 GMT">
	<link rel="shortcut icon" href="backend/favicon.ico">
    <title>Request Rules</title>
    
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
  <h1 style="text-align:center;">Request Rules</h1>
  <hr>
  <p>The general rules for requests can be found <a href="http://greyghost.mooo.com/live-stream-rules.html">here</a>, however this request system has a few of it's own. This page will also serve to re-iterate a few rules as well.</p>
  <ul>
  <?php
	//Get rules
    $rules=get_rules();
	//Output rules as list items
	foreach($rules as $rule)
	{
		echo "<li>" . stripcslashes($rule) . "</li>\r\n";
	}
  ?>
  </ul>
  <p>As a helpful tip, when making open requests, please list the album and/or the year if you can. I have multiple versions of many songs, and it would help me find the correct version faster.</p>
  <hr>
  <p><a href="index.php">Go back</a></p>
  </body>
</html>