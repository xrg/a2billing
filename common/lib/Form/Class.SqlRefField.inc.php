<?php
require_once("Class.BaseField.inc.php");

class SqlRefField extends BaseField{
	public $field_values; ///< Array with the cached field values
	public $def_value;
	public $reftable;
	public $refname;
	public $refid ;

	function SqlRefField($fldtitle, $fldname,$reftbl, $refid = 'id', $refname = 'name', $flddescr=null, $fldwidth = null){
		$this->fieldname = $fldname;
		$this->fieldtitle = $fldtitle;
		$this->reftable = $reftbl;
		$this->refname = $refname;
		$this->refid = $refid;
		$this->listWidth = $fldwidth;
		$this->editDescr = $flddescr;
	}

	public function DispList(array &$qrow,&$form){
		echo htmlspecialchars($qrow[$this->fieldname.'_'.$this->refname]);
		if ($form->FG_DEBUG>3)
			echo " (Ref:" .htmlspecialchars($qrow[$this->fieldname]) .")";
	}
	
	public function DispAddEdit($val,&$form){
		if (!$this->field_values)
			$this->prepare($form->a2billing->DBHandle());
		gen_Combo($form->prefix.$this->fieldname,$val,$this->field_values);
		?>
		<div class="descr"><?= $this->editDescr?></div>
		<?php
	}

	public function getDefault() {
		return $this->def_value;
	}
	
	public function listQueryField(&$dbhandle){
		if (!$this->does_list)
			return;
		return $this->detailQueryField($dbhandle);
	}
	
	public function detailQueryField(&$dbhandle){
		return array($this->fieldname, $this->fieldname.'_'.$this->refname);
	}

	public function listQueryTable(&$table,&$form){
		if ($this->does_list)
			return $this->detailQueryTable($table,$form);
		else
			return null;
	}
	public function detailQueryTable(&$table,&$form){
		$table .= ' LEFT OUTER JOIN ' .
			str_params("( SELECT %1 AS %0_%1, %2 AS %0_%2 FROM %3) AS %0_table ".
				"ON %0_%1 = %0",
			    array($this->fieldname,$this->refid,$this->refname, $this->reftable));
	}
	
	protected function prepare(&$dbhandle){
		//echo "Prepare!";
		$debug = $GLOBALS['FG_DEBUG'];
		$qry = "SELECT $this->refid,$this->refname FROM $this->reftable;";
		if ($debug>3)
			echo "Query: $qry<br>\n";
		$res = $dbhandle->Execute($qry);
		if (!$res ){
			if ($debug>1)
				echo "Cannot fetch ref values: ". $dbhandle->ErrorMsg();
		}else
			while($row = $res->fetchRow())
				$this->field_values[] = 
					array ($row[$this->refid],$row[$this->refname]);
		if (($debug>3) && (count($this->field_values)<=20))
			print_r($this->field_values);
	}

};

class SqlRefFieldN extends SqlRefField{
	
	protected function prepare(&$dbhandle){
		$this->field_values[] = array ( null, _("(none)"));
		parent::prepare($dbhandle);
	}
	
	
	public function buildValue($val,&$form){
		if (empty($val))
			return null;
		else
			return $val;
	}
};

?>