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
    <meta name="description" content="Listening to a live show? Got a song you have to hear? This is the place to request it!">
	<link rel="shortcut icon" href="backend/favicon.ico">
    <title><?php echo $sysname; ?>Music Request System-Edit Automatic Opening/Closing Rules</title>
    
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
	if(is_logging_enabled() === true)
	{
		if(isset($_GET['mode']) && ($_GET['mode'] == "add" || $_GET['mode'] == "delete") && securitycheck() === true)
		{
			//Begin submission
			switch($_GET['mode'])
			{
				case "add":
				//Start adding rule
				if(!empty($_GET['days']) &&
				isset($_GET['openhour']) && ($openhour=preg_replace("/[^0-9]/","",$_GET['openhour'])) != "" &&
				isset($_GET['openminute']) && ($openminute=preg_replace("/[^0-9]/","",$_GET['openminute'])) != "" &&
				isset($_GET['openmerid']) && ($openmerid=preg_replace("/[^A-Z]/","",$_GET['openmerid'])) != "" && ($openmerid == "AM" || $openmerid == "PM") &&
				isset($_GET['closehour']) && ($closehour=preg_replace("/[^0-9]/","",$_GET['closehour'])) != "" &&
				isset($_GET['closeminute']) && ($closeminute=preg_replace("/[^0-9]/","",$_GET['closeminute'])) != "" &&
				isset($_GET['closemerid']) && ($closemerid=preg_replace("/[^A-Z]/","",$_GET['closemerid'])) != "" && ($closemerid == "AM" || $closemerid == "PM"))
				{
					//Assume rule does not span across a day
					$nextday=0;
					if($openhour > $closehour || ($openhour == $closehour && $openminute > $closeminute))
					{
						//Rule does span across a day
						$nextday=1;
					}
					$debug=add_autoopen_rule(implode(",",$_GET['days']),$openhour,$openminute,$openmerid,$closehour,$closeminute,$closemerid,$nextday);
					if($debug === true)
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Successfully added new open/close rule");
						trigger_error("Added new open/close rule.");
					}
					else
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to add new open/close rule, server problem");
						trigger_error("Failed to add new open/close rule. Beat the server with a dead trout and try again.",E_USER_ERROR);
					}
				}
				else
				{
					//Some information missing
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to add new open/close rule: submission incomplete.");
					trigger_error("Cannot submit rule. Some information missing. Beat the submission form with a dead trout and try again.",E_USER_WARNING);
				}
				break;
				
				case "delete":
				//Start adding rule
				if(isset($_GET['id']) && ($id=preg_replace("/[^0-9]/","",$_GET['id'])) != "")
				{
					$debug=remove_autoopen_rule($id);
					if($debug === true)
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Successfully removed open/close rule");
						trigger_error("Removed open/close rule.");
					}
					else
					{
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to remove open/close rule, server problem");
						trigger_error("Failed to remove open/close rule. Beat the server with a dead trout and try again.",E_USER_ERROR);
					}
				}
				else
				{
					//Some information missing
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to remove open/close rule: submission incomplete.");
					trigger_error("Cannot remove rule. Some information missing. Beat the submission form with a dead trout and try again.",E_USER_WARNING);
				}
				break;
			}
		}
		write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Visited auto open/close rule editing page");
		if(securitycheck() === false)
		{
			die("You are not an administrator. <a href=\"login.php?ref=autoopen\">Sign in</a> or <a href=\"index.php\">Cancel</a>.");
		}
	}
	else
	{
		if(isset($_GET['mode']) && ($_GET['mode'] == "add" || $_GET['mode'] == "delete") && securitycheck() === true)
		{
			//Begin submission
			switch($_GET['mode'])
			{
				case "add":
				//Start adding rule
				if(!empty($_GET['days']) &&
				isset($_GET['openhour']) && ($openhour=preg_replace("/[^0-9]/","",$_GET['openhour'])) != "" &&
				isset($_GET['openminute']) && ($openminute=preg_replace("/[^0-9]/","",$_GET['openminute'])) != "" &&
				isset($_GET['openmerid']) && ($openmerid=preg_replace("/[^A-Z]/","",$_GET['openmerid'])) != "" && ($openmerid == "AM" || $openmerid == "PM") &&
				isset($_GET['closehour']) && ($closehour=preg_replace("/[^0-9]/","",$_GET['closehour'])) != "" &&
				isset($_GET['closeminute']) && ($closeminute=preg_replace("/[^0-9]/","",$_GET['closeminute'])) != "" &&
				isset($_GET['closemerid']) && ($closemerid=preg_replace("/[^A-Z]/","",$_GET['closemerid'])) != "" && ($closemerid == "AM" || $closemerid == "PM"))
				{
					//Assume rule does not span across a day
					$nextday=0;
					if($openhour > $closehour || ($openhour == $closehour && $openminute > $closeminute))
					{
						//Rule does span across a day
						$nextday=1;
					}
					$debug=add_autoopen_rule(implode(",",$_GET['days']),$openhour,$openminute,$openmerid,$closehour,$closeminute,$closemerid,$nextday);
					if($debug === true)
					{
						trigger_error("Added new open/close rule.");
					}
					else
					{
						trigger_error("Failed to add new open/close rule. Beat the server with a dead trout and try again.",E_USER_ERROR);
					}
				}
				else
				{
					//Some information missing
					trigger_error("Cannot submit rule. Some information missing. Beat the submission form with a dead trout and try again.",E_USER_WARNING);
				}
				break;
				
				case "delete":
				//Start adding rule
				if(isset($_GET['id']) && ($id=preg_replace("/[^0-9]/","",$_GET['id'])) != "")
				{
					$debug=remove_autoopen_rule($id);
					if($debug === true)
					{
						trigger_error("Removed open/close rule.");
					}
					else
					{
						trigger_error("Failed to remove open/close rule. Beat the server with a dead trout and try again.",E_USER_ERROR);
					}
				}
				else
				{
					//Some information missing
					trigger_error("Cannot remove rule. Some information missing. Beat the submission form with a dead trout and try again.",E_USER_WARNING);
				}
				break;
			}
		}
		if(securitycheck() === false)
		{
			die("You are not an administrator. <a href=\"login.php?ref=autoopen\">Sign in</a> or <a href=\"index.php\">Cancel</a>.");
		}
	}
?>
  <body>
  <h1 style="text-align:center; text-decoration:underline;"><?php echo $sysname; ?>Music Request System-Edit Automatic Opening/Closing Rules</h1>
  <h3>Existing Rules</h3>
  <p>
  <?php
	$rules=get_autoopen_rules();
	foreach($rules as $rule)
	{
		//FORMAT: [id,days,open,close,nextday]
		$daystring="";
		foreach(explode(",",$rule[1]) as $day)
		{
			switch($day)
			{
				case 0:
				$daystring.="Sunday, ";
				break;
				
				case 1:
				$daystring.="Monday, ";
				break;
				
				case 2:
				$daystring.="Tuesday, ";
				break;
				
				case 3:
				$daystring.="Wednesday, ";
				break;
				
				case 4:
				$daystring.="Thursday, ";
				break;
				
				case 5:
				$daystring.="Friday, ";
				break;
				
				case 6:
				$daystring.="Saturday, ";
				break;
				
				default:
				$daystring.="Blasphemy, ";
				break;
			}
		}
		echo(substr($daystring,0,-2) . ": open at " . $rule[2] . ", close at " . $rule[3] . " <a href=\"autoopen.php?mode=delete&id=" . $rule[0] . "\">Delete</a><br>\r\n");
	}
  ?>
  </p>
  <form method="get" action="autoopen.php">
  <input type="hidden" required="required" name="mode" value="add">
  Days: <input type="checkbox" name="days[]" value="0">Sunday | <input type="checkbox" name="days[]" value="1">Monday | <input type="checkbox" name="days[]" value="2">Tuesday | <input type="checkbox" name="days[]" value="3">Wednesday | <input type="checkbox" name="days[]" value="4">Thursday | <input type="checkbox" name="days[]" value="5">Friday | <input type="checkbox" name="days[]" value="6">Saturday<br>
  Open at: <select name="openhour" required="required">
  <option value="1">1</option>
  <option value="2">2</option>
  <option value="3">3</option>
  <option value="4">4</option>
  <option value="5">5</option>
  <option value="6">6</option>
  <option value="7">7</option>
  <option value="8">8</option>
  <option value="9">9</option>
  <option value="10">10</option>
  <option value="11">11</option>
  <option value="12">12</option>
  </select>:<select name="openminute" required="required">
  <option value="00">00</option>
  <option value="10">10</option>
  <option value="15">15</option>
  <option value="20">20</option>
  <option value="30">30</option>
  <option value="40">40</option>
  <option value="45">45</option>
  <option value="50">50</option>
  </select>&nbsp;<select name="openmerid" required="required">
  <option value="AM">AM</option>
  <option value="PM">PM</option>
  </select><br>
  Close at: <select name="closehour" required="required">
  <option value="1">1</option>
  <option value="2">2</option>
  <option value="3">3</option>
  <option value="4">4</option>
  <option value="5">5</option>
  <option value="6">6</option>
  <option value="7">7</option>
  <option value="8">8</option>
  <option value="9">9</option>
  <option value="10">10</option>
  <option value="11">11</option>
  <option value="12">12</option>
  </select>:<select name="closeminute" required="required">
  <option value="00">00</option>
  <option value="10">10</option>
  <option value="15">15</option>
  <option value="20">20</option>
  <option value="30">30</option>
  <option value="40">40</option>
  <option value="45">45</option>
  <option value="50">50</option>
  </select>&nbsp;<select name="closemerid" required="required">
  <option value="AM">AM</option>
  <option value="PM">PM</option>
  </select><br>
  <input type="submit" value="Add/edit rule"> or <input type="button" value="Cancel" onclick="window.location.href='admin.php'">
  </form>
  </body>
</html>