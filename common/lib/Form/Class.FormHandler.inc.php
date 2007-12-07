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
		if (!$this_prefix)
			$this->_dirty_vars=array_merge($_GET, $_POST);
		else {
			$tmp_arr = array_merge($_GET, $_POST);
			$tlen=strlen($this->prefix);
			$this->_dirty_vars=array();
			// Find vars matching prefix and strip that!
			foreach($tmp_arr as $key => $data)
				if (strncmp($this->prefix,$key,$tlen))
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
		case 'ask-edit':
		case 'editForm':
			$this->RenderEdit();
			break;
		case 'delForm':
			$this->RenderDel();
			break;
		case 'dump-form':
			if (!$this->FG_DEBUG)
				break;
			$this->dbg_DumpForm();
			break;
		default:
			if ($this->FG_DEBUG) echo "Cannot handle action: $this->action";
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

	protected function RenderDel(){
		require("RenderAskDel.inc.php");
	}
	protected function RenderAskAdd(){
		require("RenderAskAdd.inc.php");
	}

	// helper functions
	/** Return a reference to the first primary key column of the model.
	    Throw an exception if no primary key! */
	public function getModelPK(){
		if ($this->s_modelPK)
			return $this->s_modelPK;
		foreach($this->model as &$fld)
			if($fld && ($fld instanceof PKeyField))
			return $this->s_modelPK = &$fld;
		
		throw new Exception('Model doesn\'t have a primary key!');
	}
	/** Construct an url out of the follow parameters + some custom ones
	   @param $arr_more  An array to be added in the form ( key => data ...)
	   @return A string like "?key1=data&key2=data..."
	*/
	function gen_GetParams($arr_more = NULL,$do_amper=false){
		$arr = $this->follow_params;
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
		$arr = $this->follow_params;
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
		$mod_pk= $this->getModelPK();
		return $_SERVER['PHP_SELF'].'?'.
			$this->prefix.'action=ask-edit&'.
			$this->prefix.$mod_pk->fieldname.'='.rawurlencode($arr[$mod_pk->fieldname]);
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
		foreach($this->model as $fld)
			$fld->buildInsert($ins_data,$this);
		
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
		$dbg_elem->content.= ") VALUES(". implode(',', $ins_values).");\n";
		
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
			$this->action = 'idle';
			
		}
	}
};

?>