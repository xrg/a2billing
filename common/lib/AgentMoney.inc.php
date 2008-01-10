<?php
require_once (DIR_COMMON."Form/Class.ListSumView.inc.php");
require_once (DIR_COMMON."Form/Class.SumMultiView.inc.php");
require_once (DIR_COMMON."Class.SqlActionElem.inc.php");
require_once (DIR_COMMON."Form/Class.SqlActionForm.inc.php");

function AgentMoney($agentid,&$sel_form,$intl, $rights){
	global $PAGE_ELEMS;
	global $FG_DEBUG;
	$dbhandle = A2Billing::DBHandle();
	if($intl)
		$view_name = 'cc_agent_money_vi';
	else
		$view_name = 'cc_agent_money_v';
	$HD_Form= new FormHandler($view_name,_("Transactions"),_("Transaction"));
	$HD_Form->checkRights($rights);
	$HD_Form->init(null,false);
	$HD_Form->views['list'] = new SumMultiView();
	$HD_Form->views['pay'] = $HD_Form->views['true'] =
		$HD_Form->views['false'] = new IdleView();
		
	if ($FG_DEBUG)
		$HD_Form->views['dump-form'] = new DbgDumpView();
	
	$PAGE_ELEMS[] = &$HD_Form;
	
	$HD_Form->model[] = new ClauseField('agentid',$agentid);
	$HD_Form->model[] = new DateTimeField(_("Date"),'date');
// 	if ($intl)
	
	$HD_Form->model[] = new TextField(_("Type"),'pay_type');
	end($HD_Form->model)->fieldexpr = 'gettexti(pay_type,\'C\')';
	
	$HD_Form->model[] = new TextField(_("Description"),'descr');
	$HD_Form->model[] = new MoneyField(_("In"),'pos_credit');
	$HD_Form->model[] = new MoneyField(_("Out"),'neg_credit');
	$HD_Form->model[] = new MoneyField(_("Sum"),'credit');
	$HD_Form->views['list']->sums[]= array( 'group' => false,
		'fns' => array('date' => true, 'pay_type' =>true, 'descr' =>true,
		 'pos_credit' => true, 'neg_credit' => true));
	$HD_Form->views['list']->sums[]= array(
		'fns' => array('descr' =>array(_("Totals")),
		 'pos_credit' => 'SUM', 'neg_credit' => 'SUM'));
	$HD_Form->views['list']->sums[]= array(
		'fns' => array('descr' =>array(_("Sum Total")),
		 'credit' => 'SUM'));

	$Totals = new SqlDetailsActionForm();
	$Totals->checkRights($rights);
	$Totals->init();
	$Totals->setAction('true');

	$PAGE_ELEMS[] = &$Totals;
	$Totals->expectRows = true;
	
	$cardsqr = "SELECT SUM(CASE WHEN credit > 0.0 THEN credit ELSE NULL END) AS pos_credit,
			SUM(CASE WHEN credit < 0.0 THEN (0.0 - credit) ELSE NULL END) AS neg_credit,
			SUM(creditlimit) AS climit
			FROM cc_card, cc_card_group
			WHERE cc_card.grp = cc_card_group.id AND cc_card_group.agentid IS NOT NULL
			AND agentid = %1";

	$callsqr = "SELECT SUM(sessionbill) AS calls 
			FROM cc_call, cc_card, cc_card_group 
			WHERE cc_call.cardid = cc_card.id AND cc_card_group.id = cc_card.grp
				AND cc_card_group.agentid = %1 ". $dc2 ;

	$dleftqr = "cc_calc_daysleft(%1,now(), interval '1 month')";
	
	$Totals->QueryString = str_dbparams($dbhandle, "SELECT format_currency(tc.pos_credit, %2) AS total_ccredit,
		 format_currency(tc.neg_credit, %2) AS total_cdebit, " .
		"format_currency(tc.climit, %2) AS total_cclimit,
		format_currency(sb.calls,%2) AS total_calls, format_currency((sb.calls * cc_agent.commission), %2) AS total_com,
		format_currency((sb.calls * (1.0 - cc_agent.commission)), %2) AS total_wh,
		format_currency(cc_agent.credit, %2) AS agent_credit, format_currency(cc_agent.climit, %2) AS climit, dleft.days_left
		  FROM ($cardsqr) AS tc, ($callsqr) AS sb, cc_agent, $dleftqr AS dleft
		  WHERE cc_agent.id = %1;",
		array($agentid,A2Billing::instance()->currency));
	$Totals->noRowsString =  _("Totals could not be calculated!");
	$Totals->rmodel[] = new MoneyField(_("Total sum credited to customers"),'total_ccredit');
	$Totals->rmodel[] = new MoneyField(_("Total sum debited from customers"),'total_cdebit');
	$Totals->rmodel[] = new MoneyField(_("Total potential debit from customers"),'total_cclimit');
	$Totals->rmodel[] = new IntField(_("Total calls made by customers"),'total_calls');
	$Totals->rmodel[] = new MoneyField(_("Wholesale price of calls"),'total_wh');
	$Totals->rmodel[] = new MoneyField(_("Estimated profit from calls"),'total_com');
	$Totals->rmodel[] = new MoneyField(_("Outstanding balance"),'agent_credit');
	$Totals->rmodel[] = new MoneyField(_("Credit Limit"),'climit');
	$Totals->rmodel[] = new IntField(_("Estimated Days left"),'days_left');

// if ($vat>0) echo  " (" .gettext("includes VAT"). "$vat %)";

}

?>