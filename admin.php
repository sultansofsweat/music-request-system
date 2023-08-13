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
	//Useful functions
	
	//Function for determining if the system password has not been changed
	function first_use()
	{
		if(password_verify("admin",get_system_password()) === true)
		{
			return true;
		}
		return false;
	}
	//Function for determining if the MRS is running on a "compliant" (i.e. 5.5.0 or newer) PHP version
	function determine_compliance()
	{
		if(function_exists("version_compare"))
		{
			//Return the result of a version compare with the running PHP version and 5.5.0.
			return version_compare(phpversion(),"5.5.0",">=");
		}
		//Automatically assume non-compliance since it can't be checked
		return false;
	}
	
	//Function for reformatting all dates
	function reformat_dates($newformat)
	{
		foreach(get_requests() as $req)
		{
			$time=strtotime($req[3]);
			$debug=write_request($req[0],$req[1],$req[2],date($newformat,$time),$req[4],$req[5],$req[6],$req[7],$req[8]);
			if($debug === false)
			{
				trigger_error("Could not reformat post " . $req[0] . ", please microwave it.",E_USER_WARNING);
			}
		}
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
?>
<?php
	//Ancilliary page error handlers
	if(isset($_GET['slstatus']))
	{
		if($_GET['slstatus'] == 0)
		{
			echo ("Successfully changed song list.<br>\r\n");
		}
		elseif($_GET['slstatus'] == 1)
		{
			echo ("Failed to change song list. The list requires prompt microwaving.<br>\r\n");
		}
		else
		{
			echo ("Failed to change song list. Some wicked unidentifiable problem occurred and the whole system needs prompt microwaving.<br>\r\n");
		}
	}
	if(isset($_GET['blstatus']))
	{
		if($_GET['blstatus'] == 0)
		{
			echo ("Successfully changed base list.<br>\r\n");
		}
		elseif($_GET['blstatus'] == 1)
		{
			echo ("Failed to change base list. The list requires prompt microwaving.<br>\r\n");
		}
		else
		{
			echo ("Failed to change base list. Some wicked unidentifiable problem occurred and the whole system needs prompt microwaving.<br>\r\n");
		}
	}
	if(isset($_GET['pchange']))
	{
		if($_GET['pchange'] == 0)
		{
			echo ("Successfully changed password.<br>\r\n");
		}
		elseif($_GET['pchange'] == 1)
		{
			echo ("Failed to change password: user had ONE JOB and the old password given was incorrect.<br>\r\n");
		}
		elseif($_GET['pchange'] == 2)
		{
			echo ("Failed to change password: user had ONE JOB and the new password did not verify.<br>\r\n");
		}
		elseif($_GET['pchange'] == 3)
		{
			echo ("Failed to change password: the password file was dunked in a pool and couldn't be found or opened.<br>\r\n");
		}
		else
		{
			echo ("Failed to change password. Some wicked unidentifiable problem occurred and the whole system needs prompt microwaving.<br>\r\n");
		}
	}
	if(isset($_GET['rl']))
	{
		if($_GET['rl'] == 0)
		{
			echo ("Successfully changed rule list.<br>\r\n");
		}
		else
		{
			echo ("Failed to change rule list. Some wicked unidentifiable problem occurred and the whole system needs prompt microwaving.<br>\r\n");
		}
	}
	if(isset($_GET['autoset']))
	{
		if($_GET['autoset'] == "yes")
		{
			echo ("Successfully changed automation settings.<br>\r\n");
		}
		else
		{
			echo ("Failed to change automation settings. Throw a GPX clock radio at the server.<br>\r\n");
		}
	}
	if(isset($_GET['copyset']))
	{
		if($_GET['copyset'] == "yes")
		{
			echo ("Successfully changed copyright information.<br>\r\n");
		}
		else
		{
			echo ("Failed to change copyright information. It's time to hit the server with a bug bomb. Or, maybe even a real bomb.<br>\r\n");
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
    <title><?php echo $sysname; ?>Music Request System-Administration</title>
    
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
  <?php
	//Set up all settings variables
	$name=stripcslashes(get_system_setting("name"));
	$sysmessage=stripcslashes(get_system_setting("sysmessage"));
	$timezone=get_system_setting("timezone");
	$logging=get_system_setting("logging");
	$uas=get_system_setting("altsesstore");
	$asl=get_system_setting("altsesstorepath");
	$errlvl=get_system_setting('errlvl');
	$logerr=get_system_setting('logerr');
	$autorefresh=get_system_setting("autorefresh");
	$eroc=get_system_setting("eroc");
	$status=get_system_setting("status");
	$vcomments=get_system_setting("viewcomments");
	$pexpire=get_system_setting("postexpiry")/60/60;
	$blanking=get_system_setting('blanking');
	$light=get_system_setting("light");
	$search=get_system_setting("searching");
	$stripwords=stripcslashes(get_system_setting("stripwords"));
	$reqrestrict=""; //$reqrestrict=stripcslashes(get_system_setting("stripwords"));
	$songformat=get_system_setting("songformat");
	$songformathr=get_system_setting("songformathr");
	$hidehr=get_system_setting("hidenr");
	$extlists=get_system_setting("extlists");
	$christmas=get_system_setting("christmas");
	$posting=get_system_setting("posting");
	$comments=get_system_setting("comments");
	$anon=get_system_setting("anon");
	$open=get_system_setting("open");
	$pdreq=get_system_setting("pdreq");
	$unlock=get_system_setting("unlock");
	$iplock=get_system_setting("iplock");
	$type=get_system_setting("type");
	$daylock=get_system_setting("dayrestrict");
	$overflow=get_system_setting("limit");
	$api=get_system_setting("interface");
	$sysuid=get_system_setting("sysid");
	$apipages=explode(",",get_system_setting("apipages"));
	$upgrade=get_system_setting("stable");
	$datetime=get_system_setting("datetime");
	$timelimit=30; //$timelimit=get_system_setting("timelimit");
	$popular=get_system_setting("popular");
	$recent=get_system_setting("recent");
    $rss=get_system_setting("rss");
	$autoban=get_system_setting("autoban");
	$banwords=get_system_setting("banwords");
	$partial=get_system_setting("partial");
	$beforeban=get_system_setting("beforeban");
	$logatt=get_system_setting("logatt");
    $banfail=get_system_setting("banfail");
    $reqpass=get_system_setting("passreq");
    $baninvpass=get_system_setting("baninvpass");
	$autoopen=get_system_setting("autoopen");
	$mirror=get_system_setting("mirror");
	$ipundlimit=get_system_setting("ipundlimit");
	if(is_logging_enabled() === true)
	{
		set_timezone();
		if(isset($_POST['s']) && $_POST['s'] == "y" && securitycheck() === true)
		{
			//Begin submission
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Started to save administration settings");
			if(isset($_POST['default']) && $_POST['default'] == "y")
			{
				//Set all settings to defaults
				$name=stripcslashes(get_system_default("name"));
				$sysmessage=stripcslashes(get_system_default("sysmessage"));
				$timezone=get_system_default("timezone");
				$logging=get_system_default("logging");
				$uas=get_system_default("altsesstore");
				$asl=get_system_default("altsesstorepath");
				$errlvl=get_system_default('errlvl');
				$logerr=get_system_default('logerr');
				$autorefresh=get_system_default("autorefresh");
				$eroc=get_system_default("eroc");
				$status=get_system_default("status");
				$vcomments=get_system_default("viewcomments");
				$pexpire=get_system_default("postexpiry")/60/60;
				$blanking=get_system_default('blanking');
				$light=get_system_default("light");
				$search=get_system_default("searching");
				$stripwords=stripcslashes(get_system_default("stripwords"));
				$reqrestrict=""; //$reqrestrict=stripcslashes(get_system_default("stripwords"));
				$songformat=get_system_default("songformat");
				$songformathr=get_system_default("songformathr");
				$hidehr=get_system_default("hidenr");
				$extlists=get_system_default("extlists");
				$christmas=get_system_default("christmas");
				$posting=get_system_default("posting");
				$comments=get_system_default("comments");
				$anon=get_system_default("anon");
				$open=get_system_default("open");
				$pdreq=get_system_default("pdreq");
				$unlock=get_system_default("unlock");
				$iplock=get_system_default("iplock");
				$type=get_system_default("type");
				$daylock=get_system_default("dayrestrict");
				$overflow=get_system_default("limit");
				$api=get_system_default("interface");
				$sysuid=get_system_default("sysid");
				$apipages=explode(",",get_system_default("apipages"));
				$upgrade=get_system_default("stable");
				$datetime=get_system_default("datetime");
				$timelimit=30; //$timelimit=get_system_default("timelimit");
                $popular=get_system_default("popular");
				$recent=get_system_default("recent");
                $rss=get_system_default("rss");
				$autoban=get_system_default("autoban");
				$banwords=get_system_default("banwords");
				$partial=get_system_default("partial");
				$beforeban=get_system_default("beforeban");
				$logatt=get_system_default("logatt");
                $banfail=get_system_default("banfail");
                $reqpass=get_system_default("passreq");
                $baninvpass=get_system_default("baninvpass");
				$autoopen=get_system_default("autoopen");
				$mirror=get_system_default("mirror");
				$ipundlimit=get_system_default("ipundlimit");
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Set settings to default");
				trigger_error("Set all settings to their default values.");
			}
			elseif(isset($_POST['setdef']) && $_POST['setdef'] == "y")
			{
				//Set all current settings as the defaults
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Set current settings as default");
				trigger_error("This feature is not yet implemented.");
			}
			else
			{
				//Set error flag
				$error=false;
				//Get all posted settings
				if(isset($_POST['name']))
				{
					$name=htmlspecialchars($_POST['name']);
					$debug=save_system_setting("name",$name);
					if($debug !== true)
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"name\" to \"$name\"");
						$error=true;
					}
					else
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Changed setting \"name\" to \"$name\"");
					}
				}
				if(isset($_POST['sysmessage']))
				{
					$sysmessage=htmlspecialchars($_POST['sysmessage']);
					$debug=save_system_setting("sysmessage",stripcslashes($sysmessage));
					if($debug !== true)
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"sysmessage\" to \"$sysmessage\"");
						$error=true;
					}
					else
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Changed setting \"sysmessage\" to \"$sysmessage\"");
					}
				}
				if(isset($_POST['zone']))
				{
					switch($_POST['zone'])
					{
						case "America/Toronto":
						case "America/Winnipeg":
						case "America/Denver":
						case "America/Phoenix":
						case "America/Vancouver":
						$timezone=$_POST['zone'];
						break;
						default:
						$timezone="America/Toronto";
						trigger_error("Timezone submitted not understood, reverting to default.",E_USER_WARNING);
						break;
					}
					$debug=save_system_setting("timezone",$timezone);
					if($debug !== true)
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"timezone\" to \"$timezone\"");
						$error=true;
					}
					else
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Changed setting \"timezone\" to \"$timezone\"");
					}
				}
				if(isset($_POST['logging']))
				{
					if($_POST['logging'] == "yes")
					{
						$logging="yes";
					}
					else
					{
						$logging="no";
					}
					$debug=save_system_setting("logging",$logging);
					if($debug !== true)
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"logging\" to \"$logging\"");
						$error=true;
					}
					else
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Changed setting \"logging\" to \"$logging\"");
					}
				}
				if(isset($_POST['uas']) && isset($_POST['asl']))
				{
					if($_POST['uas'] == "yes")
					{
						$uas="yes";
					}
					else
					{
						$uas="no";
					}
					$asl=preg_replace("/[^A-Za-z0-9]/","",$_POST['asl']);
					if($uas == "yes" && ($asl == "" || !file_exists($asl) || !is_dir($asl)))
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"altsesstore\" to \"$uas\": path name for \"altsesstorepath\" invalid");
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"altsesstorepath\" to \"$asl\": path name invalid");
						trigger_error("Path specified for alternative storage doesn't exist, ignoring.",E_USER_WARNING);
					}
					else
					{
						$debug=save_system_setting("altsesstorepath",$asl);
						if($debug !== true)
						{
							write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"altsesstorepath\" to \"$asl\"");
							write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"altsesstore\" to \"$uas\": companion setting \"altsesstorepath\" failed to save");
							$error=true;
						}
						else
						{
							write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Changed setting \"altsesstorepath\" to \"$asl\"");
							$debug=save_system_setting("altsesstore",$uas);
							if($debug !== true)
							{
								write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"altsesstore\" to \"$uas\"");
								$error=true;
							}
							else
							{
								write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Changed setting \"altsesstore\" to \"$uas\"");
							}
						}
					}
				}
				if(isset($_POST['errlvl']))
				{
					$errlvl=preg_replace("/[^0-2]/","",$_POST['errlvl']);
					if($errlvl == "")
					{
						$errlvl=get_system_setting("errlvl");
					}
					$debug=save_system_setting("errlvl",$errlvl);
					if($debug !== true)
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"errlvl\" to \"$errlvl\"");
						$error=true;
					}
					else
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Changed setting \"errlvl\" to \"$errlvl\"");
					}
				}
				if(isset($_POST['logerr']))
				{
					if($_POST['logerr'] == "yes")
					{
						$logerr="yes";
					}
					else
					{
						$logerr="no";
					}
					$debug=save_system_setting("logerr",$logerr);
					if($debug !== true)
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"logerr\" to \"$logerr\"");
						$error=true;
					}
					else
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Changed setting \"logerr\" to \"$logerr\"");
					}
				}
				if(isset($_POST['autorefresh']))
				{
					$autorefresh=preg_replace("/[^0-9]/","",$_POST['autorefresh']);
					if($autorefresh == "")
					{
						$autorefresh=get_system_setting("autorefresh");
					}
					$debug=save_system_setting("autorefresh",$autorefresh);
					if($debug !== true)
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"autorefresh\" to \"$autorefresh\"");
						$error=true;
					}
					else
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Changed setting \"autorefresh\" to \"$autorefresh\"");
					}
				}
				if(isset($_POST['eroc']))
				{
					if($_POST['eroc'] == "yes")
					{
						$eroc="yes";
					}
					else
					{
						$eroc="no";
					}
					$debug=save_system_setting("eroc",$eroc);
					if($debug !== true)
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"eroc\" to \"$eroc\"");
						$error=true;
					}
					else
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Changed setting \"eroc\" to \"$eroc\"");
					}
				}
				if(isset($_POST['status']))
				{
					if($_POST['status'] == "yes")
					{
						$status="yes";
					}
					else
					{
						$status="no";
					}
					$debug=save_system_setting("status",$status);
					if($debug !== true)
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"status\" to \"$status\"");
						$error=true;
					}
					else
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Changed setting \"status\" to \"$status\"");
					}
				}
				if(isset($_POST['vcomments']))
				{
					if($_POST['vcomments'] == "yes")
					{
						$vcomments="yes";
					}
					else
					{
						$vcomments="no";
					}
					$debug=save_system_setting("viewcomments",$vcomments);
					if($debug !== true)
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"viewcomments\" to \"$vcomments\"");
						$error=true;
					}
					else
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Changed setting \"viewcomments\" to \"$vcomments\"");
					}
				}
				if(isset($_POST['pexpire']))
				{
					switch($_POST['pexpire'])
					{
						case 1:
						case 3:
						case 24:
						$pexpire=$_POST['pexpire'];
						break;
						default:
						trigger_error("Post expiry time submitted not understood, ignoring.",E_USER_WARNING);
						$pexpire=get_system_setting("postexpiry")/60/60;
						break;
					}
					$debug=save_system_setting("postexpiry",$pexpire*60*60);
					if($debug !== true)
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"postexpiry\" to \"$pexpire\"");
						$error=true;
					}
					else
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Changed setting \"postexpiry\" to \"$pexpire\"");
					}
				}
				if(isset($_POST['blanking']))
				{
					if($_POST['blanking'] == "yes")
					{
						$blanking="yes";
					}
					else
					{
						$blanking="no";
					}
					$debug=save_system_setting("blanking",$blanking);
					if($debug !== true)
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"blanking\" to \"$blanking\"");
						$error=true;
					}
					else
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Changed setting \"blanking\" to \"$blanking\"");
					}
				}
				if(isset($_POST['light']))
				{
					if($_POST['light'] == "yes")
					{
						$light="yes";
					}
					else
					{
						$light="no";
					}
					$debug=save_system_setting("light",$light);
					if($debug !== true)
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"light\" to \"$light\"");
						$error=true;
					}
					else
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Changed setting \"light\" to \"$light\"");
					}
				}
				if(isset($_POST['search']))
				{
					if($_POST['search'] == "yes")
					{
						$search="yes";
					}
					else
					{
						$search="no";
					}
					$debug=save_system_setting("searching",$search);
					if($debug !== true)
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"searching\" to \"$search\"");
						$error=true;
					}
					else
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Changed setting \"searching\" to \"$search\"");
					}
				}
				if(isset($_POST['stripwords']))
				{
					$stripwords=htmlspecialchars($_POST['stripwords']);
					$debug=save_system_setting("stripwords",stripcslashes($stripwords));
					if($debug !== true)
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"stripwords\" to \"$stripwords\"");
						$error=true;
					}
					else
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Changed setting \"stripwords\" to \"$stripwords\"");
					}
				}
				if(isset($_POST['reqrestrict']))
				{
					$reqrestrict=htmlspecialchars($_POST['reqrestrict']);
					$debug=save_system_setting("reqrestrict",$reqrestrict);
					if($debug !== true)
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"reqrestrict\" to \"$reqrestrict\"");
						$error=true;
					}
					else
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Changed setting \"reqrestrict\" to \"$reqrestrict\"");
					}
				}
				if(isset($_POST['songformat']))
				{
					$songformat=preg_replace("/[^a-z0-9\*\|]/","",$_POST['songformat']);
					$debug=save_system_setting("songformat",$songformat);
					if($debug !== true)
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"songformat\" to \"$songformat\"");
						$error=true;
					}
					else
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Changed setting \"songformat\" to \"$songformat\"");
					}
				}
				if(isset($_POST['songformathr']))
				{
					$songformathr=preg_replace("/[^A-Za-z0-9 \|]/","",$_POST['songformathr']);
					$debug=save_system_setting("songformathr",$songformathr);
					if($debug !== true)
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"songformathr\" to \"$songformathr\"");
						$error=true;
					}
					else
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Changed setting \"songformathr\" to \"$songformathr\"");
					}
				}
				if(isset($_POST['hidenr']))
				{
					$hidehr=preg_replace("/[^0-2]/","",$_POST['hidenr']);
					if($hidehr == "")
					{
						$hidehr=get_system_setting("hidenr");
					}
					$debug=save_system_setting("hidenr",$hidehr);
					if($debug !== true)
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"hidenr\" to \"$hidehr\"");
						$error=true;
					}
					else
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Changed setting \"hidenr\" to \"$hidehr\"");
					}
				}
				if(isset($_POST['extlists']))
				{
					$extlists=htmlspecialchars($_POST['extlists']);
					$debug=save_system_setting("extlists",$extlists);
					if($debug !== true)
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"extlists\" to \"$extlists\"");
						$error=true;
					}
					else
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Changed setting \"extlists\" to \"$extlists\"");
					}
				}
				if(isset($_POST['christmas']))
				{
                    $christmas="yes";
                }
                else
                {
                    $christmas="no";
                }
                $debug=save_system_setting("christmas",$christmas);
                if($debug !== true)
                {
                    write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"christmas\" to \"$christmas\"");
                    $error=true;
                }
                else
                {
                    write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Changed setting \"christmas\" to \"$christmas\"");
                }
				if(isset($_POST['posting']))
				{
					if($_POST['posting'] == "yes")
					{
						$posting="yes";
					}
					else
					{
						$posting="no";
					}
					$debug=save_system_setting("posting",$posting);
					if($debug !== true)
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"posting\" to \"$posting\"");
						$error=true;
					}
					else
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Changed setting \"posting\" to \"$posting\"");
					}
				}
				if(isset($_POST['comments']))
				{
					if($_POST['comments'] == "yes")
					{
						$comments="yes";
					}
					else
					{
						$comments="no";
					}
					$debug=save_system_setting("comments",$comments);
					if($debug !== true)
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"comments\" to \"$comments\"");
						$error=true;
					}
					else
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Changed setting \"comments\" to \"$comments\"");
					}
				}
				if(isset($_POST['anon']))
				{
					if($_POST['anon'] == "yes")
					{
						$anon="yes";
					}
					else
					{
						$anon="no";
					}
					$debug=save_system_setting("anon",$anon);
					if($debug !== true)
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"anon\" to \"$anon\"");
						$error=true;
					}
					else
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Changed setting \"anon\" to \"$anon\"");
					}
				}
				if(isset($_POST['open']))
				{
					if($_POST['open'] == "yes")
					{
						$open="yes";
					}
					else
					{
						$open="no";
					}
					$debug=save_system_setting("open",$open);
					if($debug !== true)
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"open\" to \"$open\"");
						$error=true;
					}
					else
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Changed setting \"open\" to \"$open\"");
					}
				}
				if(isset($_POST['pdreq']))
				{
					if($_POST['pdreq'] == "yes")
					{
						$pdreq="yes";
					}
					else
					{
						$pdreq="no";
					}
					$debug=save_system_setting("pdreq",$pdreq);
					if($debug !== true)
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"pdreq\" to \"$pdreq\"");
						$error=true;
					}
					else
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Changed setting \"pdreq\" to \"$pdreq\"");
					}
				}
				if(isset($_POST['unlock']))
				{
					$unlock=preg_replace("/[^0-9]/","",$_POST['unlock']);
					if($unlock == "")
					{
						$unlock=get_system_setting("unlock");
					}
					$debug=save_system_setting("unlock",$unlock);
					if($debug !== true)
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"unlock\" to \"$unlock\"");
						$error=true;
					}
					else
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Changed setting \"unlock\" to \"$unlock\"");
					}
				}
				if(isset($_POST['iplock']))
				{
					$iplock=preg_replace("/[^0-9]/","",$_POST['iplock']);
					if($iplock == "")
					{
						$iplock=get_system_setting("iplock");
					}
					$debug=save_system_setting("iplock",$iplock);
					if($debug !== true)
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"iplock\" to \"$iplock\"");
						$error=true;
					}
					else
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Changed setting \"iplock\" to \"$iplock\"");
					}
				}
				if(isset($_POST['type']))
				{
					$type=preg_replace("/[^0-2]/","",$_POST['type']);
					if($type == "")
					{
						$type=get_system_setting("type");
					}
					$debug=save_system_setting("type",$type);
					if($debug !== true)
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"type\" to \"$type\"");
						$error=true;
					}
					else
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Changed setting \"type\" to \"$type\"");
					}
				}
				if(isset($_POST['dayrestrict']))
				{
					$daylock=preg_replace("/[^0-9]/","",$_POST['dayrestrict']);
					if($daylock == "")
					{
						$daylock=get_system_setting("dayrestrict");
					}
					$debug=save_system_setting("dayrestrict",$daylock);
					if($debug !== true)
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"dayrestrict\" to \"$daylock\"");
						$error=true;
					}
					else
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Changed setting \"dayrestrict\" to \"$daylock\"");
					}
				}
				if(isset($_POST['overflow']))
				{
					$overflow=preg_replace("/[^0-9]/","",$_POST['overflow']);
					if($overflow == "")
					{
						$overflow=get_system_setting("limit");
					}
					$debug=save_system_setting("limit",$overflow);
					if($debug !== true)
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"limit\" to \"$overflow\"");
						$error=true;
					}
					else
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Changed setting \"limit\" to \"$overflow\"");
					}
				}
				if(isset($_POST['api']))
				{
					if($_POST['api'] == "yes")
					{
						$api="yes";
					}
					else
					{
						$api="no";
					}
					$debug=save_system_setting("interface",$api);
					if($debug !== true)
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"interface\" to \"$api\"");
						$error=true;
					}
					else
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Changed setting \"interface\" to \"$api\"");
					}
				}
				if(isset($_POST['genuid']) && $_POST['genuid'] == "y")
				{
					$sysuid=uniqid("",true);
					$debug=save_system_setting("sysid",$sysuid);
					if($debug !== true)
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"sysid\" to \"$sysuid\"");
						$error=true;
					}
					else
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Changed setting \"sysid\" to \"$sysuid\"");
					}
				}
				if(isset($_POST['napipass']) && $_POST['napipass'] != "" && isset($_POST['capipass']) && $_POST['napipass'] == $_POST['capipass'])
				{
					$apikey=password_hash($_POST['napipass'],PASSWORD_DEFAULT);
					$debug=save_system_setting("autokey",$apikey);
					if($debug !== true)
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"autokey\" to \"********\"");
						$error=true;
					}
					else
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Changed setting setting \"autokey\" to \"********\"");
					}
				}
                elseif(isset($_POST['napipass']) && $_POST['napipass'] != "" && isset($_POST['capipass']) && $_POST['capipass'] != "")
                {
                    write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"autokey\" to \"********\": passwords did not match");
					$error=true;
                }
				if(isset($_POST['apipages']) && is_array($_POST['apipages']))
				{
					$apipages=array();
					foreach($_POST['apipages'] as $page)
					{
						$apipages[]=preg_replace("/[^0-6]/","",$page);
					}
					$apipages=implode(",",array_filter(array_unique($apipages)));
					$debug=save_system_setting("apipages",$apipages);
					if($debug !== true)
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"apipages\" to \"$apipages\"");
						$error=true;
					}
					else
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Changed setting \"apipages\" to \"$apipages\"");
					}
                    $apipages=explode(",",$apipages);
				}
				if(isset($_POST['upgrade']))
				{
					if($_POST['upgrade'] == "yes")
					{
						$upgrade="yes";
					}
					else
					{
						$upgrade="no";
					}
					$debug=save_system_setting("stable",$upgrade);
					if($debug !== true)
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"stable\" to \"$upgrade\"");
						$error=true;
					}
					else
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Changed setting \"stable\" to \"$upgrade\"");
					}
				}
				if(isset($_POST['datetime']))
				{
					$datetime=htmlspecialchars($_POST['datetime']);
					if($datetime == "")
					{
						$datetime=get_system_setting("datetime");
					}
					$debug=save_system_setting("datetime",$datetime);
					if($debug !== true)
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"datetime\" to \"$datetime\"");
						$error=true;
					}
					else
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Changed setting \"datetime\" to \"$datetime\"");
					}
				}
				if(isset($_POST['resetdate']) && $_POST['resetdate'] == "y")
				{
					$resetdate="y";
					reformat_dates($datetime);
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Reformatted all post dates");
				}
				if(isset($_POST['timelimit']))
				{
					$timelimit=preg_replace("/[^0-9]/","",$_POST['timelimit']);
					$debug=save_system_setting("timelimit",$timelimit);
					if($debug !== true)
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"timelimit\" to \"$timelimit\"");
						$error=true;
					}
					else
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Changed setting \"timelimit\" to \"$timelimit\"");
					}
				}
				if(isset($_POST['popular']))
				{
					$popular=max(1,preg_replace("/[^0-9]/","",$_POST['popular']));
					$debug=save_system_setting("popular",$popular);
					if($debug !== true)
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"popular\" to \"$popular\"");
						$error=true;
					}
					else
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Changed setting \"popular\" to \"$popular\"");
					}
				}
				if(isset($_POST['rss']))
				{
					if($_POST['rss'] == "yes")
					{
						$rss="yes";
					}
					else
					{
						$rss="no";
					}
					$debug=save_system_setting("rss",$rss);
					if($debug !== true)
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"rss\" to \"$rss\"");
						$error=true;
					}
					else
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Changed setting \"rss\" to \"$rss\"");
					}
				}
				if(isset($_POST['autoban']))
				{
					if($_POST['autoban'] == "yes")
					{
						$autoban="yes";
					}
					else
					{
						$autoban="no";
					}
					$debug=save_system_setting("autoban",$autoban);
					if($debug !== true)
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"autoban\" to \"$autoban\"");
						$error=true;
					}
					else
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Changed setting \"autoban\" to \"$autoban\"");
					}
				}
				if(isset($_POST['banwords']))
				{
					$banwords=preg_replace("/\s+/","", htmlspecialchars($_POST['banwords']));
					$debug=save_system_setting("banwords",$banwords);
					if($debug !== true)
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"banwords\" to \"$banwords\"");
						$error=true;
					}
					else
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Changed setting \"banwords\" to \"$banwords\"");
					}
				}
				if(isset($_POST['partial']) && $_POST['partial'] == "yes")
				{
					$partial="yes";
				}
				else
				{
					$partial="no";
				}
				if($partial != get_system_setting("partial"))
				{
					$debug=save_system_setting("partial",$partial);
					if($debug !== true)
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"partial\" to \"$partial\"");
						$error=true;
					}
					else
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Changed setting \"partial\" to \"$partial\"");
					}
				}
				if(isset($_POST['beforeban']))
				{
					$beforeban=preg_replace("/[^0-9]/","",$_POST['beforeban']);
					$debug=save_system_setting("beforeban",$beforeban);
					if($debug !== true)
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"beforeban\" to \"$beforeban\"");
						$error=true;
					}
					else
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Changed setting \"beforeban\" to \"$beforeban\"");
					}
				}
				if(isset($_POST['logatt']))
				{
					if($_POST['logatt'] == "yes")
					{
						$logatt="yes";
					}
					else
					{
						$logatt="no";
					}
					$debug=save_system_setting("logatt",$logatt);
					if($debug !== true)
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"logatt\" to \"$logatt\"");
						$error=true;
					}
					else
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Changed setting \"logatt\" to \"$logatt\"");
					}
				}
				if(isset($_POST['baninvpass']))
				{
					if($_POST['baninvpass'] == "yes")
					{
						$baninvpass="yes";
					}
					else
					{
						$baninvpass="no";
					}
					$debug=save_system_setting("baninvpass",$baninvpass);
					if($debug !== true)
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"baninvpass\" to \"$baninvpass\"");
						$error=true;
					}
					else
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Changed setting \"baninvpass\" to \"$baninvpass\"");
					}
				}
				if(isset($_POST['reqpass']))
				{
					if($_POST['reqpass'] == "yes")
					{
						$reqpass="yes";
					}
					else
					{
						$reqpass="no";
					}
                    if($reqpass == "yes" && isset($_POST['ereqpass']) && isset($_POST['creqpass']) && $_POST['ereqpass'] == $_POST['creqpass'])
                    {
                        $debug=save_request_password($_POST['ereqpass']);
                        if($debug !== true)
                        {
                            write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to save new request password, not enabling request password entry");
                            $error=true;
                        }
                        else
                        {
                            write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Saved new request password");
                            $debug=save_system_setting("passreq",$reqpass);
                            if($debug !== true)
                            {
                                write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"passreq\" to \"$reqpass\"");
                                $error=true;
                            }
                            else
                            {
                                write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Changed setting \"passreq\" to \"$reqpass\"");
                            }
                        }
                    }
                    elseif($reqpass == "no")
                    {
                        $debug=save_system_setting("passreq",$reqpass);
                        if($debug !== true)
                        {
                            write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"passreq\" to \"$reqpass\"");
                            $error=true;
                        }
                        else
                        {
                            write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Changed setting \"passreq\" to \"$reqpass\"");
                        }
                    }
                    else
                    {
                        write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"passreq\" to \"$reqpass\": invalid password supplied");
                        trigger_error("Cannot set request password as they do not match. Try again.",E_USER_ERROR);
                        $error=true;
                    }
				}
				if(isset($_POST['banfail']))
				{
					$banfail=max(0,preg_replace("/[^0-9]/","",$_POST['banfail']));
					$debug=save_system_setting("banfail",$banfail);
					if($debug !== true)
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"banfail\" to \"$banfail\"");
						$error=true;
					}
					else
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Changed setting \"banfail\" to \"$banfail\"");
					}
				}
				if(isset($_POST['autoopen']))
				{
					if($_POST['autoopen'] == "yes")
					{
						$autoopen="yes";
					}
					else
					{
						$autoopen="no";
					}
					$debug=save_system_setting("autoopen",$autoopen);
					if($debug !== true)
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"autoopen\" to \"$autoopen\"");
						$error=true;
					}
					else
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Changed setting \"autoopen\" to \"$autoopen\"");
					}
				}
				if(isset($_POST['mirror']))
				{
					$mirror=filter_var($_POST['mirror'],FILTER_SANITIZE_URL);
					$debug=save_system_setting("mirror",$mirror);
					if($debug !== true)
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"mirror\" to \"$mirror\"");
						$error=true;
					}
					else
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Changed setting \"mirror\" to \"$mirror\"");
					}
				}
				if(isset($_POST['ipundlimit']))
				{
					$ipundlimit=max(0,min(2,preg_replace("/[^0-2]/","",$_POST['ipundlimit'])));
					$debug=save_system_setting("ipundlimit",$ipundlimit);
					if($debug !== true)
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"ipundlimit\" to \"$ipundlimit\"");
						$error=true;
					}
					else
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Changed setting \"ipundlimit\" to \"$ipundlimit\"");
					}
				}
				if($error === true)
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change all system settings");
					$return="no";
				}
				else
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Successfully changed all system settings");
					$return="yes";
				}
				if(!isset($_POST['debug']) || $_POST['debug'] != "y")
				{
					die("<script type=\"text/javascript\">window.location = \"index.php?admsave=$return\"</script>");
				}
			}
		}
		else
		{
			//Visiting page
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Visited administration console");
			if(securitycheck() === false)
			{
				die("<p>You are not an administrator. Please <a href=\"login.php?ref=admin\">sign in</a> or <a href=\"index.php\">cancel</a>.</p>");
			}
			//Check for a "first use" flag file and notify the admin that they should change the poassword
			if(first_use() === true)
			{
				trigger_error("The administrator password is the default! Please consider changing it.",E_USER_WARNING);
			}
			//Check for PHP version compliance and issue a notice if non-compliance is found
			if(determine_compliance() === false)
			{
				trigger_error("Use of non-compliant PHP versions may not be allowed in future releases. Please upgrade to at least PHP 5.5.0 before installing further MRS upgrades.",E_USER_DEPRECATED);
			}
			//If deprecation log has entries, throw a notice
			if(is_dep_log_blank() !== true)
			{
				trigger_error("There are entries in the deprecation log! Please report these if you have not done so!");
			}
		}
	}
	else
	{
		set_timezone();
		if(isset($_POST['s']) && $_POST['s'] == "y" && securitycheck() === true)
		{
			//Begin submission
			if(isset($_POST['default']) && $_POST['default'] == "y")
			{
				//Set all settings to defaults
				$name=stripcslashes(get_system_default("name"));
				$sysmessage=stripcslashes(get_system_default("sysmessage"));
				$timezone=get_system_default("timezone");
				$logging=get_system_default("logging");
				$uas=get_system_default("altsesstore");
				$asl=get_system_default("altsesstorepath");
				$errlvl=get_system_default('errlvl');
				$logerr=get_system_default('logerr');
				$autorefresh=get_system_default("autorefresh");
				$eroc=get_system_default("eroc");
				$status=get_system_default("status");
				$vcomments=get_system_default("viewcomments");
				$pexpire=get_system_default("postexpiry")/60/60;
				$blanking=get_system_default('blanking');
				$light=get_system_default("light");
				$search=get_system_default("searching");
				$stripwords=stripcslashes(get_system_default("stripwords"));
				$reqrestrict=""; //$reqrestrict=stripcslashes(get_system_default("stripwords"));
				$songformat=get_system_default("songformat");
				$songformathr=get_system_default("songformathr");
				$hidehr=get_system_default("hidenr");
				$extlists=get_system_default("extlists");
				$christmas=get_system_default("christmas");
				$posting=get_system_default("posting");
				$comments=get_system_default("comments");
				$anon=get_system_default("anon");
				$open=get_system_default("open");
				$pdreq=get_system_default("pdreq");
				$unlock=get_system_default("unlock");
				$iplock=get_system_default("iplock");
				$type=get_system_default("type");
				$daylock=get_system_default("dayrestrict");
				$overflow=get_system_default("limit");
				$api=get_system_default("interface");
				$sysuid=get_system_default("sysid");
				$apipages=explode(",",get_system_default("apipages"));
				$upgrade=get_system_default("stable");
				$datetime=get_system_default("datetime");
				$timelimit=30; //$timelimit=get_system_default("timelimit");
                $popular=get_system_default("popular");
				$recent=get_system_default("recent");
				$autoban=get_system_default("autoban");
				$banwords=get_system_default("banwords");
				$partial=get_system_default("partial");
				$beforeban=get_system_default("beforeban");
				$logatt=get_system_default("logatt");
                $banfail=get_system_default("banfail");
                $reqpass=get_system_default("passreq");
                $baninvpass=get_system_default("baninvpass");
				$autoopen=get_system_default("autoopen");
				$mirror=get_system_default("mirror");
				$ipundlimit=get_system_default("ipundlimit");
				trigger_error("Set all settings to their default values.");
			}
			elseif(isset($_POST['setdef']) && $_POST['setdef'] == "y")
			{
				//Set all current settings as the defaults
				trigger_error("This feature is not yet implemented.");
			}
			else
			{
				//Set error flag
				$error=false;
				//Get all posted settings
				if(isset($_POST['name']))
				{
					$name=htmlspecialchars($_POST['name']);
					$debug=save_system_setting("name",$name);
					if($debug !== true)
					{
						$error=true;
					}
				}
				if(isset($_POST['sysmessage']))
				{
					$sysmessage=htmlspecialchars($_POST['sysmessage']);
					$debug=save_system_setting("sysmessage",stripcslashes($sysmessage));
					if($debug !== true)
					{
						$error=true;
					}
				}
				if(isset($_POST['zone']))
				{
					switch($_POST['zone'])
					{
						case "America/Toronto":
						case "America/Winnipeg":
						case "America/Denver":
						case "America/Phoenix":
						case "America/Vancouver":
						$timezone=$_POST['zone'];
						break;
						default:
						$timezone="America/Toronto";
						trigger_error("Timezone submitted not understood, reverting to default.",E_USER_WARNING);
						break;
					}
					$debug=save_system_setting("timezone",$timezone);
					if($debug !== true)
					{
						$error=true;
					}
				}
				if(isset($_POST['logging']))
				{
					if($_POST['logging'] == "yes")
					{
						$logging="yes";
					}
					else
					{
						$logging="no";
					}
					$debug=save_system_setting("logging",$logging);
					if($debug !== true)
					{
						$error=true;
					}
				}
				if(isset($_POST['uas']) && isset($_POST['asl']))
				{
					if($_POST['uas'] == "yes")
					{
						$uas="yes";
					}
					else
					{
						$uas="no";
					}
					$asl=preg_replace("/[^A-Za-z0-9]/","",$_POST['asl']);
					if($uas == "yes" && ($asl == "" || !file_exists($asl) || !is_dir($asl)))
					{
						trigger_error("Path specified for alternative storage doesn't exist, ignoring.",E_USER_WARNING);
					}
					else
					{
						$debug=save_system_setting("altsesstorepath",$asl);
						if($debug !== true)
						{
							$error=true;
						}
						else
						{
							$debug=save_system_setting("altsesstore",$uas);
							if($debug !== true)
							{
								$error=true;
							}
							else
							{
							}
						}
					}
				}
				if(isset($_POST['errlvl']))
				{
					$errlvl=preg_replace("/[^0-2]/","",$_POST['errlvl']);
					if($errlvl == "")
					{
						$errlvl=get_system_setting("errlvl");
					}
					$debug=save_system_setting("errlvl",$errlvl);
					if($debug !== true)
					{
						$error=true;
					}
				}
				if(isset($_POST['logerr']))
				{
					if($_POST['logerr'] == "yes")
					{
						$logerr="yes";
					}
					else
					{
						$logerr="no";
					}
					$debug=save_system_setting("logerr",$logerr);
					if($debug !== true)
					{
						$error=true;
					}
				}
				if(isset($_POST['autorefresh']))
				{
					$autorefresh=preg_replace("/[^0-9]/","",$_POST['autorefresh']);
					if($autorefresh == "")
					{
						$autorefresh=get_system_setting("autorefresh");
					}
					$debug=save_system_setting("autorefresh",$autorefresh);
					if($debug !== true)
					{
						$error=true;
					}
				}
				if(isset($_POST['eroc']))
				{
					if($_POST['eroc'] == "yes")
					{
						$eroc="yes";
					}
					else
					{
						$eroc="no";
					}
					$debug=save_system_setting("eroc",$eroc);
					if($debug !== true)
					{
						$error=true;
					}
				}
				if(isset($_POST['status']))
				{
					if($_POST['status'] == "yes")
					{
						$status="yes";
					}
					else
					{
						$status="no";
					}
					$debug=save_system_setting("status",$status);
					if($debug !== true)
					{
						$error=true;
					}
				}
				if(isset($_POST['vcomments']))
				{
					if($_POST['vcomments'] == "yes")
					{
						$vcomments="yes";
					}
					else
					{
						$vcomments="no";
					}
					$debug=save_system_setting("viewcomments",$vcomments);
					if($debug !== true)
					{
						$error=true;
					}
				}
				if(isset($_POST['pexpire']))
				{
					switch($_POST['pexpire'])
					{
						case 1:
						case 3:
						case 24:
						$pexpire=$_POST['pexpire'];
						break;
						default:
						trigger_error("Post expiry time submitted not understood, ignoring.",E_USER_WARNING);
						$pexpire=get_system_setting("postexpiry")/60/60;
						break;
					}
					$debug=save_system_setting("postexpiry",$pexpire*60*60);
					if($debug !== true)
					{
						$error=true;
					}
				}
				if(isset($_POST['blanking']))
				{
					if($_POST['blanking'] == "yes")
					{
						$blanking="yes";
					}
					else
					{
						$blanking="no";
					}
					$debug=save_system_setting("blanking",$blanking);
					if($debug !== true)
					{
						$error=true;
					}
				}
				if(isset($_POST['light']))
				{
					if($_POST['light'] == "yes")
					{
						$light="yes";
					}
					else
					{
						$light="no";
					}
					$debug=save_system_setting("light",$light);
					if($debug !== true)
					{
						$error=true;
					}
				}
				if(isset($_POST['search']))
				{
					if($_POST['search'] == "yes")
					{
						$search="yes";
					}
					else
					{
						$search="no";
					}
					$debug=save_system_setting("searching",$search);
					if($debug !== true)
					{
						$error=true;
					}
				}
				if(isset($_POST['stripwords']))
				{
					$stripwords=htmlspecialchars($_POST['stripwords']);
					$debug=save_system_setting("stripwords",stripcslashes($stripwords));
					if($debug !== true)
					{
						$error=true;
					}
				}
				if(isset($_POST['reqrestrict']))
				{
					$reqrestrict=htmlspecialchars($_POST['reqrestrict']);
					$debug=save_system_setting("reqrestrict",$reqrestrict);
					if($debug !== true)
					{
						$error=true;
					}
				}
				if(isset($_POST['songformat']))
				{
					$songformat=preg_replace("/[^a-z0-9\*\|]/","",$_POST['songformat']);
					$debug=save_system_setting("songformat",$songformat);
					if($debug !== true)
					{
						$error=true;
					}
				}
				if(isset($_POST['songformathr']))
				{
					$songformathr=preg_replace("/[^A-Za-z0-9 \|]/","",$_POST['songformathr']);
					$debug=save_system_setting("songformathr",$songformathr);
					if($debug !== true)
					{
						$error=true;
					}
				}
				if(isset($_POST['hidenr']))
				{
					$hidehr=preg_replace("/[^0-2]/","",$_POST['hidenr']);
					if($hidehr == "")
					{
						$hidehr=get_system_setting("hidenr");
					}
					$debug=save_system_setting("hidenr",$hidehr);
					if($debug !== true)
					{
						$error=true;
					}
				}
				if(isset($_POST['extlists']))
				{
					$extlists=htmlspecialchars($_POST['extlists']);
					$debug=save_system_setting("extlists",$extlists);
					if($debug !== true)
					{
						$error=true;
					}
				}
				if(isset($_POST['christmas']))
				{
                    $christmas="yes";
                }
                else
                {
                    $christmas="no";
                }
                $debug=save_system_setting("christmas",$christmas);
                if($debug !== true)
                {
                    $error=true;
                }
				if(isset($_POST['posting']))
				{
					if($_POST['posting'] == "yes")
					{
						$posting="yes";
					}
					else
					{
						$posting="no";
					}
					$debug=save_system_setting("posting",$posting);
					if($debug !== true)
					{
						$error=true;
					}
				}
				if(isset($_POST['comments']))
				{
					if($_POST['comments'] == "yes")
					{
						$comments="yes";
					}
					else
					{
						$comments="no";
					}
					$debug=save_system_setting("comments",$comments);
					if($debug !== true)
					{
						$error=true;
					}
				}
				if(isset($_POST['anon']))
				{
					if($_POST['anon'] == "yes")
					{
						$anon="yes";
					}
					else
					{
						$anon="no";
					}
					$debug=save_system_setting("anon",$anon);
					if($debug !== true)
					{
						$error=true;
					}
				}
				if(isset($_POST['open']))
				{
					if($_POST['open'] == "yes")
					{
						$open="yes";
					}
					else
					{
						$open="no";
					}
					$debug=save_system_setting("open",$open);
					if($debug !== true)
					{
						$error=true;
					}
				}
				if(isset($_POST['pdreq']))
				{
					if($_POST['pdreq'] == "yes")
					{
						$pdreq="yes";
					}
					else
					{
						$pdreq="no";
					}
					$debug=save_system_setting("pdreq",$pdreq);
					if($debug !== true)
					{
						$error=true;
					}
				}
				if(isset($_POST['unlock']))
				{
					$unlock=preg_replace("/[^0-9]/","",$_POST['unlock']);
					if($unlock == "")
					{
						$unlock=get_system_setting("unlock");
					}
					$debug=save_system_setting("unlock",$unlock);
					if($debug !== true)
					{
						$error=true;
					}
				}
				if(isset($_POST['iplock']))
				{
					$iplock=preg_replace("/[^0-9]/","",$_POST['iplock']);
					if($iplock == "")
					{
						$iplock=get_system_setting("iplock");
					}
					$debug=save_system_setting("iplock",$iplock);
					if($debug !== true)
					{
						$error=true;
					}
				}
				if(isset($_POST['type']))
				{
					$type=preg_replace("/[^0-2]/","",$_POST['type']);
					if($type == "")
					{
						$type=get_system_setting("type");
					}
					$debug=save_system_setting("type",$type);
					if($debug !== true)
					{
						$error=true;
					}
				}
				if(isset($_POST['dayrestrict']))
				{
					$daylock=preg_replace("/[^0-9]/","",$_POST['dayrestrict']);
					if($daylock == "")
					{
						$daylock=get_system_setting("dayrestrict");
					}
					$debug=save_system_setting("dayrestrict",$daylock);
					if($debug !== true)
					{
						$error=true;
					}
				}
				if(isset($_POST['overflow']))
				{
					$overflow=preg_replace("/[^0-9]/","",$_POST['overflow']);
					if($overflow == "")
					{
						$overflow=get_system_setting("limit");
					}
					$debug=save_system_setting("limit",$overflow);
					if($debug !== true)
					{
						$error=true;
					}
				}
				if(isset($_POST['api']))
				{
					if($_POST['api'] == "yes")
					{
						$api="yes";
					}
					else
					{
						$api="no";
					}
					$debug=save_system_setting("interface",$api);
					if($debug !== true)
					{
						$error=true;
					}
				}
				if(isset($_POST['genuid']) && $_POST['genuid'] == "y")
				{
					$sysuid=uniqid("",true);
					$debug=save_system_setting("sysid",$sysuid);
					if($debug !== true)
					{
						$error=true;
					}
				}
				if(isset($_POST['napipass']) && $_POST['napipass'] != "" && isset($_POST['capipass']) && $_POST['napipass'] == $_POST['capipass'])
				{
					$apikey=password_hash($_POST['napipass'],PASSWORD_DEFAULT);
					$debug=save_system_setting("autokey",$apikey);
					if($debug !== true)
					{
						$error=true;
					}
				}
                elseif(isset($_POST['napipass']) && $_POST['napipass'] != "" && isset($_POST['capipass']) && $_POST['capipass'] != "")
                {
					$error=true;
                }
				if(isset($_POST['apipages']) && is_array($_POST['apipages']))
				{
					$apipages=array();
					foreach($_POST['apipages'] as $page)
					{
						$apipages[]=preg_replace("/[^0-6]/","",$page);
					}
					$apipages=implode(",",array_filter(array_unique($apipages),"is_numeric"));
					$debug=save_system_setting("apipages",$apipages);
					if($debug !== true)
					{
						$error=true;
					}
                    $apipages=explode(",",$apipages);
				}
				if(isset($_POST['upgrade']))
				{
					if($_POST['upgrade'] == "yes")
					{
						$upgrade="yes";
					}
					else
					{
						$upgrade="no";
					}
					$debug=save_system_setting("stable",$upgrade);
					if($debug !== true)
					{
						$error=true;
					}
				}
				if(isset($_POST['datetime']))
				{
					$datetime=htmlspecialchars($_POST['datetime']);
					if($datetime == "")
					{
						$datetime=get_system_setting("datetime");
					}
					$debug=save_system_setting("datetime",$datetime);
					if($debug !== true)
					{
						$error=true;
					}
				}
				if(isset($_POST['resetdate']) && $_POST['resetdate'] == "y")
				{
					$resetdate="y";
					reformat_dates($datetime);
				}
				if(isset($_POST['timelimit']))
				{
					$timelimit=preg_replace("/[^0-9]/","",$_POST['timelimit']);
					$debug=save_system_setting("timelimit",$timelimit);
					if($debug !== true)
					{
						$error=true;
					}
				}
				if(isset($_POST['popular']))
				{
					$popular=max(1,preg_replace("/[^0-9]/","",$_POST['popular']));
					$debug=save_system_setting("popular",$popular);
					if($debug !== true)
					{
						$error=true;
					}
				}
				if(isset($_POST['recent']))
				{
					$recent=max(1,preg_replace("/[^0-9]/","",$_POST['recent']));
					$debug=save_system_setting("recent",$recent);
					if($debug !== true)
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"recent\" to \"$recent\"");
						$error=true;
					}
					else
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Changed setting \"recent\" to \"$recent\"");
					}
				}
				if(isset($_POST['rss']))
				{
					if($_POST['rss'] == "yes")
					{
						$rss="yes";
					}
					else
					{
						$rss="no";
					}
					$debug=save_system_setting("rss",$rss);
					if($debug !== true)
					{
						$error=true;
					}
				}
				if(isset($_POST['autoban']))
				{
					if($_POST['autoban'] == "yes")
					{
						$autoban="yes";
					}
					else
					{
						$autoban="no";
					}
					$debug=save_system_setting("autoban",$autoban);
					if($debug !== true)
					{
						$error=true;
					}
				}
				if(isset($_POST['banwords']))
				{
					$banwords=preg_replace("/\s+/","", htmlspecialchars($_POST['banwords']));
					$debug=save_system_setting("banwords",$banwords);
					if($debug !== true)
					{
						$error=true;
					}
				}
				if(isset($_POST['partial']) && $_POST['partial'] == "yes")
				{
					$partial="yes";
				}
				else
				{
					$partial="no";
				}
				if($partial != get_system_setting("partial"))
				{
					$debug=save_system_setting("partial",$partial);
					if($debug !== true)
					{
						$error=true;
					}
				}
				if(isset($_POST['beforeban']))
				{
					$beforeban=preg_replace("/[^0-9]/","",$_POST['beforeban']);
					$debug=save_system_setting("beforeban",$beforeban);
					if($debug !== true)
					{
						$error=true;
					}
				}
				if(isset($_POST['logatt']))
				{
					if($_POST['logatt'] == "yes")
					{
						$logatt="yes";
					}
					else
					{
						$logatt="no";
					}
					$debug=save_system_setting("logatt",$logatt);
					if($debug !== true)
					{
						$error=true;
					}
				}
				if(isset($_POST['baninvpass']))
				{
					if($_POST['baninvpass'] == "yes")
					{
						$baninvpass="yes";
					}
					else
					{
						$baninvpass="no";
					}
					$debug=save_system_setting("baninvpass",$baninvpass);
					if($debug !== true)
					{
						$error=true;
					}
				}
				if(isset($_POST['reqpass']))
				{
					if($_POST['reqpass'] == "yes")
					{
						$reqpass="yes";
					}
					else
					{
						$reqpass="no";
					}
                    if($reqpass == "yes" && isset($_POST['ereqpass']) && isset($_POST['creqpass']) && $_POST['ereqpass'] == $_POST['creqpass'])
                    {
                        $debug=save_request_password($_POST['ereqpass']);
                        if($debug !== true)
                        {
                            $error=true;
                        }
                        else
                        {
                            $debug=save_system_setting("passreq",$reqpass);
                            if($debug !== true)
                            {
                                $error=true;
                            }
                        }
                    }
                    elseif($reqpass == "no")
                    {
                        $debug=save_system_setting("passreq",$reqpass);
                        if($debug !== true)
                        {
                            $error=true;
                        }
                    }
                    else
                    {
                        trigger_error("Cannot set request password as they do not match. Try again.",E_USER_ERROR);
                        $error=true;
                    }
				}
				if(isset($_POST['banfail']))
				{
					$banfail=max(0,preg_replace("/[^0-9]/","",$_POST['banfail']));
					$debug=save_system_setting("banfail",$banfail);
					if($debug !== true)
					{
						$error=true;
					}
				}
				if(isset($_POST['autoopen']))
				{
					if($_POST['autoopen'] == "yes")
					{
						$autoopen="yes";
					}
					else
					{
						$autoopen="no";
					}
					$debug=save_system_setting("autoopen",$autoopen);
					if($debug !== true)
					{
						$error=true;
					}
				}
				if(isset($_POST['mirror']))
				{
					$mirror=filter_var($_POST['mirror'],FILTER_SANITIZE_URL);
					$debug=save_system_setting("mirror",$mirror);
					if($debug !== true)
					{
						$error=true;
					}
				}
				if(isset($_POST['ipundlimit']))
				{
					$ipundlimit=max(0,min(2,preg_replace("/[^0-2]/","",$_POST['ipundlimit'])));
					$debug=save_system_setting("ipundlimit",$ipundlimit);
					if($debug !== true)
					{
						$error=true;
					}
				}
				if($error === true)
				{
					$return="no";
				}
				else
				{
					$return="yes";
				}
				if(!isset($_POST['debug']) || $_POST['debug'] != "y")
				{
					die("<script type=\"text/javascript\">window.location = \"index.php?admsave=$return\"</script>");
				}
			}
		}
		else
		{
			//Visiting page
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Visited administration console");
			if(securitycheck() === false)
			{
				die("<p>You are not an administrator. Please <a href=\"login.php?ref=admin\">sign in</a> or <a href=\"index.php\">cancel</a>.</p>");
			}
			//Check for a "first use" flag file and notify the admin that they should change the poassword
			if(first_use() === true)
			{
				trigger_error("The administrator password is the default! Please consider changing it.",E_USER_WARNING);
			}
			//Check for PHP version compliance and issue a notice if non-compliance is found
			if(determine_compliance() === false)
			{
				trigger_error("Use of non-compliant PHP versions may not be allowed in future releases. Please upgrade to at least PHP 5.5.0 before installing further MRS upgrades.",E_USER_DEPRECATED);
			}
			//If deprecation log has entries, throw a notice
			if(is_dep_log_blank() !== true)
			{
				trigger_error("There are entries in the deprecation log! Please report these if you have not done so!");
			}
		}
	}
  ?>
  <body>
  <?php
	if(verify_request_db() !== true)
	{
		trigger_error("Request database is in an inconsistent state! Please rebuild it using the microwave located under 'requests'.",E_USER_WARNING);
	}
  ?>
  <h1 style="text-align:center; text-decoration:underline;"><?php echo $sysname; ?>MRS-Administration Console</h1>
  <p><a href="#system">System</a><br>
  <a href="#homepage">Homepage</a><br>
  <a href="#search">Song Selection</a><br>
  <a href="#database">Song Database</a><br>
  <a href="#requests">Requests</a><br>
  <a href="#api">API</a><br>
  <a href="#upgrade">Upgrades</a><br>
  <a href="#options">Save Options</a><br>
  <a href="#save">Save/Exit</a></p>
  <hr>
  <form method="post" action="admin.php">
  <input type="hidden" name="s" value="y">
  <a name="system"></a><h3>System</h3>
  System ID: <?php echo $sysuid; ?> | <input type="checkbox" name="genuid" value="y">Generate new system ID<br>
  System name: <input type="text" name="name" size="50" value="<?php echo $name; ?>"><br>
  System message:<br>
  <textarea name="sysmessage" rows="5" cols="50"><?php echo $sysmessage; ?></textarea><br>
  Timezone: <input type="radio" name="zone" value="America/Toronto" <?php if ($timezone == "America/Toronto") { echo ("checked=\"checked\""); } ?>>Eastern | <input type="radio" name="zone" value="America/Winnipeg" <?php if ($timezone == "America/Winnipeg") { echo ("checked=\"checked\""); } ?>>Central | <input type="radio" name="zone" value="America/Denver" <?php if ($timezone == "America/Denver") { echo ("checked=\"checked\""); } ?>>Mountain | <input type="radio" name="zone" value="America/Phoenix" <?php if ($timezone == "America/Phoenix") { echo ("checked=\"checked\""); } ?>>Mountain (no DST) | <input type="radio" name="zone" value="America/Vancouver" <?php if ($timezone == "America/Vancouver") { echo ("checked=\"checked\""); } ?>>Pacific<br>
  Logging: <input type="radio" name="logging" value="yes" <?php if ($logging == "yes") { echo ("checked=\"checked\""); } ?>>Yes | <input type="radio" name="logging" value="no"  <?php if ($logging == "no") { echo ("checked=\"checked\""); } ?>>No<br>
  Use alternate session storage?: <input type="radio" name="uas" value="yes"  <?php if ($uas == "yes") { echo ("checked=\"checked\""); } ?>>Yes | <input type="radio" name="uas" value="no"  <?php if ($uas == "no") { echo ("checked=\"checked\""); } ?>>No<br>
  Alternate session storage location: <input type="text" name="asl" value="<?php if(isset($asl)) { echo $asl; } ?>"><br>
  Limit script execution time to: <input type="text" name="timelimit" maxlength="3" size="3" value="<?php echo $timelimit; ?>" <?php if(!function_exists("set_time_limit")) { echo("disabled=\"disabled\""); } ?>> seconds <?php if(!function_exists("set_time_limit")) { echo("(disabled for security reasons)"); } else { echo("(enter 0 for no limit, but note that doing so is potentially VERY dangerous)"); } ?><br>
  Error reporting level: <input type="radio" name="errlvl" value="0"<?php if(isset($errlvl) && $errlvl == 0) { echo " checked=\"checked\""; } ?>>Only errors | <input type="radio" name="errlvl" value="1"<?php if(isset($errlvl) && $errlvl == 1) { echo " checked=\"checked\""; } ?>>System messages only | <input type="radio" name="errlvl" value="2"<?php if(isset($errlvl) && $errlvl == 2) { echo " checked=\"checked\""; } ?>>All messages<br>
  Write all errors to a log file: <input type="radio" name="logerr" value="yes"  <?php if ($logerr == "yes") { echo ("checked=\"checked\""); } ?>>Yes | <input type="radio" name="logerr" value="no"  <?php if ($logerr == "no") { echo ("checked=\"checked\""); } ?>>No<br>  
  Log system login attempts: <input type="radio" name="logatt" value="yes" <?php if (isset($logatt) && $logatt == "yes") { echo ("checked=\"checked\""); } ?>>Yes | <input type="radio" name="logatt" value="no" <?php if (isset($logatt) && $logatt == "no") { echo ("checked=\"checked\""); } ?>>No<br>  
  <a href="password.php">Change administrator password</a><br>
  <a href="security.php">Change security options</a><br>
  <a href="copyright.php">Edit system copyright information</a><br>
  <a href="viewlog.php">View system logs</a><br>
  <a href="viewatt.php">View login attempts</a><br>
  <a href="viewerr.php">View error logs</a><br>
  <a href="viewdep.php">View deprecation message log</a><?php if(is_dep_log_blank() !== true) { echo " <b>/!\NOT BLANK/!\</b>"; } ?><br>
  <a href="purgesess.php">Clear session storage location</a> (ONLY applicable when using alternative storage locations)<br>
  <hr>
  <a name="homepage"></a><h3>Homepage</h3>
  Automatically refresh after: <input type="text" name="autorefresh" maxlength="4" size="4" value="<?php echo $autorefresh; ?>"> seconds (0 for never)<br>
  Always show existing requests: <input type="radio" name="eroc" value="yes"  <?php if ($eroc == "yes") { echo ("checked=\"checked\""); } ?>>Yes | <input type="radio" name="eroc" value="no"  <?php if ($eroc == "no") { echo ("checked=\"checked\""); } ?>>No<br>
  Display request status publicly: <input type="radio" name="status" value="yes"  <?php if ($status == "yes") { echo ("checked=\"checked\""); } ?>>Yes | <input type="radio" name="status" value="no"  <?php if ($status == "no") { echo ("checked=\"checked\""); } ?>>No<br>
  Display comments and responses publicly: <input type="radio" name="vcomments" value="yes"  <?php if ($vcomments == "yes") { echo ("checked=\"checked\""); } ?>>Yes | <input type="radio" name="vcomments" value="no"  <?php if ($vcomments == "no") { echo ("checked=\"checked\""); } ?>>No<br>
  Hide declined and played requests after: <input type="radio" name="pexpire" value="1" <?php if ($pexpire == "1") { echo ("checked=\"checked\""); } ?>>1 hour | <input type="radio" name="pexpire" value="3" <?php if ($pexpire == "3") { echo ("checked=\"checked\""); } ?>>3 hours | <input type="radio" name="pexpire" value="24" <?php if ($pexpire == "24") { echo ("checked=\"checked\""); } ?>>1 day<br>
  Distinguish open, queued and declined/played requests using: <input type="radio" name="blanking" value="yes" <?php if ($blanking == "yes") { echo ("checked=\"checked\""); } ?>>Opacity changes | <input type="radio" name="blanking" value="no" <?php if ($blanking == "no") { echo ("checked=\"checked\""); } ?>>Separators<br>
  <hr>
  <a name="search"></a><h3>Song Selection</h3>
  Disable song selection: <input type="radio" name="light" value="yes"  <?php if ($light == "yes") { echo ("checked=\"checked\""); } ?>>Yes | <input type="radio" name="light" value="no"  <?php if ($light == "no") { echo ("checked=\"checked\""); } ?>>No<br>
  Allow searching: <input type="radio" name="search" value="yes"  <?php if ($search == "yes") { echo ("checked=\"checked\""); } ?>>Yes | <input type="radio" name="search" value="no"  <?php if ($search == "no") { echo ("checked=\"checked\""); } ?>>No<br>
  Strip the following words from searches (comma-separated): <input type="text" size="50" name="stripwords" value="<?php echo $stripwords; ?>"><br>
  Restrict request selection as follows:<br>
  <textarea name="reqrestrict" rows="10" cols="50" disabled="disabled"><?php echo $reqrestrict; ?></textarea><br>
  List fields: <input type="text" name="songformat" size="50" value="<?php echo $songformat; ?>"> (fields are separated by '|' character, letters only, '*' to denote fields that should be ignored by the system)<br>
  Display fields as: <input type="text" name="songformathr" size="50" value="<?php echo $songformathr; ?>"> (fields are separated by '|' character, letters and spaces only, must be in same order as above less static data fields marked with '*')<br>
  Hide non-requestable songs: <input type="radio" name="hidenr" value="0" <?php if($hidehr == 0) { echo("checked=\"checked\""); } ?>>Never | <input type="radio" name="hidenr" value="1" <?php if($hidehr == 1) { echo("checked=\"checked\""); } ?>>Only when searching | <input type="radio" name="hidenr" value="2" <?php if($hidehr == 2) { echo("checked=\"checked\""); } ?>>Always<br>
  Popular request search uses: <input type="text" name="popular" size="2" value="<?php echo $popular; ?>"> most popular request counts (minimum 1, maximum unlimited but should be fairly small)<br>
  Recently added request search uses: <input type="text" name="recent" size="2" value="<?php echo $recent; ?>"> most recent addition times (minimum 1, maximum unlimited but should be fairly small)<br>
  <a href="listformat.php">Reformat song lists</a><br>
  <hr>
  <a name="database"></a><h3>Song Database</h3>
  Additional song lists: <input type="text" name="extlists" value="<?php echo $extlists; ?>" size="50"><br>
  <input type="checkbox" name="christmas" value="yes" <?php if($christmas == "yes") { echo("checked=\"checked\""); } ?>>Display "christmas" song list option<br>
  <a href="listadd.php">Add songs to main list</a><br>
  <a href="listimport.php">Add songs from file to main list</a><br>
  <a href="listedit.php">Edit main list</a><br>
  <a href="listadd2.php">Import additional song list(s)</a><br>
  <a href="listedit2.php">Edit additional song list(s)</a><br>
  <a href="listdel.php">Delete additional song list(s)</a><br>
  <hr>
  <a name="requests"></a><h3>Requests</h3>
  Enable requests: <input type="radio" name="posting" value="yes" <?php if ($posting == "yes") { echo ("checked=\"checked\""); } ?>>Yes | <input type="radio" name="posting" value="no"  <?php if ($posting == "no") { echo ("checked=\"checked\""); } ?>>No<br>
  Enable automatic opening/closing rules: <input type="radio" name="autoopen" value="yes" <?php if ($autoopen == "yes") { echo ("checked=\"checked\""); } ?>>Yes | <input type="radio" name="autoopen" value="no"  <?php if ($autoopen == "no") { echo ("checked=\"checked\""); } ?>>No<br>
  Enable RSS feed of requests: <input type="radio" name="rss" value="yes" <?php if ($rss == "yes") { echo ("checked=\"checked\""); } ?>>Yes | <input type="radio" name="rss" value="no"  <?php if ($rss == "no") { echo ("checked=\"checked\""); } ?>>No<br>
  Display requested date using the following format: <input type="text" name="datetime" value="<?php echo $datetime; ?>">(see <a href="https://secure.php.net/manual/en/function.date.php">documentation</a> for valid date constants, invalid stuff will invoke undefined behaviour)<br>
  <input type="checkbox" name="resetdate" value="y" <?php if(isset($resetdate) && $resetdate == "y") { echo "checked=\"checked\""; } ?>> Reformat all current posts to match new date string.<br>
  Enable requestee-submitted comments: <input type="radio" name="comments" value="yes" <?php if ($comments == "yes") { echo ("checked=\"checked\""); } ?>>Yes | <input type="radio" name="comments" value="no"  <?php if ($comments == "no") { echo ("checked=\"checked\""); } ?>>No<br>
  Anonymous requesting: <input type="radio" name="anon" value="yes" <?php if ($anon == "yes") { echo ("checked=\"checked\""); } ?>>Yes | <input type="radio" name="anon" value="no"  <?php if ($anon == "no") { echo ("checked=\"checked\""); } ?>>No<br>
  Allow off-list requests: <input type="radio" name="open" value="yes"  <?php if ($open == "yes") { echo ("checked=\"checked\""); } ?>>Yes | <input type="radio" name="open" value="no"  <?php if ($open == "no") { echo ("checked=\"checked\""); } ?>>No<br>
  Allow multiple active requests for users: <input type="radio" name="pdreq" value="no"  <?php if ($pdreq == "no") { echo ("checked=\"checked\""); } ?>>Yes | <input type="radio" name="pdreq" value="yes"  <?php if ($pdreq == "yes") { echo ("checked=\"checked\""); } ?>>No<br><br>
  Allow:&nbsp;
  <select name="unlock">
  <option value="">-Select one-</option>
  <option value="1" <?php if ($unlock == "1") { echo ("selected=\"selected\""); } ?>>1</option>
  <option value="2" <?php if ($unlock == "2") { echo ("selected=\"selected\""); } ?>>2</option>
  <option value="3" <?php if ($unlock == "3") { echo ("selected=\"selected\""); } ?>>3</option>
  <option value="5" <?php if ($unlock == "5") { echo ("selected=\"selected\""); } ?>>5</option>
  <option value="10" <?php if ($unlock == "10") { echo ("selected=\"selected\""); } ?>>10</option>
  <option value="0" <?php if ($unlock == "0") { echo ("selected=\"selected\""); } ?>>Unlimited</option>
  </select> requests per username<br>
  Allow:&nbsp;
  <select name="iplock">
  <option value="">-Select one-</option>
  <option value="1" <?php if ($iplock == "1") { echo ("selected=\"selected\""); } ?>>1</option>
  <option value="2" <?php if ($iplock == "2") { echo ("selected=\"selected\""); } ?>>2</option>
  <option value="3" <?php if ($iplock == "3") { echo ("selected=\"selected\""); } ?>>3</option>
  <option value="5" <?php if ($iplock == "5") { echo ("selected=\"selected\""); } ?>>5</option>
  <option value="10" <?php if ($iplock == "10") { echo ("selected=\"selected\""); } ?>>10</option>
  <option value="0" <?php if ($iplock == "0") { echo ("selected=\"selected\""); } ?>>Unlimited</option>
  </select> requests per IP address<br>
  Apply above restrictions for: <input type="radio" name="type" value="0" <?php if ($type == "0") { echo ("checked=\"checked\""); } ?>>1 hour | <input type="radio" name="type" value="1" <?php if ($type == "1") { echo ("checked=\"checked\""); } ?>>3 hours | <input type="radio" name="type" value="2" <?php if ($type == "2") { echo ("checked=\"checked\""); } ?>>1 day<br>
  Allow:&nbsp;
  <select name="dayrestrict">
  <option value="">-Select one-</option>
  <option value="1" <?php if ($daylock == "1") { echo ("selected=\"selected\""); } ?>>1</option>
  <option value="2" <?php if ($daylock == "2") { echo ("selected=\"selected\""); } ?>>2</option>
  <option value="3" <?php if ($daylock == "3") { echo ("selected=\"selected\""); } ?>>3</option>
  <option value="5" <?php if ($daylock == "5") { echo ("selected=\"selected\""); } ?>>5</option>
  <option value="10" <?php if ($daylock == "10") { echo ("selected=\"selected\""); } ?>>10</option>
  <option value="0" <?php if ($daylock == "0") { echo ("selected=\"selected\""); } ?>>Unlimited</option>
  </select> requests daily<br>
  Apply daily limit to: <input type="radio" name="ipundlimit" value="0" <?php if(isset($ipundlimit) && $ipundlimit == "0") { echo "checked=\"checked\""; } ?>>Usernames | <input type="radio" name="ipundlimit" value="1" <?php if(isset($ipundlimit) && $ipundlimit == "1") { echo "checked=\"checked\""; } ?>>IP addresses | <input type="radio" name="ipundlimit" value="2" <?php if(isset($ipundlimit) && $ipundlimit == "2") { echo "checked=\"checked\""; } ?>>Both<br>
  Put system in overload mode after:&nbsp;
  <select name="overflow">
  <option value="">-Select one-</option>
  <option value="3" <?php if ($overflow == "3") { echo ("selected=\"selected\""); } ?>>3</option>
  <option value="5" <?php if ($overflow == "5") { echo ("selected=\"selected\""); } ?>>5</option>
  <option value="6" <?php if ($overflow == "6") { echo ("selected=\"selected\""); } ?>>6</option>
  <option value="9" <?php if ($overflow == "9") { echo ("selected=\"selected\""); } ?>>9</option>
  <option value="10" <?php if ($overflow == "10") { echo ("selected=\"selected\""); } ?>>10</option>
  <option value="13" <?php if ($overflow == "13") { echo ("selected=\"selected\""); } ?>>13</option>
  <option value="15" <?php if ($overflow == "15") { echo ("selected=\"selected\""); } ?>>15</option>
  <option value="16" <?php if ($overflow == "16") { echo ("selected=\"selected\""); } ?>>16</option>
  <option value="19" <?php if ($overflow == "19") { echo ("selected=\"selected\""); } ?>>19</option>
  <option value="20" <?php if ($overflow == "20") { echo ("selected=\"selected\""); } ?>>20</option>
  <option value="0" <?php if ($overflow == "0") { echo ("selected=\"selected\""); } ?>>Unlimited</option>
  </select> active requests submitted<br><br>
  Require password to submit requests: <input type="radio" name="reqpass" value="yes" <?php if(isset($reqpass) && $reqpass == "yes") { echo "checked=\"checked\""; } ?>>Yes | <input type="radio" name="reqpass" value="no" <?php if(isset($reqpass) && $reqpass == "no") { echo "checked=\"checked\""; } ?>>No<br>
  Password: <input type="password" name="ereqpass"><br>
  Confirm password: <input type="password" name="creqpass"><br>
  <a href="ruledit.php">Edit system rules</a><br>
  <a href="autoopen.php">Edit automatic opening/closing settings</a><br>
  <a href="archive.php">Archive requests</a><br>
  <a href="delall.php">Delete all requests</a><br>
  <a href="microwave.php">Rebuild request database</a> <?php if(verify_request_db() !== true) { echo "<b>RECOMMENDED!</b>"; } ?><br>
  <hr>
  <a name="autoban"></a><h3>Automatic Banning/Username Filtering</h3>
  Allow the MRS to automatically ban IPs after:&nbsp;
  <select name="banfail">
  <option value="">-Select one-</option>
  <option value="0" <?php if ($banfail == "0") { echo ("selected=\"selected\""); } ?>>Don't bother</option>
  <option value="2" <?php if ($banfail == "2") { echo ("selected=\"selected\""); } ?>>2</option>
  <option value="3" <?php if ($banfail == "3") { echo ("selected=\"selected\""); } ?>>3</option>
  <option value="5" <?php if ($banfail == "5") { echo ("selected=\"selected\""); } ?>>5</option>
  <option value="5" <?php if ($banfail == "10") { echo ("selected=\"selected\""); } ?>>10</option>
  </select> failed login attempts.<br>
  Automatically ban IPs that submit invalid passwords: <input type="radio" name="baninvpass" value="yes" <?php if(isset($baninvpass) && $baninvpass == "yes") { echo "checked=\"checked\""; } ?>>Yes | <input type="radio" name="baninvpass" value="no" <?php if(isset($baninvpass) && $baninvpass == "no") { echo "checked=\"checked\""; } ?>>No (NOTE: only has an effect if a request password is set! Also note that the number of attempts before a ban is the same as below.)<br>
  Allow the MRS to automatically ban IPs based on the rules below: <input type="radio" name="autoban" value="yes" <?php if(isset($autoban) && $autoban == "yes") { echo "checked=\"checked\""; } ?>>Yes | <input type="radio" name="autoban" value="no" <?php if(isset($autoban) && $autoban == "no") { echo "checked=\"checked\""; } ?>>No<br>
  List of words to disallow in usernames:<br>
  <textarea name="banwords" rows="10" cols="50"><?php if(isset($banwords)) { echo $banwords; } ?></textarea><br>
  <input type="checkbox" name="partial" value="yes" <?php if(isset($partial) && $partial == "yes") { echo "checked=\"checked\""; } ?>> Do partial word matching (note that this is POTENTIALLY DANGEROUS)<br>
  Allow:&nbsp;
  <select name="beforeban">
  <option value="">-Select one-</option>
  <option value="0" <?php if ($beforeban == "0") { echo ("selected=\"selected\""); } ?>>0</option>
  <option value="1" <?php if ($beforeban == "1") { echo ("selected=\"selected\""); } ?>>1</option>
  <option value="2" <?php if ($beforeban == "2") { echo ("selected=\"selected\""); } ?>>2</option>
  <option value="3" <?php if ($beforeban == "3") { echo ("selected=\"selected\""); } ?>>3</option>
  <option value="5" <?php if ($beforeban == "5") { echo ("selected=\"selected\""); } ?>>5</option>
  </select> attempted posts before automatically banning<br>
  <hr>
  <a name="api"></a><h3>API</h3>
  System API: <input type="radio" name="api" value="yes" <?php if($api == "yes") {echo("checked=\"checked\""); } ?>>Enabled | <input type="radio" name="api" value="no" <?php if($api == "no") {echo("checked=\"checked\""); } ?>>Disabled<br>
  New API Password: <input type="password" name="napipass"><br>
  Confirm new API Password: <input type="password" name="capipass"><br>
  Pages:<br>
  <input type="checkbox" name="apipages[]" value="0" <?php if(in_array(0,$apipages)) { echo("checked=\"checked\""); } ?>>Version information<br>
  <input type="checkbox" name="apipages[]" value="1" <?php if(in_array(1,$apipages)) { echo("checked=\"checked\""); } ?>>Request view<br>
  <input type="checkbox" name="apipages[]" value="2" <?php if(in_array(2,$apipages)) { echo("checked=\"checked\""); } ?>>Request actions (queue, decline, mark as played)<br>
  <input type="checkbox" name="apipages[]" value="3" <?php if(in_array(3,$apipages)) { echo("checked=\"checked\""); } ?>>Open/close system<br>
  <input type="checkbox" name="apipages[]" value="4" <?php if(in_array(4,$apipages)) { echo("checked=\"checked\""); } ?> disabled="disabled">Archive/delete requests<br>
  <input type="checkbox" name="apipages[]" value="5" <?php if(in_array(5,$apipages)) { echo("checked=\"checked\""); } ?> disabled="disabled">Remote administration console<br>
  <input type="checkbox" name="apipages[]" value="6" <?php if(in_array(6,$apipages)) { echo("checked=\"checked\""); } ?> disabled="disabled">Remote API configuration<br>
  <hr>
  <a name="upgrade"></a><h3>System Upgrades</h3>
  Mirror to check:&nbsp;
  <select name="mirror">
  <option value="">-Select one-</option>
  <option value="http://firealarms.mooo.com/mrs/" <?php if($mirror == "http://firealarms.mooo.com/mrs/") { echo("selected=\"selected\""); } ?>>firealarms.mooo.com (Canada)</option>
  </select><br>
  <a href="upgrade/index.php">Check for updates</a><br>
  <hr>
  <a name="options"></a><h3>Save Options</h3>
  <input type="checkbox" name="debug" value="y">Enable verbose mode<br>
  <input type="checkbox" name="default" value="y">Reset to defaults (erases any above changes!)<br>
  <input type="checkbox" name="setdef" value="y" disabled="disabled">Set values as new defaults<br>
  <a name="save"></a><input type="submit" value="Save changes"><input type="button" value="Undo changes" onclick="window.location.href='admin.php'"><input type="button" value="Exit console" onclick="window.location.href='index.php'">
  </form>
  </body>
</html>