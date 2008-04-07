<?php
	/** skeleton, abstract class for Model fields
	    Some functions are provided, empty, for convenience.
	*/

abstract class BaseField {
	public $fieldname;
	public $fieldexpr = null; ///< if set, use this expression in select
	public $fieldtitle; ///< The user-visible title of the field.
	public $fieldedittitle; // title of the field for edition or addition action
	public $fieldacr = null;   ///< An acronym, to be used in list table
	
	public $does_list = true; ///< Field will appear in list view
	public $does_list_sort = true; ///< Field will be sortable in list view
	public $does_edit = true; ///< Field will appear in edit view
	public $does_add = true; ///< Field will appear in add view
	public $does_del = true; ///< Field will appear in del view
	
	public $add_tab = false;
	public $tablabel= null;
	
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
	
	/** Echo the (formatted) representation of the field, in special render mode
	   \param $qrow An array(fieldname=> val, ...) resulting from the sql query
	   \param $form The form
	   \param $rmode the rendering mode
	   */
	abstract public function renderSpecial(array &$qrow,&$form,$rmode, &$robj);

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
		$v = $form->getpost_dirty($this->fieldname);
		if (!isset($v))
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
	
	/** Display this as a search form element
	   By default, in search forms, the does_add flag controlls whether
	   this field is optional */
	public function DispSearch(&$form){
		if (!$this->does_add){
		?><input type="checkbox" name="<?= $form->prefix.'use_'.$this->fieldname ?>" value="t" <?php
		$val = $form->getpost_dirty('use_'.$this->fieldname);
		if (empty($val))
			$val = false;
		if (($val == 't') || ($val === true) || ($val == 1))
			echo 'checked ';
		?>/> <?php
		}
		$this->DispAdd($form);
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
	/** Correctly get the order expression for this field.
	    Override this in formatted fields that need other ordering */
	public function getOrder(&$form){
		if ($this->fieldexpr)
			return $this->fieldexpr;
		else
			return $this->fieldname;
	}

	/** Transform the value (unquoted) to a Insert/Update form.
	    If wrongfuly called, may throw exception. Returning an
	    array will skip quoting. */
	public function buildValue($val,&$form){
		return $val;
	}
		
	/** Build the query for sums 
	    \param sum_fns An array containing aggregate fns. for each field, null if
	    	this field should be omitted, true if it should be grouped
	    \param fields An array to be appended with the field expressions to sum
	    \param fields_out Fields for the outer query. Should at least contain the
			names of $fields, or array entries with ($fieldname, $format_expr)
	    \param table  The string of the table to query
	    \param table_out some additional terms to append to the table of the \b outer query
	    \param clauses Any clauses applying to the query
	    \param grps   An array of the GROUP BY clauses
	*/
	
	public function buildSumQuery(&$dbhandle, &$sum_fns,&$fields,&$fields_out, &$table,&$table_out,
		&$clauses, &$grps, &$form){
		if (!$this->does_list)
			return;
		
		// fields
		if ($this->fieldexpr)
			$fld = $this->fieldexpr;
		else
			$fld = $this->fieldname;
		
		if (isset($sum_fns[$this->fieldname]) && !is_null($sum_fns[$this->fieldname])){
			if ($sum_fns[$this->fieldname] === true){
				$grps[] = $this->fieldname;
				$fields[] = "$fld AS ". $this->fieldname;
			}
			elseif (is_string($sum_fns[$this->fieldname]))
				$fields[] = $sum_fns[$this->fieldname] ."($fld) AS ". $this->fieldname;
			elseif (is_array($sum_fns[$this->fieldname]))
				$fields[] = str_dbparams($dbhandle, '%1 AS '.$this->fieldname,$sum_fns[$this->fieldname]);

			$fields_out[] = $this->fieldname;
		}
		
		$this->listQueryTable($table,$form);
		$tmp= $this->listQueryClause($dbhandle,$form);
		if ( is_string($tmp))
			$clauses[] = $tmp;
	}

	
	public function buildInsert(&$ins_arr,&$form){
		if (!$this->does_add)
			return;
		$ins_arr[] = array($this->fieldname,
			$this->buildValue($form->getpost_dirty($this->fieldname),$form));
	}

	public function buildUpdate(&$ins_arr,&$form){
		if (!$this->does_edit)
			return;
		$ins_arr[] = array($this->fieldname,
			$this->buildValue($form->getpost_dirty($this->fieldname),$form));
	}
	
	public function buildSearchClause(&$dbhandle,$form, $search_exprs){
		$val = $this->buildValue($form->getpost_dirty($this->fieldname),$form);
		if (empty($this->fieldexpr))
			$fldex = $this->fieldname;
		else
			$fldex = $this->fieldexpr;
		if (is_array($search_exprs) && (isset($search_exprs[$this->fieldname])))
			$sex =$search_exprs[$this->fieldname];
		else
			$sex = '='; //what's on *your* mind?
			
		if ($val == null)
			switch($sex) {
				// Assume NULL -> 0 ..
			case '<>':
			case '!=':
			case '>':
				return $fldex .' IS NOT NULL';
			case '<':
				return 'false';
			case '>=':
				return 'true';
			case '=':
			case '<=':
			default:
				return $fldex .' IS NULL';
			}
		else return str_dbparams($dbhandle,"$fldex $sex %1",array($val));
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
	
	function RenderListHead_NoSort(&$form){
		if (!$this->does_list)
			return;
		echo "<td";
		if ($this->listWidth)
			echo ' width="'.$this->listWidth .'"';
		echo '>';
		
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
		if ($this->fieldedittitle)
			echo htmlspecialchars($this->fieldedittitle);
		else
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
	
	// Set if you want to create a new tab from this element
	function addTab(){
		$this->add_tab = true;
	}
	
	// Set the label of the new Tab
	function SetTabLabel($label){
		$this->tablabel= $label;
	}
};

function dontList(BaseField &$bf){
	$bf->does_list = false;
	return $bf;
}

function dontAdd(BaseField &$bf){
	$bf->does_add = false;
	return $bf;
}

function dontEdit(BaseField &$bf){
	$bf->does_edit = false;
	return $bf;
}

?>