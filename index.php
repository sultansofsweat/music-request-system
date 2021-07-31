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
	//Execute automatic open/close
	$debug=auto_open_close();
	if($debug === true)
	{
		echo("<script type=\"text/javascript\">window.location = \"index.php\"</script>");
	}
?>
<?php
	//Useful functions
	
	//Function for determining if you can view list when system is closed
	function view_list_when_closed()
	{
		if(get_system_setting("eroc") == "yes")
		{
			return true;
		}
		return false;
	}
	//Function for determining if you can view comments as a non-administrator
	function view_comments_as_peasant()
	{
		if(get_system_setting("viewcomments") == "yes")
		{
			return true;
		}
		return false;
	}
	//Function for determining if you can view request status when system is closed
	function view_status_when_closed()
	{
		if(get_system_setting("status") == "yes")
		{
			return true;
		}
		return false;
	}
	//Function for getting autorefresh time
	function autorefresh()
	{
		return get_system_setting("autorefresh");
	}
	//Function for getting system message
	function system_message()
	{
		return get_system_setting("sysmessage");
	}
	//Function for getting the post expiry time
	function get_expiry_time()
	{
		return get_system_setting("postexpiry");
	}
	//Function for determining whether or not the system is in light mode
	function in_light_mode()
	{
		if(get_system_setting("light") == "yes")
		{
			return true;
		}
		return false;
	}
	//Function for getting unseen requests
	function get_unseen_reqs()
	{
		return get_specific_reqs_return(array(0));
	}
	//Function for getting queued requests
	function get_queued_reqs()
	{
		return get_specific_reqs_return(array(2));
	}
	function get_stale_reqs()
	{
		return get_specific_reqs_return(array(1,3));
	}
	//Function for determining the "request level"
	function request_level()
	{
		//Start at a default of open
		$level=2;
		//Check if system is closed
		if(is_system_enabled() === false)
		{
			$level=0;
		}
		//Check if system is in overload mode
		if(system_in_overload() === true)
		{
			$level=0;
		}
		//Check if user's IP is banned
		if(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] != "")
		{
			$ban=is_ip_banned($_SERVER['REMOTE_ADDR']);
		}
		else
		{
			$ban=false;
		}
		if($ban === true)
		{
			$level=1;
		}
		//Check if user's username is banned
		if(isset($_SESSION['uname']) && $_SESSION['uname'] != "")
		{
			$ban=is_user_banned($_SESSION['uname']);
		}
		else
		{
			$ban=false;
		}
		if($ban === true)
		{
			$level=1;
		}
		//Return the current status
		return $level;
	}
	//Function for displaying the header
	function display_header($admin,$reqlvl,$posting,$logging,$vlist)
	{
		//First line: Login/out, Request, Admin console (if applicable)
		//Second line: Banning stuff for admin, Rules+About otherwise
		//Third line: Quick view (for admins only)
		echo("Hello, ");
		if(isset($_SESSION['uname']) && $_SESSION['uname'] != "")
		{
			echo $_SESSION['uname'];
		}
		else
		{
			echo "unidentified user";
		}
		echo("!<br>\r\n");
		if($admin === true)
		{
			echo ("<a href=\"logout.php\">Exit Admin Mode</a> | <a href=\"select.php\">Request</a> | <a href=\"admin.php\">Administration</a> | <a href=\"about.php\">About the MRS</a><br>\r\n");
			echo ("<a href=\"bun.php\">Ban username</a> | <a href=\"vun.php\">View username banlist</a> | <a href=\"bip.php\">Ban IP address</a> | <a href=\"vip.php\">View IP banlist</a> | <a href=\"viewreports.php\">View Post Reports</a><br>\r\n");
			echo ("Quick view: ");
			if($posting === true)
			{
				echo("Posting enabled | ");
			}
			else
			{
				echo("Posting disabled | ");
			}
			if($logging === true)
			{
				echo ("Logging enabled\r\n");
			}
			else
			{
				echo ("Logging disabled\r\n");
			}
		}
		else
		{
			/* Request levels:
			0-Closed
			1-Banned
			2-Open */
			switch($reqlvl)
			{
				case 0:
				echo ("<a href=\"login.php\">Enter Admin Mode</a> | ");
				echo ("Requesting disabled.");
				if($vlist === true && in_light_mode() === false)
				{
					echo (" <a href=\"select.php\">View Songs</a><br>\r\n");
				}
				else
				{
					echo ("<br>\r\n");
				}
				break;
				case 1:
				echo ("<strike>Enter Admin Mode</strike> | ");
				echo ("You are banned from making requests.<br>\r\n");
				break;
				case 2:
				echo ("<a href=\"login.php\">Enter Admin Mode</a> | ");
				echo ("<a href=\"select.php\">Request</a><br>\r\n");
				break;
				default:
				die(trigger_error("Invalid header level",E_USER_ERROR));
				break;
			}
			echo ("<a href=\"rules.php\">Request rules</a> | <a href=\"about.php\">About the MRS</a><br>\r\n");
		}
	}
	//Function for displaying a request
	function display_request($request,$admin,$exptime,$open,$vstat,$vcomments)
	{
		if(!is_array($request) || count($request) < 9)
		{
			//Request is not valid, throw an error and get out of here before something bad happens
			trigger_error("Request passed to display_request is not in a valid format",E_USER_WARNING);
			return "";
		}
		//Formulate request
		$request[4]=format_request($request[4]);
		//Determine the opacity of the output
		$opacity = 1;
		if(get_system_setting("blanking") == "yes")
		{
			if(time() > strtotime($request[3]) + $exptime && $request[5] != 0 && $request[5] != 2)
			{
				$opacity = 0.2;
			}
			else
			{
				switch($request[5])
				{
					case 1:
					case 3:
					$opacity=0.5;
					break;
					case 2:
					$opacity=0.7;
					break;
					default:
					$opacity=1;
					break;
				}
			}
		}
		//Begin output
		$string = "<hr>\r\n<div style=\"opacity:$opacity\">\r\n";
		//Output request
		$string.=$request[4] . "<br>\r\n";
		//Output requestee
		$string .= "Requested by " . $request[1];
		if($admin === true || $_SERVER['REMOTE_ADDR'] == $request[2])
		{
			//Output IP address of requestee
			$string .= " with IP address " . $request[2];
		}
		//Output date
		$string .= " on " . $request[3] . "<br>\r\n";
		//Output request status, if applicable
		if($admin === true || $_SERVER['REMOTE_ADDR'] == $request[2] || $vstat === true)
		{
			switch($request[5])
			{
				case 0:
				$string .= "Request has not been seen.<br>\r\n";
				break;
				case 1:
				$string .= "Request has been declined.<br>\r\n";
				break;
				case 2:
				$string .= "Request has been placed in the queue.<br>\r\n";
				break;
				case 3:
				$string .= "Request has been played.<br>\r\n";
				break;
				default:
				$string .= "Request has been microwaved and is in an indeterminate state.<br>\r\n";
				break;
			}
		}
		//If a requestee comment exists, output it
		if($request[7] != "" && $request[7] != "None" && ($vcomments === true || $admin === true))
		{
			$string .= "Comment: " . $request[7] . "<br>\r\n";
		}
		//If a response exists, output it
		if($request[6] != "" && $request[6] != "None" && ($vcomments === true || $admin === true))
		{
			$string .= "Response: " . $request[6] . "<br>\r\n";
		}
		//Output post options
		if($admin === true)
		{
			$string .= "<a href=\"delete.php?p=" . $request[0] . "\">Delete</a> | <a href=\"edit.php?p=" . $request[0] . "\">Edit</a> | ";
			if(time() <= strtotime($request[3]) + $exptime || $request[5] == 0 || $request[5] == 2)
			{
				switch($request[5])
				{
					case 0:
					$string .= "<a href=\"queue.php?p=" . $request[0] . "\">Put in queue</a> | <a href=\"decline.php?p=" . $request[0] . "\">Decline</a> | <a href=\"played.php?p=" . $request[0] . "\">Mark as played</a> | ";
					break;
					case 1:
					$string .= "<a href=\"queue.php?p=" . $request[0] . "\">Put in queue</a> | ";
					break;
					case 2:
					$string .= "<a href=\"decline.php?p=" . $request[0] . "\">Decline</a> | <a href=\"played.php?p=" . $request[0] . "\">Mark as played</a> | ";
					break;
					case 3:
					$string .= "<a href=\"queue.php?p=" . $request[0] . "\">Put in queue</a> | <a href=\"decline.php?p=" . $request[0] . "\">Decline</a> | ";
					break;
					default:
					$string .= "<a href=\"queue.php?p=" . $request[0] . "\">Put in queue</a> | <a href=\"decline.php?p=" . $request[0] . "\">Decline</a> | <a href=\"played.php?p=" . $request[0] . "\">Mark as played</a> | ";
					break;
				}
			}
			$string .= "<a href=\"bun.php?p=" . $request[1] . "\">Ban username</a> | <a href=\"bip.php?p=" . $request[2] . "\">Ban IP address</a>\r\n";
		}
		else
		{
			if(time() <= strtotime($request[3]) + $exptime || (($request[5] == 0 || $request[5] == 2) && $open == 2))
			{
				if($_SERVER['REMOTE_ADDR'] != $request[2])
                {
					$string .= "<a href=\"report.php?p=" . $request[0] . "\">Report request</a>\r\n";
				}
			}
		}
		//Finalize output string
		$string .= "</div>\r\n";
		//Output formatted string
		return stripcslashes($string);
	}
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
	//Check for a submission status
	if(isset($_GET['status']) && $_GET['status'] != "")
	{
		switch($_GET['status'])
		{
			case 0:
			trigger_error("Request made successfully");
			break;
			case 2:
			trigger_error("Request failed: you are banned",E_USER_WARNING);
			break;
			case 1:
			trigger_error("Request failed: this system is like, uh, closed or something",E_USER_WARNING);
			break;
			case 3:
			trigger_error("Request failed: cannot submit a request without a name, valid IP or, well, a request!",E_USER_WARNING);
			break;
			case 4:
			trigger_error("Request failed: slow your role, breh",E_USER_WARNING);
			break;
			case 5:
			trigger_error("Request failed: ballot box stuffing detected!",E_USER_WARNING);
			break;
			case 6:
			trigger_error("Request failed: attempt to tip the boat over!",E_USER_WARNING);
			break;
			case 8:
			trigger_error("Request failed: the system administrator does not have that song, you'd have better luck requesting GPX make good products!",E_USER_WARNING);
			break;
			case 7:
			default:
			trigger_error("Request failed: microwaves and/or Russians got involved. Contact the webmaster.",E_USER_WARNING);
			break;
		}
	}
	//Check for a IP ban status
	if(isset($_GET['ipstatus']) && $_GET['ipstatus'] != "")
	{
		switch($_GET['ipstatus'])
		{
			case 0:
			trigger_error("IP address banned successfully");
			break;
			case 1:
			trigger_error("IP address ban failed: the IP address submitted was not valid and should be thrown into a swimming pool",E_USER_WARNING);
			break;
			default:
			trigger_error("IP address ban failed: microwaves and/or Russians got involved. Contact the webmaster.",E_USER_WARNING);
			break;
		}
	}
	//Check for a username ban status
	if(isset($_GET['unstatus']) && $_GET['unstatus'] != "")
	{
		switch($_GET['unstatus'])
		{
			case 0:
			trigger_error("Username banned successfully");
			break;
			case 1:
			trigger_error("Username ban failed: the IP address submitted was not valid and should be thrown into a swimming pool",E_USER_WARNING);
			break;
			default:
			trigger_error("Username ban failed: microwaves and/or Russians got involved. Contact the webmaster.",E_USER_WARNING);
			break;
		}
	}
	//Check for a decline status
	if(isset($_GET['decstatus']) && $_GET['decstatus'] != "")
	{
		switch($_GET['decstatus'])
		{
			case 0:
			trigger_error("Declined request successfully");
			break;
			case 1:
			trigger_error("Failed to decline request: the request to decline was either microwaved or dunked in a pool and could not be found",E_USER_WARNING);
			break;
			case 2:
			trigger_error("Failed to decline request: the request file requires to be either microwaved or dunked in a pool",E_USER_WARNING);
			break;
			default:
			trigger_error("Failed to decline request: microwaves and/or Russians got involved. Contact the webmaster.",E_USER_WARNING);
			break;
		}
	}
	//Check for a decline status
	if(isset($_GET['mapstatus']) && $_GET['mapstatus'] != "")
	{
		switch($_GET['mapstatus'])
		{
			case 0:
			trigger_error("Marked request as played successfully");
			break;
			case 1:
			trigger_error("Failed to mark request as played: the request to mark was either microwaved or dunked in a pool and could not be found",E_USER_WARNING);
			break;
			case 2:
			trigger_error("Failed to mark request as played: the request file requires to be either microwaved or dunked in a pool",E_USER_WARNING);
			break;
			default:
			trigger_error("Failed to mark request as played: microwaves and/or Russians got involved. Contact the webmaster.",E_USER_WARNING);
			break;
		}
	}
	//Check for a decline status
	if(isset($_GET['qstatus']) && $_GET['qstatus'] != "")
	{
		switch($_GET['qstatus'])
		{
			case 0:
			trigger_error("Queued request successfully");
			break;
			case 1:
			trigger_error("Failed to queue request: the request to queue was either microwaved or dunked in a pool and could not be found",E_USER_WARNING);
			break;
			case 2:
			trigger_error("Failed to queue request: the request file requires to be either microwaved or dunked in a pool",E_USER_WARNING);
			break;
			default:
			trigger_error("Failed to queue request: microwaves and/or Russians got involved. Contact the webmaster.",E_USER_WARNING);
			break;
		}
	}
	//Check for a delete status
	if(isset($_GET['delstatus']) && $_GET['delstatus'] != "")
	{
		switch($_GET['delstatus'])
		{
			case 0:
			trigger_error("Request deleted successfully");
			break;
			case 1:
			trigger_error("Failed to delete request: the request file requires to be either microwaved or dunked in a pool",E_USER_WARNING);
			break;
			case 2:
			trigger_error("Failed to delete request: the request to delete was either microwaved or dunked in a pool and could not be found",E_USER_WARNING);
			break;
			default:
			trigger_error("Failed to delete request: microwaves and/or Russians got involved. Contact the webmaster.",E_USER_WARNING);
			break;
		}
	}
	//Check for an edit status
	if(isset($_GET['editstatus']) && $_GET['editstatus'] != "")
	{
		switch($_GET['editstatus'])
		{
			case 0:
			trigger_error("Request edited successfully");
			break;
			case 1:
			trigger_error("Failed to edit request: the request file requires to be either microwaved or dunked in a pool",E_USER_WARNING);
			break;
			case 2:
			trigger_error("Failed to edit request: the request to edit was either microwaved or dunked in a pool and could not be found",E_USER_WARNING);
			break;
			default:
			trigger_error("Failed to edit request: microwaves and/or Russians got involved. Contact the webmaster.",E_USER_WARNING);
			break;
		}
	}
	//Check login or logout status
	if(isset($_GET['li']) && $_GET['li'] == "yes")
	{
		trigger_error("Login successful.");
	}
	if(isset($_GET['lo']) && $_GET['lo'] == "yes")
	{
		trigger_error("Logout successful.");
	}
	//Check security change status
	if(isset($_GET['sc']) && $_GET['sc'] == "yes")
	{
		trigger_error("Security level changed. You have been logged out as a further security measure. Please log in again.");
	}
	//Check settings change status
	if(isset($_GET['admsave']))
	{
		switch($_GET['admsave'])
		{
			case "yes":
			trigger_error("Successfully changed system settings");
			break;
			case "no":
			trigger_error("Failed to change system settings: some Russian made off with parts of the MRS.",E_USER_WARNING);
			break;
			default:
			trigger_error("Failed to change system settings: microwaves and/or Russians got involved. Contact the webmaster.",E_USER_WARNING);
			break;
		}
	}
	//Check for a report status
	if(isset($_GET['repstatus']) && $_GET['repstatus'] != "")
	{
		switch($_GET['repstatus'])
		{
			case 0:
			trigger_error("Report sent successfully.");
			break;
			case 1:
			trigger_error("Report failed: could not write a report. Please microwave the system administrator.",E_USER_WARNING);
			break;
			case 3:
			trigger_error("Report failed: don't fiddle around with the post details or YOU will be microwaved!",E_USER_WARNING);
			break;
			case 4:
			trigger_error("Report failed: get off this system's lawn",E_USER_WARNING);
			break;
			case 5:
			trigger_error("Report failed: the report file was either microwaved or dunked in a pool and could not be found",E_USER_WARNING);
			break;
			default:
			trigger_error("Report failed: microwaves and/or Russians got involved. Contact the webmaster.",E_USER_WARNING);
			break;
		}
	}
	//Check for a ID status
	if(isset($_GET['idstat']) && $_GET['idstat'] != "")
	{
		switch($_GET['idstat'])
		{
			case 0:
			trigger_error("System ID changed successfully. Please log in again.");
			break;
			case 1:
			trigger_error("ID change failed: something is wrong with the backend and the server should be microwaved",E_USER_WARNING);
			break;
			case 2:
			trigger_error("ID change failed: get off this system's lawn",E_USER_WARNING);
			break;
			default:
			trigger_error("Report failed: microwaves and/or Russians got involved. Contact the webmaster.",E_USER_WARNING);
			break;
		}
	}
	if(isset($_GET['banuser']) && $_GET['banuser'] == "yes")
	{
		trigger_error("Successfully added username to the ban list.");
	}
	elseif(isset($_GET['banuser']) && $_GET['banuser'] == "no")
	{
		trigger_error("Failed to ban user: microwaves and/or Russians got involved.",E_USER_WARNING);
	}
	if(isset($_GET['banip']) && $_GET['banip'] == "yes")
	{
		trigger_error("Successfully added IP address to the ban list.");
	}
	elseif(isset($_GET['banip']) && $_GET['banip'] == "no")
	{
		trigger_error("Failed to ban IP address: microwaves and/or Russians got involved.",E_USER_WARNING);
	}
	if(isset($_GET['banzored']) && $_GET['banzored'] == "yes")
	{
		trigger_error("You're banned. Don't bother trying to sign in, go away instead.",E_USER_WARNING);
	}
?>
<?php
	//Change timezone
	set_timezone();
	//Log page open if required
	if(is_logging_enabled() === true)
	{
		write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Visited index page");
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <meta name="generator" content="CoffeeCup HTML Editor (www.coffeecup.com)">
    <meta name="created" content="Sat, 07 Oct 2017 09:22:11 GMT">
    <meta name="description" content="Listening to a live show? Got a song you have to hear? This is the place to request it!">
    <meta name="keywords" content="">
	<link rel="shortcut icon" href="backend/favicon.ico">
    <title><?php echo system_name(); ?>Music Request System-Home</title>
    <?php
		if(autorefresh() > 0)
		{
			echo ("<meta http-equiv=\"refresh\" content=\"" . autorefresh() . "\">\r\n");
		}
	?>
	
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
		//Run functions that are used multiple times
		
		$admin=securitycheck();
		$sysenable=is_system_enabled();
		$viewonclose=view_list_when_closed();
		$expiry=get_expiry_time();
		$reqlvl=request_level();
		$viewstat=view_status_when_closed();
		$logging=is_logging_enabled();
		$message=system_message();
		$viewcomments=view_comments_as_peasant();
		
		//Verify config
		verify_system_config();
	?>
	<h1 style="text-align:center; text-decoration:underline;"><?php echo system_name(); ?>Music Request System</h1>
	<!-- Display header -->
	<p><?php display_header($admin,$reqlvl,$sysenable,$logging,$viewonclose); ?></p>
	<?php
		//Output the number of all-time requests
		echo ("<h3>There have been " . get_post_count() . " request");
		if(get_post_count() != 1)
		{
			echo("s");
		}
		echo (" made all-time on this system.");
		//If the system is not in light mode, output the number of songs
		if(in_light_mode() === false)
		{
			echo (" Right now, there are " . get_song_count() . " song");
			if(get_song_count() != 1)
			{
				echo("s");
			}
			echo (" total on " . count_song_lists() . " list");
			if(count_song_lists() != 1)
			{
				echo("s");
			}
			echo (" in our music library.");
		}
		echo ("</h3>\r\n");
		//If logging is enabled, tell the user
		if($logging === true && $admin !== true)
		{
			echo("<h3>WARNING: System logging is enabled! <a href=\"logging.php\">Learn more</a></h3>\r\n");
		}
		//Output the system message, if any
		if($message != "")
		{
			echo ("<h3>" . stripcslashes($message) . "</h3>\r\n");
		}
		
		if($sysenable === false && $admin === false)
		{
			//System is closed
			echo ("<hr>\r\n<p>The MRS is currently closed and will not accept any new requests.");
			if($viewonclose === true)
			{
				//Existing requests can be seen
				echo (" You may see existing requests below.");
			}
			echo ("</p>\r\n");
		}
		
		if($sysenable === true || $viewonclose === true || $admin === true)
		{
			//Get all requests, then get all open and all queued requests
			$open=get_unseen_reqs();
			$queued=get_queued_reqs();
			$all=get_stale_reqs();
			usort($all,"sort_reqs_desc");
			usort($open,"sort_reqs_asc");
			usort($queued,"sort_reqs_asc");
			//Output all open requests
			foreach($open as $req)
			{
				echo display_request($req,$admin,$expiry,$reqlvl,$viewstat,$viewcomments);
			}
			if(get_system_setting("blanking") == "no")
			{
				echo("<hr><hr><hr>\r\n");
			}
			//Output all queued requests
			foreach($queued as $req)
			{
				echo display_request($req,$admin,$expiry,$reqlvl,$viewstat,$viewcomments);
			}
			if(get_system_setting("blanking") == "no")
			{
				echo("<hr><hr><hr>\r\n");
			}
			//Output all other requests
			foreach($all as $req)
			{
				echo display_request($req,$admin,$expiry,$reqlvl,$viewstat,$viewcomments);
			}
		}
	?>
  </body>
</html>