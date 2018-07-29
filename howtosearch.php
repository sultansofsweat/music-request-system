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
  <p>The MRS has a very sophisticated searching tool. As such, it is important to know how to use it.</p>
  <h2>Regular searching</h2>
  <p>Simply entering your search terms (eg: "rough trade") works fine behaves as in previous versions in that it searches all song fields for the string "rough trade", as entered.</p>
  <h2>Advanced searching</h2>
  <p>An advanced search would look something like this: "artist=rough trade, title=high school confidential"<br>
  It is important to note that search terms are <i>cascading</i>, so in the example above, everything with an artist field containing "rough trade" would be matched, and then of those matches, items with a title field containing "high school confidential" would be matched. <b>It is impossible to match one or the other.</b></p>
  <h2>Strict searching</h2>
  <p>A strict search would look something like this: "artist==rough"<br>
  A strict search forces the search engine to give back only exact matches. In the query above, only songs with an artist field of "rough" will show up; the "rough trade" entries that other queries above matched will not be matched.</p>
  <h2>Searching song lists other than the main list</h2>
  <p>Adding "list=&lt;listname&gt;" to the query will tell the engine to search a song list other than the main list.</p>
  <p></p>
  <p>And that's it! Happy searching!<br>
  <a href="post.php">Go back</a></p>
  </body>
</html>