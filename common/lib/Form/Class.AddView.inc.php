<?php
require_once("Class.FormViews.inc.php");

class AskAddView extends FormView {
	protected $nb_fragment = 0;
	
	public function Render(&$form){
		$dbhandle = &$form->a2billing->DBHandle();
	?>
	<form action=<?= $_SERVER['PHP_SELF']?> method=post name="<?= $form->prefix?>Frm" id="<?= $form->prefix ?>Frm">
	<?php		$hidden_arr = array( 'action' => 'add', 'sub_action' => '');
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
		<table class="addForm" cellspacing="2">
			<thead><tr><td class="field">&nbsp;</td><td class="value">&nbsp;</td></tr></thead>
			<tbody>
		<?php
				}
				$loopmodel++;
				
				if ($fld && $fld->does_add){
			?><tr><td class="field"><?php
					$fld->RenderAddTitle($form);
			?></td><td class="value"><?php
					$fld->DispAdd($form);
			?></td></tr>
		<?php
				}
			}
		?>
		<tr class="confirm"><td colspan=2 align="right">
		<button type=submit>
		<?= str_params(_("Create this %1"),array($form->model_name_s),1) ?>
		<img src="./Images/icon_arrow_orange.png" ></button>
		<td>
		</tr>
		</tbody>
		</table> </form>
		<?php
		if ($this->nb_fragment > 0) echo '</div></div>';
	}
};

class AskAdd2View extends AskAddView{
	protected function RenderFields(&$form){
		foreach($form->model as $fld)
		    if ($fld && $fld->does_add){
		?><tr><td class="field"><?php
			$fld->RenderAddTitle($form);
		?></td><td class="value"><?php
			$fld->DispAddEdit($form->getpost_dirty($fld->fieldname),$form);
		?></td></tr>
		<?php
		}
	}
};

class AddView extends FormView {
	public function Render(&$form){
		if ($form->FG_DEBUG>0)
			echo "Stub!";
	}
	public function PerformAction(&$form){
		$dbg_elem = new DbgElem();
		$dbhandle = $form->a2billing->DBHandle();
		
		if ($form->FG_DEBUG>0)
			array_unshift($form->pre_elems,$dbg_elem);
			
		// just build the value list..
		$ins_data=array();
		
		try {
			foreach($form->model as $fld)
				$fld->buildInsert($ins_data,$form);
		} catch (Exception $ex){
			$form->setAction('ask-add2');
			$form->pre_elems[] = new ErrorElem($ex->getMessage());
			$dbg_elem->content.=  $ex->message.' ('. $ex->getCode() .")\n";
// 			throw new Exception( $err_str);
		}
		$ins_keys = array();
		$ins_values = array();
		$ins_qm = array();
		
		foreach ($ins_data as $ins){
			$ins_keys[] =$ins[0];
			if (count($ins)>2)
				$ins_qm[] = $ins[2];
			else
				$ins_qm[] = '?';
			$ins_values[] = $ins[1];
		}
		
		if (substr($form->model_table,0,5)=='ONLY ')
			$mtable=substr($form->model_table,5);
		else
			$mtable=$form->model_table;
		
		$dbg_elem->content.= "Query: INSERT INTO ". $mtable ."(";
		$dbg_elem->content.= implode(', ',$ins_keys);
		$dbg_elem->content.= ") VALUES(". var_export($ins_values,true).");\n";
		
		$query = "INSERT INTO ". $mtable ."(" .
			implode(', ',$ins_keys) . ") VALUES(". 
			implode(',', $ins_qm).");";
		
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

		$res = $dbhandle->Execute($query,$ins_values);
		
		if (!$res){
			$form->setAction('ask-add2');
			$form->pre_elems[] = new ErrorElem(str_params(_("Cannot create new %1, database error."),array($form->model_name_s),1));
			$dbg_elem->content.= $dbhandle->ErrorMsg() ."\n";
// 			throw new Exception( $err_str);
		}else{
			$dbg_elem->content.= ".. success: ". gettype($res) . "\n";
			$form->pre_elems[] = new StringElem(_("New data has successfully been inserted into the database."));
			$form->setAction('list');
		}
	}

};
?>
