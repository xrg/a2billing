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
	public $plots = array();
	
	
	protected function RenderHead(){
	}
	
	protected function performSumQuery(&$summ,&$form,&$dbhandle){
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
		
		if (isset($summ['clauses']))
			$query_clauses = array_merge($query_clauses,$summ['clauses']);

		$QUERY .= implode(', ', $query_fields);
		$QUERY .= ' FROM ' . $query_table;
		
		if (count($query_clauses))
			$QUERY .= ' WHERE ' . implode(' AND ', $query_clauses);
		
		if (!empty($query_grps) && 
			(!isset($summ['group']) || ($summ['group'] != false)) )
			$QUERY .= ' GROUP BY ' . implode(', ', $query_grps);
		
			//Try to see if we can order
		if (!empty($form->order)){
			if (empty($query_grps) || in_array($form->order,$query_grps))
				 $ordert =$form->order;
			else {
				// search again for expressions
				foreach ($form->model as $fld)
					if ($fld->fieldname == $form->order){
						if (in_array($fld->fieldexpr,$query_grps))
							$ordert = $fld->fieldexpr;
						break;
					}
			}
		}
		elseif (!empty($summ['order']))
			$ordert = $summ['order'];
		else
			$ordert = null;
		
		if(!empty($ordert)){
			$QUERY .= " ORDER BY $ordert";
			if (!empty($form->sens)){
				if (strtolower($form->sens)=='desc')
					$QUERY .= " DESC";
			} elseif (!empty($summ['sens']) && (strtolower($summ['sens'])=='desc'))
				$QUERY .= " DESC";
		}
		
		if (!empty($summ['limit']))
			$QUERY .= ' LIMIT '.$summ['limit'];
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
	
	public function RenderGraph(&$form,&$graph){
		$gmode= $form->getpost_single('graph');
		
		$graph->SetScale("textlin");
		$graph->yaxis->scale->SetGrace(3);

		if ($form->FG_DEBUG>1)
			echo "RenderGraph!\n";
		
		$dbhandle = &$form->a2billing->DBHandle();
		$tsum = $this->plots[$gmode];
		
		if (!$tsum)
			return false;
		
		$graph->title->Set($tsum->title);
		$res = $this->performSumQuery($tsum,$form,$dbhandle);
		
		switch($tsum['type']){
		case 'bar':
			$xdata = array();
			$ydata = array();
			$xkey = $tsum['x'];
			$ykey = $tsum['y'];
			while ($row = $res->fetchRow()){
				$xdata[] = $row[$xkey];
				$ydata[] = $row[$ykey];
			}
			if (! empty($tsum['xlabelangle'])){
				$graph->xaxis->SetLabelAngle($tsum['xlabelangle']);
				if ($tsum['xlabelangle']<0)
					$graph->xaxis->SetLabelAlign('left');
			}
			if (! empty($tsum['xlabelfont']))
				$graph->xaxis->SetFont($tsum['xlabelfont']);
			else
				$graph->xaxis->SetFont(FF_VERA);
			$graph->xaxis->SetTickLabels($xdata);
			$bplot = new BarPlot($ydata);
			$graph->Add($bplot);
			if ($form->FG_DEBUG>2){
				echo "X data: ";
				print_r($xdata);
				echo "\n Y data: ";
				print_r($ydata);
			}
			if ($form->FG_DEBUG>1)
				echo "Added Bar plot";
			break;

		default:
			if ($form->FG_DEBUG>1)
			echo "Unknown graph type: ".$tsum['type'] . "\n";
		}
		return true;
	}

};

/** A multiple column (paginated), multi-sum (queries) view */
class Multi2SumView extends SumMultiView {
	public $page_cols = 1; ///< Number of columns to display
	public $page_rows = 20; ///< Maximum number of rows per column/page
	public $page_rows_first = 7;
	public $page_rows_last = 5;
	
	protected function RenderTHead(&$form, &$col, &$row, &$mrows){
		//if ( $n < $num_rows * $num_cols)
		//	$n += $num_rows_first;
		if ( (($col++) % $this->page_cols) == 0){
			echo "<tr>";
			$row++;
		}
		echo "<td width=\"" . (100/$this->page_cols) . "%\">";
		if ($form->FG_DEBUG>2) echo "Col: $col/".$this->page_cols ." <br>\n";
?>
	<table cellPadding="2" cellSpacing="2" align='center' class="<?= $this->list_class?>">
		<thead><tr>
		<?php
		if (!empty($this->titlerow) && ($row ==0)){
			?><tr class="title"><td colspan="<?= $this->ncols?>"><?= $this->titlerow ?></td></tr>
		<?php
		}
		foreach ($form->model as $fld)
			$fld->RenderListHead($form);
		?>
		</tr></thead>
		<tbody>
		<?php
		// Set the mrows to the appropriate value:
		if ($row == 0)
			$mrows= $this->page_rows_first;
		else
			$mrows= $this->page_rows;
	}
	
	protected function RenderTFoot(&$col,&$row) {
		echo "</tbody></table></td>";
		if ( ($col % $this->page_cols) == 0)
			echo "</tr>";
		echo "\n";
	}
	
	public function Render(&$form){
		$this->RenderHead();
	// For convenience, ref the dbhandle locally
	$dbhandle = &$form->a2billing->DBHandle();
		
	if ($this->ncols ==null)
		$this->ncols = count($form->model);
	//	else echo str_params(_("No %1 found!"),array($form->model_name_s),1);
		
	// Variables that hold the table/pagination counters
	$in_table = false;
	$pg_row = 0;
	$row_num = 0;
	$col_num = 0;
	$mrows = 10;
	
	echo '<table class="invoice_cols">';
	foreach($this->sums as $summ) {
		if (!$in_table){
			$this->RenderTHead($form,$col_num,$pg_row, $mrows);
			$in_table = true;
		}
		$res = $this->performSumQuery($summ,$form,$dbhandle);
		if (!$res)
			continue;
		if (!empty($summ['title'])){
			?><tr class="sumtitle"><td colspan="<?= $this->ncols?>"><?= $summ['title'] ?></td></tr>
	<?php
		}
			
		while ($row = $res->fetchRow()){
			if (!$in_table){
				$this->RenderTHead($form,$col_num,$pg_row, $mrows);
				$in_table = true;
			}
			if ($row_num % 2)
				echo '<tr class="odd">';
			else	echo '<tr>';
			
			foreach ($form->model as $fld)
				if ($fld) $fld->RenderListCell($row,$form);
			echo "</tr>\n";
			$row_num++;
			
			if (($row_num % $mrows) == 0){
				$this->RenderTFoot($col_num, $pg_row);
				$in_table = false;
			}
		}
	}
	
	if ($row_num ==0){
		?><tr><td colspan="<?= $this->ncols?>"><?= _("No sums found!") ?></td></tr>
	<?php
	}
	
	if ($in_table){
		//close inner and outer tables
		$this->RenderTFoot($col_num, $pg_row);
	}
	echo '</table>';
	} // fn Render

};

?>
