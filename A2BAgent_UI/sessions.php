<?php
require_once ("./lib/defines.php");
require_once ("./lib/module.access.php");
require_once (DIR_COMMON."Form.inc.php");
require_once (DIR_COMMON."Class.HelpElem.inc.php");
require_once (DIR_COMMON."Form/Class.SqlRefField.inc.php");
require_once (DIR_COMMON."Form/Class.TimeField.inc.php");
require_once (DIR_COMMON."Form/Class.SelectionForm.inc.php");
require_once (DIR_COMMON."Form/Class.ClauseField.inc.php");

$menu_section='menu_reports';


HelpElem::DoHelp(gettext("Callshop essions. Each session starts when the customer enters a booth and ends when he leaves (preferably having paid the account)."));

$HD_Form= new FormHandler('cc_shopsessions',_("Sessions"),_("Session"));
$HD_Form->checkRights(ACX_ACCESS);
$HD_Form->default_order = 'id';
$HD_Form->default_sens = 'DESC';
$HD_Form->init(null,false);
$HD_Form->views['list']=new ListView();
$HD_Form->views['details'] = new DetailsView();

// $PAGE_ELEMS[] = new AddNewButton($HD_Form); No, we don't open them this way!

$HD_Form->model[] = new PKeyField(_("ID"),'id');
$HD_Form->model[] = new FreeClauseField('booth IN (SELECT id FROM cc_booth ' .
	' WHERE agentid = \''.$_SESSION['agent_id'].'\')');
$HD_Form->model[] = new DateTimeField(_("Start"),'starttime');
$HD_Form->model[] = new DateTimeField(_("End"),'endtime');

$HD_Form->model[] = new TextField(_("State"),'state');

$HD_Form->model[] = new SqlRefField(_("Booth"), "booth","cc_booth", "id", "name");
$HD_Form->model[] = new SqlBigRefField(_("Card"), "card","cc_card", "id", "username");

$detbtn = new OtherBtnField();
$detbtn->title = _("Invoice");
$detbtn->url = "invoices_cshop.php?";
$detbtn->extra_params = array('sid' => 'id');
$HD_Form->model[] = &$detbtn;

//$HD_Form->model[] = new DelBtnField();

$SEL_Form = new SelectionForm();
$SEL_Form->init();
$SEL_Form->model[] = new DateTimeField(_("Period from"),'date_from');
	end($SEL_Form->model)->does_add = false;
	end($SEL_Form->model)->def_date = '00:00 last month';
	end($SEL_Form->model)->fieldexpr = 'starttime';
$SEL_Form->model[] = new DateTimeField(_("Period to"),'date_to');
	end($SEL_Form->model)->does_add = false;
	end($SEL_Form->model)->def_date = 'now';
	end($SEL_Form->model)->fieldexpr = 'starttime';
$SEL_Form->search_exprs['date_from'] = '>=';
$SEL_Form->search_exprs['date_to'] = '<=';
//$CS_Form->agentid=$SEL_Form->getpost_single('agentid');

$PAGE_ELEMS[] = &$SEL_Form;
$PAGE_ELEMS[] = &$HD_Form;

$clauses = $SEL_Form->buildClauses();
// 	$PAGE_ELEMS[] = new DbgElem(print_r($clauses,true));
foreach ($clauses as $clause)
	$HD_Form->model[] = new FreeClauseField($clause);

require("PP_page.inc.php");

?>