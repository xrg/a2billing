<?php
getpost_ifset(array('id', 'billingtype', 'did', 'startingdate', 'expirationdate', 'id_cc_didgroup', 'id_cc_country', 'activated', 'fixrate', 'id_trunk'));


$HD_Form = new FormHandler("cc_system_log csl LEFT JOIN cc_ui_authen cua ON csl.iduser = cua.userid", "Log");

$HD_Form -> FG_DEBUG = 0;
$HD_Form -> FG_TABLE_ID="id";
$HD_Form -> FG_TABLE_DEFAULT_ORDER = "creationdate";
$HD_Form -> FG_TABLE_DEFAULT_SENS = "DESC";
//$HD_Form -> FG_TABLE_CLAUSE = " csl.iduser = cua.userid";

// TODO Integrate a generic LIST to Framework
$actived_list = array(); 
$actived_list["1"] = array( gettext("Active"), "1");
$actived_list["0"] = array( gettext("Inactive"), "0");

$billingtype_list = array();
$billingtype_list["0"] = array( gettext("Fix per month + dialoutrate"), "0");
$billingtype_list["1"] = array( gettext("Fix per month"), "1");
$billingtype_list["2"] = array( gettext("Only dialout rate"), "2");
$billingtype_list["3"]  = array( gettext("Free"), "3");

$HD_Form -> AddViewElement(gettext("ID"), "id", "10%", "center", "sort");
$HD_Form -> AddViewElement(gettext("LOGIN"), "login", "8%", "center", "sort");
$HD_Form -> AddViewElement(gettext("LOGLEVEL"), "loglevel", "14%", "center");
$HD_Form -> AddViewElement(gettext("ACTION"), "action", "14%", "center", "sort");
$HD_Form -> AddViewElement(gettext("description"), "description", "12%", "center", "sort");
$HD_Form -> AddViewElement(gettext("TABLENAME"), "tablename", "12%", "center", "sort", "15");
$HD_Form -> AddViewElement(gettext("PAGENAME"), "pagename", "5%", "center", "sort");
$HD_Form -> AddViewElement(gettext("IPADDRESS"), "ipaddress", "4%", "center", "SORT");
$HD_Form -> AddViewElement(gettext("DATE"), "creationdate", "14%", "center", "sort");
//$HD_Form -> AddViewElement(gettext("DATA"), "data", "8%", "center", "SORT");

// added a parameter to append  FG_TABLE_ID  ( by default ) or disable 0.
$HD_Form -> FieldViewElement ('id, cua.login, loglevel,action,description,tablename,pagename,ipaddress,creationdate,data');

$HD_Form -> CV_DISPLAY_LINE_TITLE_ABOVE_TABLE = false;
$HD_Form -> CV_TEXT_TITLE_ABOVE_TABLE = '';
$HD_Form -> CV_DISPLAY_FILTER_ABOVE_TABLE = false;

$HD_Form -> FG_DELETION = false;
$HD_Form -> FG_INTRO_TEXT_ADD = gettext("you can add easily a new")." ".$HD_Form->FG_INSTANCE_NAME.".<br>".gettext("Fill the following fields and confirm by clicking on the button add.");

$HD_Form -> FG_GO_LINK_AFTER_ACTION_DELETE = $_SERVER['PHP_SELF']."?atmenu=document&stitle=Document&wh=AC&id=";

?>
