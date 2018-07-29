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
    <title><?php echo $sysname; ?>Music Request System-Import Song List</title>
    
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
	if(is_logging_enabled() === true)
	{
		//Change the timezone
		set_timezone();
		if(isset($_POST['s']))
		{
			//Submission started
			//Copy post value into variable (and sanitize it)
			$s=preg_replace("/[^0-9]/","",$_POST['s']);
			if($s == 1)
			{
				//Submitted file
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Submitted file \"" . $_FILES['ufile']['tmp_name'] . "\", size \"" . $_FILES['ufile']['size'] . "\" of type \"" . $_FILES['ufile']['type'] . "\"");
				if(securitycheck() === false)
				{
					//User is not administrator
					unlink($_FILES['ufile']['tmp_name']);
					die("You are not an administrator. <a href=\"login.php?ref=listedit2\">Sign in</a> or <a href=\"index.php\">Cancel</a>.");
				}
				//Move uploaded file to temporary storage
				$debug=move_uploaded_file($_FILES['ufile']['tmp_name'],"tempfile.txt");
				if($debug === true)
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Moved file to temp location");
					//Create storage
					$list="";
					if(isset($_POST['format']) && $_POST['format'] != "" && isset($_POST['delimiter']) && $_POST['delimiter'] != "")
					{
						//Process format
						$format=filter_var($_POST['format'],FILTER_SANITIZE_STRING);
						$format=explode("-",strtolower($format));
						$artist=array_search("artist",$format);
						$title=array_search("title",$format);
						$album=array_search("album",$format);
						$year=array_search("year",$format);
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Processed input format");
						$delimiter=filter_var($_POST['delimiter'],FILTER_SANITIZE_STRING);
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Processed delimiter");
						//Read file, split file into lines
						$file=explode("\r\n",file_get_contents("tempfile.txt"));
						//For each line, split it based on the delimiter
						for($i=0;$i<count($file);$i++)
						{
							$file[$i]=explode($delimiter,$file[$i]);
						}
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Processed input file");
						//Process each line into the storage
						foreach($file as $song)
						{
							$list2=array("","","","");
							if(isset($artist) && $artist !== false && isset($song[$artist]) && isset($list2[$artist]))
							{
								$list2[0]=$song[$artist];
							}
							if(isset($title) && $title !== false && isset($song[$title]) && isset($list2[$title]))
							{
								$list2[1]=$song[$title];
							}
							if(isset($album) && $album !== false && isset($song[$album]) && isset($list2[$album]))
							{
								$list2[2]=$song[$album];
							}
							if(isset($year) && $year !== false && isset($song[$year]) && isset($list2[$year]))
							{
								$list2[3]=$song[$year];
							}
							$list[]=$list2;
						}
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Processed set of songs");
						//Implode each line based on system delimiter ("|")
						for($i=0;$i<count($list);$i++)
						{
							$list[$i]=implode("|",$list[$i]);
						}
						//Implode entire list
						$list=implode("\r\n",$list);
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Finalized set of songs");
					}
					else
					{
						//Malformed submission, reset and try again
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Submission malformed");
						trigger_error("ERROR: Malformed submission; delimiter and/or format incomplete or missing. Try again.",E_USER_WARNING);
						$s=0;
					}
				}
				else
				{
					//Failed to move file, reset form and try again
					trigger_error("ERROR: Could not process the uploaded file. Please microwave it and try again.",E_USER_WARNING);
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Could not move file to temp location");
					$s=0;
				}
			}
			elseif($s == 2)
			{
				//Submit the final list
				if(isset($_POST['list']) && $_POST['list'] != "" && securitycheck() === true)
				{	
					//Remove URL-breaking characters from list
					$list=strip_tags($_POST['list']);
					$list=str_replace("\"","'",$list);
					$list=str_replace("&"," and ",$list);
					$list=str_replace("+"," and ",$list);
					//Write contents of submission to song list
					$debug=append_to_song_list($list);
					if($fh)
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Changed song list");
						$status=0;
					}
					else
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change song list");
						$status=1;
					}
				}
				else
				{
					//No list given
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"No list changed");
					$status=2;
				}
				//Remove leftover file
				unlink("tempfile.txt");
				//Get out of here
				echo("<script type=\"text/javascript\">window.location = \"listedit.php?ilstatus=$status\"</script>");
			}
			else
			{
				//Assume malformed submission
				$s=0;
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Submission was malformed");
			}
		}
		else
		{
			//Not submitting
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Visited list importing page");
			if(securitycheck() === false)
			{
				//User is not administrator
				die("You are not an administrator. <a href=\"login.php?ref=listedit2\">Sign in</a> or <a href=\"index.php\">Cancel</a>.");
			}
		}
	}
	else
	{
		//Logging disabled
		if(isset($_POST['s']))
		{
			//Submission started
			//Copy post value into variable (and sanitize it)
			$s=preg_replace("/[^0-9]/","",$_POST['s']);
			if($s == 1)
			{
				//Submitted file
				if(securitycheck() === false)
				{
					//User is not administrator
					unlink($_FILES['ufile']['tmp_name']);
					die("You are not an administrator. <a href=\"login.php?ref=listedit2\">Sign in</a> or <a href=\"index.php\">Cancel</a>.");
				}
				//Move uploaded file to temporary storage
				$debug=move_uploaded_file($_FILES['ufile']['tmp_name'],"tempfile.txt");
				if($debug === true)
				{
					//Create storage
					$list="";
					if(isset($_POST['format']) && $_POST['format'] != "" && isset($_POST['delimiter']) && $_POST['delimiter'] != "")
					{
						//Process format
						$format=filter_var($_POST['format'],FILTER_SANITIZE_STRING);
						$format=explode("-",strtolower($format));
						$artist=array_search("artist",$format);
						$title=array_search("title",$format);
						$album=array_search("album",$format);
						$year=array_search("year",$format);
						$delimiter=filter_var($_POST['delimiter'],FILTER_SANITIZE_STRING);
						//Read file, split file into lines
						$file=explode("\r\n",file_get_contents("tempfile.txt"));
						//For each line, split it based on the delimiter
						for($i=0;$i<count($file);$i++)
						{
							$file[$i]=explode($delimiter,$file[$i]);
						}
						//Process each line into the storage
						foreach($file as $song)
						{
							$list2=array("","","","");
							if(isset($artist) && $artist !== false && isset($song[$artist]) && isset($list2[$artist]))
							{
								$list2[0]=$song[$artist];
							}
							if(isset($title) && $title !== false && isset($song[$title]) && isset($list2[$title]))
							{
								$list2[1]=$song[$title];
							}
							if(isset($album) && $album !== false && isset($song[$album]) && isset($list2[$album]))
							{
								$list2[2]=$song[$album];
							}
							if(isset($year) && $year !== false && isset($song[$year]) && isset($list2[$year]))
							{
								$list2[3]=$song[$year];
							}
							$list[]=$list2;
						}
						//Implode each line based on system delimiter ("|")
						for($i=0;$i<count($list);$i++)
						{
							$list[$i]=implode("|",$list[$i]);
						}
						//Implode entire list
						$list=implode("\r\n",$list);
					}
					else
					{
						//Malformed submission, reset and try again
						echo ("ERROR: Malformed submission; delimiter and/or format incomplete or missing. Try again.");
						$s=0;
					}
				}
				else
				{
					//Failed to move file, reset form and try again
					echo ("ERROR: Could not process the uploaded file. Please microwave it and try again.");
					$s=0;
				}
			}
			elseif($s == 2)
			{
				//Submit the final list
				if(isset($_POST['list']) && $_POST['list'] != "" && securitycheck() === true)
				{	
					//Remove URL-breaking characters from list
					$list=strip_tags($_POST['list']);
					$list=str_replace("\"","'",$list);
					$list=str_replace("&"," and ",$list);
					$list=str_replace("+"," and ",$list);
					//Write contents of submission to song list
					$debug=append_to_song_list($list);
					if($debug)
					{
						$status=0;
					}
					else
					{
						$status=1;
					}
				}
				else
				{
					//No list given
					$status=2;
				}
				//Remove leftover file
				unlink("tempfile.txt");
				//Get out of here
				echo("<script type=\"text/javascript\">window.location = \"listedit.php?ilstatus=$status\"</script>");
			}
			else
			{
				//Assume malformed submission
				$s=0;
			}
		}
		else
		{
			//Not submitting
			if(securitycheck() === false)
			{
				//User is not administrator
				die("You are not an administrator. <a href=\"login.php?ref=listedit2\">Sign in</a> or <a href=\"index.php\">Cancel</a>.");
			}
		}
	}
?>
  </head>
  <body>
  <h1 style="text-align:center; text-decoration:underline;"><?php echo $sysname; ?>MRS-Import Song List</h1>
  <p>This will allow you to take an input file of arbitrary format (see caveats below) and convert it into a format the MRS understands. Easy!</p>
  <p><b>HOWEVER</b>, there are some guidelines that must be followed:</p>
  <ul>
  <li>The submitted file MUST be a text file (or similar format, such as CSV). <b>Databases are unsupported!</b></li>
  <li>The format MUST contain the words "artist", "title", "album", and "year", <b>even if all are not present in the input file</b>. The system has fallbacks, but undefined behaviour may result.</li>
  <li>Keep your delimiters simple!</li>
  <li>You may only input one format and one delimiter. Every line must follow that format and the items must be separated by that delimiter.</li>
  </ul>
  <p>After submitting the file, the system will process it, and the finalized list will be displayed in the text box. Make sure it is exactly as you want it before submitting!</p>
  <p><b><u>WARNING:</u></b> The format of this list is "Artist|Title|Album|Year". Likewise, there are characters (such as &amp; and +) that are not compatible with the request handling mechanisms and should not be used. Not following either of these conventions <b>WILL</b> break the system (although it should filter out at least some of the latter)!</p>
  <form method="post" action="listedit2.php" enctype="multipart/form-data">
  <input type="hidden" name="s" value="<?php if(isset($s) && $s == 1) { echo "2"; } else { echo "1"; } ?>">
  Delimiting symbol: <input type="text" name="delimiter" <?php if(!isset($s) || (isset($s) && $s != 1)) { echo "required=\"required\""; } else { echo "disabled=\"disabled\""; } ?>><br>
  Line format (items separated by dash ('-') ONLY): <input type="text" name="format" <?php if(!isset($s) || (isset($s) && $s != 1)) { echo "required=\"required\""; } else { echo "disabled=\"disabled\""; }  ?>><br>
  File to import: <input type="file" name="ufile" <?php if(!isset($s) || (isset($s) && $s != 1)) { echo "required=\"required\""; } else { echo "disabled=\"disabled\""; }  ?>><br>
  Finalized list:<br>
  <textarea name="list" rows="30" cols="100" <?php if(isset($s) && $s == 1) { echo "required=\"required\""; } else { echo "disabled=\"disabled\""; }  ?>><?php if(isset($list)) { echo stripcslashes($list); } ?></textarea><br>
  <input type="submit"><input type="button" value="Cancel" onclick="window.location.href='listedit.php'">
  </form>
  </body>
</html>