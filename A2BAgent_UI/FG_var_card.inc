<?php

getpost_ifset(array('id',"check", "type", "mode", "cardnumber"));

$HD_Form = new FormHandler("cc_card_agent_v","Card");

// This has to be here, so that subsequent calls to Quote() work..
$HD_Form -> setDBHandler (DbConnect());


$HD_Form -> FG_DEBUG = 0;
// FAQ
$HD_Form -> FG_TABLE_DEFAULT_SENS = "ASC";
$HD_Form -> FG_FILTER_SEARCH_SESSION_NAME = 'entity_card_selection';

$HD_Form -> FG_FK_DELETE_CONFIRM = true;


$HD_Form -> AddViewElement(gettext("ID"), "id", "3%", "center", "sort");
$HD_Form -> AddViewElement(gettext("CARD NUMBER"), "username", "11%", "center", "sort");
$HD_Form -> AddViewElement(gettext("LASTNAME"), "lastname", "10%", "center", "sort", "15");
$HD_Form -> AddViewElement("<acronym title=\"". gettext("BALANCE"). "\">".gettext("BA")."</acronym>", "credit", "10%", "center", "sort", "", "", "", "", "", "", "");

//$HD_Form -> AddViewElement(gettext("ACTION (eval)"), "", "10%", "", "", "","eval", "\"Test %&1\"");

$HD_Form -> AddViewElement(gettext("ACTION"), "", "10%", "", "", "","object", &$fb_form);

$FG_COL_QUERY="id, username, lastname, format_currency(credit,'EUR','EUR')";

$HD_Form -> FG_TABLE_CLAUSE = "agentid = ". $HD_Form->DBHandle->Quote($_SESSION["agent_id"]);
$HD_Form -> FieldViewElement ($FG_COL_QUERY);

$HD_Form -> CV_NO_FIELDS  = gettext("NO CARDS HAVE BEEN CREATED!");
$HD_Form -> CV_DISPLAY_LINE_TITLE_ABOVE_TABLE = true; //false;
$HD_Form -> CV_TEXT_TITLE_ABOVE_TABLE = _("Customers");
$HD_Form -> CV_DISPLAY_FILTER_ABOVE_TABLE = false; //--
$HD_Form -> FG_LIMITE_DISPLAY = 10;

// 	$HD_Form -> FG_OTHER_BUTTON1 = true;
// 	$HD_Form -> FG_OTHER_BUTTON1_ALT = '<font color="red">&lt;select&gt;</font>';
// 	$HD_Form -> FG_OTHER_BUTTON1_IMG = '';
// 	
// 	$HD_Form -> FG_OTHER_BUTTON1_LINK = "javascript:sendValue('|param|');";


?>