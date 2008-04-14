<?php
require_once("Class.ListView.inc.php");

class ListSumView extends ListView {
	public $sum_fns = array();
	public $ncols = null;

	protected function performSumQuery(&$form,&$dbhandle){
		if ($form->FG_DEBUG>3)
			echo "<div class=\"debug\"> ListSum! Building Sum query..</div>";
		
		$query_fields = array();
		$query_outerfields = array();
		$query_clauses = array();
		$query_grps = array();
		$query_table = $form->model_table;
		$query_outertable ='';
		
		foreach($form->model as $fld){
			$fld->buildSumQuery($dbhandle, $this->sum_fns,
				$query_fields,$query_outerfields,$query_table,$query_outertable,
				$query_clauses, $query_grps,$form);
		}
	
		if (!strlen($query_table)){
			if ($form->FG_DEBUG>0)
				echo "No sum table!\n";
			return;
		}
		
		$QUERY = 'SELECT ';
		if (count($query_fields)==0) {
			if ($form->FG_DEBUG>0)
				echo "No sum query fields!\n";
			return;
		}
		
		$QUERY .= implode(', ', $query_fields);
		$QUERY .= ' FROM ' . $query_table;
		
		if (count($query_clauses))
			$QUERY .= ' WHERE ' . implode(' AND ', $query_clauses);
		
		if (!empty($query_grps))
			$QUERY .= ' GROUP BY ' . implode(', ', $query_grps);

		$needouter=false;
		if(!empty($query_outertable))
			$needouter=true;
		else{
			foreach($query_outerfields as $qof)
				if (!is_string($qof)){
					$needouter=true;
					break;
				}
		}
		
		if ($needouter){
			$qf2=array();
			foreach ($query_outerfields as $qof)
				if (is_string($qof))
					$qf2[]=$qof;
				elseif (is_array($qof)){
					// if ($need_raw)
					//	$qf2[]=$qof[1]. ' AS '.$qof[1] .'_raw';
					$qf2[]=$qof[0].' AS '.$qof[1];
				}
			$QUERY = 'SELECT '.implode(', ', $qf2). ' FROM '.
				'('.$QUERY .') AS innerfoo '.$query_outertable;
		}

		$QUERY .= ';';
		
		if ($form->FG_DEBUG>3)
			echo "<div class=\"debug\">SUM QUERY: $QUERY\n</div>\n";
		
		// Perform the query
		$res =$dbhandle->Execute($QUERY);
		if (! $res){
			if ($form->FG_DEBUG>0)
				echo "Query Failed: ". nl2br(htmlspecialchars($dbhandle->ErrorMsg()));
			return;
		}
		return $res;
	}

	public function Render(&$form){
		$this->RenderHead();
	// For convenience, ref the dbhandle locally
	$dbhandle = &$form->a2billing->DBHandle();
		
	$res = $this->performQuery($form,$dbhandle);
	if (!$res)
		return;	
	if ($res->EOF) /*&& cur_page==0) */ {
		if ($form->list_no_records)
			echo $list_no_records;
		else echo str_params(_("No %1 found!"),array($form->model_name_s),1);
	} else {
		$sum_res = $this->performSumQuery($form,$dbhandle);
		// now, DO render the table!
		if ($this->ncols ==null)
			$this->ncols = count($form->model);
		if ($this->sum_title == null)
			$this->sum_title = _("Sum");
		?>
	<TABLE cellPadding="2" cellSpacing="2" align='center' class="<?= $form->list_class?>">
		<thead><tr>
		<?php
		foreach ($form->model as $fld)
			if ($fld) $fld->RenderListHead($form);
		?>
		</tr></thead>
		<tbody>
		<?php
		$row_num = 0;
		while ($row = $res->fetchRow()){
			if ($form->FG_DEBUG > 4) {
				echo '<tr><td colspan = '.$this->ncols .'>';
				print_r($row);
				echo '</td></tr>';
			}
			if ($row_num % 2)
				echo '<tr class="odd">';
			else	echo '<tr>';
			
			foreach ($form->model as $fld)
				if ($fld) $fld->RenderListCell($row,$form);
			echo "</tr>\n";
			$row_num++;
		}
		for(;$row_num < $form->list_least_rows-1; $row_num++)
			if ($row_num % 2)
				echo '<tr class="odd"></tr>';
			else	echo '<tr></tr>';
		
		?>
			<tr class="sum"><td colspan="<?= $this->ncols ?>" ><?= $this->sum_title ?></td></tr>
		<?php
		if ($sum_res)
			while ($row = $sum_res->fetchRow()){
			if ($form->FG_DEBUG > 4) {
				echo '<tr class="sum"><td colspan = 3>';
				print_r($row);
				echo '</td></tr>';
			}
			if ($row_num % 2)
				echo '<tr class="sum_odd">';
			else	echo '<tr>';
			
			foreach ($form->model as $fld)
				if ($fld) $fld->RenderListCell($row,$form);
			echo "</tr>\n";
			$row_num++;
		}

		?>
		
		</tbody>
	</table>
	<?php
		$this->RenderPages($form,$res->NumRows());

	} // query table

	}

};

?>