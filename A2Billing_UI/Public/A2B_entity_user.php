<?php
$menu_section=20;
include ("../lib/defines.php");
include ("../lib/module.access.php");
include ("../lib/Form/Class.FormHandler.inc.php");
include ("./form_data/FG_var_user.inc");


if (! has_rights (ACX_RATECARD)){ 
	   Header ("HTTP/1.0 401 Unauthorized");
	   Header ("Location: PP_error.php?c=accessdenied");	   
	   die();	   
}



/***********************************************************************************/

$HD_Form -> setDBHandler (DbConnect());

// TODO init() shouldn't initialize FG_EDITION_LINK, FG_DELETION_LINK and others
// because on this way we need to redefine here and it is not posible to do it in
// the include file
$HD_Form -> init();

$HD_Form -> FG_EDITION_LINK= $_SERVER[PHP_SELF]."?form_action=ask-edit&groupID=$groupID&id=";
$HD_Form -> FG_DELETION_LINK= $_SERVER[PHP_SELF]."?form_action=ask-delete&groupID=$groupID&id=";

if ($id!="" || !is_null($id)){	
	$HD_Form -> FG_EDITION_CLAUSE = str_replace("%id", "$id", $HD_Form -> FG_EDITION_CLAUSE);	
}


if (!isset($form_action))  $form_action="list"; //ask-add
if (!isset($action)) $action = $form_action;


$list = $HD_Form -> perform_action($form_action);



// #### HEADER SECTION
include("PP_header.php");

// #### HELP SECTION
if ($form_action == 'ask-add') echo $CC_help_admin_edit;
else echo $CC_help_admin_list;



// #### TOP SECTION PAGE
$HD_Form -> create_toppage ($form_action);


// #### CREATE FORM OR LIST
//$HD_Form -> CV_TOPVIEWER = "menu";
if (strlen($_GET["menu"])>0) $_SESSION["menu"] = $_GET["menu"];

$HD_Form -> create_form ($form_action, $list, $id=null) ;

// #### FOOTER SECTION
include("PP_footer.php");




?>
