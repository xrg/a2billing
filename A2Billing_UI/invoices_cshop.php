<?php
require_once ("./lib/defines.php");
require_once ("./lib/module.access.php");
require_once (DIR_COMMON."Form.inc.php");
// require_once (DIR_COMMON."Class.HelpElem.inc.php");
// require_once (DIR_COMMON."Form/Class.SqlRefField.inc.php");
require_once (DIR_COMMON."Form/Class.TimeField.inc.php");
require_once (DIR_COMMON."Form/Class.ListSumView.inc.php");

$menu_section='menu_agents';

//HelpElem::DoHelp(gettext("Agents, callshops. <br>List or manipulate agents, which can deliver cards to customers."));

$HD_Form= new FormHandler('cc_session_invoice',_("Transactions"),_("Transaction"));
$HD_Form->checkRights(ACX_AGENTS);
$HD_Form->init(null,false);
$HD_Form->views['list'] = new ListSumView();
if ($FG_DEBUG)
	$HD_Form->views['dump-form'] = new DbgDumpView();

$PAGE_ELEMS[] = &$HD_Form;

// $HD_Form->model[] = new PKeyFieldEH(_("ID"),'id');
$HD_Form->model[] = new DateTimeField(_("Time"),'starttime');


$HD_Form->model[] = new TextField(_("Description"),'descr');
$HD_Form->model[] = new TextField("",'f2');
$HD_Form->model[] = new TextField(_("Called Number"),'cnum');
//end($HD_Form->model)->fieldname ='agent';

$HD_Form->model[] = new IntField(_("Duration"), "duration");
$HD_Form->model[] = new FloatField(_("Credit"), "pos_charge");
$HD_Form->model[] = new FloatField(_("Charge"), "neg_charge");

$HD_Form->views['list']->sum_fns= array('duration' => 'SUM', 'pos_charge' => 'SUM', 'neg_charge' => 'SUM');
require("PP_page.inc.php");

?>
