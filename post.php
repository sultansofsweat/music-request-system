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
		echo ("<script type=\"text/javascript\">window.location = \"post2.php\"</script>");
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
	//Function for getting Christmas music
	function christmas()
	{
		//Check if file exists (IE it's freaking December, dammit)
		if(file_exists("backend/christmas.txt"))
		{
			//Get contents of file and split it by line
			$music=explode("\r\n",file_get_contents("backend/christmas.txt"));
			//Return songlist
			return $music;
		}
		else
		{
			//It isn't December yet!
			return array("CF|Christmas music gets played in December, DAMMIT!");
		}
	}
	
	//Function for obtaining a list of songs, sorted based on new songs vs old songs
	function getsongs()
	{
		//Get base and song lists
		$bl=array_filter(get_base_list());
		$sl=array_filter(get_song_list());
		//Select only those songs which are "new" (IE are in the song list but not the base list)
		$usl=array_diff($sl,$bl);
		//Return an array containing the unique "new" songs and the base list
		return array($usl,$bl);
	}
	
	//Function for finding music matching a query
	function findsongs($search,$query)
	{
		if($search === true)
		{
			//Get all songs
			$unsortedsongs=getsongs();
			$songs=array();
			$sl=array();
			//Add a flag to each song indicating whether it is new or not
			foreach($unsortedsongs[0] as $song)
			{
				$songs[]="1|" . $song;
			}
			foreach($unsortedsongs[1] as $song)
			{
				$songs[]="0|" . $song;
			}
			//Get the list of words to strip from the query
			$stripwords=explode("|",get_system_setting("stripwords"));
			//Get the song list format
			$format=explode("|",strtolower(get_system_setting("songformat")));
			//Process the search query
			$processedquery=explode(",",strtolower($query));
			for($i=0;$i<count($processedquery);$i++)
			{
				$processedquery[$i]=trim($processedquery[$i]);
				$processedquery[$i]=explode("=",$processedquery[$i]);
				if(count($processedquery[$i]) < 2)
				{
					$processedquery[$i]=array("any",$processedquery[$i][0]);
				}
				if(count($processedquery[$i]) > 2)
				{
					$processedquery[$i]=array($processedquery[$i][0],$processedquery[$i][1]);
				}
				if(!in_array($processedquery[$i][0],$format) && $processedquery[$i][0] != "any")
				{
					$processedquery[$i]=array();
				}
			}
			//Filter out all the useless crap
			$processedquery=array_filter(array_map('array_filter',$processedquery));
			
			//Strip all words that should not be in a query
			for($i=0;$i<count($processedquery);$i++)
			{
				foreach($stripwords as $word)
				{
					$w1=$word . " ";
					$w2=" " . $word;
					$w3=" " . $word . " ";
					$processedquery[$i][1]=str_replace($w1,"",$processedquery[$i][1]);
					$processedquery[$i][1]=str_replace($w2,"",$processedquery[$i][1]);
					$processedquery[$i][1]=str_replace($w3,"",$processedquery[$i][1]);
				}
			}
			
			//Loop through each query part
			foreach($processedquery as $directive)
			{
				//Reset results list
				$sl=array();
				//Get location within format string
				if($directive[0] != "any")
				{
					$location=array_search($directive[0],$format);
					if($location !== false)
					{
						//Increment (since each song now has a flag at the beginning)
						$location++;
						foreach($songs as $song)
						{
							//Get the contents of the song
							$s2=explode("|",$song);
							//Check if the respective item matches the query
							if(strpos(str_replace(" ","",strtolower($s2[$location])),$directive[1]) !== false)
							{
								if(isset($_GET['mod']) && $_GET['mod'] == "y")
								{
									//Add regardless of whether it is a MOD file or not
									$sl[]=$song;
								}
								elseif(strtolower($s2[3]) != "mod")
								{
									//Not a MOD, add it to list
									$sl[]=$song;
								}
							}
						}
					}
				}
				else
				{
					foreach($songs as $song)
					{
						$s2=str_replace(" ","",strtolower($song));
						//Check if the respective item matches the query
						if(strpos(strtolower($s2),$directive[1]) !== false)
						{
							$s2=explode("|",$s2);
							if(isset($_GET['sm']) && $_GET['sm'] == "y")
							{
								//Add regardless of whether it is a MOD file or not
								$sl[]=$song;
							}
							elseif(strtolower($s2[3]) != "mod")
							{
								//Not a MOD, add it to list
								$sl[]=$song;
							}
						}
					}
				}
				//Reset list of songs
				$songs=$sl;
			}
			
			//Return the matches
			return $sl;
		}
		else
		{
			//Get all music
			if($query == "christmas")
			{
				//Query is for christmas music, return that
				return christmas();
			}
			else
			{
				//Get all songs
				$unsortedsongs=getsongs();
				$songs=array();
				//Add a flag to each song indicating whether it is new or not
				foreach($unsortedsongs[0] as $song)
				{
					$songs[]="1|" . $song;
				}
				foreach($unsortedsongs[1] as $song)
				{
					$songs[]="0|" . $song;
				}
				if($query == "all")
				{
					//Return all music
					return $songs;
				}
				elseif($query == "mod")
				{
					//Return all songs that are MOD files
					$sl=array();
					foreach($songs as $song)
					{
						$s2=explode("|",$song);
						if(strtolower($s2[3]) == "mod")
						{
							$sl[]=$song;
						}
					}
					return $sl;
				}
				elseif($query == "other")
				{
					//Return all songs whose artist has a first letter that is not a letter
					$letters=array_merge(range('A','Z'),range('a','z'));
					$sl=array();
					foreach($songs as $song)
					{
						$s2=explode("|",$song);
						if(!in_array(strtolower(substr($s2[1],0,1)),$letters) && strtolower($s2[3]) != "mod")
						{
							$sl[]=$song;
						}
					}
					return $sl;
				}
				else
				{
					//Return all songs whose artist has a first letter matching the query
					$sl=array();
					foreach($songs as $song)
					{
						$s2=explode("|",$song);
						if(strtolower(substr($s2[1],0,1)) == $query && strtolower($s2[3]) != "mod")
						{
							$sl[]=$song;
						}
					}
					return $sl;
				}
			}
		}
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
	
	if($uban[0] === true || $iban[0] === true)
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
  <form action="post.php" method="get">
  <input type="hidden" name="type" value="search">
  <?php
	//Make sure that searching is allowed
	if(file_exists("backend/searching.txt") && file_get_contents("backend/searching.txt") == "yes")
	{
		$search=true;
	}
	else
	{
		$search=false;
	}
  ?>
  Search for a song: <input type="text" name="query" <?php if($search !== true) { echo ("value=\"Searching disabled\" disabled=\"disabled\""); } elseif(isset($_GET['query'])) { echo("value=\"" . $_GET['query'] . "\"");} ?>> Show me MOD file results: <input type="checkbox" name="sm" value="y" <?php if($search !== true) { echo ("disabled=\"disabled\""); } if(isset($_GET['sm']) && $_GET['sm'] == "y") {echo "checked=\"checked\""; } ?>><input type="submit" value="Search" <?php if($search !== true) { echo ("disabled=\"disabled\""); } ?>><br>
  </form>
  <a href="howtosearch.php">How to search</a><br>
  Or, display songs: <a href="post.php?type=display&query=all">ALL</a> | <a href="post.php?type=display&query=a">A</a> | <a href="post.php?type=display&query=b">B</a> | <a href="post.php?type=display&query=c">C</a> | <a href="post.php?type=display&query=d">D</a> | <a href="post.php?type=display&query=e">E</a> | <a href="post.php?type=display&query=f">F</a> | <a href="post.php?type=display&query=g">G</a> | <a href="post.php?type=display&query=h">H</a> | <a href="post.php?type=display&query=i">I</a> | <a href="post.php?type=display&query=j">J</a> | <a href="post.php?type=display&query=k">K</a> | <a href="post.php?type=display&query=l">L</a> | <a href="post.php?type=display&query=m">M</a> | <a href="post.php?type=display&query=n">N</a> | <a href="post.php?type=display&query=o">O</a> | <a href="post.php?type=display&query=p">P</a> | <a href="post.php?type=display&query=q">Q</a> | <a href="post.php?type=display&query=r">R</a> | <a href="post.php?type=display&query=s">S</a> | <a href="post.php?type=display&query=t">T</a> | <a href="post.php?type=display&query=u">U</a> | <a href="post.php?type=display&query=v">V</a> | <a href="post.php?type=display&query=w">W</a> | <a href="post.php?type=display&query=x">X</a> | <a href="post.php?type=display&query=y">Y</a> | <a href="post.php?type=display&query=z">Z</a> | <a href="post.php?type=display&query=other">Other</a> | <a href="post.php?type=display&query=mod">MOD</a> | <a href="post.php?type=display&query=christmas">Christmas Music</a><br>
  Or, <?php
		//Make sure that open requests are enabled
		if(is_open_enabled() === true)
		{
			echo ("<a href=\"post2.php\">make a request not on this list</a>");
		}
		else
		{
			echo ("<strike>make a request not on this list</strike>");
		}
  ?><br>
  <hr>
  <?php
	//$oeh=set_error_handler("eh");
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
		if(pendingrequest() === true && get_system_setting("pdreq") === true)
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
		if(pendingrequest() === true && get_system_setting("pdreq") === true)
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
  <table id="reqtable" class="tablesorter">
  <thead>
  <tr>
  <th></th>
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
  </tr>
  </thead>
  <tbody>
<?php
	if(isset($_GET['type']) && $_GET['type'] != "")
	{
		//Set up list of songs
		$sl=array();
		if($_GET['type'] == "search")
		{
			//Begin searching
			if(!isset($_GET['query']) || $_GET['query'] == "")
			{
				//Query is blank, this is disallowed
				echo("<script type=\"text/javascript\">window.location = \"post.php?blank=yes\"</script>");
			}
			//Get music matching the query
			$sl=findsongs(true,$_GET['query']);
		}
		else
		{
			//Get music matching the query
			$sl=findsongs(false,$_GET['query']);
		}
		
		//Get format
		$format=explode("|",get_system_setting("songformat"));
		//Get location of filename, if it exissts
		$fnl=array_search("filename",$format);
		//Get count of rows
		if($fnl !== false)
		{
			$count=count($format)-1;
		}
		else
		{
			$count=count($format);
		}
		//Loop through all found songs
		for($i=0;$i<count($sl);$i++)
		{
			//Split the file
			$song=explode("|",$sl[$i]);
			//If Christmas and not december, output that information
			if($song[0] == "CF")
			{
				echo ("<tr>\r\n<td></td>\r\n<td colspan=$count>" . $song[1] . "</td>\r\n</tr>\r\n");
			}
			else
			{
				//Output each song
				$req=$song[1] . "-" . $song[2] . " (from the album " . $song[3] . ", " . $song[4] . ")";
				if(isset($song[5]) && $song[5] != "")
				{
					$filename=$song[5];
				}
				else
				{
					$filename="";
				}
				if(($posting === true || $posting == "yes") && currentrequest($req) === false)
				{
					echo ("<tr>\r\n<td>");
					switch($song[0])
					{
						case 1:
						echo ("<img src=\"backend/new.gif\" alt=\"New\">");
						break;
					}
					echo ("<a href=\"post2.php?req=" . stripcslashes($req) . "&filename=" . $filename . "\">Request this</a></td>\r\n");
					for($j=1;$j<count($song);$j++)
					{
						if($j < 5)
						{
							echo ("<td>" . $song[$j] . "</td>\r\n");
						}
					}
					for($j=count($song);$j<$count;$j++)
					{
						echo ("<td></td>\r\n");
					}
					echo("</tr>\r\n");
				}
				else
				{
					echo ("<tr>\r\n<td>");
					switch($song[0])
					{
						case 1:
						echo ("<img src=\"backend/new.gif\" alt=\"New\">");
						break;
					}
					echo ("<strike>Request this</strike></td>\r\n");
					for($j=1;$j<count($song);$j++)
					{
						if($j < 5)
						{
							echo ("<td>" . $song[$j] . "</td>\r\n");
						}
					}
					for($j=count($song);$j<$count;$j++)
					{
						echo ("<td></td>\r\n");
					}
					echo("</tr>\r\n");
				}
			}
		}
	}
?>
</tbody>
</table>
  <br><a href="index.php">Cancel</a>
  </body>
</html>