<?php


getpost_ifset(array('id', 'login', 'name','passwd','location','language','tariffgroup','climit'));

$language_list = get_languages();
$language_list_r = get_languages_r($language_list);

$currency_list = array();
$currency_list_r = array();
$indcur=0;

$currencies_list = get_currencies();

foreach($currencies_list as $key => $cur_value) {
	$currency_list[$key]  = array( $cur_value[1].' ('.$cur_value[2].')', $key);
	$currency_list_r[$key]  = array( $key, $cur_value[1]);
	$currency_list_key[$key][0] = $key;
}


$HD_Form = new FormHandler("cc_agent","Agent");

$HD_Form -> FG_DEBUG = 0;
// FAQ

$actived_list = array();
$actived_list["t"] = array( gettext("Active"), "t");
$actived_list["f"] = array( gettext("Inactive"), "f");

$yesno = array();
$yesno["1"] = array( gettext("Yes"), "1");
$yesno["0"] = array( gettext("No"), "0");


$HD_Form -> AddViewElement(gettext("ID"), "id", "5%", "center", "sort");
$HD_Form -> AddViewElement(gettext("NAME"), "name", "25%", "left", "sort");
$HD_Form -> AddViewElement(gettext("USER ID"), "login", "15%", "left", "sort");
$HD_Form -> AddViewElement(gettext("OPTIONS"), "options", "7%", "center", "sort");
$HD_Form -> AddViewElement(gettext("LOCATION"), "location", "25%", "left", "sort");
$HD_Form -> AddViewElement(gettext("LANGUAGE"), "language", "15%", "center", "sort");
$HD_Form -> AddViewElement(gettext("CREDIT"), "credit", "10%", "center", "sort");
$HD_Form -> AddViewElement(gettext("CLIMIT"), "climit", "10%", "center", "sort");
$HD_Form -> AddViewElement(gettext("TARIFFG"), "tariffgroup", "10%", "center", "sort", "15", "lie", "cc_tariffgroup", "tariffgroupname", "id='%id'", "%1");
$HD_Form -> AddViewElement("<acronym title=\"CURRENCY\">".gettext("CUR")."</acronym>", "currency", "5%", "center", "sort", "", "list", $currency_list_key);
$HD_Form -> AddViewElement("<acronym title=\"ACTIVATED\">".gettext("ACT")."</acronym>", "activated", "4%", "center", "sort", "", "list", $actived_list);

//$HD_Form -> AddViewElement(gettext("NAME"), "id_cc_card", "25%", "center", "sort", "15", "lie", "cc_card", "username", "id='%id'", "%1");
//$HD_Form -> AddViewElement(gettext("USER ID"), "activated", "25%", "center", "sort", "", "list", $actived_list);


$HD_Form -> FieldViewElement ('id, name, login, options, location, language, credit, climit, tariffgroup, currency, active');


$HD_Form -> CV_NO_FIELDS  = gettext("THERE IS NO ".strtoupper($HD_Form->FG_INSTANCE_NAME)." CREATED!");
$HD_Form -> CV_DISPLAY_LINE_TITLE_ABOVE_TABLE = false;
$HD_Form -> CV_TEXT_TITLE_ABOVE_TABLE = '';
$HD_Form -> CV_DISPLAY_FILTER_ABOVE_TABLE = false;
$HD_Form -> FG_LIMITE_DISPLAY = 10;


$HD_Form -> FG_EDITION = true;
$HD_Form -> FG_DELETION = true;
//$HD_Form -> FG_OTHER_BUTTON1 = true;

$m_pass='';
if (isset($form_action) && $form_action=="ask-add"){
	$pass = MDP_NUMERIC(10);
	$m_pass = "value='".$pass."'";
	$default_initbalance = "value='0'";
}


$HD_Form -> AddEditElement (gettext("NAME"),
			   "name",
			   "",
			   "INPUT",
			   "size=20 maxlength=40",
			   "",
			   gettext("Insert the Agent name"),
			   "" , "", "",	"", "" , "", "", gettext("Human readable name for the agent"));

$HD_Form -> AddEditElement (gettext("USER ID"),
			   "login",
			   "",
			   "INPUT",
			   "size=20 maxlength=20",
			   "",
			   gettext("Insert the Agent userid"),
			   "" , "", "",	"", "" , "", "", gettext("The username the agent will use to login."));

$HD_Form -> AddEditElement (gettext("PASSWORD"),
			   "passwd",
			   '',
			   "INPUT",
			   "size=20 $m_pass maxlength=40",
			   "",
			   gettext("Set the Agent password"),
			   "" , "", "",	"", "" , "", "", "");

$HD_Form -> AddEditElement (gettext("LOCATION"),
			   "location",
			   "",
			   "TEXTAREA",
			   "size=20 maxlength=40",
			   "",
			   gettext("Insert the Agent location"),
			   "" , "", "",	"", "" , "", "", gettext("Text describing the location of the agent."));

$HD_Form -> AddEditElement (gettext("LANGUAGE"),
			   "language",
			   "",
			   "SELECT",
			   "",
			   "",
			   "",
			   "list" , "", "","", $language_list, "%1", "", "" );

$HD_Form -> AddEditElement (gettext("CREDIT LIMIT"),
			   "climit",
			   "",
			   "INPUT",
			   "size=20 maxlength=20",
			   "",
			   gettext("Adjust the credit limit for the agent here"),
			   "" , "", "",	"", "" , "", "", gettext("Credit limit may take any number you choose. Changing it is not a transaction, but may allow or restrict the agent's operation."));

$HD_Form -> AddEditElement (gettext("TARIFFGROUP"),
			   "tariffgroup",
			   "",
			   "SELECT",
			   "", "", "",
			   "sql",
			   "cc_tariffgroup",
			   "tariffgroupname, id",
			   "", "", "%1", "", gettext("The tariff that will apply to the agent's cards"));

$HD_Form -> AddEditElement (gettext("CURRENCY"),
			   "currency",
			   "",
			   "SELECT",
			   "",
			   "",
			   "",
			   "list" , "", "",	"", $currency_list, "%1", "", "" );

$HD_Form -> AddEditElement (gettext("ACTIVATED"),
			   "active",
			   't',
			   "RADIOBUTTON",
			   "",
			   "",
			   gettext("Choose if you want to activate this agent"),
			   "" , "", "" , "Yes :t, - No:f",	"", "", "", "" );

$HD_Form -> AddEditElement (gettext("EMAIL"),
			   "email",
			   "",
			   "INPUT",
			   "size=30 maxlength=40",
			   "",
			   gettext("Insert the Agent email"),
			   "" , "", "",	"", "" , "", "", gettext("The agent's email"));

$HD_Form -> AddEditElement (gettext("COMMISSION"),
			   "commission",
			   "",
			   "INPUT",
			   "size=20 maxlength=20",
			   "",
			   gettext("The agents commission"),
			   "" , "", "",	"", "" , "", "", gettext("Comission. Enter 0.10 for 10% commission."));


//NOTE: we have an issue here: 'sanitize' may destroy html syntax, like the '=' char.

$HD_Form -> AddEditElement (gettext("BANNER"),
			   "banner",
			   "",
			   "TEXTAREA",
			   "rows=5 cols=60",
			   "",
			   gettext("Insert the Agent banner"),
			   "" , "", "",	"", "" , "", "", gettext("Text (HTML) which will be printed under the company logo at the invoices."));

// $HD_Form -> AddEditElement (gettext("ACTIVATED"),
// 				   "activated",
// 				   't',
// 				   "RADIOBUTTON",
// 				   "",
// 				   "",
// 				   gettext("Choose if you want to activate this card"),
// 				   "" , "", "", "Yes :t, - No:f", "", "", "", "" );



 $HD_Form -> FieldEditElement ('name, login, passwd, location, language, climit, tariffgroup, currency, active, email, commission, banner');





// Set the filter variables

$HD_Form -> FG_FILTER_APPLY = true;
$HD_Form -> FG_FILTERFIELD = 'agent';
$HD_Form -> FG_FILTERFIELDNAME = 'agent';


if (isset($filterprefix)  &&  (strlen($filterprefix)>0)){
        if (strlen($HD_Form -> FG_TABLE_CLAUSE)>0) $HD_Form -> FG_TABLE_CLAUSE.=" AND ";
        $HD_Form -> FG_TABLE_CLAUSE.="cid like '$filterprefix%'";
}




$HD_Form -> FG_INTRO_TEXT_EDITION= gettext("Modify via the form below, the properties of the Agent");
$HD_Form -> FG_INTRO_TEXT_ASK_DELETION = gettext("If you really want remove this ".$HD_Form->FG_INSTANCE_NAME.", click on the delete button.");
$HD_Form -> FG_INTRO_TEXT_ADD = gettext("you can add easily a new ".$HD_Form->FG_INSTANCE_NAME."<br>Fill the following fields and confirm by clicking on the button add.");

//$HD_Form -> FG_OTHER_BUTTON1_LINK="A2B_entity_agent.php?form_action=ask-edit&stitle=Agent&id=|col_orig1|";
//$HD_Form -> FG_OTHER_BUTTON1_IMG ="../Images/icon-card.png";



$HD_Form -> FG_INTRO_TEXT_ADITION = gettext("Add an ".$HD_Form->FG_INSTANCE_NAME." now.");
$HD_Form -> FG_TEXT_ADITION_CONFIRMATION = gettext("Your new ".$HD_Form->FG_INSTANCE_NAME." has been inserted. <br>");


$HD_Form -> FG_BUTTON_EDITION_SRC = $HD_Form -> FG_BUTTON_ADITION_SRC  = "../Images/cormfirmboton.gif";
$HD_Form -> FG_BUTTON_EDITION_BOTTOM_TEXT = $HD_Form -> FG_BUTTON_ADITION_BOTTOM_TEXT = gettext("Click Confirm Data to apply your changes");



$HD_Form -> FG_GO_LINK_AFTER_ACTION_ADD = $_SERVER['PHP_SELF']."?atmenu=document&stitle=Document&wh=AC&id=";
$HD_Form -> FG_GO_LINK_AFTER_ACTION_EDIT = $_SERVER['PHP_SELF']."?atmenu=document&stitle=Document&wh=AC&id=";
$HD_Form -> FG_GO_LINK_AFTER_ACTION_DELETE = $_SERVER['PHP_SELF']."?atmenu=document&stitle=Document&wh=AC&id=";
?>
