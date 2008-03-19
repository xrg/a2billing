<?php

/** Base class for importing engines
*/
abstract class ImportEngine {
	public $dbg_elem; ///< put a DbgElem here to collect debug info

	/** Return the allowed mimetypes. */
	abstract public function getMimeTypes();
	abstract public function Init(array $args);
	
	abstract public function parseContent(&$instream);
	
	protected function out($level,$str){
		if(isset($this->dbg_elem) && ($this->dbg_elem instanceof StringElem)){
			$this->dbg_elem->content.=$str."\n";
		}
		else
			fwrite(STDERR,$str."\n");
	}
	
	//TODO: base functions for dbg rendering..
};


?>