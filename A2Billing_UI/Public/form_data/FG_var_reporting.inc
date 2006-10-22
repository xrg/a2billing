<?php

getpost_ifset(array('maxmin','maxuserval','maxuserval2'));


$HD_Form = new FormHandler("cc_call","Research Result");

$HD_Form -> FG_DEBUG = 0;

$HD_Form -> FG_TABLE_ID="username";
$HD_Form -> FG_TABLE_DEFAULT_ORDER = "username";
$HD_Form -> SQL_GROUP=" group by username, sessiontime, buyrate, buycost";

if (!isset($maxmin)) $maxmin = $HD_Form ->FG_TABLE_ID;

$HD_Form -> FG_FILTER_SEARCH_FORM = true;
$HD_Form -> FG_FILTER_SEARCH_TOP_TEXT = gettext('Define specific criteria to do report');
$HD_Form -> FG_FILTER_SEARCH_1_TIME_TEXT = gettext("Selection by month <br>of the call stop time");
$HD_Form -> FG_FILTER_SEARCH_2_TIME_TEXT = gettext("Selection by day <br>of the call end time");
$HD_Form ->FG_FILTER_SEARCH_2_TIME_FIELD = 'stoptime';
$HD_Form -> AddViewElement(gettext("Username"), "username", "20%", "center");
$HD_Form -> AddViewElement(gettext("Duration"), "sessiontime", "10%", "center");
$HD_Form -> AddViewElement(gettext("Buyrate"), "buyrate", "20%", "center","","","","","","","","display_2bill");
$HD_Form -> AddViewElement(gettext("Buycost"), "buycost", "20%", "center","","","","","","","","display_2bill");


$HD_Form -> AddSearchElement_Maxmin(gettext ("Users making the more call"), 'USER', 'maxuserval', 'cc_call',array ('username,sum(sessiontime) as sessiontime, sum(buyrate) as buyrate, sum(buycost) as buycost,count(*),stoptime', '*'), array ('0',gettext("CARD NUMBER"),'username'), array ('count(*)','count'), 'DESC',  'temp_tab', 'group by username,stoptime', gettext("Fill in the number of results would you like to have"), array (true,gettext("Nbr Call"),'count','20%',"center","sort","","","","","","",""));
$HD_Form -> AddSearchElement_Maxmin(gettext ("More call destination"),'DEST','maxuserval2', 'cc_call', array ('destination,sum(sessiontime) as sessiontime, sum(buyrate) as buyrate, sum(buycost) as buycost,count(*),stoptime', '*'),array ('0',gettext("DESTINATION"),'destination'), array('count(*)','count') , 'DESC',  'temp_tab', 'group by destination,stoptime', gettext("Fill in the number of results would you like to have"), array (true,gettext("Nbr Call"),'count','20%',"center","sort","","","","","","",""));

$HD_Form -> FieldViewElement ('username, sessiontime, buyrate, buycost',false);


$HD_Form -> CV_NO_FIELDS  = gettext("THERE IS NO RESULT FOUND!");
$HD_Form -> CV_DISPLAY_LINE_TITLE_ABOVE_TABLE = false;
$HD_Form -> CV_TEXT_TITLE_ABOVE_TABLE = '';
$HD_Form -> CV_DISPLAY_FILTER_ABOVE_TABLE = false;

$HD_Form -> FG_GO_LINK_AFTER_ACTION_ADD = $_SERVER['PHP_SELF']."?atmenu=document&stitle=Document&wh=AC&id=";
$HD_Form -> FG_GO_LINK_AFTER_ACTION_EDIT = $_SERVER['PHP_SELF']."?atmenu=document&stitle=Document&wh=AC&id=";
$HD_Form -> FG_GO_LINK_AFTER_ACTION_DELETE = $_SERVER['PHP_SELF']."?atmenu=document&stitle=Document&wh=AC&id=";
?>
