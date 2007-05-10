<?php
/***************************************************************************
 *
 * callbackexec.php : PHP A2Billing - Request Callback
 * Written for PHP 4.x & PHP 5.X versions.
 *
 * A2Billing -- Asterisk billing solution.
 * Copyright (C) 2004, 2007 Belaid Arezqui <areski _atl_ gmail com>
 *
 * See http://www.asterisk2billing.org for more information about
 * the A2Billing project. 
 * Please submit bug reports, patches, etc to <areski _atl_ gmail com>
 *
 * This software is released under the terms of the GNU Lesser General Public License v2.1
 * A copy of which is available from http://www.gnu.org/copyleft/lesser.html
 *
 ****************************************************************************/

/***************************************************************************
 *
 * USAGE : http://domainname/A2Billing_UI/api/SOAP/callbackexec.php?wsdl
 *
 * http://localhost/~areski/svn/a2billing/trunk/A2Billing_UI/api/SOAP/callbackexec.php?wsdl
 *
 * 	http://domain/path/soap/soap-db-callback.php?security_key=13a7fa40cfcef6fe7ac9718a5c76cdb5&phone_number=XXXXX&callerid=123456	
 *	 &callback_time=2006-09-20+19%3A30%3A00
 *
 ****************************************************************************/

include ("../../lib/defines.php");
include ("../../lib/regular_express.inc");
require_once('SOAP/Server.php');
require_once('SOAP/Disco.php');

define ("LOG_CALLBACK", isset($A2B->config["log-files"]['api_callback'])?$A2B->config["log-files"]['api_callback']:null); 

/*
//$phone_number = '34650784355';
$phone_number = $_GET['phone_number'];
$callerid = $_GET['callerid'];
$security_key = $_GET['security_key'];
$uniqueid = $_GET['uniqueid'];
$callback_time = urldecode($_GET['callback_time']);
*/

//$ans = Request($security_key, $phone_number, $callerid, $callback_time, $uniqueid);
//print_r($ans);




class Callback
{
	var $__dispatch_map = array();

	function Callback() {
        // Define the signature of the dispatch map on the Web servicesmethod

        // Necessary for WSDL creation
		
        $this->__dispatch_map['Request'] =
             array('in' => array('security_key' => 'string', 'phone_number' => 'string', 'callerid' => 'string', 'callback_time' => 'string', 'uniqueid' => 'string'),
                   'out' => array('id' => 'string', 'result' => 'string', 'details' => 'string')
                   );
		
        $this->__dispatch_map['Status'] =
			array('in' => array('security_key' => 'string', 'id' => 'string'),
				'out' => array('uniqueid' => 'string', 'result' => 'string', 'details' => 'string')
				);
		
     }
	 

	/*
	 *		Function to make Callback : it will insert a callback request 
	 */ 
	function Status($security_key, $id){
		// nada
	}
	
	
	/*
	 *		Function to make Callback : it will insert a callback request 
	 */ 
	function Request($security_key, $phone_number, $callerid, $callback_time, $uniqueid){
		
		$status = 'PENDING';
		$server_ip = 'localhost';
		$num_attempt = 0;
		$channel = 'SIP/'.$phone_number.'@mylittleIP';	
		$exten = $phone_number;
		$context = 'a2billing';
		$priority = 1;
		//$timeout	callerid
		$variable = "phonenumber=$phone_number|callerid=$callerid";
		
		if (strlen($uniqueid)==0){
			$uniqueid 	=  MDP_STRING(5).'-'.MDP_NUMERIC(10);
		}
		
		$FG_regular[]  = array(    "^([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})$"   ,"(YYYY-MM-DD HH:MM:SS)");
		
		
		// The wrapper variables for security
		// $security_key = API_SECURITY_KEY;
		write_log( LOG_CALLBACK, " Service_Callback( security_key=$security_key, phone_number=$phone_number, callerid=$callerid, uniqueid=$uniqueid, callback_time=$callback_time)");
		$mysecurity_key = API_SECURITY_KEY;
		
		$mail_content = "[" . date("Y/m/d G:i:s", mktime()) . "] "."SOAP API - Request asked: Callback [$phone_number, callback_time=$callback_time]";
		
		
		
		// CHECK CALLERID
		if (strlen($callerid)<1)
		{
			write_log( LOG_CALLBACK, basename(__FILE__).' line:'.__LINE__."[" . date("Y/m/d G:i:s", mktime()) . "] "." ERROR FORMAT CALLERID AT LEAST 1 DIGIT ");
			sleep(2);
			return array($keyword, 'result=Error', " ERROR - FORMAT CALLERID AT LEAST 1 DIGIT ");
		}
		
		// CHECK PHONE_NUMBER
		if (strlen($phone_number)<10)
		{
			write_log( LOG_CALLBACK, basename(__FILE__).' line:'.__LINE__."[" . date("Y/m/d G:i:s", mktime()) . "] "." ERROR FORMAT PHONENUMBER AT LEAST 10 DIGITS ");
			sleep(2);
			return array($keyword, 'result=Error', " ERROR - FORMAT PHONENUMBER AT LEAST 10 DIGITS ");
		}
		
		// CHECK CALLBACK TIME
		if (strlen($callback_time)>1 && !(ereg( $FG_regular[0][0], $callback_time)))
		{
			write_log( LOG_CALLBACK, basename(__FILE__).' line:'.__LINE__."[" . date("Y/m/d G:i:s", mktime()) . "] "." ERROR FORMAT CALLBACKTIME : ".$FG_regular[0][0]);
			sleep(2);
			return array($keyword, 'result=Error', " ERROR - FORMAT CALLBACKTIME : ".$FG_regular[0][0]);
		}
		
		// CHECK SECURITY KEY
		if (md5($mysecurity_key) !== $security_key  || strlen($security_key)==0)
		{
			write_log( LOG_CALLBACK, basename(__FILE__).' line:'.__LINE__."[" . date("Y/m/d G:i:s", mktime()) . "] "." CODE_ERROR SECURITY_KEY");
			sleep(2);
			return array($keyword, 'result=Error', ' KEY - BAD PARAMETER ');
		}
		
		$DBHandle = DbConnect();
		if (!$DBHandle){			
			write_log( LOG_CALLBACK, basename(__FILE__).' line:'.__LINE__."[" . date("Y/m/d G:i:s", mktime()) . "] "." ERROR CONNECT DB");
			sleep(2);
			return array($keyword, 'result=Error', ' ERROR - CONNECT DB ');
		}
		
		if (strlen($callback_time)>1){
			$QUERY = " INSERT INTO callback_spool (uniqueid, status, server_ip, num_attempt, channel, exten, context, priority, variable, callback_time ) ".
				 " values ('$uniqueid', '$status', '$server_ip', '$num_attempt', '$channel', '$exten', '$context', '$priority', '$variable', '$callback_time')";
		}else{
			$QUERY = " INSERT INTO callback_spool (uniqueid, status, server_ip, num_attempt, channel, exten, context, priority, variable ) ".
				 " values ('$uniqueid', '$status', '$server_ip', '$num_attempt', '$channel', '$exten', '$context', '$priority', '$variable')";
		}
		
		$res = $DBHandle -> Execute($QUERY);
		if (!$res){
			write_log( LOG_CALLBACK, basename(__FILE__).' line:'.__LINE__."[" . date("Y/m/d G:i:s", mktime()) . "] "." ERROR INSERT INTO DB");
			sleep(2);
			return array($keyword, 'result=Error', ' ERROR - INSERT INTO DB');
		}
		
		return array($keyword, 'result=Success', " Success - Callback request has been accepted ");
	}

} // end Class




$server = new SOAP_Server();

$webservice = new Callback();

$server->addObjectMap($webservice, 'http://schemas.xmlsoap.org/soap/envelope/');


if (isset($_SERVER['REQUEST_METHOD'])  &&  $_SERVER['REQUEST_METHOD']=='POST') {

     $server->service($HTTP_RAW_POST_DATA);
	 
} else {
     // Create the DISCO server
     $disco = new SOAP_DISCO_Server($server,'Callback');
     header("Content-type: text/xml");
     if (isset($_SERVER['QUERY_STRING']) && strcasecmp($_SERVER['QUERY_STRING'],'wsdl') == 0) {
         echo $disco->getWSDL();
     } else {
         echo $disco->getDISCO();
     }
}


?>
