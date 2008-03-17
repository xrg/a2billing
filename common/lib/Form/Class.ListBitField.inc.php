<?php
/** Bitfield list field */
class ListBitField extends BaseField {
	public $field_values; ///< Array with the field values
	public $def_value;

	function ListBitField($fldtitle, $fldname,$fldvals, $flddescr=null, $fldwidth = null){
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
		echo htmlspecialchars($val[1]);
	}
	public function renderSpecial(array &$qrow,&$form,$rmode, &$robj){
		$val = $qrow[$this->fieldname];
		return ($val[1]);
	}
	
	public function DispAddEdit($val,&$form){
		$tmp_int = (integer)$val;
		$tmp_value= array();
		$tmp_i = 1;
		for($tmp_i=1;($tmp_i!=0) && ($tmp_int!=0);$tmp_i*=2){
			if ($tmp_int & $tmp_i){
				$tmp_value[] = $tmp_i;
				$tmp_int -= $tmp_i;
			}
		}
		if ($form->FG_DEBUG>1)
			echo "TmpVal: $val <br>";
		if ($form->FG_DEBUG>2)
			echo "TmpValues:". print_r($tmp_value,true) ."<br>\n";
		gen_Combo($form->prefix.$this->fieldname,$tmp_value,$this->field_values,true);
		?>
		<div class="descr"><?= $this->editDescr?></div>
		<?php
	}

	public function buildValue($val,&$form){
		// PHP is kind enough to provide us an array into $val
		$tmp_val = 0;
		foreach($val as $bval)
			$tmp_val += $bval;
		return $tmp_val;
	}

	public function getDefault() {
		return $this->def_value;
	}

};
?>