<?php /* file module.access.php
	
	Module access - an access control module for back office areas


If you're using $_SESSION , make sure you aren't using session_register() too.
From the manual.
If you are using $_SESSION (or $HTTP_SESSION_VARS), do not use session_register(), session_is_registered() and session_unregister().


*/
$FG_DEBUG = 3;
error_reporting(E_ALL & ~E_NOTICE);

// Zone strings
define ("MODULE_ACCESS_DOMAIN",		"CallingCard System");
define ("MODULE_ACCESS_DENIED",		"./Access_denied.htm");


define ("ACX_ACCESS",					1);



header("Expires: Sat, Jan 01 2000 01:01:01 GMT");
session_name("UICSESSION");
session_start();


if (isset($_GET["logout"]) && $_GET["logout"]=="true") { 
	   session_destroy();
	   $cus_rights=0;
	   Header ("HTTP/1.0 401 Unauthorized");
	   Header ("Location: index.php");
	   die();
	}
	
function access_sanitize_data($data){
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
	return ($_SESSION['cus_rights'] & $condition);
}
?>
