<?php
require_once("Class.BaseField.inc.php");

class RefField extends BaseField{
	public $field_values; ///< Array with the field values
	public $def_value;

	function RefField($fldtitle, $fldname,$fldvals, $flddescr=null, $fldwidth = null){
		$this->fieldname = $fldname;
		$this->fieldtitle = $fldtitle;
		$this->field_values = $fldvals;
		$this->listWidth = $fldwidth;
		$this->editDescr = $flddescr;
		if (count($fldvals))
			$this->def_value=$fldvals[0][0];
	}

	public function DispList(array &$qrow,&$form){
		$val = $qrow[$this->fieldname];
		foreach ($this->field_values as $fval)
			if($fval[0] == $val){
			echo htmlspecialchars($fval[1]);
			return;
		}
		
		if ($form->FG_DEBUG>0)
			echo "Unknown val: " .$val ;
	}
	public function renderSpecial(array &$qrow,&$form,$rmode, &$robj){
		$val = $qrow[$this->fieldname];
		foreach ($this->field_values as $fval)
			if($fval[0] == $val){
			return $fval[1];
		}
		
		if ($form->FG_DEBUG>0)
			return "Unknown val: " .$val ;
	}
	
	public function DispAddEdit($val,&$form){
		gen_Combo($form->prefix.$this->fieldname,$val,$this->field_values);
		?>
		<div class="descr"><?= $this->editDescr?></div>
		<?php
	}

	public function getDefault() {
		return $this->def_value;
	}

};

class RefFieldN extends RefField{
	public function buildValue($val,&$form){
		if (empty($val))
			return null;
		else
			return $val;
	}
};

?>