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
    <title><?php echo $sysname; ?>Music Request System-Delete Song List</title>
    
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
        if(securitycheck() === true && isset($_POST['s']) && $_POST['s'] == "y")
        {
            $list=htmlspecialchars($_POST['list']);
            if(file_exists("songs/$list.txt"))
            {
                $debug=unlink("songs/$list.txt");
                if($debug === true)
                {
                    write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Deleted song list \"$list\"");
                    $existing=explode(",",get_system_setting("extlists"));
                    for($i=0;$i<count($existing);$i++)
                    {
                        $e=explode("=",$existing[$i]);
                        if($e[1] == $list)
                        {
                            $existing[$i]="";
                        }
                    }
                    $existing=implode(",",array_filter($existing));
                    $debug=save_system_setting("extlists",$existing);
                    if($debug === true)
                    {
                        write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Removed song list \"$list\" from extlists");
                        trigger_error("Successfully removed song list.");
                    }
                    else
                    {
                        write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to remove song list \"$list\" from extlists");
                        trigger_error("Failed to remove song list. Is it a GPX?",E_USER_ERROR);
                    }
                }
				else
                {
                    write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to delete song list \"$list\"");
                    trigger_error("Failed to remove song list. Is it a GPX?",E_USER_ERROR);
                }
			}
		}
		else
		{
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Visited external list deletion page");
			if(securitycheck() === false)
			{
				die("You are not an administrator. <a href=\"login.php?ref=listdel\">Log in</a> or <a href=\"index.php\">cancel</a>.");
			}
		}
        $lists=glob("songs/*.txt");
        for($i=0;$i<count($lists);$i++)
        {
            $lists[$i]=substr($lists[$i],6,-4);
            if($lists[$i] == "main")
            {
                $lists[$i]="";
            }
        }
        $lists=array_filter($lists);
	}
	else
	{
		if(securitycheck() === true && isset($_POST['s']) && $_POST['s'] == "y")
        {
            $list=htmlspecialchars($_POST['list']);
            if(file_exists("songs/$list.txt"))
            {
                $debug=unlink("songs/$list.txt");
                if($debug === true)
                {
                    $existing=explode(",",get_system_setting("extlists"));
                    for($i=0;$i<count($existing);$i++)
                    {
                        $e=explode("=",$existing[$i]);
                        if($e[1] == $list)
                        {
                            $existing[$i]="";
                        }
                    }
                    $existing=implode(",",array_filter($existing));
                    $debug=save_system_setting("extlists",$existing);
                    if($debug === true)
                    {
                        trigger_error("Successfully removed song list.");
                    }
                    else
                    {
                        trigger_error("Failed to remove song list. Is it a GPX?",E_USER_ERROR);
                    }
                }
				else
                {
                    trigger_error("Failed to remove song list. Is it a GPX?",E_USER_ERROR);
                }
			}
		}
		else
		{
			if(securitycheck() === false)
			{
				die("You are not an administrator. <a href=\"login.php?ref=listdel\">Log in</a> or <a href=\"index.php\">cancel</a>.");
			}
		}
        $lists=glob("songs/*.txt");
        for($i=0;$i<count($lists);$i++)
        {
            $lists[$i]=substr($lists[$i],6,-4);
            if($lists[$i] == "main")
            {
                $lists[$i]="";
            }
        }
        $lists=array_filter($lists);
	}
?>
  <h1 style="text-align:center; text-decoration:underline;"><?php echo $sysname; ?>MRS-Delete Song List</h1>
  <p><a href="listadd2.php">Import new list</a> or <a href="listedit2.php">edit list</a> instead.</p>
  <p><b>This is permanent!</b> Be VERY sure this is what you want to do, otherwise prepare to speak to the program director.</p>
  <p>Delete the following list:</p>
  <form method="post" action="listdel.php">
  <input type="hidden" name="s" value="y">
  <select name="list">
  <option value="">-----Select one-----</option>
  <?php
	foreach($lists as $lst)
	{
		echo("<option value=\"$lst\">" . $lst . "</option>\r\n");
	}
  ?>
  </select><br>
  <input type="submit"><input type="button" value="Cancel" onclick="window.location.href='admin.php'">
  </form>
  </body>
</html>