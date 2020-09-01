<?php
	//This file contains the MRS API error handler, which is used to replace the built-in that PHP uses
	
	function get_error_mode()
	{
		$errlvl=1;
		if(file_exists(dirname(__FILE__) . "/../backend/errlvl.txt"))
		{
			$errlvl=file_get_contents(dirname(__FILE__) . "/../backend/errlvl.txt");
		}
		return $errlvl;
	}
	function write_error($number,$string,$file,$line)
	{
		$output=implode("|",array(date("g:i:s"),$number,$string,$file,$line));
		if(file_exists(dirname(__FILE__) . "/../error"))
		{
			$fh=fopen(dirname(__FILE__) . "/../error/" . date("Ymd") . ".txt",'a');
		}
		else
		{
			$fh=false;
		}
		if($fh)
		{
			fwrite($fh,stripcslashes($output) . "\r\n");
			fclose($fh);
		}
		else
		{
			trigger_error("Unable to output error information to log: file could not be opened.",E_USER_WARNING);
		}
	}
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
	
	function eh($errno, $errstr, $errfile, $errline)
	{
		write_error($errno,$errstr,basename($errfile),$errline);
        http_response_code(500);
		/*switch ($errno)
		{
			case E_ERROR:
			case E_COMPILE_ERROR:
			case E_CORE_ERROR:
			echo "<p><b><u>ERROR:</u></b> " . $errstr . "<br>\n
			Located on line $errline of " . basename($errfile) . "<br>
			This is a fatal error, stopping execution. Threaten a thousand camels upon the server.</p>\n";
			exit(1);
			break;
			
			case E_USER_ERROR:
			echo "<p><b><u>ERROR:</u></b> " . $errstr . "<br>\n
			Located on line $errline of " . basename($errfile) . "</p>\n";
			break;
			
			case E_WARNING:
			case E_USER_WARNING:
			echo "<p><b><u>WARNING:</u></b> " . $errstr . "<br>\n
			Located on line $errline of " . basename($errfile) . "</p>\n";
			break;
			
			case E_CORE_WARNING:
			case E_COMPILE_WARNING:
			echo "<p><b><u>SYSTEM WARNING:</u></b> " . $errstr . "<br>\n
			Located on line $errline of " . basename($errfile) . "<br>\n
			This is probably a problem. Continuing anyways, expect severe breakage.</p>\n";
			break;
			
			case E_DEPRECATED:
			case E_USER_DEPRECATED:
			if(get_error_mode() == 0)
			{
				break;
			}
			echo "<p><b><u>DEPRECATION NOTICE:</u></b> " . $errstr . "<br>\n
			Located on line $errline of " . basename($errfile) . "</p>\n";
			break;

			case E_NOTICE:
			if(get_error_mode() != 2)
			{
				break;
			}
			case E_USER_NOTICE:
			if(get_error_mode() == 0)
			{
				break;
			}
			echo "<p><b><u>NOTICE:</u></b> " . $errstr . "<br>\n
			Located on line $errline of " . basename($errfile) . "</p>\n";
			break;

			default:
			echo "<p>Unidentified error <b><u>[$errno]</u></b>: $errstr<br>\n
			Located on line $errline of " . basename($errfile) . "</p>\n";
			break;
    	}*/

    	/* Don't execute PHP internal error handler */
    	return true;
	}
	
	//Shutdown function
	function sh()
	{
		$last_error = error_get_last();
		if(!empty($last_error) && isset($last_error['type']) && $last_error['type'] != "")
		{
			eh($last_error['type'], $last_error['message'], $last_error['file'], $last_error['line']);
		}
	}
	
	//Set error handler to the custom one
	$oeh=set_error_handler("eh");
	register_shutdown_function("sh");
?>