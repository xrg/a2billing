<?php
require_once("./lib/defines.php");
require_once("./lib/module.access.php");
include_once("./lib/help.php");

if (! has_rights (ACX_MISC)){
	   Header ("HTTP/1.0 401 Unauthorized");
	   Header ("Location: PP_error.php?c=accessdenied");
	   die();

}
require_once(DIR_COMMON."Form/Class.FormHandler.inc.php");
require_once("./form_data/FG_var_config_group.inc");


/***********************************************************************************/

$HD_Form -> init();

if (!isset($form_action))  $form_action="list"; //ask-add
if (!isset($action)) $action = $form_action;

$list = $HD_Form -> perform_action($form_action);

require("PP_header.php");


// #### HELP SECTION
show_help('config_group');

$HD_Form -> create_toppage ($form_action);



$HD_Form -> create_form ($form_action, $list, $id=null) ;

// #### FOOTER SECTION
require('PP_footer.php');

?>
