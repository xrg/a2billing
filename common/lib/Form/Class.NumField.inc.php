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
	public function renderSpecial(array &$qrow,&$form,$rmode, &$robj){
		return $qrow[$this->fieldname];
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
	
	public function DispList(array &$qrow,&$form){
		echo htmlspecialchars(round($qrow[$this->fieldname],5));
	}
	

	public function buildValue($val,&$form){
		if (preg_match('/^\-?[0-9]+([,.][0-9]*)?$/',$val)>=1)
		return str_replace(',','.',$val);
	}

};

class PercentField extends FloatField {
	public function DispList(array &$qrow,&$form){
		if ($qrow[$this->fieldname] !== NULL)
			echo htmlspecialchars(round($qrow[$this->fieldname]*100.0,2))."%";
	}
};

class MoneyField extends FloatField {

	public function DispAddEdit($val,&$form){
	?><input type="text" name="<?= $form->prefix.$this->fieldname ?>" value="<?=
		htmlspecialchars((float)$val);?>" />&nbsp;&nbsp;<?= $form->a2billing->currency ?>
	<div class="descr"><?= $this->editDescr?></div>
	<?php
	}
	
	public function DispList(array &$qrow,&$form){
		echo htmlspecialchars($qrow[$this->fieldname]);
	}
	
	public function detailQueryField(&$dbhandle){
		if ($this->fieldexpr)
			$fld= $this->fieldexpr;
		else
			$fld = $this->fieldname;
		return "format_currency($fld, '". A2Billing::instance()->currency ."') AS " .
			$this->fieldname;
	}
	
	public function buildSumQuery(&$dbhandle, &$sum_fns,&$fields,&$fields_out, &$table,&$table_out,
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
				$fields[] = "$fld AS ". $this->fieldname;
			}
			elseif (is_string($sum_fns[$this->fieldname]))
				$fields[] = $sum_fns[$this->fieldname] ."($fld) AS ". $this->fieldname;
			
			$fields_out[] = array("format_currency($this->fieldname, '".
				$form->a2billing->currency ."')", $this->fieldname);
			
		}
		
		$this->listQueryTable($table,$form);
		$tmp= $this->listQueryClause($dbhandle,$form);
		if ( is_string($tmp))
			$clauses[] = $tmp;
	}
	
	public function editQueryField(&$dbhandle){
		if (!$this->does_edit)
			return;
		if ($this->fieldexpr)
			$fld= $this->fieldexpr;
		else
			$fld = $this->fieldname;
		return "conv_currency_from($fld, '". A2Billing::instance()->currency ."') AS " .
			$this->fieldname;
	}

	public function buildInsert(&$ins_arr,&$form){
		if (!$this->does_add)
			return;
		$ins_arr[] = array($this->fieldname,
			$this->buildValue($form->getpost_dirty($this->fieldname),$form),
			str_dbparams($form->a2billing->DBHandle(), "conv_currency_to( ?, %1)",
				array($form->a2billing->currency)));
	}

	public function buildUpdate(&$ins_arr,&$form){
		if (!$this->does_edit)
			return;
		$ins_arr[] = str_dbparams($form->a2billing->DBHandle(),
			$this->fieldname . " = conv_currency_to( %1, %2)",
			array($this->buildValue($form->getpost_dirty($this->fieldname),$form),
				$form->a2billing->currency));
	}
	
	public function getOrder(&$form){
		if ($this->fieldexpr)
			return $this->fieldexpr;
		else
			return $form->model_table.'.'.$this->fieldname;
	}

};

class MoneyField2 extends MoneyField {
	public function detailQueryField(&$dbhandle){
		if ($this->fieldexpr)
			$fld= $this->fieldexpr;
		else
			$fld = $this->fieldname;
		return "format_currency2($fld, '". A2Billing::instance()->currency ."') AS " .
			$this->fieldname;
	}
	
	public function buildSumQuery(&$dbhandle, &$sum_fns,&$fields, &$fields_out, &$table,&$table_out,
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
				$fields[] = "$fld AS ". $this->fieldname;
			}
			elseif (is_string($sum_fns[$this->fieldname]))
				$fields[] = $sum_fns[$this->fieldname] ."($fld) AS ". $this->fieldname;
			
			$fields_out[] = array("format_currency2($this->fieldname, '".
				$form->a2billing->currency ."')", $this->fieldname);
			
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

/** Displays integer seconds in time format */
class SecondsField extends IntField{
	public function DispList(array &$qrow,&$form){
		$val = $qrow[$this->fieldname];
		if (empty($val) || !is_numeric($val))
			echo _("0 sec");
		else{
			echo sprintf("%d:%02d s",intval($val / 60),intval($val%60));
		}
		//echo htmlspecialchars($qrow[$this->fieldname]);
	}

};

/** A number (=epoch) translated into a human date */
class EpochField extends IntField {
	public function DispList(array &$qrow,&$form){
		$val = $qrow[$this->fieldname];
		if (empty($val) || !is_numeric($val))
			return;
		echo date(_("Y-m-d H:i:s T"),$val);
	}
};

class EpochFieldN extends EpochField {
	public function buildValue($val,&$form){
		if (empty($val) || !is_numeric($val))
			return null;
		else
			return $val;
	}
};

?>