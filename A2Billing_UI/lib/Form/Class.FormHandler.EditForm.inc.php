
<script language="JavaScript" src="./javascript/calendar3.js"></script>
<script language="JavaScript" type="text/JavaScript">
<!--
function MM_openBrWindow(theURL,winName,features) { //v2.0
  window.open(theURL,winName,features);
}

function sendto(action, record, field_inst, instance){
  document.myForm.form_action.value = action;
  if (record != null) document.myForm.sub_action.value = record;
  if (field_inst != null) document.myForm.elements[field_inst].value = instance;
  myForm.submit();
}

function sendtolittle(direction){
  myForm.action=direction;
  myForm.submit();

}

//-->
</script>


<table class="editform_table1" cellspacing="2">
			
	<FORM action=<?= $_SERVER['PHP_SELF']?> method=post name="myForm" id="myForm">
		<?php $this->gen_PostParams(array( form_action => 'edit', sub_action => '',
			$this->FG_TABLE_ID => ${$this->FG_TABLE_ID} ),true); ?>
<?php
	if (!is_null($this->FG_QUERY_EDITION_HIDDEN_FIELDS) && $this->FG_QUERY_EDITION_HIDDEN_FIELDS!=""){
		$split_hidden_fields = split(",",trim($this->FG_QUERY_EDITION_HIDDEN_FIELDS));
		$split_hidden_fields_value = split(",",trim($this->FG_QUERY_EDITION_HIDDEN_VALUE));
		
		for ($cur_hidden=0;$cur_hidden<count($split_hidden_fields);$cur_hidden++){
			echo "<INPUT type=\"hidden\" name=\"".trim($split_hidden_fields[$cur_hidden])."\" value=\"".trim($split_hidden_fields_value[$cur_hidden])."\">\n";
		}
	}
?> 
            <TBODY>
<?php 
	for($i=0;$i<$this->FG_NB_TABLE_EDITION;$i++){ 
		$pos = strpos($this->FG_TABLE_EDITION[$i][14], ":"); // SQL CUSTOM QUERY
		
		if ($pos !==false) {
			if ($this->FG_DEBUG) { ?>
			<tr><td style="color: red;" colspan=2>SQL Custom query, skipping! </td></tr>
			<?php }
			continue;
		}
		
		if (strlen($this->FG_TABLE_EDITION[$i][16])>1){
			echo '<TR><TD width="%25" valign="top" bgcolor="#FEFEEE" colspan="2" class="tableBodyRight" ><i>';
			echo $this->FG_TABLE_EDITION[$i][16];
			echo '</i></TD></TR>';
		}
		
		if (!$pos){
			
?>
                    <tr>
		
		<?php if (!$this-> FG_fit_expression[$i]  &&  isset($this-> FG_fit_expression[$i]) ){ ?>
			<TD width="%25" valign="middle" class="form_head_red"> 		<?= $this->FG_TABLE_EDITION[$i][0]?> 		</TD>  
		  	<TD width="%75" valign="top" class="tableBodyRight" background="../Images/background_cells_red.png" class="text">
        <?php }else{ ?>
			<TD width="%25" valign="middle" class="form_head"> 		<?= $this->FG_TABLE_EDITION[$i][0]?> 		</TD>  
			<TD width="%75" valign="top" class="tableBodyRight" background="../Images/background_cells.png" class="text">
		<?php } ?>
                        <?php 
			if ($this->FG_DEBUG >= 1) print($this->FG_TABLE_EDITION[$i][3]);
		  		if (strtoupper ($this->FG_TABLE_EDITION[$i][3])==strtoupper ("INPUT"))
				{
					if (isset ($this->FG_TABLE_EDITION[$i][15]) && strlen($this->FG_TABLE_EDITION[$i][15])>1){				
						$list[0][$i] = call_user_func($this->FG_TABLE_EDITION[$i][15], $list[0][$i]);
					}
			  ?>
                        <INPUT class="form_enter" name=<?= $this->FG_TABLE_EDITION[$i][1]?>  <?= $this->FG_TABLE_EDITION[$i][4]?> value="<?php if($this->VALID_SQL_REG_EXP){ echo stripslashes($list[0][$i]); }else{ echo $_POST[$this->FG_TABLE_ADITION[$i][1]];  }?>"> 
                        <?php 
				}elseif (strtoupper ($this->FG_TABLE_EDITION[$i][3])==strtoupper ("POPUPVALUE")){
			?>
				<INPUT class="form_enter" name=<?= $this->FG_TABLE_EDITION[$i][1]?>  <?= $this->FG_TABLE_EDITION[$i][4]?> value="<?
					if($this->VALID_SQL_REG_EXP){ 
						echo stripslashes($list[0][$i]);
					}else{ echo $_POST[$this->FG_TABLE_ADITION[$i][1]]; }?>">
                                	<a href="#" onclick="window.open('<?= $this->FG_TABLE_EDITION[$i][12]?>popup_formname=myForm&popup_fieldname=<?= $this->FG_TABLE_EDITION[$i][1]?>' <?= $this->FG_TABLE_EDITION[$i][7]?>);"><img src="../Images/icon_arrow_orange.png"/></a>
			 <?php
				}elseif (strtoupper ($this -> FG_TABLE_EDITION[$i][3])==strtoupper ("POPUPVALUETIME"))
				{
                        ?>
                        <INPUT class="form_enter" name=<?php echo $this->FG_TABLE_EDITION[$i][1]?>  <?php echo $this->FG_TABLE_EDITION[$i][4]?> value="<?php if($this->VALID_SQL_REG_EXP){ echo stripslashes($list[0][$i]); }else{ echo $_POST[$this->FG_TABLE_ADITION[$i][1]]; }?>">
                         <a href="#" onclick="window.open('<?= $this->FG_TABLE_EDITION[$i][14]?>formname=myForm&fieldname=<?= $this->FG_TABLE_EDITION[$i][1]?>' <?= $this->FG_TABLE_EDITION[$i][14]?>);"><img src="../Images/icon_arrow_orange.png"/></a>
                        <?php
				}elseif (strtoupper ($this->FG_TABLE_EDITION[$i][3])==strtoupper ("POPUPDATETIME"))
				{
                        ?>
                         <INPUT class="form_enter" name=<?php echo $this->FG_TABLE_EDITION[$i][1]?>  <?php echo $this->FG_TABLE_EDITION[$i][4]?> value="<?php if($this->VALID_SQL_REG_EXP){ echo stripslashes($list[0][$i]); }else{ echo $_POST[$this->FG_TABLE_ADITION[$i][1]]; }?>">
                          <a href="javascript:cal<?= $this->FG_TABLE_EDITION[$i][1]?>.popup();"><img src="img/cal.gif" width="16" height="16" border="0" title="Click Here to Pick up the date" alt="Click Here to Pick up the date"></a>
                          <script language="JavaScript">
                         <!-- // create calendar object(s) just after form tag closed
                             // specify form element as the only parameter (document.forms['formname'].elements['inputname']);
                             // note: you can have as many calendar objects as you need for your application
                          var cal<?= $this->FG_TABLE_EDITION[$i][1]?> = new calendar3(document.forms['myForm'].elements['<?= $this->FG_TABLE_EDITION[$i][1]?>']);
                          cal<?= $this->FG_TABLE_EDITION[$i][1]?>.year_scroll = false;
                          cal<?= $this->FG_TABLE_EDITION[$i][1]?>.time_comp = true;
                          cal<?= $this->FG_TABLE_EDITION[$i][1]?>.formatpgsql = true;
                          //-->
                          </script>
			<?php	
		  		}elseif (strtoupper ($this->FG_TABLE_EDITION[$i][3])==strtoupper ("TEXTAREA"))
				{
			  ?>
                     <textarea class="form_enter" name=<?= $this->FG_TABLE_EDITION[$i][1]?>  <?= $this->FG_TABLE_EDITION[$i][4]?>><?php if($this->VALID_SQL_REG_EXP){ echo stripslashes($list[0][$i]); }else{ echo $_POST[$this->FG_TABLE_ADITION[$i][1]];  }?></textarea> 
                        <?php 	
				}elseif (strtoupper ($this->FG_TABLE_EDITION[$i][3])==strtoupper ("SELECT"))
				{
					
					if ($this->FG_DEBUG >= 1)
						echo "<br> TYPE OF SELECT :".$this->FG_TABLE_EDITION[$i][7]."<br>";
					$tmp_value=NULL;
					if (strtoupper ($this->FG_TABLE_EDITION[$i][7])==strtoupper ("SQL")){
						$instance_sub_table = new Table($this->FG_TABLE_EDITION[$i][8], $this->FG_TABLE_EDITION[$i][9]);
						if ($this-> FG_DEBUG >=2) 
							$instance_sub_table->debug_st=1;
						$select_list = $instance_sub_table -> Get_list ($this->DBHandle, $this->FG_TABLE_EDITION[$i][10], null, null, null, null, null, null);
						if ($this->FG_DEBUG >= 3) { 
							echo "<br> sql_select_list:";
							print_r($select_list);
						}
					}elseif (strtoupper ($this->FG_TABLE_EDITION[$i][7])==strtoupper ("LIST"))
					{
						$select_list = $this->FG_TABLE_EDITION[$i][11];
						if ($this->FG_DEBUG >= 3) {
							echo "<br>select-list:"; print_r($select_list);
						}
					}
					$tmp_multiple=false;
					$tmp_value=$list[0][$i];
					if(strpos($this->FG_TABLE_EDITION[$i][4], "label-first")!==false){
						// array is ('label','id') instead of (id,label)
						$tmp2 = array();
						foreach($select_list as $tmp)
							$tmp2[]=array($tmp[1],$tmp[0]);
						$select_list = $tmp2;
					}
					if (!is_array($select_list))
						$select_list = array();
					if (isset($this->FG_TABLE_EDITION[$i][15]))
						array_unshift($select_list,$this->FG_TABLE_EDITION[$i][15]);
					
					
					if(strpos($this->FG_TABLE_EDITION[$i][4], "multiple")!==false){
						$tmp_multiple=true;
						if ($this->FG_DEBUG >= 3)
							echo "Multiple<br>\n";
						if (strpos($this->FG_TABLE_EDITION[$i][4], "bitfield")!==false){
							//decode bitfield into values
							$tmp_int = (integer)$tmp_value;
							$tmp_value= array();
							$tmp_i = 1;
							for($tmp_i=1;($tmp_i!=0) && ($tmp_int!=0);$tmp_i*=2){
								if ($tmp_int & $tmp_i){
									$tmp_value[] = $tmp_i;
									$tmp_int -= $tmp_i;
								}
							}
						}elseif (strpos($this->FG_TABLE_EDITION[$i][4], "sql")!==false) {
							// decode SQL list into values
							$tmp_value=sql_decodeArray($tmp_value);
							
						} // else how to decode this?
					}
					if ($this->FG_TABLE_EDITION[$i][12] != ""){
						// replace expression into Option display
						foreach($select_list as $tmp_disp)
							$tmp_disp[1]=str_params($this->FG_TABLE_EDITION[$i][12],$tmp_disp,1);
					}
					
					if ($this->FG_DEBUG >= 3) {
						echo "list: ";
						print_r ($list);
						echo "<br>\n";
					}
					if ($this->FG_DEBUG >= 2){ ?>
						<br>
						#<?= $i?> <br> 
						SQL-REGEXP: <?= $this->VALID_SQL_REG_EXP ?><br>
						list[0]: <?= $list[0][$i] ?><br>
						fieldname: <?= $this->FG_TABLE_ADITION[$i][1] ?> <br>
						tmp_value: <?php var_dump($tmp_value); ?><br>
					<?php
					}
						//now, build the combo automatically!
					gen_Combo($this->FG_TABLE_EDITION[$i][1],$tmp_value,$select_list,$tmp_multiple);


				}elseif (strtoupper ($this->FG_TABLE_EDITION[$i][3])==strtoupper ("RADIOBUTTON")){
					$radio_table = split(",",trim($this->FG_TABLE_EDITION[$i][10]));
					foreach ($radio_table as $radio_instance){
						$radio_composant = split(":",$radio_instance);
						echo $radio_composant[0];
						echo ' <input class="form_enter" type="radio" name="'.$this->FG_TABLE_EDITION[$i][1].'" value="'.$radio_composant[1].'" ';
						if($this->VALID_SQL_REG_EXP){ 
							$know_is_checked = stripslashes($list[0][$i]); 
						}else{ 
							$know_is_checked = $_POST[$this->FG_TABLE_EDITION[$i][1]];  
						}
												
						if ($know_is_checked==$radio_composant[1]){
							echo "checked";
						}
						echo ">";
												
					}								
						//  Yes <input type="radio" name="digitalized" value="t" checked>
						//  No<input type="radio" name="digitalized" value="f">
						
				}elseif (strtoupper ($this->FG_TABLE_EDITION[$i][3])==strtoupper ("OBJECT"))
				{
					$this->FG_TABLE_EDITION[$i][4]->DispEdit($i,$this->FG_TABLE_EDITION[$i],$list[0][$i], $this->DBHandle);
				}
		  ?>
                        <span class="liens">
                        <?php 						
					if (!$this-> FG_fit_expression[$i]  &&  isset($this-> FG_fit_expression[$i]) ){
						echo "<br>".$this->FG_TABLE_EDITION[$i][6]." - ".$this->FG_regular[$this->FG_TABLE_EDITION[$i][5]][1];
					}
			
			  ?>
                        </span>
			<?php  
					if (strlen($this->FG_TABLE_COMMENT[$i])>0){  ?><?php  echo "<br/>".$this->FG_TABLE_COMMENT[$i];?>  <?php  } ?>
                        &nbsp; </TD>
                    </TR>
                    <?php
		}// end if pos
	}//END_FOR ?>

                </FORM>
              </TABLE>
	  <TABLE cellspacing="0" class="editform_table8">
		<tr>
		 <td colspan="2" style="border-bottom: medium dotted rgb(102, 119, 102);">&nbsp; </td>
		</tr>
		<tr>
			<td width="50%" class="text_azul"><span class="tableBodyRight"><?php echo $this->FG_BUTTON_EDITION_BOTTOM_TEXT?></span></td>
			<td width="50%" align="right" class="text">
			
				<a href="#" onClick="sendto('edit');" class="cssbutton_big"><IMG src="../Images/icon_arrow_orange.png">
				<?php echo gettext("CONFIRM DATA"); ?> </a>
				
			</td>
		</tr>
	  </TABLE>
