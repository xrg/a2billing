<?php
require_once("Class.BaseField.inc.php");

	/** A field that is queried, but not displayed
	*/

class HiddenField extends BaseField {

	function HiddenField($fldtitle, $fldname,$fldexpr = null){
		$this->does_edit = false;
		$this->does_add = false;
		$this->does_list = false;
		$this->fieldname = $fldname;
		$this->fieldtitle = $fldtitle;
		$this->fieldexpr = $fldexpr;
	}

	public function DispList(array &$qrow,&$form){
	}
	public function renderSpecial(array &$qrow,&$form,$rmode, &$robj){
	}
	
	/// Reimplement: the key may not be listed, but is always queried
	public function listQueryField(&$dbhandle){
		return $this->detailQueryField($dbhandle);
	}
	
	public function detailQueryField(&$dbhandle){
		if ($this->fieldexpr)
			return $this->fieldexpr ." AS ". $this->fieldname;
		return $this->fieldname;
	}
	public function editQueryField(&$dbhandle){
		return $this->detailQueryField($dbhandle);
	}

	public function editQueryClause(&$dbhandle,&$form){
	}

	public function editHidden(array &$qrow,&$form){
	}
	
	public function listHidden(array &$qrow,&$form){
	}
};

?>