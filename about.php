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
		//Logging enabled on system
		write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Visited about page");
		$verinfo=get_version_information();
		if($verinfo !== false)
		{
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Obtained version information");
		}
		else
		{
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to obtain version information");
			trigger_error("No version information found.",E_USER_WARNING);
			$verinfo=array(array("0","0","0",false),"","January 1, 1970 at 12:00 AM Eastern Time");
		}
		//Get copyright information
		$copyinfo=get_copyright_information();
		if($copyinfo === false)
		{
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to obtain copyright information, assuming it doesn't exist");
			unset($copyinfo);
			$display=false;
		}
		else
		{
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Obtained copyright information");
			$display=true;
		}
	}
	else
	{
		//Logging disabled
		$verinfo=get_version_information();
		if($verinfo === false)
		{
			trigger_error("No version information found.",E_USER_WARNING);
			$verinfo=array(array("0","0","0",false),"","January 1, 1970 at 12:00 AM Eastern Time");
		}
		//Get copyright information
		$copyinfo=get_copyright_information();
		if($copyinfo === false)
		{
			unset($copyinfo);
			$display=false;
		}
		else
		{
			$display=true;
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
    <title><?php echo $sysname; ?>Music Request System-About</title>
    
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
  <h1 style="text-align:center; text-decoration:underline;">About the <?php echo $sysname; ?>Music Request System</h1>
  <h3 style="text-decoration:underline;">Version Information</h3>
  <?php
	echo ("<p>Version: " . $verinfo[0][0] . "." . $verinfo[0][1]);
	if(trim($verinfo[1]) != "")
	{
		echo $verinfo[1];
	}
	echo ("<br>\r\nRevision: " . $verinfo[0][2] . "<br>\r\nReleased: " . $verinfo[2] . "</p>\r\n");
  ?>
  <h3 style="text-decoration:underline;">MRS Software Copyright Information</h3>
  <p>The Music Request System (MRS) is copyright &copy; 2015-2025 Brad Hunter/<a href="http://www.youtube.com/user/carnelprod666" target="_blank">CarnelProd666</a>. The MRS is licensed under the <a href="license.php" target="_blank">DBAD Public License</a>, version 1.1, except for the components listed below. Learn more about the MRS <a href="http://firealarms.mooo.com/mrs" target="_blank">here</a>. Comments should be directed to the system administrator and/or <a href="http://github.com/sultansofsweat" target="_blank">the software writer</a>.</p>
  <p>The MRS makes use of <a href="http://jquery.com/" target="_blank">JQuery</a> and the <a href="https://mottie.github.io/tablesorter/docs/index.html" target="_blank">TableSorter</a> plugin, each of which is copyright their respective owners.<br>
  For systems running on non-compliant PHP versions, the MRS makes use of <a href="https://github.com/ircmaxell/password_compat/" target="_blank">password_compat</a>, produced by ircmaxell and licensed under the <a href="https://github.com/ircmaxell/password_compat/blob/master/LICENSE.md" target="_blank">MIT license</a>.</p>
  <div <?php if(!isset($display) || $display !== true) { echo "style=\"display: none;"; } ?>>
  <h3 style="text-decoration:underline;"><?php echo $sysname; ?>Copyright Information</h3>
  <p><?php if(isset($copyinfo)) { echo nl2br(stripcslashes($copyinfo)); } ?></p>
  </div>
  <p></p>
  <a href="index.php">Go back</a>
  </form>
  </body>
</html>