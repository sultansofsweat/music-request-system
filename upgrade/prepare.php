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
    <title><?php echo $sysname; ?>Music Request System-Preparing Updates</title>
    
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
	write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Started preparing upgrade packs for installation");
	//Run security check
	if(securitycheck() === false)
	{
		//No admin privileges, no page viewing privileges
		die("You are not an administrator. <a href=\"../login.php?ref=admin\">Sign in</a> or <a href=\"../index.php\">Cancel</a>.");
	}
	$error=false;
	//Get list of updates
	if(file_exists("upgrade/packages.txt"))
	{
		$upgrades=array_filter(explode("\r\n",file_get_contents("upgrade/packages.txt")));
		//Go through upgrades one by one
		foreach($upgrades as $upgrade)
		{
			$upgrade=explode("|",$upgrade);
			//If upgrade is downloaded or already prepared (and not installed), begin [re]preparing it
			if(isset($upgrade[0]) && isset($upgrade[2]) && $upgrade[2] >= 1 && $upgrade[2] < 3)
			{
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Started preparing upgrade pack " . $upgrade[0]);
				//Open necessary files
				$codeadd=fopen("upgrade/prepare/code-add.txt",'a');
				$coderem=fopen("upgrade/prepare/code-rem.txt",'a');
				$confadd=fopen("upgrade/prepare/conf-add.txt",'a');
				$confchg=fopen("upgrade/prepare/conf-chg.txt",'a');
				$confrem=fopen("upgrade/prepare/conf-rem.txt",'a');
				$version=fopen("upgrade/prepare/version.txt",'w');
				if($codeadd && $coderem && $confadd && $confchg && $confrem && $version)
				{
					//Process base files
					if(file_exists("upgrade/" . $upgrade[0] . "/code-add.txt"))
					{
						fwrite($codeadd,file_get_contents("upgrade/" . $upgrade[0] . "/code-add.txt") . "\r\n");
					}
					else
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to prepare upgrade pack " . $upgrade[0] . ": cannot open file \"code-add.txt\".");
						trigger_error("Cannot prepare upgrade pack \"" . $upgrade[0] . "\" as a file in the upgrade pack cannot be found. Proceeding anyway to find other problems, but you will need to run prepare script again after the problem is fixed.",E_USER_WARNING);
						$error=true;
					}
					if(file_exists("upgrade/" . $upgrade[0] . "/code-rem.txt"))
					{
						fwrite($coderem,file_get_contents("upgrade/" . $upgrade[0] . "/code-rem.txt") . "\r\n");
					}
					else
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to prepare upgrade pack " . $upgrade[0] . ": cannot open file \"code-rem.txt\".");
						trigger_error("Cannot prepare upgrade pack \"" . $upgrade[0] . "\" as a file in the upgrade pack cannot be found. Proceeding anyway to find other problems, but you will need to run prepare script again after the problem is fixed.",E_USER_WARNING);
						$error=true;
					}
					if(file_exists("upgrade/" . $upgrade[0] . "/conf-add.txt"))
					{
						fwrite($confadd,file_get_contents("upgrade/" . $upgrade[0] . "/conf-add.txt") . "\r\n");
					}
					else
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to prepare upgrade pack " . $upgrade[0] . ": cannot open file \"conf-add.txt\".");
						trigger_error("Cannot prepare upgrade pack \"" . $upgrade[0] . "\" as a file in the upgrade pack cannot be found. Proceeding anyway to find other problems, but you will need to run prepare script again after the problem is fixed.",E_USER_WARNING);
						$error=true;
					}
					if(file_exists("upgrade/" . $upgrade[0] . "/conf-chg.txt"))
					{
						fwrite($confchg,file_get_contents("upgrade/" . $upgrade[0] . "/conf-chg.txt") . "\r\n");
					}
					else
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to prepare upgrade pack " . $upgrade[0] . ": cannot open file \"conf-chg.txt\".");
						trigger_error("Cannot prepare upgrade pack \"" . $upgrade[0] . "\" as a file in the upgrade pack cannot be found. Proceeding anyway to find other problems, but you will need to run prepare script again after the problem is fixed.",E_USER_WARNING);
						$error=true;
					}
					if(file_exists("upgrade/" . $upgrade[0] . "/conf-rem.txt"))
					{
						fwrite($confrem,file_get_contents("upgrade/" . $upgrade[0] . "/conf-rem.txt") . "\r\n");
					}
					else
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to prepare upgrade pack " . $upgrade[0] . ": cannot open file \"conf-rem.txt\".");
						trigger_error("Cannot prepare upgrade pack \"" . $upgrade[0] . "\" as a file in the upgrade pack cannot be found. Proceeding anyway to find other problems, but you will need to run prepare script again after the problem is fixed.",E_USER_WARNING);
						$error=true;
					}
					if(file_exists("upgrade/" . $upgrade[0] . "/externalchanges.php"))
					{
						$debug=copy("upgrade/" . $upgrade[0] . "/externalchanges.php","upgrade/prepare/externalchanges-" . $upgrade[0] . ".php");
						if($debug !== true)
						{
							write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to prepare upgrade pack " . $upgrade[0] . ": cannot copy file \"externalchanges.php\".");
							trigger_error("Cannot prepare upgrade pack \"" . $upgrade[0] . "\" as a file in the upgrade pack cannot be opened. Proceeding anyway to find other problems, but you will need to run prepare script again after the problem is fixed.",E_USER_WARNING);
							$error=true;
						}
					}
					else
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to prepare upgrade pack " . $upgrade[0] . ": cannot open file \"externalchanges.php\".");
						trigger_error("Cannot prepare upgrade pack \"" . $upgrade[0] . "\" as a file in the upgrade pack cannot be found. Proceeding anyway to find other problems, but you will need to run prepare script again after the problem is fixed.",E_USER_WARNING);
						$error=true;
					}
					if(file_exists("upgrade/" . $upgrade[0] . "/version.txt"))
					{
						fwrite($version,file_get_contents("upgrade/" . $upgrade[0] . "/version.txt"));
					}
					else
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to prepare upgrade pack " . $upgrade[0] . ": cannot open file \"version.txt\".");
						trigger_error("Cannot prepare upgrade pack \"" . $upgrade[0] . "\" as a file in the upgrade pack cannot be found. Proceeding anyway to find other problems, but you will need to run prepare script again after the problem is fixed.",E_USER_WARNING);
						$error=true;
					}
					//Close new files
					fclose($codeadd);
					fclose($coderem);
					fclose($confadd);
					fclose($confchg);
					fclose($confrem);
					fclose($version);
					//Get all other files
					$files=array_merge(glob("upgrade/" . $upgrade[0] . "/*.php"),glob("upgrade/" . $upgrade[0] . "/*.gif"),glob("upgrade/" . $upgrade[0] . "/*.ico"));
					foreach($files as $file)
					{
						if(strpos($file,"externalchanges.php") === false)
						{
							$newname=str_replace($upgrade[0],"prepare",$file);
							$debug=copy($file,$newname);
							if($debug !== true)
							{
								write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to prepare upgrade pack " . $upgrade[0] . ": cannot move file \"$file\".");
								trigger_error("Cannot prepare upgrade pack \"" . $upgrade[0] . "\" as a file in the upgrade pack cannot be moved. Proceeding anyway to find other problems, but you will need to run prepare script again after the problem is fixed.",E_USER_WARNING);
								$error=true;
							}
						}
					}
				}
				else
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to prepare upgrade pack " . $upgrade[0] . ": cannot open prepared files.");
					trigger_error("Cannot prepare upgrade pack \"" . $upgrade[0] . "\" as the final files cannot be opened. Proceeding anyway to find other problems, but you will need to run prepare script again after the problem is fixed.",E_USER_WARNING);
					$error=true;
				}
				//If no error occurred, mark as prepared
				if($error === false)
				{
					$upgpacks=array_filter(explode("\r\n",file_get_contents("upgrade/packages.txt")));
					for($i=0;$i<count($upgpacks);$i++)
					{
						$upgpacks[$i]=explode("|",$upgpacks[$i]);
						if(count($upgpacks[$i]) == 3 && $upgpacks[$i][0] == $upgrade[0])
						{
							$upgpacks[$i][2]=2;
						}
						$upgpacks[$i]=implode("|",$upgpacks[$i]);
					}
					$upgpacks=implode("\r\n",$upgpacks);
					$fh=fopen("upgrade/packages.txt",'w');
					if($fh)
					{
						fwrite($fh,$upgpacks);
						fclose($fh);
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Updated list of available upgrades");
					}
					else
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to update list of available upgrades");
						trigger_error("Cannot prepare upgrade pack \"" . $upgrade[0] . "\" as the upgrade list cannot be updated. Proceeding anyway to find other problems, but you will need to run prepare script again after the problem is fixed.",E_USER_WARNING);
						$error=true;
					}
				}
			}
			else
			{
				if(isset($upgrade[0]))
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Ignoring update \"" . $upgrade[0] . "\" as it is not downloaded");
				}
				else
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Malformed update found on list");
					trigger_error("Malformed update encountered. Proceeding anyway to find other problems, but you will need to run prepare script again after the problem is fixed.",E_USER_WARNING);
					$error=true;
				}
			}
		}
	}
	else
	{
		write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to read package list");
		trigger_error("Failed to prepare updates as the update list cannot be read.",E_USER_ERROR);
		$error=true;
	}
	//If no error occurred, clean up created files
	if($error === false)
	{
		$codeadd=implode("\r\n",array_unique(array_filter(explode("\r\n",file_get_contents("upgrade/prepare/code-add.txt")))));
		$coderem=implode("\r\n",array_unique(array_filter(explode("\r\n",file_get_contents("upgrade/prepare/code-rem.txt")))));
		$confadd=implode("\r\n",array_filter(explode("\r\n",file_get_contents("upgrade/prepare/conf-add.txt"))));
		$confchg=implode("\r\n",array_filter(explode("\r\n",file_get_contents("upgrade/prepare/conf-chg.txt"))));
		$confrem=implode("\r\n",array_filter(explode("\r\n",file_get_contents("upgrade/prepare/conf-rem.txt"))));
		$fcodeadd=fopen("upgrade/prepare/code-add.txt",'w');
		$fcoderem=fopen("upgrade/prepare/code-rem.txt",'w');
		$fconfadd=fopen("upgrade/prepare/conf-add.txt",'w');
		$fconfchg=fopen("upgrade/prepare/conf-chg.txt",'w');
		$fconfrem=fopen("upgrade/prepare/conf-rem.txt",'w');
		if($fcodeadd && $fcoderem && $fconfadd && $fconfchg && $fconfrem)
		{
			fwrite($fcodeadd,$codeadd);
			fclose($fcodeadd);
			fwrite($fcoderem,$coderem);
			fclose($fcoderem);
			fwrite($fconfadd,$confadd);
			fclose($fconfadd);
			fwrite($fconfrem,$confrem);
			fclose($fconfrem);
			fwrite($fconfchg,$confchg);
			fclose($fconfchg);
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Cleaned up final change files");
		}
		else
		{
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to prepare upgrades: cannot open prepared files.");
			trigger_error("Cannot prepare final upgrade pack as the final files cannot be opened.",E_USER_ERROR);
			$error=true;
		}
	}
	//If no error occurred, set the prepare complete flag
	if($error === false)
	{
		$fh=fopen("upgrade/prepare-complete.txt",'w');
		if($fh)
		{
			fclose($fh);
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Set prepare complete flag");
		}
		else
		{
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to set prepare complete flag");
			trigger_error("Cannot set prepare complete flag.",E_USER_ERROR);
			$error=true;
		}
	}
	//Redirect back out based on end result
	if($error === false)
	{
		write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Finished preparing updates");
		echo("<script type=\"text/javascript\">window.location = \"index.php?prepare=y\"</script>");
	}
	else
	{
		write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to prepare updates");
		echo("<script type=\"text/javascript\">window.location = \"index.php?prepare=n\"</script>");
	}
	//Change back to upgrader directory to avoid breaking everything else
	chdir("upgrade");
  ?>
  <body>
  <h1 style="text-align:center; text-decoration:underline;"><?php echo $sysname; ?>Music Request System-Preparing Updates For Installation</h1>
  <p>You should not see this page. If you do, it's likely something has been reduced to custard.</p>
  <p><a href="index.php">Go back</a></p>
  </body>
</html>