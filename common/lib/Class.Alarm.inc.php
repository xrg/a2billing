<?php

/** Instance: Holds the volatile data for an alarm
*/
class AlmInstance {
	public $id;
	public $parent_alarm;
	public $ar_id;
	public $name;
	public $alm_subtype;
	public $alm_params = array();
	public $ar_params = array();
	public $ar_status;
	
	function AlmInstance(&$alarm, $dbrow = null) {
		$this->parent_alarm = &$alarm;
		if (is_array($dbrow)){
			$this->id = $dbrow['id'];
			$this->ar_id = $dbrow['ar_id'];
			$this->alm_subtype = $dbrow['asubtype'];
			$this->name = $dbrow['name'];
			$this->tomail = $dbrow['tomail'];
			$this->ar_status= $dbrow['ar_status'];
			parse_str($dbrow['aparams'], $this->alm_params);
			parse_str($dbrow['ar_params'], $this->ar_params);
		}
	}
		
	/** Just saves the params and timestamp as an alarm_run row */
	public function Save($status = null){
		$dbhandle = A2Billing::DBHandle();
		global $verbose;
		if ($status)
			$this->ar_status=$status;
		if (empty($this->ar_status))
			$this->ar_status=1;
		if ($this->ar_id){
			// update a previous alarm_run record
			$qry = sql_dbparams($dbhandle,"UPDATE cc_alarm_run
				SET tmodify = now(), status = %#2, params = %!3
				WHERE id = %1;",
				array($this->ar_id, $this->ar_status, 
				arr2url($this->ar_params)));
		}else { //no run record, insert
			$qry = str_dbparams($dbhandle,"INSERT INTO cc_alarm_run(alid,status,params)
				VALUES(%#1,%#2,%!3);",
				array($this->id, $this->ar_status, 
				arr2url($this->ar_params)));
		}
		$res= $dbhandle->Execute($qry);
		if (!$res){
			echo "Cannot mark alarm-run: ";
			echo $dbhandle->ErrorMsg() ."\n";
		}elseif($dbhandle->Affected_Rows()<1){
			echo "Cannot update alarm run.\n";
		}
		if ($verbose>1){
			$str= $dbhandle->NoticeMsg();
			if ($str)
				echo $str . "\n";
		}
	}
};

/** Alarm: an object that can perform (periodic) actions and alarm
    The object should be reusable (as if), and as of such should not
    contain volatile data.
*/
abstract class A2BAlarm {
	static public $mail_flag = false; ///< If set, mail has to be sent
	
	function A2BAlarm($dbrow = null){
	}

	abstract public function ProcessAlarm(AlmInstance $inst);
	
	public function sendMail($templ,$tomail,$locale,$params){
		global $verbose;
		$dbhandle = A2Billing::DBHandle();
		if ($verbose>2)
			echo "Sending $templ mail to $tomail\n";
		$res = $dbhandle->Execute("SELECT create_mail(?, ?, ?, ?);",
			array($templ, $tomail, $locale,arr2url( $params)));
		if (!$res){
			echo "Cannot mark mail: ";
			echo $dbhandle->ErrorMsg() ."\n";
		}elseif($res->EOF){
			echo "Cannot send mail, no template?\n";
		}
		
		$this->mail_flag=true;
	}
	
	public function sendSysMail($templ,$inst,$params){
		return $this->sendMail($templ,$inst->tomail, 'C',$params);
	}
	
};

?>