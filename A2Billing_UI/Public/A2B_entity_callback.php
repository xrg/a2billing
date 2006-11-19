<?php
include ("../lib/defines.php");
include ("../lib/Form/Class.FormHandler.inc.php");
include ("./form_data/FG_var_callback.inc");




/***********************************************************************************/

$HD_Form -> setDBHandler (DbConnect());


$HD_Form -> init();


if ($id!="" || !is_null($id)){	
	$HD_Form -> FG_EDITION_CLAUSE = str_replace("%id", "$id", $HD_Form -> FG_EDITION_CLAUSE);	
}


if (!isset($form_action))  $form_action="list"; //ask-add
if (!isset($action)) $action = $form_action;


$list = $HD_Form -> perform_action($form_action);



// #### HEADER SECTION
include("PP_header.php");

// #### HELP SECTION
//echo '<br><br>'.$CC_help_callerid_list;
?>
<br>
<a href="#" target="_self" onclick="imgidclick('img1000','div1000','help.png','viewmag.png');"><img style="" id="img1000" src="../Css/kicons/viewmag.png" onmouseover="this.style.cursor='hand';" height="16" width="16"></a>
<div id="div1000" style="">
<div id="kiki"><div class="w1">
	<img src="../Css/kicons/cache.png" class="kikipic">
	<div class="w2">
Callback will offer you an easy way to connect any phone to our Asterisk platform.
We handle a spool with all the callbacks that need to be running and you might be able to view here all the pending and performed callback with their current status. Different parameters determine the callback, the way to reach the user, the time when we need to call him, the result of the last attempts, etc...
<br>
</div></div></div>
</div>

<script language="JavaScript" src="./javascript/calendar2.js"></script>
<?
// #### TOP SECTION PAGE
$HD_Form -> create_toppage ($form_action);


// #### CREATE FORM OR LIST
//$HD_Form -> CV_TOPVIEWER = "menu";
if (strlen($_GET["menu"])>0) $_SESSION["menu"] = $_GET["menu"];

$HD_Form -> create_form ($form_action, $list, $id=null) ;

// #### FOOTER SECTION
include("PP_footer.php");




?>
