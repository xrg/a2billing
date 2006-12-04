<?php

getpost_ifset(array('id',"check", "type", "mode", "cardnumber", "action", "booth", "cardid", "order", "sens"));

$HD_Form = new FormHandler("cc_card_agent_v","Card");

// This has to be here, so that subsequent calls to Quote() work..
$HD_Form -> setDBHandler (DbConnect());

$language_list= get_languages();
// $language_list_r= get_languages_r($language_list); *-*

$HD_Form -> FG_DEBUG = 0;
// FAQ
$HD_Form -> FG_TABLE_DEFAULT_SENS = "ASC";
$HD_Form -> FG_FILTER_SEARCH_SESSION_NAME = 'entity_card_selection';

$HD_Form -> FG_FK_DELETE_CONFIRM = true;


$HD_Form -> AddViewElement(gettext("ID"), "id", "3%", "center", "sort");
$HD_Form -> AddViewElement(gettext("CARD NUMBER"), "username", "11%", "center", "sort");
$HD_Form -> AddViewElement(gettext("NAME"), "name", "10%", "left", "sort", "15");
$HD_Form -> AddViewElement("<acronym title=\"". gettext("BALANCE"). "\">".gettext("BA")."</acronym>", "credit", "10%", "center", "sort", "", "", "", "", "", "", "");

//$HD_Form -> AddViewElement(gettext("ACTION (eval)"), "", "10%", "", "", "","eval", "\"Test %&1\"");

if ($popup_select == '')
	$HD_Form -> AddViewElement(gettext("NOW IN"), "", "15%", "", "", "","object", $fb_form);

$FG_COL_QUERY="id, username, firstname|| ' ' ||lastname AS name, format_currency(credit,'EUR','EUR'), def, id, now_id, now_name, def_id, def_name";

if ($action == 'fillb') {
	echo _("Fill booth") . $booth  . _(" with card ") . $cardid ."<br>\n" ;

	$FG_QUERY= str_params("UPDATE cc_booth SET cur_card_id = %#0 WHERE id = %#1 ; ",
		array($cardid, $booth));
	//echo $FG_QUERY;
	if ($HD_Form->DBHandle->Execute($FG_QUERY))
		echo "<span class='action_success'>" . _("Booth Filled"). "</span>";
	else{
		echo "<span class='action_fail'>" . _("Cannot fill booth!") . " </span>";
		if ($HD_Form->FG_DEBUG>0)
			echo "<br>".$HD_Form->DBHandle->ErrorMsg() . "<br>";
	}
}elseif ($action == 'emptyb') {
	echo _("Empty booth "). $booth ."<br>" ;
	$FG_QUERY= str_params("UPDATE cc_booth SET cur_card_id = NULL WHERE id = %#0 ; ",
		array($booth));
	//echo $FG_QUERY;
	if ($HD_Form->DBHandle->Execute($FG_QUERY))
		echo "<span class='action_success'>" . _("Booth Emptied"). "</span>";
	else {
		echo "<span class='action_fail'>" . _("Cannot empty booth! (Does it have zero balance?)") . " </span>";
		if ($HD_Form->FG_DEBUG>0)
			echo "<br>".$HD_Form->DBHandle->ErrorMsg() . "<br>";
		}
}

$HD_Form -> FG_TABLE_CLAUSE = str_dbparams($HD_Form->DBHandle,"agentid = %1 AND (firstusedate IS NOT NULL OR firstname IS NOT NULL)",
	array($_SESSION["agent_id"]));
if ($popup_select == 'freg')
	$HD_Form -> FG_TABLE_CLAUSE .= " AND now_id IS NULL AND def = false";
$HD_Form -> FieldViewElement ($FG_COL_QUERY);

$HD_Form -> CV_NO_FIELDS  = gettext("NO CARDS HAVE BEEN CREATED!");
$HD_Form -> CV_DISPLAY_LINE_TITLE_ABOVE_TABLE = true; //false;
$HD_Form -> CV_TEXT_TITLE_ABOVE_TABLE = _("Customers");
$HD_Form -> CV_DISPLAY_FILTER_ABOVE_TABLE = false; //--
$HD_Form -> FG_LIMITE_DISPLAY = 10;

$HD_Form -> CV_FOLLOWPARAMETERS= "&popup_select=".$_GET['popup_select']."&popup_formname=".$_GET['popup_formname']."&popup_fieldname=".$_GET['popup_fieldname'];

// 	$HD_Form -> FG_OTHER_BUTTON1 = true;
// 	$HD_Form -> FG_OTHER_BUTTON1_ALT = '<font color="red">&lt;select&gt;</font>';
// 	$HD_Form -> FG_OTHER_BUTTON1_IMG = '';
// 	
// 	$HD_Form -> FG_OTHER_BUTTON1_LINK = "javascript:sendValue('|param|');";

if ($popup_select == 'freg'){
	$HD_Form -> FG_OTHER_BUTTON1 = true;
	$HD_Form -> FG_OTHER_BUTTON1_ALT = '<font color="red">&lt;'. _("select") .'&gt;</font>';
	$HD_Form -> FG_OTHER_BUTTON1_IMG = '';
	$HD_Form -> FG_OTHER_BUTTON1_LINK = "javascript:doFill($booth,'|col0|');";
}else 
	$HD_Form -> FG_EDITION = true;

$HD_Form-> FG_QUERY_EDITION_HIDDEN_FIELDS = 'popup_select,booth';
$HD_Form-> FG_QUERY_EDITION_HIDDEN_VALUE = "$popup_select, $booth";
$HD_Form-> FG_QUERY_EDITION_HIDDEN_IGNORE = true;

if (isset($form_action) && $form_action=="ask-add"){
	/* An agent could never add a customer. This is a trick. Instead,
	   we edit an existing, never used, regular and make him appear */
	  $form_action='ask-edit';
	
	$query = str_dbparams($HD_Form->DBHandle,"SELECT id FROM cc_card_agent_v WHERE firstusedate IS NULL AND (firstname IS NULL OR lastname IS NULL) AND agentid = %1 LIMIT 1;", array($_SESSION["agent_id"]));
	
	$res = $HD_Form->DBHandle->Query($query);
	
	if ($res){
		$row= $res->fetchRow();
		$id = $row[0];
		$_GET['id'] = $id;
		echo "Found regular: id = " . $id . "<br>";
		echo "action: $action <br>";
	} else {
		echo "Query: " . htmlspecialchars($query) . " failed.<br>";
		echo "Error: " . $HD_Form->DBHandle->ErrorMsg() . "<br>";
		$form_action='do-nothing';
	}
}


$HD_Form -> AddEditElement (gettext("CARD NUMBER "),
			   "",
			   "",
			   "INPUT",
			    "size=20 $maxi readonly  maxlength=40",
			   "",
			   gettext("Insert the username"),
			   "" , "", "",	"", "", "",	"", "" );

$HD_Form -> AddEditElement (gettext("BALANCE"),
			   "",
			   "",
			   "INPUT",
			   "size=30 readonly maxlength=30",
			   "",
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

$field_elements='username, credit, language, lastname, firstname';

$edit_details=get_config('agent','edit_customer_address',false);

if ($edit_details) {
$HD_Form -> AddEditElement (gettext("EMAIL"),
			   "email",
			   '$value',
			   "INPUT",
			   "size=30 maxlength=30",
			   "1",
			   gettext("Insert the email"),
			   "" , "", "",	"", "", "" , "NO", "");


$HD_Form -> AddEditElement (gettext("ADDRESS"),
			   "address",
			   '$value',
			   "INPUT",
			   "size=30 maxlength=30",
			   "0",
			   gettext("Insert the address of the customers"),
			   "" , "", "",	"", "", "" , "NO", "");


$HD_Form -> AddEditElement (gettext("CITY"),
			   "city",
			   '$value',
			   "INPUT",
			   "size=30 maxlength=30",
			   "0",
			   gettext("Insert the city"),
			   "" , "", "",	"", "", "" , "NO", "");


$HD_Form -> AddEditElement (gettext("STATE/PROVINCE"),
			   "state",
			   '$value',	
			   "INPUT",	
			   "size=30 maxlength=30",	
			   "11",	
			   gettext("Insert the state"),
			   "" , "", "",	"", "", "" , "NO", "");


$HD_Form -> AddEditElement (gettext("COUNTRY"),
			   "country",			
			   '$value',	
			   "INPUT",
			   "size=30 maxlength=30",	
			   "0",	
			   gettext("Insert the country"),
			   "" , "", "",	"", "", "" , "NO", "");


$HD_Form -> AddEditElement (gettext("ZIP/POSTAL CODE"),
			   "zipcode",			
			   '$value',	
			   "INPUT",	
			   "size=30 maxlength=30",	
			   "0",
			   gettext("Insert the zipcode"),
			   "" , "", "",	"", "", "" , "NO", "");
	$field_elements .=', email, address, city, state, country, zipcode';
}

$HD_Form -> FieldEditElement ($field_elements);

$HD_Form -> FG_BUTTON_EDITION_SRC = $HD_Form -> FG_BUTTON_ADITION_SRC  = "images/cormfirmboton.gif";
$HD_Form -> FG_BUTTON_EDITION_BOTTOM_TEXT = $HD_Form -> FG_BUTTON_ADITION_BOTTOM_TEXT = gettext("Once you have completed the form above, click on the CONTINUE button.");

$HD_Form -> FG_GO_LINK_AFTER_ACTION_EDIT = $_SERVER['PHP_SELF']."?form_action=list&popup_select=$popup_select&booth=$booth&id=";

echo "Link after edit: " . $HD_Form -> FG_GO_LINK_AFTER_ACTION_EDIT . "<br>";
?>