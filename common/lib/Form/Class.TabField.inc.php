<?php
require_once("Class.BaseField.inc.php");

	/** A field that is queried, but not displayed
	*/

class TabField extends BaseField {
	public $caption;
	
	function TabField($caption){
		$this->does_list_sort=false;
		$this->does_edit=false;
		$this->does_add=false;
		$this->does_del=false;
		$this->fieldtitle=null;
		
		$this->caption=$caption;
	}
	
	public function listQueryField(&$dbhandle){
		return null;
	}

	public function renderSpecial(array &$qrow,&$form,$rmode, &$robj){
	}
	
	public function buildInsert(&$ins_arr,&$form){
	}

	public function buildUpdate(&$ins_arr,&$form){
	}
	
	/** Editing may be skipped, by default */
	public function DispEdit(array &$qrow,&$form, $id_fragment){
		$this->DispAddEdit($qrow[$this->fieldname],$form, $id_fragment);
	}
	
	/** Produce the array of hidden values for an edit form.
	    \return if needed, an array of (key => value) for the hidden field
	*/
	public function editHidden(array &$qrow,&$form){
		return null;
	}
	
	public function DispAdd(&$form, $id_fragment){
		$v = $form->getpost_dirty($this->fieldname);
		if (!isset($v))
			$v=$this->getDefault();
		$this->DispAddEdit($v,$form, $id_fragment);
	}

	/** Alternatively, a field can have a common method for both
	    add and edit actions.
	    \param $val the value of the field
	    */
	public function DispAddEdit($val,&$form){
		//stub!
	}
	
	public function DispList(array &$qrow,&$form){
		//stub!
	}
	
	/** Select the rigth separator for Tabs : edit, add or delete View
	*/
	public function DispTab($val,&$form, $id_fragment){
		if ($form->getAction()=='ask-del')
			$this->DispTabDel($val,$form, $id_fragment);
		elseif ($form->getAction()=='ask-edit')
			$this->DispTabEdit($val,$form, $id_fragment);
		else
			$this->DispTabAdd($val,$form, $id_fragment);
	}
	
	/** Produce the separator with Tabs for Edit View
	*/
	public function DispTabEdit($val,&$form, $id_fragment){
		
		if ($id_fragment > 1){
			?>
			<tr class="confirm"><td colspan=2 align="right">
			<button type=submit>
			<?= str_params(_("Update this %1"),array($form->model_name_s),1) ?>
			<img src="./Images/icon_arrow_orange.png" ></button>
			<td>
			</tr>
			</tbody></table></div>
			<?php
		}
		echo '<div id="fragment-'.$id_fragment.'">';
		if ($id_fragment > 1){
			?>
			<table class="editForm" cellspacing="2">
				<thead><tr><td class="field">&nbsp;</td><td class="value">&nbsp;</td></tr></thead>
				<tbody>
			<?php
		}
	}
	
	/** Produce the separator with Tabs for Add View
	*/
	public function DispTabAdd($val,&$form, $id_fragment){
		
		if ($id_fragment > 1){
			?>
			<tr class="confirm"><td colspan=2 align="right">
			<button type=submit>
			<?= str_params(_("Create this %1"),array($form->model_name_s),1) ?>
			<img src="./Images/icon_arrow_orange.png" ></button>
			<td>
			</tr>
			</tbody></table></div>
			<?php
		}
		echo '<div id="fragment-'.$id_fragment.'">';
		if ($id_fragment > 1){
			?>
			<table class="addForm" cellspacing="2">
				<thead><tr><td class="field">&nbsp;</td><td class="value">&nbsp;</td></tr></thead>
				<tbody>
			<?php
		}
	}
	
	/** Produce the separator with Tabs for Del View
	*/
	public function DispTabDel($val,&$form, $id_fragment){
		
		if ($id_fragment > 1){
			?>
			<tr class="confirm"><td colspan=2 align="right">
			<button type=submit>
			<?= str_params(_("Delete this %1"),array($form->model_name_s),1) ?>
			<img src="./Images/icon_arrow_orange.png" ></button>
			<td>
			</tr>
			</tbody></table></div>
			<?php
		}
		echo '<div id="fragment-'.$id_fragment.'">';
		if ($id_fragment > 1){
			?>
			<table class="detailForm" cellspacing="2">
				<thead><tr><td class="field">&nbsp;</td><td class="value">&nbsp;</td></tr></thead>
				<tbody>
			<?php
		}
	}
};
