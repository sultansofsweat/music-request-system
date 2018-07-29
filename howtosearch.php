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
		write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Visited logging page");
		$format=explode("|",get_system_setting("songformat"));
	}
	else
	{
		$format=explode("|",get_system_setting("songformat"));
	}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="generator" content="CoffeeCup HTML Editor (www.coffeecup.com)">
    <meta name="dcterms.created" content="Thu, 31 Jul 2014 03:23:24 GMT">
	<link rel="shortcut icon" href="backend/favicon.ico">
    <title><?php echo $sysname; ?>Music Request System-How To Search</title>
    
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
  <h1 style="text-align:center;">How To Search For Songs On The Music Request System</h1>
  <hr>
  <p>This version of the MRS uses a much more sophisticated searching protocol than previously. It is still possible to search using the old methodology, but it is now also possible to restrict search terms to specific fields, and it is possible to specify multiple search queries.</p>
  <p>Search queries take the following form: "field1=query1, field2=query2, ...", where the fields are as follows:</p>
  <ul>
  <li>any (or omitted)</li>
  <?php
	foreach($format as $item)
	{
		echo("<li>$item</li>\r\n");
	}
  ?>
  </ul>
  <p>Note that this is a strict format; anything other than an '=' sign will be ignored. Likewise, once one query is processed, the next one will ONLY act on the songs the first query found!</p>
  <p>With that said, happy searching!</p>
  <hr>
  <p><a href="post.php">Go back</a></p>
  </body>
</html>