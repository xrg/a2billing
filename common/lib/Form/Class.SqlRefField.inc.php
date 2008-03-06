<?php
require_once("Class.BaseField.inc.php");

class SqlRefField extends BaseField{
	public $field_values; ///< Array with the cached field values
	public $def_value;
	public $reftable;
	public $refname;
	public $refid ;
	public $refclause;
	public $comboid;
	public $combotable; ///< Alt table to use for the combo
	public $combofield; ///< Alt field to use for the combo
	public $comboclause;
	public $comboorder; ///< Order for the combo box, SQL expression
	public $list_ref = false ; ///< If true, ref will be visible in list
	public $detail_ref = false;
	public $refexpr;
	public $list_url;
	public $detail_url;


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
		$act = $form->getAction();
		$url=null;
		if (($act == 'list') && $this->list_url)
			$url = str_alparams($this->list_url,$qrow);
		elseif (($act == 'details') && $this->detail_url)
			$url = str_alparams($this->detail_url,$qrow);
		if ($url)
			echo '<a href="'.$url .'" >';
		echo htmlspecialchars($qrow[$this->fieldname.'_'.$this->refname]);
		if ( ($this->list_ref && $act == 'list') || ($this->detail_ref && $act == 'details'))
			echo " (" .htmlspecialchars($qrow[$this->fieldname]) .")";
		else if ($form->FG_DEBUG>3)
			echo " (Ref:" .htmlspecialchars($qrow[$this->fieldname]) .")";
		if ($url)
			echo '</a>';
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
		$rclause = '';
		if (!empty($this->refclause))
			$rclause = ' WHERE ' . $this->refclause;
		if ($this->fieldexpr)
			$fld = $this->fieldexpr;
		else
			$fld = $this->fieldname;
		
		if ($this->refexpr)
			$refname = $this->refexpr;
		else
			$refname = $this->refname;
		$table .= ' LEFT OUTER JOIN ' .
			str_params("( SELECT %1 AS %0_%1, %5 AS %0_%2 FROM %3 $rclause) AS %0_table ".
				"ON %0_%1 = %4",
			    array($this->fieldname,$this->refid,$this->refname, $this->reftable, $fld, $refname));
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
				$fields[] = "$fld AS ". $this->fieldname;
				$fields[] = $this->fieldname.'_'.$this->refname;
				$grps[] = $this->fieldname.'_'.$this->refname;
				
			}
			// TODO: how do we aggregate on refs?
			elseif (is_string($sum_fns[$this->fieldname]))
				$fields[] = $sum_fns[$this->fieldname] ."($fld) AS ". $this->fieldname;
			elseif (is_array($sum_fns[$this->fieldname]))
				$fields[] = str_dbparams($dbhandle, '%1 AS '.$this->fieldname,$sum_fns[$this->fieldname]);
			
			$this->listQueryTable($table,$form);
			$tmp= $this->listQueryClause($dbhandle,$form);
			if ( is_string($tmp))
				$clauses[] = $tmp;
		}
		
	}

	
	protected function prepare(&$dbhandle){
		//echo "Prepare!";
		$debug = $GLOBALS['FG_DEBUG'];
		if (!empty($this->combotable))
			$reftable = $this->combotable;
		else
			$reftable = $this->reftable;
		if (!empty($this->combofield))
			$refname = $this->combofield . " AS " .$this->refname;
		else
			$refname = $this->refname;

		if (!empty($this->comboid))
			$refid = $this->comboid . " AS " .$this->refid;
		else
			$refid = $this->refid;

		$qry = "SELECT $refid,$refname FROM $reftable";
		if (!empty($this->comboclause))
			$qry .= ' WHERE ' . $this->comboclause;
		elseif (!empty($this->refclause))
			$qry .= ' WHERE ' . $this->refclause;
		
		if (!empty($this->comboorder))
			$qry .= ' ORDER BY ' . $this->comboorder;

		$qry .= ';';
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
	
	/** Set the urls so that details will point to the referring entity */
	function SetRefEntity($fname){
		$this->detail_url = $fname .'?action=details&' . $this->refid .'=%' .
			$this->fieldname ;
	}
	function SetRefEntityL($fname){
		$this->list_url = $fname .'?action=details&' . $this->refid .'=%' .
			$this->fieldname ;
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

/** Class for sql ref where the combo would have too many values.. 
	\todo have popup window to select among the available entries.
*/
class SqlBigRefField extends SqlRefField{

	public function DispAddEdit($val,&$form){
		?><input type="text" name="<?= $form->prefix.$this->fieldname ?>" value="<?=
		htmlspecialchars($val);?>" />
		<div class="descr"><?= $this->editDescr?></div>
		<?php
	}

	protected function prepare(&$dbhandle){
		//stub!
	}
	
	public function buildValue($val,&$form){
		if (empty($val))
			return null;
		else
			return $val;
	}
};

?>