<?php
	//This file contains all functions used by multiple pages on the MRS
	
	//Include password file (used to shim non-compliant PHP versions, compliant versions should ignore it with no changes)
	include(dirname(__FILE__) . "/password.php");
		
	//Function for getting the song list
	function get_song_list()
	{
		if(file_exists("backend/songlist.txt") && file_get_contents("backend/songlist.txt") != "")
		{
			return explode("\r\n",file_get_contents("backend/songlist.txt"));
		}
		return array();
	}
	//Function for getting the base song list
	function get_base_list()
	{
		if(file_exists("backend/baselist.txt") && file_get_contents("backend/baselist.txt") != "")
		{
			return explode("\r\n",file_get_contents("backend/baselist.txt"));
		}
		return array();
	}
	//Function for writing log message to system log
	function write_log($ip,$time,$message)
	{
		if(file_exists("log"))
		{
			$fh=fopen("log/" . date("Ymd") . ".txt",'a') or die("Failed to open file \"log/" . date("Ymd") . ".txt\" in append mode. It should now be microwaved.");
		}
		else
		{
			$fh=fopen("../log/" . date("Ymd") . ".txt",'a') or die("Failed to open file \"log/" . date("Ymd") . ".txt\" in append mode. It should now be microwaved.");
		}
		fwrite($fh,$ip . " at " . $time . ": " . stripcslashes($message) . "\r\n");
		fclose($fh);
	}
	//Function for getting alternative session store information
	function alt_ses_store()
	{
		if(file_exists("backend/altsesstore.txt") && file_get_contents("backend/altsesstore.txt") == "yes" && file_exists("backend/altsesstorepath.txt") && file_exists(file_get_contents("backend/altsesstorepath.txt")) && is_readable(file_get_contents("backend/altsesstorepath.txt")) && is_writable(file_get_contents("backend/altsesstorepath.txt")))
		{
			return file_get_contents("backend/altsesstorepath.txt");
		}
		return false;
	}
	//Function for retrieving system name
	function system_name()
	{
		if(file_exists("backend/name.txt") && file_get_contents("backend/name.txt") != "")
		{
			return file_get_contents("backend/name.txt") . " ";
		}
		return "";
	}
	//Function for getting posting status
	function is_system_enabled()
	{
		if(file_exists("backend/posting.txt") && file_get_contents("backend/posting.txt") == "yes")
		{
			return true;
		}
		return false;
	}
	//Function for getting logging status
	function is_logging_enabled()
	{
		if(file_exists("backend/logging.txt") && file_get_contents("backend/logging.txt") == "yes")
		{
			return true;
		}
		return false;
	}
	//Function for getting system overload level
	function get_system_overload()
	{
		if(file_exists("backend/limit.txt") && file_get_contents("backend/limit.txt") != "")
		{
			return file_get_contents("backend/limit.txt");
		}
		return 0;
	}
	function system_in_overflow()
	{
		return system_in_overload();
	}
	//Function for determining if the system is in overload mode
	function system_in_overload()
	{
		//Get the overload level
		$olevel=get_system_overload();
		if($olevel > 0)
		{
			//System has an overload point, get number of open requests
			$openreqs=get_open_reqs();
			if($openreqs >= $olevel)
			{
				//System is in overload mode
				return true;
			}
		}
		return false;
	}
	//Function for getting the system IP banlist
	function get_IP_banlist()
	{
		return get_all_ip_bans();
	}
	//Function for getting the system username banlist
	function get_uname_banlist()
	{
		return get_all_user_bans();
	}
	function get_all_reqs()
	{
		return get_all_requests();
	}
	//Function for getting all requests
	function get_all_requests()
	{
		return glob("posts/*.txt");
	}
	//Function for getting all requests, formatted as files
	function get_all_reqs_as_files()
	{
		return get_all_requests();
	}
	//Function for getting a specific request
	function get_request($id)
	{
		if(!file_exists("posts/" . $id . ".txt"))
		{
			trigger_error("ID passed to get_request does not correspond to a valid request",E_USER_WARNING);
			return array(0,"Error","127.0.0.1","01/01/1970 12:00 AM","This request could not be displayed due to an internal error",3,"","Please microwave the system.","");
		}
		return get_post_contents("posts/" . $id . ".txt");
	}
	//Function for getting post contents (post contents format: [id,name,ip,date,request,status,admincomment,usercomment,filename])
	function get_post_contents($post)
	{
		//Set up initial format
		$content=array(0,"Error","127.0.0.1","01/01/1970 12:00 AM","This request could not be displayed due to an internal error",3,"","Please microwave the system.","");
		if(!file_exists($post))
		{
			//Could not read post, throw error
			trigger_error("File passed to get_post_contents does not exist",E_USER_WARNING);
		}
		else
		{
			//Read and split file
			$contents=explode("\r\n",base64_decode(file_get_contents($post)));
			$contents[5]=explode("|",$contents[5]);
			//Insert contents into formatted array
			$content[0]=$contents[0];
			$content[1]=$contents[1];
			$content[2]=$contents[2];
			$content[3]=$contents[3];
			$content[4]=$contents[4];
			$content[5]=$contents[5][0];
			$content[6]=$contents[5][1];
			if(isset($contents[6]))
			{
				$content[7]=$contents[6];
			}
			else
			{
				$content[7]="None";
			}
			if(isset($contents[7]))
			{
				$content[8]=$contents[7];
			}
			else
			{
				$content[8]="";
			}
		}
		//Return the formatted array
		return $content;
	}
	//Function for getting requests of a specific status
	function get_specific_reqs($reqlvl)
	{
		//If requests levels is not an array, make it an array of just the request level
		if(!is_array($reqlvl))
		{
			$reqlvl=array($reqlvl);
		}
		$count=0;
		//Get all posts
		$posts=get_all_requests();
		//Loop through posts
		foreach($posts as $post)
		{
			//Get contents
			$content=get_post_contents($post);
			//If post is either unseen or in queue, increment counter
			if(in_array($content[5],$reqlvl))
			{
				$count++;
			}
		}
		//Return the counter
		return $count;
	}
	//Function for getting requests of a specific status, and returning a list of them
	function get_specific_reqs_return($reqlvl)
	{
		//If requests levels is not an array, make it an array of just the request level
		if(!is_array($reqlvl))
		{
			$reqlvl=array($reqlvl);
		}
		$reqs=array();
		//Get all posts
		$posts=get_all_requests();
		//Loop through posts
		foreach($posts as $post)
		{
			//Get contents
			$content=get_post_contents($post);
			//If post is either unseen or in queue, add the filename to the list
			if(in_array($content[5],$reqlvl))
			{
				$reqs[]=$post;
			}
		}
		//Return the list
		return $reqs;
	}
	//Function for getting all open requests
	function get_open_reqs()
	{
		return get_specific_reqs(array(0,2));
	}
	//Function for setting the system timezone
	function set_timezone()
	{
		if(file_exists("backend/timezone.txt"))
		{
			date_default_timezone_set(file_get_contents("backend/timezone.txt"));
		}
		else
		{
			date_default_timezone_set("America/Toronto");
		}
	}
	//Function to check if post exists
	function does_post_exist($id)
	{
		if(file_exists("posts/$id.txt"))
		{
			return true;
		}
		return false;
	}
	//Function for writing a request
	function write_request($id,$poster,$ip,$date,$request,$status,$response,$comment,$filename)
	{
		if(func_num_args() < 9)
		{
			trigger_error("Invalid call to function write_request (8 parameters passed instead of 9)",E_USER_ERROR);
			return false;
		}
		$content="$id\r\n$poster\r\n$ip\r\n$date\r\n" . stripcslashes($request) . "\r\n$status|" . stripcslashes($response) . "\r\n" . stripcslashes($comment) . "\r\n$filename\r\n";
		$fh=fopen("posts/$id.txt",'w');
		if(!$fh)
		{
			trigger_error("Could not open post $id for writing",E_USER_WARNING);
			return false;
		}
		fwrite($fh,base64_encode($content));
		fclose($fh);
		return true;
	}
	//Function for getting a system setting
	function get_system_setting($setting)
	{
		//If setting file doesn't exist, return the system default
		if(!file_exists("backend/" . $setting . ".txt"))
		{
			return get_system_default($setting);
		}
		return file_get_contents("backend/" . $setting . ".txt");
	}
	//Define an HTTP response code changing function if it does not exist
	if (!function_exists('http_response_code'))
	{
		function http_response_code($newcode = NULL)
		{
			static $code = 200;
			if($newcode !== NULL)
			{
				header('X-PHP-Response-Code: '.$newcode, true, $newcode);
				if(!headers_sent())
					$code = $newcode;
			}       
			return $code;
		}
	}
	
	//Function for getting the RSS feed
	function get_rss_feed()
	{
		if(file_exists("rss/requests.xml"))
		{
			return file_get_contents("rss/requests.xml");
		}
		return false;
	}
	//Function for appending to RSS feed
	function add_to_feed($xml)
	{
		if(strpos($xml,"<!-- insert here -->") === false)
		{
			return false;
		}
		$xmlc=file_get_contents("rss/requests.xml");
		$xmlc=str_replace("<!-- insert here -->",$xml,$xmlc);
		$fh=fopen("rss/requests.xml",'w');
		if($fh)
		{
			fwrite($fh,$xmlc);
			fclose($fh);
			return true;
		}
		else
		{
			trigger_error("Failed to open XML file \"rss/requests.xml\". It or the containing folder should now be microwaved.",E_USER_ERROR);
			return false;
		}
	}
	
	
	//Function for checking if the song has been requested already
	function currentrequest($song)
	{
		//Get list of all requests
		$files=get_all_reqs();
		//Get post restriction time, which will serve as a baseline for how long to remember requests
		switch(get_system_setting("type"))
		{
			case 0:
			$keeptime=1*60*60;
			break;
			case 2:
			$keeptime=24*60*60;
			break;
			case 1:
			default:
			$keeptime=3*60*60;
			break;
		}
		foreach($files as $file)
		{
			//Decode and split the file
			$contents=get_post_contents($file);
			if($contents[4] == $song && time() < strtotime($contents[3]) + $keeptime)
			{
				//Song has been requested already
				return true;
			}
		}
		//Song has not been requested already
		return false;
	}
	//Function for checking if the user has a pending request
	function pendingrequest()
	{
		//Get user's IP address
		$username=$_SERVER['REMOTE_ADDR'];
		
		//Get list of all requests
		$files=get_all_reqs();
		foreach($files as $file)
		{
			//Decode and splut the file
			$contents=get_post_contents($file);
			if($contents[2] == $username)
			{
				//Check the status of the request
				$status=explode("|",$contents[5]);
				if($status[0] == 0 || $status[0] == 2)
				{
					//User has a currently active request in the system
					return true;
				}
			}
		}
		//User does not have a currently active request in the system
		return false;
	}
	//Function for checking the count of songs the user has requested already.
	function countrequest($uni,$username,$modifier)
	{
		if(!isset($username) || $username == "")
		{
			//There is no username by which to count requests against
			return 0;
		}
		//Get list of all requests
		$files=get_all_reqs();
		//Set up the counter
		$count=0;
		foreach($files as $file)
		{
			//Get contents of request
			$contents=get_post_contents($file);
			//True for username, false for IP address
			if($uni === true)
			{
				//Get username from contents
				$un=$contents[1];
			}
			else
			{
				//Get IP address from contents
				$un=$contents[2];
			}
			$time=strtotime($contents[3]);
			$mtime=$time + $modifier;
			if($un == $username && time() < $mtime)
			{
				//User has made a request before the expiry time
				$count++;
			}
		}
		//Return the counter
		return $count;
	}
	
	//Function for determining whether or not a user has exceeded their specified limit
	function user_lockout()
	{
		//Set username and IP address
		if(isset($_SESSION['uname']))
		{
			$username=$_SESSION['uname'];
		}
		else
		{
			$username="";
		}
		$ip=$_SERVER['REMOTE_ADDR'];
		//Get request limits
		$limits=array(get_system_setting("unlock"),get_system_setting("iplock"),get_system_setting("dayrestrict"));
		//Get time limit
		switch(get_system_setting("type"))
		{
			case 0:
			$modifier=1*60*60;
			break;
			case 2:
			$modifier=24*60*60;
			break;
			case 1:
			default:
			$modifier=3*60*60;
			break;
		}
		//Get request counts
		if($username != "")
		{
			$uncount=countrequest(true,$username,$modifier);
		}
		else
		{
			$uncount=0;
		}
		if($ip != "")
		{
			$ipcount=countrequest(false,$ip,$modifier);
			$daycount=countrequest(false,$ip,24*60*60);
		}
		else
		{
			$ipcount=0;
			$daycount=0;
		}
		
		if(($uncount > $limits[0] && $limits[0] > 0) || ($ipcount > $limits[1] && $limits[1] > 0) || ($daycount > $limits[2] && $limits[2] > 0))
		{
			//User exceeded limit
			return true;
		}
		//No lockout reached
		return false;
	}
	
	//Function for getting the system post count
	function get_post_count()
	{
		if(file_exists("backend/postid.txt"))
		{
			return file_get_contents("backend/postid.txt");
		}
		return -1;
	}
	//Function for incrementing the post count
	function increment_post_count()
	{
		$pcount=get_post_count();
		$pcount++;
		$fh=fopen("backend/postid.txt",'w');
		if($fh)
		{
			fwrite($fh,$pcount);
			fclose($fh);
			return $pcount;
		}
		else
		{
			trigger_error("Failed to open file \"postid.txt\" in write mode. It should now be microwaved.",E_USER_ERROR);
			return -1;
		}
	}
	function get_system_password()
	{
		if(!file_exists("backend/password.txt"))
		{
			trigger_error("The password file appears to have been abducted by Russians, and the MRS cannot proceed without it",E_USER_ERROR);
			return false;
		}
		return base64_decode(file_get_contents("backend/password.txt"));
	}
	
	//Function for saving a system setting
	function save_system_setting($setting,$value)
	{
		//Open appropriate file
		$fh=fopen("backend/" . $setting . ".txt",'w');
		if(!$fh)
		{
			trigger_error("Failed to save setting " . $setting,E_USER_WARNING);
			return false;
		}
		//Write setting to file
		$debug=fwrite($fh,$value);
		if($debug === false || $debug != strlen("$value"))
		{
			trigger_error("Failed to write setting " . $setting . " to system",E_USER_WARNING);
			return false;
		}
		//Close file
		$debug=fclose($fh);
		if($debug === false)
		{
			trigger_error("Failed to close setting file for setting " . $setting,E_USER_WARNING);
			return false;
		}
		return true;
	}
	
	function get_system_default($setting)
	{
		$defaults=array("anon" => "no",
					"dayrestrict" => 10,
					"eroc" => "no",
					"iplock" => 5,
					"limit" => 0,
					"logging" => "no",
					"name" => "",
					"open" => "no",
					"pdreq" => "no",
					"posting" => "no",
					"searching" => "yes",
					"status" => "no",
					"stripwords" => "",
					"timezone" => "America/Toronto",
					"type" => "0",
					"unlock" => 2,
					"stable" => "yes",
					"security" => 7,
					"timeout" => 20,
					"postexpiry" => 10800,
					"light" => "no",
					"altsesstore" => "no",
					"altsesstorepath" => "",
					"autorefresh" => 0,
					"sysmessage" => "",
					"comments" => "no",
					"viewcomments" => "no",
					"interface" => "no",
					"autokey" => "",
					"songformat" => "artist|title|album|year",
					"songformathr" => "Artist|Title|Album|Year",
					"sysid" => "",
					"errlvl" => 1,
					"blanking" => "yes",
					"logerr" => "no");
		return $defaults[$setting];
	}
	function get_songs_formatted()
	{
		$songs=get_song_list();
		for($i=0;$i<count($songs);$i++)
		{
			$songs[$i]=$songs[$i][0] . "-" . $songs[$i][1] . " (From the album " . $songs[$i][2] . ", " . $songs[$i][3] . ")";
		}
		return $songs;
	}
	
	//Functions for sorting requests
	function sort_reqs_asc($a,$b)
	{
		if(file_exists($a) && file_exists($b))
		{
			$acontents=get_post_contents($a);
			$bcontents=get_post_contents($b);
			if(strtotime($acontents[3]) < strtotime($bcontents[3]))
			{
				return -1;
			}
			elseif(strtotime($acontents[3]) > strtotime($bcontents[3]))
			{
				return 1;
			}
			else
			{
				return 0;
			}
		}
		return 0;
	}
	function sort_reqs_desc($a,$b)
	{
		if(file_exists($a) && file_exists($b))
		{
			$acontents=get_post_contents($a);
			$bcontents=get_post_contents($b);
			if(strtotime($acontents[3]) > strtotime($bcontents[3]))
			{
				return -1;
			}
			elseif(strtotime($acontents[3]) < strtotime($bcontents[3]))
			{
				return 1;
			}
			else
			{
				return 0;
			}
		}
		return 0;
	}
	
	//Function for writing an archive
	function write_archive($id)
	{
		$contents=implode("\r\n",get_request($id));
		$fh=fopen("archive/$id.txt",'w');
		if($fh)
		{
			fwrite($fh,$contents);
			fclose($fh);
			return true;
		}
		return false;
	}
	//Function for getting all request ids
	function get_all_req_ids()
	{
		$ids=array();
		$files=glob("posts/*.txt");
		foreach($files as $file)
		{
			$ids[]=substr($file,6,-4);
		}
		return $ids;
	}
	
	//Banning functions
	function ban_user($username,$reason="")
	{
		$fh=fopen("ban/uname.txt",'a');
		if($fh)
		{
			fwrite($fh,$username . "\r\n");
			fclose($fh);
			return true;
		}
		else
		{
			return false;
		}
	}
	function ban_ip($ip,$reason="")
	{
		$fh=fopen("ban/ip.txt",'a');
		if($fh)
		{
			fwrite($fh,$ip . "\r\n");
			fclose($fh);
			return true;
		}
		else
		{
			return false;
		}
	}
	function unban_user($username)
	{
		$bans=get_all_user_bans();
		$remove=array(strtolower($username));
		if(in_array($remove[0],$bans))
		{
			$bans=array_diff($bans,$remove);
			$bans=implode("\r\n",$bans);
			$fh=fopen("ban/uname.txt",'w');
			if($fh)
			{
				fwrite($fh,$bans);
				fclose($fh);
				return 0;
			}
			else
			{
				return 1;
			}
		}
		else
		{
			return 2;
		}
	}
	function unban_ip($ip)
	{
		$bans=get_all_ip_bans();
		$remove=array($ip);
		if(in_array($remove[0],$bans))
		{
			$bans=array_diff($bans,$remove);
			$bans=implode("\r\n",$bans);
			$fh=fopen("ban/ip.txt",'w');
			if($fh)
			{
				fwrite($fh,$bans);
				fclose($fh);
				return 0;
			}
			else
			{
				return 1;
			}
		}
		else
		{
			return 2;
		}
	}
	
	function is_user_banned($username)
	{
		$result=array(false,"");
		$bans=get_all_user_bans();
		if(in_array(strtolower($username),$bans))
		{
			$result[0]=true;
		}
		return $result;
	}
	function is_ip_banned($ip)
	{
		$result=array(false,"");
		$bans=get_all_ip_bans();
		if(in_array($ip,$bans))
		{
			$result[0]=true;
		}
		return $result;
	}
	
	function get_all_user_bans()
	{
		if(file_exists("ban/uname.txt"))
		{
			$contents=explode("\r\n",file_get_contents("ban/uname.txt"));
			return array_filter(array_map("strtolower",$contents));
		}
		else
		{
			trigger_error("Username ban list doesn't exist",E_USER_WARNING);
			return array();
		}
	}
	function get_all_ip_bans()
	{
		if(file_exists("ban/ip.txt"))
		{
			$contents=explode("\r\n",file_get_contents("ban/ip.txt"));
			return array_filter($contents);
		}
		else
		{
			trigger_error("IP address ban list doesn't exist",E_USER_WARNING);
			return array();
		}
	}
	
	function delete_all_posts()
	{
		$results=array(0,0,0);
		$posts=glob("posts/*.txt");
		if(count($posts) > 0)
		{
			foreach($posts as $post)
			{
				$results[1]++;
				$debug=unlink($post);
				if($debug === true)
				{
					$results[0]++;
				}
				else
				{
					$results[2]++;
				}
			}
		}
		return $results;
	}
	
	function delete_post($post)
	{
		if(file_exists("posts/$post.txt"))
		{
			$debug=unlink("posts/$post.txt");
		}
		else
		{
			trigger_error("Post \"$post\" doesn't exist",E_USER_WARNING);
			$debug=false;
		}
		return $debug;
	}
	
	function get_raw_song_list()
	{
		if(file_exists("backend/songlist.txt"))
		{
			return file_get_contents("backend/songlist.txt");
		}
		else
		{
			trigger_error("Song list doesn't exist",E_USER_WARNING);
			return "";
		}
	}
	function save_song_list($list)
	{
		$fh=fopen("backend/songlist.txt",'w');
		if($fh)
		{
			fwrite($fh,$list);
			fclose($fh);
			return true;
		}
		else
		{
			return false;
		}
	}
	function append_to_song_list($list)
	{
		$existing=get_raw_song_list();
		$existing .= $list;
		return save_song_list($existing);
	}
	
	function get_reports()
	{
		$reports=array();
		$files=glob("reports/*.txt");
		foreach($files as $file)
		{
			$report=array(0,"SystemHadOneJob",date("m/d/Y g:i A",0),"Internal error occurred. Defenestrate your modem.","0.0.0.0",date("m/d/Y g:i A",0),"And probably call the station manager too.","0-0");
			$contents=explode("\r\n",base64_decode(file_get_contents($file)));
			$contents[]=substr($file,8,-4);
			if(count($contents) < 8)
			{
				trigger_error("Report $file is of an invalid format. The nasal demons have been summoned.",E_USER_WARNING);
			}
			else
			{
				$report=$contents;
			}
			$reports[]=$report;
		}
		return $reports;
	}
	function remove_report($id)
	{
		if(file_exists("reports/$id.txt"))
		{
			return unlink("reports/$id.txt");
		}
		else
		{
			trigger_error("File $id.txt doesn't exist.",E_USER_WARNING);
			return false;
		}
	}
	function write_report($postid,$poster,$postdate,$contents,$reporter,$reportdate,$comment)
	{
		foreach(func_get_args() as $arg)
		{
			if($arg == "")
			{
				trigger_error("Invalid call to function write_report: empty parameter found.",E_USER_ERROR);
				return false;
			}
		}
		$contents=base64_encode(implode("\r\n",array($postid,$poster,$postdate,$contents,$reporter,$reportdate,$comment)));
		$i=0;
		while(file_exists("reports/$postid-$i.txt"))
		{
			$i++;
		}
		$fh=fopen("reports/$postid-$i.txt",'w');
		if($fh)
		{
			fwrite($fh,$contents);
			fclose($fh);
			return true;
		}
		else
		{
			return false;
		}
	}
	
	function get_all_error_logs()
	{
		$logs1=glob("error/*.txt");
		$logs=array();
		if(count($logs1) >= 1)
		{
			foreach($logs1 as $log)
			{
				$logs[]=substr($log,6,-4);
			}
		}
		return $logs;
	}
	function get_error_log($log)
	{
		if(file_exists("error/$log.txt"))
		{
			return file_get_contents("error/$log.txt");
		}
		else
		{
			trigger_error("File \"$log\" doesn't exist in error log directory.",E_USER_WARNING);
			return "";
		}
	}
	
	function get_all_logs()
	{
		$logs1=glob("log/*.txt");
		$logs=array();
		if(count($logs1) >= 1)
		{
			foreach($logs1 as $log)
			{
				$logs[]=substr($log,4,-4);
			}
		}
		return $logs;
	}
	function get_log($log)
	{
		if(file_exists("log/$log.txt"))
		{
			return file_get_contents("log/$log.txt");
		}
		else
		{
			trigger_error("File \"$log\" doesn't exist in system log directory.",E_USER_WARNING);
			return "";
		}
	}
	
	function save_rules($rules)
	{
		$fh=fopen("backend/rules.txt",'w');
		if($fh)
		{
			fwrite($fh,stripcslashes($rules));
			fclose($fh);
			return true;
		}
		else
		{
			trigger_error("Unable to open rule list in write mode. The file should be microwaved.", E_USER_WARNING);
			return false;
		}
	}
	function get_rules()
	{
		if(file_exists("backend/rules.txt"))
		{
			return explode("\r\n",file_get_contents("backend/rules.txt"));
		}
		else
		{
			trigger_error("Unable to open rule list in read mode. The file should be microwaved.", E_USER_WARNING);
			return array("There are no specific rules set in the system at present.");
		}
	}
?>