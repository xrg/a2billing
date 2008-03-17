<?php
require_once("Class.ActionForm.inc.php");

/** This form performs an SQL action and displays its result in a simplistic way
*/

class SqlActionForm extends ActionForm {
	protected $qryres;
	public $contentString;
	public $rowString;

	public function PerformAction(){
		global $PAGE_ELEMS;
		$this->verifyRights();
		
		if ($this->action != 'true')
			return;
		
		$dbg_elem = new DbgElem();
		$dbhandle = $this->a2billing->DBHandle();
				
		if ($this->FG_DEBUG>0)
			array_unshift($this->pre_elems,$dbg_elem);
			
			
		$query = str_aldbparams($dbhandle,$this->QueryString,$this->_dirty_vars);
		
		if (strlen($query)<1){
			$this->pre_elems[] = new ErrorElem("Cannot update, internal error");
			$dbg_elem->content.= "Action: no query!\n";
		}
		
		$dbg_elem->content .= $query . "\n";
		
		$res = $dbhandle->Execute($query);
		
		if (!$res){
			$this->action = 'ask';
			$this->pre_elems[] = new ErrorElem(str_params($this->failureString,array(_("database error")),1));
			$dbg_elem->content.= $dbhandle->ErrorMsg() ."\n";
// 			throw new Exception( $err_str);
		}elseif ($this->expectRows && $res->EOF && ($dbhandle->Affected_Rows()<1)){
			// No result rows: update clause didn't match
			$dbg_elem->content.= ".. EOF, no rows!\n";
			$dbg_elem->content.= $dbhandle->ErrorMsg() ."\n";
			$dbg_elem->content.= $dbhandle->NoticeMsg() ."\n";
			$dbg_elem->obj = $dbhandle->Affected_Rows();
			$this->pre_elems[] = new ErrorElem(str_params($this->failureString,array(_("no rows")),1));
			$this->action = 'ask';
		} else {
			$dbg_elem->content.= "Success: Rows: ". $dbhandle->Affected_Rows() . "\n";
			$dbg_elem->content.= $dbhandle->ErrorMsg() ."\n";
			$dbg_elem->content.= $dbhandle->NoticeMsg() ."\n";
			if (strlen($this->successString))
				$this->pre_elems[] = new StringElem(str_params($this->successString,
					array($dbhandle->Affected_Rows()),1));
			$this->action = 'display';
			$this->qryres = &$res;
		}
	}

	function RenderContent(){
		echo '<div class="content">'."\n";
		if (isset($this->contentString))
			echo $this->contentString;
		if (!empty($this->successScript)){ ?>
	<script language="JavaScript" type="text/JavaScript">
	<?= $this->successScript ?>
	</script>
	<?php
		}
		
		if (isset($this->rowString))
		while($row=$this->qryres->fetchRow())
			echo str_alparams($this->rowString,$row);
		
		if (isset($this->afterContentString))
			echo $this->afterContentString;
		
		echo '</div>'."\n";
	}
};

class SqlTableActionForm extends SqlActionForm {
	public $noRowsString;
	public $rmodel = array();
	public $listclass = 'actlist';
	
	function RenderContent(){
		echo '<div class="content">'."\n";
		if (isset($this->contentString))
			echo $this->contentString;
		if ($this->qryres->EOF){
			if (isset($this->noRowsString))
				echo $this->noRowsString;
		}else {
			$this->action = 'list';
		?>
	<TABLE cellPadding="2" cellSpacing="2" align='center' class="<?= $this->listclass ?>">
		<thead><tr>
		<?php
		foreach ($this->rmodel as $fld)
			$fld->RenderListHead_NoSort($this);
		?>
		</tr></thead>
		<tbody>
		<?php
		$row_num = 0;
		while ($row = $this->qryres->fetchRow()){
			if ($this->FG_DEBUG > 4) {
				echo '<tr><td colspan = 3>';
				print_r($row);
				echo '</td></tr>';
			}
			if ($row_num % 2)
				echo '<tr class="odd">';
			else	echo '<tr>';
			
			foreach ($this->rmodel as $fld)
				$fld->RenderListCell($row,$this);
			echo "</tr>\n";
			$row_num++;
		}
		?>
		</tbody>
	</table>
	<?php
		}
		
		if (isset($this->afterContentString))
			echo $this->afterContentString;
		
		echo '</div>'."\n";
	}

};

/** Vertical enumeration, in "details" style */
class SqlDetailsActionForm extends SqlTableActionForm {
	function RenderContent(){
		echo '<div class="content">'."\n";
		if (isset($this->contentString))
			echo $this->contentString;
		if ($this->qryres->EOF){
			if (isset($this->noRowsString))
				echo $this->noRowsString;
		}else { ?>
	<table cellPadding="2" cellSpacing="2" align='center' class="<?= $this->listclass ?>">
	<?php if (!empty($this->headerString)) {
	?><thead><tr><td colspan=2><?php
		echo $this->headerString;
	?></td></tr></thead>
	<?php }
	?> <tbody>
		<?php
		$row_num = 0;
		while ($row = $this->qryres->fetchRow()){
			foreach($this->rmodel as $fld){
			if ($row_num %2 ==1) 
				$cls= 'class="odd"';
			else	$cls = '';
			?><tr <?= $cls ?>><td class="field"><?php
				$fld->RenderEditTitle($this);
			?></td><td class="value"><?php
				$fld->DispList($row,$this);
			?></td></tr>
			<?php
			$row_num++;
			}
		}
		?>
		</tbody>
	</table>
	<?php
		}
		
		if (isset($this->afterContentString))
			echo $this->afterContentString;
		
		echo '</div>'."\n";
	}
};

// 		<thead><tr>
// 		<?php
// 
// 		foreach ($this->rmodel as $fld)
// 			$fld->RenderListHead_NoSort($this);
// 		? >
// 		</tr></thead>

?>
