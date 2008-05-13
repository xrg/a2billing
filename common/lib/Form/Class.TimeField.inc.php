<?php
require_once("Class.TextField.inc.php");
	/* Time and date fields */

/** Date and time (timestamp). ISO string + calendar */
class DateTimeField extends TextField {
	public $def_date;
	static $sqlTimeFmt = null;
	
	public function detailQueryField(&$dbhandle){
		if (!empty($this->fieldexpr))
			return $this->fmtContent($this->fieldexpr) . ' AS ' .$this->fieldname;
		else
			return $this->fmtContent($this->fieldname) . ' AS ' .$this->fieldname;
	}
	
	protected function fmtContent($content){
		if (DateTimeField::$sqlTimeFmt == null)
			DateTimeField::$sqlTimeFmt= _("YYYY-MM-DD HH24:MI:SS TZ");
		return 'to_char(' . $content .', \''.DateTimeField::$sqlTimeFmt .
			'\')';
	}

	public function getDefault() {
		if(!empty($this->def_date)){
			$tstamp = strtotime($this->def_date);
			if ($tstamp !== false)
				return date('Y-m-d H:i:s',$tstamp);
		}
		return $this->def_value;
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
				if (!empty($this->fieldexpr))
					$grps[] = $this->fieldexpr;
				else
					$grps[] = $this->fieldname;
				$fields[] = $fld . " AS ". $this->fieldname;
			}
			elseif (is_string($sum_fns[$this->fieldname]))
				$fields[] = $sum_fns[$this->fieldname] .$fld. 
					" AS ". $this->fieldname;
						
			$fields_out[] = array($this->fmtContent($this->fieldname), 
				$this->fieldname);
			
		}
		
		$this->listQueryTable($table,$form);
		$tmp= $this->listQueryClause($dbhandle,$form);
		if ( is_string($tmp))
			$clauses[] = $tmp;
	}

	public function getOrder(&$form){
		if ($this->fieldexpr)
			return $this->fieldexpr;
		else
			return $form->model_table.'.'.$this->fieldname;
	}

};

class DateField extends DateTimeField {
	static $sqlDateFmt = null;
	
	protected function fmtContent($content){
		if (DateField::$sqlDateFmt == null)
			DateField::$sqlDateFmt= _("YYYY-MM-DD");
		return 'to_char(' . $content .', \''.DateField::$sqlDateFmt .
			'\')';
	}

};

/** Time of week field */
class TimeOWField extends TextField {
};

class DateTimeFieldN extends DateTimeField {
	public function buildValue($val,&$form){
		if (empty($val))
			return null;
		else
			return $val;
	}

};

class DateTimeFieldDH extends DateTimeField {
	public function DispList(array &$qrow,&$form){
		if ($form->getAction()!='list')
			return parent::DispList($qrow,$form);
		
		$pkparams= $form->getPKparams($qrow,true);
		$pkparams[$form->prefix.'action']='details';
		$url= $_SERVER['PHP_SELF'].$form->gen_AllGetParams($pkparams);
		echo '<a href="' .$url. '">';
		parent::DispList($qrow,$form);
		echo '</a>';
	}

};

?>
