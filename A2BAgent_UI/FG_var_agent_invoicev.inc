<?php

$plang=getenv("LANG");
getpost_ifset(array('Period','frommonth','tomonth','fromstatsmonth','tostatsmonth'));
if ($totaltext == "")
{
	$totaltext = 0;
}
$HD_Form = new FormHandler("cc_agent_invoices_v ","Invoice");

$HD_Form -> FG_TABLE_NAME = "cc_agent_invoices_v";

$HD_Form -> FG_TABLE_CLAUSE = "";
$HD_Form -> setDBHandler (DbConnect());

$HD_Form -> FG_TABLE_CLAUSE = str_dbparams($HD_Form->DBHandle,"agentid = %1", array($_SESSION['agent_id']));

if (! isset($choose_currency) || ( $choose_currency == ''))
	$choose_currency = strtoupper(BASE_CURRENCY);
	
$date_clause='';
if (DB_TYPE == "postgres"){
	$UNIX_TIMESTAMP = "";
}else{
	$UNIX_TIMESTAMP = "UNIX_TIMESTAMP";
}
$lastdayofmonth = date("t", strtotime($tostatsmonth.'-01'));
if (strlen($HD_Form -> FG_TABLE_CLAUSE) > 0 && $frommonth)
{
	$date_clause .=' AND ';
}	
if ($frommonth && isset($fromstatsmonth))
{
	$date_clause.="  $UNIX_TIMESTAMP(cover_startdate) >= $UNIX_TIMESTAMP('$fromstatsmonth-01')";
}
if( ($frommonth && $tomonth) || (strlen($HD_Form -> FG_TABLE_CLAUSE) > 0 && $tomonth) )
{
	$date_clause .=' AND ';
}

if ($tomonth && isset($tostatsmonth))
{
	$date_clause.=" $UNIX_TIMESTAMP(cover_startdate) <= $UNIX_TIMESTAMP('".$tostatsmonth."-$lastdayofmonth 23:59:59')"; 
}	

$HD_Form -> FG_TABLE_CLAUSE .= $date_clause;

	
//
$HD_Form -> FG_TABLE_ID = "id";
$HD_Form -> FG_TABLE_DEFAULT_ORDER = "id";
$HD_Form -> FG_TABLE_DEFAULT_SENS = "DESC";
$HD_Form -> FG_DEBUG = 0;
$HD_Form -> FG_LIMITE_DISPLAY = 10;

$status_list = array();
$status_list["0"] = array( gettext("Failed"), "0");
$status_list["1"] = array( gettext("Successful"), "1");

$payment_status_list = array();
$payment_status_list["0"] = array( gettext("UNPAID"), "0");
$payment_status_list["1"] = array( gettext("SENT-UNPAID"), "1");
$payment_status_list["2"] = array( gettext("SENT-PAID"),  "2");
$payment_status_list["3"] = array( gettext("PAID"),  "3");

$HD_Form -> AddViewElement(gettext("ID"), "id", "4%", "center", "sort");
$HD_Form -> AddViewElement(gettext("INVOICEDATE"), "invoicecreated_date", "7%", "center", "sort", "22", "", "", "", "", "", "display_dateonly");
$HD_Form -> AddViewElement(gettext("COVERSTART"), "cover_startdate", "7%", "center", "sort", "22", "", "", "", "", "", "display_dateonly");
$HD_Form -> AddViewElement(gettext("COVEREND"), "cover_enddate", "7%", "center", "sort", "22", "", "", "", "", "", "display_dateonly");
$HD_Form -> AddViewElement(gettext("SENTDATE"), "invoicesent_date", "7%", "center", "sort", "22", "", "", "", "", "", "display_dateonly");
$HD_Form -> AddViewElement(gettext("STATUS"), "invoicestatus", "5%", "center", "sort", "", "list", $status_list);
$HD_Form -> AddViewElement(gettext("PAYMENTSTATUS"), "payment_status", "5%", "center", "sort", "", "list", $payment_status_list);
$HD_Form -> AddViewElement(gettext("AMOUNT"), str_dbparams($HD_Form->DBHandle," format_currency(total, %1, %2) AS total", array(strtoupper(BASE_CURRENCY),$choose_currency)), "9%", "center", "sort", "", "", "", "", "", "");
//$HD_Form -> AddViewElement(gettext("PAYMENT"), "payment", "10%", "center", "sort", "15", "lie", "cc_logpayment as t2, cc_invoices t3", "CASE WHEN SUM(t2.payment) IS NULL THEN 0 ELSE SUM(t2.payment) END", "t3.cardid = t2.card_id  AND t2.card_id='$cardid' AND t3.id='%id' AND t2.date >= t3.cover_startdate AND t2.date < t3.cover_enddate", "%1");

$HD_Form -> AutoFieldViewElements ();


$HD_Form -> FG_ACTION_SIZE_COLUMN = '10%';
$HD_Form -> CV_NO_FIELDS  = gettext("THERE IS NO INVOICES CREATED!");
$HD_Form -> CV_DISPLAY_LINE_TITLE_ABOVE_TABLE = false;
$HD_Form -> CV_TEXT_TITLE_ABOVE_TABLE = '';
$HD_Form -> CV_DISPLAY_FILTER_ABOVE_TABLE = false;

$HD_Form -> FG_EDITION = false;
$HD_Form -> FG_DELETION = false;

$HD_Form -> FG_OTHER_BUTTON1 = true;
	
	$HD_Form -> FG_OTHER_BUTTON1_LINK="A2B_entity_ainvoice_detail.php?id=|param|&invoice_type=2";
	
	
	$HD_Form -> FG_OTHER_BUTTON1_ALT = 'Details';
		
	$HD_Form -> FG_OTHER_BUTTON1_IMG = 'images/details.png';
	

	//$HD_Form -> FG_OTHER_BUTTON2_IMG = '';

?>
