<?php
require_once("Class.BaseField.inc.php");

	/** Field class for primary key (scalar)
	*/

class ClauseField extends BaseField {
	protected $fieldvalue;
	public $parentfield;
	
	function ClauseField($fldname,$fldvalue,$pfield = null){
		$this->does_edit = false;
		$this->does_add = false;
		$this->does_list = false;
		$this->fieldname = $fldname;
		$this->fieldvalue = $fldvalue;
		if ($pfield)
			$this->parentfield = $pfield;
	}

	public function DispList(array &$qrow,&$form){
	}

	public function renderSpecial(array &$qrow,&$form,$rmode, &$robj){
	}
	
	public function listQueryClause(&$dbhandle,&$form){
		return str_dbparams($dbhandle,
			"$this->fieldname = %1",array($this->fieldvalue));
	}

	public function editQueryClause(&$dbhandle,&$form){
		return $this->listQueryClause($dbhandle,$form);
	}
	
	public function ResetValue($val){
		$this->fieldvalue = $val;
	}

	public function buildInsert(&$ins_arr,&$form){
		$ins_arr[] = array($this->fieldname,
			$this->fieldvalue);
	}

	public function buildUpdate(&$ins_arr,&$form){
		$ins_arr[] = array($this->fieldname,
			$this->fieldvalue);
	}
	
	public function buildSumQuery(&$dbhandle, &$sum_fns,&$fields,&$fields_out, &$table,
		&$clauses, &$grps, &$form){
		
		$this->listQueryTable($table,$form);
		$tmp= $this->listQueryClause($dbhandle,$form);
		if ( is_string($tmp))
			$clauses[] = $tmp;
	}


};

class FreeClauseField extends BaseField {
	protected $clause;
	
	function FreeClauseField($cls){
		$this->does_edit = false;
		$this->does_add = false;
		$this->does_list = false;
		$this->clause = $cls;
	}

	public function DispList(array &$qrow,&$form){
	}
	public function renderSpecial(array &$qrow,&$form,$rmode, &$robj){
	}
	
	public function listQueryClause(&$dbhandle,&$form){
		return $this->clause;
	}

	public function editQueryClause(&$dbhandle,&$form){
		return $this->listQueryClause($dbhandle,$form);
	}
	
	public function ResetClause($cls){
		$this->clause = $cls;
	}

	public function buildInsert(&$ins_arr,&$form){
		//$ins_arr[] = $this->clause;
	}

	public function buildUpdate(&$ins_arr,&$form){
		//$ins_arr[] = $this->clause;
	}
	
	public function buildSumQuery(&$dbhandle, &$sum_fns,&$fields,&$fields_out, &$table,&$table_out,
		&$clauses, &$grps, &$form){
		
		$this->listQueryTable($table,$form);
		$tmp= $this->listQueryClause($dbhandle,$form);
		if ( is_string($tmp))
			$clauses[] = $tmp;
	}


};

?>