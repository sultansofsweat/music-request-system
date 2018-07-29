<?php
	function checkflag()
	{
		//Check for a set admin flag in the session
		if(isset($_SESSION['sradmin']) && $_SESSION['sradmin'] == "y")
		{
			//Valid
			return true;
		}
		//Invalid
		return false;
	}
	function checkip()
	{
		//Check for a set IP in the session and that it matches the user's IP
		if(isset($_SESSION['ip']) && $_SESSION['ip'] != "" && $_SESSION['ip'] == $_SERVER['REMOTE_ADDR'])
		{
			//Valid
			return true;
		}
		//Invalid
		return false;
	}
	function checkua()
	{
		//Check for a set useragent in the session and that it matches the user's IP
		if(isset($_SESSION['ua']) && $_SESSION['ua'] == $_SERVER['HTTP_USER_AGENT'])
		{
			//Valid
			return true;
		}
		//Invalid
		return false;
	}
	function checkid()
	{
		//Check for a unique ID
		if(isset($_SESSION['identifier']) && $_SESSION['identifier'] != "" && intval($_SESSION['identifier']) >= 0 && intval($_SESSION['identifier']) <= 2000000)
		{
			//Valid
			return true;
		}
		//Invalid
		return false;
	}
	function get_system_timeout()
	{
		if(file_exists("backend/timeout.txt"))
		{
			return file_get_contents("backend/timeout.txt");
		}
		return 0;
	}
	function get_system_id()
	{
		if(file_exists("backend/sysid.txt"))
		{
			return file_get_contents("backend/sysid.txt");
		}
		return false;
	}
	function get_security_level()
	{
		if(file_exists("backend/security.txt"))
		{
			return file_get_contents("backend/security.txt");
		}
		return 0;
	}
	function checktimeout($admin)
	{
		//If user is not an administrator, exit immediately
		if($admin !== true)
		{
			return false;
		}
		//Check for an existing last access time, and if it doesn't exist, set it and assume user is still within time limit
		if(!isset($_SESSION['lastaccess']) || $_SESSION['lastaccess'] == "")
		{
			$_SESSION['lastaccess']=time();
			return true;
		}
		else
		{
			//Get system timeout
			$timeout=get_system_timeout();
			//Compute timeout in seconds
			$timeout*=60;
			//If timeout is set and user has not exceeded it, set current time and return true
			if($timeout > 0 && isset($_SESSION['lastaccess']) && ($_SESSION['lastaccess']+$timeout) > time())
			{
				$_SESSION['lastaccess']=time();
				return true;
			}
			elseif($timeout <= 0)
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		//Assume that timeout reachec
		return false;
	}
	function checksysid($admin)
	{
		//If user is not admin, exit immediately
		if($admin !== true)
		{
			return false;
		}
		//Check if system has a sysid
		$sysid=get_system_id();
		if($sysid === false || $_SESSION['systemid'] == $sysid)
		{
			return true;
		}
		return false;
	}
	function securitycheck()
	{
		$admin=false;
		//Check security level
		switch(get_security_level())
		{
			case 1:
			$flag=checkflag();
			$ip=checkip();
			$ua=true;
			$id=true;
			break;
			case 2:
			$flag=checkflag();
			$ip=true;
			$ua=checkua();
			$id=true;
			break;
			case 3:
			$flag=checkflag();
			$ip=checkip();
			$ua=checkua();
			$id=true;
			break;
			case 4:
			$flag=checkflag();
			$ip=true;
			$ua=true;
			$id=checkid();
			break;
			case 5:
			$flag=checkflag();
			$ip=checkip();
			$ua=true;
			$id=checkid();
			break;
			case 6:
			$flag=checkflag();
			$ip=true;
			$ua=checkua();
			$id=checkid();
			break;
			case 7:
			$flag=checkflag();
			$ip=checkip();
			$ua=checkua();
			$id=checkid();
			break;
			case 0:
			default:
			$flag=checkflag();
			$ip=true;
			$ua=true;
			$id=true;
			break;
		}
		if($flag === true && $ip === true && $ua === true && $id === true)
		{
			//Valid login
			$admin=true;
		}
		//Check system ID
		if(checksysid($admin) === true)
		{
			$admin=true;
		}
		else
		{
			$admin=false;
		}
		//Check if timeout breached
		if(checktimeout($admin) === true)
		{
			$admin=true;
		}
		else
		{
			$admin=false;
		}
		//Return result of security check
		return $admin;
	}
?>