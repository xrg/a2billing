<?php
include ("lib/defines.php");
include ("lib/module.access.php");
include ("lib/Form/Class.FormHandler.inc.php");


if (! has_rights (ACX_ACCESS)){
	   Header ("HTTP/1.0 401 Unauthorized");
	   Header ("Location: PP_error.php?c=accessdenied");
	   die();
}

class FillBoothForm{
	var $list_booths;
	var $pref_booth=-1;
	
	function init(&$DBHandle){
		global $list_booths;
		$itb = new Table("cc_booth", "id, name");
		//$itb->debug_st=1;
		$FG_TABLE_CLAUSE = "agentid = ". $DBHandle->Quote($_SESSION["agent_id"]) ." AND cur_card_id IS NULL";
		$ltb = $itb -> Get_list ($DBHandle, $FG_TABLE_CLAUSE);
		$list_booths=$ltb;
		
	}

	function disp($query_row, $qc){
		global $list_booths;
		$ress = '';
		$opts = '';
		 // name the fields (for clarity), see FG_var_card's FG_COL_QUERY
		$f_id = $query_row[0];
		$f_def = $query_row[4];
		$f_now_id = $query_row[5];
		$f_now_name = $query_row[6];
		$f_def_id = $query_row[7];
		$f_def_name = $query_row[8];
		if ($f_now_id != null)
			$ress .= "<span>" . htmlspecialchars($f_now_name) ."</span>";
		else {
			$ress .= _("Nowhere");
			echo gettype($f_def). $f_def;
			if ($list_booths)
			foreach($list_booths as $lb){
				$opts .= '<option value="' . $lb[0] .'"';
				if ($lb[0]==$pref_booth)
					$opts .= ' selected';
				$opts.= '>' . htmlspecialchars($lb[1]);
				$opts.="</option>\n";
			}
			$ress = <<<EOS
		<form class="FillBooth" action="${_SERVER['PHP_SELF']}" method="GET" >
		<input type="hidden" name="action" value="fillb" />
		<input type="hidden" name="cardid" value="$f_id" />
		<select name="booth">
		$opts
		</select>
		<button type="submit"> Fill! </button>
	</form>
EOS;
		}
		return $ress;
	}
};

include("PP_header.php");

$fb_form=new FillBoothForm();
include ("FG_var_card.inc");

$HD_Form -> init();

if ($id!="" || !is_null($id)){	
	$HD_Form -> FG_EDITION_CLAUSE = str_replace("%id", "$id", $HD_Form -> FG_EDITION_CLAUSE);	
}



// Fill booth action must be carried out before this, because this queries for the empty ones.
$fb_form->init($HD_Form->DBHandle);

if (!isset($form_action))  $form_action="list"; //ask-add
if (!isset($action)) $action = $form_action;

$list = $HD_Form -> perform_action($form_action);

$HD_Form -> create_toppage ($form_action);


// #### CREATE FORM OR LIST
//$HD_Form -> CV_TOPVIEWER = "menu";
if (strlen($_GET["menu"])>0) $_SESSION["menu"] = $_GET["menu"];

$HD_Form -> create_form ($form_action, $list, $id=null) ;

// #### FOOTER SECTION
if (!($popup_select>=1)) include("PP_footer.php");
?>