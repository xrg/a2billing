<?php
require_once("Class.BaseField.inc.php");

/** Helper class, provides the necessary javascript.. */
class RevRef2Header extends ElemBase {
	public $formName ='Frm';
	//TODO: When form->prefix is used, Frm should be updated here..
	
	function Render(){
	}
	
	// stub functions..
	function RenderHead() {
	?>

<script language="JavaScript" type="text/JavaScript">
<!--
function formRR2delete(rid,raction,rname, instance){
  document.<?= $this->formName ?>.action.value = "object-edit";
  document.<?= $this->formName ?>.sub_action.value = rid;
  document.<?= $this->formName ?>.elements[raction].value='delete';
  if (rname != null) document.<?= $this->formName ?>.elements[rname].value = instance;
  <?= $this->formName ?>.submit();
}

function formRR2add(rid,raction){
  document.<?= $this->formName ?>.action.value = "object-edit";
  document.<?= $this->formName ?>.sub_action.value = rid;
  document.<?= $this->formName ?>.elements[raction].value='add';
  <?= $this->formName ?>.submit();
}
//-->
</script>
	
	<?php
	}
};

$PAGE_ELEMS[] = new RevRef2Header();

/** Reverse reference 2: this.id -> assoc.left => assoc.right -> present.id => present.name
*/
class RevRef2 extends BaseField{
	var $assoctable;
	var $assocleft;
	var $assocright;
	
	var $presenttable;
	var $presentname = 'name';
	var $presentid = 'id';
	

	function RevRef2($fldtitle,$fldname,$lkey,$assoctable,$asl,$asr,
		$ptable, $prid = 'id',$prname= 'name', $flddescr = null){
		$this->fieldname = $fldname;
		$this->fieldtitle = $fldtitle;
		$this->assoctable = $assoctable;
		$this->localkey= $lkey;
		$this->assocleft = $asl;
		$this->assocright = $asr;
		$this->presenttable = $ptable;
		$this->presentid = $prid;
		$this->presentname = $prname;
		$this->editDescr = $flddescr;
		$this->does_list = false;
		$this->does_add = false;
	}

	public function DispList(array &$qrow,&$form){
		if ($form->getAction()=='details')
			return $this->DispForm($qrow,$form,false);
	}
	public function renderSpecial(array &$qrow,&$form,$rmode, &$robj){
		// Todo: sth like the values in an array..
	}

	public function detailQueryField(&$dbhandle){
		if (!$this->does_list)
			return;
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
	
	protected function DispForm(array &$qrow,&$form, $active){
		$DBHandle = $form->a2billing->DBHandle();
		$presentname = $this->presenttable . '.' . $this->presentname ;
		$presentid = $this->presenttable . '.' . $this->presentid ;
		$assocleft= $this->assoctable . '.' . $this->assocleft;
		$assocright= $this->assoctable . '.' . $this->assocright;
		
		if ($active){
		?><input type="hidden" name="<?= $form->prefix.$this->fieldname . '_action' ?>" value="">
		<?php
		}
		
		$QUERY = str_dbparams($DBHandle, "SELECT $presentid, $presentname FROM $this->presenttable, $this->assoctable ".
			"WHERE $assocleft= %1 AND $assocright = $presentid ; ",array($qrow[$this->localkey]));
			
		$res = $DBHandle->Execute ($QUERY);
		if (! $res){
			if ($form->FG_DEBUG) {
				?> Query failed: <?= htmlspecialchars($QUERY) ?><br>
				Error: <?= $DBHandle->ErrorMsg() ?><br>
				<?php
			}
			echo _("No data found!");
		}else{
		?> <table class="FormRR2t1">
		<thead>
		<tr><td><?= $this->fieldtitle ?></td><?php
		if ($active){
			?><td><?= _("Action") ?></td></tr><?php
		} ?>
		</thead>
		<tbody>
		<?php while ($row = $res->fetchRow()){ ?>
			<tr><td><?= htmlspecialchars($row[$this->presentname]) ?></td><?php
			if ($active) { ?>
			    <td><a onClick="formRR2delete('<?= $form->prefix.$this->fieldname ?>','<?=$form->prefix.$this->fieldname. '_action' ?>','<?= $form->prefix.$this->fieldname .'_del' ?>','<?= $row[$this->presentid] ?>')" > <img src="./Images/icon-del.png" alt="<?= _("Remove this") ?>" /></a></td>
			<?php }
			?></tr>
		<?php } ?>
		</tbody>
		</table>
		<?php if ($active) { ?>
		<input type="hidden" name="<?= $form->prefix.$this->fieldname . '_del' ?>" value="">
		<?php }
		}
		
		if (!$active)
			return;
		// Now, find those refs NOT already in the list!
		$QUERY = str_dbparams($DBHandle, "SELECT $presentid, $presentname FROM $this->presenttable ".
			"WHERE $presentid NOT IN (SELECT $assocright FROM $this->assoctable WHERE $assocleft= %1); ",
			array($qrow[$this->localkey]));
		$res = $DBHandle->Execute ($QUERY);
		if (! $res){
			if ($form->FG_DEBUG) {
				?> Query failed: <?= htmlspecialchars($QUERY) ?><br>
				Error: <?= $DBHandle->ErrorMsg() ?><br>
				<?php
			}
			echo _("No additional data found!");
		}else{
			$add_combos = array(array('', _("Select one to add..")));
			while ($row = $res->fetchRow()){
				$add_combos[] = array($row[$this->presentid],$row[$this->presentname]);
			}
			gen_Combo($form->prefix.$this->fieldname. '_add','',$add_combos);
			?>
			 <a onClick="formRR2add('<?= $form->prefix.$this->fieldname ?>','<?=$form->prefix.$this->fieldname. '_action' ?>')"><img src="./Images/btn_Add_94x20.png" alt="<?= _("Add this") ?>" /></a>
		<?php
		}
		
		?><div class="descr"><?= $this->editDescr?></div><?php
	}
	
	public function DispEdit(array &$qrow,&$form){
		return $this->DispForm($qrow,$form,true);
	}

	public function PerformObjEdit(&$form){
		$DBHandle=$form->a2billing->DBHandle();
		$oeaction = $form->getpost_single($this->fieldname.'_action');
		$oeid = $form->getpost_single($this->localkey);
		
		$dbg_elem = new DbgElem();
		if ($form->FG_DEBUG>0)
			$form->pre_elems[]= &$dbg_elem;

		switch($oeaction){
		case 'add':
			$QUERY = str_dbparams($DBHandle,"INSERT INTO $this->assoctable ($this->assocleft, $this->assocright) VALUES(%1, %2);",
				array($oeid, $form->getpost_single($this->fieldname.'_add')));
			$dbg_elem->content .= "Query: ". htmlspecialchars($QUERY) ."\n";
			$res = $DBHandle->Execute ($QUERY);
			
			if (! $res){
				$form->pre_elems[]= new ErrorElem(str_params(_("Cannot insert new %1"),array($this->fieldtitle),1));
				$dbg_elem->content .= "Query failed:". $DBHandle->ErrorMsg() ."\n";
			}else{
				$dbg_elem->content .= "Item added!";
			}
			break;
			
		case 'delete':
			$QUERY = str_dbparams($DBHandle,"DELETE FROM $this->assoctable WHERE $this->assocleft = %1 AND $this->assocright = %2;",
				array($oeid, $form->getpost_single($this->fieldname.'_del')));
			$dbg_elem->content .= "Query: ". htmlspecialchars($QUERY) ."\n";
			$res = $DBHandle->Execute ($QUERY);
			if (! $res){
				$form->pre_elems[]= new ErrorElem(str_params(_("Cannot delete %1"),array($this->fieldtitle),1));
				$dbg_elem->content .= "Query failed: " .$DBHandle->ErrorMsg() ." \n";
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