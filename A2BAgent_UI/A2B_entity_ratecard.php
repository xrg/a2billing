<?php
include ("./lib/defines.php");
include ("./lib/module.access.php");
include ("./lib/Form/Class.FormHandler.inc.php");


if (! has_rights (ACX_ACCESS)){
	   Header ("HTTP/1.0 401 Unauthorized");
	   Header ("Location: PP_error.php?c=accessdenied");
	   die();
}
/***********************************************************************************/

include ("FG_var_ratecard.inc");

$HD_Form -> init();


if ($id!="" || !is_null($id)){
	$HD_Form -> FG_EDITION_CLAUSE = str_replace("%id", "$id", $HD_Form -> FG_EDITION_CLAUSE);
}

if (!isset($form_action))  $form_action="list"; //ask-add
if (!isset($action)) $action = $form_action;


if ( ($form_action == "list") &&  ($HD_Form->FG_FILTER_SEARCH_FORM) && ($_POST['posted_search'] == 1 )){
	$HD_Form->FG_TABLE_CLAUSE = "tp_id='$mytariff_id'";
}

$list = $HD_Form -> perform_action($form_action);


// #### HEADER SECTION
include("PP_header.php");

// #### HELP SECTION
if ($form_action == 'list')
{
    echo '<br><br>'.$CC_help_ratecard;
}

$HD_Form -> FG_TABLE_CLAUSE = "cc_tariffplan.id = cc_tariffgroup_plan.idtariffplan AND cc_tariffgroup_plan.idtariffgroup = '".$_SESSION["tariff"]."'";

if ($form_action == "list" ) $HD_Form -> create_select_form_client($HD_Form -> FG_TABLE_CLAUSE);

// $HD_Form -> FG_TABLE_CLAUSE .= " cc_tariffgroup_plan.idtariffplan=cc_ratecard.idtariffplan  AND cc_tariffgroup_plan.idtariffgroup = '".$_SESSION["tariff"]."'";

// 'AND cc_ratecard.idtariffplan='".$_SESSION["mytariff_id"]."'

 // #### TOP SECTION PAGE
$HD_Form -> create_toppage ($form_action);


// #### CREATE FORM OR LIST
//$HD_Form -> CV_TOPVIEWER = "menu";
if (strlen($_GET["menu"])>0) $_SESSION["menu"] = $_GET["menu"];

$HD_Form -> create_form ($form_action, $list, $id=null) ;

// #### CREATE SEARCH FORM
if ($form_action == "list"){
	$HD_Form -> create_search_form();
}


// #### FOOTER SECTION
include("PP_footer.php");

?>
