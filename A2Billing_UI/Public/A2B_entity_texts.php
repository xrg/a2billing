<?php
include ("../lib/defines.php");
include ("../lib/module.access.php");
include ("../lib/Form/Class.FormHandler.inc.php");


if (! has_rights (ACX_ADMINISTRATOR)){
	   Header ("HTTP/1.0 401 Unauthorized");
	   Header ("Location: PP_error.php?c=accessdenied");
	   die();
}


getpost_ifset(array('posted', 'filterlang'));

$FG_DEBUG = 1;

/********************/

class EditTextForm {
	function disp($query_row, $qc) {
		$ret= "<input type=\"text\" name=\"text[";
		$ret .= $query_row[0]. "]\"";
		$ret .= " value=\"";
		$ret .=  htmlspecialchars($query_row[2]);
		$ret .= "\" /> ";
		switch ($query_row[3]){
		case 0:
			$ret .= _("Untranslated");
			break;
		case 1:
			$ret .= _("Normal");
			break;
		case 2:
			$ret .= _("Auto-input");
			break;
		case 3:
			$ret .= _("Fuzzy");
			break;
		case 4:
			$ret .= _("Translated");
			break;
		}
		
		return $ret;
	}
};

$etf = new EditTextForm();
include ("./form_data/FG_var_texts.inc");

$HD_Form -> setDBHandler (DbConnect());
$HD_Form -> init();


if ($id!="" || !is_null($id)){	
	$HD_Form -> FG_EDITION_CLAUSE = str_replace("%id", "$id", $HD_Form -> FG_EDITION_CLAUSE);
}


if (!isset($form_action))  $form_action="list"; //ask-add
if (!isset($action)) $action = $form_action;

if ($posted==2) {
	if ($FG_DEBUG>1) echo "<br>posted!<br>";
 	$texts_list=$_POST['text'];
 	if (is_array($texts_list)){
 		foreach($texts_list as $txt_id => $txt_val){
 			$qry= str_dbparams($HD_Form->DBHandle,"UPDATE cc_texts SET txt = %2 WHERE id = %1 AND lang = %3;",
 				array($txt_id, $txt_val, $filterlang));
 			if ($FG_DEBUG>1 ) echo $qry . "<br>";
 			
 			$res=$HD_Form->DBHandle->Query($qry);
 			if ((! $res) &&( $FG_DEBUG))
 				echo "<br>Query failed: ". $HD_Form->DBHandle->ErrorMsg() . "<br>";
 		}
 	}
 	else if ($FG_DEBUG>1) echo "Texts is not an array!<br>";
 	
 	$form_action = "list";
}

$list = $HD_Form -> perform_action($form_action);

// #### HEADER SECTION
include("PP_header.php");

// #### HELP SECTION
// if (($form_action == 'ask-add') || ($form_action == 'ask-edit')) echo '<br><br>'.$CC_help_add_rate;
// else echo '<br><br>'.$CC_help_def_ratecard;

// DISPLAY THE UPDATE MESSAGE
if (isset($update_msg) && strlen($update_msg)>0) echo $update_msg; 
	
?>
<center>

<form method=post action="<?php echo $_SERVER['PHP_SELF']?>?s=1&t=0&order=<?php echo $order?>&sens=<?php echo $sens?>&current_page=<?php echo $current_page?>">
<input type="hidden" name="posted" value=1>
<input type="hidden" name="current_page" value=0>
	<table class="bar-status" width="75%" border="0" cellspacing="1" cellpadding="2" align="center">
	<tbody>
	<tr>
		<td align="left" valign="top" bgcolor="#000033">
			<font face="verdana" size="1" color="#ffffff"><b>&nbsp;&nbsp;<?php echo gettext("LANGUAGE");?></b></font>
		</td>
		<td class="bar-search" align="left" bgcolor="#acbdee">
		<table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#acbdee"><tr>
			<td width="50%" align="center">&nbsp;&nbsp;
				<select name="filterlang" size="1"  style="border: 2px outset rgb(204, 51, 0); width=250">
					<option value=''><?php echo gettext("Choose the language");?></option>

				<?php
				foreach ($language_list as $lang)
					if (isset($lang['locale'])){
					?>
					<option class=input value='<?= $lang['locale'];?>' <?php
						if($lang['locale']==$filterlang) echo "selected";
					?> ><?php
					if (isset($lang['flag']))
						echo "<img src=\"../Images/flags/".$lang['flag'] ."\" >";
					echo $lang['name'];
					?></option><?php
				}
				?>
				</select>
			</td>
			<td class="bar-search" align="center" bgcolor="#cddeff" width="50%">
			<input type="image"  name="image16" align="top" border="0" src="<?= Images_Path_Main;?>/button-search.gif" />
		</td>

		</tr></table></td>
	</tr>
	</tbody></table>
</form>
</center>
<?php

//----------------------------
// #### TOP SECTION PAGE
$HD_Form -> create_toppage ($form_action);


// #### CREATE FORM OR LIST
//$HD_Form -> CV_TOPVIEWER = "menu";
if (strlen($_GET["menu"])>0) $_SESSION["menu"] = $_GET["menu"];

if ($form_action == "list") {
	?><form method=post action="<?php echo $_SERVER['PHP_SELF']?>">
	<input type="hidden" name="order" value="<?= $order?>">
	<input type="hidden" name="sens" value="<?= $sens?>">
	<input type="hidden" name="current_page" value="<?= $current_page?>">
	<input type="hidden" name="posted" value=2>
	<input type="hidden" name="form_action" value="apply">
	<input type="hidden" name="filterlang" value="<?= $filterlang ?>">
	<?php
}
$HD_Form -> create_form ($form_action, $list, $id=null) ;

if ($form_action == "list"){
	?>
	<input type="submit" value="<?= _("Submit changes"); ?>" />
	</form >
	<?php
}

// #### CREATE SEARCH FORM
if ($form_action == "list"){
	$HD_Form -> create_search_form();
}

// #### FOOTER SECTION
include("PP_footer.php");

?>
