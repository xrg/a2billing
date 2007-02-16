<?php


class paypal_ipn
{
	var $paypal_post_vars;
	var $paypal_response;
	var $timeout;

	var $error_email;
	
	function paypal_ipn($paypal_post_vars) {
		$this->paypal_post_vars = $paypal_post_vars;
		$this->timeout = 120;
	}

	function send_response()
	{
		$errno = $errstr = null;
		$fp = @fsockopen( "www.paypal.com", 80, $errno, $errstr, 120 ); 
		
		if (!$fp) { 
			$this->error_out("TRY 1 - PHP fsockopen() error: " . $errstr , "");			
			sleep(10);
			$fp = @fsockopen( "www.paypal.com", 80, $errno, $errstr, 120 ); 
		}

		if (!$fp) { 
			$this->error_out("TRY 2 - PHP fsockopen() error: " . $errstr , "");
		} else {
			foreach($this->paypal_post_vars AS $key => $value) {
				if (@get_magic_quotes_gpc()) {
					$value = stripslashes($value);
				}
				$values[] = "$key" . "=" . urlencode($value);
			}

			$response = @implode("&", $values);
			$response .= "&cmd=_notify-validate";

			fputs( $fp, "POST /cgi-bin/webscr HTTP/1.0\r\n" ); 
			fputs( $fp, "Content-type: application/x-www-form-urlencoded\r\n" ); 
			fputs( $fp, "Content-length: " . strlen($response) . "\r\n\n" ); 
			fputs( $fp, "$response\n\r" ); 
			fputs( $fp, "\r\n" );

			$this->send_time = time();
			$this->paypal_response = ""; 

			// get response from paypal
			while (!feof($fp)) { 
				$this->paypal_response .= fgets( $fp, 1024 ); 

				if ($this->send_time < time() - $this->timeout) {
					$this->error_out("Timed out waiting for a response from PayPal. ($this->timeout seconds)" , "");
				}
			}

			fclose( $fp );

		}

	}
	
	function is_verified() {
		if( ereg("VERIFIED", $this->paypal_response) || ereg("verified", $this->paypal_response) )
			return true;
		else
			return false;
	} 

	function get_payment_status() {
		return $this->paypal_post_vars['payment_status'];
	}

	function error_out($message, $em_headers)
	{

		$date = date("D M j G:i:s T Y", time());
		$message .= "\n\nThe following data was received from PayPal:\n\n";

		@reset($this->paypal_post_vars);
		while( @list($key,$value) = @each($this->paypal_post_vars)) {
			$message .= $key . ':' . " \t$value\n";
		}
		error_log ("\n\n[$date] paypay_ipn notification\n\n".$message, 3, PAYPAL_LOGFILE);	
		//error_log ("\n\n[$date] paypay_ipn notification\n\n".$message, 3, 'mylog.txt');	
		mail($this->error_email, "[$date] paypay_ipn notification", $message, $em_headers);

	}
} 

?>
