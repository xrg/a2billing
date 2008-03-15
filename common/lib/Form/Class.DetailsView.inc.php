<?php
require_once("Class.FormViews.inc.php");

class DetailsView extends FormView {
	public $table_class="detailForm";

	function RenderStyle(){
	}

	public function Render(&$form){
		$this->RenderStyle();
		
		$res= $this->PerformQuery($form);
		
		if (!$res)
			return;
	
		// do the table..
		$row=$res->fetchRow();
		?>
	<form action=<?= $_SERVER['PHP_SELF']?> method=post name="<?= $form->prefix?>Frm" id="<?= $form->prefix ?>Frm">
	<?php
		$hidden_arr = array('action' => $form->getAction(), 'sub_action' => '');
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
	?>
<table class="<?= $this->table_class ?>" cellspacing="2">
	<thead><tr><td class="field">&nbsp;</td><td class="value">&nbsp;</td></tr>
	</thead>
	<tbody>
	<?php
		foreach($form->model as $fld)
			if ($fld){
		?><tr><td class="field"><?php
				$fld->RenderEditTitle($form);
		?></td><td class="value"><?php
				$fld->DispList($row,$form);
		?></td></tr>
		<?php
		}
	?>
	</tbody>
	</table> </form>
	<?php
	}
	
	function PerformQuery(&$form){
		$dbhandle = &$form->a2billing->DBHandle();
		if ($form->FG_DEBUG>3)
			echo "Details! Building query..";
			
		
		$query_fields = array();
		$query_clauses = array();
		$query_table = $form->model_table;
		
		foreach($form->model as $fld){
			$tmp= $fld->detailQueryField($dbhandle);
			if ( is_string($tmp))
				$query_fields[] = $tmp;
			elseif (is_array($tmp))
				$query_fields=array_merge($query_fields,$tmp);
			
			$tmp= $fld->detailQueryClause($dbhandle,$form);
			if ( is_string($tmp))
				$query_clauses[] = $tmp;
				
			$fld->detailQueryTable($query_table,$form);
		}
		
		if (!strlen($query_table)){
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
		$QUERY .= ' FROM ' . $query_table;
		
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
				echo $form->edit_no_records;
			else echo str_params(_("No %1 found!"),array($form->model_name_s),1);
			}
		return $res;
	}
};

?>