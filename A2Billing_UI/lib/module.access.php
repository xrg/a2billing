<?php /* file module.access.php
	
	Module access - an access control module for back office areas


If you're using $_SESSION , make sure you aren't using session_register() too.
From the manual.
If you are using $_SESSION (or $HTTP_SESSION_VARS), do not use session_register(), session_is_registered() and session_unregister().


*/
$FG_DEBUG = 0;
error_reporting(E_ALL & ~E_NOTICE);

// Zone strings
define ("MODULE_ACCESS_DOMAIN",		"CallingCard System");
define ("MODULE_ACCESS_DENIED",		"./Access_denied.htm");


define ("ACX_CUSTOMER",					1);
define ("ACX_BILLING",					2);			// 1 << 1
define ("ACX_RATECARD",					4);			// 1 << 2
define ("ACX_TRUNK",   					8);			// 1 << 3
define ("ACX_CALL_REPORT",   			16);		// 1 << 4
define ("ACX_CRONT_SERVICE",   			32);		// 1 << 5
define ("ACX_ADMINISTRATOR",   			64);		// 1 << 6
define ("ACX_FILE_MANAGER",   			128);		// 1 << 7
define ("ACX_MISC",   				    256);		// 1 << 8
define ("ACX_DID",   					512);		// 1 << 9
define ("ACX_CALLBACK",					1024);		// 1 << 10
define ("ACX_OUTBOUNDCID",				2048);		// 1 << 11
define ("ACX_PACKAGEOFFER",				4096);		// 1 << 12
define ("ACX_PREDICTIVE_DIALER",		8192);		// 1 << 13
define ("ACX_INVOICING",			16384);		// 1 << 14
define ("ACX_AGENTS", 				32768);		// 1 << 15


header("Expires: Sat, Jan 01 2000 01:01:01 GMT");
//echo "PHP_AUTH_USER : $PHP_AUTH_USER";

if (!isset($_SESSION)) {
	session_name("UIADMINSESSION");
	session_start();
}


if (isset($_GET["logout"]) && $_GET["logout"]=="true") {

	$log = new Logger();			
	$log -> insertLog($admin_id, 1, "USER LOGGED OUT", "User Logged out from website", '', $_SERVER['REMOTE_ADDR'], $_SERVER['REQUEST_URI'],'');
	$log = null;   
	session_destroy();
	$rights=0;
	Header ("HTTP/1.0 401 Unauthorized");
	   Header ("Location: index.php");
	   die();
}
	

function access_sanitize_data($data)
{
	$lowerdata = strtolower ($data);
	$data = str_replace('--', '', $data);	
	$data = str_replace("'", '', $data);
	$data = str_replace('=', '', $data);
	$data = str_replace(';', '', $data);
	if (!(strpos($lowerdata, ' or ')===FALSE)){ return false;}
	if (!(strpos($lowerdata, 'table')===FALSE)){ return false;}
	
	return $data;
}

function has_rights ($condition) {	
	return ($_SESSION["rights"] & $condition);
}


$ACXCUSTOMER 	= has_rights (ACX_CUSTOMER);
$ACXBILLING 	= has_rights (ACX_BILLING);
$ACXRATECARD 	= has_rights (ACX_RATECARD);
$ACXTRUNK		= has_rights (ACX_TRUNK); 
$ACXDID			= has_rights (ACX_DID);
$ACXCALLREPORT	= has_rights (ACX_CALL_REPORT);
$ACXCRONTSERVICE= has_rights (ACX_CRONT_SERVICE);
$ACXMISC 		= has_rights (ACX_MISC);
$ACXADMINISTRATOR = has_rights (ACX_ADMINISTRATOR);
$ACXFILEMANAGER = has_rights (ACX_FILE_MANAGER);
$ACXCALLBACK	= has_rights (ACX_CALLBACK);
$ACXOUTBOUNDCID = has_rights (ACX_OUTBOUNDCID);
$ACXPACKAGEOFFER = has_rights (ACX_PACKAGEOFFER);
$ACXPREDICTIVEDIALER = has_rights (ACX_PREDICTIVE_DIALER);
$ACXINVOICING = has_rights (ACX_INVOICING);
$ACXINVOICING2 = has_rights (ACX_INVOICING);
?>
