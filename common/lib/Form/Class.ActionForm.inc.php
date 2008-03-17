<?php

/** Generic Form leading to one SQL action
*/

class ActionForm extends ElemBase {
	public $FG_DEBUG = 0;
	private $rights_checked = false;
		/** prefix all url vars with this, so that multiple forms can co-exist
		in the same html page! */
	public $prefix = '';
	protected $action;
	
	public $a2billing; ///< Reference to an a2billing instance
		
	// model-related vars
		/** The most important var: hold one object per field to be viewed/edited */
	public $model = array();
	protected $_dirty_vars=null; ///< all variables starting with 'prefix'. Set on init.
	public $successString;
	public $failureString;
	public $pre_elems = array();

	function ActionForm(){
		$this->successString= _("Action finished successfully!");
		$this->failureString= _("Action failed: %1");
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

	public function verifyRights(){
		if (!$this->rights_checked){
			error_log("Attempt to use ActionForm w/o rights!");
			die();
		}
	}
	
	function init($sA2Billing= null){
		if (!$this->rights_checked){
			error_log("Attempt to use ActionForm w/o rights!");
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
			$this->action = 'ask';
	}

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

	public function PerformAction(){
		global $PAGE_ELEMS;
		if (!$this->rights_checked){
			error_log("Attempt to use ActionForm w/o rights!");
			die();
		}
		if ($this->action != 'true')
			return;
		
		$dbg_elem = new DbgElem();
		
		$dbg_elem->content .=  "Stub!\n";
		
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
		case 'true':
			break;
		case 'ask':
			$this->RenderAsk();
			break;
		case 'display':
		case 'list':
			$this->RenderContent();
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
	
	protected function RenderAsk(){
?>
	<form action=<?= $_SERVER['PHP_SELF']?> method=post name="<?= $this->prefix?>Frm" id="<?= $this->prefix ?>Frm">
	<?php	$hidden_arr = array( 'action' => 'true', 'sub_action' => '');
		if (strlen($this->prefix)>0){
			$arr2= array();
			foreach($hidden_arr as $key => $val)
				$arr2[$this->prefix.$key] = $val;
			$hidden_arr = $arr2;
		}

	$this->gen_PostParams($hidden_arr,true); 
	?>
	<table class="actionForm" cellspacing="2">
	<thead><tr><td class="field">&nbsp;</td><td class="value">&nbsp;</td></tr>
	</thead>
	<tbody>
	<?php
		foreach($this->model as $fld)
			if ($fld && $fld->does_add){
		?><tr><td class="field"><?php
				$fld->RenderAddTitle($this);
		?></td><td class="value"><?php
				$fld->DispAdd($this);
		?></td></tr>
		<?php
			}
	?>
	<tr class="confirm"><td colspan=2 align="right">
	<button type=submit>
	<?= $this->submitString ?>
	<img src="./Images/icon_arrow_orange.png" ></input>
	<td>
	</tr>
	</tbody>
	</table> </form>
	<?php
	}

	function RenderContent(){
		echo "Stub!\n";
	}

};

?>