<?php
error_reporting( E_ALL - E_NOTICE );

include (dirname(__FILE__)."/company_info.php");

define( 'FULL_PATH', dirname(__FILE__) . '/' );
define( 'SMARTY_DIR', FULL_PATH . '/Smarty/' );
define( 'TEMPLATE_DIR',  './templates/' );
define( 'TEMPLATE_C_DIR', '../templates_c/' );


require_once SMARTY_DIR . 'Smarty.class.php';

$smarty = new Smarty;


$skin_name = $_SESSION["stylefile"];


//$smarty->template_dir = TEMPLATE_DIR . $skin_name.'/';
$smarty->template_dir = TEMPLATE_DIR . $skin_name.'/';

$smarty->compile_dir = TEMPLATE_C_DIR;
$smarty->plugins_dir= "./plugins/";

$smarty->assign("TEXTCONTACT", TEXTCONTACT);
$smarty->assign("EMAILCONTACT", EMAILCONTACT);
$smarty->assign("COPYRIGHT", COPYRIGHT);
$smarty->assign("CCMAINTITLE", CCMAINTITLE);
$smarty->assign("WEBUI_VERSION", WEBUI_VERSION);
$smarty->assign("WEBUI_DATE", WEBUI_DATE);

$smarty->assign("SKIN_NAME", $skin_name);

// for menu

$smarty->assign("ACXCUSTOMER", $ACXCUSTOMER);
$smarty->assign("ACXBILLING", $ACXBILLING);
$smarty->assign("ACXRATECARD", $ACXRATECARD);
$smarty->assign("ACXTRUNK", $ACXTRUNK);
$smarty->assign("ACXDID", $ACXDID);
$smarty->assign("ACXCALLREPORT", $ACXCALLREPORT);
$smarty->assign("ACXCRONTSERVICE", $ACXCRONTSERVICE);
$smarty->assign("ACXSIGNUP", $ACXSIGNUP);
$smarty->assign("ACXADMINISTRATOR", $ACXADMINISTRATOR);
$smarty->assign("ACXFILEMANAGER", $ACXFILEMANAGER);

$section = "";
if($_GET["section"]!="")
{
	$section = $_GET["section"];		
}
$smarty->assign("section", $section);


// OPTION FOR THE MENU
$smarty->assign("A2Bconfig", $A2B->config);

$smarty->assign("PAGE_SELF", $PHP_SELF);

?>
