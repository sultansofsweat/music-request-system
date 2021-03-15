<?php
	//Change directory to allow use of rest of MRS
	chdir("..");
?>
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
		ini_set("error_reporting",E_ALL & ~E_NOTICE & ~E_USER_NOTICE);
		break;
		case 2:
		ini_set("error_reporting",E_ALL);
		break;
		case 1:
		default:
		ini_set("error_reporting",E_ALL & ~E_NOTICE);
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
	<link rel="shortcut icon" href="../backend/favicon.ico">
    <title><?php echo $sysname; ?>Music Request System-Downloading Update</title>
    
    <style type="text/css">
    <!--
    body {
      color:#000000;
	  background-color:#FFFFFF;
      background-image:url('../backend/background.gif');
      background-repeat:repeat;
    }
    a  { color:#FFFFFF; background-color:#0000FF; }
    a:visited { color:#FFFFFF; background-color:#800080; }
    a:hover { color:#000000; background-color:#00FF00; }
    a:active { color:#000000; background-color:#FF0000; }
    -->
    </style>
  </head>
  <?php
	//Log page visit, if logging enabled
	date_default_timezone_set(get_system_setting("timezone"));
	write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Visited upgrade pack sideload page");
	//Run security check
	if(securitycheck() === false)
	{
		//No admin privileges, no page viewing privileges
		die("You are not an administrator. <a href=\"../login.php?ref=admin\">Sign in</a> or <a href=\"../index.php\">Cancel</a>.");
	}
	$terminate=false;
	$buildcode="";
	//Process submission
	if(isset($_POST['s']) && $_POST['s'] == "y")
	{
		write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Attempting to upload upgrade package");
		if(isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK)
		{
			$buildcode=preg_replace("/[^A-Za-z0-9\-]/","",basename($_FILES['file']['name'],".zip"));
			$debug=move_uploaded_file($_FILES['file']['tmp_name'],"upgrade/$buildcode.zip");
			if($debug === true)
			{
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Uploaded upgrade package");
			}
			else
			{
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to upload upgrade package");
				trigger_error("File transfer failed.",E_USER_ERROR);
				$terminate=true;
			}
		}
		else
		{
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to upload upgrade package");
			trigger_error("File upload failed.",E_USER_ERROR);
			$terminate=true;
		}
		//Extract upgrade pack
		if($terminate === false)
		{
			//If directory does not already exist, make it
			if(!is_dir("upgrade/$buildcode"))
			{
				$debug=mkdir("upgrade/$buildcode");
				if($debug === true)
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Created directory for update $buildcode");
				}
			}
			else
			{
				$debug=true;
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Directory $buildcode already exists, using that instead of recreating");
			}
			if($debug === true)
			{
				$arch=new ZipArchive;
				if($arch->open("upgrade/$buildcode.zip"))
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Opened upgrade pack $buildcode");
					$debug=$arch->extractTo("upgrade/$buildcode");
					if($debug === true)
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Extracted upgrade pack $buildcode");
					}
					else
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to extract upgrade pack $buildcode");
						trigger_error("Failed to extract upgrade pack. Please re-download the MRS upgrade package, or contact the software vendor.",E_USER_ERROR);
						$terminate=true;
					}
					$arch->close();
				}
				else
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to open upgrade pack");
					trigger_error("Failed to open upgrade pack. Please re-download the MRS upgrade package, or contact the software vendor.",E_USER_ERROR);
					$terminate=true;
				}
			}
			else
			{
				trigger_error("Failed to create directory \"upgrade/$buildcode\".",E_USER_ERROR);
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to create directory for update $buildcode");
				$terminate=true;
			}
		}
		//Add upgrade pack to list
		if($terminate === false)
		{
			if(file_exists("upgrade/packages.txt"))
			{
				$upgrades=array_filter(explode("\r\n",file_get_contents("upgrade/packages.txt")));
				$upgrades[]="$buildcode|DONT_CHECK|1";
				$upgrades=implode("\r\n",$upgrades);
				$fh=fopen("upgrade/packages.txt",'w');
				if($fh)
				{
					fwrite($fh,$upgrades);
					fclose($fh);
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Updated list of available upgrades");
				}
				else
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to update list of available upgrades");
					trigger_error("Failed to update list of available updates; file cannot be opened.",E_USER_ERROR);
					$terminate=true;
				}
			}
			else
			{
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to update list of available upgrades");
				trigger_error("Failed to update list of available updates; file cannot be found.",E_USER_ERROR);
				$terminate=true;
			}
		}
		//Redirect back out based on end result
		if($terminate === false)
		{
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Finished sideloading update $buildcode");
			echo("<script type=\"text/javascript\">window.location = \"index.php?sideload=y\"</script>");
		}
		else
		{
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to sideload update $buildcode");
			echo("<script type=\"text/javascript\">window.location = \"index.php?sideload=n\"</script>");
		}
	}
	//Change back to upgrader directory to avoid breaking everything else
	chdir("upgrade");
  ?>
  <body>
  <h1 style="text-align:center; text-decoration:underline;"><?php echo $sysname; ?>Music Request System-Sideload Upgrade Pack</h1>
  <p><b>IMPORTANT: there is NO warranty provided for this feature!</b> If you bust something with it, congratulations, you are now the proud owner of multiple pieces instead of just one.<br>
  Sideloaded upgrade packages MUST be saved as a ZIP archive, and the resulting upgrade pack will assume the name of the archive uploaded, with anything but letters, numbers and dashes ('-') removed.</p>
  <form method="post" action="sideload.php" enctype="multipart/form-data">
  <input type="hidden" name="s" value="y">
  Sideload this file: <input type="file" name="file" required="required"><br>
  <input type="submit" value="Sideload Package"><input type="reset"><input type="button" value="Go back" onclick="window.location.href='index.php'">
  </body>
</html>