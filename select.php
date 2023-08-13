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
	//If "light" mode is enabled, bypass this page entirely
	if(get_system_setting("light") == "yes")
	{
		echo ("<script type=\"text/javascript\">window.location = \"post.php\"</script>");
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
	//If username is not stored, set it
	if(!isset($_SESSION['uname']))
	{
		$_SESSION['uname']="";
	}
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
	//Function for determining if Christmas music requesting is allowed
	function is_christmas_allowed()
	{
		if(file_exists("songs/christmas.txt"))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	//Function for searching the song list
	function search($list,$terms)
	{
		//Split terms into array
		$terms=explode(",",$terms);
		//Get song list
        $list=get_songs($list);
		//Get list of possible search fields
		$rawfields=explode("|",get_system_setting("songformat"));
		//Filter out search fields that are not for display
		$fields=array();
		foreach($rawfields as $field)
		{
			if(strpos($field,"*") === false)
			{
				$fields[]=$field;
			}
		}
		//Add "any" to valid field list
		$fields[]="any";
		//Process search terms. FORMAT: [field,query,strict?]
		$queries=array();
		foreach($terms as $term)
		{
			//If no modifiers found, treat it as "any"
			if(strpos($term,"=") === false)
			{
				$term=strtolower(preg_replace("/[^A-Za-z0-9]/","",$term));
				foreach($fields as $field)
				{
					$queries[]=array("any",$term,false);
				}
			}
			else
			{
				//Check for strict modifier ("==")
				if(strpos($term,"==") !== false)
				{
					$term=str_replace("==","=",$term);
					$strict=true;
				}
				else
				{
					$strict=false;
				}
				//Split term into components
				$term=explode("=",$term);
				//Format query
				$term[1]=strtolower(preg_replace("/[^A-Za-z0-9]/","",$term[1]));
				//If query is "any" and strict is set, throw an error and unset strict
				if($term[0] == "any" && $strict === true)
				{
					trigger_error("Strict flag with the any field is illegal. Strict flag has been removed.",E_USER_WARNING);
					$strict=false;
				}
				//Make sure term field is valid
				if(in_array($term[0],$fields))
				{
					//Add term to query list
					$queries[]=array($term[0],$term[1],$strict);
				}
				else
				{
					trigger_error("Invalid search field: " . $term[0] . ". It is being thrown out with the bathwater. Expect problems.");
				}
			}
		}
		//Go through each query
		foreach($queries as $query)
		{
			$songs=array();
			//Go through songs and check against the query
			foreach($list as $song)
			{
				//Formulate search term
				if($query[0] == "any")
				{
					$searchterm=strtolower(preg_replace("/[^A-Za-z0-9]/","",implode("",$song)));
				}
				else
				{
					$searchterm=strtolower(preg_replace("/[^A-Za-z0-9]/","",$song[$query[0]]));
				}
				if($query[2] === true && $searchterm == $query[1])
				{
					$songs[]=$song;
				}
				elseif($query[2] === false && strpos($searchterm,$query[1]) !== false)
				{
					$songs[]=$song;
				}
			}
			//Make base list the list of found songs for next query
			$list=$songs;
		}
		return $list;
	}
	//Function for getting songs based on an input query
	function query($list,$term)
	{
		//Get song list
        $list=get_songs($list);
		$songs=array();
		//If query is for everything, just give everything
		if($term == "all")
		{
			return $list;
		}
		//If query is for new songs, get only songs added in the last 7 days
		elseif($term == "new")
		{
			foreach($list as $song)
			{
				if(isset($song["added_to_system"]) && ($song["added_to_system"] + 7*24*60*60) > time())
				{
					$songs[]=$song;
				}
			}
		}
		//If query is for popular songs, get the songs requested the most frequent (system configurable)
		elseif($term == "freq")
		{
            usort($list,"sort_by_popularity");
            $current=0;
            $index=0;
            for($i=0;$i<get_system_setting("popular");$i++)
            {
                if($list[$index]["request_count"] == 0)
                {
                    break;
                }
                $current=$list[$index]["request_count"];
                while($index < count($list) && $list[$index]["request_count"] == $current)
                {
                    $songs[]=$list[$index];
                    $index++;
                }
                if($index >= count($list))
                {
                    break;
                }
            }
		}
		//If query is for recently added songs, get the songs corresponding to the most recent addition times (system configurable)
		elseif($term == "recadd")
		{
            usort($list,"sort_by_date_added");
            $current=0;
			$count=0;
            $index=0;
            for($i=0;$i<get_system_setting("recent");$i++)
            {
                $current=$list[$index]["added_to_system"];
                while($index < count($list) && $list[$index]["added_to_system"] == $current)
                {
                    $songs[]=$list[$index];
                    $index++;
                }
                if($index >= count($list))
                {
                    break;
                }
            }
		}
		else
		{
			foreach($list as $song)
			{
				//Get character to search
				$character=substr($song["artist"],0,1);
				//If searching "other", check if first letter of artist is a non-alpha character
				if($term == "other" && preg_replace("/[^A-Za-z]/","",$character) == "")
				{
					$songs[]=$song;
				}
				elseif(strtolower($character) == $term)
				{
					$songs[]=$song;
				}
			}
		}
		return $songs;
	}
	
	//Function for checking if open requests are allowed
	function is_open_enabled()
	{
		/* Check methodology
		-System is open or not
		-Open requests are enabled
		-No overload in system
		-No pending request for user
		-User has not exceeded request limit
		*/
		if(get_system_setting("posting") != "yes")
		{
			//echo ("DEBUG: posting disabled.<br>\r\n");
			//System disabled
			return false;
		}
		if(get_system_setting("open") != "yes")
		{
			//echo ("DEBUG: open disabled.<br>\r\n");
			//Open requests disabled
			return false;
		}
		
		//Everything passed
		return true;
	}
?>
<?php
	if(is_logging_enabled() === true)
	{
		//Change the timezone
		set_timezone();
		//Logging enabled on system
		write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Visited posting page");
	}
	//Check if the user is banned
	if(isset($_SESSION['uname']) && $_SESSION['uname'] != "")
	{
		$uban=is_user_banned($_SESSION['uname']);
	}
	else
	{
		$uban=array(false);
	}
	if(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] != "")
	{
		$iban=is_ip_banned($_SERVER['REMOTE_ADDR']);
	}
	else
	{
		$iban=array(false);
	}
	
	if($uban === true || $iban === true)
	{
		//User is banned, redirect them back to the main page
		die("<script type=\"text/javascript\">window.location = \"index.php\"</script>");
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
    <title><?php echo $sysname; ?>Music Request System-Make A Request</title>
	<script type="text/javascript" src="backend/jquery.js"></script>
	<script type="text/javascript" src="backend/tablesorter.js"></script>
	<link rel="stylesheet" href="backend/tsstyle/style.css" type="text/css" media="print, projection, screen" />
	<script type="text/javascript">
	$(function() {
		$("#reqtable").tablesorter();
	});
	</script>
    
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
  <h1 style="text-align:center; text-decoration:underline;"><?php echo $sysname; ?>MRS-Make A Request</h1>
  <?php
	//Make sure that searching is allowed
	if(get_system_setting("searching") == "yes")
	{
		$search=true;
	}
	else
	{
		$search=false;
	}
  ?>
  <form action="select.php" method="get">
  <input type="hidden" name="s" value="y">
  Search for a song: <input type="text" name="query" <?php if($search !== true) { echo ("value=\"Searching disabled\" disabled=\"disabled\""); } elseif(isset($_GET['query'])) { echo("value=\"" . $_GET['query'] . "\"");} ?>>
  <input type="submit">
  </form>
  <p><a href="howtosearch.php">How to search</a><br>
  Or, display songs: <a href="select.php?list=main&query=all">ALL</a> | <a href="select.php?list=main&query=new">NEW</a> | <a href="select.php?list=main&query=freq">POPULAR</a> | <a href="select.php?list=main&query=recadd">RECENT ADDITIONS</a> | <a href="select.php?list=main&query=a">A</a> | <a href="select.php?list=main&query=b">B</a> | <a href="select.php?list=main&query=c">C</a> | <a href="select.php?list=main&query=d">D</a> | <a href="select.php?list=main&query=e">E</a> | <a href="select.php?list=main&query=f">F</a> | <a href="select.php?list=main&query=g">G</a> | <a href="select.php?list=main&query=h">H</a> | <a href="select.php?list=main&query=i">I</a> | <a href="select.php?list=main&query=j">J</a> | <a href="select.php?list=main&query=k">K</a> | <a href="select.php?list=main&query=l">L</a> | <a href="select.php?list=main&query=m">M</a> | <a href="select.php?list=main&query=n">N</a> | <a href="select.php?list=main&query=o">O</a> | <a href="select.php?list=main&query=p">P</a> | <a href="select.php?list=main&query=q">Q</a> | <a href="select.php?list=main&query=r">R</a> | <a href="select.php?list=main&query=s">S</a> | <a href="select.php?list=main&query=t">T</a> | <a href="select.php?list=main&query=u">U</a> | <a href="select.php?list=main&query=v">V</a> | <a href="select.php?list=main&query=w">W</a> | <a href="select.php?list=main&query=x">X</a> | <a href="select.php?list=main&query=y">Y</a> | <a href="select.php?list=main&query=z">Z</a> | <a href="select.php?list=main&query=other">Other</a>
  <?php
    if(get_system_setting("christmas") == "yes")
    {
        echo(" | <a href=\"select.php?list=christmas&query=all\">Christmas Music</a>");
    }
    foreach(explode(",",get_system_setting("extlists")) as $extlist)
    {
        $extlist=explode("=",$extlist);
        if(count($extlist) == 2)
        {
            echo(" | <a href=\"select.php?list=" . $extlist[1] . "&query=all\">" . $extlist[0] . "</a>");
        }
    }
  ?>
  <br>
  Or, <?php
		//Make sure that open requests are enabled
		if(is_open_enabled() === true)
		{
			echo ("<a href=\"post.php\">make a request not on this list</a>");
		}
		else
		{
			echo ("<strike>make a request not on this list</strike>");
		}
  ?></p>
  <hr>
  <?php
	/* Path to follow:
	-Get posting status
	-Get number of requests made by user+if they have active request
	-Perform query
	-Display results */
		
	if(is_logging_enabled() === true)
	{
		//Change timezone
		set_timezone();
		//Logging enabled
		$posting="no";
	
		if(isset($_GET['blank']))
		{
			//User submitted blank query, or a query that eventually became blank
			trigger_error("The query you submitted was blank, or contained no usable search terms. Please try again.");
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"User submitted blank search query");
		}
		//Get system state
		$posting=get_system_setting("posting");
		write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Obtained system setting: posting enabled/disabled");
		//Check whether system is in overload mode or not
        if(system_in_overload() === true)
        {
			trigger_error("The system is presently experiencing an overflow in requests. Please try again later.",E_USER_WARNING);
			$posting="no";
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"System in overflow mode");
        }
		//Check if user has a pending request
		if(pendingrequest() === true && get_system_setting("pdreq") == "yes")
		{
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"User has a pending request, and further requests are not permitted");
			trigger_error("You have a presently unplayed/undeclined request. Please wait until this request is played or declined.",E_USER_NOTICE);
			$posting="no";
		}
		//Check if user has hit a lockout point
		if(user_lockout() === true)
		{
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"User has exceeded their post limit");
			trigger_error("You have exceeded your request quota. Try again later.",E_USER_WARNING);
			$posting=false;
		}
		//Convert posting switch to true and false
		if($posting == "yes")
		{
			$posting=true;
		}
		else
		{
			$posting=false;
		}
	}
	else
	{
		//Logging disabled
		$posting="no";
	
		if(isset($_GET['blank']))
		{
			//User submitted blank query, or a query that eventually became blank
			trigger_error("The query you submitted was blank, or contained no usable search terms. Please try again.");
		}
		//Get system state
		$posting=get_system_setting("posting");
		//Check whether system is in overload mode or not
        if(system_in_overload() === true)
        {
			trigger_error("The system is presently experiencing an overflow in requests. Please try again later.",E_USER_WARNING);
			$posting="no";
        }
		//Check if user has a pending request
		if(pendingrequest() === true && get_system_setting("pdreq") == "yes")
		{
			trigger_error("You have a presently unplayed/undeclined request. Please wait until this request is played or declined.",E_USER_NOTICE);
			$posting="no";
		}
		//Check if user has hit a lockout point
		if(user_lockout() === true)
		{
			trigger_error("You have exceeded your request quota. Try again later.",E_USER_WARNING);
			$posting=false;
		}
	}
?>
<?php
	$songs=array();
	if(isset($_GET['query']) && $_GET['query'] != "")
	{
		$query=htmlspecialchars($_GET['query']);
		if($query == "")
		{
			//Query is blank, this is disallowed
			echo("<script type=\"text/javascript\">window.location = \"select.php?blank=yes\"</script>");
		}
		if(isset($_GET['list']) && ($list=preg_replace("/[^A-Za-z0-9]/","",$_GET['list'])) != "")
		{
			if(!file_exists("songs/$list.txt") && $list != "christmas")
			{
				$list="main";
			}
		}
		else
		{
			$list="main";
		}
		
		if($list == "christmas" && is_christmas_allowed() === false)
		{
			$songs=array(array("artist" => "CF","title" => "Christmas music gets played in December, DAMMIT!"));
			$continue=false;
		}
		else
		{
			$continue=true;
		}
		
		if($continue === true)
		{
			$query=explode(",",$query);
			for($i=0;$i<count($query);$i++)
			{
				$query[$i]=trim($query[$i]);
				if(strpos($query[$i],"list=") !== false)
				{
					$q=explode("=",$query[$i]);
					$list=$q[1];
					$query[$i]="";
				}
			}
			$query=array_filter($query);
			$query=implode(",",$query);
			
			if(isset($_GET['s']) && $_GET['s'] == "y")
			{
				$songs=search($list,$query);
                if(get_system_setting("hidenr") >= 1)
                {
                    $hidenr=true;
                }
                else
                {
                    $hidenr=false;
                }
			}
			else
			{
				$songs=query($list,$query);
                if(get_system_setting("hidenr") >= 2)
                {
                    $hidenr=true;
                }
                else
                {
                    $hidenr=false;
                }
			}
		}
		
		$rawformats=explode("|",get_system_setting("songformat"));
		$formats=array();
		foreach($rawformats as $format)
		{
			if(strpos($format,"*") === false)
			{
				$formats[]=$format;
			}
		}
		
		echo("<h3>Songs matching your search terms: <u>" . count($songs) . "</u></h3>");
	}
?>
  <table id="reqtable" class="tablesorter">
  <thead>
  <tr>
  <th style="width:90px;"></th>
  <?php
	//Get user-readable song format
	$humanreadable=explode("|",get_system_setting("songformathr"));
	foreach($humanreadable as $hr)
	{
		//DO NOT OUTPUT IF FILE NAME!
		if(strtolower(preg_replace("/[^A-Za-z]/","",$hr)) != "filename")
		{
			echo ("<th>$hr</th>\r\n");
		}
	}
  ?>
  <th>#</th>
  </tr>
  </thead>
  <tbody>
	<?php
		foreach($songs as $song)
		{
			if($song["artist"] == "CF")
			{
				$count=count($formats);
				echo ("<tr>\r\n<td></td>\r\n<td colspan=" . ($count+1) . ">" . $song["title"] . "</td>\r\n</tr>\r\n");
			}
			else
			{
				if(isset($song[5]) && $song[5] != "")
				{
					$filename=$song[5];
				}
				else
				{
					$filename="";
				}
				if(($posting === true || $posting == "yes") && current_request($song) === false)
				{
					echo ("<tr>\r\n<td>");
					if(($song["added_to_system"] + 7*24*60*60) > time())
					{
						echo ("<img src=\"backend/new.gif\" alt=\"New\">");
					}
					echo ("<a href=\"post.php?list=$list&req=" . $song["ID"] . "\">Request this</a></td>\r\n");
					foreach($formats as $format)
					{
						if(isset($song[$format]))
						{
							echo("<td>" . $song[$format] . "</td>\r\n");
						}
						else
						{
							echo("<td></td>\r\n");
						}
					}
                    echo("<td>" . $song["request_count"] . "</td>\r\n");
					echo("</tr>\r\n");
				}
				elseif($hidenr === false)
				{
					echo ("<tr>\r\n<td>");
					if(($song["added_to_system"] + 7*24*60*60) > time())
					{
						echo ("<img src=\"backend/new.gif\" alt=\"New\">");
					}
					echo ("<strike>Request this</strike></td>\r\n");
					foreach($formats as $format)
					{
						if(isset($song[$format]))
						{
							echo("<td>" . $song[$format] . "</td>\r\n");
						}
						else
						{
							echo("<td></td>\r\n");
						}
					}
                    echo("<td>" . $song["request_count"] . "</td>\r\n");
					echo("</tr>\r\n");
				}
			}
		}
	?>
</tbody>
</table>
  <br><a href="index.php">Cancel</a>
  </body>
</html>