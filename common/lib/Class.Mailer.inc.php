<?php

/** Abstract the body of the email
*/
abstract class Mailer_EmailBody {
	abstract public function create_body();
};

class Mailer_TextBody extends Mailer_EmailBody {
	public $text = "";
	public $charset="UTF-8";
	
	function Mailer_TextBody($text){
		$this->text = $text;
	}
	
	
	public function create_body() {
		$ret= "Content-Transfer-Encoding: 8bit\r\n".
		     "Content-Type: text/plain; charset = \"".$this->charset."\"\r\n";
		$ret.= "\r\n\r\n";
		$str = str_replace("\r\n", "\n", $this->text);
        	$str = str_replace("\r", "\n", $str);
        	$str = str_replace("\n", "\r\n", $str);
		$ret.= $str;
		$ret.= "\r\n\r\n";
		return $ret;
	}

};

class Mailer_Multipart extends Mailer_EmailBody {
	protected $parts = array();
	
	public function create_body() {
		if (empty($this->parts))
			throw new Exception("Empty multipart body.");
		
		$boundary = "_b" . md5(uniqid(time()));
		$ret = "Content-Type: Multipart/Mixed;\r\n";
		$ret .= "\tboundary=\"Boundary-=$boundary\"\r\n";
		
		foreach ($this->parts as $part){
			//if (! $part instanceof Mailer_EmailBody)
			//	throw new Exception("Wrong class in multipart body");
			
			$ret .= $part->create_body();
			$ret .= "\r\n--Boundary-=".$this->boundary."\r\n";
		}
		return $ret;

	}
};

/*
	/**Sets the Encoding of the message. Options for this are "8bit" (default),
	   "7bit", "binary", "base64", and "quoted-printable". * /
	public $encoding          = "8bit";
	*-*
        // Add all attachments
        if((!empty($this->attachment))|| !empty($this->alt_body))
        {
            // Set message boundary
            $this->boundary = "_b" . md5(uniqid(time()));
            // Set message subboundary for multipart/alternative
            $this->subboundary = "_sb" . md5(uniqid(time()));

            $header[] = "Content-Type: Multipart/Mixed;";
            $header[] = sprintf("\tboundary=\"Boundary-=%s\"", $this->boundary);
        }
        else
        {
            $header[] = sprintf("Content-Transfer-Encoding: %s", $this->encoding);
            $header[] = sprintf("Content-Type: %s; charset = \"%s\"",
                                $this->content_type, $this->body_charset);
        }

	protected $boundary = false;  ///< Holds the message boundary.
	protected $subboundary = false;  ///< message boundary for multipart/alternative messages
	protected $attachment = array();  ///< Holds all string and binary attachments.
*/

/** A wrapper around the mail() function.
   Mailer adds some more headers, charset support and a little
   better formatting to the capabilities of mail().

   However, it is only supposed to send properly laid-out mail.
   
   Portions derived from class.phpmailer.
*/
class Mailer {
	public $subject_charset;
	public $priority          = 3; ///< Email priority (1 = High, 3 = Normal, 5 = low)

	protected $from; ///< The "from" field, one line, already encoded.
	protected $to = array(); ///< Holds all "To" addresses.
	protected $cc = array(); ///< Holds all "CC" addresses.
	
	protected $bcc= array(); ///< Holds all "BCC" addresses.
	protected $reply_to = array(); ///<  Holds all "Reply-To" addresses.
	
	protected $custom_headers = array();  ///< Holds all custom headers.
	
	protected $do_wrap = false; ///< Controls whether body will be wrapped

	public $body ; ///< The email body, must be class Mailer_EmailBody .


	/** Returns the proper RFC 822 formatted date. Returns string.
	* @returns string
	*/
	function rfc_date() {
		$tz = date("Z");
		$tzs = ($tz < 0) ? "-" : "+";
		$tz = abs($tz);
		$tz = ($tz/3600)*100 + ($tz%3600)/60;
		$date = sprintf("%s %s%04d", date("D, j M Y H:i:s"), $tzs, $tz);
		return $date;
	}


	/** Encode an e-mail address like "$name" <$email> ..
	   This will also quote-printable encode any non-latin
	   characters
	*/
	static public function fmt_address($name, $mail, $encoding = 'UTF-8'){
		if ($encoding != 'iso-8859-1'){
			if (($name2 = Mailer::encode_qp($name))!=$name)
				$name2="=?".$encoding."?Q?".$name2."?=";
		}
		return "$name2 <$mail>";
	}
	
	/** Encode an e-mail address like "$name" <$email> ..
	   This will also base64 encode any non-latin
	   characters
	*/
	static public function fmt_address64($name, $mail, $encoding= 'UTF-8'){
		if ($encoding != 'iso-8859-1'){
			$name2="=?".$encoding."?b?".base64_encode($name)."?=";
		}
		return "$name2 <$mail>";
	}
	
	

	/** Assembles message headers.  Returns a string if successful
	or false if unsuccessful.
	@returns array of string headers
	*/
	protected function create_headers() {
		$header = array();
		$header[] = sprintf("Date: %s", $this->rfc_date());
	
		//TODO: fix encoding!
		$header[] = "From: ". $this->from;
		if(!empty($this->cc))
			$header[] = $this->addr_append("Cc", $this->cc);
	
		// sendmail and mail() extract Bcc from the header before sending
		if(!empty($this->bcc))
			$header[] = $this->addr_append("Bcc", $this->bcc);
	
		if(!empty($this->reply_to))
			$header[] = $this->addr_append("Reply-to", $this->reply_to);
	
		$header[] = sprintf("X-Priority: %d", $this->priority);
		$header[] = sprintf("X-Mailer: Mail System ");
		//$header[] = sprintf("Return-Path: %s", $this -> Sender);
			//print_r ($header);
	
		// Add custom headers
		foreach ($this->custom_headers as $ch)
			$header[] = $ch;
		
		$header[] = "MIME-Version: 1.0";
		return $header;
	}

    /**
     * Assembles the message body.  Returns a string if successful
     * or false if unsuccessful.
     * @private
     * @returns string
     */
    function create_body() {
        // wordwrap the message body if set
        if($this->do_Wrap)
            $this->Body = wordwrap($this->Body,78,"\r\n");

        // If content type is multipart/alternative set body like this:
        if ((!empty($this->AltBody)) && (count($this->attachment) < 1))
        {
            $pfn("This is a MIME message. If you are reading this text, you");
            $pfn("might want to consider changing to a mail reader that");
            $pfn("understands how to properly display MIME multipart messages.");
            $pfn("");
            $pfn("--Boundary-=".$this->boundary);
if (0) {
            // Insert body. If multipart/alternative, insert both html and plain
            $mime[] = sprintf("Content-Type: %s; charset = \"%s\";\r\n" .
                              "\tboundary=\"Boundary-=%s\";\r\n\r\n",
                               $this->ContentType, $this->CharSet, $this->subboundary);

            $mime[] = sprintf("--Boundary-=%s\r\n", $this->subboundary);
            $mime[] = sprintf("Content-Type: text/plain; charset = \"%s\";\r\n", $this->CharSet);
            $mime[] = sprintf("Content-Transfer-Encoding: %s\r\n\r\n", $this->Encoding);
            $mime[] = sprintf("%s\r\n\r\n", $this->AltBody);

            $mime[] = sprintf("--Boundary-=%s\r\n", $this->subboundary);
            $mime[] = sprintf("Content-Type: text/html; charset = \"%s\";\r\n", $this->CharSet);
            $mime[] = sprintf("Content-Transfer-Encoding: %s\r\n\r\n", $this->Encoding);
            $mime[] = sprintf("%s\r\n\r\n", $this->Body);

            $mime[] = sprintf("\r\n--Boundary-=%s--\r\n\r\n", $this->subboundary);

            $mime[] = sprintf("\r\n--Boundary-=%s--\r\n", $this->boundary);

            $this->Body = $this->encode_string(join("", $mime), $this->encoding);
}
        }
        else
        {
            $this->Body = $this->encode_string($this->Body, $this->encoding);
        }


        if(count($this->attachment) > 0)
        {
            if(!$body = $this->attach_all())
                return false;
        }
        else
            $body = $this->Body;

      //  return($body);
    }
    
	/** Print one line
	This function is used as a parameter to create_body
	for debugging
	*/
	protected function printline($str){
		echo $str ."\n";
	}

	/** Print the mail in plaintext in standard output
	   used for debugging */
	public function PrintMail(){
		echo join("\r\n",$this->create_headers());
		echo "\r\n";
		if ( ! ($this->body instanceof Mailer_EmailBody))
			throw new Exception("Invalid object as email body");
		echo $this->body->create_body($this->printline);
	}

	/** Encodes attachment in requested format.
	    @returns string */
	protected function encode_file ($path, $encoding = "base64") {
		if(!@$fd = fopen($path, "rb"))
			throw new Exception(sprintf("File Error: Could not open file %s", $path));
		$file = fread($fd, filesize($path));
		$encoded = $this->encode_string($file, $encoding);
		fclose($fd);
		
		return($encoded);
	}

	/** Encodes string to requested format.
	* @returns string
	*/
	/*
	protected function encode_string ($str, $encoding = "base64") {
		switch(strtolower($encoding)) {
		case "base64":
			$encoded = chunk_split(base64_encode($str));
			break;
	
		case "7bit":
		case "8bit":
			$encoded = $this->fix_eol($str);
			if (substr($encoded, -2) != "\r\n")
				$encoded .= "\r\n";
			break;
	
		case "binary":
			$encoded = $str;
			break;
	
		case "quoted-printable":
			$encoded = $this->encode_qp($str);
			break;
	
		default:
			throw new Exception("Unknown encoding: " .$encoding);
		}
		return($encoded);
	}
	*/

	/** Encode string to quoted-printable.
	*/
	static public function encode_qp ($str) {
	
		$encoded = $str;
		// Replace every high ascii, control and = characters
		$encoded = preg_replace("/([\001-\010\013\014\016-\037\075\177-\377])/e",
			"'='.sprintf('%02X', ord('\\1'))", $encoded);
		// Replace every spaces and tabs when it's the last character on a line
		$encoded = preg_replace("/([\011\040])\r\n/e",
			"'='.sprintf('%02X', ord('\\1')).'\r\n'", $encoded);
	
		// Maximum line length of 76 characters before CRLF (74 + space + '=')
		//$encoded = wordwrap($encoded, 74, "\r\n");
	
		return $encoded;
	}
	
	/** Sets the From: field */
	public function setFrom($name, $mail) {
		$this->from = $this->fmt_address($name,$mail);
	}

};

?>