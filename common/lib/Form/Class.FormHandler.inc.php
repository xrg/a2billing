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
	
	public $views = array(); ///< Each view implements actions/views.
	
	// appearance vars
	public $list_class = 'cclist'; ///< class of the table used in list view
	public $sens; ///< sort direction, null should default to ascending
	public $order; ///< sort field, should match some model[]->fieldname
	public $cpage; ///< Current page
	public $ndisp; ///< Number of records to display
	public $default_order;
	public $default_sens;
	
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

	function init($sA2Billing= null, $stdActions=true){
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
		else $this->order = $this->default_order;
		if ($this->sens = $this->getpost_single('sens'))
			$this->addFollowParam('sens',$this->sens);
		else $this->sens = $this->default_sens;
		
		if ($this->cpage= $this->getpost_single('cpage'))
			$this->addFollowParam('cpage',$this->cpage);
		if ($this->ndisp = $this->getpost_single('ndisp'))
			$this->addFollowParam('ndisp',$this->ndisp);
		else
			$this->ndisp = 30;
			
		if ($stdActions){
			$this->views['idle'] = new IdleView();
			$this->views['list'] = new ListView();
			if (!session_readonly()){
				$this->views['edit'] = new EditView();
				$this->views['add'] = new AddView();
				$this->views['delete'] = new DeleteView();
				$this->views['object-edit'] = new ObjEditView();
			}
			$this->views['ask-add'] = new AskAddView();
			$this->views['ask-add2'] = new AskAdd2View();
			$this->views['ask-edit2'] = new AskEdit2View();
			$this->views['ask-edit'] = new AskEditView();
			$this->views['ask-del'] = new AskDelView();
			$this->views['details'] = new DetailsView();
			if ($this->FG_DEBUG)
				$this->views['dump-form'] = new DbgDumpView();
		}
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
		
		if (isset($this->views[$this->action]))
			$this->views[$this->action]->PerformAction($this);
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
				
		if (isset($this->views[$this->action]))
			$this->views[$this->action]->Render($this);
		else{
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
	
	/** Render the view/edit form for the HTML body */
	public function RenderSpecial($rmode,&$robj){
		if (!$this->rights_checked){
			error_log("Attempt to use FormHandler w/o rights!");
			die();
		}
		
/*		foreach($this->pre_elems as $el)
			if ($el instanceof FormElemBase)
				$el->InFormRender($this);
			elseif ($el instanceof ElemBase)
				$el->Render();
			else if ($this->FG_DEBUG)
				print_r($el);*/
				
		if (isset($this->views[$this->action]))
			$this->views[$this->action]->RenderSpecial($rmode,$this,$robj);
		else{
			if ($this->FG_DEBUG) echo "Cannot handle action: $this->action";
			if ($this->FG_DEBUG>2){
				print_r($this->_dirty_vars);
			}
		}
		
/*		foreach($this->meta_elems as $el)
			if ($el instanceof FormElemBase)
				$el->InFormRender($this);
			elseif ($el instanceof ElemBase)
				$el->Render();
			else if ($this->FG_DEBUG)
				print_r($el);*/
	}
	public function RenderHeadSpecial($rmode,&$robj){
		if (!$this->rights_checked){
			error_log("Attempt to use FormHandler w/o rights!");
			die();
		}
		if (isset($this->views[$this->action]))
			$this->views[$this->action]->RenderHeadSpecial($rmode,$this,$robj);		
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
		$pkparams[$this->prefix.'action']='ask-edit';
		return $_SERVER['PHP_SELF'].$this->gen_AllGetParams($pkparams);
	}
	
	/** Generate a page element calling the graph for one of the sums
	  \param $ind a key referencing sums[$ind]
	  \param $type The type of the graph
	  */
	function GraphUrl($grph, $alt = null){
		$img_url=$this->selfUrl(array('graph' =>'t','action' => $grph));
		if ($title)
			$tmp_title = $alt;
		else
			$tmp_title = _("Graph");
		return new StringElem("<div class=\"graph_img\"><img src=\"$img_url\" alt=\"$tmp_title\"></div>");
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
	
	function setAction($act){
		$this->action = $act;
		if ($this->FG_DEBUG && !isset($this->views[$this->action]))
			error_log("Action ".$this->action ." not defined!");
	}

	function addFollowParam($key,$var){
		$this->follow_params[$this->prefix . $key] = $var;
	}

	function addAllFollowParam($key,$var,$append_prefix=true){
		if ($append_prefix)
			$key = $this->prefix . $key;
		$this->always_follow_params[$key] = $var;
	}

	

};

?>