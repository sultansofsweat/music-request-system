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
    <title><?php echo $sysname; ?>Music Request System-Reformat Song Lists</title>
    
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
    $oldformat=get_system_default("songformat");
    $newformat=get_system_setting("songformat");
	if(is_logging_enabled() === true)
	{
		set_timezone();
		if(isset($_POST['s']) && $_POST['s'] == "y" && securitycheck() === true)
		{
			$oldformat=preg_replace("/[^a-z0-9\*\|]/","",$_POST['oldformat']);
            $newformat=preg_replace("/[^a-z0-9\*\|]/","",$_POST['newformat']);
            write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Processed song list formats");
            $lists=glob("songs/*.txt");
            $errors=0;
            foreach($lists as $list)
            {
                write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Began processing list \"" . basename($list,".txt") . "\"");
                $newsongs=array();
                $oldsongs=get_songs(basename($list,".txt"));
                foreach($oldsongs as $song)
                {
                    $newsong=array();
                    $newsong[]=$song["added_to_system"];
                    $newsong[]=$song["request_count"];
                    $newsong[]=$song["last_requested"];
                    foreach($newformat as $field)
                    {
                        if(isset($song[$field]))
                        {
                            $newsong[]=$song[$field];
                        }
                        else
                        {
                            $newsong[]="";
                        }
                    }
                    $newsong=implode("|",$newsong);
                    $newsongs[]=$newsong;
                }
                $newsongs=implode("\r\n",$newsongs);
                write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Finished processing list \"" . basename($list,".txt") . "\"");
                $fh=fopen($list,'w');
                if($fh)
                {
                    fwrite($fh,$newsongs);
                    fclose($fh);
                    write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Successfully wrote new list back to \"" . basename($list,".txt") . "\"");
                }
                else
                {
                    trigger_error("Failed to rewrite song list " . basename($list,".txt") . ". Microwave the file and try again.",E_USER_WARNING);
                    write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to write new list back to \"" . basename($list,".txt") . "\"");
                }
            }
            write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Finished updating song list formats");
            trigger_error("Finished updating list formats.");
            $oldformat=implode("|",$oldformat);
            $newformat=implode("|",$newformat);
		}
		else
		{
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Visited song list reformat page");
			if(securitycheck() === false)
			{
				die("You are not an administrator. <a href=\"login.php?ref=listformat\">Log in</a> or <a href=\"index.php\">cancel</a>.");
			}
		}
	}
	else
	{
		if(isset($_POST['s']) && $_POST['s'] == "y" && securitycheck() === true)
		{
            $oldformat=explode("|",preg_replace("/[^a-z0-9\*\|]/","",$_POST['oldformat']));
            $newformat=explode("|",preg_replace("/[^a-z0-9\*\|]/","",$_POST['newformat']));
            $lists=glob("songs/*.txt");
            $errors=0;
            foreach($lists as $list)
            {
                $newsongs=array();
                $oldsongs=get_songs(basename($list,".txt"));
                foreach($oldsongs as $song)
                {
                    $newsong=array();
                    $newsong[]=$song["added_to_system"];
                    $newsong[]=$song["request_count"];
                    $newsong[]=$song["last_requested"];
                    foreach($newformat as $field)
                    {
                        if(isset($song[$field]))
                        {
                            $newsong[]=$song[$field];
                        }
                        else
                        {
                            $newsong[]="";
                        }
                    }
                    $newsong=implode("|",$newsong);
                    $newsongs[]=$newsong;
                }
                $newsongs=implode("\r\n",$newsongs);
                $fh=fopen($list,'w');
                if($fh)
                {
                    fwrite($fh,$newsongs);
                    fclose($fh);
                }
                else
                {
                    trigger_error("Failed to rewrite song list " . basename($list,".txt") . ". Microwave the file and try again.",E_USER_WARNING);
                }
            }
            trigger_error("Finished updating list formats.");
            $oldformat=implode("|",$oldformat);
            $newformat=implode("|",$newformat);
		}
		else
		{
			if(securitycheck() === false)
			{
				die("You are not an administrator. <a href=\"login.php?ref=listformat\">Log in</a> or <a href=\"index.php\">cancel</a>.");
			}
		}
	}
?>
  <h1 style="text-align:center; text-decoration:underline;"><?php echo $sysname; ?>MRS-Reformat Song Lists</h1>
  <p>This will reformat all song lists on the system according to the new format specified. You <b>must</b> do this for things to display correctly.</p>
  <form method="post" action="listformat.php">
  <input type="hidden" name="s" value="y">
  Old list format: <input type="text" name="oldformat" size="50" value="<?php echo $oldformat; ?>"> (you did remember this, right?)<br>
  New list format: <input type="text" name="newformat" size="50" readonly="readonly" value="<?php echo $newformat; ?>"> (provided only for the purposes of comparison, you cannot edit this)<br>
  <input type="submit" value="Reformat lists"><input type="button" value="Cancel" onclick="window.location.href='admin.php'">
  </form>
  </body>
</html>