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
    <title><?php echo $sysname; ?>Music Request System-Add To Song List</title>
    
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
<?php
	if(is_logging_enabled() === true)
	{
		set_timezone();
		if(isset($_POST['s']) && $_POST['s'] == "y" && securitycheck() === true)
		{
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Began submitting new songs to list \"main\"");
			$songs=htmlspecialchars($_POST['list']);
			$debug=add_to_song_list("main",$songs);
			if($debug === true)
			{
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Added new songs to list \"main\"");
				trigger_error("Successfully added new songs to list.");
				$songs="";
			}
			else
			{
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to add new songs to list \"main\"");
				trigger_error("Failed to add new songs to list. Check for electrical gremlins and try again.",E_USER_ERROR);
			}
		}
		else
		{
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Visited add page for song list \"main\"");
			$songs="";
			if(securitycheck() === false)
			{
				die("You are not an administrator. <a href=\"login.php?ref=listadd\">Log in</a> or <a href=\"index.php\">cancel</a>.");
			}
		}
	}
	else
	{
		if(isset($_POST['s']) && $_POST['s'] == "y" && securitycheck() === true)
		{
			$songs=htmlspecialchars($_POST['list']);
			$debug=add_to_song_list("main",$songs);
			if($debug === true)
			{
				trigger_error("Successfully added new songs to list.");
				$songs="";
			}
			else
			{
				trigger_error("Failed to add new songs to list. Check for electrical gremlins and try again.",E_USER_ERROR);
			}
		}
		else
		{
			$songs="";
			if(securitycheck() === false)
			{
				die("You are not an administrator. <a href=\"login.php?ref=listadd\">Log in</a> or <a href=\"index.php\">cancel</a>.");
			}
		}
	}
?>
  <h1 style="text-align:center; text-decoration:underline;"><?php echo $sysname; ?>MRS-Add To Song List</h1>
  <p><a href="listedit.php">Edit existing songs</a> or <a href="listimport.php">add songs from file</a> instead.</p>
  <p><b><u>WARNING:</u></b> The format of this list is "<?php echo get_system_setting("songformat"); ?>". Likewise, there are characters (such as &amp; and +) that are not compatible with the request handling mechanisms and should not be used. Not following either of these conventions <b>WILL</b> break the system and summon the program director!<br>
  Count the number of fields in the format string. You need to have this many fields per song in the list, or in other words, you need to have that many '|' characters (less one, of course) per line. Not doing so will probably also summon the program director, and may break other things.</p>
  <form method="post" action="listadd.php">
  <input type="hidden" name="s" value="y">
  <textarea name="list" rows="30" cols="100"><?php echo stripcslashes($songs); ?></textarea><br>
  <input type="submit" value="Add songs"><input type="reset" value="Clear list"><input type="button" value="Cancel" onclick="window.location.href='admin.php'">
  </form>
  </body>
</html>