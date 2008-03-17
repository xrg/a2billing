<?php

require_once("Class.BaseField.inc.php");

/** Helper class, provides the necessary javascript.. */
class RevRefHeader extends ElemBase {
	public $formName ='Frm';
	//TODO: When form->prefix is used, Frm should be updated here..
	
	function Render(){
	}
	
	// stub functions..
	function RenderHead() {
	?>

<script language="JavaScript" type="text/JavaScript">
<!--
function formRRdelete(rid,raction,rname, instance){
  document.<?= $this->formName ?>.action.value = "object-edit";
  document.<?= $this->formName ?>.sub_action.value = rid;
  document.<?= $this->formName ?>.elements[raction].value='delete';
  if (rname != null) document.<?= $this->formName ?>.elements[rname].value = instance;
  <?= $this->formName ?>.submit();
}
function formRRdelete2(rid,raction,rname, instance, inst2){
  document.<?= $this->formName ?>.action.value = "object-edit";
  document.<?= $this->formName ?>.sub_action.value = rid;
  document.<?= $this->formName ?>.elements[raction].value='delete';
  if (rname != null) document.<?= $this->formName ?>.elements[rname].value = instance;
  if (rname != null) document.<?= $this->formName ?>.elements[rname+'2'].value = inst2;
  <?= $this->formName ?>.submit();
}

function formRRadd(rid,raction){
  document.<?= $this->formName ?>.action.value = "object-edit";
  document.<?= $this->formName ?>.sub_action.value = rid;
  document.<?= $this->formName ?>.elements[raction].value='add';
  <?= $this->formName ?>.submit();
}
//-->
</script>
	
	<?php
	}
}; // end class RevRefHeader

$PAGE_ELEMS[] = new RevRefHeader();

/** Reverse reference Edit: control a table which references the edited entry
    We are editing table1 and have some table2 where table2.refid = table1.localkey
    So, we present here the list of table2.refname..
    
*/
class RevRef extends BaseField {
	public $reftable;
	public $refname = 'name';
	public $localkey ;
	public $refid = 'rid';
	public $refkey = 'id'; /// The (primary) key for $reftable. If NULL, there is *no* key!
	
	function RevRef($fldtitle,$fldname,$lkey,$reftable,$refid = 'rid',$refname = 'name',$flddescr = null,$refkey='id'){
		$this->fieldname = $fldname;
		$this->fieldtitle = $fldtitle;
		$this->reftable = $reftable;
		$this->localkey= $lkey;
		$this->refid = $refid;
		$this->refname = $refname;
		$this->editDescr = $flddescr;
		$this->refkey = $refkey;
		$this->does_list = false;
		$this->does_add = false;
	}

	public function DispList(array &$qrow,&$form){
		if ($form->getAction()=='details')
			return $this->DispForm($qrow,$form,false);
	}
	public function renderSpecial(array &$qrow,&$form,$rmode, &$robj){
		//Todo: sth. like the values in an array..
	}

	public function DispEdit(array &$qrow,&$form){
		return $this->DispForm($qrow,$form,true);
	}
	
	public function listQueryField(&$dbhandle){
		if (!$this->does_list)
			return;
		return $this->detailQueryField($dbhandle);
	}
	
	public function detailQueryField(&$dbhandle){
		return $this->localkey;
	}
	
	public function editQueryField(&$dbhandle){
		if (!$this->does_edit)
			return;
		return $this->localkey;
	}
	
	public function buildInsert(&$ins_arr,&$form){
	}

	public function buildUpdate(&$ins_arr,&$form){
	}

	public function DispForm(array &$qrow,&$form,$active){
	//public function DispEdit($scol, $sparams, $svalue, $DBHandle = null){
		$refname = $this->refname ;
		$refid = $this->refid ;
		if( $this->refkey !=NULL)
			$refkey = $this->refkey ;
		else
			$refkey = $this->refid;
		$DBHandle=$form->a2billing->DBHandle();
		?><input type="hidden" name="<?= $form->prefix.$this->fieldname . '_action' ?>" value="">
		<?php
		$QUERY = str_dbparams($DBHandle, "SELECT $refkey, $refname FROM $this->reftable ".
			"WHERE $refid = %1 ; ",array($qrow[$this->localkey]));
			
		if ($form->FG_DEBUG>2)
			echo "QUERY: ".htmlspecialchars( $QUERY)."\n<br>";
		$res = $DBHandle->Execute ($QUERY);
		if (! $res){
			if ($form->FG_DEBUG) {
				?> Query failed: <?= htmlspecialchars($QUERY) ?><br>
				Error: <?= $DBHandle->ErrorMsg() ?><br>
				<?php
			}
			echo _("No data found!");
		}else{
		?> <table class="FormRRt1">
		<thead>
		<tr><td><?= $this->fieldtitle ?></td> <?php 
			if ($active) {
			?><td><?= _("Action") ?></td><?php 
			}
		?></tr>
		</thead>
		<tbody>
		<?php while ($row = $res->fetchRow()){ ?>
			<tr><td><?= htmlspecialchars($row[$refname]) ?></td>
			<?php if ($active) { 
				if ($this->refkey !=NULL){ ?>
			    <td><a onClick="formRRdelete('<?= $form->prefix.$this->fieldname ?>','<?= $form->prefix.$this->fieldname. '_action' ?>','<?= $form->prefix.$this->fieldname .'_del' ?>','<?= $row[$refkey] ?>')" > <img src="./Images/icon-del.png" alt="<?= _("Remove this") ?>" /></a></td>
			   <?php } else { ?>
			    <td><a onClick="formRRdelete2('<?= $form->prefix.$this->fieldname ?>','<?= $form->prefix.$this->fieldname. '_action' ?>','<?= $form->prefix.$this->fieldname .'_del' ?>','<?= $row[$refkey] ?>','<?= $row[$refname] ?>')" > <img src="./Images/icon-del.png" alt="<?= _("Remove this") ?>" /></a></td>
			</tr>
		<?php		}
			     }
			} ?>
		</tbody>
		</table>
		<?php if ($active) { ?>
		<input type="hidden" name="<?= $form->prefix.$this->fieldname . '_del' ?>" value="">
		<?php 	if ($this->refkey ==NULL) { ?>
		<input type="hidden" name="<?= $form->prefix.$this->fieldname . '_del2' ?>" value="">
		<?php 	  }
			}
		}
		
		if ($active)
			$this->dispAddBox($form);
		$this->dispEditDescr();
	}
	
	public function PerformObjEdit(&$form){
		if ($form->FG_DEBUG)
			echo "PerformObjEdit stub!!\n";
	}
	
	/** By default, no addition method is defined */
	public function dispAddbox(&$form){
		if ($this->debug_st)
			echo "dispAddbox stub!!\n";
	
	}
	protected function dispEditDescr(){
	?>
	<div class="descr"><?= $this->editDescr?></div>
	<?php
	}
};


/** Rev Ref, where the add field is a combo with not-refed entries.
 
 	However, we need $refoid, the (primary) key for $reftable.
 	
 	Adding an item means UPDATE $reftable SET $refid = %id ;
 	Deleting means UPDATE $reftable SET $refid = NULL;
 */
class RevRefcmb extends RevRef {
	
	public function dispAddbox(/*-*/$scol, $sparams, $svalue, $DBHandle ){
			// Now, find those refs NOT already in the list!
		$QUERY = "SELECT $refkey, $refname FROM $this->reftable ".
			"WHERE $refid IS NULL;";
		$res = $DBHandle->Execute ($QUERY);
		if (! $res){
			if ($this->debug_st) {
				?> Query failed: <?= htmlspecialchars($QUERY) ?><br>
				Error: <?= $DBHanlde->ErrorMsg() ?><br>
				<?php
			}
			echo _("No additional data found!");
		}else{
			$add_combos = array(array('', _("Select one to add..")));
			while ($row = $res->fetchRow()){
				$add_combos[] = $row;
			}
			gen_Combo($this->fieldname. '_add','',$add_combos);
			 ?>
			 <a onClick="formRRadd('<?= $this->fieldname ?>','<?=$this->fieldname. '_action' ?>')"><img src="../Images/btn_Add_94x20.png" alt="<?= _("Add this") ?>" /></a>
		<?php
		}
	}
	
	
	public function PerformObjEdit($scol, $sparams, $DBHandle = null){
		$oeaction = getpost_single($this->fieldname.'_action');
		if ($this->debug_st)
			echo "Object edit! Action: $oeaction <br>\n";
		$oeid = getpost_single($sparams[1]);
		switch($oeaction){
		case 'add':
			$QUERY = str_dbparams($DBHandle,"UPDATE $this->reftable SET $this->refid = %1 ".
				"WHERE $this->refkey = %2;", array($oeid, getpost_single($this->fieldname.'_add')));
			$res = $DBHandle->Execute ($QUERY);
			if (! $res){
				if ($this->debug_st) {
					?> Query failed: <?= htmlspecialchars($QUERY) ?><br>
					Error: <?= $DBHanlde->ErrorMsg() ?><br>
					<?php
				}
				echo _("Could not add!");
			}else{
				if ($this->debug_st)
					echo _("Item added!");
			}
			break;
		case 'delete':
			$QUERY = str_dbparams($DBHandle,"UPDATE $this->reftable SET $this->refid = NULL ".
				"WHERE $this->refkey = %1;", array(getpost_single($this->fieldname.'_del')));
			$res = $DBHandle->Execute ($QUERY);
			if (! $res){
				if ($this->debug_st) {
					?> Query failed: <?= htmlspecialchars($QUERY) ?><br>
					Error: <?= $DBHanlde->ErrorMsg() ?><br>
					<?php
				}
				echo _("Could not delete!");
			}else{
				if ($this->debug_st)
					echo _("Item deleted!");
			}
			break;
		default:
			echo "Unknown action $oeaction";
		}
	}

};

/** Rev ref, where the add field is free text.
 	
 	Adding an item means INSERT INTO $reftable ($refid , $refname) VALUES (id, txt) ;
 	Deleting means DELETE FROM $reftable WHERE $refkey = key;
 */

class RevRefTxt extends RevRef {

	var $addprops="size=40 maxlength=100";
	var $addval="";
	
	public function dispAddbox(&$form){
			// Now, find those refs NOT already in the list!
		?>
		<input class="form_enter" type="INPUT" name="<?=$this->fieldname. '_new'. $this->refname ?>" value="<?= $this->addval ?>" <?= $this->addprops ?> />
		<a onClick="formRRadd('<?= $this->fieldname ?>','<?=$this->fieldname. '_action' ?>')"><img src="./Images/btn_Add_94x20.png" alt="<?= _("Add this") ?>" /></a>
		<?php
		
	}

	/** Called by the framework when we have requested an 'object-edit'
	*/
	public function PerformObjEdit(&$form){
		$DBHandle=$form->a2billing->DBHandle();
		$oeaction = /* $form-> */ getpost_single($this->fieldname.'_action');
		$oeid = /* $form-> */ getpost_single($this->localkey);
		
		$dbg_elem = new DbgElem();
		if ($form->FG_DEBUG>0)
			$form->pre_elems[]= &$dbg_elem;

		switch($oeaction){
		case 'add':
			$QUERY = str_dbparams($DBHandle,"INSERT INTO $this->reftable ($this->refid, $this->refname) VALUES(%1, %2);",
				array($oeid, getpost_single($this->fieldname.'_new' . $this->refname)));
			$dbg_elem->content .= "Query: ". htmlspecialchars($QUERY) ."\n";
			
			$res = $DBHandle->Execute ($QUERY);
			
			if (! $res){
				$form->pre_elems[]= new ErrorElem(str_params(_("Cannot insert new %1"),array($this->fieldtitle),1));
				$dbg_elem->content .= "Query failed: $DBHanlde->ErrorMsg(); \n";
			}else{
				$dbg_elem->content .= "Item added!";
			}
			break;
		case 'delete':
			if ($this->refkey != NULL)
				$QUERY = str_dbparams($DBHandle,"DELETE FROM $this->reftable WHERE $this->refkey = %1 ;",
					array(getpost_single($this->fieldname.'_del')));
			else
				$QUERY = str_dbparams($DBHandle,"DELETE FROM $this->reftable WHERE $this->refid = %1 AND $this->refname = %2 ;",
					array(getpost_single($this->fieldname.'_del'),getpost_single($this->fieldname.'_del2')));
			$dbg_elem->content .= "Query: ". htmlspecialchars($QUERY) ."\n";
			
			$res = $DBHandle->Execute ($QUERY);
			
			if (! $res){
				$form->pre_elems[]= new ErrorElem(str_params(_("Cannot delete %1"),array($this->fieldtitle),1));
				$dbg_elem->content .= "Query failed: $DBHanlde->ErrorMsg(); \n";
			}else{
				$dbg_elem->content .= "Item deleted!";
			}
			
			break;
		default:
			$dbg_elem->content .= "Unknown action $oeaction";
		}
		
		return 'ask-edit';
	}
};
?>