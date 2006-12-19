<?php

getpost_ifset(array('id', 'credit', 'agentid', 'pay_type', 'descr'));


$HD_Form_c = new FormHandler("cc_agentpay", "payment");

$HD_Form_c -> setDBHandler (DbConnect());

$HD_Form_c -> FG_DEBUG = 0;
// FAQ
if ($wantinclude==1) $HD_Form_c -> FG_TABLE_DEFAULT_ORDER = $order;
else $HD_Form_c -> FG_TABLE_DEFAULT_ORDER = "date";

$HD_Form_c -> FG_TABLE_DEFAULT_SENS = "ASC";
$HD_Form_c -> FG_TABLE_CLAUSE = str_dbparams( $HD_Form_c->DBHandle," cc_agentpay.id = id AND agentid = %1",
		array($_SESSION['agent_id']));
$HD_Form_c -> FG_TABLE_ID="cc_agentpay.id";


$chargetype_list = get_chargetypes();

// -- chargetype : 1 - connection charge for DID setup, 2 - Montly charge for DID use, 3 - just wanted to charge you for extra, 4 - cactus renting charges, etc...

$HD_Form_c -> AddViewElement(gettext("DATE"), "date", "10%", "center", "sort", "16", "", "", "", "", "", "display_dateformat");
$HD_Form_c -> AddViewElement(gettext("AMOUNT"), "credit", "10%", "center", "sort");
$HD_Form_c -> AddViewElement(gettext("CHARGETYPE"), "pay_type", "20%", "center", "sort", "", "list", $chargetype_list);
$HD_Form_c -> AddViewElement(gettext("DESCRIPTION"), "descr", "30%", "center", "SORT", "");

// added a parameter to append  FG_TABLE_ID  ( by default ) or disable 0.
$HD_Form_c -> FieldViewElement (str_dbparams($HD_Form_c->DBHandle,'date, format_currency(credit,%1,%2), pay_type, descr',
	array(strtoupper(BASE_CURRENCY),$_SESSION['currency'])));


$HD_Form_c -> CV_NO_FIELDS  = gettext("THERE IS NO ".strtoupper($HD_Form_c->FG_INSTANCE_NAME)." CREATED!");
$HD_Form_c -> CV_DISPLAY_LINE_TITLE_ABOVE_TABLE = false;
$HD_Form_c -> CV_TEXT_TITLE_ABOVE_TABLE = '';
$HD_Form_c -> CV_DISPLAY_FILTER_ABOVE_TABLE = false;



?>
