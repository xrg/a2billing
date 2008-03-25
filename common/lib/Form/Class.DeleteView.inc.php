<?php
require_once("Class.DetailsView.inc.php");

class AskDelView extends DetailsView {
	protected $nb_fragment = 0;
	
	public function Render(&$form){
		$this->RenderStyle();
		$res= $this->PerformQuery($form);
		
		if (!$res)
			return;
		
		// do the table..
		$row=$res->fetchRow();
		?>
		<div class='askDel'><?= str_params(_("This %1 will be deleted!"),array($form->model_name_s),1) ?>
		</div>
		<form action=<?= $_SERVER['PHP_SELF']?> method=post name="<?= $form->prefix?>Frm" id="<?= $form->prefix ?>Frm">
		<?php
		$hidden_arr = array('action' => 'delete','sub_action' => '');
		foreach($form->model as $fld)
			if ($arr2 = $fld->editHidden($row,$form))
				$hidden_arr = array_merge($hidden_arr,$arr2);
		if (strlen($form->prefix)>0){
			$arr2= array();
			foreach($hidden_arr as $key => $val)
				$arr2[$form->prefix.$key] = $val;
			$hidden_arr = $arr2;
		}
		$form->gen_PostParams($hidden_arr,true);
		
		foreach($form->model as $fld)
			if ($fld instanceof TabField){
				$this->nb_fragment++;
				if ($this->nb_fragment==1) echo "\n<div id=\"rotate\"> <ul>\n";
				echo '<li><a href="#fragment-'.$this->nb_fragment.'"><span>'.$fld->caption."</span></a></li>\n";
			}
		if ($this->nb_fragment > 0) echo "</ul>\n";
		
		$this->nb_fragment = 0;
		$loopmodel = 0;
		foreach($form->model as $fld){
			if ($fld instanceof TabField){
				$this->nb_fragment++;
				$fld->DispTab($row, $form, $this->nb_fragment);
			}
			if ($loopmodel == 0){
				?>
				<table class="detailForm" cellspacing="2">
				<thead><tr><td class="field">&nbsp;</td><td class="value">&nbsp;</td></tr></thead>
				<tbody>
				<?php
			}
			$loopmodel++;
		
			if ($fld && !($fld instanceof OptionField)){
				?><tr><td class="field"><?php
						$fld->RenderEditTitle($form);
				?></td><td class="value"><?php
						$fld->DispList($row,$form);
				?></td></tr>
				<?php
			}
		}
	?>
	<tr class="confirm"><td colspan=2 align="right">
	<button type=submit>
	<?= str_params(_("Delete this %1"),array($form->model_name_s),1) ?>
	<img src="./Images/icon_arrow_orange.png" ></input>
	<td>
	</tr>
	</tbody>
	</table> </form>
	<?php
	if ($this->nb_fragment > 0) echo '</div></div>';
	}
};

class DeleteView extends FormView {
	public function Render(&$form){
		if ($form->FG_DEBUG>0)
			echo "Stub!";
	}
		/** Format and execute the Delete query */
	public function PerformAction(&$form){
		$dbg_elem = new DbgElem();
		$dbhandle = $form->a2billing->DBHandle();
		
		if ($form->FG_DEBUG>0)
			array_unshift($form->pre_elems,$dbg_elem);
			
		$del_clauses = array();
		try {
			foreach($form->model as $fld){
				$qc = $fld->delQueryClause($dbhandle,$form);
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
			$form->setAction('ask-del');
			$form->pre_elems[] = new ErrorElem($ex->getMessage());
			$dbg_elem->content.=  $ex->getMessage().' ('. $ex->getCode() .")\n";
// 			throw new Exception( $err_str);
		}
		
		
		$query = "DELETE FROM " . $form->model_table ;
		
			// Protect against a nasty update!
		if (count($del_clauses)<1){
			$form->pre_elems[] = new ErrorElem("Cannot delete, internal error");
			$dbg_elem->content.= "Delete: no query clauses!\n";
		}
		
		$query .= ' WHERE ' . implode (' AND ', $del_clauses) . ';';
		
		$dbg_elem->content .=$query . "\n";
		
		/* Note: up till now, no data has been quoted/sanitized. Thus, we
		   feed it direcltly to the second part of the query. Pgsql, in particular,
		   can handle a binary transfer of that data to the db, in a well protected
		   manner */
		if (session_readonly()){
			$dbg_elem->content.= "Read-only: query not performed.\n";
			$form->pre_elems[] = new StringElem(_("Read only. No data has been altered."));
			$form->setAction('list');
			return;
			
		}

		if ($form->FG_DEBUG>4){
			$form->setAction('ask-del');
			$dbg_elem->content .= "Debug mode, won't delete!\n";
			return;
		}
		$res = $dbhandle->Execute($query);
		
		if (!$res){
			$form->setAction('ask-del');
			$form->pre_elems[] = new ErrorElem(str_params(_("Cannot delete %1, database error."),array($form->model_name_s),1));
			$dbg_elem->content.= $dbhandle->ErrorMsg() ."\n";
// 			throw new Exception( $err_str);
		}elseif ($dbhandle->Affected_Rows()<1){
			// No result rows: update clause didn't match
			$dbg_elem->content.= ".. EOF, no rows!";
			$dbg_elem->obj = $dbhandle->Affected_Rows();
			$form->pre_elems[] = new ErrorElem(str_params(_("Cannot delete %1, record not found."),array($form->model_name_s),1));
			$form->setAction('list');
		} else {
			$dbg_elem->content.= "Success: DELETE ". $dbhandle->Affected_Rows() . "\n";
			$form->pre_elems[] = new StringElem(_("Record successfully removed from the database."));
			$form->setAction('list');
			
		}
	}

};
?>