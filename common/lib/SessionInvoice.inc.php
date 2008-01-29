<?php
/** One function that sets up page elems for an agent-session invoice */

function AgentSessionInvoice($sess_row,$rights,$booth_page){
	global $PAGE_ELEMS;
	global $FG_DEBUG;
	$dbhandle=A2Billing::DBHandle();
	$HD_Form= new FormHandler('cc_session_invoice',_("Transactions"),_("Transaction"));
	$HD_Form->checkRights($rights);
	$HD_Form->init(null,false);
	$HD_Form->views['list'] = new ListSumView();
	$HD_Form->views['pay'] = $HD_Form->views['true'] =
		$HD_Form->views['false'] = new IdleView();
		
	if ($FG_DEBUG)
		$HD_Form->views['dump-form'] = new DbgDumpView();
	
	$PAGE_ELEMS[] = &$HD_Form;
	
	$HD_Form->model[] = new ClauseField('sid',$sess_row['sid']);
	$HD_Form->model[] = new DateTimeField(_("Date"),'starttime');
	
	
	$HD_Form->model[] = new TextField(_("Description"),'descr');
		end($HD_Form->model)->fieldacr = _("Descr");
	$HD_Form->model[] = new TextField("",'f2');
	$HD_Form->model[] = new TextField(_("Called Number"),'cnum');
		end($HD_Form->model)->fieldacr = _("C. Num");
	//end($HD_Form->model)->fieldname ='agent';
	
	$HD_Form->model[] = new SecondsField(_("Duration"), "duration");
		end($HD_Form->model)->fieldacr = _("Dur");
	$HD_Form->model[] = new MoneyField(_("Credit"), "pos_charge");
	$HD_Form->model[] = new MoneyField(_("Charge"), "neg_charge");
	
	$HD_Form->views['list']->sum_fns= array('duration' => 'SUM', 'pos_charge' => 'SUM', 'neg_charge' => 'SUM');
	
	
	// Per date calls..
	$Sum_Form= new FormHandler('cc_session_calls',_("Per-date calls"));
	$Sum_Form->checkRights($rights);
	$Sum_Form->init(null,false);
	$Sum_Form->views['list'] = new SumMultiView();
	$Sum_Form->views['pay'] = $Sum_Form->views['true'] =
		$Sum_Form->views['false']= new IdleView();
	if ($FG_DEBUG)
		$Sum_Form->views['dump-form'] = new DbgDumpView();
	
	$PAGE_ELEMS[] = &$Sum_Form;
	
	$Sum_Form->model[] = new ClauseField('sid',$sess_row['sid']);
	$Sum_Form->model[] = new DateField(_("Date"),'starttime');
	end($Sum_Form->model)->fieldexpr='date_trunc(\'day\', starttime)';
	
	
	$Sum_Form->model[] = new IntField(_("Calls"),'cnum');
	
	$Sum_Form->model[] = new SecondsField(_("Duration"), "duration");
	//$Sum_Form->model[] = new FloatField(_("Credit"), "pos_charge");
	$Sum_Form->model[] = new MoneyField(_("Charge"), "neg_charge");
	
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
		if ($sess_row['credit'] > 0 ){
			$pay_form->ButtonStr = str_params(_("Pay back %1"),array($sess_row['credit_fmt']),1);
			$pay_form->elem_success=new StringElem(_("Sesion paid back!"));
		}else {
			$pay_form->ButtonStr = str_params(_("Pay %1"),array($sess_row['credit_fmt']),1);
			$pay_form->elem_success=new StringElem(_("Sesion paid!"));
		}
		$pay_form->follow_params['sum'] = $sess_row['credit'];
		$pay_form->follow_params['sid'] = $sess_row['sid'];
		$pay_form->QueryString = str_dbparams($dbhandle, 'SELECT pay_session(%1, %2, true) AS money;',
			array($sess_row['sid'], $_GET['sum']));
		$pay_form->elem_fail=new StringElem(_("Session could not be paid!"));
		
		$pay_form->elem_success->content .= "\n<br><a href=\"$booth_page\">" .
			_("Back to booths")."</a>";
	}
}
?>