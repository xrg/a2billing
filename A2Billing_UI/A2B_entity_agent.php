<?php
include ("./lib/defines.php");
include ("./lib/module.access.php");
include (DIR_COMMON."Form/Class.FormHandler.inc.php");
include (DIR_COMMON."Class.HelpElem.inc.php");
// include ("./form_data/FG_var_agent.inc");
// include ("./lib/help.php");

$menu_section='menu_agents';


HelpElem::DoHelp(gettext("Agents, callshops. <br>List or manipulate agents, which can deliver cards to customers."));

$HD_Form= new FormHandler();
$HD_Form->checkRights(ACX_AGENTS);
$HD_Form->init();

$BODY_ELEMS[] = &$HD_Form;


// if ($id!="" || !is_null($id)){
// 	$HD_Form -> FG_EDITION_CLAUSE = str_replace("%id", "$id", $HD_Form -> FG_EDITION_CLAUSE);
// }


// if (!isset($form_action))  $form_action="list"; //ask-add
// if (!isset($action)) $action = $form_action;
// 
// 
// $list = $HD_Form -> perform_action($form_action);
// 
// 
// 
// // #### HEADER SECTION
// include("PP_header.php");
// 
// // #### HELP SECTION
// show_help('agent_list');
// 
// // #### TOP SECTION PAGE
// $HD_Form -> create_toppage ($form_action);
// 
// 
// // #### CREATE FORM OR LIST
// //$HD_Form -> CV_TOPVIEWER = "menu";
// if (strlen($_GET["menu"])>0) $_SESSION["menu"] = $_GET["menu"];
// 
// $HD_Form -> create_form ($form_action, $list, $id=null) ;
// 
// // #### FOOTER SECTION
// include("PP_footer.php");


require("PP_page.inc.php");

?>
