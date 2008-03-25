<?php
require_once("Class.FormViews.inc.php");

class AskEditView extends FormView {
	protected $nb_fragment = 0;
	 
	public function Render(&$form){
	// For convenience, ref the dbhandle locally
	$dbhandle = &$form->a2billing->DBHandle();
	
	if ($form->FG_DEBUG>3)
		echo "List! Building query..";
		
	
	$query_fields = array();
	$query_clauses = array();
	foreach($form->model as $fld){
		$tmp= $fld->editQueryField($dbhandle);
		if ( is_string($tmp))
			$query_fields[] = $tmp;
		
		$tmp= $fld->editQueryClause($dbhandle,$form);
		if ( is_string($tmp))
			$query_clauses[] = $tmp;
	}
	
	if ($form->model_table == null){
		if ($form->FG_DEBUG>0)
			echo "No table!\n";
		return;
	}
	
	$QUERY = 'SELECT ';
	if (count($query_fields)==0) {
		if ($form->FG_DEBUG>0)
			echo "No query fields!\n";
		return;
	}
	
	$QUERY .= implode(', ', $query_fields);
	$QUERY .= ' FROM ' . $form->model_table;
	
	if (count($query_clauses))
		$QUERY .= ' WHERE ' . implode(' AND ', $query_clauses);
	
	$QUERY .= ' LIMIT 1;'; // we can only edit one record at a time!
	
	if ($form->FG_DEBUG>3)
		echo "QUERY: $QUERY\n<br>\n";
	
	// Perform the query
	$res =$dbhandle->Execute($QUERY);
	if (! $res){
		if ($form->FG_DEBUG>0)
			echo "Query Failed: ". nl2br(htmlspecialchars($dbhandle->ErrorMsg()));
		return;
	}
	
	if ($res->EOF) /*&& cur_page==0) */ {
		if ($form->edit_no_records)
			echo $edit_no_records;
		else echo str_params(_("No %1 found!"),array($form->model_name_s),1);
	} else {
		// do the table..
		$row=$res->fetchRow();
		?>
	<form action=<?= $_SERVER['PHP_SELF']?> method=post name="<?= $form->prefix?>Frm" id="<?= $form->prefix ?>Frm">
	<?php
		$hidden_arr = array('action' => 'edit','sub_action' => '');
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
				<table class="editForm" cellspacing="2">
					<thead><tr><td class="field">&nbsp;</td><td class="value">&nbsp;</td></tr></thead>
					<tbody>
				<?php
			}
			$loopmodel++;
			
			if ($fld && $fld->does_edit){
		?><tr><td class="field"><?php
				$fld->RenderEditTitle($form);
		?></td><td class="value"><?php
				$fld->DispEdit($row,$form);
		?></td></tr>
		<?php
			}
		}
	?>
	<tr class="confirm"><td colspan=2 align="right">
	<button type=submit>
	<?= str_params(_("Update this %1"),array($form->model_name_s),1) ?>
	<img src="./Images/icon_arrow_orange.png" ></input>
	<td>
	</tr>
	</tbody>
	</table> </form>
	<?php
	if ($this->nb_fragment > 0) echo '</div></div>';
	}
}
};


class AskEdit2View extends AskEditView {
};

class EditView extends FormView{
	public function Render(&$form){
		if ($form->FG_DEBUG>0)
			echo "Stub!";
	}

	/** Format and execute the Update query */
	public function PerformAction(&$form){
		$dbg_elem = new DbgElem();
		$dbhandle = $form->a2billing->DBHandle();
		
		if ($form->FG_DEBUG>0)
			array_unshift($form->pre_elems,$dbg_elem);
			
		// just build the value list..
		$upd_data=array();
		$upd_clauses = array();
		try {
			foreach($form->model as $fld){
				$fld->buildUpdate($upd_data,$form);
				$qc = $fld->editQueryClause($dbhandle,$form);
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
			$form->action = 'ask-edit2';
			$form->pre_elems[] = new ErrorElem($ex->getMessage());
			$dbg_elem->content.=  $ex->getMessage().' ('. $ex->getCode() .")\n";
// 			throw new Exception( $err_str);
		}
		
		$upd_values = array();
		
		$query = "UPDATE " . $form->model_table . " SET ";
		
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
			$form->pre_elems[] = new ErrorElem("Cannot update, internal error");
			$dbg_elem->content.= "Update: no query clauses!\n";
		}
		
		$query .= ' WHERE ' . implode (' AND ', $upd_clauses) . ';';
		$query_dbg .= ' WHERE ' . implode (' AND ', $upd_clauses) . ';';
		
		$dbg_elem->content .=$query_dbg . "\n";
		
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
		
		$res = $dbhandle->Execute($query,$upd_values);
		
		if (!$res){
			$form->setAction('ask-edit2');
			$form->pre_elems[] = new ErrorElem(str_params(_("Cannot update %1, database error."),array($form->model_name_s),1));
			$dbg_elem->content.= $dbhandle->ErrorMsg() ."\n";
// 			throw new Exception( $err_str);
		}elseif ($dbhandle->Affected_Rows()<1){
			// No result rows: update clause didn't match
			$dbg_elem->content.= ".. EOF, no rows!";
			$dbg_elem->obj = $dbhandle->Affected_Rows();
			$form->pre_elems[] = new ErrorElem(str_params(_("Cannot update %1, record not found."),array($form->model_name_s),1));
			$form->setAction('ask-edit2');
		} else {
			$dbg_elem->content.= "Success: UPDATE ". $dbhandle->Affected_Rows() . "\n";
			$form->pre_elems[] = new StringElem(_("Data has successfully been updated in the database."));
			$form->setAction('list');
			
		}
	}

};

?>