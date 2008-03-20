<?php
//require_once("Class.ElemBase.inc.php");
require_once("Class.ClauseField.inc.php");

/** Selection form
*/

class SelectionForm extends ElemBase {
	public $prefix = 'sel_';
	public $FG_DEBUG = 0;
	public $model = array();
	public $a2billing;
	protected $_dirty_vars=null; ///< all variables starting with 'prefix'. Set on init.
	public $search_exprs = array(); ///< an array with comparison operators for the fields
		/** If set, it will produce a clause \b only when _GET/_POST arrays are empty
		    It can be either a string used directly SQL expression or an array, 
		    with a list of the \e model fields which will be queried for default vals */
	public $fallbackClause;
	protected $enabled = true;

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
	
	public function enable($en = true){
		$this->enabled = $en;
	}
	
	/** Append the clauses to a form.
	   \param $fparams If true, also set the form to follow the parameters of the 
	   select form
	 */
	public function appendClauses(&$form,$fparams = true){
		$clauses= $this->buildClauses();
		foreach($clauses as $cla)
			$form->model[] = new FreeClauseField($cla);
		
		if ($fparams){
			foreach ($this->model as $fld){
				if ((!$fld->does_add) && 
					(!isset($this->_dirty_vars['use_'.$fld->fieldname]) ||
						$this->_dirty_vars['use_'.$fld->fieldname] != 't'))
				continue;
				// We assume here that the only selection vars for fld are
				// 'name', 'use_name' ..
				if (isset($this->_dirty_vars['use_'.$fld->fieldname]))
					$form->addAllFollowParam($this->prefix.'use_'.$fld->fieldname,$this->_dirty_vars['use_'.$fld->fieldname],false);
				if (!empty($this->_dirty_vars[$fld->fieldname]))
					$form->addAllFollowParam($this->prefix.$fld->fieldname,$this->_dirty_vars[$fld->fieldname],false);
			}
		}
	}
	
	/** Returns an array, indexed by the fieldname, with search clauses */
	public function buildClauses($search_exprs = null){
		$dbhan = $this->a2billing->DBHandle_p();
		
		if ($search_exprs != null)
			$sexes = $search_exprs;
		else $sexes = $this->search_exprs;
		$retc = array();
		$dvars= $this->_dirty_vars;
		$dvars2=null;
		
		if (empty($dvars)){
			if (is_string($this->fallbackClause)){
				$retc[]=$this->fallbackClause;
				return $retc;
			}elseif (is_array($this->fallbackClause)){
				$dvars2=array();
				foreach($this->fallbackClause as $fbc)
				  foreach($this->model as $fld)
				     if($fbc==$fld->fieldname){
				     	if(!$fld->does_add)
				     		$dvars2['use_'.$fbc]='t';
				     	$dvars2[$fbc]=$fld->getDefault();
				     }
				     //Store back the generated array into dirty
				     // vars so that calls to getpost_dirty() will
				     // return its values.
				$this->_dirty_vars=$dvars2;
			}
		}
		foreach ($this->model as $fld){
			if ((!$fld->does_add) && 
				(!isset($this->_dirty_vars['use_'.$fld->fieldname]) ||
					$this->_dirty_vars['use_'.$fld->fieldname] != 't'))
			continue;
			$cls = $fld->buildSearchClause($dbhan,$this,$sexes);
			if (!empty($cls))
				$retc[$fld->fieldname] = $cls;
		}
		
			// Restore the original (empty? ) _dirty_vars.
		if (!empty($dvars2))
			$this->_dirty_vars=$dvars;
		return $retc;
	}

	public function Render(){
		if (!$this->enabled)
			return;
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
		foreach($this->model as $fld){
		?><tr><td class="field"><?php
				$fld->RenderEditTitle($form);
		?></td><td class="value"><?php
				$fld->DispSearch($this);
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