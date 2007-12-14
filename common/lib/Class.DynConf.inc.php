<?php

require_once('Class.Config.inc.php');
require_once('Class.A2Billing.php');

/** This class holds the dynamic (DB) configuration entries

    It will only fetch them on demand. Then, it will cache
    them forever (?).

*/


class DynConf extends ConfigGen {
	static protected $the_instance = null;
	protected $DBHandle;
	protected $prepOne = null;
	protected $groups = array();

	/** Returns the config's instance.
	   Of course, it will create one when called for the first time.
	*/
	public static function &instance() {
		if (!self::$the_instance){
			self::$the_instance = new self();
			self::$the_instance->init();
		}
		
		return self::$the_instance;
	}
	
	function init(){
		$this->DBHandle= &A2Billing::DBHandle();
		$this->prepOne = $this->DBHandle->Prepare('SELECT val FROM cc_sysconf WHERE grp = ? AND name = ?');
		if (!$this->prepOne)
			throw new Exception('Cannot prepare statement!');
	}
	
	/**   Retrieve a config variable.
		If the variable is already there, it will just get it.
		
	    @param $group The group of the variable
	    @param $var   The name of the variable
	    @param $default  Return this if variable is not set
	    @param $doerror  If true, this function will throw an exception if var
	                     is not set
	    @return The value of the variable or @b $default
	*/
	public function GetCfgVar($group, $var, $default = null,$doerror = false){
		if (!is_string($group) || !is_string($var))
			throw new Exception('Cannot handle non-strings here!');
		if (isset($this->groups[$group]) && isset($this->groups[$group][$var]))
			return $this->groups[$group][$var];
		// echo "Fetching $group/$var..\n";
		if (!isset($this->groups[$group]))
			$this->groups[$group]=array();
		$res = $this->DBHandle->Execute($this->prepOne,array($group,$var));
		if (($res) && (!$res->EOF)){
			$row = $res->fetchRow();
			$this->groups[$group][$var]=$row[0];
			return $this->groups[$group][$var];
		}
		if (isset($GLOBALS['FG_DEBUG']) && ($GLOBALS['FG_DEBUG'] >1)){
			error_log("Could not fetch $group:$var " .$this->DBHandle->ErrorMsg());
		}
		if ($doerror){
			throw new Exception("Cannot fetch $group:$var from database. ".$this->DBHandle->ErrorMsg());
		}
		// Do not cache this.. it may be updated in the meantime
		return $default;
	}
	
	/** Prepare all values for config group.
	    In AGI, it is convenient to fetch all values of the group from the
	    database. Call this and it will cache them.
	    @note This fn will throw an exception if the db or the group doesn't exist.
	    @return none
	*/
	public function PrefetchGroup($group){
		if (!is_string($group))
			throw new Exception('Cannot handle non-strings here!');
		// Hmm, if I don't check for the existance of the group, calling this
		// function twice will update it from the db!
		
		$res = $this->DBHandle->Execute('SELECT name, val FROM cc_sysconf WHERE grp = ?',array($group));
		if (!$res || $res->EOF) {
			throw new Exception("Cannot prefetch conf [$group]" . $this->DBHandle->ErrorMsg());
		}
		if (!isset($this->groups[$group]))
			$this->groups[$group]=array();
		$row= null;
		while ($row= $res->fetchRow()){
			$this->groups[$group][$row[0]] = $row[1];
		}
	}
	
	
	/** Convenience function, call GetCfgVar from any context */
	public static function GetCfg($group, $var, $default = null,$doerr = false){
		return self::instance()->GetCfgVar($group, $var, $default,$doerr);
	}
	
	public function SetDefVar($group, $var, $default){
		if (!is_string($group) || !is_string($var))
			throw new Exception('Cannot handle non-strings here!');
		if (isset($this->groups[$group]) && isset($this->groups[$group][$var]))
			return;
		// echo "Fetching $group/$var..\n";
		if (!isset($this->groups[$group]))
			$this->groups[$group]=array();
		$res = $this->DBHandle->Execute($this->prepOne,array($group,$var));
		if (($res) && (!$res->EOF)){
			$row = $res->fetchRow();
			$this->groups[$group][$var]=$row[0];
			return;
		}
		$this->groups[$group][$var]=$default;
	}
	
	/// For debugging only: print cached config
	public function dbg_print_cached_config(){
		print_r($this->groups);
		echo "\n";
	}
};

?>