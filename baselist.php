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
    <title><?php echo $sysname; ?>Music Request System-Set Base List</title>
    
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
<?php
    //Set status
    $status=3;
	//Write the contents of the song list into the base list
	if(is_logging_enabled() === true)
	{
		//Logging enabled
		//Change the timezone
		set_timezone();
		if(securitycheck() === true)
		{
            if(file_exists("backend/songlist.txt"))
            {
                $fh=fopen("backend/baselist.txt",'w');
				if($fh)
				{
					fwrite($fh,file_get_contents("backend/songlist.txt"));
					fclose($fh);
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Set contents of base list to contents of song list");
					$status=0;
				}
				else
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to set contents of base list to contents of song list; song list could not be opened");
					$status=2;
				}
            }
            else
            {
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to set contents of base list to contents of song list; song list does not exist");
                $status=2;
            }
		}
		else
		{
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to set contents of base list to contents of song list; not administrator");
            $status=1;
		}
	}
	else
	{
		//Logging disabled
		if(securitycheck() === true)
		{
            if(file_exists("backend/songlist.txt"))
            {
                $fh=fopen("backend/baselist.txt",'w');
				if($fh)
				{
					fwrite($fh,file_get_contents("backend/songlist.txt"));
					fclose($fh);
					$status=0;
				}
				else
				{
					$status=2;
				}
            }
            else
            {
                $status=2;
            }
		}
		else
		{
            $status=1;
		}
	}
	echo ("<script type=\"text/javascript\">window.location = \"listedit.php?status=" . $status . "\"</script>");
?>
  </head>
  <body>
  </body>
</html>