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

$menu_section='menu_config';

HelpElem::DoHelp(_("Time periods are named zones of time intervals."));

$HD_Form= new FormHandler('time_periods',_("Periods"),_("Period"));
$HD_Form->checkRights(ACX_MISC);
$HD_Form->default_order='id';
$HD_Form->default_sens='ASC';
$HD_Form->init();

$PAGE_ELEMS[] = &$HD_Form;
$PAGE_ELEMS[] = new AddNewButton($HD_Form);

$status_list = array();
$status_list[] = array('1',gettext("Active"));
$status_list[] = array('0',gettext("Inactive"));

$percity_list = array();
$percity_list[] = array('weekly',gettext("Weekly"));
$percity_list[] = array('daily',gettext("Daily"));
$percity_list[] = array('monthly',gettext("Monthly"));
$percity_list[] = array('yearly',gettext("Yearly"));

$HD_Form->model[] = new PKeyFieldEH(_("ID"),'id');
$HD_Form->model[] = new TextFieldEH(_("Name"),'name');
$HD_Form->model[] = dontAdd(new BoolField(_("Enabled"),'enabled',_("If true, the time period will be considered valid and cached.")));

$HD_Form->model[] = new TextAreaField(_("Comment"),'comment');

$tmp = new RevRefForm(_("Intervals"),'ints','id','time_period_interval','idtp');
$HD_Form->meta_elems[] = $tmp;
	$tmp->Form->checkRights(ACX_MISC);
	$tmp->Form->init();
	$tmp->Form->model[] = new PKeyFieldEH(_("ID"),'id');
	$tmp->Form->model[] = new RefField(_("Periodicity"), "percity", $percity_list/*,_("")*/);

		//TODO: special "interval" fields:
	$tmp->Form->model[]= new TextField(_("Start"),'istart',_('Starting point. See Manual for syntax.'));
	$tmp->Form->model[]= new TextField(_("End"),'iend',_('End point'));
	$tmp->Form->model[] = new DelBtnField();
	$tmp->Form->meta_elems[] = new AddNewButton($tmp->Form);

$HD_Form->model[] = new DelBtnField();


require("PP_page.inc.php");
?>
