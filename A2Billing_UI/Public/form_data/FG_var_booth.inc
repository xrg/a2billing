<?php

getpost_ifset(array('id',"check", "type", "mode", "cardnumber"));

$HD_Form = new FormHandler("cc_booth","Booth");

// This has to be here, so that subsequent calls to Quote() work..
$HD_Form -> setDBHandler (DbConnect());


$booth_states = array();
$booth_states[0] = array(gettext("N/A"), "0");
$booth_states[1] = array(gettext("Empty"), "1");
$booth_states[2] = array(gettext("Idle"),"2");
$booth_states[3] = array(gettext("Ready"),"3");
$booth_states[4] = array(gettext("Active"),"4");
$booth_states[5] = array(gettext("Disabled"),"5");

$HD_Form -> FG_DEBUG = 0;
// FAQ
$HD_Form -> FG_TABLE_DEFAULT_SENS = "ASC";
$HD_Form -> FG_FILTER_SEARCH_SESSION_NAME = 'entity_booth_selection';

$HD_Form -> FG_FK_DELETE_CONFIRM = true;


$HD_Form -> AddViewElement(gettext("ID"), "id", "3%", "center", "sort");
$HD_Form -> AddViewElement(gettext("NAME"), "name", "11%", "left", "sort");
$HD_Form -> AddViewElement(gettext("LOCATION"), "llocation", "10%", "left", "sort", "15");

// $HD_Form -> AddViewElement(gettext("STATE"), "state", "5%", "center", "sort","","list",$booth_states);

// $HD_Form -> AddViewElement(gettext("CREDIT"), "credit", "5%", "center", "sort");

$HD_Form -> AddViewElement(acronym(_("CUR CARD"),_("CURRENT CARD")), "cur_card_id", "25%", "center", "sort", "15", "lie", "cc_card", "username", "id='%id'", "%&1");

$HD_Form -> AddViewElement(acronym(_("DEF CARD"),_("DEFAULT CARD")), "def_card_id", "25%", "center", "sort", "15", "lie", "cc_card", "username", "id='%id'", "%&1");


// $HD_Form -> AddViewElement("<acronym title=\"". gettext("BALANCE"). "\">".gettext("BA")."</acronym>", "credit", "10%", "center", "sort", "", "", "", "", "", "", "");

//$HD_Form -> AddViewElement(gettext("ACTION (eval)"), "", "10%", "", "", "","eval", "\"Test %&1\"");

//$HD_Form -> AddViewElement(gettext("ACTION"), "", "10%", "", "", "","object", &$fb_form);

$FG_COL_QUERY="id, name, location, cur_card_id, def_card_id";

if (isset($agentid)) {
	$HD_Form -> FG_TABLE_CLAUSE = "owner = ".
	$HD_Form->DBHandle->Quote($agentid);
}

$HD_Form -> FieldViewElement ($FG_COL_QUERY);

$HD_Form -> CV_NO_FIELDS  = gettext("NO BOOTHS HAVE BEEN CREATED!");
$HD_Form -> CV_DISPLAY_LINE_TITLE_ABOVE_TABLE = true; //false;
$HD_Form -> CV_TEXT_TITLE_ABOVE_TABLE = _("Customers");
$HD_Form -> CV_DISPLAY_FILTER_ABOVE_TABLE = false; //--
$HD_Form -> FG_LIMITE_DISPLAY = 10;

// 	$HD_Form -> FG_OTHER_BUTTON1 = true;
// 	$HD_Form -> FG_OTHER_BUTTON1_ALT = '<font color="red">&lt;select&gt;</font>';
// 	$HD_Form -> FG_OTHER_BUTTON1_IMG = '';
// 	
// 	$HD_Form -> FG_OTHER_BUTTON1_LINK = "javascript:sendValue('|param|');";

$HD_Form -> FG_EDITION = true;


$HD_Form -> FG_INTRO_TEXT_EDITION= gettext("You can modify, through the following form, the different properties of the ".$HD_Form->FG_INSTANCE_NAME);

$maxi = '';

$HD_Form -> AddEditElement (gettext("NAME"),
			   "name",
			   "",
			   "INPUT",
			    "size=20 $maxi readonly  maxlength=40",
			   "11",
			   gettext("Insert the name of the booth"),
			   "" , "", "",	"", "", "","", "" );

$HD_Form -> AddEditElement (gettext("LOCATION "),
			   "location",
			   "",
			   "TEXTAREA",
			    "size=20 $maxi2 maxlength=40",
			   "",
			   gettext("Insert the text describing the location of the booth"),
			   "" , "", "",	"", "" , "", "", "");


$HD_Form -> AddEditElement (gettext("DISABLED"),
			   "disabled",
			   'f',
			   "RADIOBUTTON",
			   "",
			   "",
			   gettext("If true, the booth will be out of service"),
			   "" , "", "" , "Yes :t, - No:f","", "", "", "" );

$HD_Form -> AddEditElement (gettext("CURRENT CARD"),
			   "cur_card_id",
			   "$value",	
			   "INPUT",	
			   "size=30 maxlength=50 disabled",	
			   "","",
			    "" , "", "", "", "","", "NO-NULL" ,
			   gettext("This is the card currently used. To change it, use the agent interface."));

$HD_Form -> AddEditElement (gettext("DEFAULT CARD"),
			   "def_card_id",		
			   "$value",	
			   "POPUPVALUE",	
			   "size=30 maxlength=50",	
			   "",	
			   gettext("Insert the Id CardNumber"),
			    ", 'CardNumberSelection','width=550,height=330,top=20,left=100'",
			    "", "", "", "", 				
				"A2B_entity_regulars.php?popup_select=def&"
				, "NO-NULL",
				gettext("Select the default card for the booth. This card will be re-used for all the non-regular customers. <br>Select any <b>empty</b> card to attach to the booth."));


$HD_Form -> FieldEditElement ('name, location, disabled, cur_card_id, def_card_id');

$HD_Form -> FG_BUTTON_EDITION_SRC = $HD_Form -> FG_BUTTON_ADITION_SRC  = "../Images/cormfirmboton.gif";
$HD_Form -> FG_BUTTON_EDITION_BOTTOM_TEXT = $HD_Form -> FG_BUTTON_ADITION_BOTTOM_TEXT = gettext("Once you have completed the form above, click on the CONTINUE button.");

?>