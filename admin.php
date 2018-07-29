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
		if(!file_exists("backend/firstuse.txt"))
		{
			return false;
		}
		return true;
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
	//Check for a "first use" flag file and notify the admin that they should change the poassword
	if(first_use() === true)
	{
		trigger_error("The administrator password is the default! Please consider changing it.",E_USER_WARNING);
	}
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
	//Change system timezone
	set_timezone();
	if(is_logging_enabled() === true)
	{
		//Logging is enabled
		if(isset($_POST['s']) && $_POST['s'] == "y" && securitycheck() === true)
		{
			//Begin submission
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Began changing system settings");
			$error=false;
			if(isset($_POST['anon']) && $_POST['anon'] != "")
			{
				//Sanitize!
				if($_POST['anon'] != "yes" && $_POST['anon'] != "no")
				{
					$anon=get_system_default("anon");
				}
				else
				{
					$anon=$_POST['anon'];
				}
				//Write setting
				$debug=save_system_setting("anon",$anon);
				if($debug !== true)
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"anon\" to \"$anon\"");
					$error=true;
				}
				else
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Successfully changed setting \"anon\" to \"$anon\"");
				}
			}
			//Enable/disable system logging
			if(isset($_POST['logging']) && $_POST['logging'] != "")
			{
				//Sanitize!
				if($_POST['logging'] != "yes" && $_POST['logging'] != "no")
				{
					$logging=get_system_default("logging");
				}
				else
				{
					$logging=$_POST['logging'];
				}
				//Write setting
				$debug=save_system_setting("logging",$logging);
				if($debug !== true)
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"logging\" to $logging");
					$error=true;
				}
				else
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Successfully changed setting \"logging\" to $logging");
				}
			}
			//Enable/disable custom requests
			if(isset($_POST['open']) && $_POST['open'] != "")
			{
				//Sanitize!
				if($_POST['open'] != "yes" && $_POST['open'] != "no")
				{
					$open=get_system_default("open");
				}
				else
				{
					$open=$_POST['open'];
				}
				//Write setting
				$debug=save_system_setting("open",$open);
				if($debug !== true)
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"open\" to \"$open\"");
					$error=true;
				}
				else
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Successfully changed setting \"open\" to \"$open\"");
				}
			}
			//Enable/disable the system
			if(isset($_POST['posting']) && $_POST['posting'] != "")
			{
				//Sanitize!
				if($_POST['posting'] != "yes" && $_POST['posting'] != "no")
				{
					$posting=get_system_default("posting");
				}
				else
				{
					$posting=$_POST['posting'];
				}
				//Write setting
				$debug=save_system_setting("posting",$posting);
				if($debug !== true)
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"posting\" to \"$posting\"");
					$error=true;
				}
				else
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Successfully changed setting \"posting\" to \"$posting\"");
				}
			}
			//Change the time of request restriction
			if(isset($_POST['type']) && $_POST['type'] != "")
			{
				//Sanitize!
				$type=intval($_POST['type']);
				if($type < 0)
				{
					$type=get_system_default('type');
				}
				if($type > 2)
				{
					$type=get_system_default('type');
				}
				//Write setting
				$debug=save_system_setting("type",$type);
				if($debug !== true)
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"type\" to \"$type\"");
					$error=true;
				}
				else
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Successfully changed setting \"type\" to \"$type\"");
				}
			}
			//Change username request retriction
			if(isset($_POST['unlock']) && $_POST['unlock'] != "")
			{
				//Sanitize!
				$unlock=preg_replace("/[^0-9]/","",$_POST['unlock']);
				if($unlock == "" || $unlock < 0)
				{
					$unlock=get_system_default('unlock');
				}
				//Write setting
				$debug=save_system_setting("unlock",$unlock);
				if($debug !== true)
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"unlock\" to \"$unlock\"");
					$error=true;
				}
				else
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Successfully changed setting \"unlock\" to \"$unlock\"");
				}
			}
			//Change IP request restriction
			if(isset($_POST['iplock']) && $_POST['iplock'] != "")
			{
				//Sanitize!
				$iplock=preg_replace("/[^0-9]/","",$_POST['iplock']);
				if($iplock == "" || $iplock < 0)
				{
					$iplock=get_system_default('iplock');
				}
				//Write setting
				$debug=save_system_setting("iplock",$iplock);
				if($debug !== true)
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"iplock\" to \"iplock\"");
					$error=true;
				}
				else
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Successfully changed setting \"iplock\" to \"$iplock\"");
				}
			}
			//Change overall request restriction per day
			if(isset($_POST['dayrestrict']) && $_POST['dayrestrict'] != "")
			{
				//Sanitize!
				$daylock=preg_replace("/[^0-9]/","",$_POST['dayrestrict']);
				if($daylock == "" || $daylock < 0)
				{
					$daylock=get_system_default('dayrestrict');
				}
				//Write setting
				$debug=save_system_setting("dayrestrict",$daylock);
				if($debug !== true)
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"dayrestrict\" to \"$daylock\"");
					$error=true;
				}
				else
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Successfully changed setting \"dayrestrict\" to \"$daylock\"");
				}
			}
			//Enable/disable request status viewing by everyone
			if(isset($_POST['status']) && $_POST['status'] != "")
			{
				//Sanitize!
				if($_POST['status'] != "yes" && $_POST['status'] != "no")
				{
					$status=get_system_default('status');
				}
				else
				{
					$status=$_POST['status'];
				}
				//Write setting
				$debug=save_system_setting("status",$status);
				if($debug !== true)
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"status\" to \"$status\"");
					$error=true;
				}
				else
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Successfully changed setting \"status\" to \"$status\"");
				}
			}
			//Enable/disable viewing requests and songs while system closed
			if(isset($_POST['eroc']) && $_POST['eroc'] != "")
			{
				//Sanitize!
				if($_POST['eroc'] != "yes" && $_POST['eroc'] != "no")
				{
					$eroc=get_system_default('eroc');
				}
				else
				{
					$eroc=$_POST['eroc'];
				}
				//Write setting
				$debug=save_system_setting("eroc",$eroc);
				if($debug !== true)
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"eroc\" to \"$eroc\"");
					$error=true;
				}
				else
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Successfully changed setting \"eroc\" to \"$eroc\"");
				}
			}
			//Enable/disable making requests while a request by the user is already pending
			if(isset($_POST['pdreq']) && $_POST['pdreq'] != "")
			{
				//Sanitize!
				if($_POST['pdreq'] != "yes" && $_POST['pdreq'] != "no")
				{
					$pdreq=get_system_default('pdreq');
				}
				else
				{
					$pdreq=$_POST['pdreq'];
				}
				//Write setting
				$debug=save_system_setting("pdreq",$pdreq);
				if($debug !== true)
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"pdreq\" to \"$pdreq\"");
					$error=true;
				}
				else
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Successfully changed setting \"pdreq\" to \"$pdreq\"");
				}
			}
			//Enable/disable searching
			if(isset($_POST['search']) && $_POST['search'] != "")
			{
				//Sanitize!
				if($_POST['search'] != "yes" && $_POST['search'] != "no")
				{
					$search=get_system_default('searching');
				}
				else
				{
					$search=$_POST['search'];
				}
				//Write setting
				$debug=save_system_setting("searching",$search);
				if($debug !== true)
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"searching\" to \"$search\"");
					$error=true;
				}
				else
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Successfully changed setting \"searching\" to \"$search\"");
				}
			}
			//Change list of words to strip
			if(isset($_POST['stripwords']))
			{
				//Sanitize!
				$stripwords=preg_replace("/[^A-Za-z0-9 ]/","",$_POST['stripwords']);
				//Write setting
				$debug=save_system_setting("stripwords",$stripwords);
				if($debug !== true)
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"stripwords\" to \"$stripwords\"");
					$error=true;
				}
				else
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Successfully changed setting \"stripwords\" to \"$stripwords\"");
				}
			}
			//Change timezone
			if(isset($_POST['zone']) && $_POST['zone'] != "")
			{
				//Sanitize!
				if(!in_array($_POST['zone'],timezone_identifiers_list()))
				{
					$timezone=get_system_default('timezone');
				}
				else
				{
					$timezone=$_POST['zone'];
				}
				//Write setting
				$debug=save_system_setting("timezone",$timezone);
				if($debug !== true)
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"timezone\" to \"$timezone\"");
					$error=true;
				}
				else
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Successfully changed setting \"timezone\" to \"$timezone\"");
				}
			}
			//Change system name
			if(isset($_POST['name']))
			{
				//Sanitize!
				$name=preg_replace("/[^A-Za-z0-9 ]/", "", $_POST['name']);
				//Write setting
				$debug=save_system_setting("name",$name);
				if($debug !== true)
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"name\" to \"$name\"");
					$error=true;
				}
				else
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Successfully changed setting \"name\" to \"$name\"");
				}
			}
			//Change system overflow setting
			if(isset($_POST['overflow']) && $_POST['overflow'] != "")
			{
				//Sanitize!
				$overflow=preg_replace("/[^0-9]/","",$_POST['overflow']);
				if($overflow == "" || $overflow < 0)
				{
					$overflow=get_system_default('limit');
				}
				//Write setting
				$debug=save_system_setting("limit",$overflow);
				if($debug !== true)
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"overflow\" to \"$overflow\"");
					$error=true;
				}
				else
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Successfully changed setting \"overflow\" to \"$overflow\"");
				}
			}
			//Change system upgrade setting
			if(isset($_POST['upgrade']) && $_POST['upgrade'] != "")
			{
				//Sanitize!
				if($_POST['upgrade'] != "yes" && $_POST['upgrade'] != "no")
				{
					$upgrade=get_system_default('stable');
				}
				else
				{
					$upgrade=$_POST['upgrade'];
				}
				//Write setting
				$debug=save_system_setting("stable",$upgrade);
				if($debug !== true)
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"stable\" to \"$upgrade\"");
					$error=true;
				}
				else
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Successfully changed setting \"stable\" to \"$upgrade\"");
				}
			}
			//Change light mode setting
			if(isset($_POST['light']) && $_POST['light'] != "")
			{
				//Sanitize!
				if($_POST['light'] != "yes" && $_POST['light'] != "no")
				{
					$light=get_system_default('light');
				}
				else
				{
					$light=$_POST['light'];
				}
				//Write setting
				$debug=save_system_setting("light",$light);
				if($debug !== true)
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"light\" to \"$light\"");
					$error=true;
				}
				else
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Successfully changed setting \"light\" to \"$light\"");
				}
			}
			//Change post expiry setting
			if(isset($_POST['pexpire']) && $_POST['pexpire'] != "")
			{
				//Sanitize!
				$pexpire=preg_replace("/[^0-9]/","",$_POST['pexpire']);
				if($pexpire == "" || $pexpire < 0)
				{
					$pexpire=get_system_default('postexpiry');
				}
				//Compute post expiry in seconds
				$pexpire=$pexpire*60*60;
				//Write setting
				$debug=save_system_setting("postexpiry",$pexpire);
				if($debug !== true)
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"postexpiry\" to \"$pexpire\"");
					$error=true;
				}
				else
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Successfully changed setting \"postexpiry\" to \"$pexpire\"");
				}
			}
			//Change auto refresh setting
			if(isset($_POST['autorefresh']) && $_POST['autorefresh'] != "")
			{
				//Sanitize!
				$autorefresh=preg_replace("/[^0-9]/","",$_POST['autorefresh']);
				if($autorefresh == "" || $autorefresh < 0)
				{
					$autorefresh=get_system_default('autorefresh');
				}
				//Write setting
				$debug=save_system_setting("autorefresh",$autorefresh);
				if($debug !== true)
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"autorefresh\" to \"$autorefresh\"");
					$error=true;
				}
				else
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Successfully changed setting \"autorefresh\" to \"$autorefresh\"");
				}
			}
			//Change system message
			if(isset($_POST['sysmessage']))
			{
				//Sanitize!
				$sysmessage=filter_var($_POST['sysmessage'],FILTER_SANITIZE_STRING,FILTER_FLAG_STRIP_BACKTICK | FILTER_FLAG_ENCODE_LOW | FILTER_FLAG_ENCODE_HIGH | FILTER_FLAG_ENCODE_AMP);
				//Write setting
				$debug=save_system_setting("sysmessage",$sysmessage);
				if($debug !== true)
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"sysmessage\" to \"$sysmessage\"");
					$error=true;
				}
				else
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Successfully changed setting \"sysmessage\" to \"$sysmessage\"");
				}
			}
			//Change session storage information
			if(isset($_POST['uas']) && $_POST['uas'] != "" && isset($_POST['asl']))
			{
				//Sanitize!
				if($_POST['uas'] != "yes" && $_POST['uas'] != "no")
				{
					$uas=get_system_default('altsesstore');
				}
				else
				{
					$uas=$_POST['uas'];
				}
				$asl=preg_replace("/[^A-Za-z0-9]/","",$_POST['asl']);
				if($uas == "yes" && $asl != "")
				{
					//Set a new storage directory
					if(!file_exists($asl))
					{
						//Make the non-existing directory
						@mkdir($asl);
					}
					if(is_dir($asl) && is_writable($asl) && is_readable($asl))
					{
						//Write alt session store flag
						$debug=save_system_setting("altsesstore",$uas);
						if($debug !== true)
						{
							write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"altsesstore\" to \"$uas\", not proceeding with \"asl\" setting");
							$error=true;
						}
						else
						{
							write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Successfully changed setting \"altsesstore\" to \"$uas\"");
							//Write alt session store location
							$debug=save_system_setting("altsesstorepath",$asl);
							if($debug !== true)
							{
								//Failed, disable alt session store as precaution
								write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"altsesstorepath\" to \"$asl\", disabling alternate session store as safety precaution");
								$error=true;
								save_system_setting("altsesstore","no");
							}
							else
							{
								write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Successfully changed setting \"altsesstorepath\" to \"$asl\"");
							}
						}
					}
					else
					{
						//Directory does not follow specific rules
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Prospective storage path \"$asl\" is either not a directory, is not readable, or is not writable. Not changing alternat session storage details.");
						$error=true;
					}
				}
				elseif($uas == "no")
				{
					//Change alt session storage flag and erase the saved storage path
					$debug=save_system_setting("altsesstore",$uas);
					if($debug !== true)
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"altsesstore\" to \"$uas\", not proceeding with \"asl\" setting");
						$error=true;
					}
					else
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Successfully changed setting \"altsesstore\" to \"$uas\"");
						//Write alt session store location
						$debug=save_system_setting("altsesstorepath","");
						if($debug !== true)
						{
							//Failed, but not the end of the world
							write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to truncate setting \"altsesstorepath\"");
							$error=true;
						}
						else
						{
							write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Successfully truncated setting \"altsesstorepath\"");
						}
					}
				}
				else
				{
					//Some other combination that doesn't work and so therefore the setting won't be changed
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Submitted data for alternate session storage was not in a state consistent with what the MRS requires, skipping settings changes");
					$error=true;
				}
			}
			//Change whether users can comment
			if(isset($_POST['comments']) && $_POST['comments'] != "")
			{
				//Sanitize!
				if($_POST['comments'] != "yes" && $_POST['comments'] != "no")
				{
					$comments=get_system_default('comments');
				}
				else
				{
					$comments=$_POST['comments'];
				}
				//Write setting
				$debug=save_system_setting("comments",$comments);
				if($debug !== true)
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"comments\" to \"$comments\"");
					$error=true;
				}
				else
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Successfully changed setting \"comments\" to \"$comments\"");
				}
			}
			//Change whether users can view comment
			if(isset($_POST['vcomments']) && $_POST['vcomments'] != "")
			{
				//Sanitize!
				if($_POST['vcomments'] != "yes" && $_POST['vcomments'] != "no")
				{
					$vcomments=get_system_default('vcomments');
				}
				else
				{
					$vcomments=$_POST['vcomments'];
				}
				//Write setting
				$debug=save_system_setting("viewcomments",$vcomments);
				if($debug !== true)
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"viewcomments\" to \"$vcomments\"");
					$error=true;
				}
				else
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Successfully changed setting \"viewcomments\" to \"$vcomments\"");
				}
			}
			//Change error reporting level
			if(isset($_POST['errlvl']) && $_POST['errlvl'] != "")
			{
				$errlvl=preg_replace("/[^0-2]/","",$_POST['errlvl']);
				if($errlvl == "")
				{
					$errlvl=get_system_default('errlvl');
				}
				//Write setting
				$debug=save_system_setting("errlvl",$errlvl);
				if($debug !== true)
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"errlvl\" to \"$errlvl\"");
					$error=true;
				}
				else
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Successfully changed setting \"errlvl\" to \"$errlvl\"");
				}
			}
			//Change whether or not posts are blanked
			if(isset($_POST['blanking']) && $_POST['blanking'] != "")
			{
				if($_POST['blanking'] != "yes" && $_POST['blanking'] != "no")
				{
					$blanking=get_system_default('blanking');
				}
				else
				{
					$blanking=$_POST['blanking'];
				}
				//Write setting
				$debug=save_system_setting("blanking",$blanking);
				if($debug !== true)
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"blanking\" to \"$blanking\"");
					$error=true;
				}
				else
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Successfully changed setting \"blanking\" to \"$blanking\"");
				}
			}
			//Change whether or not posts are blanked
			if(isset($_POST['logerr']) && $_POST['logerr'] != "")
			{
				if($_POST['logerr'] != "yes" && $_POST['logerr'] != "no")
				{
					$logerr=get_system_default('logerr');
				}
				else
				{
					$logerr=$_POST['logerr'];
				}
				//Write setting
				$debug=save_system_setting("logerr",$logerr);
				if($debug !== true)
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to change setting \"logerr\" to \"$logerr\"");
					$error=true;
				}
				else
				{
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Successfully changed setting \"logerr\" to \"$logerr\"");
				}
			}
			//Return to the home page
			if($error === true)
			{
				$admsave="no";
			}
			else
			{
				$admsave="yes";
			}
			if(isset($_POST['debug']) && $_POST['debug'] == "y")
			{
				echo("You submitted this page in debugging mode. Check above for any errors. Click <a href=\"index.php?admsave=$admsave\">here</a> to go home.");
			}
			else
			{
				echo("<script type=\"text/javascript\">window.location = \"index.php?admsave=$admsave\"</script>");
			}
		}
		elseif(securitycheck() === true && !isset($_GET['default']))
		{
			//Get all system settings
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Visited administration page");
			$anon=get_system_setting("anon");
			$logging=get_system_setting("logging");
			$open=get_system_setting("open");
			$posting=get_system_setting("posting");
			$type=get_system_setting("type");
			$unlock=get_system_setting("unlock");
			$iplock=get_system_setting("iplock");
			$daylock=get_system_setting("dayrestrict");
			$eroc=get_system_setting("eroc");
			$pdreq=get_system_setting("pdreq");
			$search=get_system_setting("searching");
			$stripwords=stripcslashes(get_system_setting("stripwords"));
			$timezone=get_system_setting("timezone");
			$name=stripcslashes(get_system_setting("name"));
			$overflow=get_system_setting("limit");
			$upgrade=get_system_setting("stable");
			$systemid=get_system_setting("sysid");
			$light=get_system_setting("light");
			$pexpire=get_system_setting("postexpiry")/60/60;
			$uas=get_system_setting("altsesstore");
			$asl=get_system_setting("altsesstorepath");
			$autorefresh=get_system_setting("autorefresh");
			$sysmessage=stripcslashes(get_system_setting("sysmessage"));
			$status=get_system_setting("status");
			$comments=get_system_setting("comments");
			$vcomments=get_system_setting("viewcomments");
			$songformat=get_system_setting("songformat");
			$songformathr=get_system_setting("songformathr");
			$errlvl=get_system_setting('errlvl');
			$blanking=get_system_setting('blanking');
			$logerr=get_system_setting('logerr');
		}
		elseif(securitycheck() === true && isset($_GET['default']))
		{
			//Get all system defaults
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Reset all settings to default");
			
			$anon=get_system_default('anon');
			$logging=get_system_default('logging');
			$open=get_system_default('open');
			$posting=get_system_default('posting');
			$type=get_system_default('type');
			$unlock=get_system_default('unlock');
			$iplock=get_system_default('iplock');
			$daylock=get_system_default('dayrestrict');
			$eroc=get_system_default('eroc');
			$pdreq=get_system_default('pdreq');
			$search=get_system_default('searching');
			$stripwords=get_system_default('stripwords');
			$timezone=get_system_default('timezone');
			$name=get_system_default('name');
			$overflow=get_system_default('limit');
			$upgrade=get_system_default('stable');
			$systemid=get_system_setting("sysid");
			$light=get_system_default('light');
			$pexpire=get_system_default('postexpiry')/60/60;
			$uas=get_system_default('altsesstore');
			$asl=get_system_default('altsesstorepath');
			$autorefresh=get_system_default('autorefresh');
			$sysmessage=get_system_default('sysmessage');
			$status=get_system_default('status');
			$comments=get_system_default('comments');
			$vcomments=get_system_default('viewcomments');
			$songformat=get_system_default('songformat');
			$songformathr=get_system_default('songformathr');
			$errlvl=get_system_default('errlvl');
			$blanking=get_system_default('blanking');
			$logerr=get_system_default('logerr');
		}
		else
		{
			//User is not permitted to view this page
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Visited administration page");
			die("You are not an administrator. <a href=\"login.php?ref=admin\">Sign in</a> or <a href=\"index.php\">Cancel</a>.");
		}
	}
	else
	{
		//Logging is disabled
		if(isset($_POST['s']) && $_POST['s'] == "y" && securitycheck() === true)
		{
			//Begin submission
			$error=false;
			if(isset($_POST['anon']) && $_POST['anon'] != "")
			{
				//Sanitize!
				if($_POST['anon'] != "yes" && $_POST['anon'] != "no")
				{
					$anon=get_system_default("anon");
				}
				else
				{
					$anon=$_POST['anon'];
				}
				//Write setting
				$debug=save_system_setting("anon",$anon);
				if($debug !== true)
				{
					$error=true;
				}
			}
			//Enable/disable system logging
			if(isset($_POST['logging']) && $_POST['logging'] != "")
			{
				//Sanitize!
				if($_POST['logging'] != "yes" && $_POST['logging'] != "no")
				{
					$logging=get_system_default("logging");
				}
				else
				{
					$logging=$_POST['logging'];
				}
				//Write setting
				$debug=save_system_setting("logging",$logging);
				if($debug !== true)
				{
					$error=true;
				}
			}
			//Enable/disable custom requests
			if(isset($_POST['open']) && $_POST['open'] != "")
			{
				//Sanitize!
				if($_POST['open'] != "yes" && $_POST['open'] != "no")
				{
					$open=get_system_default("open");
				}
				else
				{
					$open=$_POST['open'];
				}
				//Write setting
				$debug=save_system_setting("open",$open);
				if($debug !== true)
				{
					$error=true;
				}
			}
			//Enable/disable the system
			if(isset($_POST['posting']) && $_POST['posting'] != "")
			{
				//Sanitize!
				if($_POST['posting'] != "yes" && $_POST['posting'] != "no")
				{
					$posting=get_system_default("posting");
				}
				else
				{
					$posting=$_POST['posting'];
				}
				//Write setting
				$debug=save_system_setting("posting",$posting);
				if($debug !== true)
				{
					$error=true;
				}
			}
			//Change the time of request restriction
			if(isset($_POST['type']) && $_POST['type'] != "")
			{
				//Sanitize!
				$type=intval($_POST['type']);
				if($type < 0)
				{
					$type=get_system_default('type');
				}
				if($type > 2)
				{
					$type=get_system_default('type');
				}
				//Write setting
				$debug=save_system_setting("type",$type);
				if($debug !== true)
				{
					$error=true;
				}
			}
			//Change username request retriction
			if(isset($_POST['unlock']) && $_POST['unlock'] != "")
			{
				//Sanitize!
				$unlock=preg_replace("/[^0-9]/","",$_POST['unlock']);
				if($unlock == "" || $unlock < 0)
				{
					$unlock=get_system_default('unlock');
				}
				//Write setting
				$debug=save_system_setting("unlock",$unlock);
				if($debug !== true)
				{
					$error=true;
				}
			}
			//Change IP request restriction
			if(isset($_POST['iplock']) && $_POST['iplock'] != "")
			{
				//Sanitize!
				$iplock=preg_replace("/[^0-9]/","",$_POST['iplock']);
				if($iplock == "" || $iplock < 0)
				{
					$iplock=get_system_default('iplock');
				}
				//Write setting
				$debug=save_system_setting("iplock",$iplock);
				if($debug !== true)
				{
					$error=true;
				}
			}
			//Change overall request restriction per day
			if(isset($_POST['dayrestrict']) && $_POST['dayrestrict'] != "")
			{
				//Sanitize!
				$daylock=preg_replace("/[^0-9]/","",$_POST['dayrestrict']);
				if($daylock == "" || $daylock < 0)
				{
					$daylock=get_system_default('dayrestrict');
				}
				//Write setting
				$debug=save_system_setting("dayrestrict",$daylock);
				if($debug !== true)
				{
					$error=true;
				}
			}
			//Enable/disable request status viewing by everyone
			if(isset($_POST['status']) && $_POST['status'] != "")
			{
				//Sanitize!
				if($_POST['status'] != "yes" && $_POST['status'] != "no")
				{
					$status=get_system_default('status');
				}
				else
				{
					$status=$_POST['status'];
				}
				//Write setting
				$debug=save_system_setting("status",$status);
				if($debug !== true)
				{
					$error=true;
				}
			}
			//Enable/disable viewing requests and songs while system closed
			if(isset($_POST['eroc']) && $_POST['eroc'] != "")
			{
				//Sanitize!
				if($_POST['eroc'] != "yes" && $_POST['eroc'] != "no")
				{
					$eroc=get_system_default('eroc');
				}
				else
				{
					$eroc=$_POST['eroc'];
				}
				//Write setting
				$debug=save_system_setting("eroc",$eroc);
				if($debug !== true)
				{
					$error=true;
				}
			}
			//Enable/disable making requests while a request by the user is already pending
			if(isset($_POST['pdreq']) && $_POST['pdreq'] != "")
			{
				//Sanitize!
				if($_POST['pdreq'] != "yes" && $_POST['pdreq'] != "no")
				{
					$pdreq=get_system_default('pdreq');
				}
				else
				{
					$pdreq=$_POST['pdreq'];
				}
				//Write setting
				$debug=save_system_setting("pdreq",$pdreq);
				if($debug !== true)
				{
					$error=true;
				}
			}
			//Enable/disable searching
			if(isset($_POST['search']) && $_POST['search'] != "")
			{
				//Sanitize!
				if($_POST['search'] != "yes" && $_POST['search'] != "no")
				{
					$search=get_system_default('searching');
				}
				else
				{
					$search=$_POST['search'];
				}
				//Write setting
				$debug=save_system_setting("searching",$search);
				if($debug !== true)
				{
					$error=true;
				}
			}
			//Change list of words to strip
			if(isset($_POST['stripwords']))
			{
				//Sanitize!
				$stripwords=preg_replace("/[^A-Za-z0-9 ]/","",$_POST['stripwords']);
				//Write setting
				$debug=save_system_setting("stripwords",$stripwords);
				if($debug !== true)
				{
					$error=true;
				}
			}
			//Change timezone
			if(isset($_POST['zone']) && $_POST['zone'] != "")
			{
				//Sanitize!
				if(!in_array($_POST['zone'],timezone_identifiers_list()))
				{
					$timezone=get_system_default('timezone');
				}
				else
				{
					$timezone=$_POST['zone'];
				}
				//Write setting
				$debug=save_system_setting("timezone",$timezone);
				if($debug !== true)
				{
					$error=true;
				}
			}
			//Change system name
			if(isset($_POST['name']))
			{
				//Sanitize!
				$name=preg_replace("/[^A-Za-z0-9 ]/", "", $_POST['name']);
				//Write setting
				$debug=save_system_setting("name",$name);
				if($debug !== true)
				{
					$error=true;
				}
			}
			//Change system overflow setting
			if(isset($_POST['overflow']) && $_POST['overflow'] != "")
			{
				//Sanitize!
				$overflow=preg_replace("/[^0-9]/","",$_POST['overflow']);
				if($overflow == "" || $overflow < 0)
				{
					$overflow=get_system_default('limit');
				}
				//Write setting
				$debug=save_system_setting("limit",$overflow);
				if($debug !== true)
				{
					$error=true;
				}
			}
			//Change system upgrade setting
			if(isset($_POST['upgrade']) && $_POST['upgrade'] != "")
			{
				//Sanitize!
				if($_POST['upgrade'] != "yes" && $_POST['upgrade'] != "no")
				{
					$upgrade=get_system_default('stable');
				}
				else
				{
					$upgrade=$_POST['upgrade'];
				}
				//Write setting
				$debug=save_system_setting("stable",$upgrade);
				if($debug !== true)
				{
					$error=true;
				}
			}
			//Change light mode setting
			if(isset($_POST['light']) && $_POST['light'] != "")
			{
				//Sanitize!
				if($_POST['light'] != "yes" && $_POST['light'] != "no")
				{
					$light=get_system_default('light');
				}
				else
				{
					$light=$_POST['light'];
				}
				//Write setting
				$debug=save_system_setting("light",$light);
				if($debug !== true)
				{
					$error=true;
				}
			}
			//Change post expiry setting
			if(isset($_POST['pexpire']) && $_POST['pexpire'] != "")
			{
				//Sanitize!
				$pexpire=preg_replace("/[^0-9]/","",$_POST['pexpire']);
				if($pexpire == "" || $pexpire < 0)
				{
					$pexpire=get_system_default('postexpiry');
				}
				//Compute post expiry in seconds
				$pexpire=$pexpire*60*60;
				//Write setting
				$debug=save_system_setting("postexpiry",$pexpire);
				if($debug !== true)
				{
					$error=true;
				}
			}
			//Change auto refresh setting
			if(isset($_POST['autorefresh']) && $_POST['autorefresh'] != "")
			{
				//Sanitize!
				$autorefresh=preg_replace("/[^0-9]/","",$_POST['autorefresh']);
				if($autorefresh == "" || $autorefresh < 0)
				{
					$autorefresh=get_system_default('autorefresh');
				}
				//Write setting
				$debug=save_system_setting("autorefresh",$autorefresh);
				if($debug !== true)
				{
					$error=true;
				}
			}
			//Change system message
			if(isset($_POST['sysmessage']) && $_POST['sysmessage'] != "")
			{
				//Sanitize!
				$sysmessage=filter_var($_POST['sysmessage'],FILTER_SANITIZE_STRING,FILTER_FLAG_STRIP_BACKTICK | FILTER_FLAG_ENCODE_LOW | FILTER_FLAG_ENCODE_HIGH | FILTER_FLAG_ENCODE_AMP);
				//Write setting
				$debug=save_system_setting("sysmessage",$sysmessage);
				if($debug !== true)
				{
					$error=true;
				}
			}
			//Change session storage information
			if(isset($_POST['uas']) && $_POST['uas'] != "" && isset($_POST['asl']))
			{
				//Sanitize!
				if($_POST['uas'] != "yes" && $_POST['uas'] != "no")
				{
					$uas=get_system_default('altsesstore');
				}
				else
				{
					$uas=$_POST['uas'];
				}
				$asl=preg_replace("/[^A-Za-z0-9]/","",$_POST['asl']);
				if($uas == "yes" && $asl != "")
				{
					//Set a new storage directory
					if(!file_exists($asl))
					{
						//Make the non-existing directory
						@mkdir($asl);
					}
					if(is_dir($asl) && is_writable($asl) && is_readable($asl))
					{
						//Write alt session store flag
						$debug=save_system_setting("altsesstore",$uas);
						if($debug !== true)
						{
							$error=true;
						}
						else
						{
							//Write alt session store location
							$debug=save_system_setting("altsesstorepath",$asl);
							if($debug !== true)
							{
								//Failed, disable alt session store as precaution
								$error=true;
								save_system_setting("altsesstore","no");
							}
						}
					}
					else
					{
						//Directory does not follow specific rules
						$error=true;
					}
				}
				elseif($uas == "no")
				{
					//Change alt session storage flag and erase the saved storage path
					$debug=save_system_setting("altsesstore",$uas);
					if($debug !== true)
					{
						$error=true;
					}
					else
					{
						//Write alt session store location
						$debug=save_system_setting("altsesstorepath","");
						if($debug !== true)
						{
							//Failed, but not the end of the world
							$error=true;
						}
					}
				}
				else
				{
					//Some other combination that doesn't work and so therefore the setting won't be changed
					$error=true;
				}
			}
			//Change whether users can comment
			if(isset($_POST['comments']) && $_POST['comments'] != "")
			{
				//Sanitize!
				if($_POST['comments'] != "yes" && $_POST['comments'] != "no")
				{
					$comments=get_system_default('comments');
				}
				else
				{
					$comments=$_POST['comments'];
				}
				//Write setting
				$debug=save_system_setting("comments",$comments);
				if($debug !== true)
				{
					$error=true;
				}
			}
			//Change whether users can view comment
			if(isset($_POST['vcomments']) && $_POST['vcomments'] != "")
			{
				//Sanitize!
				if($_POST['vcomments'] != "yes" && $_POST['vcomments'] != "no")
				{
					$vcomments=get_system_default('vcomments');
				}
				else
				{
					$vcomments=$_POST['vcomments'];
				}
				//Write setting
				$debug=save_system_setting("viewcomments",$vcomments);
				if($debug !== true)
				{
					$error=true;
				}
			}
			//Change error reporting level
			if(isset($_POST['errlvl']) && $_POST['errlvl'] != "")
			{
				$errlvl=preg_replace("/[^0-2]/","",$_POST['errlvl']);
				if($errlvl == "")
				{
					$errlvl=get_system_default('errlvl');
				}
				//Write setting
				$debug=save_system_setting("errlvl",$errlvl);
				if($debug !== true)
				{
					$error=true;
				}
			}
			//Change whether or not posts are blanked
			if(isset($_POST['blanking']) && $_POST['blanking'] != "")
			{
				if($_POST['blanking'] != "yes" && $_POST['blanking'] != "no")
				{
					$blanking=get_system_default('blanking');
				}
				else
				{
					$blanking=$_POST['blanking'];
				}
				//Write setting
				$debug=save_system_setting("blanking",$blanking);
				if($debug !== true)
				{
					$error=true;
				}
			}
			//Change whether or not posts are blanked
			if(isset($_POST['logerr']) && $_POST['logerr'] != "")
			{
				if($_POST['logerr'] != "yes" && $_POST['logerr'] != "no")
				{
					$logerr=get_system_default('logerr');
				}
				else
				{
					$logerr=$_POST['logerr'];
				}
				//Write setting
				$debug=save_system_setting("logerr",$logerr);
				if($debug !== true)
				{
					$error=true;
				}
			}
			//Return to the home page
			if($error === true)
			{
				$admsave="no";
			}
			else
			{
				$admsave="yes";
			}
			if(isset($_POST['debug']) && $_POST['debug'] == "y")
			{
				echo ("You submitted this page in debugging mode. Check above for any errors. Click <a href=\"index.php?admsave=$admsave\">here</a> to go home.");
			}
			else
			{
				echo("<script type=\"text/javascript\">window.location = \"index.php?admsave=$admsave\"</script>");
			}
		}
		elseif(securitycheck() === true && !isset($_GET['default']))
		{
			//Get all system settings
			$anon=get_system_setting("anon");
			$logging=get_system_setting("logging");
			$open=get_system_setting("open");
			$posting=get_system_setting("posting");
			$type=get_system_setting("type");
			$unlock=get_system_setting("unlock");
			$iplock=get_system_setting("iplock");
			$daylock=get_system_setting("dayrestrict");
			$eroc=get_system_setting("eroc");
			$pdreq=get_system_setting("pdreq");
			$search=get_system_setting("searching");
			$stripwords=stripcslashes(get_system_setting("stripwords"));
			$timezone=get_system_setting("timezone");
			$name=stripcslashes(get_system_setting("name"));
			$overflow=get_system_setting("limit");
			$upgrade=get_system_setting("stable");
			$systemid=get_system_setting("sysid");
			$light=get_system_setting("light");
			$pexpire=get_system_setting("postexpiry")/60/60;
			$uas=get_system_setting("altsesstore");
			$asl=get_system_setting("altsesstorepath");
			$autorefresh=get_system_setting("autorefresh");
			$sysmessage=stripcslashes(get_system_setting("sysmessage"));
			$status=get_system_setting("status");
			$comments=get_system_setting("comments");
			$vcomments=get_system_setting("viewcomments");
			$songformat=get_system_setting("songformat");
			$songformathr=get_system_setting("songformathr");
			$errlvl=get_system_setting('errlvl');
			$blanking=get_system_setting('blanking');
			$logerr=get_system_setting('logerr');
		}
		elseif(securitycheck() === true && isset($_GET['default']))
		{
			//Get all system defaults
			$anon=get_system_default('anon');
			$logging=get_system_default('logging');
			$open=get_system_default('open');
			$posting=get_system_default('posting');
			$type=get_system_default('type');
			$unlock=get_system_default('unlock');
			$iplock=get_system_default('iplock');
			$daylock=get_system_default('dayrestrict');
			$eroc=get_system_default('eroc');
			$pdreq=get_system_default('pdreq');
			$search=get_system_default('searching');
			$stripwords=get_system_default('stripwords');
			$timezone=get_system_default('timezone');
			$name=get_system_default('name');
			$overflow=get_system_default('limit');
			$upgrade=get_system_default('stable');
			$systemid=get_system_setting("sysid");
			$light=get_system_default('light');
			$pexpire=get_system_default('postexpiry')/60/60;
			$uas=get_system_default('altsesstore');
			$asl=get_system_default('altsesstorepath');
			$autorefresh=get_system_default('autorefresh');
			$sysmessage=get_system_default('sysmessage');
			$status=get_system_default('status');
			$comments=get_system_default('comments');
			$vcomments=get_system_default('viewcomments');
			$songformat=get_system_default('songformat');
			$songformathr=get_system_default('songformathr');
			$errlvl=get_system_default('errlvl');
			$blanking=get_system_default('blanking');
			$logerr=get_system_default('logerr');
		}
		else
		{
			//User is not permitted to view this page
			die("You are not an administrator. <a href=\"login.php?ref=admin\">Sign in</a> or <a href=\"index.php\">Cancel</a>.");
		}
	}
  ?>
  <body>
  <h1 style="text-align:center; text-decoration:underline;"><?php echo $sysname; ?>MRS-Administration</h1>
  <form method="post" action="admin.php">
  <input type="hidden" name="s" value="y">
  <h3>System Settings</h3>
  System name: <input type="text" name="name" value="<?php echo $name; ?>"><br>
  Timezone: <input type="radio" name="zone" value="America/Toronto" <?php if ($timezone == "America/Toronto") { echo ("checked=\"checked\""); } ?>>Eastern | <input type="radio" name="zone" value="America/Winnipeg" <?php if ($timezone == "America/Winnipeg") { echo ("checked=\"checked\""); } ?>>Central | <input type="radio" name="zone" value="America/Denver" <?php if ($timezone == "America/Denver") { echo ("checked=\"checked\""); } ?>>Mountain | <input type="radio" name="zone" value="America/Phoenix" <?php if ($timezone == "America/Phoenix") { echo ("checked=\"checked\""); } ?>>Mountain (no DST) | <input type="radio" name="zone" value="America/Vancouver" <?php if ($timezone == "America/Vancouver") { echo ("checked=\"checked\""); } ?>>Pacific<br><br>
  Logging: <input type="radio" name="logging" value="yes" <?php if ($logging == "yes") { echo ("checked=\"checked\""); } ?>>Yes | <input type="radio" name="logging" value="no"  <?php if ($logging == "no") { echo ("checked=\"checked\""); } ?>>No<br><br>
  Allow request status viewing by all members: <input type="radio" name="status" value="yes"  <?php if ($status == "yes") { echo ("checked=\"checked\""); } ?>>Yes | <input type="radio" name="status" value="no"  <?php if ($status == "no") { echo ("checked=\"checked\""); } ?>>No<br>
  Allow comment+response viewing by all members: <input type="radio" name="vcomments" value="yes"  <?php if ($vcomments == "yes") { echo ("checked=\"checked\""); } ?>>Yes | <input type="radio" name="vcomments" value="no"  <?php if ($vcomments == "no") { echo ("checked=\"checked\""); } ?>>No<br>
  Enable request view when system locked: <input type="radio" name="eroc" value="yes"  <?php if ($eroc == "yes") { echo ("checked=\"checked\""); } ?>>Yes | <input type="radio" name="eroc" value="no"  <?php if ($eroc == "no") { echo ("checked=\"checked\""); } ?>>No<br><br>
  Use alternate session storage?: <input type="radio" name="uas" value="yes"  <?php if ($uas == "yes") { echo ("checked=\"checked\""); } ?>>Yes | <input type="radio" name="uas" value="no"  <?php if ($uas == "no") { echo ("checked=\"checked\""); } ?>>No<br>
  Alternate session storage location: <input type="text" name="asl" value="<?php if(isset($asl)) { echo $asl; } ?>"><br><br>
  Automatically refresh the home page after: <input type="text" name="autorefresh" value="<?php echo $autorefresh; ?>"> seconds (0 for never)<br>
  System message:<br>
  <textarea name="sysmessage" rows="5" cols="50"><?php echo $sysmessage; ?></textarea><br>
  Error reporting level: <input type="radio" name="errlvl" value="0"<?php if(isset($errlvl) && $errlvl == 0) { echo " checked=\"checked\""; } ?>>Only errors | <input type="radio" name="errlvl" value="1"<?php if(isset($errlvl) && $errlvl == 1) { echo " checked=\"checked\""; } ?>>System messages only | <input type="radio" name="errlvl" value="2"<?php if(isset($errlvl) && $errlvl == 2) { echo " checked=\"checked\""; } ?>>All messages<br>
  Write all errors to a log file: <input type="radio" name="logerr" value="yes"  <?php if ($logerr == "yes") { echo ("checked=\"checked\""); } ?>>Yes | <input type="radio" name="logerr" value="no"  <?php if ($logerr == "no") { echo ("checked=\"checked\""); } ?>>No<br>
  <h3>System Actions</h3>
  <a href="automated.php">Manage system API</a><br>
  <a href="ruledit.php">Edit rule list</a><br>
  <a href="archive.php">Archive all posts</a> | <a href="delall.php">Delete all posts</a><br>
  <a href="password.php">Change system password</a> | <a href="security.php">Change system security options</a><br>
  <a href="viewlog.php">View logfile</a> | <a href="viewerr.php">View error logs</a><br>
  <a href="purgesess.php">Purge existing sessions</a><br>
  <h3>Posting</h3>
  Enable requests: <input type="radio" name="posting" value="yes" <?php if ($posting == "yes") { echo ("checked=\"checked\""); } ?>>Yes | <input type="radio" name="posting" value="no"  <?php if ($posting == "no") { echo ("checked=\"checked\""); } ?>>No<br>
  Enable requestee-submitted comments: <input type="radio" name="comments" value="yes" <?php if ($comments == "yes") { echo ("checked=\"checked\""); } ?>>Yes | <input type="radio" name="comments" value="no"  <?php if ($comments == "no") { echo ("checked=\"checked\""); } ?>>No<br>
  Anonymous requesting: <input type="radio" name="anon" value="yes" <?php if ($anon == "yes") { echo ("checked=\"checked\""); } ?>>Yes | <input type="radio" name="anon" value="no"  <?php if ($anon == "no") { echo ("checked=\"checked\""); } ?>>No<br>
  Allow open requests: <input type="radio" name="open" value="yes"  <?php if ($open == "yes") { echo ("checked=\"checked\""); } ?>>Yes | <input type="radio" name="open" value="no"  <?php if ($open == "no") { echo ("checked=\"checked\""); } ?>>No<br>
  Hide declined and played requests after: <input type="radio" name="pexpire" value="1" <?php if ($pexpire == "1") { echo ("checked=\"checked\""); } ?>>1 hour | <input type="radio" name="pexpire" value="3" <?php if ($pexpire == "3") { echo ("checked=\"checked\""); } ?>>3 hours | <input type="radio" name="pexpire" value="24" <?php if ($pexpire == "24") { echo ("checked=\"checked\""); } ?>>1 day<br>
  Distinguish open, queued and declined/played requests using: <input type="radio" name="blanking" value="yes" <?php if ($blanking == "yes") { echo ("checked=\"checked\""); } ?>>Opacity changes | <input type="radio" name="blanking" value="no" <?php if ($blanking == "no") { echo ("checked=\"checked\""); } ?>>Separators<br>
  <h3>Post Restrictions</h3>
  Disable requests for users who have unplayed/undeclined requests: <input type="radio" name="pdreq" value="yes"  <?php if ($pdreq == "yes") { echo ("checked=\"checked\""); } ?>>Yes | <input type="radio" name="pdreq" value="no"  <?php if ($pdreq == "no") { echo ("checked=\"checked\""); } ?>>No<br><br>
  Restriction type: <input type="radio" name="type" value="0" <?php if ($type == "0") { echo ("checked=\"checked\""); } ?>>1 hour | <input type="radio" name="type" value="1" <?php if ($type == "1") { echo ("checked=\"checked\""); } ?>>3 hours | <input type="radio" name="type" value="2" <?php if ($type == "2") { echo ("checked=\"checked\""); } ?>>1 day<br>
  Username restriction:&nbsp;
  <select name="unlock">
  <option value="">-Select one-</option>
  <option value="1" <?php if ($unlock == "1") { echo ("selected=\"selected\""); } ?>>1</option>
  <option value="2" <?php if ($unlock == "2") { echo ("selected=\"selected\""); } ?>>2</option>
  <option value="3" <?php if ($unlock == "3") { echo ("selected=\"selected\""); } ?>>3</option>
  <option value="5" <?php if ($unlock == "5") { echo ("selected=\"selected\""); } ?>>5</option>
  <option value="10" <?php if ($unlock == "10") { echo ("selected=\"selected\""); } ?>>10</option>
  <option value="0" <?php if ($unlock == "0") { echo ("selected=\"selected\""); } ?>>None</option>
  </select><br>
  IP restriction:&nbsp;
  <select name="iplock">
  <option value="">-Select one-</option>
  <option value="1" <?php if ($iplock == "1") { echo ("selected=\"selected\""); } ?>>1</option>
  <option value="2" <?php if ($iplock == "2") { echo ("selected=\"selected\""); } ?>>2</option>
  <option value="3" <?php if ($iplock == "3") { echo ("selected=\"selected\""); } ?>>3</option>
  <option value="5" <?php if ($iplock == "5") { echo ("selected=\"selected\""); } ?>>5</option>
  <option value="10" <?php if ($iplock == "10") { echo ("selected=\"selected\""); } ?>>10</option>
  <option value="0" <?php if ($iplock == "0") { echo ("selected=\"selected\""); } ?>>None</option>
  </select><br>
  Daily restriction:&nbsp;
  <select name="dayrestrict">
  <option value="">-Select one-</option>
  <option value="1" <?php if ($daylock == "1") { echo ("selected=\"selected\""); } ?>>1</option>
  <option value="2" <?php if ($daylock == "2") { echo ("selected=\"selected\""); } ?>>2</option>
  <option value="3" <?php if ($daylock == "3") { echo ("selected=\"selected\""); } ?>>3</option>
  <option value="5" <?php if ($daylock == "5") { echo ("selected=\"selected\""); } ?>>5</option>
  <option value="10" <?php if ($daylock == "10") { echo ("selected=\"selected\""); } ?>>10</option>
  <option value="0" <?php if ($daylock == "0") { echo ("selected=\"selected\""); } ?>>None</option>
  </select><br>
  Overflow restriction:&nbsp;
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
  <option value="0" <?php if ($overflow == "0") { echo ("selected=\"selected\""); } ?>>None</option>
  </select><br>
  <h3>Song Lists</h3>
  Allow searching: <input type="radio" name="search" value="yes"  <?php if ($search == "yes") { echo ("checked=\"checked\""); } ?>>Yes | <input type="radio" name="search" value="no"  <?php if ($search == "no") { echo ("checked=\"checked\""); } ?>>No<br>
  Strip the following words from searches (comma-separated): <input type="text" name="stripwords" value="<?php echo $stripwords; ?>"><br>
  Enable light mode: <input type="radio" name="light" value="yes"  <?php if ($light == "yes") { echo ("checked=\"checked\""); } ?>>Yes | <input type="radio" name="light" value="no"  <?php if ($light == "no") { echo ("checked=\"checked\""); } ?>>No<br>
  Song list format: <input type="text" name="songformat" value="<?php echo $songformat; ?>" disabled="disabled"> (used by the system to configure outputs)<br>
  Displayed song list format: <input type="text" name="songformathr" value="<?php echo $songformathr; ?>" disabled="disabled"> (used on the song searching page)<br>
  <a href="listedit.php">Edit song database</a><br>
  <h3>System Upgrades</h3>
  Check only for stable builds: <input type="radio" name="upgrade" value="yes"  <?php if ($upgrade == "yes") { echo ("checked=\"checked\""); } ?>>Yes | <input type="radio" name="upgrade" value="no"  <?php if ($upgrade == "no") { echo ("checked=\"checked\""); } ?>>No<br>
  Mirror to check:&nbsp;
  <select name="mirror" disabled="disabled">
  <option value="">-Select one-</option>
  <option value="main" selected="selected">firealarms.redbat.ca (Primary-Canada)</option>
  </select><br>
  <a href="upgrade/index.php">Check for updates</a><br><br>
  <input type="checkbox" name="debug" value="y">Enable debugging mode (do not automatically redirect when finished)<br>
  <input type="submit"><input type="button" value="Cancel" onclick="window.location.href='index.php'"><input type="button" value="Return to defaults" onclick="window.location.href='admin.php?default=yes'">
  </form>
  </body>
</html>