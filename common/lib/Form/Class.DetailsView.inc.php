<?php

require_once("Class.FormViews.inc.php");

class DetailsView extends FormView {
	public $table_class="detailForm";

	function RenderStyle(){
	}

	protected function RenderFormHead($row,&$form){
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
	}
	
	public function Render(&$form){
		$this->RenderStyle();
		
		$res= $this->PerformQuery($form);
		
		if (!$res)
			return;
	
		// do the table..
		$row=$res->fetchRow();
		$this->RenderFormHead($row,$form);
		?>
<table class="<?= $this->table_class ?>" cellspacing="2">
	<thead><tr><td class="field">&nbsp;</td><td class="value">&nbsp;</td></tr>
	</thead>
	<tbody>
	<?php
		foreach($form->model as $fld)
			if ($fld){
			if ($fld instanceof TabField){ ?>
		<tr><td class="tabField" colspan=2><?= $fld->caption ?></td></tr>
<?php
			}else {
		?><tr><td class="field"><?php
				$fld->RenderEditTitle($form);
		?></td><td class="value"><?php
				$fld->DispList($row,$form);
		?></td></tr>
		<?php
			}
		}
	?>
	</tbody>
	</table> </form>
	<?php
	}
	
	function PerformQuery(&$form){
		$dbhandle = &$form->a2billing->DBHandle();
		if ($form->FG_DEBUG>3)
			echo "<div class=\"debug\">Details! Building query..</div>";
			
		
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
				echo "<div class=\"debug\">No table!</div>\n";
			return;
		}
		
		$QUERY = 'SELECT ';
		if (count($query_fields)==0) {
			if ($form->FG_DEBUG>0)
				echo "<div class=\"debug\">No query fields!</div>\n";
			return;
		}
		
		$QUERY .= implode(', ', $query_fields);
		$QUERY .= ' FROM ' . $query_table;
		
		if (count($query_clauses))
			$QUERY .= ' WHERE ' . implode(' AND ', $query_clauses);
		
		$QUERY .= ' LIMIT 1;'; // we can only edit one record at a time!
		
		if ($form->FG_DEBUG>3)
			echo "<div class=\"debug\">QUERY: $QUERY</div>\n";
		
		// Perform the query
		$res =$dbhandle->Execute($QUERY);
		if (! $res){
			if ($form->FG_DEBUG>0)
				echo "<div class=\"debug\">Query Failed: ". nl2br(htmlspecialchars($dbhandle->ErrorMsg()))."</div>";
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

/** A two-column detail view */
class Details2cView extends DetailsView {
	public function Render(&$form){
		$this->RenderStyle();
		
		$res= $this->PerformQuery($form);
		
		if (!$res)
			return;
	
		// do the table..
		$row=$res->fetchRow();
		$this->RenderFormHead($row,$form);
	?>
<table class="<?= $this->table_class ?>" cellspacing="2">
	<thead><tr><td class="field2c">&nbsp;</td><td class="value2c">&nbsp;</td>
	<td class="field2c">&nbsp;</td><td class="value2c">&nbsp;</td></tr>
	</thead>
	<tbody>
	<?php
		$a=0;
		foreach($form->model as $fld)
			if ($fld){
			if (($a % 2) == 0) echo '<tr>';
		?><td class="field"><?php
				$fld->RenderEditTitle($form);
		?></td><td class="value"><?php
				$fld->DispList($row,$form);
		?></td><?php
			if (($a++ %2) ==1) echo "</tr>\n";
				else echo "\n";
		}
	?>
	</tbody>
	</table> </form>
	<?php
	}
};

/** A Multi-column detail view */
class DetailsMcView extends DetailsView {
	public $ncols=2;
	
	public function Render(&$form){
		$this->RenderStyle();
		
		$res= $this->PerformQuery($form);
		
		if (!$res)
			return;
	
		// do the table..
		$row=$res->fetchRow();
		$this->RenderFormHead($row,$form);
		
		$r=0;
		$nrow= (count($form->model) + $this->ncols -1)/$this->ncols;
		//$wcol = intval (100 / $this->ncols). '%';
		$wcol= 'auto';
		//echo "N. Rows: $nrow<br>";
	?>
	<table class="detailsMc" cellspacing=0>
	<tr><?php
		//$col=0;
		foreach($form->model as $fld)
			if ($fld){
				if(($r % $nrow) ==0) { ?>
<td width="<?= $wcol ?>">
<table class="<?= $this->table_class ?>" cellspacing="2">
	<thead><tr><td class="field">&nbsp;</td><td class="value">&nbsp;</td></tr>
	</thead>
	<tbody>
	<?php
				}
		?>
		<tr><td class="field"><?php
				$fld->RenderEditTitle($form);
		?></td><td class="value"><?php
				$fld->DispList($row,$form);
		?></td></tr>
<?php
		$r++;
			if(($r % $nrow) ==0) { ?>
	</tbody>
</table></td>
<?php
			}
		}
	?>
</tr></table>
	</form>
	<?php
	}
};
?>