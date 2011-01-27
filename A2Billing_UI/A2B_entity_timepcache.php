<?php
require_once ("./lib/defines.php");
require_once ("./lib/module.access.php");
require_once ("a2blib/Form.inc.php");
require_once ("a2blib/Class.HelpElem.inc.php");
require_once ("a2blib/Form/Class.SqlRefField.inc.php");
// require_once ("a2blib/Form/Class.TabField.inc.php");
require_once ("a2blib/Form/Class.TimeField.inc.php");
require_once ("a2blib/Form/Class.RevRefForm.inc.php");

// require_once ("a2blib/Class.JQuery.inc.php");

$menu_section='menu_admin';

HelpElem::DoHelp(_("Time periods are named zones of time intervals."));

$HD_Form= new FormHandler('time_period_cache',_("Periods"),_("Period"));
$HD_Form->checkRights(ACX_ADMINISTRATOR);
$HD_Form->default_order='id';
$HD_Form->default_sens='DESC';
$HD_Form->init();

$PAGE_ELEMS[] = &$HD_Form;
$PAGE_ELEMS[] = new AddNewButton($HD_Form);
$HD_Form->model[] = dontList(new PKeyFieldEH(_("ID"),'id'));
$HD_Form->model[] = new SqlRefField(_("Period"), "idtp","time_periods", "id", "name");

$HD_Form->model[] = new DateTimeField(_("Start Date"), "tstart",_("Moment since which this value is valid."));
$HD_Form->model[] = new DateTimeField(_("End Date"), "tend",_("Moment until which this value is valid."));
$HD_Form->model[] = new BoolField(_("Active"),'status',_("Value of time period within this interval."));

$HD_Form->model[] = new GroupField(array(new EditBtnField(),new DelBtnField()));


require("PP_page.inc.php");
?>
