<?php
require_once("Class.ElemBase.inc.php");
/** Generic Element leading to one action. The element is a freeform visual item
*/

class ActionElem extends ElemBase {
	public $FG_DEBUG = 0;
		/** prefix all url vars with this, so that multiple forms can co-exist
		in the same html page! */
	public $prefix = ''; 
	
	public $a2billing; ///< Reference to an a2billing instance
		
	protected $_dirty_vars=null; ///< all variables starting with 'prefix'. Set on init.
	
	public $follow_params = array();
	
	public $ButtonStr = 'Go!';
	
	// Action names: they are configurable!
	public $action_ask = 'ask';
	public $action_do = 'do';
	public $action_success = 'true';
	public $action_fail = 'false';
	
	// Action content
	public $elem_ask;
	public $elem_success;
	public $elem_fail;
	
	protected $pre_elem ; // used for the debug element

	function ActionElem(){
	}
	
	function init($sA2Billing= null){
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
			$this->action = $this->action_ask;
	}

	function sanitize_data($data){
		return sanitize_data($data);
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
	
	function dbg_DumpForm(){
		echo "<div><pre>\n";
		print_r($this);
		echo "\n</pre></div>\n";
	}
	
	function selfUrl(array $arr){
		return $_SERVER['PHP_SELF'];
	}

	function gen_PostParams($arr = NULL, $do_nulls=false){
		if (!is_array($arr))
			return;
		
		foreach($arr as $key => $value)
			if ($do_nulls || $value !=NULL){
		?><input type="hidden" name="<?= $key ?>" value="<?= htmlspecialchars($value) ?>" >
		<?php
		}
	}
	
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

	public function PerformAction(){
		if ($this->action != $this->action_do)
			return;

		$dbg_elem = new DbgElem();
		if ($this->FG_DEBUG)
			$this->pre_elem = &$dbg_elem;

		$dbg_elem->content .=  "Stub!\n";
		
	}

	protected function do_render(&$elem){
		if (empty($elem))
			return;
		elseif ($elem instanceof FormElemBase)
			$elem->InFormRender($this);
		elseif ($elem instanceof ElemBase)
			$elem->Render();
		else if ($this->FG_DEBUG)
			print_r($elem);
	}

	/** Render the view/edit form for the HTML body */
	public function Render(){
		
		$this->do_render($this->pre_elem);
			
		switch($this->action){
		case $this->action_ask :
			$this->do_render($this->elem_ask);
			$this->RenderButton();
			break;
		
		case $this->action_do:
			if ($this->FG_DEBUG)
				echo "Given 'do' action, weird!<br>";
			break;
			
		case $this->action_success:
			$this->do_render($this->elem_success);
			break;
		case $this->action_fail:
			$this->do_render($this->elem_fail);
			break;
		
		default:
			if ($this->FG_DEBUG) echo "Cannot handle action: $this->action";
			if ($this->FG_DEBUG>2){
				echo "<pre>\n";
				print_r($this->_dirty_vars);
				echo "\n</pre>\n";
			}
		}
	}
	
	protected function RenderButton(){
	?><a href="<?= $_SERVER['PHP_SELF'] . $this->gen_GetParams(
		array($this->prefix.'action' => $this->action_do)) ?>
		"><?= $this->ButtonStr ?></a>
	<?php
	}

};

?>