<?php /* file module.access.php
	
	Module access - an access control module for back office areas


If you're using $_SESSION , make sure you aren't using session_register() too.
From the manual.
If you are using $_SESSION (or $HTTP_SESSION_VARS), do not use session_register(), session_is_registered() and session_unregister().


*/
error_reporting(E_ALL & ~E_NOTICE);

// Zone strings
define ("MODULE_ACCESS_DOMAIN",		"CallingCard System");
define ("MODULE_ACCESS_DENIED",		"./Access_denied.htm");

define ("ACX_CUSTOMER",		1);
define ("ACX_BILLING",		2);		// 1 << 1
define ("ACX_RATECARD",		4);		// 1 << 2
define ("ACX_TRUNK",   		8);		// 1 << 3
define ("ACX_CALL_REPORT",   	16);		// 1 << 4
define ("ACX_CRONT_SERVICE",   	32);		// 1 << 5
define ("ACX_ADMINISTRATOR",   	64);		// 1 << 6
define ("ACX_FILE_MANAGER",   	128);		// 1 << 7
define ("ACX_MISC",   		256);		// 1 << 8
define ("ACX_DID",   		512);		// 1 << 9
define ("ACX_CALLBACK",		1024);		// 1 << 10
define ("ACX_OUTBOUNDCID",	2048);		// 1 << 11
define ("ACX_PACKAGEOFFER",	4096);		// 1 << 12
define ("ACX_PRED_DIALER",	8192);		// 1 << 13
define ("ACX_INVOICING",	16384);		// 1 << 14
define ("ACX_AGENTS", 		0xffff);	// 1 << 15
define ("ACX_NUMPLAN",		0x10000);
define ("ACX_SERVERS",		0x20000);

define("ACX_ROOT",		0xFFFFF);

header("Expires: Sat, Jan 01 2000 01:01:01 GMT");
//echo "PHP_AUTH_USER : $PHP_AUTH_USER";

if (!isset($_SESSION)) {
	session_name("UIADMINSESSION");
	session_start();
}

$URI = $_SERVER['REQUEST_URI'];
$restircted_url = substr($URI,-16);
if(!($restircted_url == "PP_intro.php") && !($restircted_url == "signup/index.php") && isset($_SESSION["admin_id"])) {
	require_once(DIR_COMMON."Class.Logger.inc.php");
	if (!isset($log))
		$log= new Logger(); // TODO: instance
	$log -> insertLog($_SESSION["admin_id"], 1, "Page Visit", "User Visited the Page", '', $_SERVER['REMOTE_ADDR'], $_SERVER['REQUEST_URI'],'');
}


if (isset($_GET["logout"]) && $_GET["logout"]=="true") {
require_once(DIR_COMMON."Class.Logger.inc.php");
	if (!isset($log))
		$log = new Logger(/*new Config()*/); //TODO..
	$log -> insertLog($admin_id, 1, "USER LOGGED OUT", "User Logged out from website", '', $_SERVER['REMOTE_ADDR'], $_SERVER['REQUEST_URI'],'');
	$log = null;
	session_destroy();
	$rights=0;
	Header ("HTTP/1.0 401 Unauthorized");
	Header ("Location: index.php");
	exit();
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

	// Easter egg to let debug from url
if (isset($_GET['debug']) && is_numeric($_GET['debug']))
	$_SESSION['FG_DEBUG']= $FG_DEBUG = $_GET['debug'];
elseif (isset($_SESSION['FG_DEBUG']))
	$FG_DEBUG =$_SESSION['FG_DEBUG'];

require_once(DIR_COMMON."languageSettings.inc.php");

    if (isset($_GET['language'])){
    	if ($FG_DEBUG >0) echo "<!-- lang explicitly set to ".$_GET['language'] ."-->\n";
      $_SESSION["language"] = $_GET['language'];
    }
    elseif (!isset($_SESSION["language"]))
    { // we have to find a lang to use..
    	if(isset($_SESSION["lang_db"])){
    		foreach($language_list as $lang)
    		if ($lang['abbrev'] == $_SESSION["lang_db"])
    			$_SESSION["language"] = $lang['cname'];
    		if ($FG_DEBUG >0) trigger_error("Lang Selected by db: ". $_SESSION["language"], E_USER_NOTICE);
    	}else
        	$_SESSION["language"]='english';
    }

    define ("LANGUAGE",$_SESSION["language"]);
	//include (FSROOT."lib/languages/".LANGUAGE.".php");
	//define ("LANGUAGE_DIR",FSROOT."lib/languages/".LANGUAGE."/");

    $lang_abbr=SetLocalLanguage($_SESSION["language"]);
    if ($FG_DEBUG >5) trigger_error("lang abbr: $lang_abbr",E_USER_NOTICE);
    
//     if (isset($_SESSION['agent_id']) && ($_SESSION['cus_rights'] != 0) && isset($_SESSION["lang_db"]) && ($_SESSION["lang_db"]) != $lang_abbr) {
//     	$DBconn_tmp=DbConnect();
//     	$QUERY="UPDATE cc_agent SET language = ". $DBconn_tmp->Quote($lang_abbr) .
//     		", locale = " . $DBconn_tmp->Quote(getenv("LANG")) .
//     		" WHERE id = " . $DBconn_tmp->Quote($_SESSION['agent_id']) . ';' ;
//     	$res = $DBconn_tmp -> query($QUERY);
//     	$_SESSION["lang_db"]=$lang_abbr ;
//     	//echo $QUERY;
//     	if (!$res) {
//     		trigger_error("Set language to db failed:" . $DBconn_tmp->ErrorMsg(),E_USER_WARNING);
//     	}
//     	//DbDisconnect($DBconn_tmp);
//     }

?>
