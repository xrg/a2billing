<?php
require_once("Class.Provision.inc.php");
class AsteriskIniProvi extends ProviEngine {

	public function AsteriskIniProvi(){
	}

	public function getMimeType(){
		return 'text/plain';
	}
	
	public function Init(array $args){
	}
	
	public function genContent(&$outstream){
		fwrite($outstream,"Test!\n");
	}

};

?>
