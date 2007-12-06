<?php
require_once("Class.BaseField.inc.php");

	/** Field class for primary key (scalar)
	*/

class PKeyField extends BaseField {

	function PKeyField($fldtitle, $fldname,$fldwidth = null){
		$this->fieldname = $fldname;
		$this->fieldtitle = $fldtitle;
		$this->listWidth = $fldwidth;
	}

	public function DispList(array &$qrow,&$form){
		echo htmlspecialchars($qrow[$this->fieldname]);
	}

	/// Reimplement: the key may not be listed, but is always queried
	public function listQueryField(&$dbhandle){
		if ($this->fieldexpr)
			return $this->fieldexpr ." AS ". $this->fieldname;
		return $this->fieldname;
	}

};

?>