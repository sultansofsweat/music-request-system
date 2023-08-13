<?php
	//This file contains all functions used by multiple pages on the MRS
	
	//Include password file (used to shim non-compliant PHP versions, compliant versions should ignore it with no changes)
	include(dirname(__FILE__) . "/password.php");
	
	//Function for writing log message to system log
	function write_log($ip,$time,$message)
	{
		if(get_system_setting("logging") == "yes")
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
	}
	//Function for getting alternative session store information
	function alt_ses_store()
	{
		if(get_system_setting("altsesstore") == "yes" && file_exists(get_system_setting("altsesstorepath")) && is_readable(get_system_setting("altsesstorepath")) && is_writable(get_system_setting("altsesstorepath")))
		{
			return get_system_setting("altsesstorepath");
		}
		return false;
	}
	//Function for retrieving system name
	function system_name()
	{
		if(get_system_setting("name") != "")
		{
			return get_system_setting("name") . " ";
		}
		return "";
	}
	//Function for getting posting status
	function is_system_enabled()
	{
		if(get_system_setting("posting") == "yes")
		{
			return true;
		}
		return false;
	}
	//Function for getting logging status
	function is_logging_enabled()
	{
		if(get_system_setting("logging") == "yes")
		{
			return true;
		}
		return false;
	}
	//Function for getting system overload level
	function get_system_overload()
	{
		return get_system_setting("limit");
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
	//Function for getting all requests in the system
	function get_requests()
	{
		$requests=array();
		foreach(get_all_req_ids() as $id)
		{
			$requests[]=get_request($id);
		}
		return $requests;
	}
	//Function for getting a specific request
	function get_request($id)
	{
		//Set up post details array
		//FORMAT: ID|Poster|IP|Date|Request|Status|Comment|Response|Filename (deprecated and never set but included for compatibility reasons)
		$content=array(-1,"Error","127.0.0.1","01/01/1970 12:00 AM","This request could not be displayed due to an internal error",3,"","Please microwave the system.","");
		if(!file_exists("posts/$id.txt"))
		{
			//File doesn't exist
			trigger_error("request_id passed to get_request(request_id) does not correspond to a valid request",E_USER_WARNING);
		}
		else
		{
			//Read and split file
			$contents=explode("\r\n",base64_decode(file_get_contents("posts/$id.txt")));
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
		return count(get_specific_reqs_return($reqlvl));
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
		$posts=array();
		//Loop through supplied request levels
		foreach($reqlvl as $lvl)
		{
			$ids=get_req_db($lvl);
			foreach($ids as $id)
			{
				$posts[]=get_request($id);
			}
		}
		//Get all posts
		//Loop through posts
		foreach($posts as $post)
		{
			//If post is either unseen or in queue, add the filename to the list
			if(in_array($post[5],$reqlvl))
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
	function write_request($id,$poster,$ip,$date,$request,$status,$response,$comment)
	{
		/*if(func_num_args() < 9)
		{
			trigger_error("Invalid call to function write_request(id,poster,ip,date,request,status,response,comment,filename): too few arguments passed.",E_USER_ERROR);
			return false;
		}*/
		$content="$id\r\n$poster\r\n$ip\r\n$date\r\n" . stripcslashes($request) . "\r\n$status|" . stripcslashes($response) . "\r\n" . stripcslashes($comment) . "\r\n";
		$fh=fopen("posts/$id.txt",'w');
		if(!$fh)
		{
			trigger_error("Could not open post $id for writing",E_USER_WARNING);
			return false;
		}
		fwrite($fh,base64_encode($content));
		fclose($fh);
		write_req_db($status,$id);
		write_date_db(false,$id,strtotime($date));
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
	//Function for getting a system setting without getting the default
	function get_system_setting_no_default()
	{
		//If setting file doesn't exist, return the system default
		if(file_exists("backend/$setting.txt"))
		{
			return file_get_contents("backend/$setting.txt");
		}
		return "";
	}
	//Function for checking if a system setting is contained within a file
	function does_setting_exist($setting)
	{
		if(file_exists("backend/$setting.txt"))
		{
			return true;
		}
		return false;
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
	
	//Function for checking if the song has been requested already
	function current_request($song)
	{
        if(isset($song["last_requested"]) && $song["last_requested"]+get_system_setting("postexpiry") > time())
        {
            return true;
        }
		return false;
	}
	//Function for checking if the user has a pending request
	function pendingrequest()
	{
		if(get_system_setting("pdreq") == "no")
		{
			//Option isn't enabled in the system
			return false;
		}
		//Get user's IP address
		$username=$_SERVER['REMOTE_ADDR'];
		
		//Get all open requests
		//$openids=get_open_req_db();
		$openids=array_merge(get_req_db(0),get_req_db(2));
		foreach($openids as $id)
		{
			//Get request info
			$req=get_request($id);
			//Check username
			if($req[2] == $username)
			{
				//User has an open request
				return true;
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
		//$files=get_requests();
		//Get list of requests that are "fresh"
		$files=array_keys(get_recent_date_db($modifier));
		//Set up the counter
		$count=0;
		foreach($files as $file)
		{
			//Get request information
			$contents=get_request($file);
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
			/*$time=strtotime($file[3]);
			$mtime=$time + $modifier;
			if($un == $username && time() < $mtime)*/
			if($un == $username)
			{
				//User has made a request before the expiry time
				$count++;
			}
		}
		//Return the counter
		return $count;
	}
	//Similar function to countrequest, but counts for both the IP and the username.
	function countrequestboth($username,$ip,$modifier)
	{
		if(!isset($username) || $username == "")
		{
			//There is no username by which to count requests against
			return countrequest(false,$ip,$modifier);
		}
		elseif(!isset($ip) || $ip == "")
		{
			//There is no IP address by which to count requests against
			return countrequest(true,$username,$modifier);
		}
		//Get list of requests that are "fresh"
		$files=array_keys(get_recent_date_db($modifier));
		//Set up the counter
		$count=0;
		foreach($files as $file)
		{
			//Get request information
			$contents=get_request($file);
			//Get username and IP address
			$uname=$contents[1];
			$ipa=$contents[2];
			if($un == $username || $ipa == $ip)
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
			//$daycount=countrequest(false,$ip,24*60*60);
		}
		else
		{
			$ipcount=0;
			//$daycount=0;
		}
		switch(get_system_setting("ipundlimit"))
		{
			case 0:
			if($username != "")
			{
				$daycount=countrequest(true,$username,24*60*60);
			}
			else
			{
				$daycount=0;
			}
			break;
			case 2:
			if($username != "" && $ip != "")
			{
				$daycount=countrequestboth($username,$ip,24*60*60);
			}
			elseif($ip == "" && $username != "")
			{
				$daycount=countrequest(true,$username,24*60*60);
			}
			elseif($username == "" && $ip != "")
			{
				$daycount=countrequest(false,$ip,24*60*60);
			}
			else
			{
				$daycount=0;
			}
			break;
			case 1:
			default:
			if($ip != "")
			{
				$daycount=countrequest(false,$ip,24*60*60);
			}
			else
			{
				$daycount=0;
			}
			break;
		}
		
		if(($uncount >= $limits[0] && $limits[0] > 0) || ($ipcount >= $limits[1] && $limits[1] > 0) || ($daycount >= $limits[2] && $limits[2] > 0))
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
					"songformat" => "artist|title|album|year",
					"songformathr" => "Artist|Title|Album|Year",
					"sysid" => "",
					"errlvl" => 1,
					"blanking" => "yes",
					"logerr" => "no",
					"datetime" => "m/d/Y g:i A",
					"popular" => 5,
					"recent" => 5,
					"timelimit" => 30,
					"extlists" => "",
					"rss" => "no",
					"christmas" => "yes",
					"hidenr" => 0,
					"autoban" => "no",
					"banwords" => "",
					"partial" => "no",
					"beforeban" => 3,
					"apipages" => "0,1,2,3",
					"logatt" => "yes",
                    "banfail" => 0,
                    "passreq" => "no",
                    "baninvpass" => "yes",
					"autoopen" => "no",
					"mirror" => "http://firealarms.mooo.com/mrs/",
					"ipundlimit" => 1,
					"theme" => 0,
					"autokey" => "");
		if($setting == "RETURN_ALL")
		{
			return array_keys($defaults);
		}
		return $defaults[$setting];
	}
	
	//Functions for sorting requests
	function sort_reqs_asc($a,$b)
	{
        if(strtotime($a[3]) < strtotime($b[3]))
        {
            return -1;
        }
        elseif(strtotime($a[3]) > strtotime($b[3]))
        {
            return 1;
        }
        else
        {
            return 0;
        }
	}
	function sort_reqs_desc($a,$b)
	{
        if(strtotime($a[3]) > strtotime($b[3]))
        {
            return -1;
        }
        elseif(strtotime($a[3]) < strtotime($b[3]))
        {
            return 1;
        }
        else
        {
            return 0;
        }
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
			fwrite($fh,$username . "|" . $reason . "\r\n");
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
			fwrite($fh,$ip . "|" . $reason . "\r\n");
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
		$remove=strtolower($username);
		$found=false;
		for($i=0;$i<count($bans);$i++)
		{
			if($bans[$i][0] == $remove)
			{
				$bans[$i]="";
				$found=true;
			}
		}
		$bans=array_filter($bans);
		for($i=0;$i<count($bans);$i++)
		{
			$bans[$i]=implode("|",$bans[$i]);
		}
		$bans=implode("\r\n",$bans);
		if($found === false)
		{
			return 2;
		}
		$fh=fopen("ban/uname.txt",'w');
		if($fh)
		{
			fwrite($fh,$bans);
			fclose($fh);
			return 0;
		}
		return 1;
	}
	function unban_ip($ip)
	{
		$bans=get_all_ip_bans();
		$remove=$ip;
		$found=false;
		for($i=0;$i<count($bans);$i++)
		{
			if($bans[$i][0] == $remove)
			{
				$bans[$i]="";
				$found=true;
			}
		}
		$bans=array_filter($bans);
		for($i=0;$i<count($bans);$i++)
		{
			$bans[$i]=implode("|",$bans[$i]);
		}
		$bans=implode("\r\n",$bans);
		if($found === false)
		{
			return 2;
		}
		$fh=fopen("ban/ip.txt",'w');
		if($fh)
		{
			fwrite($fh,$bans);
			fclose($fh);
			return 0;
		}
		return 1;
	}
	
	function is_user_banned($username)
	{
		$bans=get_all_user_bans();
		foreach($bans as $ban)
		{
			if($ban[0] == strtolower($username))
			{
				unset($_SESSION['sradmin']);
				return true;
			}
		}
		return false;
	}
	function is_ip_banned($ip)
	{
		$bans=get_all_ip_bans();
		foreach($bans as $ban)
		{
			if($ban[0] == $ip)
			{
				unset($_SESSION['sradmin']);
				return true;
			}
		}
		return false;
	}
	
	function get_all_user_bans()
	{
		if(file_exists("ban/uname.txt"))
		{
			$contents=array_filter(explode("\r\n",file_get_contents("ban/uname.txt")));
			for($i=0;$i<count($contents);$i++)
			{
				$contents[$i]=explode("|",$contents[$i]);
				if(!isset($contents[$i][1]))
				{
					$contents[$i][1]="";
				}
				$contents[$i][0]=strtolower($contents[$i][0]);
			}
			return $contents;
		}
		else
		{
			trigger_error("Username ban list doesn't exist, assuming empty",E_USER_NOTICE);
			return array();
		}
	}
	function get_all_ip_bans()
	{
		if(file_exists("ban/ip.txt"))
		{
			$contents=array_filter(explode("\r\n",file_get_contents("ban/ip.txt")));
			for($i=0;$i<count($contents);$i++)
			{
				$contents[$i]=explode("|",$contents[$i]);
				if(!isset($contents[$i][1]))
				{
					$contents[$i][1]="";
				}
			}
			return $contents;
		}
		else
		{
			trigger_error("IP address ban list doesn't exist, assuming empty",E_USER_NOTICE);
			return array();
		}
	}
	
	function delete_all_posts()
	{
		$results=array(0,0,0);
        $posts=get_all_req_ids();
        if(count($posts) > 0)
        {
            foreach($posts as $post)
            {
                $results[1]++;
                $debug=delete_post($post);
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
            if($debug === true)
            {
                $debug=write_req_db(-1,$post);
				if($debug === true)
				{
					$debug=write_date_db(true,$post);
				}
            }
		}
		else
		{
			trigger_error("Post \"$post\" doesn't exist",E_USER_WARNING);
			$debug=false;
		}
		return $debug;
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
				if(strpos($log,"deprecation") !== false)
				{
					continue;
				}
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
	function get_dep_log()
	{
		if(file_exists("error/deprecation-messages.txt"))
		{
			return get_error_log("deprecation-messages");
		}
		else
		{
			return "";
		}
	}
	function is_dep_log_blank()
	{
		if(file_exists("error/deprecation-messages.txt") && filesize("error/deprecation-messages.txt") > 0)
		{
			return false;
		}
		else
		{
			return true;
		}
	}
	function clear_dep_log()
	{
		$fh=fopen("error/deprecation-messages.txt",'w');
		fclose($fh);
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
	
	//Function for getting all song lists
	function get_song_lists()
	{
		$lists=array();
		$files=glob("songs/*.txt");
		foreach($files as $file)
		{
			$lists[]=str_replace("songs/","",str_replace(".txt","",$file));
		}
		return $lists;
	}
	//Function for getting a song list, unsplit
	function get_raw_songs($listname)
	{
		if(file_exists("songs/$listname.txt"))
		{
			return file_get_contents("songs/$listname.txt");
		}
		else
		{
			trigger_error("The song list $listname doesn't exist. Something requires immediate defenestration.",E_USER_ERROR);
			return "";
		}
	}
	//Function for getting a song list, split and formatted
	function get_songs($listname)
	{
		$songs=array();
		if(file_exists("songs/$listname.txt"))
		{
			$raw=array_filter(explode("\r\n",get_raw_songs($listname)));
			$format=array();
			$rawformat=explode("|",get_system_setting("songformat"));
			if(is_array($raw) && count($raw) > 0)
			{
                for($i=0;$i<count($raw);$i++)
                {
                    $song=array("artist" => "SystemHad","title" => "OneJob","added_to_system" => 0);
                    $rawsong=explode("|",$raw[$i]);
					$mtime=preg_replace("/[^0-9]/","",array_shift($rawsong));
                    $reqcount=preg_replace("/[^0-9]/","",array_shift($rawsong));
                    $lastreq=preg_replace("/[^0-9]/","",array_shift($rawsong));
					for($j=0;$j<count($rawsong);$j++)
					{
                        if(isset($rawformat[$j]))
                        {
                            $song[$rawformat[$j]]=$rawsong[$j];
                        }
					}
					$song["added_to_system"]=$mtime;
                    $song["request_count"]=$reqcount;
                    $song["last_requested"]=$lastreq;
                    $song["ID"]=$i;
					$songs[]=$song;
                }
			}
		}
		else
		{
			trigger_error("Failed to get songs: list $listname doesn't seem to exist. Blow it up with a variac and try again.",E_USER_WARNING);
		}
		return $songs;
	}
	//Function for getting a single song by number
	function get_song($listname,$number)
	{
		$songs=get_songs($listname);
		
		if($number < 0 || $number >= count($songs))
		{
			trigger_error("Could not get song: song ID $number is out of range. Ban the perpetrator and try again.",E_USER_WARNING);
			return array("artist" => "SystemHad","title" => "OneJob","added_to_system" => 0);
		}
		else
		{
			return $songs[$number];
		}
	}
	//Function for getting a single song by number, returning the raw output
	function get_raw_song($listname,$number)
	{
		$songs=explode("\r\n",get_raw_songs($listname));
		
		if($number < 0 || $number >= count($songs))
		{
			trigger_error("Could not get song: song ID $number is out of range. Ban the perpetrator and try again.",E_USER_WARNING);
			return "0|SystemHad|OneJob";
		}
		else
		{
			return $songs[$number];
		}
	}
	//Function for adding to a song list
	function add_to_song_list($listname,$newsongs)
	{
		$newsongs=explode("\r\n",$newsongs);
		for($i=0;$i<count($newsongs);$i++)
		{
			$newsongs[$i]=time() . "|0|0|" . $newsongs[$i];
		}
		$newsongs=implode("\r\n",$newsongs);
		$fh=fopen("songs/$listname.txt",'a');
		if($fh)
		{
			fwrite($fh,"\r\n" . $newsongs);
			fclose($fh);
			return true;
		}
		else
		{
			return false;
		}
	}
	//Function for changing a song in a song list
	function modify_song_list($listname,$number,$newsong)
	{
        $oldsong=get_song($listname,$number);
		$newsong=time() . "|" . $oldsong["request_count"] . "|" . $oldsong["last_requested"] . "|$newsong";
		$songs=explode("\r\n",get_raw_songs($listname));
		if($number < 0 || $number >= count($songs))
		{
			trigger_error("Unable to modify song: song ID $number is out of range. Blow the song list up with dynamite and a laser beam and try again.",E_USER_WARNING);
			return false;
		}
		$songs[$number]=$newsong;
		$songs=implode("\r\n",$songs);
		$fh=fopen("songs/$listname.txt",'w');
		if($fh)
		{
			fwrite($fh,stripcslashes($songs));
			fclose($fh);
			return true;
		}
		else
		{
			return false;
		}
	}
	//Function for removing a song from a song list
	function remove_from_song_list($listname,$numbers)
	{
		$results=array(0,0);
		if(!is_array($numbers))
		{
			trigger_error("Invalid call to function remove_from_song_list(list,positions): positions must be specified as an array, " . gettype($numbers) . " given.",E_USER_ERROR);
			$results[1]=1;
			return $results;
		}
		$songs=explode("\r\n",get_raw_songs($listname));
		foreach($numbers as $number)
		{
			if($number < 0 || $number >= count($songs))
			{
				trigger_error("Unable to delete song: song ID $number is out of range. Blow the song list up with dynamite and a laser beam and try again.",E_USER_WARNING);
				$results[1]++;
			}
			else
			{
				$songs[$number]="";
			}
		}
		$newsongs=array_filter($songs);
		$results[0]=count(array_diff($songs,$newsongs));
		$songs=implode("\r\n",$newsongs);
		$fh=fopen("songs/$listname.txt",'w');
		if($fh)
		{
			fwrite($fh,stripcslashes($songs));
			fclose($fh);
			return $results;
		}
		else
		{
			return array(0,$results[0]);
		}
	}
	
	//Function for counting the number of songs over the entire MRS (i.e. all songs on all song lists)
	function get_song_count()
	{
		$count=0;
		/*$lists=glob("songs/*.txt");
		foreach($lists as $list)
		{
			$count+=count(explode("\r\n",file_get_contents($list)));
		}*/
		$lists=get_song_lists();
		foreach($lists as $list)
		{
			$count+=count(get_songs($list));
		}
		return $count;
	}
	//Function for counting the number of song lists
	function count_song_lists()
	{
		//return count(glob("songs/*.txt"));
		return count(get_song_lists());
	}
	
	function get_system_format()
	{
		$format=array();
		$raw=explode("|",get_system_setting("songformat"));
		$hr=explode("|",get_system_setting("songformathr"));
		$raw=array_values(array_filter($raw,function($element) { if(strpos($element,"*") === false) { return true; } return false; }));
		for($i=0;$i<count($raw);$i++)
		{
			if(strpos($raw[$i],"*") === false)
			{
				if(isset($hr[$i]))
				{
					$format[$raw[$i]]=$hr[$i];
				}
				else
				{
					trigger_error("Matching format string for \"" . $raw[$i] . "\" not found. Expect problems.",E_USER_WARNING);
					$format[$raw[$i]]=$raw[$i];
				}
			}
		}
		return $format;
	}
	function format_request($request)
	{
		//No formatting required
		if(strpos($request,"|") === false && strpos($request,"custom**") === false)
		{
			trigger_error("Raw song strings are deprecated and will cease to be supported by this system in the future.",E_USER_DEPRECATED);
			return $request;
		}
		//Set up return string
		$return="";
		if(ctype_digit($request) === true)
		{
			//Get song
			$req=get_song($request);
		}
		else
		{
			//Format existing song
			$rawreq=explode("|",$request);
			$req=array();
			foreach($rawreq as $r)
			{
				$r=explode("=",$r);
				while(count($r) < 2)
				{
					$r[]="";
				}
				$req[$r[0]]=$r[1];
			}
		}
        //If ID flag is set, remove it
        if(isset($req["ID"]))
        {
            unset($req["ID"]);
        }
		//If custom request, return it
		if(count($req) == 1 && isset($req["custom**"]))
		{
			return $req["custom**"];
		}
		//Get format information
		$format=get_system_format();
		//Loop through song information
		foreach($req as $rkey=>$rvalue)
		{
			if(strpos($rkey,"*") === false && $rvalue != "")
			{
				if(isset($format[$rkey]) && $format[$rkey] != "")
				{
					$return.=$format[$rkey] . ": ";
				}
				else
				{
					$return.=$rkey . ": ";
				}
				$return.=$rvalue . ", ";
			}
		}
		//Remove last comma from string and return it
		return substr($return,0,-2);
	}
    function request_song($list,$id)
    {
        $songs=explode("\r\n",get_raw_songs($list));
        if(!isset($songs[$id]))
        {
            trigger_error("Unable to update song: song ID $number is out of range. Blow the song list up with dynamite and a laser beam and try again.",E_USER_WARNING);
            return false;
        }
        $songs[$id]=explode("|",$songs[$id]);
        $songs[$id][1]++;
        $songs[$id][2]=time();
        $songs[$id]=implode("|",$songs[$id]);
        $songs=implode("\r\n",$songs);
        $fh=fopen("songs/$list.txt",'w');
        if($fh)
        {
            fwrite($fh,$songs);
            fclose($fh);
            return true;
        }
        return false;
    }
    
    function sort_by_popularity($a,$b)
    {
        if(!isset($a["request_count"]) || !isset($b["request_count"]))
        {
            trigger_error("One of the songs supplied to sort_by_popularity(song,song) is in an invalid format.",E_USER_ERROR);
            return 0;
        }
        if($a["request_count"] < $b["request_count"])
        {
            return 1;
        }
        elseif($a["request_count"] > $b["request_count"])
        {
            return -1;
        }
        else
        {
            return 0;
        }
    }
    function sort_by_date_added($a,$b)
    {
        if(!isset($a["added_to_system"]) || !isset($b["added_to_system"]))
        {
            trigger_error("One of the songs supplied to sort_by_time_added(song,song) is in an invalid format.",E_USER_ERROR);
            return 0;
        }
        if($a["added_to_system"] < $b["added_to_system"])
        {
            return 1;
        }
        elseif($a["added_to_system"] > $b["added_to_system"])
        {
            return -1;
        }
        else
        {
            return 0;
        }
    }
    
    function write_rss_entry($postid,$name,$date,$request)
    {
        $xml="<!-- insert here -->\n<entry>\n<author>" . stripcslashes($name) . "</author>\n<id>" . $postid . "</id>\n<link href=\"..\"/>\n<title type=\"html\">Request # " . $postid . " at " . $date . "</title>\n<content type=\"text\">" . stripcslashes(format_request($request)) . ". Song requested by " . stripcslashes($name) . ".</content>\n</entry>";
		$xmlc=file_get_contents("backend/rss.xml");
		$xmlc=str_replace("<!-- insert here -->",$xml,$xmlc);
		$fh=fopen("backend/rss.xml",'w');
		if($fh)
		{
			fwrite($fh,$xmlc);
			fclose($fh);
			return true;
		}
		else
		{
			trigger_error("Failed to open XML file \"backend/rss.xml\" in write mode. It or the containing folder should now be microwaved.",E_USER_ERROR);
			return false;
		}
    }
	
    //Function for autobanning a user for using an inappropriate word in their username
	function autoban($username)
	{
		//If auto banning disabled, pass the username no matter what
		if(get_system_setting("autoban") == "no")
		{
			return true;
		}
		//Get banned word list
		$banwords=explode(",",get_system_setting("banwords"));
		//Get partial flag
		$partial=get_system_setting("partial");
		//Format username
		$formatteduname=strtolower($username);
		//Loop through each word
		foreach($banwords as $word)
		{
			if($partial == "yes" && strpos($formatteduname,$word) !== false)
			{
				//Username contains banned word, block it
				return false;
			}
			elseif($formatteduname == $word)
			{
				//Username contains banned word, block it
				return false;
			}
		}
		//Username passes test
		return true;
	}
	
	//Function for creating all system setting files (if they are missing)
	function verify_system_config()
	{
		$settings=get_system_default("RETURN_ALL");
		foreach($settings as $setting)
		{
			if(does_setting_exist($setting) === false)
			{
				$debug=save_system_setting($setting,get_system_default($setting));
				if($debug === true)
				{
					trigger_error("Setting $setting was missing and was set to default. Report this to the administrator.",E_USER_NOTICE);
				}
				else
				{
					trigger_error("Setting $setting is missing in the system and it could not be created. Something probably got microwaved. Expect problems.",E_USER_WARNING);
				}
			}
		}
		if(!file_exists("ban/ip.txt") || file_get_contents("ban/ip.txt") == " ")
		{
			$fh=fopen("ban/ip.txt",'w');
			fclose($fh);
		}
		if(!file_exists("ban/uname.txt") || file_get_contents("ban/uname.txt") == " ")
		{
			$fh=fopen("ban/uname.txt",'w');
			fclose($fh);
		}
		if(!file_exists("backend/autorules.txt") || file_get_contents("backend/autorules.txt") == " ")
		{
			$fh=fopen("backend/autorules.txt",'w');
			fclose($fh);
		}
	}
	
	//Function for dealing with the allowed codes in the system
	function replace_code($input)
	{
		//SUPPORTED: [b] [i] [u] [url="blah"]
		
		$output=str_replace("[url","<a href",$input);
		$output=str_replace("[/url]","</a>",$output);
		$output=str_replace("[","<",$output);
		$output=str_replace("]",">",$output);
		$output=str_replace("&#34;","\"",$output);
		
		return $output;
	}
	
	//Function for getting copyright information
	function get_copyright_information()
	{
		if(file_exists("backend/copyinfo.txt"))
		{
			$copyinfo=replace_code(file_get_contents("backend/copyinfo.txt"));
			return $copyinfo;
		}
		else
		{
			return false;
		}
	}
	//Function for getting raw copyright information
	function get_raw_copyright_information()
	{
		if(file_exists("backend/copyinfo.txt"))
		{
			$copyinfo=file_get_contents("backend/copyinfo.txt");
			return $copyinfo;
		}
		else
		{
			return false;
		}
	}
	//Function for setting copyright information
	function set_copyright_information($info)
	{
		if($info != "")
		{
			$fh=fopen("backend/copyinfo.txt",'w');
			if($fh)
			{
				fwrite($fh,stripcslashes($info));
				fclose($fh);
				return true;
			}
		}
		return false;
	}
	//Function for clearing copyright information
	function clear_copyright_information()
	{
		return unlink("backend/copyinfo.txt");
	}
	
	//Function for tracking login requests
	function track_login($ip,$date,$success,$number="Not tracked")
	{
		$result="Failure";
		if($success === true)
		{
			$result="Success";
		}
		$fh=fopen("backend/login-log.txt",'a');
		if($fh)
		{
			fwrite($fh,"$ip|$date|$result|$number|U\r\n");
			fclose($fh);
			return true;
		}
		return false;
	}
	//Function for getting all login attempts
	function get_login_attempts()
	{
		//FORMAT: [IP,Date,Result,Number,Read]
		$logins=array();
		if(file_exists("backend/login-log.txt"));
		{
			$raw=array_filter(explode("\r\n",file_get_contents("backend/login-log.txt")));
			for($i=0;$i<count($raw);$i++)
			{
				$raw[$i]=explode("|",$raw[$i]);
			}
			$logins=$raw;
		}
		return $logins;
	}
	//Function for getting a single login attempt
	function get_login_attempt($id)
	{
		$logins=get_login_attempts();
		if(isset($logins[$id]))
		{
			return $logins[$id];
		}
		else
		{
			trigger_error("Failed to get login $id; something probably got clobbered with multiple different kinds of fruit.",E_USER_WARNING);
			return array();
		}
	}
	//Function for marking login attempts as read
	function mark_attempts_as_read()
	{
		$logins=get_login_attempts();
		for($i=0;$i<count($logins);$i++)
		{
			$logins[$i][4]="R";
			$logins[$i]=implode("|",$logins[$i]);
		}
		$fh=fopen("backend/login-log.txt",'w');
		if($fh)
		{
			foreach($logins as $login)
			{
				fwrite($fh,"$login\r\n");
			}
			fclose($fh);
			return true;
		}
		return false;
	}
	//Function for clearing login logs
	function clear_login_log()
	{
		$fh=fopen("backend/login-log.txt",'w');
		if($fh)
		{
			fclose($fh);
			return true;
		}
		return false;
	}
	
	//Function for writing to the request database
	function write_req_db($status,$id)
	{
		if(file_exists("backend/req-db.txt"))
		{
			$db=unserialize(file_get_contents("backend/req-db.txt"));
			if($status == -1)
			{
				for($i=0;$i<count($db);$i++)
				{
					if(($index=array_search($id,$db[$i])) !== false)
					{
						unset($db[$i][$index]);
					}
				}
			}
			else
			{
				for($i=0;$i<count($db);$i++)
				{
					if(($index=array_search($id,$db[$i])) !== false && $i != $status)
					{
						unset($db[$i][$index]);
					}
					elseif($i == $status)
					{
						$db[$i][]=$id;
					}
				}
			}
			$fh=fopen("backend/req-db.txt",'w');
			if($fh)
			{
				fwrite($fh,serialize($db));
				fclose($fh);
				return true;
			}
		}
		trigger_error("Failed to write to request database. Expect problems.",E_USER_ERROR);
		return false;
	}
	//Function for reading the request database
	function get_req_db($index)
	{
		if(file_exists("backend/req-db.txt"))
		{
			$db=unserialize(file_get_contents("backend/req-db.txt"));
			if(isset($db[$index]))
			{
				return $db[$index];
			}
		}
		trigger_error("Failed to read request database. Expect problems.",E_USER_ERROR);
		return array();
	}
	
	//Function for getting database of request dates
	function get_date_db()
	{
		if(file_exists("backend/date-db.txt"))
		{
			return unserialize(file_get_contents("backend/date-db.txt"));
		}
		trigger_error("Failed to read date database. Expect problems.",E_USER_ERROR);
		return array();
	}
	//Function for getting "recent" database of request dates
	function get_recent_date_db($modifier)
	{
		if(file_exists("backend/date-db.txt"))
		{
			$rawdb=unserialize(file_get_contents("backend/date-db.txt"));
			$db=array();
			foreach($rawdb as $key=>$value)
			{
				$time=$value + $modifier;
				if(time() < $time)
				{
					$db[$key]=$value;
				}
			}
			return $db;
		}
		trigger_error("Failed to read date database. Expect problems.",E_USER_ERROR);
		return array();
	}
	//Function for saving data to date database
	function write_date_db($delete,$index,$time=0)
	{
		$db=get_date_db();
		if($delete === true)
		{
			if(isset($db[$index]))
			{
				unset($db[$index]);
			}
			else
			{
				trigger_error("Failed to find request ID $index in list. Expect problems.",E_USER_ERROR);
			}
		}
		else
		{
			$db[$index]=$time;
		}
		$fh=fopen("backend/date-db.txt",'w');
		if($fh)
		{
			fwrite($fh,serialize($db));
			fclose($fh);
			return true;
		}
		trigger_error("Failed to write to date database. Expect problems.",E_USER_ERROR);
		return false;
	}
    
    //Function for saving the request password
    function save_request_password($password)
    {
        $hash=password_hash($password,PASSWORD_DEFAULT);
        if($hash !== false)
        {
            $fh=fopen("backend/subpass.txt",'w');
            if($fh)
            {
                fwrite($fh,$hash);
                fclose($fh);
                return true;
            }
        }
        return false;
    }
    //Function for validating the request password
    function validate_request_password($password)
    {
        if(file_exists("backend/subpass.txt"))
        {
            return password_verify($password,file_get_contents("backend/subpass.txt"));
        }
        return false;
    }
	
	//Function for getting the list of auto open/close rules
	function get_autoopen_rules()
	{
		if(file_exists("backend/autorules.txt"))
		{
			$rawrules=array_filter(explode("\r\n",file_get_contents("backend/autorules.txt")));
			$rules=array();
			for($i=0;$i<count($rawrules);$i++)
			{
				$rules[]=array_merge(array($i),explode("|",$rawrules[$i]));
			}
			return $rules;
		}
		trigger_error("Auto open/close rules file doesn't exist.",E_USER_ERROR);
		return array();
	}
	//Function for adding a new auto open/close rule
	function add_autoopen_rule($days,$openhour,$openminute,$openmerid,$closehour,$closeminute,$closemerid,$nextday)
	{
		$rule="$days|$openhour:$openminute $openmerid|$closehour:$closeminute $closemerid|$nextday";
		if(file_exists("backend/autorules.txt") && file_get_contents("backend/autorules.txt") != "")
		{
			$rule="\r\n$rule";
		}
		$fh=fopen("backend/autorules.txt",'a');
		if($fh)
		{
			fwrite($fh,$rule);
			fclose($fh);
			return true;
		}
		return false;
	}
	//Function for removing an auto open/close rule
	function remove_autoopen_rule($id)
	{
		if(file_exists("backend/autorules.txt"))
		{
			$rawrules=explode("\r\n",file_get_contents("backend/autorules.txt"));
			if(isset($rawrules[$id]))
			{
				unset($rawrules[$id]);
				$fh=fopen("backend/autorules.txt",'w');
				if($fh)
				{
					fwrite($fh,implode("\r\n",$rawrules));
					fclose($fh);
					return true;
				}
			}
		}
		return false;
	}
	//Function for executing auto open/close rules
	function auto_open_close()
	{
		//Force set timezone
		set_timezone();
		//Get rules
		$rules=get_autoopen_rules();
		foreach($rules as $rule)
		{
			foreach(explode(",",$rule[1]) as $day)
			{
				//Check if day matches current day
				if(date("w") == $day)
				{
					//Make timestamps
					$openstamp=strtotime("Today " . $rule[2]);
					$closestamp=strtotime("Today " . $rule[3])+($rule[4]*24*60*60);
					//Compare to current time
					if(time() > $openstamp && time() <= $closestamp && get_system_setting("posting") == "no")
					{
						//Open system
						save_system_setting("posting","yes");
						return true;
					}
					elseif(time() > $closestamp && get_system_setting("posting") == "yes")
					{
						//Close system
						save_system_setting("posting","no");
						return true;
					}
				}
			}
		}
		//No rule activated
		return false;
	}
	
	//Function for verifying the integrity of the request database
	function verify_request_db()
	{
		if(file_exists("backend/req-db.txt"))
		{
			$reqdb=unserialize(file_get_contents("backend/req-db.txt"));
			$ab=array_intersect($reqdb[0],$reqdb[1]);
			$ac=array_intersect($reqdb[0],$reqdb[2]);
			$ad=array_intersect($reqdb[0],$reqdb[3]);
			$bc=array_intersect($reqdb[1],$reqdb[2]);
			$bd=array_intersect($reqdb[1],$reqdb[3]);
			$cd=array_intersect($reqdb[2],$reqdb[3]);
			$au=array_unique($reqdb[0]);
			$bu=array_unique($reqdb[1]);
			$cu=array_unique($reqdb[2]);
			$du=array_unique($reqdb[3]);
			if(count($ab) <= 0 && count($ac) <= 0 && count($ad) <= 0 && count($bc) <= 0 && count($bd) <= 0 && count($cd) <= 0 && count($au) == count($reqdb[0]) && count($bu) == count($reqdb[1]) && count($cu) == count($reqdb[2]) && count($du) == count($reqdb[3]))
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		trigger_error("Failed to read request database. Expect problems.",E_USER_ERROR);
		return false;
	}
	//Function for rebuilding the request database
	function rebuild_request_db()
	{
		$requests=get_requests();
		$reqdb=array(array(),array(),array(),array());
		foreach($requests as $request)
		{
			$reqdb[$request[5]][]=$request[0];
		}
		$fh=fopen("backend/req-db.txt",'w');
		if($fh)
		{
			fwrite($fh,serialize($reqdb));
			fclose($fh);
			return true;
		}
		trigger_error("Failed to open request database in write mode. Microwave it and try again.",E_USER_WARNING);
		return false;
	}
	//Function for getting version information
	function get_version_information()
	{
		$version=array("major"=>0,"minor"=>0,"revision"=>0,"buildcode"=>0,"released"=>"January 1 1970 at 12:00 AM GMT");
		if(file_exists("backend/version.txt"))
		{
			$verinfo=explode("\r\n",file_get_contents("backend/version.txt"));
			if(count($verinfo) == 3)
			{
				$version["buildcode"]=$verinfo[1];
				$version["released"]=$verinfo[2];
				$verinfo=explode("|",$verinfo[0]);
				if(count($verinfo) == 3)
				{
					$version["major"]=$verinfo[0];
					$version["minor"]=$verinfo[1];
					$version["revision"]=$verinfo[2];
				}
				else
				{
					trigger_error("Failed to obtain version information. The version information isn't complete and must therefore have been defenestrated by aliens.",E_USER_ERROR);
				}
			}
			else
			{
				trigger_error("Failed to obtain version information. The file is missing information and must therefore have been defenestrated by aliens.",E_USER_ERROR);
			}
		}
		else
		{
			trigger_error("Failed to obtain version information. The file is missing and must therefore have been defenestrated by aliens.",E_USER_ERROR);
		}
		return $version;
	}
?>
<?php
    //Set new script time limit
	if(function_exists("set_time_limit"))
	{
		set_time_limit(get_system_setting("timelimit"));
	}
?>