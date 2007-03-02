<?php

getpost_ifset(array('id', 'amount', 'id_cc_card', 'chargetype', 'description', 'id_did', 'username'));


if (($form_action=='ask-edit') || ($form_action=='ask-add') || ($form_action=='add') || ($form_action=='edit') || ($form_action=='ask-delete') || ($form_action=='delete')){
	if ($wantinclude==1) $HD_Form_c = new FormHandler("cc_charge_av","charge","A2B_entity_charge.php");
	else $HD_Form_c = new FormHandler("cc_charge_av","charge");
}else {
	if ($wantinclude==1) $HD_Form_c = new FormHandler("cc_charge_av, cc_card", "charge", "A2B_entity_charge.php");
	else $HD_Form_c = new FormHandler("cc_charge_av, cc_card", "charge");
}

$HD_Form_c -> setDBHandler (DbConnect());

$HD_Form_c -> FG_DEBUG = 0;
// FAQ
if ($wantinclude==1) $HD_Form_c -> FG_TABLE_DEFAULT_ORDER = $order;
else $HD_Form_c -> FG_TABLE_DEFAULT_ORDER = "id_cc_card";

$HD_Form_c -> FG_TABLE_DEFAULT_SENS = "DESC";
$HD_Form_c -> FG_TABLE_CLAUSE = str_dbparams($HD_Form_c->DBHandle, " cc_card.id=id_cc_card AND cc_card.id IN (SELECT card_id FROM cc_agent_cards WHERE agentid = %1)",
	array($_SESSION['agent_id']));
if (isset($id_cc_card))
	$HD_Form_c -> FG_TABLE_CLAUSE .= str_dbparams($HD_Form_c->DBHandle,
		" AND cc_card.id= %1",array($id_cc_card));
	
$HD_Form_c -> FG_TABLE_ID="cc_charge_av.id";


$chargetype_list = get_chargetypes();

// -- chargetype : 1 - connection charge for DID setup, 2 - Montly charge for DID use, 3 - just wanted to charge you for extra, 4 - cactus renting charges, etc...


$HD_Form_c -> AddViewElement(gettext("CARDNUMBER"), "username", "12%", "center", "sort");
$HD_Form_c -> AddViewElement(gettext("DATE"), "cc_charge_av.creationdate", "15%", "center", "sort", "", "", "", "", "", "", "display_dateformat");
$HD_Form_c -> AddViewElement(gettext("AMOUNT"), "amount", "10%", "center", "sort");
$HD_Form_c -> AddViewElement(gettext("CHARGETYPE"), "chargetype", "20%", "center", "sort", "", "list", $chargetype_list);
$HD_Form_c -> AddViewElement(gettext("DESCRIPTION"), "description", "30%", "left", "", "");

// added a parameter to append  FG_TABLE_ID  ( by default ) or disable 0.
$HD_Form_c -> FieldViewElement ('username, fmt_date(cc_charge_av.creationdate), amount, chargetype, description');


$HD_Form_c -> CV_NO_FIELDS  = gettext("THERE IS NO CHARGE CREATED!");
$HD_Form_c -> CV_DISPLAY_LINE_TITLE_ABOVE_TABLE = false;
$HD_Form_c -> CV_TEXT_TITLE_ABOVE_TABLE = '';
$HD_Form_c -> CV_DISPLAY_FILTER_ABOVE_TABLE = false;


$HD_Form_c -> FG_EDITION = true;
$HD_Form_c -> FG_DELETION = true;



// TODO integrate in Framework 
if (isset($id_cc_card))
	$val_default="value=" . ((integer) $id_cc_card);
	
$HD_Form_c -> AddEditElement (gettext("ID CARD"),
	"id_cc_card",
	"$value",
	"POPUPVALUE",
	"size=30 maxlength=50 $val_default",
	"4",
	_("Insert the Id CardNumber"),
	", 'CardNumberSelection','width=550,height=350,top=20,left=100'",
	"", "", "", "",
	"A2B_entity_cards.php?popup_select=charge&"
	, "" ,
	gettext("Set the Card Number ID"));

$HD_Form_c -> AddEditElement (gettext("AMOUNT"),
	"amount",
	'$value',
	"INPUT",
	"size=20  maxlength=20 $val_default_fixrate",
	"12",
	gettext("Insert the amount"),
	"" , "", "", "", "" , "", "" ,_("Amount, in ") . BASE_CURRENCY);

$HD_Form_c -> AddEditElement (gettext("CHARGETYPE"),
	"chargetype",
	"",
	"SELECT",
	"",
	"",
	"",
	"list" , "", "",  "", $chargetype_list, "%1" , "" , "");

$HD_Form_c -> AddEditElement (gettext("DESCRIPTION"),
	"description",
	'',
	"TEXTAREA",
	"cols=50 rows=4",
	"",
	gettext("Insert the description"),
	"" , "", "", "", "" , "", "", "");


$HD_Form_c-> FG_QUERY_ADITION_HIDDEN_FIELDS = 'agentid,from_agent';
$HD_Form_c-> FG_QUERY_ADITION_HIDDEN_VALUE = str_dbparams($HD_Form_c->DBHandle, "%1,true", array($_SESSION['agent_id']));

$HD_Form_c -> FieldEditElement ('id_cc_card, amount, chargetype, description');



// Set the filter variables

$HD_Form_c -> FG_FILTER_APPLY = true;
$HD_Form_c -> FG_FILTERFIELD = 'cardnumber';
$HD_Form_c -> FG_FILTERFIELDNAME = 'cardnumber';

if (isset($filterprefix)  &&  (strlen($filterprefix)>0)){
	if (strlen($HD_Form_c -> FG_TABLE_CLAUSE)>0) $HD_Form_c -> FG_TABLE_CLAUSE.=" AND ";
	$HD_Form_c -> FG_TABLE_CLAUSE.="username like '$filterprefix%'";
}



$HD_Form_c -> FG_INTRO_TEXT_EDITION= gettext("Modify the properties of the charge");
$HD_Form_c -> FG_INTRO_TEXT_ASK_DELETION = gettext("If you really want remove this charge, click on the delete button.");
$HD_Form_c -> FG_INTRO_TEXT_ADD = gettext("you can add easily a new charge.<br>Fill the following fields and confirm by clicking on the button add.");


$HD_Form_c -> FG_INTRO_TEXT_ADITION = '';
$HD_Form_c -> FG_TEXT_ADITION_CONFIRMATION = gettext("Your new charge has been inserted. <br>");


$HD_Form_c -> FG_BUTTON_EDITION_SRC = $HD_Form_c -> FG_BUTTON_ADITION_SRC  = "./images/cormfirmboton.gif";
$HD_Form_c -> FG_BUTTON_EDITION_BOTTOM_TEXT = $HD_Form_c -> FG_BUTTON_ADITION_BOTTOM_TEXT = gettext("Click 'Confirm Data' to continue");

$HD_Form_c -> FG_GO_LINK_AFTER_ACTION_ADD = $_SERVER['PHP_SELF']."?atmenu=document&stitle=Document&wh=AC&id=";
$HD_Form_c -> FG_GO_LINK_AFTER_ACTION_EDIT = $_SERVER['PHP_SELF']."?atmenu=document&stitle=Document&wh=AC&id=";
$HD_Form_c -> FG_GO_LINK_AFTER_ACTION_DELETE = $_SERVER['PHP_SELF']."?atmenu=document&stitle=Document&wh=AC&id=";

?>
