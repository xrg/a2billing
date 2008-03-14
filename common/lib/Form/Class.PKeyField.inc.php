<?php
require_once("Class.BaseField.inc.php");

	/** Field class for primary key (scalar)
	*/

class PKeyField extends BaseField {

	function PKeyField($fldtitle, $fldname,$fldwidth = null){
		$this->does_edit = false;
		$this->does_add = false;
		$this->fieldname = $fldname;
		$this->fieldtitle = $fldtitle;
		$this->listWidth = $fldwidth;
	}

	public function DispList(array &$qrow,&$form){
		echo htmlspecialchars($qrow[$this->fieldname]);
	}
	public function renderSpecial(array &$qrow,&$form,$rmode, &$robj){
		return $qrow[$this->fieldname];
	}

	/// Reimplement: the key may not be listed, but is always queried
	public function listQueryField(&$dbhandle){
		if ($this->fieldexpr)
			return $this->fieldexpr ." AS ". $this->fieldname;
		return $this->fieldname;
	}
	
	public function editQueryClause(&$dbhandle,&$form){
		return str_dbparams($dbhandle,
			"$this->fieldname = %#1",array($form->getpost_dirty($this->fieldname)));
	}

	public function editHidden(array &$qrow,&$form){
		$val = $form->getpost_dirty($this->fieldname);
		if (preg_match('/^\-?[0-9]+$/',$val)<1)
			return null;
		return array ($this->fieldname => $val);
	}
	
	public function listHidden(array &$qrow,&$form){
		$val = $qrow[$this->fieldname];
		if (preg_match('/^\-?[0-9]+$/',$val)<1)
			return null;
		return array ($this->fieldname => $val);
	}
};

/** Also hyperlink to the Edit page
    \note This will NOT pass itself as the primary key field, but will ask
    the form to do so. This way, the form will always decide which key to 
    use. */
class PKeyFieldEH extends PKeyField{
	
	public function DispList(array &$qrow,&$form){
		echo '<a href="'. $form->askeditURL($qrow) . '">';
		echo htmlspecialchars($qrow[$this->fieldname]);
		echo '</a>';
	}
	
};

/** A primary key that can accept any (text) data. */
class PKeyFieldTxt extends PKeyField{
	public function editQueryClause(&$dbhandle,&$form){
		return str_dbparams($dbhandle,
			"$this->fieldname = %1",array($form->getpost_dirty($this->fieldname)));
	}

	public function editHidden(array &$qrow,&$form){
		$val = $form->getpost_dirty($this->fieldname);
		return array ($this->fieldname => $val);
	}
	
	public function listHidden(array &$qrow,&$form){
		$val = $qrow[$this->fieldname];
		return array ($this->fieldname => $val);
	}
};

/** Primary key, with arbitrary hyperlink */
class PKeyFieldH2 extends PKeyField {
	public $href;
	public $message;

	function PKeyFieldH2($fldtitle, $fldname,$hr,$msg){
		$this->does_edit = false;
		$this->does_add = false;
		$this->fieldname = $fldname;
		$this->fieldtitle = $fldtitle;
		$this->href = $hr;
		$this->message = $msg;
	}

	public function DispList(array &$qrow,&$form){
		if (empty($this->href))
			return parent::DispList($qrow,$form);
		
		$msg ='';
		if (!empty($this->message))
			$msg = ' title="' . htmlspecialchars($this->message) . '"';
		$href = str_alparams($this->href, $qrow);
		echo '<a href="' .$href.'"'.$msg .'>';
		parent::DispList($qrow,$form);
		echo '</a>';
	}

};
?>