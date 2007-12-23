<?php
require_once("Class.TextField.inc.php");
	/* Time and date fields */

/** Date and time (timestamp). ISO string + calendar */
class DateTimeField extends TextField {
	public $def_date;
	static $sqlTimeFmt = null;
	
	public function detailQueryField(&$dbhandle){
		if (DateTimeField::$sqlTimeFmt == null)
			DateTimeField::$sqlTimeFmt= _("IYYY-MM-DD HH24:MI:SS TZ");
		return 'to_char(' . $this->fieldname .', \''.DateTimeField::$sqlTimeFmt .
			'\') AS ' .$this->fieldname;
	}

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
