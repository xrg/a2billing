<?php
include ("lib/defines.php");
include ("lib/module.access.php");
include ("lib/Form/Class.FormHandler.inc.php");


if (! has_rights (ACX_ACCESS)){
	   Header ("HTTP/1.0 401 Unauthorized");
	   Header ("Location: PP_error.php?c=accessdenied");
	   die();
}

getpost_ifset(array("popup_select","form_action","action"));

	if ($popup_select){
?>
<SCRIPT LANGUAGE="javascript">
<!-- Begin
function doFill(booth,selvalue){
	window.opener.booth_action2(booth, 'load_reg', "&card=" +selvalue, this);
}
// End -->
</script>
<?php
	}

class FillBoothForm{
	var $list_booths;
	var $pref_booth=-1;
	var $txt_fill ;
	var $txt_empty;
	
	function init(&$DBHandle){
		global $list_booths;
		global $txt_fill;
		global $txt_empty;
		$itb = new Table("cc_booth", "id, name");
		//$itb->debug_st=1;
		$FG_TABLE_CLAUSE = "agentid = ". $DBHandle->Quote($_SESSION["agent_id"]) ;
		// ." AND cur_card_id IS NULL" No, allow all booths to be re-filled, but that
		// could confuse the agent if the booth is charged.. 
			// A booth without default card couldn't ever become active!
		$FG_TABLE_CLAUSE .= " AND def_card_id IS NOT NULL";
		$ltb = $itb -> Get_list ($DBHandle, $FG_TABLE_CLAUSE);
		$list_booths=$ltb;
		
		$txt_fill = _("Fill!");
		$txt_empty = _("Empty!");
		
	}

	function disp($query_row, $qc){
		global $list_booths;
		global $txt_fill;
		global $txt_empty;
		$txt_nowhere = _("Nowhere");
		$txt_default = _(", default for ");
		
		$ress = '';
		$opts = '';
		 // name the fields (for clarity), see FG_var_card's FG_COL_QUERY
		$f_id = $query_row[0];
		$f_def = $query_row[4];
		$f_now_id = $query_row[6];
		$f_now_name = $query_row[7];
		$f_def_id = $query_row[8];
		$f_def_name = $query_row[9];
		if ($f_now_id != null){
			$f_sp_name = htmlspecialchars($f_now_name);
			$ress .= <<<EOS
		<form class="EmptyBooth" action="${_SERVER['PHP_SELF']}" method="GET" >
		<label>$f_sp_name </label>
		<input type="hidden" name="action" value="emptyb" />
		<input type="hidden" name="booth" value="$f_now_id">
		<button type="submit">$txt_empty</button>
		</form>
EOS;
		} else {
			$ress .= "<span>" . $txt_nowhere ;
			if (isset($f_def_id))
				$ress .= $txt_default . htmlspecialchars($f_def_name);
			$ress.= "</span>";
			//$ress .= "Default: " . $f_def_id . " " ;
			//echo gettype($f_def). $f_def;
			if ($list_booths)
				foreach($list_booths as $lb)
				if( (!isset($f_def_id)) || ($lb[0] == $f_def_id)){
					$opts .= '<option value="' . $lb[0] .'"';
					if ($lb[0]==$pref_booth)
						$opts .= ' selected';
					$opts.= '>' . htmlspecialchars($lb[1]);
					$opts.="</option>\n";
				}
				
			$ress .= <<<EOS
		<form class="FillBooth" action="${_SERVER['PHP_SELF']}" method="GET" >
		<input type="hidden" name="action" value="fillb" />
		<input type="hidden" name="cardid" value="$f_id" />
		<select name="booth">
		$opts
		</select>
		<button type="submit">$txt_fill</button>
	</form>
EOS;
		}
		return $ress;
	}
};

include("PP_header.php");

if (!isset($popup_select)) $popup_select = '';

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

if ($action=='list') {
?>
<p class='create-btn'><a href='A2B_entity_cards.php?form_action=ask-add&popup_select=<?= $popup_select?>&booth=<?= $booth?>'><?= _("Create new regular customer");?></a>
</p>
<?php 
}

// #### CREATE FORM OR LIST
//$HD_Form -> CV_TOPVIEWER = "menu";
if (strlen($_GET["menu"])>0) $_SESSION["menu"] = $_GET["menu"];

$HD_Form -> create_form ($form_action, $list, $id=null) ;

// #### FOOTER SECTION
if ($popup_select=='') include("PP_footer.php");
?>