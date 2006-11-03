<?php

getpost_ifset(array('id', 'card_id', 'date', 'payment'));


$plang='en';

$HD_Form = new FormHandler("cc_paypal","paypal");

$HD_Form -> FG_DEBUG = 0;
// FAQ
$HD_Form -> FG_TABLE_DEFAULT_SENS = "ASC";


$HD_Form -> AddViewElement(gettext("PAYMENT_DATE"), "payment_date", "15%", "center", "sort", "30");
$HD_Form -> AddViewElement(gettext("CARDNUM"), "item_number", "10%", "center", "sort", "30");
$HD_Form -> AddViewElement(gettext("TXN_ID"), "txn_id", "10%", "center", "sort", "30");
$HD_Form -> AddViewElement(gettext("LAST_NAME"), "last_name", "10%", "center", "sort");
$HD_Form -> AddViewElement(gettext("<acronym title=\"PAYER_EMAIL\">EMAIL</acronym>"), "payer_email", "10%", "center", "sort");
$HD_Form -> AddViewElement(gettext("<acronym title=\"PAYER_STATUS\">STATUS</acronym>"), "payer_status", "7%", "center", "sort");
$HD_Form -> AddViewElement(gettext("<acronym title=\"PAYMENT_TYPE\">TYPE</acronym>"), "payment_type", "7%", "center", "sort");
$HD_Form -> AddViewElement(gettext("AMOUNT"), "mc_gross", "4%", "center", "sort");
$HD_Form -> AddViewElement(gettext("FEE"), "mc_fee", "4%", "center", "sort");
$HD_Form -> AddViewElement(gettext("<acronym title=\"CURRENCY\">CUR</acronym>"), "mc_currency", "4%", "center", "sort");
$HD_Form -> AddViewElement(gettext("<acronym title=\"PAYMENT_STATUS\">PS</acronym>"), "payment_status", "4%", "center", "sort");


$HD_Form -> FieldViewElement ('payment_date, item_number, txn_id, last_name, payer_email, payer_status, payment_type, mc_gross, mc_fee, mc_currency, payment_status');


$HD_Form -> CV_NO_FIELDS  = gettext("THERE ARE NO PAYPAL TRANSACTIONS LISTED."); 
$HD_Form -> CV_DISPLAY_LINE_TITLE_ABOVE_TABLE = false;
$HD_Form -> CV_TEXT_TITLE_ABOVE_TABLE = '';
$HD_Form -> CV_DISPLAY_FILTER_ABOVE_TABLE = false;


$HD_Form -> FG_EDITION = false;
$HD_Form -> FG_DELETION = false;


?>
