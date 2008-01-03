<?php
//require_once("Class.ElemBase.inc.php");

/** Selection form
*/

class SelectionForm extends ElemBase {
	public $prefix = 'sel_';
	public $FG_DEBUG = 0;
	public $model = array();
	public $a2billing;
	protected $_dirty_vars=null; ///< all variables starting with 'prefix'. Set on init.

	function init($sA2Billing= null, $stdActions=true){
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
	}
	function getpost_single($vname){
		return sanitize_data($this->_dirty_vars[$vname]);
	}
	
	function getpost_dirty($vname){
		return $this->_dirty_vars[$vname];
	}

	public function PerformAction(){
	}

	public function Render(){
	?>
	<form action=<?= $_SERVER['PHP_SELF']?> method=get name="<?= $this->prefix?>Sel" id="<?= $form->prefix ?>Sel">
	<?php
		$hidden_arr = array();
		foreach($this->model as $fld)
			if ($arr2 = $fld->editHidden($this->_dirty_vars,$this))
				$hidden_arr = array_merge($hidden_arr,$arr2);
		if (strlen($this->prefix)>0){
			$arr2= array();
			foreach($hidden_arr as $key => $val)
				$arr2[$this->prefix.$key] = $val;
			$hidden_arr = $arr2;
		}
		// *-* $form->gen_PostParams($hidden_arr,true);
	?>
<table class="selectForm" cellspacing="2">
	<thead><tr><td class="field">&nbsp;</td><td class="value">&nbsp;</td></tr>
	</thead>
	<tbody>
	<?php
		foreach($this->model as $fld)
			if ($fld){
		?><tr><td class="field"><?php
				$fld->RenderEditTitle($form);
		?></td><td class="value"><?php
				$fld->DispEdit($this->_dirty_vars,$this);
		?></td></tr>
		<?php
			}
	?>
	<tr class="confirm"><td colspan=2 align="right">
	<button type=submit>
	<?= _("GO!") ?>
	<img src="./Images/icon_arrow_orange.png" ></input>
	<td>
	</tr>
	</tbody>
	</table> </form>
	<?php
	}
	
};

?>
