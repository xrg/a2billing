<?php

/** Generalized config classes */


abstract class ConfigGen {

	/**   Retrieve a config variable.
		If the variable is already there, it will just get it.
		
	    @param $group The group of the variable
	    @param $var   The name of the variable
	    @param $default  Return this if variable is not set
	    @param $doerror  If true, this function will throw an exception if var
	                     is not set
	    @return The value of the variable or @b $default
	*/
	abstract public function GetCfgVar($group, $var, $default = null,$doerror = false);
	/** Attempt to preset the default value for some var.
	    This function should first lookup the var in the running config. If not
	    found, it should set it to that default, so that subsequent calls to 
	    GetCfgVar() will return this default value.
	*/
	abstract public function SetDefVar($group, $var, $default);

};

class IniConfig extends ConfigGen {
	protected $cfg = null;
	
	function ConfigGen($fname){
		if ($fname && is_string($fname)){
			if (file_exists($fname))
				$this->cfg = parse_ini_file($fname,true);
			else throw new Exception ("Ini doesn't exist: ".$fname);
		}
		else $this->cfg = array();
	}

	public function GetCfgVar($group, $var, $default = null,$doerror = false){
		if (!is_string($group) || !is_string($var))
			throw new Exception('Cannot handle non-strings here!');
		if (isset($this->cfg[$group]) && isset($this->cfg[$group][$var]))
			return $this->cfg[$group][$var];
		elseif ($doerror)
			throw new Exception("No config value for $group:$var");
		else
			return $default;
	}
	
	public function SetDefVar($group, $var, $default){
		if (!is_string($group) || !is_string($var))
			throw new Exception('Cannot handle non-strings here!');
		if (isset($this->cfg[$group]) && isset($this->cfg[$group][$var]))
			return;
		if (!isset($this->cfg[$group]))
			$this->cfg[$group]=array();
		$this->cfg[$group][$var]=$default;
	}

};

?>
