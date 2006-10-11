<?php
include_once ("../lib/defines.php");
include_once ("../lib/module.access.php");
include_once ("../lib/Form/Class.FormHandler.inc.php");



include_once ("./form_data/FG_var_diduse.inc");



if (! has_rights (ACX_RATECARD)){ 
	   Header ("HTTP/1.0 401 Unauthorized");
	   Header ("Location: PP_error.php?c=accessdenied");	   
	   die();	   
}



/***********************************************************************************/

$HD_Form -> setDBHandler (DbConnect());


$HD_Form -> init();


		

if ($id!="" || !is_null($id)){	
	$HD_Form -> FG_EDITION_CLAUSE = str_replace("%id", "$id", $HD_Form -> FG_EDITION_CLAUSE);	
}


if (!isset($form_action))  $form_action="list"; 
if (!isset($action)) $action = $form_action;


$list = $HD_Form -> perform_action($form_action);

include("PP_header.php");

echo '<br><br>'.$CC_help_did_use;

// #### TOP SECTION PAGE
$HD_Form -> create_toppage ($form_action);


// #### CREATE FORM OR LIST
//$HD_Form -> CV_TOPVIEWER = "menu";
if (strlen($_GET["menu"])>0) $_SESSION["menu"] = $_GET["menu"];

$HD_Form -> create_form ($form_action, $list, $id=null) ;

	include("PP_footer.php");


?>
