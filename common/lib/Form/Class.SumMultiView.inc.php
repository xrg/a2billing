<?php
require_once("Class.FormViews.inc.php");

/** This is a collection of sums, in the same page:
    Use it to display various statistics per-something from a model.
    each group of sums contains one element of display information..

*/
class SumMultiView extends FormView {
	public $list_class ='sumlist';
	public $titlerow; ///< A row spanning all columns, used for title
	public $ncols = null;   ///< Numer of columns for titles, used as colspan
	public $sums = array();
	
	
	protected function RenderHead(){
?>
<style>
table.sumlist {
	width: 95%;
	border-bottom: #ffab12 0px solid; 
	border-left: #e1e1e1 0px solid; 
	border-right: #e1e1e1 1px solid; 
	border-top: #e1e1e1 0px solid; 
	padding-bottom: 4px; 
	padding-left: 4px; 
	padding-right: 4px; 
	padding-top: 4px;	
	font-size: 10px;
}
.sumlist thead {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 11px;
	font-weight: bold;
	text-transform: uppercase;
	color: #FFFFFF;
	background-color: #7a7a7a;
}
.sumlist thead a{
	color: #FFFFFF;
}
.sumlist thead a:hover{
	color: #FFFFFF;
}

table.sumlist tbody tr{
	background-color: #F2F2F2;
}

table.sumlist tbody .odd{
	background-color: #E0E0E0;
}

table.sumlist tbody tr:hover {
	background-color: #FFDEA6;
}
</style>
<?php
	}
	
	protected function performSumQuery($summ,$form,$dbhandle){
		if ($form->FG_DEBUG)
			echo "<tr class=\"debug\"><td colspan=\"".$this->ncols ."\">";
		if ($form->FG_DEBUG>3)
			echo "ListSum! Building Sum query..";
		
		if (empty($summ['fns'])){
			if ($form->FG_DEBUG>0)
				echo "No sum functions!</td></tr>\n";
			return;
		}

		$query_fields = array();
		$query_clauses = array();
		$query_grps = array();
		$query_table = $form->model_table;
		
		foreach($form->model as $fld){
			$fld->buildSumQuery($dbhandle, $summ['fns'],
				$query_fields,$query_table,
				$query_clauses, $query_grps,$form);
		}
	
		if (!strlen($query_table)){
			if ($form->FG_DEBUG>0)
				echo "No sum table!</td></tr>\n";
			return;
		}
		
		$QUERY = 'SELECT ';
		if (count($query_fields)==0) {
			if ($form->FG_DEBUG>0)
				echo "No sum query fields!</td></tr>\n";
			return;
		}
		
		$QUERY .= implode(', ', $query_fields);
		$QUERY .= ' FROM ' . $query_table;
		
		if (count($query_clauses))
			$QUERY .= ' WHERE ' . implode(' AND ', $query_clauses);
		
		if (!empty($query_grps))
			$QUERY .= ' GROUP BY ' . implode(', ', $query_grps);

		$QUERY .= ';';
		
		if ($form->FG_DEBUG>3)
			echo "<br>SUM QUERY: $QUERY\n<br>\n";
		
		// Perform the query
		$res =$dbhandle->Execute($QUERY);
		if (! $res){
			if ($form->FG_DEBUG>0)
				echo "Query Failed: ". nl2br(htmlspecialchars($dbhandle->ErrorMsg())) ."</td></tr>";
			return;
		}
		if ($form->FG_DEBUG)
			echo "</td></tr>";
		return $res;

	}
	
	public function Render(&$form){
		$this->RenderHead();
	// For convenience, ref the dbhandle locally
	$dbhandle = &$form->a2billing->DBHandle();
		
		if ($this->ncols ==null)
			$this->ncols = count($form->model);
	//	else echo str_params(_("No %1 found!"),array($form->model_name_s),1);
		
	// Render the table anyway..	
		?>
	<TABLE cellPadding="2" cellSpacing="2" align='center' class="<?= $this->list_class?>">
		<thead><tr>
		<?php
		if (!empty($this->titlerow)){
			?><tr class="title"><td colspan="<?= $this->ncols?>"><?= $this->titlerow ?></td></tr>
		<?php
		}
		foreach ($form->model as $fld)
			$fld->RenderListHead($form);
		?>
		</tr></thead>
		<tbody>
		<?php
		$row_num = 0;
		foreach($this->sums as $summ) {
			$res = $this->performSumQuery($summ,$form,$dbhandle);
			if (!$res)
				continue;
			if (!empty($summ['title'])){
				?><tr class="sumtitle"><td colspan="<?= $this->ncols?>"><?= $summ['title'] ?></td></tr>
		<?php
			}
			
			while ($row = $res->fetchRow()){
				if ($row_num % 2)
					echo '<tr class="odd">';
				else	echo '<tr>';
				
				foreach ($form->model as $fld)
					if ($fld) $fld->RenderListCell($row,$form);
				echo "</tr>\n";
				$row_num++;
			}
		}
		if ($row_num ==0){
		?><tr><td colspan="<?= $this->ncols?>"><?= _("No sums found!") ?></td></tr>
		<?php
		}
		?>
		</tbody>
	</table>
	<?php

	}

};

?>
