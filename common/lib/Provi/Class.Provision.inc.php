<?php

/** Base class for provisioning engines
*/
abstract class ProviEngine {
	public $dbg_elem; ///< put a DbgElem here to collect debug info

	/** Return the mimetype of the generated content */
	abstract public function getMimeType();
	abstract public function Init(array $args);
	
	abstract public function genContent(&$outstream);
	
	protected function out($level,$str){
		if(isset($this->dbg_elem) && ($this->dbg_elem instanceof StringElem)){
			$this->dbg_elem->content.=$str."\n";
		}
		else
			fwrite(STDERR,$str."\n");
	}
};

?>