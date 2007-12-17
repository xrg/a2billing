<?php
require_once("Class.TextField.inc.php");
	/* Time and date fields */

/** Date and time (timestamp). ISO string + calendar */
class DateTimeField extends TextField {
	public $def_date;
	
	public function getDefault() {
		if($this->def_date){
			$tstamp = strtotime($this->def_date);
			if ($tstamp !== false)
				return date('Y-m-d H:i:s',$tstamp);
		}
		return $this->def_value;
	}

};

/** Time of week field */
class TimeOWField extends TextField {
};

class DateTimeFieldN extends DateTimeField {
	public function buildInsert(&$ins_arr,&$form){
		if (!$this->does_add)
			return;
		$val = $form->getpost_dirty($this->fieldname);
		if (!strlen($val) || !is_numeric($val))
			$val = null;
		$ins_arr[] = array($this->fieldname, $val);
	}

	public function buildUpdate(&$ins_arr,&$form){
		if (!$this->does_edit)
			return;
		$val = $form->getpost_dirty($this->fieldname);
		if (!strlen($val) || !is_numeric($val))
			$val = null;
		$ins_arr[] = array($this->fieldname, $val);
	}

};
?>
