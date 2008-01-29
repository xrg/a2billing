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

require_once (DIR_COMMON."SessionInvoice.inc.php");
$menu_section='menu_payments';

//HelpElem::DoHelp(gettext("Agents, callshops. <br>List or manipulate agents, which can deliver cards to customers."));

$sess_row=false;
// First, query for the session:
{
	$dbg_elem = new DbgElem();
	$dbhandle = A2Billing::DBHandle();
		
	if ($FG_DEBUG>0)
		$PAGE_ELEMS[] = &$dbg_elem;

	$sessqry = "SELECT is_open, sid, booth, card, is_inuse, credit, ".
		   " ( duration >= interval '1 day') AS has_days, ".
		   str_dbparams($dbhandle," format_currency(credit,%1) AS credit_fmt ",array(A2Billing::instance()->currency)).
		   " FROM cc_shopsession_status_v " .
		   " WHERE agentid = ".$_SESSION['agent_id'] ;
	
	if (isset($_GET['booth']))
		$sessqry .= str_dbparams($dbhandle,' AND booth = %#1 ', array($_GET['booth']));
	elseif (isset($_GET['sid']))
		$sessqry .= str_dbparams($dbhandle,' AND sid = %#1 ', array($_GET['sid']));

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

if ($sess_row)
	AgentSessionInvoice($sess_row,ACX_ACCESS,"booths.php");

require("PP_page.inc.php");

?>
