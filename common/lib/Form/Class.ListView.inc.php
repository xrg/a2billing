<?php
require_once("Class.FormViews.inc.php");

class ListView extends FormView {

	protected function RenderHead(){
	}
	
	/** Builds and executes the table list query */
	protected function performQuery(&$form,&$dbhandle){
		if ($form->FG_DEBUG>3)
			echo "<div class='debug'>List! Building query..</div>";
		
		$query_fields = array();
		$query_clauses = array();
		$query_table = $form->model_table;
		
		foreach($form->model as $fld){
			$tmp= $fld->listQueryField($dbhandle);
			if ( is_string($tmp))
				$query_fields[] = $tmp;
			elseif (is_array($tmp))
				$query_fields=array_merge($query_fields,$tmp);
			
			$tmp= $fld->listQueryClause($dbhandle,$form);
			if ( is_string($tmp))
				$query_clauses[] = $tmp;
				
			$fld->listQueryTable($query_table,$form);
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
		
		if ($form->order)
			$QUERY .= " ORDER BY $form->order";
		if (($form->sens) && (strtolower($form->sens)=='desc'))
			$QUERY .= " DESC";
		if ($form->ndisp)
			$QUERY .= " LIMIT $form->ndisp";
		if ($form->cpage)
			$QUERY .= " OFFSET " . ($form->cpage * $form->ndisp);
		$QUERY .= ';';
		
		if ($form->FG_DEBUG>3)
			echo "<div class=\"debug\">QUERY: $QUERY\n</div>\n";
		
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
		// now, DO render the table!
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
				echo '<tr class="debug"><td colspan = 3>';
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
		for(;$row_num < $form->list_least_rows; $row_num++)
			if ($row_num % 2)
				echo '<tr class="odd"></tr>';
			else	echo '<tr></tr>';
		?>
		</tbody>
	</table>
	<?php
		$this->RenderPages($form,$res->NumRows());

	} // query table

	}

	protected function RenderPages(&$form,&$numrows){
			//automatically choose to use paginating..
		if (($form->ndisp && ($numrows >=$form->ndisp)) || 
			( isset($form->cpage) && $form->cpage>0)){
		?>
		<table class="paginate">
		<tr><td align="left">
			<form name="<?= $form->prefix ?>otherForm2" action="<?php echo $_SERVER['PHP_SELF']?>">
			<?= _("DISPLAY")?>
			<?= $form->gen_PostParams(array(cpage => 0)); ?>
			
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
		$page_var= $form->prefix.'cpage';
		
			//echo "<center><p>\n";
		if ($form->cpage > 0) {
			?>
			<a href="<?= $url . $form->gen_GetParams( array( $page_var =>  0)) ?>" ><?= _("First")?></a>
			<a href="<?= $url . $form->gen_GetParams( array( $page_var =>  $form->cpage - 1)) ?>" ><?= _("Prev")?></a>
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
				$temp = $url . $form->gen_GetParams( array( $page_var => $i-1));
				if ($i != $page) echo "<a class=\"pagenav\" href=\"{$temp}\">$i</a>\n";
				else echo "$i\n";
			}
		}
		
		if ($form->ndisp && ($numrows >=$form->ndisp)){
			?> <a href="<?= $url . $form->gen_GetParams( array( $page_var =>  $form->cpage+1)) ?>" ><?= _("Next")?></a>
			<?php
		}
		?>
		</td></tr>
		<table>
		<?php }

	}
};