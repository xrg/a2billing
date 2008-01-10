<?php /* file module.access.php
	
	Module access - an access control module for back office areas


If you're using $_SESSION , make sure you aren't using session_register() too.
From the manual.
If you are using $_SESSION (or $HTTP_SESSION_VARS), do not use session_register(), session_is_registered() and session_unregister().


*/
error_reporting(E_ALL & ~E_NOTICE);

define ("ACX_ACCESS",	1);

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

require_once(DIR_COMMON."Class.A2Billing.inc.php");	
require_once(DIR_COMMON."languageSettings.inc.php");

function UseLanguage(){
	global $language_list;
	global $FG_DEBUG;
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
    
    if (isset($_SESSION['agent_id']) && ($_SESSION['cus_rights'] != 0) && isset($_SESSION["lang_db"]) && ($_SESSION["lang_db"]) != $lang_abbr) {
    	$DBconn_tmp=A2Billing::DBHandle();
    	$QUERY="UPDATE cc_agent SET language = ". $DBconn_tmp->Quote($lang_abbr) .
    		", locale = " . $DBconn_tmp->Quote(getenv("LANG")) .
    		" WHERE id = " . $DBconn_tmp->Quote($_SESSION['agent_id']) . ';' ;
    	$res = $DBconn_tmp -> query($QUERY);
    	$_SESSION["lang_db"]=$lang_abbr ;
    	//echo $QUERY;
    	if (!$res) {
    		trigger_error("Set language to db failed:" . $DBconn_tmp->ErrorMsg(),E_USER_WARNING);
    	}
    	//DbDisconnect($DBconn_tmp);
    }
}
	// If session is registered, take care of login
if( session_is_registered('pr_login'))
	UseLanguage();



function has_rights ($condition) {
	return ($_SESSION['cus_rights'] & $condition);
}
?>
