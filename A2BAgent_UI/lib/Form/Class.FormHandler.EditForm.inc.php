
<script language="JavaScript" src="./javascript/calonlydays.js"></script>
<script language="JavaScript" type="text/JavaScript">
<!--
function MM_openBrWindow(theURL,winName,features) { //v2.0
  window.open(theURL,winName,features);
}

function sendto(action, record, field_inst, instance){
  document.myForm.form_action.value = action;
  document.myForm.sub_action.value = record;
  if (field_inst != null) document.myForm.elements[field_inst].value = instance;
  myForm.submit();
}

function sendtolittle(direction){
  myForm.action=direction;
  myForm.submit();

}

//-->
</script>


<table width="95%" border="0" cellpadding="3" cellspacing="2" bgcolor="#EAEAEA" align="center">
			
	<FORM action=<?= $_SERVER['PHP_SELF']?> method=post name="myForm" id="myForm"> 
		<INPUT type="hidden" name="id" value="<?= $id?>">
		<INPUT type="hidden" name="form_action" value="edit">
		<INPUT type="hidden" name="sub_action" value="">
		<INPUT type="hidden" name="atmenu" value="<?= $atmenu?>">
		<INPUT type="hidden" name="stitle" value="<?= $stitle?>">	
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
		
		
		if (strlen($this->FG_TABLE_EDITION[$i][16])>1){
			echo '<TR><TD width="%25" valign="top" bgcolor="#FEFEEE" colspan="2" class="tableBodyRight" ><i>';
			echo $this->FG_TABLE_EDITION[$i][16];
			echo '</i></TD></TR>';
		}
		
		if (!$pos){
			
?>
                    <TR> 
		
		<?php if (!$this-> FG_fit_expression[$i]  &&  isset($this-> FG_fit_expression[$i]) ){ ?>
			<TD width="%25" valign="middle" class="form_head_red"> 		<?= $this->FG_TABLE_EDITION[$i][0]?> 		</TD>  
		  	<TD width="%75" valign="top" class="tableBodyRight" background="<?= Images_Path;?>/background_cells_red.gif" class="text">
        <?php }else{ ?>
			<TD width="%25" valign="middle" class="form_head"> 		<?= $this->FG_TABLE_EDITION[$i][0]?> 		</TD>  
			<TD width="%75" valign="top" class="tableBodyRight" background="<?= Images_Path;?>/background_cells.gif" class="text">
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
					}else{ echo $this->FG_TABLE_ADITION[$i][2]; }?>">
                                	<a href="#" onclick="window.open('<?= $this->FG_TABLE_EDITION[$i][12]?>popup_formname=myForm&popup_fieldname=<?= $this->FG_TABLE_EDITION[$i][1]?>' <?= $this->FG_TABLE_EDITION[$i][7]?>);"><img src="<?= Images_Path_Main;?>/icon_arrow_orange.gif"/></a>
			 <?php
				}elseif (strtoupper ($this -> FG_TABLE_EDITION[$i][3])==strtoupper ("POPUPVALUETIME"))
				{
                        ?>
                        <INPUT class="form_enter" name=<?= $this->FG_TABLE_EDITION[$i][1]?>  <?= $this->FG_TABLE_EDITION[$i][4]?> value="<?php if($this->VALID_SQL_REG_EXP){ echo stripslashes($list[0][$i]); }else{ echo $this->FG_TABLE_ADITION[$i][1]; }?>">
                         <a href="#" onclick="window.open('<?= $this->FG_TABLE_EDITION[$i][14]?>formname=myForm&fieldname=<?= $this->FG_TABLE_EDITION[$i][1]?>' <?= $this->FG_TABLE_EDITION[$i][14]?>);"><img src="<?= Images_Path_Main;?>/icon_arrow_orange.gif"/></a>
                        <?php
				}elseif (strtoupper ($this->FG_TABLE_EDITION[$i][3])==strtoupper ("POPUPDATETIME"))
				{
                        ?>
                         <INPUT class="form_enter" name=<?= $this->FG_TABLE_EDITION[$i][1]?>  <?= $this->FG_TABLE_EDITION[$i][4]?> value="<?php if($this->VALID_SQL_REG_EXP){ echo stripslashes($list[0][$i]); }else{ echo $this->FG_TABLE_ADITION[$i][1]; }?>">
                          <a href="javascript:cal<?= $this->FG_TABLE_EDITION[$i][1]?>.popup();"><img src="img/cal.gif" width="16" height="16" border="0" title="Click Here to Pick up the date" alt="Click Here to Pick up the date"></a>
                          <script language="JavaScript">
                         <!-- // create calendar object(s) just after form tag closed
                             // specify form element as the only parameter (document.forms['formname'].elements['inputname']);
                             // note: you can have as many calendar objects as you need for your application
                          var cal<?= $this->FG_TABLE_EDITION[$i][1]?> = new calendaronlyminutes(document.forms['myForm'].elements['<?= $this->FG_TABLE_EDITION[$i][1]?>']);
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
								
					if ($this->FG_DEBUG >= 1)	echo gettext("<br> TYPE OF SELECT :").$this->FG_TABLE_EDITION[$i][7];
					if (strtoupper ($this->FG_TABLE_EDITION[$i][7])==strtoupper ("SQL")){
						$instance_sub_table = new Table($this->FG_TABLE_EDITION[$i][8], $this->FG_TABLE_EDITION[$i][9]);
						if (FG_DEBUG >=2) $instance_sub_table->debug_st=1;
						$select_list = $instance_sub_table -> Get_list ($this->DBHandle, $this->FG_TABLE_EDITION[$i][10], null, null, null, null, null, null);
						if ($this->FG_DEBUG >= 3) { echo "<br>"; print_r($select_list);}
											
					}elseif (strtoupper ($this->FG_TABLE_EDITION[$i][7])==strtoupper ("LIST"))
					{
						$select_list = $this->FG_TABLE_EDITION[$i][11];
						if ($this->FG_DEBUG >= 3) { echo "<br>"; print_r($select_list);}
										 }
						 if ($this->FG_DEBUG >= 3) print_r ($list);			 
						 if ($this->FG_DEBUG >= 2) echo "<br>#$i<br>::>".$this->VALID_SQL_REG_EXP;
						 if ($this->FG_DEBUG >= 2) echo "<br><br>::>".$list[0][$i];
						 if ($this->FG_DEBUG >= 2) echo "<br><br>::>".$$this->FG_TABLE_ADITION[$i][1];
			  			?>
						<SELECT name='<?= $this->FG_TABLE_EDITION[$i][1]?><?php if (strpos($this->FG_TABLE_EDITION[$i][4], "multiple")) echo "[]";?>' class="form_enter" <?= $this->FG_TABLE_EDITION[$i][4]?>  class="form_enter">
                        <?php
						echo ($this->FG_TABLE_EDITION[$i][15]);
						
						if (count($select_list)>0)
						{
							$select_number=0;
							foreach ($select_list as $select_recordset){ 
								$select_number++;
								?>
								<OPTION  value=<?= $select_recordset[1]?> <?php 
									
									if($this->VALID_SQL_REG_EXP){ 
										if (strpos($this->FG_TABLE_EDITION[$i][4], "multiple")){										
											if (intval($select_recordset[1]) & intval($list[0][$i])) echo "selected"; 
										}else{
											if (strcmp($list[0][$i],$select_recordset[1])==0) echo "selected";  
										}
									}else{ 										
										if (strpos($this->FG_TABLE_EDITION[$i][4], "multiple")){
											//if (intval($select_recordset[1]) & intval($_POST[$this->FG_TABLE_EDITION[$i][1]])) echo "selected"; 
											if (is_array($_POST[$this->FG_TABLE_EDITION[$i][1]]) && (intval($select_recordset[1]) & array_sum($_POST[$this->FG_TABLE_EDITION[$i][1]]))) echo "selected"; 
										}else{
											if (strcmp($_POST[$this->FG_TABLE_EDITION[$i][1]],$select_recordset[1])==0){ echo "selected"; } 
										}
									}
									  
									// CLOSE THE <OPTION
									echo '> ';
									if ($this->FG_TABLE_EDITION[$i][12] != ""){
										$value_display = $this->FG_TABLE_EDITION[$i][12];
										$nb_recor_k = count($select_recordset);
										for ($k=1;$k<=$nb_recor_k;$k++){
											$value_display  = str_replace("%$k", $select_recordset[$k-1], $value_display );
										}
											
									}else{
										$value_display = $select_recordset[0];	
									}
									
									// DISPLAY THE VALUE
									echo $value_display;									
									?>
								</OPTION>
                          		<?php 
			  				}// END_FOREACH
						}else{
							echo gettext("No data found !!!");
						}//END_IF				
			  ?>
                        </SELECT>
                        <?php   
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
						
                               		}//END_IF (RADIOBUTTON)  
							   
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
					}else{
								
						if (strtoupper ($this->FG_TABLE_EDITION[$i][3])==strtoupper ("SELECT")){
							$table_split = split(":",$this->FG_TABLE_EDITION[$i][14]);
						
					?>
                    <TR> 
						<!-- ******************** PARTIE EXTERN : SELECT ***************** -->
                      	<TD width="122" class="form_head"><?= $this->FG_TABLE_EDITION[$i][0]?></TD>
					  	<TD align="center" valign="top" background="<?= Images_Path;?>/background_cells.gif" class="tableBodyRight">
                     		<br>
                         
						 	<!-- Table with list instance already inserted -->
                        	<table width="300" border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="#EDF3FF">
								<TR bgcolor="#ffffff"> 
								<TD height=16 style="PADDING-LEFT: 5px; PADDING-RIGHT: 3px" class="form_head"> 
								  <TABLE border=0 cellPadding=0 cellSpacing=0 width="100%">
									<TBODY>
									  <TR> 
										<TD class="form_head"><?= $this->FG_TABLE_EDITION[$i][0]?> <?= gettext("LIST ");?></TD>
									  </TR>
									</TBODY>
								  </TABLE></TD>
							  </TR>
							  <TR> 
								<TD> <TABLE border=0 cellPadding=0 cellSpacing=0 width="100%">
									<TBODY>
									  <TR> 
										<TD bgColor=#e1e1e1 colSpan=<?= $this->FG_TOTAL_TABLE_COL?> height=1><IMG height=1 src="<?= Images_Path_Main;?>/clear.gif" width=1></TD>
									  </TR>
									  <?php
								$SPLIT_CLAUSE = str_replace("%id", "$id", $table_split[4]);
	
	
								$instance_sub_table = new Table($table_split[2], $table_split[3]);
								if (FG_DEBUG >=2) $instance_sub_table->debug_st=1;
								$split_select_list = $instance_sub_table -> Get_list ($this->DBHandle, $SPLIT_CLAUSE, null, null, null, null, null, null);			
				
								if (!is_array($split_select_list)){	
									$num=0;
								}else{	
									$num = count($split_select_list);
								}
		
	if($num>0)
	{	
	for($j=0;$j<$num;$j++)
	  {
			if (is_numeric($table_split[7])){
					
					$instance_sub_sub_table = new Table($table_split[8], $table_split[9]);
					if (FG_DEBUG >=2) $instance_sub_sub_table->debug_st=1;
					
					$SUB_TABLE_SPLIT_CLAUSE = str_replace("%1", $split_select_list[$j][$table_split[7]], $table_split[11] );
					$sub_table_split_select_list = $instance_sub_sub_table -> Get_list ($this->DBHandle, $SUB_TABLE_SPLIT_CLAUSE, null, null, null, null, null, null);
					$split_select_list[$j][$table_split[7]] = $sub_table_split_select_list[0][0];
			}	
			
	?>
                                  <TR bgcolor="<?= $this->FG_TABLE_ALTERNATE_ROW_COLOR[$j%2]?>"  onMouseOver="bgColor='#C4FFD7'" onMouseOut="bgColor='<?= $this->FG_TABLE_ALTERNATE_ROW_COLOR[$j%2]?>'"> 
                                    <TD vAlign=top align="<?= $this->FG_TABLE_COL[$i][3]?>" class=tableBody> 
                                      <font face="Verdana" size="2">
                                      <b><?= $split_select_list[$j][$table_split[7]]?></b> : <?= $split_select_list[$j][0]?>
                                      </font> </TD>
                                    <TD align="center" vAlign=top class=tableBodyRight> 
                                      <input onClick="sendto('del-content','<?= $i?>','<?= $table_split[1]?>','<?= $split_select_list[$j][1]?>');" title="Remove this <?= $this->FG_TABLE_EDITION[$i][0]?>" alt="Remove this <?= $this->FG_TABLE_EDITION[$i][0]?>" border=0 height=11 hspace=2 id=submit33 name=submit33 src="<?= Images_Path_Main;?>/icon-del.gif" type=image width=33 value="add-split">
                                    </TD>
                                  </TR>
                                  <?php 
	  }//end_for
	}else{
			?>
                                  <TR bgcolor="<?= $this->FG_TABLE_ALTERNATE_ROW_COLOR[$j%2]?>"  onMouseOver="bgColor='#C4FFD7'" onMouseOut="bgColor='<?= $this->FG_TABLE_ALTERNATE_ROW_COLOR[$j%2]?>'"> 
                                    <TD colspan="2" align="<?= $this->FG_TABLE_COL[$i][3]?>" vAlign=top class=tableBody> 
                                      <div align="center" class="liens"><?= gettext("No");?><?= $this->FG_TABLE_EDITION[$i][0]?></div></TD>
                                  </TR>
                                  <?php 
	}
	?>
                                  <TR> 
                                    <TD class=tableDivider colSpan=<?= $this->FG_TOTAL_TABLE_COL?>><IMG height=1 src="<?= Images_Path_Main;?>/clear.gif" width=1></TD>
                                  </TR>
                                </TBODY>
                              </TABLE></td>
                          </tr>
                          <TR bgcolor="#ffffff"> 
                            <TD bgcolor="#AAAAAA"  height=16 style="PADDING-LEFT: 5px; PADDING-RIGHT: 3px"> 
                              <TABLE border=0 cellPadding=0 cellSpacing=0 width="100%">                                
                                  <TR> 
                                    <TD height="4" align="right"></TD>
                                </TR>
                              </TABLE>
							</TD>
                          </TR>
                        </table><br>
						</TD>
                    </TR>
                    <TR>
					  <!-- *******************   Select to ADD new instances  ****************************** -->					  					  
                      <TD class="form_head">&nbsp;</TD>
					  <TD align="center" valign="top" background="<?= Images_Path;?>/background_cells.gif" class="tableBodyRight">
                      <br>
                        <TABLE width="300" height=50 border=0 align="center" cellPadding=0 cellSpacing=0>
<TBODY>
                            <TR> 
                              	<TD bgColor=#7f99cc colSpan=3 height=16 style="PADDING-LEFT: 5px; PADDING-RIGHT: 5px" class="form_head">
									<TABLE border=0 cellPadding=0 cellSpacing=0 width="100%">                                
										<TR> 
										  <TD class="form_head"><?= gettext("Add a new ");?><?= $this->FG_TABLE_EDITION[$i][0]?></TD>
										</TR>
									</TABLE>
								</TD>
                            </TR>
                            <TR>
                              <TD class="form_head"> <IMG height=1 src="<?= Images_Path_Main;?>/clear.gif" width=1>
                              </TD>
                              <TD bgColor=#F3F3F3 style="PADDING-BOTTOM: 7px; PADDING-LEFT: 5px; PADDING-RIGHT: 5px; PADDING-TOP: 5px">

								<TABLE width="97%" border=0 align="center" cellPadding=0 cellSpacing=0>
                                    <TR>
                                      <TD width="40%" class="tableBody"><?= $this->FG_TABLE_EDITION[$i][0]?></TD>
                                      <TD width="60%"><div align="center">
                                          <SELECT name=<?= $table_split[1]?> class="form_enter">
                                            <?php
					 $split_select_list = $instance_sub_table -> Get_list ($this->DBHandle, null, null, null, null, null, null, null);

					 if (count($split_select_list)>0)
					 {
						 $select_number=0;
						 foreach ($split_select_list as $select_recordset){
							 $select_number++;
							 if ($table_split[6]!="" && !is_null($table_split[6])){
							 	if (is_numeric($table_split[7])){
									$instance_sub_sub_table = new Table($table_split[8], $table_split[9]);
									if (FG_DEBUG >=2) $instance_sub_sub_table->debug_st=1;
									$SUB_TABLE_SPLIT_CLAUSE = str_replace("%1", $select_recordset[$table_split[7]], $table_split[11] );
									$sub_table_split_select_list = $instance_sub_sub_table -> Get_list ($this->DBHandle, $SUB_TABLE_SPLIT_CLAUSE, null, null, null, null, null, null);
									$select_recordset[$table_split[7]] = $sub_table_split_select_list[0][0];
								}
								 $value_display = $table_split[6];
								 $nb_recor_k = count($select_recordset);
								 for ($k=1;$k<=$nb_recor_k;$k++){
									$value_display  = str_replace("%$k", $select_recordset[$k-1], $value_display );
								 }
							 }else{
							 	$value_display  = $select_recordset[0];
							 }

			  ?>
                                            <OPTION  value=<?= $select_recordset[1]?>>
                                            <?= $value_display?>
                                            </OPTION>
                                            <?php
						 }// END_FOREACH
					  }else{
						echo gettext("No data found !!!");
					  }//END_IF
							  ?>
                                          </SELECT>
                                        </div>
										</TD>
                                    </TR>
									<TR>
                                      <TD colSpan=2 height=4></TD>
                                    </TR>
                                    <TR>
                                      <TD colspan="2" align="center" vAlign="middle">
										<input onClick="sendto('add-content','<?= $i?>');" title="<?= gettext("add new a ");?><?= $this->FG_TABLE_EDITION[$i][0]?>" alt="<?= gettext("add new a ");?><?= $this->FG_TABLE_EDITION[$i][0]?>" border=0 height=20 hspace=2 id=submit32 name=submit3 src="<?= Images_Path_Main;?>/btn_Add_94x20.gif" type=image width=94 value="add-split">
                                      </TD>
                                    </TR>
                                </TABLE>
							</TD>
                            <TD class="form_head"><IMG height=1 src="<?= Images_Path_Main;?>/clear.gif" width=1></TD>
                            </TR>
                            <TR>
                              <TD colSpan=3 class="form_head"><IMG height=1 src="<?= Images_Path_Main;?>/clear.gif" width=1></TD>
                            </TR>

                        </TABLE>

                        </TD>
                    </TR>

					<?php }elseif (strtoupper ($this->FG_TABLE_EDITION[$i][3])==strtoupper ("INSERT")){
							$table_split = split(":",$this->FG_TABLE_EDITION[$i][14]);
					?>
					<TR>
					  <!-- ******************** PARTIE EXTERN : INSERT ***************** -->

                      	<TD width="122" class="form_head"><?= $this->FG_TABLE_EDITION[$i][0]?></TD>

                      	<TD align="center" valign="top" background="<?= Images_Path;?>/background_cells.gif" class="text"><br>


                        <!-- Table with list instance already inserted -->
                        <table width="300" border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="#EDF3FF">
                          <TR bgcolor="#ffffff">
                            <TD height=16 style="PADDING-LEFT: 5px; PADDING-RIGHT: 3px" class="form_head">
                            	<TABLE border=0 cellPadding=0 cellSpacing=0 width="100%">
                                	<TR>
                                		<TD class="form_head"><?= $this->FG_TABLE_EDITION[$i][0]?><?= gettext("LIST");?> </TD>
                                	</TR>
                            	</TABLE>
							</TD>
                          </TR>
                          <TR>
                            <TD>
								<TABLE border=0 cellPadding=0 cellSpacing=0 width="100%">
                                <TR>
                                	<TD bgColor=#e1e1e1 colSpan=<?= $this->FG_TOTAL_TABLE_COL?> height=1><IMG height=1 src="<?= Images_Path_Main;?>/clear.gif" width=1></TD>
                                </TR>
                                <?php
			$SPLIT_CLAUSE = str_replace("%id", "$id", $table_split[4]);

			$instance_sub_table = new Table($table_split[2], $table_split[3]);
			if (FG_DEBUG >=2) $instance_sub_table->debug_st=1;
			$split_select_list = $instance_sub_table -> Get_list ($this->DBHandle, $SPLIT_CLAUSE, null, null, null, null, null, null);
	if (!is_array($split_select_list)){
		$num=0;
	}else{
		$num = count($split_select_list);
	}



	if($num>0)
	{
	for($j=0;$j<$num;$j++)
	  {

	?>
                                  <TR bgcolor="<?= $this->FG_TABLE_ALTERNATE_ROW_COLOR[$j%2]?>"  onMouseOver="bgColor='#C4FFD7'" onMouseOut="bgColor='<?= $this->FG_TABLE_ALTERNATE_ROW_COLOR[$j%2]?>'">
                                    <TD vAlign="top" align="<?= $this->FG_TABLE_COL[$i][3]?>" class="tableBody">
                                      <font face="Verdana" size="2">
                                      <b><?= $split_select_list[$j][$table_split[7]]?></b> : <?= $split_select_list[$j][0]?>
                                      </font> </TD>
                                    <TD align="center" vAlign="top2" class="tableBodyRight">
                                      <input onClick="sendto('del-content','<?= $i?>','<?= $table_split[1]?>','<?= $split_select_list[$j][1]?>');" alt="Remove this <?= $this->FG_TABLE_EDITION[$i][0]?>" border=0 height=11 hspace=2 id=submit33 name=submit33 src="<?= Images_Path_Main;?>/icon-del.gif" type=image width=33 value="add-split">
                                    </TD>
                                  </TR>
                                  <?php
	  }//end_for
	}else{
			?>
                                  <TR bgcolor="<?= $this->FG_TABLE_ALTERNATE_ROW_COLOR[$j%2]?>"  onMouseOver="bgColor='#C4FFD7'" onMouseOut="bgColor='<?= $this->FG_TABLE_ALTERNATE_ROW_COLOR[$j%2]?>'">
                                    <TD colspan="2" align="<?= $this->FG_TABLE_COL[$i][3]?>" vAlign="top" class="tableBody">
                                      <div align="center" class="liens">No <?= $this->FG_TABLE_EDITION[$i][0]?></div></TD>
                                  </TR>
                                  <?php
	}
	?>
                                  <TR> 
                                    <TD class="tableDivider" colSpan=<?= $this->FG_TOTAL_TABLE_COL?>><IMG height=1 src="<?= Images_Path_Main;?>/clear.gif" width=1></TD>
                                  </TR>
                              </TABLE></td>
                          </tr>
                          <TR bgcolor="#ffffff"> 
                            <TD bgcolor="#AAAAAA"  height=16 style="PADDING-LEFT: 5px; PADDING-RIGHT: 3px"> 
                            	<TABLE border=0 cellPadding=0 cellSpacing=0 width="100%">
                                	<TR><TD height="4" align="right"></TD></TR>
                              	</TABLE>
							</TD>
                          </TR>
                        </table><br>
</TD>
                    </TR>
                    <TR>
					  <!-- *******************   Select to ADD new instances  ****************************** -->					  
                      <TD class="form_head">&nbsp;</TD>
                      <TD align="center" valign="top" background="<?= Images_Path;?>/background_cells.gif" class="text"><br>
                        <TABLE width="300" height=50 border=0 align="center" cellPadding=0 cellSpacing=0>
                            <TR> 
                            	<TD bgColor=#7f99cc colSpan=3 height=16 style="PADDING-LEFT: 5px; PADDING-RIGHT: 5px" class="form_head">
									<TABLE border=0 cellPadding=0 cellSpacing=0 width="100%">
										<TR> 
											<TD class="form_head"><?= gettext("Add a new");?> <?= $this->FG_TABLE_EDITION[$i][0]?></TD>
										</TR>
									</TABLE>
								</TD>
                            </TR>
							
                            <TR> 
								<TD class="form_head"> <IMG height=1 src="<?= Images_Path_Main;?>/clear.gif" width=1>
								</TD>
								<TD bgColor=#F3F3F3 style="PADDING-BOTTOM: 7px; PADDING-LEFT: 5px; PADDING-RIGHT: 5px; PADDING-TOP: 5px"> 
                                
								<TABLE width="97%" border=0 align="center" cellPadding=0 cellSpacing=0>
									<TR> 
										<TD width="122" class="tableBody"><?= $this->FG_TABLE_EDITION[$i][0]?></TD>
										<TD width="516"><div align="center"> 				
											<INPUT TYPE="TEXT" name=<?= $table_split[1]?> class="form_enter"  size="20" maxlength="20">
										</TD>
                                    </TR>                                    
                                    <TR> 
										<TD colspan="2" align="center">									  	
											<input onClick="sendto('add-content','<?= $i?>');" alt="add new a <?= $this->FG_TABLE_EDITION[$i][0]?>" border=0 height=20 hspace=2 id=submit32 name=submit3 src="<?= Images_Path_Main;?>/btn_Add_94x20.gif" type=image width=94 value="add-split">
										</TD>
                                    </TR>
                                    <TR> 
                                      <TD colSpan=2 height=4></TD>
                                    </TR>
                                    <TR> 
                                      <TD colSpan=2> <div align="right"></div></TD>
                                    </TR>
                                </TABLE>
								</TD>
								<TD class="form_head"><IMG height=1 src="<?= Images_Path_Main;?>/clear.gif" width=1>
								</TD>
                            </TR>
                            <TR> 
                              <TD colSpan=3 class="form_head"><IMG height=1 src="<?= Images_Path_Main;?>/clear.gif" width=1></TD>
                            </TR>
                        </TABLE>
                        <br></TD>
                    </TR>					
					<?php  }elseif (strtoupper ($this->FG_TABLE_EDITION[$i][3])==strtoupper ("CHECKBOX")){
							
							$table_split = split(":",$this->FG_TABLE_EDITION[$i][14]);
					?>
					<TR> 
					 <!-- ******************** PARTIE EXTERN : CHECKBOX ***************** -->
                     
 					 <td width="206" height="42" valign="top" bgcolor="#e2e2d3">
					 	<table width="100%" border="0" cellpadding="2" cellspacing="0" class="form_text">
                   		<tr>
                        	<td width="122"><?= $this->FG_TABLE_EDITION[$i][0]?></td>
                        </tr>
						</table>
					</td>
					<td width="400" valign="top" background="<?= Images_Path;?>/background_cells.gif" class="text">
					    
	<?php 
	$SPLIT_CLAUSE = str_replace("%id", "$id", $table_split[4]);
	


	$instance_sub_table = new Table($table_split[2], $table_split[3]);
	if (FG_DEBUG >=2) $instance_sub_table->debug_st=1;
	$split_select_list = $instance_sub_table -> Get_list ($this->DBHandle, $SPLIT_CLAUSE, null, null, null, null, null, null);			
	if (!is_array($split_select_list)){	
		$num=0;
	}else{	
		$num = count($split_select_list);
	}
	
	 ////////////////////////////////////////////////////////////////////////////////////////////////////////

	 $table_split[12] = str_replace("%id", "$id", $table_split[12]);
	 $split_select_list_tariff = $instance_sub_table -> Get_list ($this->DBHandle, $table_split[12], null, null, null, null, null, null);
	 if (count($split_select_list_tariff)>0)
	 {
			 $select_number=0;
			  ?>				
			  <TABLE width="400" height=50 border=0 align="center" cellPadding=0 cellSpacing=0>
				<TR> 
                	<TD colSpan=3 height=16 style="PADDING-LEFT: 5px; PADDING-RIGHT: 5px">
						<table border="0" cellpadding="0" cellspacing="0" width="100%">
							<tr>
								<td bgcolor="#e2e2d3" class="textnegrita"><font color="#000000"> <?= $this->FG_TABLE_COMMENT[$i]?></font></td>
							</tr>
                        </table>
					</TD>
				</TR>
                <TR> 
                	<TD class="form_head"> <IMG height=1 src="<?= Images_Path_Main;?>/clear.gif" width=1>
                    </TD>
                    <TD bgColor=#F3F3F3 style="PADDING-BOTTOM: 7px; PADDING-LEFT: 5px; PADDING-RIGHT: 5px; PADDING-TOP: 5px"> 
						<TABLE width="97%" border=0 align="center" cellPadding=0 cellSpacing=0>
                        
 <?php 
 	foreach ($split_select_list_tariff as $select_recordset){ 
				 $select_number++;
				 
				 if ($table_split[6]!="" && !is_null($table_split[6])){
				 
						if (is_numeric($table_split[7])){
							$instance_sub_sub_table = new Table($table_split[8], $table_split[9]);
							$SUB_TABLE_SPLIT_CLAUSE = str_replace("%1", $select_recordset[$table_split[7]], $table_split[11] );
							$sub_table_split_select_list_tariff = $instance_sub_sub_table -> Get_list ($this->DBHandle, $SUB_TABLE_SPLIT_CLAUSE, null, null, null, null, null, null);
							$select_recordset[$table_split[7]] = $sub_table_split_select_list_tariff[0][0];
						}													 
						 $value_display = $table_split[6];
						 $nb_recor_k = count($select_recordset);
						 for ($k=1;$k<=$nb_recor_k;$k++){
							$value_display  = str_replace("%$k", $select_recordset[$k-1], $value_display );
						 }
				 }else{													 	
					$value_display  = $select_recordset[0];
				 }
				 
				 
				 $checked_tariff=false;
				 if($num>0)
				 {
					for($j=0;$j<$num;$j++)
					{
						if ($select_recordset[1]==$split_select_list[$j][1]) $checked_tariff=true;
					}
				 }

?>
			<TR>
				<TD class="tableBody"><input type="checkbox" name="<?= $table_split[0]?>[]" value="<?= $select_recordset[1]?>" <?php if ($checked_tariff) echo"checked";?>></TD>
				<TD class="text_azul">&nbsp; <?= $value_display?></TD>
			</TR>
<?php }// END_FOREACH?>
                         <TR><TD colSpan=2 height=4>
				<span class="liens">
					<?php
				if (!$this-> FG_fit_expression[$i]  &&  isset($this-> FG_fit_expression[$i]) ){
					echo "<br>".$this->FG_TABLE_EDITION[$i][6];
				}
		  ?>
					</span>
				</TD></TR>
                                </TABLE></TD>
                              <TD class="form_head"><IMG height=1 src="<?= Images_Path_Main;?>/clear.gif" width=1>
                              </TD>
                            </TR>
                            <TR>
                              <TD colSpan=3 class="form_head"><IMG height=1 src="<?= Images_Path_Main;?>/clear.gif" width=1></TD>
                            </TR>
                        </TABLE>

			  <?php
	  		}else{
				echo gettext("No data found !!!");
	  }?>

					 </TD>
                    </TR>
                    <?php   	  }// end if if (strtoupper ($this->FG_TABLE_EDITION[$i][3])==strtoupper ("SELECT"))
							}// end if pos
			}//END_FOR ?>

                </FORM>
              </TABLE>
			  <table width="95%" height="70" cellpadding="3" cellspacing="0" bgcolor="#FFFFFF" align="center">
					<tr height="2">
                      <td colspan="2" style="border-bottom: medium dotted rgb(102, 119, 102);"> &nbsp;</td>

                    </tr>
                    <tr>
                      <td width="434" class="text_azul"><span class="tableBodyRight"><?= $this->FG_BUTTON_EDITION_BOTTOM_TEXT?></span></td>
                      <td width="190" align="right" class="text"><input onClick="sendto('edit');"  border=0 hspace=0 id=submit3 name=submit32 src="<?= $this->FG_BUTTON_EDITION_SRC?>" type=image value="add-split"></td>
                    </tr>
               </table>