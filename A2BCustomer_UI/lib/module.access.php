<?php
/* file module.access.php
	
	Module access - an access control module for back office areas


If you're using $_SESSION , make sure you aren't using session_register() too.
From the manual.
If you are using $_SESSION (or $HTTP_SESSION_VARS), do not use session_register(), session_is_registered() and session_unregister().
*/

require_once(DIR_COMMON."Class.A2Billing.inc.php");
require_once(DIR_COMMON.'Class.DynConf.inc.php');
require_once(DIR_COMMON.'Misc.inc.php');

error_reporting(E_ALL & ~E_NOTICE);

if (!DynConf::GetCfg(CUSTOMER_CFG,'enable',true)){
	@syslog(LOG_ERR,"Somebody tried to access Customer UI on ".$_SERVER['PHP_SELF']." but you have this disabled.");
	Header ("HTTP/1.0 401 Unauthorized");
	exit();
}

define ("MODULE_ACCESS_DOMAIN",	"CallingCard System");
define ("MODULE_ACCESS_DENIED",	"./Access_denied.htm");

define ("ACX_ACCESS", 1);

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

function has_rights ($condition)
{
	return ($_SESSION['cus_rights'] & $condition);
}

require_once(DIR_COMMON."languageSettings.inc.php");

    if (isset($_GET['language'])){
    	if ($FG_DEBUG >0) echo "<!-- lang explicitly set to ".$_GET['language'] ."-->\n";
      $_SESSION["language"] = $_GET['language'];
    }
    // TODO: lang_db
    else if (!isset($_SESSION["language"]))
	$_SESSION["language"] = negot_language('english');


    define ("LANGUAGE",$_SESSION["language"]);
	//include (FSROOT."lib/languages/".LANGUAGE.".php");
	//define ("LANGUAGE_DIR",FSROOT."lib/languages/".LANGUAGE."/");

    $lang_abbr=SetLocalLanguage($_SESSION["language"]);
    if ($FG_DEBUG >5) trigger_error("lang abbr: $lang_abbr",E_USER_NOTICE);

?>