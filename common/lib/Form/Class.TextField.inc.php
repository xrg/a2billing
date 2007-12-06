<?php
require_once("Class.BaseField.inc.php");

class TextField extends BaseField{

	function TextField($fldtitle, $fldname, $flddescr, $fldwidth = null){
		$this->fieldname = $fldname;
		$this->fieldtitle = $fldtitle;
		$this->listWidth = $fldwidth;
		$this->editDescr = $flddescr;
	}

	public function DispList(array &$qrow,&$form){
		echo htmlspecialchars($qrow[$this->fieldname]);
	}
	
};

?>