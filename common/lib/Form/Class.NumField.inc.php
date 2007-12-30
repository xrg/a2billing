<?php
require_once("Class.BaseField.inc.php");

/** Integer (numeric) field */
class IntField extends BaseField{
	public $def_value = 0;
	
	function IntField($fldtitle, $fldname, $flddescr=null, $fldwidth = null){
		$this->fieldname = $fldname;
		$this->fieldtitle = $fldtitle;
		$this->listWidth = $fldwidth;
		$this->editDescr = $flddescr;
	}

	public function DispList(array &$qrow,&$form){
		echo htmlspecialchars($qrow[$this->fieldname]);
	}
	
	public function DispAddEdit($val,&$form){
	?><input type="text" name="<?= $form->prefix.$this->fieldname ?>" value="<?=
		htmlspecialchars($val);?>" />
	<div class="descr"><?= $this->editDescr?></div>
	<?php
	}
	
	public function getDefault() {
		return $this->def_value;
	}


};

class IntFieldN extends IntField{

	function IntField($fldtitle, $fldname, $flddescr=null, $fldwidth = null){
		$this->def_value = NULL;
		$this->fieldname = $fldname;
		$this->fieldtitle = $fldtitle;
		$this->listWidth = $fldwidth;
		$this->editDescr = $flddescr;
	}

	public function buildValue($val,&$form){
		if (empty($val) || !is_numeric($val))
			return null;
		else
			return $val;
	}

};

class FloatField extends IntField{

	function FloatField($fldtitle, $fldname, $flddescr=null, $fldwidth = null){
		$this->fieldname = $fldname;
		$this->fieldtitle = $fldtitle;
		$this->listWidth = $fldwidth;
		$this->editDescr = $flddescr;
	}
	
	public function DispAddEdit($val,&$form){
	?><input type="text" name="<?= $form->prefix.$this->fieldname ?>" value="<?=
		htmlspecialchars($val);?>" />
	<div class="descr"><?= $this->editDescr?></div>
	<?php
	}

};

class BoolField extends IntField{

	public function DispList(array &$qrow,&$form){
		$val = $qrow[$this->fieldname];
		if (($val == 't') || ($val === true) || ($val == 1))
			echo _("TRUE");
		else
			echo _("FALSE");
	}
	
	public function DispAddEdit($val,&$form){
	?><input type="checkbox" name="<?= $form->prefix.$this->fieldname ?>" value="t" <?php
		if (($val == 't') || ($val === true) || ($val == 1))
			echo 'checked ';
		?>/>
	<div class="descr"><?= $this->editDescr?></div>
	<?php
	}
	
	public function buildValue($val,&$form){
		if (empty($val))
			return 'f';
		elseif (($val=='t') || ($val=='true'))
			return 't';
		else
			return 'f';
	}
};

?>