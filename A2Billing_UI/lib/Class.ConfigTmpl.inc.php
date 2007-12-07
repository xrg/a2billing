<?php
	// Templates for configuration values

class ConfigGroupTmpl {
	public $name;
	public $description;
	public $mandatory = true;
	public $items = array();

	ConfigGroupTmpl($name, $descr, $mand = true){
		$this->name = $name;
		$this->description = $descr;
		$this->mandatory = $mand;
	}
};

class ConfigVarTmpl {
	public $name;
	public $description;
	public $def_val;
	public $vtype;
	public $mandatory = false;
	
	ConfigVarTmpl($name, $descr,$def=null, $typ ='string', $mand = true){
		$this->name = $name;
		$this->description = $descr;
		$this->def_val = $def;
		$this->vtype = $typ;
		$this->mandatory = $mand;
	}
};

?>