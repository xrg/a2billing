<?php
	/** skeleton, abstract class for Model fields
	    Some functions are provided, empty, for convenience.
	*/

abstract class BaseField {
	public $fieldname;
	public $fieldexpr = null; ///< if set, use this expression in select
	public $fieldtitle; ///< The user-visible title of the field.
	public $fieldacr = null;   ///< An acronym, to be used in list table
	
	public $does_list = true; ///< Field will appear in list view
	public $does_list_sort = true; ///< Field will be sortable in list view
	public $does_edit = true; ///< Field will appear in edit view
	public $does_add = true; ///< Field will appear in add view
	public $does_del = true; ///< Field will appear in del view
	
	public $listWidth = null;
	public $editDescr = null;
	
	//public var $

	/** Produce the necessary html code at the body.
	    Useful for styles, scripts etc.
	    @param $action	The form action. Sometimes, the header is only needed 
	    		for edits etc.
	   */
	public function html_body($action = null){
	}

	/** Display the field inside the list table.
	   \param $qrow An array(fieldname=> val, ...) resulting from the sql query
	   \param $form The form
	   */
	abstract public function DispList(array &$qrow,&$form);
	
	/** Editing may be skipped, by default */
	public function DispEdit(array &$qrow,&$form){
		$this->DispAddEdit($qrow[$this->fieldname],$form);
	}
	
	/** Produce the array of hidden values for an edit form.
	    \return if needed, an array of (key => value) for the hidden field
	*/
	public function editHidden(array &$qrow,&$form){
		return null;
	}
	
	public function DispAdd(&$form){
		$v = '';
		if ($form->getAction() =='ask-add2')
			$v=$form->getpost_dirty($this->fieldname);
		else
			$v=$this->getDefault();
		$this->DispAddEdit($v,$form);
	}

	/** Alternatively, a field can have a common method for both
	    add and edit actions.
	    \param $val the value of the field
	    */
	public function DispAddEdit($val,&$form){
		//stub!
	}
	
	/** Return the default value (for a addition) */
	public function getDefault() {
		return '';
	}
	
	/** query expression */
	public function listQueryField(&$dbhandle){
		if (!$this->does_list)
			return;
		return $this->detailQueryField($dbhandle);
	}
	
	public function detailQueryField(&$dbhandle){
		if ($this->fieldexpr)
			return $this->fieldexpr ." AS ". $this->fieldname;
		return $this->fieldname;
	}
	
	public function editQueryField(&$dbhandle){
		if (!$this->does_edit)
			return;
		if ($this->fieldexpr)
			return $this->fieldexpr ." AS ". $this->fieldname;
		return $this->fieldname;
	}

	/** Add this clause to the query */
	public function listQueryClause(&$dbhandle){
		return null;
	}
	
	public function detailQueryClause(&$dbhandle,&$form){
		return $this->editQueryClause($dbhandle,$form);
	}
	
	public function listQueryTable(&$table,&$form){
	}
	
	public function detailQueryTable(&$table,&$form){
	}
	
	public function editQueryClause(&$dbhandle,&$form){
		return null;
	}
	
	
	public function delQueryClause(&$dbhandle,&$form){
		return $this->editQueryClause($dbhandle,$form);
	}
	
// 	public function addQueryClause(&$dbhandle,&$form){
// 		return null;
// 	}

	public function buildInsert(&$ins_arr,&$form){
		if (!$this->does_add)
			return;
		$ins_arr[] = array($this->fieldname,
			$form->getpost_dirty($this->fieldname));
	}

	public function buildUpdate(&$ins_arr,&$form){
		if (!$this->does_edit)
			return;
		$ins_arr[] = array($this->fieldname,
			$form->getpost_dirty($this->fieldname));
	}


	/** Render the List head cell (together with 'td' element) */
	function RenderListHead(&$form){
		if (!$this->does_list)
			return;
		echo "<td";
		if ($this->listWidth)
			echo ' width="'.$this->listWidth .'"';
		echo '>';
		
		if ($this->does_list_sort)
			$this->RenderListHead_sort($form);
		else
			$this->RenderListHead_i($form);
		echo "</td>\n";
	}
	
	protected function RenderListHead_sort(&$form){
		$sens = $form->sens;
		if (!$sens) $sens = 'asc';
		
		$order_sel = false;
		if ($form->order == $this->fieldname) {
			if ($sens == 'asc')
				$sens = 'desc';
			else
				$sens = 'asc';
			$order_sel = true;
		}
		echo '<a href="';
		echo $form->selfUrl(array( $form->prefix.'order'=> $this->fieldname, $form->prefix. 'sens'=>$sens));
		echo '">';
		$this->RenderListHead_i($form);
		if ($order_sel) {
			if($sens == 'asc')
				echo '&nbsp;<img src="./Images/icon_up_12x12.png" border="0">';
			else
				echo '&nbsp;<img src="./Images/icon_down_12x12.png" border="0">';
		}
		echo '</a>';
	}
	
	protected function RenderListHead_i($form){
		if ($this->fieldacr){
			echo '<acronym title="'.htmlspecialchars($this->fieldtitle).'" >';
			echo htmlspecialchars($this->fieldacr);
			echo '<acronym>';
			
		}else
			echo htmlspecialchars($this->fieldtitle);
	}
	
	public function RenderListCell(array &$qrow,&$form){
		if (!$this->does_list)
			return;
		echo "<td>";
		$this->DispList($qrow,$form);
		echo "</td>";
	}
	
	public function RenderEditTitle(&$form){
		echo htmlspecialchars($this->fieldtitle);
	}
	public function RenderAddTitle(&$form){
		$this->RenderEditTitle($form);
	}
	
	/** Called by the framework when we have requested an 'object-edit'
	    \return The next 'action' state for the form.
	*/
	public function PerformObjEdit(&$form){
		return null;
	}

};

function dontList(BaseField &$bf){
	$bf->does_list = false;
	return $bf;
}

?>