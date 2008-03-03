<?php
require_once ("./lib/defines.php");
require_once ("./lib/module.access.php");
require_once (DIR_COMMON."Form.inc.php");
require_once (DIR_COMMON."Class.HelpElem.inc.php");
require_once (DIR_COMMON."Form/Class.RefField.inc.php");
require_once (DIR_COMMON."Form/Class.TimeField.inc.php");
require_once (DIR_COMMON."Form/Class.SqlRefField.inc.php");

$menu_section='menu_cront';
HelpElem::DoHelp(_("Data from run or pending alarms."));

$HD_Form= new FormHandler('cc_alarm_run',_("Alarm Data"),_("Alarm Data"));
$HD_Form->checkRights(ACX_CRONT_SERVICE);
$HD_Form->default_order='id';
$HD_Form->default_sens='DESC';
$HD_Form->init();

$PAGE_ELEMS[] = &$HD_Form;
$PAGE_ELEMS[] = new AddNewButton($HD_Form);

$HD_Form->model[] = new PKeyFieldEH(_("ID"),'id');
$HD_Form->model[] = new SqlRefField(_("Alarm"),'alid','cc_alarm','id','name');
$HD_Form->model[] = dontAdd(dontList(new DateTimeField(_("Creation"), "tcreate", _("Creation date"))));
$HD_Form->model[] = dontAdd(new DateTimeField(_("Modify"), "tmodify", _("Last modification date")));

$mstates = array();
$mstates[] = array(0,_("Unknown"));
$mstates[] = array(1,_("Run"));
$mstates[] = array(2,_("Failed to run"));
$mstates[] = array(3,_("Raised Error"));
$mstates[] = array(10,_("Request to run"));

$HD_Form->model[] = new RefField(_("Status"),'status', $mstates);
	end($HD_Form->model)->def_value=10;

$HD_Form->model[] = dontList(new TextAreaField(_("Parameters"),'params'));

$HD_Form->model[] = new DelBtnField();

require("PP_page.inc.php");
?>
