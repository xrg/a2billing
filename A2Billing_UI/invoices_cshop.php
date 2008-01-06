<?php
require_once ("./lib/defines.php");
require_once ("./lib/module.access.php");
require_once (DIR_COMMON."Form.inc.php");
// require_once (DIR_COMMON."Class.HelpElem.inc.php");
// require_once (DIR_COMMON."Form/Class.SqlRefField.inc.php");
require_once (DIR_COMMON."Form/Class.TimeField.inc.php");
require_once (DIR_COMMON."Form/Class.ClauseField.inc.php");
require_once (DIR_COMMON."Form/Class.ListSumView.inc.php");
require_once (DIR_COMMON."Form/Class.SumMultiView.inc.php");

require_once (DIR_COMMON."Class.SqlActionElem.inc.php");

$menu_section='menu_agents';

//HelpElem::DoHelp(gettext("Agents, callshops. <br>List or manipulate agents, which can deliver cards to customers."));

$sess_row=false;
// First, query for the session:
{
	$dbg_elem = new DbgElem();
	$dbhandle = A2Billing::DBHandle();
		
	if ($FG_DEBUG>0)
		$PAGE_ELEMS[] = &$dbg_elem;

	$sessqry = "SELECT is_open, sid, agentid, booth, card, is_inuse, credit, ".
		   " ( duration >= interval '1 day') AS has_days, ".
		   str_dbparams($dbhandle," format_currency(credit,%1) AS credit_fmt ",array(A2Billing::instance()->currency)).
		   " FROM cc_shopsession_status_v ";
	
	if (isset($_GET['booth']))
		$sessqry .= str_dbparams($dbhandle,' WHERE booth = %#1 ', array($_GET['booth']));
	elseif (isset($_GET['sid']))
		$sessqry .= str_dbparams($dbhandle,' WHERE sid = %#1 ', array($_GET['sid']));

	$sessqry .=' ORDER BY sid DESC LIMIT 1;';
	
	if ($FG_DEBUG >2)
		$dbg_elem->content .= "Query: ".$sessqry ."\n";
	$sess_res = $dbhandle->Execute($sessqry);
	if (!$sess_res){
		$dbg_elem->content .= $dbhandle->ErrorMsg();
		$PAGE_ELEMS[] = new ErrorElem(_("Cannot locate session!"));
	
	}elseif ($sess_res->EOF){
		$dbg_elem->content .= "No data found!";
		$PAGE_ELEMS[] = new ErrorElem(_("Cannot locate session!"));
	}
	else
		$sess_row = $sess_res->fetchRow();
	
	
}

if ($sess_row){
	$HD_Form= new FormHandler('cc_session_invoice',_("Transactions"),_("Transaction"));
	$HD_Form->checkRights(ACX_AGENTS);
	$HD_Form->init(null,false);
	$HD_Form->views['list'] = new ListSumView();
	$HD_Form->views['pay'] = $HD_Form->views['true'] =
		$HD_Form->views['false'] = new IdleView();
		
	if ($FG_DEBUG)
		$HD_Form->views['dump-form'] = new DbgDumpView();
	
	$PAGE_ELEMS[] = &$HD_Form;
	
	$HD_Form->model[] = new ClauseField('sid',$sess_row['sid']);
	$HD_Form->model[] = new DateTimeField(_("Time"),'starttime');
	
	
	$HD_Form->model[] = new TextField(_("Description"),'descr');
	$HD_Form->model[] = new TextField("",'f2');
	$HD_Form->model[] = new TextField(_("Called Number"),'cnum');
	//end($HD_Form->model)->fieldname ='agent';
	
	$HD_Form->model[] = new IntField(_("Duration"), "duration");
	$HD_Form->model[] = new MoneyField(_("Credit"), "pos_charge");
	$HD_Form->model[] = new MoneyField(_("Charge"), "neg_charge");
	
	$HD_Form->views['list']->sum_fns= array('duration' => 'SUM', 'pos_charge' => 'SUM', 'neg_charge' => 'SUM');
	
	
	// Per date calls..
	$Sum_Form= new FormHandler('cc_session_calls',_("Per-date calls"));
	$Sum_Form->checkRights(ACX_AGENTS);
	$Sum_Form->init(null,false);
	$Sum_Form->views['list'] = new SumMultiView();
	$Sum_Form->views['pay'] = $Sum_Form->views['true'] =
		$Sum_Form->views['false']= new IdleView();
	if ($FG_DEBUG)
		$Sum_Form->views['dump-form'] = new DbgDumpView();
	
	$PAGE_ELEMS[] = &$Sum_Form;
	
	$Sum_Form->model[] = new ClauseField('sid',$sess_row['sid']);
	$Sum_Form->model[] = new DateTimeField(_("Date"),'starttime');
	
	
	$Sum_Form->model[] = new IntField(_("Calls"),'cnum');
	
	$Sum_Form->model[] = new IntField(_("Duration"), "duration");
	//$Sum_Form->model[] = new FloatField(_("Credit"), "pos_charge");
	$Sum_Form->model[] = new FloatField(_("Charge"), "neg_charge");
	
	if ($sess_row['has_days'] == 't')
		$Sum_Form->views['list']->sums[] = array('title' => _("Per day calls"),
			'fns' => array( 'starttime' =>true, 'cnum' => 'COUNT',
				'duration' => 'SUM', 'neg_charge' => 'SUM'));
	
	$Sum_Form->views['list']->sums[] = array('title' => _("Total"),
		'fns' => array( 'cnum' => 'COUNT',
			'duration' => 'SUM', 'neg_charge' => 'SUM'));
			
	if ($sess_row['is_open'] != 't')
		$PAGE_ELEMS[] = new StringElem(_("Session is closed"));
	elseif ($sess_row['is_inuse'] == 't')
		$PAGE_ELEMS[] = new StringElem(_("Card is in use, cannot close session now."));
	else{
		$pay_form = new SqlActionElem();
		$pay_form->action_do = 'pay';
		$pay_form->action_ask = 'list';
		$pay_form->init();
		$PAGE_ELEMS[] = &$pay_form;
		$pay_form->ButtonStr = str_params(_("Pay %1"),array($sess_row['credit_fmt']),1);
		$pay_form->follow_params['sum'] = $sess_row['credit'];
		$pay_form->follow_params['sid'] = $sess_row['sid'];
		$pay_form->QueryString = str_dbparams($dbhandle, 'SELECT pay_session(%1, %2, true) AS money;',
			array($sess_row['sid'], $_GET['sum']));
	}
} //sess_row

require("PP_page.inc.php");

?>
