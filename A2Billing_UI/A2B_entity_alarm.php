<?php
require_once ("./lib/defines.php");
require_once ("./lib/module.access.php");
require_once ("a2blib/Form.inc.php");
require_once ("a2blib/Class.HelpElem.inc.php");
require_once ("a2blib/Form/Class.SqlRefField.inc.php");

$menu_section='menu_cront';
HelpElem::DoHelp(_("Alarms are various (periodical) checks on the system."));

$HD_Form= new FormHandler('cc_alarm',_("Alarms"),_("Alarm"));
$HD_Form->checkRights(ACX_CRONT_SERVICE);
$HD_Form->init();

$astatus = array();
$astatus[] = array(0,_("Inactive"));
$astatus[] = array(1,_("Active"));
$astatus[] = array(2,_("Suspended"));
// $astatus[] = array(3,_(""));

$atypes = array();
$atypes[] = array('test',_("Test"));
$atypes[] = array('agent-credit',_("Agents Credit"));
//$atypes[] = array('',_("New, halt"));

$PAGE_ELEMS[] = &$HD_Form;
$PAGE_ELEMS[] = new AddNewButton($HD_Form);

$HD_Form->model[] = new PKeyFieldEH(_("ID"),'id');
$HD_Form->model[] = new TextFieldEH(_("Name"),'name');
$HD_Form->model[] = new TextField(_("Period"),'period');
$HD_Form->model[] = new RefField(_("Type"),'atype', $atypes);
$HD_Form->model[] = new TextField(_("Subtype"),'asubtype');
$HD_Form->model[] = new RefField(_("State"),'status', $astatus);
$HD_Form->model[] = dontList(new TextField(_("Mail"),'tomail',_("Send mail there if needed.")));
$HD_Form->model[] = dontList(new TextAreaField(_("Parameters"),'aparams',_("Parameters to the alarm engine.")));

$HD_Form->model[] = new DelBtnField();

require("PP_page.inc.php");

?>
