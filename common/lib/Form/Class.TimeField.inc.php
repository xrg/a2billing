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

?>
