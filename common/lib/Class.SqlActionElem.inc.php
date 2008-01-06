<?php
require_once("Class.ActionElem.inc.php");

class SqlActionElem extends ActionElem {
	public $expectRows = false;
	public $QueryString = '';
	
	public function PerformAction(){
		if ($this->action != $this->action_do)
			return;

		$dbg_elem = new DbgElem();
		$dbhandle = $this->a2billing->DBHandle();
		
		if ($this->FG_DEBUG)
			$this->pre_elem = &$dbg_elem;
		$query = str_aldbparams($dbhandle,$this->QueryString,$this->_dirty_vars);
		
		if (strlen($query)<1){
			$this->pre_elem = new ErrorElem("Cannot update, internal error");
			$dbg_elem->content.= "Action: no query!\n";
		}
		
		$dbg_elem->content .= $query . "\n";
		
		$res = $dbhandle->Execute($query);
		
		if (!$res){
			$this->action = $this->action_fail;
			$dbg_elem->content.= $dbhandle->ErrorMsg() ."\n";
// 			throw new Exception( $err_str);
		}elseif ($this->expectRows && ($dbhandle->Affected_Rows()<1)){
			// No result rows: update clause didn't match
			$dbg_elem->content.= ".. EOF, no rows!\n";
			$dbg_elem->content.= $dbhandle->ErrorMsg() ."\n";
			$dbg_elem->obj = $dbhandle->Affected_Rows();
			$this->action = $this->action_fail;
		} else {
			$dbg_elem->content.= "Success: Rows: ". $dbhandle->Affected_Rows() . "\n";
			$dbg_elem->content.= $dbhandle->ErrorMsg() ."\n";
			$this->action = $this->action_success;
			$this->qryres = &$res;
		}
		
	}

};

?>