<?php
	// Please, include ../Form.inc.php instead!
	/** Generic form
	    The form is the main handler of data->html interaction.
	*/

abstract class FormElemBase extends ElemBase{
	abstract function InFormRender(&$form);
};

class FormHandler extends ElemBase{
	public $FG_DEBUG = 0;
	protected $action = null;
	private $rights_checked = false;
		/** prefix all url vars with this, so that multiple forms can co-exist
		in the same html page! */
	public $prefix = ''; 
	
	public $a2billing; ///< Reference to an a2billing instance
	
		/** Custom elements before the form. These can be search options or anything. 
		    If they are instances of FormElemBase, they will be called with the form
		    as a parameter, which helps a lot.
		*/
	public $pre_elems = array();
	public $meta_elems = array(); ///< Same as pre_elems, but rendered after the form.
	
	// model-related vars
		/** The most important var: hold one object per field to be viewed/edited */
	public $model = array();
	public $model_name = 'Records'; ///< plural form for table
	public $model_name_s = 'Record'; ///< Singular form
	
	public $model_table = null; ///< the \b main table related to the model
	private $s_modelPK = null; ///< Cached reference to the primary key column
	
	// appearance vars
	public $list_class = 'cclist'; ///< class of the table used in list view
	public $sens; ///< sort direction, null should default to ascending
	public $order; ///< sort field, should match some model[]->fieldname
	public $cpage; ///< Current page
	public $ndisp; ///< Number of records to display
	public $follow_params = array(); ///< Parameters to be followed accross pages
	public $always_follow_params = array(); ///< Parameters to follow even when crossing action
	
	//running vars
	protected $_dirty_vars=null; ///< all variables starting with 'prefix'. Set on init.
	
	function FormHandler($tablename=null, $inames=null, $iname=null){
		$this->model_table = $tablename;
		if ($inames) $this->model_name=$inames;
		if ($iname) $this->model_name_s = $iname;
			
	}
	
	/** Before this class can be initted, its rights should be
	   proven. Any attempt to use the class w/o them will fail. */
	public function checkRights($rights){
		if (!has_rights($rights)){
			Header ("HTTP/1.0 401 Unauthorized");
			Header ("Location: PP_error.php?c=accessdenied");
			die();
		}
		$this->rights_checked = true;
	}

	function init($sA2Billing= null){
		if (!$this->rights_checked){
			error_log("Attempt to use FormHandler w/o rights!");
			die();
		}
		if ($sA2Billing)
			$this->a2billing= &$sA2Billing;
		else
			$this->a2billing= &A2Billing::instance();
			
		if (isset($GLOBALS['FG_DEBUG']))
			$this->FG_DEBUG = $GLOBALS['FG_DEBUG'];

			// Fill a local array with dirty versions of data..
		if (!$this->prefix)
			$this->_dirty_vars=array_merge($_GET, $_POST);
		else {
			$tmp_arr = array_merge($_GET, $_POST);
			$tlen=strlen($this->prefix);
			$this->_dirty_vars=array();
			// Find vars matching prefix and strip that!
			foreach($tmp_arr as $key => $data)
				if (strncmp($this->prefix,$key,$tlen)==0)
				$this->_dirty_vars[substr($key,$tlen)]=$data;
		}
			
		// set action, for a start:
		$this->action = $this->getpost_single('action');
		if ($this->action == null)
			$this->action = 'list';
		
		if ($this->order= $this->getpost_single('order'))
			$this->addFollowParam('order',$this->order);
		if ($this->sens = $this->getpost_single('sens'))
			$this->addFollowParam('sens',$this->sens);
		
		if ($this->cpage= $this->getpost_single('cpage'))
			$this->addFollowParam('cpage',$this->cpage);
		if ($this->ndisp = $this->getpost_single('ndisp'))
			$this->addFollowParam('ndisp',$this->ndisp);
		else
			$this->ndisp = 30;
	}
	
	/** Perform add, edit etc.
	    If the action fails (eg. db error), this will throw an \b exception
	    The exception message shall be human readable, that is, will be output
	    to the reader.
	    
	    \return If it returns a string, that will be the url to go after here.
	    
	*/
	public function PerformAction(){
		if (!$this->rights_checked){
			error_log("Attempt to use FormHandler w/o rights!");
			die();
		}
		switch ($this->action){
		case 'add':
			return $this->PerformAdd();
		case 'edit':
			return $this->PerformEdit();
		case 'delete':
			return $this->PerformDelete();
		case 'object-edit':
			$subfld=$this->getpost_single('sub_action');
			foreach($this->model as $fld)
				if ($fld->fieldname == $subfld){
					$act=$fld->PerformObjEdit($this);
					if ($act)
						$this->action = $act;
					break;
				}
			break;
		}
	}


	/** Render the view/edit form for the HTML body */
	public function Render(){
		if (!$this->rights_checked){
			error_log("Attempt to use FormHandler w/o rights!");
			die();
		}
		
		foreach($this->pre_elems as $el)
			if ($el instanceof FormElemBase)
				$el->InFormRender($this);
			elseif ($el instanceof ElemBase)
				$el->Render();
			else if ($this->FG_DEBUG)
				print_r($el);
				
		switch($this->action){
		case 'idle':
			break;
		case 'list':
			$this->RenderList();
			break;
		case 'ask-add':
		case 'ask-add2':
			$this->RenderAskAdd();
			break;
		case 'ask-edit2':
		case 'ask-edit':
		case 'editForm':
			$this->RenderEdit();
			break;
		case 'ask-del':
			$this->RenderAskDel();
			break;
		case 'details':
			$this->RenderDetails();
			break;
		case 'delete':
			break;
		case 'dump-form':
			if (!$this->FG_DEBUG)
				break;
			$this->dbg_DumpForm();
			break;
		default:
			if ($this->FG_DEBUG) echo "Cannot handle action: $this->action";
			if ($this->FG_DEBUG>2){
				echo "<pre>\n";
				print_r($this->_dirty_vars);
				echo "\n</pre>\n";
			}
		}
		
		foreach($this->meta_elems as $el)
			if ($el instanceof FormElemBase)
				$el->InFormRender($this);
			elseif ($el instanceof ElemBase)
				$el->Render();
			else if ($this->FG_DEBUG)
				print_r($el);
	}
	
	protected function RenderList(){
		// This function is one file!
		require("RenderList.inc.php");
	}
	
	protected function RenderEdit(){
		require("RenderEdit.inc.php");
	}

	protected function RenderAskDel(){
		?><div class='askDel'><?= str_params(_("This %1 will be deleted!"),array($this->model_name_s),1) ?>
		</div>
		<?php
		require("RenderDetails.inc.php");
	}
	protected function RenderDetails(){
		require("RenderDetails.inc.php");
	}
	protected function RenderAskAdd(){
		require("RenderAskAdd.inc.php");
	}

	// helper functions
	/** Return an array with primary key field/values, used eg. by edit urls.
	    \param $qrow an array with fields/values for the corresponding db row
	*/
	public function getPKparams(array $qrow,$use_prefix= true){
		$ret = array();
		foreach($this->model as &$fld)
			if($fld && ($fld instanceof PKeyField)){
				$arr2=$fld->listHidden($qrow,$this);
				if (isset($arr2) && is_array($arr2)){
					if ($use_prefix){
						foreach($arr2 as $key =>$val)
						$ret[$this->prefix.$key]=$val;
					}else
						$ret= array_merge($ret,$arr2);
				}
			}
		if (count($ret)==0)
			throw new Exception('Model doesn\'t have a primary key!');	
		return $ret;
	}
	
	/** Get pkparams from those of the url */
	public function getPKparamsU($use_prefix= true){
		return $this->getPKparams($this->_dirty_vars,$use_prefix);
	}

	/** Construct an url out of the follow parameters + some custom ones
	   @param $arr_more  An array to be added in the form ( key => data ...)
	   @return A string like "?key1=data&key2=data..."
	*/
	function gen_GetParams($arr_more = NULL,$do_amper=false){
		$arr = array_merge($this->always_follow_params,$this->follow_params);
		if (is_array($arr_more))
		$arr = array_merge($arr, $arr_more);
		$str = arr2url($arr);
		
		if (strlen($str)){
			if ($do_amper)
			$str = '&' . $str;
			else
			$str = '?' . $str;
		}
		return $str;
	}
	
	function gen_AllGetParams($arr_more = NULL,$do_amper=false){
		$arr = $this->always_follow_params;
		if (is_array($arr_more))
		$arr = array_merge($arr, $arr_more);
		$str = arr2url($arr);
		
		if (strlen($str)){
			if ($do_amper)
			$str = '&' . $str;
			else
			$str = '?' . $str;
		}
		return $str;
	}
	
	function gen_PostParams($arr_more = NULL, $do_nulls=false){
		$arr = array_merge($this->always_follow_params,$this->follow_params);
		if (is_array($arr_more))
		$arr = array_merge($arr, $arr_more);
		// unfortunately, it is hard to use CV_FOLLOWPARAMETERS here!
		
		foreach($arr as $key => $value)
			if ($do_nulls || $value !=NULL){
		?><input type="hidden" name="<?= $key ?>" value="<?= htmlspecialchars($value) ?>" >
		<?php
		}
	}
	
	/** Return an URL to this page, with some extra params */
	function selfUrl(array $arr){
		return $_SERVER['PHP_SELF']. $this->gen_GetParams($arr);
	}
	
	/** Return a URL to the ask-edit page
	    \param $arr The row of the query
	*/
	function askeditURL(array $arr){
		$pkparams = $this->getPKparams($arr,true);
		$pkparams['action']='ask-edit';
		return $_SERVER['PHP_SELF'].$this->gen_AllGetParams($pkparams);
	}
	
	/// Throw away anything that could make data weird.. Sometimes too much.
	function sanitize_data($data){
		if(is_array($data)){
			return $data; //Need to sanatize this later
		}
		$lowerdata = strtolower ($data);
		$data = str_replace('--', '', $data);
		$data = str_replace("'", '', $data);
		$data = str_replace('=', '', $data);
		$data = str_replace(';', '', $data);
		//$lowerdata = str_replace('table', '', $lowerdata);
		//$lowerdata = str_replace(' or ', '', $data);
		if (!(strpos($lowerdata, ' or 1')===FALSE)){ return false;}
		if (!(strpos($lowerdata, ' or true')===FALSE)){ return false;}
		if (!(strpos($lowerdata, 'table')===FALSE)){ return false;}
		return $data;
	}
	
	function getpost_single($vname){
		return sanitize_data($this->_dirty_vars[$vname]);
	}
	
	function getpost_dirty($vname){
		return $this->_dirty_vars[$vname];
	}
	
	function getAction(){
		return $this->action;
	}
	
	function addFollowParam($key,$var){
		$this->follow_params[$this->prefix . $key] = $var;
	}

	function addAllFollowParam($key,$var,$append_prefix=true){
		if ($append_prefix)
			$key = $this->prefix . $key;
		$this->always_follow_params[$key] = $var;
	}

	// ---- Debuging functions..
	
	function dbg_DumpForm(){
		echo "<div><pre>\n";
		print_r($this);
		echo "\n</pre></div>\n";
	}
	
	protected function PerformAdd(){
		$dbg_elem = new DbgElem();
		$dbhandle = $this->a2billing->DBHandle();
		
		if ($this->FG_DEBUG>0)
			array_unshift($this->pre_elems,$dbg_elem);
			
		// just build the value list..
		$ins_data=array();
		
		try {
			foreach($this->model as $fld)
				$fld->buildInsert($ins_data,$this);
		} catch (Exception $ex){
			$this->action = 'ask-add2';
			$this->pre_elems[] = new ErrorElem($ex->getMessage());
			$dbg_elem->content.=  $ex->message.' ('. $ex->getCode() .")\n";
// 			throw new Exception( $err_str);
		}
		$ins_keys = array();
		$ins_values = array();
		$ins_qm = array();
		
		foreach ($ins_data as $ins){
			$ins_keys[] =$ins[0];
			$ins_qm[] = '?';
			$ins_values[] = $ins[1];
		}
		
		$dbg_elem->content.= "Query: INSERT INTO ". $this->model_table ."(";
		$dbg_elem->content.= implode(', ',$ins_keys);
		$dbg_elem->content.= ") VALUES(". var_export($ins_values,true).");\n";
		
		$query = "INSERT INTO ". $this->model_table ."(" .
			implode(', ',$ins_keys) . ") VALUES(". 
			implode(',', $ins_qm).");";
		
		/* Note: up till now, no data has been quoted/sanitized. Thus, we
		   feed it direcltly to the second part of the query. Pgsql, in particular,
		   can handle a binary transfer of that data to the db, in a well protected
		   manner */
		$res = $dbhandle->Execute($query,$ins_values);
		
		if (!$res){
			$this->action = 'ask-add2';
			$this->pre_elems[] = new ErrorElem(str_params(_("Cannot create new %1, database error."),array($this->model_name_s),1));
			$dbg_elem->content.= $dbhandle->ErrorMsg() ."\n";
// 			throw new Exception( $err_str);
		}else{
			$dbg_elem->content.= ".. success: ". gettype($res) . "\n";
			$this->pre_elems[] = new StringElem(_("New data has successfully been inserted into the database."));
			$this->action = 'list';
			
		}
	}


	/** Format and execute the Update query */
	protected function PerformEdit(){
		$dbg_elem = new DbgElem();
		$dbhandle = $this->a2billing->DBHandle();
		
		if ($this->FG_DEBUG>0)
			array_unshift($this->pre_elems,$dbg_elem);
			
		// just build the value list..
		$upd_data=array();
		$upd_clauses = array();
		try {
			foreach($this->model as $fld){
				$fld->buildUpdate($upd_data,$this);
				$qc = $fld->editQueryClause($dbhandle,$this);
				if ($qc){
					if (is_string($qc))
						$upd_clauses[] = $qc;
					elseif(is_array($qc))
						$upd_clauses = array_merge($upd_clauses,$qc);
					else
						throw new Exception("Why clause " . gettype($qc)." ?");
				}
			}
		} catch (Exception $ex){
			$this->action = 'ask-edit2';
			$this->pre_elems[] = new ErrorElem($ex->getMessage());
			$dbg_elem->content.=  $ex->getMessage().' ('. $ex->getCode() .")\n";
// 			throw new Exception( $err_str);
		}
		
		$upd_values = array();
		
		$query = "UPDATE " . $this->model_table . " SET ";
		
		$query_u = array();
		foreach($upd_data AS $upd)
			if (is_array($upd)){
				$query_u[] = $upd[0] ." = ? ";
				$upd_values[] = $upd[1];
			}elseif(is_string($upd))
				$query_u[] = $upd;
		$query .= implode(", ", $query_u);
		$query_dbg = $query; // format a string that contains the values, too
		$query_dbg .= "( ". var_export($upd_values,true) .") ";
		
			// Protect against a nasty update!
		if (count($upd_clauses)<1){
			$this->pre_elems[] = new ErrorElem("Cannot update, internal error");
			$dbg_elem->content.= "Update: no query clauses!\n";
		}
		
		$query .= ' WHERE ' . implode (' AND ', $upd_clauses) . ';';
		$query_dbg .= ' WHERE ' . implode (' AND ', $upd_clauses) . ';';
		
		$dbg_elem->content .=$query_dbg . "\n";
		
		/* Note: up till now, no data has been quoted/sanitized. Thus, we
		   feed it direcltly to the second part of the query. Pgsql, in particular,
		   can handle a binary transfer of that data to the db, in a well protected
		   manner */
		
		$res = $dbhandle->Execute($query,$upd_values);
		
		if (!$res){
			$this->action = 'ask-edit2';
			$this->pre_elems[] = new ErrorElem(str_params(_("Cannot update %1, database error."),array($this->model_name_s),1));
			$dbg_elem->content.= $dbhandle->ErrorMsg() ."\n";
// 			throw new Exception( $err_str);
		}elseif ($dbhandle->Affected_Rows()<1){
			// No result rows: update clause didn't match
			$dbg_elem->content.= ".. EOF, no rows!";
			$dbg_elem->obj = $dbhandle->Affected_Rows();
			$this->pre_elems[] = new ErrorElem(str_params(_("Cannot update %1, record not found."),array($this->model_name_s),1));
			$this->action = 'ask-edit2';
		} else {
			$dbg_elem->content.= "Success: UPDATE ". $dbhandle->Affected_Rows() . "\n";
			$this->pre_elems[] = new StringElem(_("Data has successfully been updated in the database."));
			$this->action = 'list';
			
		}
	}
	
		/** Format and execute the Delete query */
	protected function PerformDelete(){
		$dbg_elem = new DbgElem();
		$dbhandle = $this->a2billing->DBHandle();
		
		if ($this->FG_DEBUG>0)
			array_unshift($this->pre_elems,$dbg_elem);
			
		$del_clauses = array();
		try {
			foreach($this->model as $fld){
				$qc = $fld->delQueryClause($dbhandle,$this);
				if ($qc){
					if (is_string($qc))
						$del_clauses[] = $qc;
					elseif(is_array($qc))
						$del_clauses = array_merge($del_clauses,$qc);
					else
						throw new Exception("Why clause " . gettype($qc)." ?");
				}
			}
		} catch (Exception $ex){
			$this->action = 'ask-del';
			$this->pre_elems[] = new ErrorElem($ex->getMessage());
			$dbg_elem->content.=  $ex->getMessage().' ('. $ex->getCode() .")\n";
// 			throw new Exception( $err_str);
		}
		
		
		$query = "DELETE FROM " . $this->model_table ;
		
			// Protect against a nasty update!
		if (count($del_clauses)<1){
			$this->pre_elems[] = new ErrorElem("Cannot delete, internal error");
			$dbg_elem->content.= "Delete: no query clauses!\n";
		}
		
		$query .= ' WHERE ' . implode (' AND ', $del_clauses) . ';';
		
		$dbg_elem->content .=$query . "\n";
		
		/* Note: up till now, no data has been quoted/sanitized. Thus, we
		   feed it direcltly to the second part of the query. Pgsql, in particular,
		   can handle a binary transfer of that data to the db, in a well protected
		   manner */
		if ($this->FG_DEBUG>4){
			$this->action = 'ask-del';
			$dbg_elem->content .= "Debug mode, won't delete!\n";
			return;
		}
		$res = $dbhandle->Execute($query);
		
		if (!$res){
			$this->action = 'ask-del';
			$this->pre_elems[] = new ErrorElem(str_params(_("Cannot delete %1, database error."),array($this->model_name_s),1));
			$dbg_elem->content.= $dbhandle->ErrorMsg() ."\n";
// 			throw new Exception( $err_str);
		}elseif ($dbhandle->Affected_Rows()<1){
			// No result rows: update clause didn't match
			$dbg_elem->content.= ".. EOF, no rows!";
			$dbg_elem->obj = $dbhandle->Affected_Rows();
			$this->pre_elems[] = new ErrorElem(str_params(_("Cannot delete %1, record not found."),array($this->model_name_s),1));
			$this->action = 'list';
		} else {
			$dbg_elem->content.= "Success: DELETE ". $dbhandle->Affected_Rows() . "\n";
			$this->pre_elems[] = new StringElem(_("Record successfully removed from the database."));
			$this->action = 'list';
			
		}
	}

};

?>