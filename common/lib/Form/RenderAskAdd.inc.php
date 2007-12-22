<?php
     /* This is the implementation of function FormHandler::RenderEdit()
     */

	// For convenience, ref the dbhandle locally
	$dbhandle = &$this->a2billing->DBHandle();
?>
<style>
table.addForm {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 11px;
	font-weight: bold;
	width: 90%;
}
table.addForm thead {
	text-transform: uppercase;
	color: #FFFFFF;
	background-color: #7a7a7a;
}
table.addForm thead .field {
	width: 25%;
}
table.addForm thead .value {
	width: 75%;
}

table.addForm tbody .field {
	text-transform: uppercase;
	color: #FFFFFF;
	background-color: #9a9a9a;
}
table.addForm div.descr {
	font-size: 9px;
	font-weight: normal;
}
</style>

	<form action=<?= $_SERVER['PHP_SELF']?> method=post name="<?= $this->prefix?>Frm" id="<?= $this->prefix ?>Frm">
	<?php	$hidden_arr = array( 'action' => 'add', 'sub_action' => '');
		if (strlen($this->prefix)>0){
			$arr2= array();
			foreach($hidden_arr as $key => $val)
				$arr2[$this->prefix.$key] = $val;
			$hidden_arr = $arr2;
		}

	$this->gen_PostParams($hidden_arr,true); 
	?>
	<table class="addForm" cellspacing="2">
	<thead><tr><td class="field">&nbsp;</td><td class="value">&nbsp;</td></tr>
	</thead>
	<tbody>
	<?php
		foreach($this->model as $fld)
			if ($fld && $fld->does_add){
		?><tr><td class="field"><?php
				$fld->RenderAddTitle($this);
		?></td><td class="value"><?php
				$fld->DispAdd($this);
		?></td></tr>
		<?php
			}
	?>
	<tr class="confirm"><td colspan=2 align="right">
	<button type=submit>
	<?= str_params(_("Create this %1"),array($this->model_name_s),1) ?>
	<img src="./Images/icon_arrow_orange.png" ></input>
	<td>
	</tr>
	</tbody>
	</table> </form>
	<?php

//eof
?>