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

	public function buildValue($val,&$form){
		if (is_numeric($val))
			return $val;
		return (double) str_replace(',','.',$val);
	}

};

class MoneyField extends FloatField {

	public function DispAddEdit($val,&$form){
	?><input type="text" name="<?= $form->prefix.$this->fieldname ?>" value="<?=
		htmlspecialchars($val);?>" />&nbsp;&nbsp;<?= $form->a2billing->currency ?>
	<div class="descr"><?= $this->editDescr?></div>
	<?php
	}
	
	public function detailQueryField(&$dbhandle){
		if ($this->fieldexpr)
			$fld= $this->fieldexpr;
		else
			$fld = $this->fieldname;
		return "format_currency($fld, '". A2Billing::instance()->currency ."') AS " .
			$this->fieldname;
	}
	
	public function buildSumQuery(&$dbhandle, &$sum_fns,&$fields, &$table,
		&$clauses, &$grps, &$form){
		if (!$this->does_list)
			return;
		
		// fields
		if ($this->fieldexpr)
			$fld = $this->fieldexpr;
		else
			$fld = $this->fieldname;
		
		if (isset($sum_fns[$this->fieldname]) && !is_null($sum_fns[$this->fieldname])){
			if ($sum_fns[$this->fieldname] === true){
				$grps[] = $this->fieldname;
				$fields[] = "format_currency($fld, '".
					$form->a2billing->currency ."') ".
					"AS ". $this->fieldname;
			}
			elseif (is_string($sum_fns[$this->fieldname]))
				$fields[] = "format_currency(".
				$sum_fns[$this->fieldname] ."($fld), '".
					$form->a2billing->currency ."') ".
					"AS ". $this->fieldname;
			
		}
		
		$this->listQueryTable($table,$form);
		$tmp= $this->listQueryClause($dbhandle,$form);
		if ( is_string($tmp))
			$clauses[] = $tmp;
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