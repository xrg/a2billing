<?php
$menu_section='menu_billing';

include ("../lib/defines.php");
include ("../lib/module.access.php");
include ("../lib/Form/Class.FormHandler.inc.php");
include ("./form_data/FG_var_tariffplan.inc");


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
	$id_tp=$id;
}


if (!isset($form_action))  $form_action="list"; //ask-add
if (!isset($action)) $action = $form_action;


$list = $HD_Form -> perform_action($form_action);



// #### HEADER SECTION
include("PP_header.php");

// #### HELP SECTION
if (($form_action == 'ask-add') || ($form_action == 'ask-edit')) echo $CC_help_edit_ratecard;
else echo $CC_help_list_ratecard;



// #### TOP SECTION PAGE
$HD_Form -> create_toppage ($form_action);


// #### CREATE FORM OR LIST
//$HD_Form -> CV_TOPVIEWER = "menu";
if (strlen($_GET["menu"])>0) $_SESSION["menu"] = $_GET["menu"];

$HD_Form -> create_form ($form_action, $list, $id=null) ;

if ($form_action == 'ask-edit') {
	//TODO: styles
?>
<table align="center" border="0" width="65%"  cellspacing="1" cellpadding="2">
<tbody>
	<form name="updateForm" action="tariffplan_export.php" method="post">
	<INPUT type="hidden" name="id_tp" value="<?= $id_tp ?>">
	<tr> <td>#<?= $id_tp ?>
		<?= _("Type"); ?>&nbsp;: 
		<select name="export_style" size="1" class="form_input_select">
			<option value='peer-full-csv' selected><?= _("Peer Full CSV") ?></option>
			<option value='peer-full-xml'><?= _("Peer Full XML") ?></option>
			<option value='client-csv' ><?= _("Client CSV") ?></option>
		</select>
		</td>
	</tr>
	<tr><td align="right" >
		<input class="form_input_button" value="<?= _("EXPORT RATECARD");?>" type="submit">
	</td> </tr>
</form>
</table>

<?php
}

// #### FOOTER SECTION
include("PP_footer.php");




?>
