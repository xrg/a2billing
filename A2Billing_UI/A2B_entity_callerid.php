<?php
require_once ("./lib/defines.php");
require_once ("./lib/module.access.php");
require_once (DIR_COMMON."Form.inc.php");
require_once (DIR_COMMON."Class.HelpElem.inc.php");
require_once (DIR_COMMON."Form/Class.SqlRefField.inc.php");

$menu_section='menu_customers';

  
HelpElem::DoHelp(gettext("CallerID <br> Set the caller ID so that the customer calling in is authenticated on the basis of the callerID rather than with account number."));

$HD_Form= new FormHandler('cc_callerid',_("CallerIDs"),_("CallerID"));
$HD_Form->checkRights(ACX_CUSTOMER);
$HD_Form->init();

$PAGE_ELEMS[] = &$HD_Form;
$PAGE_ELEMS[] = new AddNewButton($HD_Form);

$HD_Form->model[] = new PKeyFieldEH(_("ID"),'id');
$HD_Form->model[] = new TextFieldEH(_("Caller ID"),'cid',_("Insert the CallerID"));
$HD_Form->model[] = new SqlBigRefField(_("CardNumber"), "cardid","cc_card", "id", "username");
	end($HD_Form->model)->SetRefEntity("A2B_entity_card.php");
	end($HD_Form->model)->SetRefEntityL("A2B_entity_card.php");
	end($HD_Form->model)->SetEditTitle("Card ID");

$actived_list = array();
$actived_list[] = array('t',gettext("Active"));
$actived_list[] = array('f',gettext("Inactive"));

$HD_Form->model[] = new RefField(_("ACTIVATED"), "activated", $actived_list,_("Allow the callerID to operate"),"4%");


$HD_Form->model[] = new DelBtnField();


require("PP_page.inc.php");

?>

