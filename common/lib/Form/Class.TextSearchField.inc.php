<?php
require_once("Class.TextField.inc.php");

/** A text search field, for the SelectForm */
class TextSearchField extends TextField {
	public $have_modes = array('no','st','ct','eq','en');
	public $case_sensitive =false;
	
	public function DispSearch(&$form){
		$cur_val = $form->getpost_dirty('use_'.$this->fieldname);
		if(empty($cur_val))
			$cur_val = 'no';
		foreach($this->have_modes as $mo){
			echo "<input type=\"radio\" name=\"". $form->prefix ."use_" .$this->fieldname .
				"\" value=\"$mo\"";
			if ($cur_val == $mo )
				echo ' checked';
			echo '> ';
			switch ($mo) {
			case 'no':
				echo _("Ignore");
				break;
			case 'st':
				echo _("Starts with");
				break;
			case 'eq':
				echo _("Equals");
				break;
			case 'ct':
				echo _("Contains");
				break;
			case 'en':
				echo _("Ends in");
				break;
			};
			echo '&nbsp; ';
		}
		$this->DispAdd($form);
	}

	public function buildSearchClause(&$dbhandle,&$form, $search_exprs){
		$val = $this->buildValue($form->getpost_dirty($this->fieldname),$form);
		$mo_val = $form->getpost_dirty('use_'.$this->fieldname);
		if (empty($mo_val))
			$mo_val = 'no';
		if (empty($this->fieldexpr))
			$fldex = $this->fieldname;
		else
			$fldex = $this->fieldexpr;
		
		if($this->case_sensitive)
			$like ='LIKE';
		else
			$like ='ILIKE';
		
		if ($val == null)
			switch($mo_val) {
			case 'no':
			default:
				return null;
			case 'eq':
				return "$fldex IS NULL";
			case 'st':
			case 'en':
				return null;
			
			}
		else
		    switch($mo_val){
		    case 'eq':
		    	if ($this->case_sensitive)
		    		return str_dbparams($dbhandle,"$fldex = %1",array($val));
		    	else
		    		return str_dbparams($dbhandle,"lower($fldex) = lower(%1)",array($val));
		    case 'st':
		    	return str_dbparams($dbhandle,"$fldex $like %1 || '%%'",array($val));
		    case 'en':
		    	return str_dbparams($dbhandle,"$fldex $like '%%' || %1",array($val));
		    case 'ct':
		    	return str_dbparams($dbhandle,"$fldex $like '%%' || %1 || '%%'",array($val));
		    case 'no':
		    default:
		    	return null;
		    }
	}

};

?>