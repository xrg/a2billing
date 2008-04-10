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
// require_once (DIR_COMMON."Form/Class.ClauseField.inc.php");
require_once (DIR_COMMON."Form/Class.SelectionForm.inc.php");
require_once (DIR_COMMON."Form/Class.SumMultiView.inc.php");
require_once (DIR_COMMON."Form/Class.ElemGraph.inc.php");

$menu_section='menu_creport';

$SEL_Form = new SelectionForm();
$SEL_Form->init();
$SEL_Form->model[] = new DateTimeField(_("Period from"),'date_from');
	end($SEL_Form->model)->does_add = false;
	end($SEL_Form->model)->def_date = '00:00 - 9 days';
	end($SEL_Form->model)->fieldexpr = 'starttime';
$SEL_Form->model[] = new DateTimeField(_("Period to"),'date_to');
	end($SEL_Form->model)->does_add = false;
	end($SEL_Form->model)->def_date = 'now';
	end($SEL_Form->model)->fieldexpr = 'starttime';

$SEL_Form->fallbackClause=array('date_from');
/*$SEL_Form->model[] = new SqlRefFieldN(_("Agent"),'agentid','cc_agent','id','name');
	end($SEL_Form->model)->does_add = false;*/

$SEL_Form->search_exprs['date_from'] = '>=';
$SEL_Form->search_exprs['date_to'] = '<=';

$SEL_Form->model[] = new TextSearchField(_("Destination"),'destination');
$SEL_Form->model[] = new TextSearchField(_("Called number"),'calledstation');
/*$SEL_Form->model[] = new SqlRefField(_("Plan"),'idrp','cc_retailplan','id','name', _("Retail plan"));
	end($SEL_Form->model)->does_add = false;*/
$SEL_Form->model[] =dontAdd(new SqlRefFieldN(_("Agent"),'agid','cc_agent','id','name'));
	end($SEL_Form->model)->fieldexpr='(SELECT agentid FROM cc_card,cc_card_group
		WHERE cc_card.grp = cc_card_group.id AND cc_card.id = cc_call_v.cardid)';
	end($SEL_Form->model)->combofield="name || ' ('|| login || ')'";

$SEL_Form->model[] =dontAdd(new SqlRefFieldN(_("Server"),'srvid','cc_a2b_server','id','host'));
$SEL_Form->model[] = dontAdd(new SqlRefFieldN(_("Trunk"),'trunk','cc_trunk','id','trunkcode',
		 _("Trunk used for the call")));

$PAGE_ELEMS[] = &$SEL_Form;

// HelpElem::DoHelp(gettext("*-*"),'vcard.png');

$sform= new FormHandler('cc_call_v',_("Calls"),_("Call"));
$sform->checkRights(ACX_CALL_REPORT);
$sform->init(null,false);
$sform->views['sums'] = new SumMultiView();
if ($sform->getAction()=='list')
	$sform->setAction('sums');

if ($FG_DEBUG)
	$sform->views['dump-form'] = new DbgDumpView();

$SEL_Form->appendClauses($sform);

$sform->model[] = new DateField(_("Date"),'starttime');
	end($sform->model)->fieldexpr='date_trunc(\'day\', starttime)';
$sform->model[] = new TextField(_("Destination"), "destination");
	end($sform->model)->fieldacr = _("Dest");
$sform->model[] =new SqlRefFieldN(_("Agent"),'agid','cc_agent','id','login');
	end($sform->model)->fieldexpr='(SELECT agentid FROM cc_card,cc_card_group
		WHERE cc_card.grp = cc_card_group.id AND cc_card.id = cc_call_v.cardid)';

$sform->model[] = new IntField(_("Calls"),'uniqueid');

$sform->model[] = new SecondsField(_("Duration"), "sessiontime");

$sform->model[] = new PercentField(_("Answer to Seizure Ratio"),'asr');
	end($sform->model)->fieldacr = _("ASR");
	end($sform->model)->fieldexpr= 'COUNT(CASE WHEN tcause = \'ANSWER\' THEN 1 ELSE null END)::FLOAT / COUNT(uniqueid)';

$sform->model[] = new SecondsField(_("Average Length of Calls"), "aloc");
	end($sform->model)->fieldacr = _("ALOC");
	end($sform->model)->fieldexpr= 'sessiontime';
	
	//$Sum_Form->model[] = new FloatField(_("Credit"), "pos_charge");
$sform->model[] = new MoneyField(_("Bill"), "sessionbill");
$sform->model[] = new MoneyField(_("Cost"), "buycost");

$sform->views['sums']->sums[] = array('title' => _("Per day calls"),
	'fns' => array( 'starttime' =>true, 'uniqueid' => 'COUNT',
		'sessiontime' => 'SUM', 'asr' => '', 'aloc' => 'AVG',
		'sessionbill' => 'SUM', 'buycost' => 'SUM'),
	'order' => 'date_trunc(\'day\',starttime)', 'sens' => 'DESC');
	

$sform->views['sums']->sums[] = array('title' => _("Per destination calls"),
	'fns' => array( 'destination' =>true, 'uniqueid' => 'COUNT',
		'sessiontime' => 'SUM', 'asr' => '', 'aloc' => 'AVG',
		'sessionbill' => 'SUM', 'buycost' => 'SUM'),
	'order' => 'COUNT(uniqueid)', 'sens' => 'DESC');

$sform->views['sums']->sums[] = array('title' => _("Per agent calls"),
	'fns' => array( 'agid' =>true, 'uniqueid' => 'COUNT',
		'sessiontime' => 'SUM', 'asr' => '', 'aloc' => 'AVG',
		'sessionbill' => 'SUM', 'buycost' => 'SUM'),
	'order' => 'COUNT(uniqueid)', 'sens' => 'DESC');

$sform->views['sums']->sums[] = array('title' => _("Total"),
	'fns' => array( 'uniqueid' => 'COUNT',
		'sessiontime' => 'SUM', 'asr' => '', 'aloc' => 'AVG',
		'sessionbill' => 'SUM', 'buycost' => 'SUM'),
	'order' => 'COUNT(uniqueid)', 'sens' => 'DESC');

$sform->views['sums']->plots['day']= array('title' => _("Per day calls"), 'subtitles' => _("Sum of Session time"),
	'type' => 'bar', 'limit' => 10,
	ylegend => _('Sum of Session time '),
	x => 'starttime', y => 'sessiontime',
	'fns' => array( 'starttime' =>true, 'sessiontime' => 'SUM'),
	'order' => 'date_trunc(\'day\',starttime)');

$sform->views['sums']->plots['dest'] = array('title' => _("Per destination calls"),
	x=>'destination', y=> 'sessiontime', ylabel => _(" seconds"), 'limit' => 20,
	'fns' => array( 'destination' =>true, 'sessiontime' => 'SUM' ),
	'order' => 'COUNT(uniqueid)', 'sens' => 'DESC');

$sform->views['day-bar'] = new BarView('sums','day', 'style-ex2');
$sform->views['dest-pie'] = new PieView('sums','dest', 'style-ex1');
$sform->views['day-line'] = new LineView('sums','day', 'style-ex1');


$PAGE_ELEMS[] = &$sform;
$PAGE_ELEMS[] = $sform->GraphUrl('day-bar');
$PAGE_ELEMS[] = $sform->GraphUrl('dest-pie');
$PAGE_ELEMS[] = $sform->GraphUrl('day-line');




if (!empty($_GET['graph']))
	require("PP_graph.inc.php");
else
	require("PP_page.inc.php");

?>