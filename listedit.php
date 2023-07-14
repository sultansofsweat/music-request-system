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
    <title><?php echo $sysname; ?>Music Request System-Edit Song List</title>
    
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
		if(isset($_GET['clear']) && $_GET['clear'] == "yes" && isset($_SESSION['listedit-order']))
		{
			unset($_SESSION['listedit-order']);
			trigger_error("Successfully cleared current queue.");
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Cleared current edit list");
		}
		if(isset($_SESSION['listedit-order']))
		{
			$order=$_SESSION['listedit-order'];
		}
		else
		{
			$order="";
		}
		$songs=array();
		$delete="no";
		$editsongs=array();
		if(securitycheck() === true && isset($_POST['s']) && $_POST['s'] == "1")
		{
			if(isset($_POST['add']) && ($add=preg_replace("/[^0-9]/","",$_POST['add'])) != "")
			{
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Adding song $add to edit list");
				if($order == "")
				{
					$order=array($add);
				}
				else
				{
					$order=array($order,$add);
				}
				$order=implode(",",$order);
				$_SESSION['listedit-order']=$order;
			}
			foreach(explode(",",$order) as $song)
			{
				$rawsong=explode("|",get_raw_song("main",$song),4);
				$editsongs[]=$rawsong[3];
			}
		}
		elseif(securitycheck() === true && isset($_POST['s']) && $_POST['s'] == "2")
		{
			$order=explode(",",(preg_replace("/[^0-9\,]/","",$_POST['order'])));
			if(isset($_POST['delete']) && $_POST['delete'] == "yes")
			{
				$debug=remove_from_song_list("main",$order);
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Deleted " . $debug[0] . " songs in song list \"main\" with " . $debug[1] . " errors");
				trigger_error("Finished editing song list. Removed " . $debug[0] . " songs with " . $debug[1] . " errors.");
				unset($_SESSION['listedit-order']);
			}
			else
			{
				$edited=explode("\r\n",htmlspecialchars($_POST['list']));
				if(count($order) != count($edited))
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to edit song list \"main\": discrepancy in lists submitted");
					trigger_error("Failed to edit song list \"main\": list of songs to edit and list of replacements are different lengths.",E_USER_ERROR);
				}
				else
				{
					$count=0;
					$errors=0;
					for($i=0;$i<count($order);$i++)
					{
						$debug=modify_song_list("main",$order[$i],$edited[$i]);
						if($debug === false)
						{
							$errors++;
						}
						else
						{
							$count++;
						}
					}
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Edited $count songs in song list \"main\" with $errors errors");
					trigger_error("Finished editing song list. Edited $count songs with $errors errors.");
					unset($_SESSION['listedit-order']);
				}
			}
		}
		else
		{
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Visited main list editing page");
			if(securitycheck() === false)
			{
				die("You are not an administrator. <a href=\"login.php?ref=listedit\">Log in</a> or <a href=\"index.php\">cancel</a>.");
			}
		}
		$rawsongs=explode("\r\n",stripcslashes(get_raw_songs("main")));
		foreach($rawsongs as $song)
		{
			$song=explode("|",$song,4);
			$songs[]=$song[3];
		}
		$editsongs=implode("\r\n",$editsongs);
	}
	else
	{
		if(isset($_GET['clear']) && $_GET['clear'] == "yes" && isset($_SESSION['listedit-order']))
		{
			unset($_SESSION['listedit-order']);
			trigger_error("Successfully cleared current queue.");
		}
		if(isset($_SESSION['listedit-order']))
		{
			$order=$_SESSION['listedit-order'];
		}
		else
		{
			$order="";
		}
		$songs=array();
		$delete="no";
		$editsongs=array();
		if(securitycheck() === true && isset($_POST['s']) && $_POST['s'] == "1")
		{
			if(isset($_POST['add']) && ($add=preg_replace("/[^0-9]/","",$_POST['add'])) != "")
			{
				if($order == "")
				{
					$order=array($add);
				}
				else
				{
					$order=array($order,$add);
				}
				$order=implode(",",$order);
				$_SESSION['listedit-order']=$order;
			}
			foreach(explode(",",$order) as $song)
			{
				$rawsong=explode("|",get_raw_song("main",$song),4);
				$editsongs[]=$rawsong[3];
			}
		}
		elseif(securitycheck() === true && isset($_POST['s']) && $_POST['s'] == "2")
		{
			$order=explode(",",(preg_replace("/[^0-9\,]/","",$_POST['order'])));
			if(isset($_POST['delete']) && $_POST['delete'] == "yes")
			{
				$debug=remove_from_song_list("main",$order);
				trigger_error("Finished editing song list. Removed " . $debug[0] . " songs with " . $debug[1] . " errors.");
				unset($_SESSION['listedit-order']);
			}
			else
			{
				$edited=explode("\r\n",htmlspecialchars($_POST['list']));
				if(count($order) != count($edited))
				{
					trigger_error("Failed to edit song list \"main\": list of songs to edit and list of replacements are different lengths.",E_USER_ERROR);
				}
				else
				{
					$count=0;
					$errors=0;
					for($i=0;$i<count($order);$i++)
					{
						$debug=modify_song_list("main",$order[$i],$edited[$i]);
						if($debug === false)
						{
							$errors++;
						}
						else
						{
							$count++;
						}
					}
					trigger_error("Finished editing song list. Edited $count songs with $errors errors.");
					unset($_SESSION['listedit-order']);
				}
			}
		}
		else
		{
			if(securitycheck() === false)
			{
				die("You are not an administrator. <a href=\"login.php?ref=listedit\">Log in</a> or <a href=\"index.php\">cancel</a>.");
			}
		}
		$rawsongs=explode("\r\n",stripcslashes(get_raw_songs("main")));
		foreach($rawsongs as $song)
		{
			$song=explode("|",$song,4);
			$songs[]=$song[3];
		}
		$editsongs=implode("\r\n",$editsongs);
	}
?>
  <h1 style="text-align:center; text-decoration:underline;"><?php echo $sysname; ?>MRS-Edit Song List</h1>
  <p><a href="listadd.php">Add new songs</a> or <a href="listimport.php">add songs from file</a> instead.</p>
  <p>First, choose songs to edit (or delete):</p>
  <form method="post" action="listedit.php">
  <input type="hidden" name="s" value="1">
  <input type="hidden" name="order" value="<?php echo $order; ?>">
  <select name="add">
  <option value="">-----Select one-----</option>
  <?php
	for($i=0;$i<count($songs);$i++)
	{
		echo("<option value=\"$i\">" . $songs[$i] . "</option>\r\n");
	}
  ?>
  </select><br>
  <input type="submit" value="Add to queue"><input type="button" value="Clear queue" onclick="window.location.href='listedit.php?clear=yes'">
  </form>
  <p>Then edit the songs below.<br>
  <b><u>WARNING:</u></b> Do not change the order of the songs in the box! It probably doesn't matter, but you may summon the program director by doing so.<br>
  The format of this list is "<?php echo get_system_setting("songformat"); ?>". Likewise, there are characters (such as &amp; and +) that are not compatible with the request handling mechanisms and should not be used. Not following either of these conventions <b>WILL</b> break the system!</p>
  <form method="post" action="listedit.php">
  <input type="hidden" name="s" value="2">
  <input type="hidden" name="order" value="<?php echo $order; ?>">
  <input type="checkbox" name="delete" value="yes" <?php if($delete == "yes") { echo "checked=\"checked\""; } ?>>Delete these songs instead of editing (<b>this is PERMANENT</b>).<br>
  <textarea name="list" rows="30" cols="100"><?php echo stripcslashes($editsongs); ?></textarea><br>
  <input type="submit"><input type="button" value="Cancel" onclick="window.location.href='admin.php'">
  </form>
  </body>
</html>