<?php
     /* This is the implementation of function FormHandler::RenderList()
     */

	// For convenience, ref the dbhandle locally
	$dbhandle = &$this->a2billing->DBHandle();
?>
<style>
table.cclist {
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
.cclist thead {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 11px;
	font-weight: bold;
	text-transform: uppercase;
	color: #FFFFFF;
	background-color: #7a7a7a;
}
.cclist thead a{
	color: #FFFFFF;
}
.cclist thead a:hover{
	color: #FFFFFF;
}

table.cclist tbody tr{
	background-color: #F2F2F2;
}

table.cclist tbody .odd{
	background-color: #E0E0E0;
}

table.cclist tbody tr:hover {
	background-color: #FFDEA6;
}
</style>
<?php
	if ($this->FG_DEBUG>3)
		echo "List! Building query..";
		
	
	$query_fields = array();
	$query_clauses = array();
	$query_table = $this->model_table;
	
	foreach($this->model as $fld){
		$tmp= $fld->listQueryField($dbhandle);
		if ( is_string($tmp))
			$query_fields[] = $tmp;
		elseif (is_array($tmp))
			$query_fields=array_merge($query_fields,$tmp);
		
		$tmp= $fld->listQueryClause($dbhandle);
		if ( is_string($tmp))
			$query_clauses[] = $tmp;
			
		$fld->listQueryTable($query_table,$form);
	}
	
	if (!strlen($query_table)){
		if ($this->FG_DEBUG>0)
			echo "No table!\n";
		return;
	}
	
	$QUERY = 'SELECT ';
	if (count($query_fields)==0) {
		if ($this->FG_DEBUG>0)
			echo "No query fields!\n";
		return;
	}
	
	$QUERY .= implode(', ', $query_fields);
	$QUERY .= ' FROM ' . $query_table;
	
	if (count($query_clauses))
		$QUERY .= ' WHERE ' . implode(' AND ', $query_clauses);
	
	if ($this->order)
		$QUERY .= " ORDER BY $this->order";
	if (($this->sens) && (strtolower($this->sens)=='desc'))
		$QUERY .= " DESC";
	if ($this->ndisp)
		$QUERY .= " LIMIT $this->ndisp";
	if ($this->cpage)
		$QUERY .= " OFFSET " . ($this->cpage * $this->ndisp);
	$QUERY .= ';';
	
	if ($this->FG_DEBUG>3)
		echo "QUERY: $QUERY\n<br>\n";
	
	// Perform the query
	$res =$dbhandle->Execute($QUERY);
	if (! $res){
		if ($this->FG_DEBUG>0)
			echo "Query Failed: ". nl2br(htmlspecialchars($dbhandle->ErrorMsg()));
		return;
	}
	
	if ($res->EOF) /*&& cur_page==0) */ {
		if ($this->list_no_records)
			echo $list_no_records;
		else echo str_params(_("No %1 found!"),array($this->model_name_s),1);
	} else {
		// now, DO render the table!
		?>
	<TABLE cellPadding="2" cellSpacing="2" align='center' class="<?= $this->list_class?>">
		<thead><tr>
		<?php
		foreach ($this->model as $fld)
			if ($fld) $fld->RenderListHead($this);
		?>
		</tr></thead>
		<tbody>
		<?php
		$row_num = 0;
		while ($row = $res->fetchRow()){
			if ($this->FG_DEBUG > 4) {
				echo '<tr><td colspan = 3>';
				print_r($row);
				echo '</td></tr>';
			}
			if ($row_num % 2)
				echo '<tr class="odd">';
			else	echo '<tr>';
			
			foreach ($this->model as $fld)
				if ($fld) $fld->RenderListCell($row,$this);
			echo "</tr>\n";
			$row_num++;
		}
		for(;$row_num < $this->list_least_rows; $row_num++)
			if ($row_num % 2)
				echo '<tr class="odd"></tr>';
			else	echo '<tr></tr>';
		?>
		</tbody>
	</table>
	<?php
			//automatically choose to use paginating..
		if (($this->ndisp && ($res->NumRows() >=$this->ndisp)) || 
			( isset($this->cpage) && $this->cpage>0)){
		?>
		<table class="paginate">
		<tr><td align="left">
			<form name="<?= $this->prefix ?>otherForm2" action="<?php echo $_SERVER['PHP_SELF']?>">
			<?= _("DISPLAY")?>
			<?= $this->gen_PostParams(array(cpage => 0)); ?>
			
			<select name="ndisp" size="1" class="form_input_select">
				<option value="10" selected>10</option>
				<option value="30">30</option>
				<option value="50">50</option>
				<option value="100">100</option>
				<option value="ALL"><?= _("All") ?></option>
			</select>
			<input class="form_input_button"  value=" <?= _("GO");?> " type="SUBMIT">
			</form>
		</td>
		<td align="right">
		<?php
		//$window = 8;
		
		$pages =10;
		$page_var= $this->prefix.'cpage';
		
			//echo "<center><p>\n";
		if ($this->cpage > 0) {
			?>
			<a href="<?= $url . $this->gen_GetParams( array( $page_var =>  0)) ?>" ><?= _("First")?></a>
			<a href="<?= $url . $this->gen_GetParams( array( $page_var =>  $this->cpage - 1)) ?>" ><?= _("Prev")?></a>
			<?php
		}
			
		if (false) {
			if ($page <= $window) { 
				$min_page = 1; 
				$max_page = min(2 * $window, $pages); 
			}
			elseif ($page > $window && $pages >= $page + $window) { 
				$min_page = ($page - $window) + 1; 
				$max_page = $page + $window; 
			}
			else { 
				$min_page = ($page - (2 * $window - ($pages - $page))) + 1; 
				$max_page = $pages; 
			}
			
			// Make sure min_page is always at least 1
			// and max_page is never greater than $pages
			$min_page = max($min_page, 1);
			$max_page = min($max_page, $pages);
			
			for ($i = $min_page; $i <= $max_page; $i++) {
				$temp = $url . $this->gen_GetParams( array( $page_var => $i-1));
				if ($i != $page) echo "<a class=\"pagenav\" href=\"{$temp}\">$i</a>\n";
				else echo "$i\n";
			}
		}
		
		if ($this->ndisp && ($res->NumRows() >=$this->ndisp)){
			?> <a href="<?= $url . $this->gen_GetParams( array( $page_var =>  $this->cpage+1)) ?>" ><?= _("Next")?></a>
			<?php
		}
		?>
		</td></tr>
		<table>
		<?php }

	} // query table
	
?>