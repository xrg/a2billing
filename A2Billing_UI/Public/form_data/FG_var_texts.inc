<?php

getpost_ifset(array('id', 'posted', 'filterlang'));

/*if (! isset($filterlang))
	$filterlang='es_ES';*/

$HD_Form = new FormHandler("cc_texts AS t1, cc_texts AS t2","Texts");

$HD_Form -> FG_DEBUG = 1;
// FAQ
$HD_Form -> FG_TABLE_DEFAULT_ORDER = "t1.id";
$HD_Form -> FG_TABLE_ID = 't1.id';
$HD_Form -> FG_TABLE_DEFAULT_SENS = "ASC";

$HD_Form->FG_FILTER_SEARCH_SESSION_NAME = 'entity_texts';


$HD_Form -> FG_TABLE_CLAUSE = 't1.id = t2.id AND t1.lang = \'C\' AND t2.lang = \''. $filterlang ."'" ;

$HD_Form -> AddViewElement(gettext("ID"), "id", "5%", "center", "sort", "");
$HD_Form -> AddViewElement(gettext("ENGLISH TEXT"), "txt_C", "40%", "left", "sort", "", "", "", "", "", "", "");
$HD_Form -> AddViewElement(gettext("LOCAL TEXT"), "txt_L", "40%", "left", "sort", "", "object", $etf, "", "", "", "");

$HD_Form -> FieldViewElement ('t1.id AS id, t1.txt AS txt_C, t2.txt AS txt_L, t2.src');


$HD_Form -> CV_NO_FIELDS  = gettext("NO LOCAL TEXTS HAVE BEEN CREATED."); 
$HD_Form -> CV_DISPLAY_LINE_TITLE_ABOVE_TABLE = false;
$HD_Form -> CV_TEXT_TITLE_ABOVE_TABLE = '';
$HD_Form -> CV_DISPLAY_FILTER_ABOVE_TABLE = false;
$HD_Form -> CV_FOLLOWPARAMETERS="&filterlang=$filterlang";

$HD_Form -> FG_FILTER_APPLY = true;
$HD_Form -> FG_FILTERFIELD = 'text';
$HD_Form -> FG_FILTERFIELDNAME = 'text';
$HD_Form -> FG_FILTER_FORM_ACTION = 'list';

if (isset($filterprefix)  &&  (strlen($filterprefix)>0))
{
	if (strlen($HD_Form -> FG_TABLE_CLAUSE) > 0)
    {
        $HD_Form -> FG_TABLE_CLAUSE.=" AND ";
    }
	$HD_Form -> FG_TABLE_CLAUSE.="t1.txt like '%$filterprefix%'";
}

?>
