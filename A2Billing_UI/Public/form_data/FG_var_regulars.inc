<?php

getpost_ifset(array('id',"check", "type", "mode", "cardnumber", "action", "booth", "cardid",'posted','select'));

$HD_Form = new FormHandler("cc_card_agent_v","Card");

// This has to be here, so that subsequent calls to Quote() work..
$HD_Form -> setDBHandler (DbConnect());

$language_list= get_languages();
$currency_list = array();
$currency_list_r = array();
$indcur=0;

$currencies_list = get_currencies();

foreach($currencies_list as $key => $cur_value) {
	$currency_list[$key]  = array( $cur_value[1].' ('.$cur_value[2].')', $key);
	$currency_list_r[$key]  = array( $key, $cur_value[1]);
	$currency_list_key[$key][0] = $key;
}


$typepaid_list = array();
$typepaid_list["0"]  = array( gettext("PREPAID CARD"), "0");
$typepaid_list["1"]  = array( gettext("POSTPAY CARD"), "1");

$HD_Form -> FG_DEBUG = 0;
// FAQ
$HD_Form -> FG_TABLE_DEFAULT_SENS = "ASC";
$HD_Form -> FG_FILTER_SEARCH_SESSION_NAME = 'entity_card_selection';

$HD_Form -> FG_FK_DELETE_CONFIRM = true;

if (isset($posted)){
	$HD_Form->FG_TOP_FILTER_VALUE = $select; //FIXME: PLEASE quote()!
	$agentid = $select;
	
}else{
	$HD_Form -> FG_TOP_FILTER_NAME = 0;
	$HD_Form -> FG_TABLE_CLAUSE = '';
}

$HD_Form -> AddViewElement(gettext("ID"), "id", "3%", "center", "sort");
$HD_Form -> AddViewElement(_("AGENT"), "agentid", "15%", "left", "sort", "", "lie", "cc_agent", "name", "id='%id'", "%&1");
$HD_Form -> AddViewElement(gettext("CARD NUMBER"), "username", "11%", "center", "sort");
$HD_Form -> AddViewElement(gettext("NAME"), "name", "10%", "left", "sort", "15");
$HD_Form -> AddViewElement("<acronym title=\"". gettext("BALANCE"). "\">".gettext("BA")."</acronym>", "credit", "10%", "center", "sort", "", "", "", "", "", "", "");

//$HD_Form -> AddViewElement(gettext("ACTION (eval)"), "", "10%", "", "", "","eval", "\"Test %&1\"");

if ($popup_select == '')
	$HD_Form -> AddViewElement(gettext("NOW IN"), "", "40%", "", "", "","object", $fb_form);

$FG_COL_QUERY="id, agentid, username, firstname|| ' ' ||lastname AS name, format_currency(credit,'EUR','EUR'), def, id, now_id, now_name, def_id, def_name, agentid";

if ($action == 'fillb') {
	?> Fill booth <?= $booth ?>  with card <?= $cardid ?> <br> 
	<?php 
	$FG_QUERY= str_params("UPDATE cc_booth SET cur_card_id = %#0 WHERE id = %#1 ; ",
		array($cardid, $booth));
	echo $FG_QUERY;
	if ($HD_Form->DBHandle->Execute($FG_QUERY))
		echo "<span class='action_success'>" . _("Booth Filled"). "</span>";
	else {
		echo "<span class='action_fail'>" . _("Cannot fill booth!") . "</span>";
 		if ($HD_Form->FG_DEBUG>0)
			echo $HD_Form->DBHandle->ErrorMsg();
		}

}elseif ($action == 'emptyb') {
	?> Empty booth <?= $booth ?><br> 
	<?php 
	$FG_QUERY= str_params("UPDATE cc_booth SET cur_card_id = NULL WHERE id = %#0 ; ",
		array($booth));
	echo $FG_QUERY;
	if ($HD_Form->DBHandle->Execute($FG_QUERY))
		echo "<span class='action_success'>" . _("Booth Emptied"). "</span>";
	else {
		echo "<span class='action_fail'>" . _("Cannot empty booth!") . " </span>";
		if ($HD_Form->FG_DEBUG>0)
			echo $HD_Form->DBHandle->ErrorMsg();
	}
}

if (isset($agentid) && ((integer)$agentid)>1)
	$HD_Form -> FG_TABLE_CLAUSE = str_dbparams($HD_Form->DBHandle, "agentid = %1", array($agentid));
$HD_Form -> FieldViewElement ($FG_COL_QUERY);

$HD_Form -> CV_NO_FIELDS  = gettext("NO CARDS HAVE BEEN CREATED!");
$HD_Form -> CV_DISPLAY_LINE_TITLE_ABOVE_TABLE = true; //false;
$HD_Form -> CV_TEXT_TITLE_ABOVE_TABLE = _("Customers");
$HD_Form -> CV_DISPLAY_FILTER_ABOVE_TABLE = false; //--
$HD_Form -> FG_LIMITE_DISPLAY = 10;

$HD_Form -> CV_FOLLOWPARAMETERS= "&posted=$posted&select=$select&popup_select=".$_GET['popup_select']."&popup_formname=".$_GET['popup_formname']."&popup_fieldname=".$_GET['popup_fieldname'];

if ($popup_select == 'def'){
	$HD_Form -> FG_OTHER_BUTTON1 = true;
	$HD_Form -> FG_OTHER_BUTTON1_ALT = '<font color="red">&lt;'. _("select") .'&gt;</font>';
	$HD_Form -> FG_OTHER_BUTTON1_IMG = '';
	$HD_Form -> FG_OTHER_BUTTON1_LINK = "javascript:sendValue('|col0|');";
}else
	$HD_Form -> FG_EDITION = true;


$HD_Form -> AddEditElement (gettext("CARD NUMBER "),
			   "username",
			   "",
			   "INPUT",
			    "size=20 maxlength=40",
			   "11",
			   gettext("Insert the username"),
			   "" , "", "",	"", "", "",	"", "" );

$HD_Form -> AddEditElement (gettext("CARD ALIAS "),
			   "useralias",
			   "",
			   "INPUT",
			    "size=20 $maxi2 maxlength=40",
			   "4",
			   gettext("Insert the useralias"),
			   "" , "", "",	"", "" , "", "", "");

$HD_Form -> AddEditElement (gettext("BALANCE"),
			   "credit",
			   "",
			   "INPUT",
			   "size=30 maxlength=30",
			   "12",
			   gettext("Insert the number of credit"),
			   "" , "", "",	"", "", "",	"", "" );

$HD_Form -> AddEditElement (gettext("LANGUAGE"),
			   "language",
			   "",
			   "SELECT",
			   "",
			   "",
			   "",
			   "list" , "", "",	"", $language_list, "%1", "", "" );


$HD_Form -> AddEditElement (gettext("ACTIVATED"),
			   "activated",
			   't',
			   "RADIOBUTTON",
			   "",
			   "",
			   gettext("Choose if you want to activate this card"),
			   "" , "", "" , "Yes :t, - No:f",	"", "", "", "" );


$HD_Form -> AddEditElement (gettext("CURRENCY"),
			   "currency",
			   "",
			   "SELECT",
			   "",
			   "",
			   "",
			   "list" , "", "",	"", $currency_list, "%1", "", "" );


$HD_Form -> AddEditElement (gettext("RUN SERVICE"),
			   "runservice",
			   '1',
			   "RADIOBUTTON",
			   "",
			   "",
			   gettext("Choose if you want to enable the service recurring on this card"),
			   "" , "", "", "Yes :1, - No:0", "", "" , "", gettext("Apply recurring service to this card."));

$HD_Form -> AddEditElement (gettext("RUN AUTOREFILL"),
			   "autorefill",
			   '1',
			   "RADIOBUTTON",
			   "",
			   "",
			   gettext("Choose if you want to enable the autorefill on this card"),
			   "" , "", "", "Yes :1, - No:0", "", "" , "", gettext("Define if you want to authorize the autorefill to apply on this card"));

$HD_Form -> AddEditElement (gettext("INITIAL BALANCE"),
			   "initialbalance",
			   "",
			   "INPUT",
			   "size=30 $default_initbalance maxlength=30",
			   "12",
			   gettext("Insert the amount of the initial balance"),
			   "" , "", "",	"", "", "",	"", gettext("The initial balance is used by autorefill to reset the current balance to this amount"));

$HD_Form -> AddEditElement (gettext("CARD TYPE"),
			   "typepaid",
			   "",
			   "SELECT",
			   "",
			   "",
			   "",
			   "list" , "", "",	"", $typepaid_list, "%1", "", "" );

$HD_Form -> AddEditElement (gettext("CREDIT LIMIT"),
			   "creditlimit",
			   "",
			   "INPUT",
			   "size=20 maxlength=20",
			   "4",
			   gettext("Insert the credit limit"),
			   "" , "", "",	"", "", "", "NO-NULL", gettext("Credit limit is only used for POSTPAY card."));


$HD_Form -> AddEditElement (gettext("FIRST USE DATE"),
			   "firstusedate",
			   "",
			   "INPUT",
			   "size=40 maxlength=40 readonly",
			   "",
			   "the first use date",
			   "" , "" , "", "", "", "", "NO-NULL", "", "", "res_display_dateformat");
$HD_Form -> AddEditElement (gettext("LASTNAME"),
			   "lastname",
			   '$value',
			   "INPUT",
			   "size=30 maxlength=30",
			   "11",
			   gettext("Insert the lastname of the customers"),
			   "" , "", "",	"", "", "" , "NO", "");

$HD_Form -> AddEditElement (gettext("FIRSTNAME"),
			   "firstname",
			   '$value',
			   "INPUT",
			   "size=30 maxlength=30",
			   "11",
			   gettext("Insert the firstname of the customers"),
			   "" , "", "",	"", "", "" , "NO", "");

$HD_Form -> AddEditElement (gettext("CALLERID"),
			   "callerid",
			   '$value',
			   "INSERT",
			   "",
			   "",
			   "",
			   "sql",
			   "call_tariff",
			   "label, id",
			   "", "", "%1 : (%2)", "", "", "cc_callerid:cid:cc_callerid:cid, cid, id_cc_card:id_cc_card = %id:id_cc_card:%3 - (%1)::othertablelinked:cli, id_cc_card:%1:id_cc_card='%1':18");


$HD_Form -> FieldEditElement ('username, useralias, credit, language, activated, currency, runservice, autorefill, initialbalance, typepaid, creditlimit, firstusedate, lastname, firstname');

$HD_Form -> FG_INTRO_TEXT_EDITION= gettext("You can modify, through the following form, the different properties of your ".$HD_Form->FG_INSTANCE_NAME);
$HD_Form -> FG_INTRO_TEXT_ASK_DELETION = gettext("If you really want remove this ".$HD_Form->FG_INSTANCE_NAME.", click on the delete button.");
$HD_Form -> FG_INTRO_TEXT_ADD = gettext("you can add easily a new ".$HD_Form->FG_INSTANCE_NAME.".<br>Fill the following fields and confirm by clicking on the button add.");





$HD_Form -> FG_INTRO_TEXT_ADITION = '';
$HD_Form -> FG_TEXT_ADITION_CONFIRMATION = gettext("Your new ".$HD_Form->FG_INSTANCE_NAME." has been inserted. <br>");


$HD_Form -> FG_BUTTON_EDITION_SRC = $HD_Form -> FG_BUTTON_ADITION_SRC  = "../Images/cormfirmboton.gif";
$HD_Form -> FG_BUTTON_EDITION_BOTTOM_TEXT = $HD_Form -> FG_BUTTON_ADITION_BOTTOM_TEXT = gettext("Once you have completed the form above, click on the CONTINUE button.");


?>