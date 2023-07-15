<?php	
	//Change working directory to ensure proper operation
	chdir("..");
	//Set the system error handler
	if(file_exists("backend/apierrorhandler.php"))
	{
		include("backend/apierrorhandler.php");
	}
    elseif(file_exists("backend/errorhandler.php"))
	{
		include("backend/errorhandler.php");
	}
	else
	{
		trigger_error("Failed to invoke system error handler. Expect information leakage.",E_USER_WARNING);
	}
	//Include useful functions
	if(file_exists("backend/functions.php"))
	{
		include("backend/functions.php");
	}
	else
	{
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
		die(http_response_code(500));
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
	
	//Get all necessary settings
	if(isset($_POST['post']))
	{
		$post=preg_replace("/[^0-9]/","",$_POST['post']);
	}
	else
	{
		$post="";
	}
	$allowed=get_system_setting("interface");
	if($allowed == "yes")
	{
		$allowed=true;
	}
	else
	{
		$allowed=false;
	}
	$key=get_system_setting("autokey");
	if($post != "")
	{
		$post_exists=does_post_exist($_POST['post']);
	}
	else
	{
		$post_exists=false;
	}
	$pagenable=explode(",",get_system_setting("apipages"));
	$default="<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 3.2 Final//EN\">\r\n
<html>\r\n
  <head>\r\n
    <meta http-equiv=\"content-type\" content=\"text/html; charset=utf-8\">\r\n
    <meta name=\"generator\" content=\"CoffeeCup HTML Editor (www.coffeecup.com)\">\r\n
    <meta name=\"created\" content=\"Thu, 2 Nov 2017 18:41:42 GMT\">\r\n
    <meta name=\"description\" content=\"\">\r\n
    <meta name=\"keywords\" content=\"\">\r\n
	<link rel=\"shortcut icon\" href=\"backend/favicon.ico\">\r\n
    <title>What Are You Doing Here?</title>\r\n
    
    <style type=\"text/css\">\r\n
    <!--\r\n
    body {\r\n
      color:#000000;\r\n
      background-color:#FFFFFF;\r\n
      background-image:url('../backend/background.gif');\r\n
      background-repeat:repeat;\r\n
    }\r\n
    a  { color:#FFFFFF; background-color:#0000FF; }\r\n
    a:visited { color:#FFFFFF; background-color:#800080; }\r\n
    a:hover { color:#000000; background-color:#00FF00; }\r\n
    a:active { color:#000000; background-color:#FF0000; }\r\n
    -->\r\n
    </style>\r\n
  </head>\r\n
  <body>\r\n
  <h1 style=\"text-align:center; text-decoration:underline;\">What Are You Doing Here?</h1>\r\n
  <img style=\"display:block; margin-left:auto; margin-right:auto;\" src=\"../backend/forbidden.png\" alt=\"You are not wanted here...\" title=\"You are not wanted here...\"><br>\r\n
  <p>You have attempted to access something you do not have permissions to access. Your computer will be microwaved if you do not <a href=\"../index.php\">leave</a> immediately. Save your computer the trouble!</p>\r\n
  </body>\r\n
</html>";
	set_timezone();
	write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Attempted to queue post $post via API");
	if($allowed == "yes")
	{
		if(in_array(2,$pagenable))
		{
			if($key != "" && isset($_POST['key']) && password_verify($_POST['key'],$key) === true)
			{
				if($post_exists === true)
				{
					if(isset($_POST['comment']))
					{
						$comment=htmlspecialchars(str_replace("|","-",$_POST['comment']));
					}
					else
					{
						$comment="";
					}
					$post=get_request($post);
					while(count($post) < 9)
					{
						$post[]="";
					}
					$debug=write_request($post[0],$post[1],$post[2],$post[3],$post[4],2,$comment,$post[7]);
					if($debug === false)
					{
						http_response_code(500);
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to queue post $post: the file has been microwaved");
						echo $default;
					}
					else
					{
						http_response_code(200);
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Successfully queue post $post");
						echo $default;
					}
				}
				else
				{
					http_response_code(500);
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to queue post $post: the file has been abducted by Russians");
					echo $default;
				}
			}
			else
			{
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to queue post $post: invalid password submitted");
				http_response_code(403);
				echo $default;
			}
		}
		else
		{
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to queue post $post: action not allowed");
			http_response_code(404);
			echo $default;
		}
	}
	else
	{
		write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to queue post $post: API not enabled");
		http_response_code(410);
		echo $default;
	}
?>
