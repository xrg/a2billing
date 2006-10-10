<?php
	session_start();
?>
<?php
error_reporting( E_ALL - E_NOTICE );

include (dirname(__FILE__)."/company_info.php");

define( 'FULL_PATH', dirname(__FILE__) . '/' );
define( 'SMARTY_DIR', FULL_PATH . '/Smarty/' );
define( 'TEMPLATE_DIR',  './templates/' );
define( 'TEMPLATE_C_DIR', './templates_c/' );

require_once SMARTY_DIR . 'Smarty.class.php';

$smarty = new Smarty;

$skin_name = "default";

$smarty->template_dir = TEMPLATE_DIR . $skin_name.'/';
$smarty->compile_dir = TEMPLATE_C_DIR;
$smarty->plugins_dir= "./plugins/";

$smarty->assign("TEXTCONTACT", TEXTCONTACT);
$smarty->assign("EMAILCONTACT", EMAILCONTACT);
$smarty->assign("COPYRIGHT", COPYRIGHT);
$smarty->assign("CCMAINTITLE", CCMAINTITLE);
$smarty->assign("WEBUI_VERSION", WEBUI_VERSION);
$smarty->assign("WEBUI_DATE", WEBUI_DATE);

// OPTION FOR THE MENU
$smarty->assign("A2Bconfig", $A2B->config);

if($_SESSION["stylefile"]!= "")
{
	$smarty->assign("CSS_NAME", $_SESSION["stylefile"]);
}
else
{
	$smarty->assign("CSS_NAME", "");
}

$smarty->assign("PAGE_SELF", $PHP_SELF);
?>
