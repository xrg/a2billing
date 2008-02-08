<?php
require_once ("./lib/defines.php");
require_once ("./lib/module.access.php");
require_once (DIR_COMMON."Form.inc.php");
require_once (DIR_COMMON."Class.HelpElem.inc.php");
require_once (DIR_COMMON."Form/Class.RevRef.inc.php");
require_once (DIR_COMMON."Form/Class.TimeField.inc.php");
require_once (DIR_COMMON."Form/Class.SqlRefField.inc.php");
require_once (DIR_COMMON."Form/Class.RevRefForm.inc.php");
require_once (DIR_COMMON."Form/Class.TextSearchField.inc.php");
require_once (DIR_COMMON."Form/Class.ClauseField.inc.php");
require_once (DIR_COMMON."Form/Class.SelectionForm.inc.php");
require_once (DIR_COMMON."Form/Class.SumMultiView.inc.php");

$menu_section='menu_invoicing';

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

$SEL_Form->model[] = new SqlRefFieldN(_("Agent"),'agentid','cc_agent','id','name');
	end($SEL_Form->model)->does_add = false;

$SEL_Form->search_exprs['date_from'] = '>=';
$SEL_Form->search_exprs['date_to'] = '<=';

$PAGE_ELEMS[] = &$SEL_Form;

// HelpElem::DoHelp(gettext("*-*"),'vcard.png');

$sform= new FormHandler('cc_call, cc_card, cc_card_group',_("Calls"),_("Call"));
$sform->checkRights(ACX_INVOICING);
$sform->init(null,false);
$sform->views['sums'] = new SumMultiView();
$sform->setAction('sums');

if ($FG_DEBUG)
	$sform->views['dump-form'] = new DbgDumpView();

$SEL_Form->appendClauses($sform);

$sform->model[] = new FreeClauseField('cc_call.cardid = cc_card.id');
$sform->model[] = new FreeClauseField('cc_card.grp = cc_card_group.id');
$sform->model[] = new FreeClauseField('cc_call.invoice_id IS NULL');

$sform->model[] = new DateField(_("Start"),'starttime');
$sform->model[] = new DateField(_("End"),'endtime');
	end($sform->model)->fieldexpr = 'starttime';
$sform->model[] = new IntField(_("Calls"),'uniqueid');

$sform->model[] = new SecondsField(_("Duration"), "sessiontime");
	end($sform->model)->fieldacr = _("Dur");

	//$Sum_Form->model[] = new FloatField(_("Credit"), "pos_charge");
$sform->model[] = new MoneyField(_("Bill"), "sessionbill");
$sform->model[] = new MoneyField(_("Cost"), "buycost");

$sform->model[] = new TextField(_("Group"),'grp_name');
	end($sform->model)->fieldexpr = 'cc_card_group.name';
$sform->model[] = new SqlRefFieldN(_("Agent"),'agentid','cc_agent','id','name');


$sform->views['sums']->sums[] = array('title' => _("Per group calls"),
	'fns' => array( 'uniqueid' => 'COUNT',
		'starttime' => 'MIN', 'endtime' => 'MAX',
		'sessiontime' => 'SUM', 'sessionbill' => 'SUM', 'buycost' => 'SUM',
		'grp_name' => true, 'agentid_name' => true),
	'order' => 'cc_card_group.name');
	
$sform->views['sums']->sums[] = array('title' => _("Total"),
	'fns' => array( 'uniqueid' => 'COUNT',
		'starttime' => 'MIN', 'endtime' => 'MAX',
		'sessiontime' => 'SUM',	'sessionbill' => 'SUM', 'buycost' => 'SUM'));


$PAGE_ELEMS[] = &$sform;

require("PP_page.inc.php");

?>
