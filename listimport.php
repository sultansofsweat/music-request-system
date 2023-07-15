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
	$delimiter="";
	$format="";
	$songs="";
	$submittable=false;
	
	if(is_logging_enabled() === true)
	{
		set_timezone();
		if(securitycheck() === true && isset($_POST['s']) && $_POST['s'] == "1")
		{
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Attempted to upload file for processing");
			$delimiter=substr(htmlspecialchars($_POST['delimiter']),0,1);
			$format=explode("|",htmlspecialchars($_POST['format']));
			if(!isset($_FILES['file']) || $delimiter == "" || $format == "")
			{
				trigger_error("Failed to process file: file not successfully submitted, or delimiter and/or format blank. Call the station manager and try again.",E_USER_WARNING);
			}
			else
			{
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Successfully read uploaded file");
				$file=explode("\r\n",file_get_contents($_FILES['file']['tmp_name']));
				$songs=array();
				$rawsongs=array();
				$sysformat=explode("|",get_system_setting("songformat"));
				foreach($file as $song)
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Began processing uploaded file");
					$song=explode($delimiter,$song);
					if(count($song) != count($format))
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Invalid song format: expected " . count($format) . " parameters, found " . count($song));
						trigger_error("NASAL DEMON ALERT: a submitted song does not contain the appropriate number of items. Microwave it immediately. The system will be discarding it.",E_USER_WARNING);
					}
					else
					{
						$toadd=array();
						for($i=0;$i<count($song);$i++)
						{
							$toadd[$format[$i]]=$song[$i];
						}
						$rawsongs[]=$toadd;
					}
				}
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Finished processing all songs in uploaded file");
				foreach($rawsongs as $song)
				{
					$final=array();
					foreach($sysformat as $sformat)
					{
						if(isset($song[$sformat]))
						{
							$final[]=$song[$sformat];
						}
						else
						{
							$final[]="";
						}
					}
					$songs[]=implode("|",$final);
				}
				$songs=implode("\r\n",$songs);
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Formatted all songs to system specifications");
				if($songs != "")
				{
					$submittable=true;
				}
			}
			$format=implode("|",$format);
		}
		elseif(securitycheck() === true && isset($_POST['s']) && $_POST['s'] == "2")
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
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Visited main list import page");
			if(securitycheck() === false)
			{
				die("You are not an administrator. <a href=\"login.php?ref=listimport\">Log in</a> or <a href=\"index.php\">cancel</a>.");
			}
		}
	}
	else
	{
		if(securitycheck() === true && isset($_POST['s']) && $_POST['s'] == "1")
		{
			$delimiter=substr(htmlspecialchars($_POST['delimiter']),0,1);
			$format=explode("|",htmlspecialchars($_POST['format']));
			if(!isset($_FILES['file']) || $delimiter == "" || $format == "")
			{
				trigger_error("Failed to process file: file not successfully submitted, or delimiter and/or format blank. Call the station manager and try again.",E_USER_WARNING);
			}
			else
			{
				$file=explode("\r\n",file_get_contents($_FILES['file']['tmp_name']));
				$songs=array();
				$rawsongs=array();
				$sysformat=explode("|",get_system_setting("songformat"));
				foreach($file as $song)
				{
					$song=explode($delimiter,$song);
					if(count($song) != count($format))
					{
						trigger_error("NASAL DEMON ALERT: a submitted song does not contain the appropriate number of items. Microwave it immediately. The system will be discarding it.",E_USER_WARNING);
					}
					else
					{
						$toadd=array();
						for($i=0;$i<count($song);$i++)
						{
							$toadd[$format[$i]]=$song[$i];
						}
						$rawsongs[]=$toadd;
					}
				}
				foreach($rawsongs as $song)
				{
					$final=array();
					foreach($sysformat as $sformat)
					{
						if(isset($song[$sformat]))
						{
							$final[]=$song[$sformat];
						}
						else
						{
							$final[]="";
						}
					}
					$songs[]=implode("|",$final);
				}
				$songs=implode("\r\n",$songs);
				if($songs != "")
				{
					$submittable=true;
				}
			}
			$format=implode("|",$format);
		}
		elseif(securitycheck() === true && isset($_POST['s']) && $_POST['s'] == "2")
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
			if(securitycheck() === false)
			{
				die("You are not an administrator. <a href=\"login.php?ref=listimport\">Log in</a> or <a href=\"index.php\">cancel</a>.");
			}
		}
	}
?>
  <h1 style="text-align:center; text-decoration:underline;"><?php echo $sysname; ?>MRS-Add To Song List</h1>
  <p><a href="listedit.php">Edit existing songs</a> or <a href="listimport.php">add songs manually</a> instead.</p>
  <p>Upload a file and specify the format below. Note that the format does not need to be the same as the system list format, and the delimiter can be almost anything, with caveats:</p>
  <ul>
  <li>The delimiter may only be a single character.</li>
  <li>Each element in the format string must be separated by a '|' character.</li>
  <li>Only fields present on the system will be kept; other data will be discarded.</li>
  <li>Following from the above, if a field is marked with a '*' on the system, but is not marked as such in the format string, the data will also be ignored.</li>
  <li>Files can only be of a text format; anything else will summon nasal demons and/or perform other undefined behaviour.</li>
  </ul>
  <form method="post" action="listimport.php" enctype="multipart/form-data">
  <input type="hidden" name="s" value="1">
  Delimiter: <input type="text" name="delimiter" size="1" maxlength="1" required="required" value="<?php echo $delimiter; ?>"><br>
  Format string: <input type="text" name="format" required="required" value="<?php echo $format; ?>"><br>
  File to import from: <input type="file" name="file" required="required"><br>
  <input type="submit" value="Upload file"><input type="reset">
  </form>
  <p>Once complete, the songs will appear below for you to make any corrections. If the result is completely inappropriate, you may try again above.<br>
  Reminder that the format of this list is "<?php echo get_system_setting("songformat"); ?>", and that certain special characters cause problems. Calls to the program director will be the result if you ignore these warnings, with a punishment that varies in severity in accordance with the circumstances.</p>
  <form method="post" action="listimport.php">
  <input type="hidden" name="s" value="2">
  <textarea name="list" rows="30" cols="100"><?php echo stripcslashes($songs); ?></textarea><br>
  <input type="submit" value="Add songs" <?php if($submittable !== true) { echo "disabled=\"disabled\""; } ?>><input type="button" value="Cancel" onclick="window.location.href='admin.php'">
  </form>
  </body>
</html>