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

// Init the class early, so that ACL message appears here.
$sform= new FormHandler('nm.attr_float',_("Values"),_("Value"));
$sform->checkRights(ACX_NETMON);
$sform->init(null,false);

$SEL_Form = new SelectionForm();
$SEL_Form->init();
$SEL_Form->model[] = new DateTimeField(_("Period from"),'date_from');
	//end($SEL_Form->model)->does_add = false;
	end($SEL_Form->model)->def_date = '00:00 - 7 days';
	end($SEL_Form->model)->fieldexpr = 'tstamp';
$SEL_Form->model[] = new DateTimeField(_("Period to"),'date_to');
	//end($SEL_Form->model)->does_add = false;
	end($SEL_Form->model)->def_date = 'now';
	end($SEL_Form->model)->fieldexpr = 'tstamp';

$SEL_Form->fallbackClause=array('date_from');
/*$SEL_Form->model[] = new SqlRefFieldN(_("Agent"),'agentid','cc_agent','id','name');
	end($SEL_Form->model)->does_add = false;*/

$SEL_Form->search_exprs['date_from'] = '>=';
$SEL_Form->search_exprs['date_to'] = '<=';

//$SEL_Form->model[] = new SqlRefField(_("System"),'sysid','nm.system','id','name');
$SEL_Form->model[] = new SqlRefField(_("Node"),"par_id", "nm.attr_node", "id","name");
	end($SEL_Form->model)->combofield="id|| '. ' || name";

$PAGE_ELEMS[] = &$SEL_Form;

// HelpElem::DoHelp(gettext("*-*"),'vcard.png');

$sform->views['idle'] = new IdleView();
if ($sform->getAction()=='list')
	$sform->setAction('idle');

$sform->views['sums'] = new SumMultiView();

if ($FG_DEBUG)
	$sform->views['dump-form'] = new DbgDumpView();

$SEL_Form->appendClauses($sform);

$sform->model[] = new DateTimeField(_("Date"),'tstamp');
	end($sform->model)->fieldexpr='date_trunc(\'hour\', tstamp)';

$sform->model[] = new FloatField(_("Value"),'value');


/*$sform->model[] = new SecondsField(_("Average Length of Calls"), "aloc");
	end($sform->model)->fieldacr = _("ALOC");
	end($sform->model)->fieldexpr= 'sessiontime';
*/
	
/*
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
*/

$sform->views['sums']->plots['line']= array('title' => _("Per day values"), /*'subtitles' => _("Sum of Session time"),*/
	'type' => 'line', 'limit' => 100,
	ylegend => _('Value'),
	x => 'tstamp', y => 'value',
	'fns' => array( 'tstamp' =>true, 'value' => 'AVG'),
	'order' => 'date_trunc(\'hour\',tstamp)');



$sform->views['lineplot'] = new LineView('sums','line', 'style-ex1');

$PAGE_ELEMS[] = &$sform;
$PAGE_ELEMS[] = $sform->GraphUrl('lineplot');

if (!empty($_GET['graph']))
	require("PP_graph.inc.php");
else
	require("PP_page.inc.php");

?>