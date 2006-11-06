<?php
include ("lib/defines.php");
include ("lib/module.access.php");
include ("lib/Form/Class.FormHandler.inc.php");
include ("FG_var_card.inc");


if (! has_rights (ACX_ACCESS)){
	   Header ("HTTP/1.0 401 Unauthorized");
	   Header ("Location: PP_error.php?c=accessdenied");
	   die();
}



/***********************************************************************************/

 
$HD_Form -> setDBHandler (DbConnect());
$HD_Form -> init();


$list = $HD_Form -> perform_action($form_action);

include("PP_header.php");

$HD_Form -> create_toppage ($form_action);


// #### CREATE FORM OR LIST
//$HD_Form -> CV_TOPVIEWER = "menu";
if (strlen($_GET["menu"])>0) $_SESSION["menu"] = $_GET["menu"];

$HD_Form -> create_form ($form_action, $list, $id=null) ;

// #### FOOTER SECTION
if (!($popup_select>=1)) include("PP_footer.php");
?>
