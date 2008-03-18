<?php
require_once ("./lib/defines.php");
require_once ("./lib/module.access.php");
require_once (DIR_COMMON."Form.inc.php");
require_once (DIR_COMMON."Class.HelpElem.inc.php");
/*require_once (DIR_COMMON."Form/Class.RevRef.inc.php");*/
require_once (DIR_COMMON."Form/Class.TimeField.inc.php");
require_once (DIR_COMMON."Form/Class.SqlRefField.inc.php");
require_once (DIR_COMMON."Provi/Class.ProvisionActionForm.inc.php");

$menu_section='menu_sipiax';
if(!DynConf::GetCfg(CUSTOMER_CFG,'menu_sipiax',true))
	exit();

HelpElem::DoHelp(_("Here you can get example settings you can use in your devices. Select the kind of device (phone) you have and settings will appear."),'phone.png');

$pr_list = array();
$pr_list[]  = array("0", _("Asterisk sip friend"),'ast-ini','sip-peer');
$pr_list[]  = array("1", _("Asterisk iax friend"),'ast-ini','iax-peer');

$HD_Form= new ProvisionActionForm();
$HD_Form->checkRights(ACX_ACCESS);
$HD_Form->init($pr_list);
$HD_Form->setArg('cardid' ,$_SESSION['card_id']);

$PAGE_ELEMS[] = &$HD_Form;


require("PP_page.inc.php");
?>
