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
	?><input type="text" name="<?= $this->fieldname ?>" value="<?=
		htmlspecialchars($val);?>" />
	<div class="descr"><?= htmlspecialchars($this->editDescr)?></div>
	<?php
	}
	
	public function getDefault() {
		return $this->def_value;
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
	?><input type="text" name="<?= $this->fieldname ?>" value="<?=
		htmlspecialchars($val);?>" />
	<div class="descr"><?= htmlspecialchars($this->editDescr)?></div>
	<?php
	}

};

?>