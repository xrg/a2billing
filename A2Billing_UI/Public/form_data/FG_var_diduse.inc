<?php
$HD_Form = new FormHandler("cc_did_use", "DID in use");

$HD_Form -> FG_DEBUG = 0;
if (isset($filterprefix)  &&  (strlen($filterprefix)>0)){
	if (strlen($HD_Form -> FG_TABLE_CLAUSE)>0) $HD_Form -> FG_TABLE_CLAUSE.=" AND ";
	$HD_Form -> FG_TABLE_CLAUSE.="id_did like '$filterprefix%'";
	$HD_Form -> FG_TABLE_DEFAULT_ORDER = "reservationdate";
	$HD_Form -> FG_TABLE_DEFAULT_SENS = "DESC";
}else
{
	if (strlen($HD_Form -> FG_TABLE_CLAUSE)>0) $HD_Form -> FG_TABLE_CLAUSE.=" AND ";
    $HD_Form -> FG_TABLE_CLAUSE.=" releasedate IS NULL";
	$HD_Form -> FG_TABLE_DEFAULT_ORDER = "id_did";
	$HD_Form -> FG_TABLE_DEFAULT_SENS = "ASC";
}

$HD_Form -> FG_TABLE_ID="cc_did_use.id";

$actived_list = array();
$actived_list["1"] = array( gettext("Reserved"), "1");
$actived_list["0"] = array( gettext("Free"), "0");

$HD_Form -> AddViewElement(gettext("ID DID"), "id_did", "6%", "center", "sort");
$HD_Form -> AddViewElement(gettext("ID card"), "id_cc_card", "6%", "center", "sort");
$HD_Form -> AddViewElement(gettext("RESERVATION DATE"), "reservationdate", "20%", "center", "sort", "14", "", "", "", "", "", "display_dateformat");
$HD_Form -> AddViewElement(gettext("LIBERATION DATE"), "releasedate", "20%", "center", "sort", "14", "", "", "", "", "", "display_dateformat");
$HD_Form -> AddViewElement(gettext("ACTIVATED"), "activated", "5%", "center", "sort", "", "list", $actived_list);

// added a parameter to append  FG_TABLE_ID  ( by default ) or disable 0.
$HD_Form -> FieldViewElement ('id_did, id_cc_card, reservationdate, releasedate, activated');


$HD_Form -> CV_NO_FIELDS  = gettext("THERE IS NO DID IN USE!");  
$HD_Form -> CV_DISPLAY_LINE_TITLE_ABOVE_TABLE = false;
$HD_Form -> CV_TEXT_TITLE_ABOVE_TABLE = '';
$HD_Form -> CV_DISPLAY_FILTER_ABOVE_TABLE = false;


$HD_Form -> FG_EDITION = false;
$HD_Form -> FG_DELETION = false;


// Set the filter variables

$HD_Form -> FG_FILTER_APPLY = true;
$HD_Form -> FG_FILTERFIELD = 'id_did';
$HD_Form -> FG_FILTERFIELDNAME = 'Did number / HISTORY';
$HD_Form -> FG_FILTER_FORM_ACTION = 'list';

?>
