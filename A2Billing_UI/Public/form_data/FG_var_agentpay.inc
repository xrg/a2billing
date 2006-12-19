<?php

getpost_ifset(array('id', 'credit', 'agentid', 'pay_type', 'descr'));


$HD_Form_c = new FormHandler("cc_agentpay", "payment");

$HD_Form_c -> FG_DEBUG = 0;
// FAQ
if ($wantinclude==1) $HD_Form_c -> FG_TABLE_DEFAULT_ORDER = $order;
else $HD_Form_c -> FG_TABLE_DEFAULT_ORDER = "date";

$HD_Form_c -> FG_TABLE_DEFAULT_SENS = "ASC";
$HD_Form_c -> FG_TABLE_CLAUSE = " cc_agentpay.id = id ";
$HD_Form_c -> FG_TABLE_ID="cc_agentpay.id";


$chargetype_list = get_chargetypes();

// -- chargetype : 1 - connection charge for DID setup, 2 - Montly charge for DID use, 3 - just wanted to charge you for extra, 4 - cactus renting charges, etc...

$HD_Form_c -> AddViewElement(_("AGENT"), 'agentid', "15%", "left", "sort", "15", "lie", "cc_agent", "name", "id='%id'", "%&1");
$HD_Form_c -> AddViewElement(gettext("DATE"), "date", "10%", "center", "sort", "16", "", "", "", "", "", "display_dateformat");
$HD_Form_c -> AddViewElement(gettext("AMOUNT"), "credit", "10%", "center", "sort");
$HD_Form_c -> AddViewElement(gettext("CHARGETYPE"), "pay_type", "20%", "center", "sort", "", "list", $chargetype_list);
$HD_Form_c -> AddViewElement(gettext("DESCRIPTION"), "descr", "20%", "center", "SORT", "");

// added a parameter to append  FG_TABLE_ID  ( by default ) or disable 0.
$HD_Form_c -> FieldViewElement ('agentid, date, credit, pay_type, descr');


$HD_Form_c -> CV_NO_FIELDS  = gettext("THERE IS NO ".strtoupper($HD_Form_c->FG_INSTANCE_NAME)." CREATED!");
$HD_Form_c -> CV_DISPLAY_LINE_TITLE_ABOVE_TABLE = false;
$HD_Form_c -> CV_TEXT_TITLE_ABOVE_TABLE = '';
$HD_Form_c -> CV_DISPLAY_FILTER_ABOVE_TABLE = false;


$HD_Form_c -> FG_EDITION = true;
$HD_Form_c -> FG_DELETION = true;


if (($form_action != 'ask-add') && ($form_action != 'add'))
	$prop = 'disabled';
else
	$agentid_field = 'agentid';

$HD_Form_c-> AddEditElement (gettext("AGENT"),
	$agentid_field,
	"$value",
	"SELECT",
	"$prop", "", "",
	"sql",
	"cc_agent",
	"name, id",
	"", "", "%1", "", gettext("The agent involved in the transaction"));

$HD_Form_c -> AddEditElement (gettext("AMOUNT"),
	"credit",
	'$value',
	"INPUT",
	"size=20  maxlength=20",
	"12",
	gettext("Insert the amount"),
	"" , "", "", "", "" , "", "" ,_("The amount can be positive or negative<br>\nA positive one means we received money from the agent."));

$HD_Form_c -> AddEditElement (gettext("CHARGETYPE"),
	"pay_type",
	"",
	"SELECT",
	"",
	"",
	"",
	"list" , "", "",  "", $chargetype_list, "%1" , "" , "");

$HD_Form_c -> AddEditElement (gettext("DESCRIPTION"),
	"descr",
	'',
	"TEXTAREA",
	"cols=50 rows=4",
	"",
	gettext("Any comment on the transaction"),
	"" , "", "", "", "" , "", "", "");

$HD_Form_c -> FieldEditElement ('agentid, credit, pay_type,descr');



// Set the filter variables

// $HD_Form_c -> FG_FILTER_APPLY = true;
// $HD_Form_c -> FG_FILTERFIELD = 'cardnumber';
// $HD_Form_c -> FG_FILTERFIELDNAME = 'cardnumber';

// if (isset($filterprefix)  &&  (strlen($filterprefix)>0)){
// 	if (strlen($HD_Form_c -> FG_TABLE_CLAUSE)>0) $HD_Form_c -> FG_TABLE_CLAUSE.=" AND ";
// 	$HD_Form_c -> FG_TABLE_CLAUSE.="username like '$filterprefix%'";
// }



$HD_Form_c -> FG_INTRO_TEXT_EDITION= gettext("Alter the details of the payment.");
$HD_Form_c -> FG_INTRO_TEXT_ASK_DELETION = gettext("If you really want remove this ".$HD_Form_c->FG_INSTANCE_NAME.", click on the delete button.");
$HD_Form_c -> FG_INTRO_TEXT_ADD = gettext("you can add easily a new ".$HD_Form_c->FG_INSTANCE_NAME.".<br>Fill the following fields and confirm by clicking on the button add.");


$HD_Form_c -> FG_INTRO_TEXT_ADITION = '';
$HD_Form_c -> FG_TEXT_ADITION_CONFIRMATION = gettext("Your new ".$HD_Form_c->FG_INSTANCE_NAME." has been inserted. <br>");


$HD_Form_c -> FG_BUTTON_EDITION_SRC = $HD_Form_c -> FG_BUTTON_ADITION_SRC  = "../Images/cormfirmboton.gif";
$HD_Form_c -> FG_BUTTON_EDITION_BOTTOM_TEXT = $HD_Form_c -> FG_BUTTON_ADITION_BOTTOM_TEXT = gettext("Click 'Confirm Data' to continue");



$HD_Form_c -> FG_GO_LINK_AFTER_ACTION_ADD = $_SERVER['PHP_SELF']."?atmenu=document&stitle=Document&wh=AC&id=";
$HD_Form_c -> FG_GO_LINK_AFTER_ACTION_EDIT = $_SERVER['PHP_SELF']."?atmenu=document&stitle=Document&wh=AC&id=";
$HD_Form_c -> FG_GO_LINK_AFTER_ACTION_DELETE = $_SERVER['PHP_SELF']."?atmenu=document&stitle=Document&wh=AC&id=";

?>
