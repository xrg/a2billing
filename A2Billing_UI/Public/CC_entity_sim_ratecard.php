<?php
include ("../lib/defines.php");
include ("../lib/module.access.php");
//include ("./frontoffice_data/CC_var_def_ratecard.inc");
include ("../lib/Class.RateEngine.php");	


if (! has_rights (ACX_RATECARD)){ 
	   Header ("HTTP/1.0 401 Unauthorized");
	   Header ("Location: PP_error.php?c=accessdenied");	   
	   die();	   
}

getpost_ifset(array('posted', 'tariffplan', 'balance', 'id_cc_card', 'called'));


$FG_DEBUG = 0;
$DBHandle  = DbConnect();

/*
if (is_string ($tariffplan) && strlen(trim($tariffplan))>0){
		
		list($sim_mytariff_id, $sim_mytariffname) = split('-:-', $tariffplan);		
		$_SESSION["sim_mytariff_id"]= $sim_mytariff_id;
		$_SESSION["sim_mytariffname"]= $sim_mytariffname;
		//$_SESSION["basetariffgroup"]= $basetariffgroup;		
}else{
		$sim_mytariff_id = $_SESSION["sim_mytariff_id"];
		$sim_mytariffname = $_SESSION["sim_mytariffname"];
		//$basetariffgroup = $_SESSION["basetariffgroup"];	
}
if ($FG_DEBUG == 1)  echo "sim_mytariff_id:$sim_mytariff_id<br>";
if ($FG_DEBUG == 1)  echo "sim_mytariffname:$sim_mytariffname<br>";
*/


if ($called  && $id_cc_card){

		
		$calling=ereg_replace("^\+","011",$called);	
		$calling=ereg_replace("[^0-9]","",$calling);	
		$calling=ereg_replace("^01100","011",$calling);	
		$calling=ereg_replace("^00","011",$calling);	
		$calling=ereg_replace("^0111","1",$calling);
		
		if ( strlen($calling)>2 && is_numeric($calling)){
				
				$A2B -> DBHandle = DbConnect();
				$instance_table = new Table();
				$A2B -> set_instance_table ($instance_table);
				
				$resmax = $DBHandle -> query("SELECT username, tariff FROM cc_card where id='$id_cc_card'");
				$num = $resmax -> numRows();
				if ($num==0){ echo gettext("Error card !!!"); exit();}			
				
				for($i=0;$i<$num;$i++)
					{
						$row [] =$resmax -> fetchRow();	
					}
				
				$A2B -> cardnumber = $row[0][0] ;
				$A2B -> credit = $balance;
				if ($FG_DEBUG == 1) echo "cardnumber = ".$row[0][0] ."<br>";
				
				if ($A2B -> callingcard_ivr_authenticate_light ($error_msg)){
					if ($FG_DEBUG == 1) $RateEngine -> debug_st = 1;
		
					$RateEngine = new RateEngine();
					$RateEngine -> webui = 0;
					// LOOKUP RATE : FIND A RATE FOR THIS DESTINATION
					
					
					$A2B ->agiconfig['accountcode'] = $A2B -> cardnumber ;
					$A2B ->agiconfig['use_dnid']=1;
					$A2B ->agiconfig['say_timetocall']=0;						
					$A2B ->dnid = $A2B ->destination = $calling;
					
					if ($A2B->removeinterprefix) $A2B->destination = $A2B -> apply_rules ($A2B->destination);			
					
					$resfindrate = $RateEngine->rate_engine_findrates($A2B, $A2B->destination, $row[0][1]);
					if ($FG_DEBUG == 1) echo "resfindrate=$resfindrate";
					
					// IF FIND RATE
					if ($resfindrate!=0){	
						$res_all_calcultimeout = $RateEngine->rate_engine_all_calcultimeout($A2B, $A2B->credit);
						if ($FG_DEBUG == 1) print_r($RateEngine->ratecard_obj);
					}
					
				}
		}
}

/**************************************************************/

$instance_table_tariffname = new Table("cc_tariffplan", "id, tariffname");

$FG_TABLE_CLAUSE = "";

$list_tariffname = $instance_table_tariffname  -> Get_list ($DBHandle, $FG_TABLE_CLAUSE, "tariffname", "ASC", null, null, null, null);

$nb_tariffname = count($list_tariffname);


/*************************************************************/


?>

<?php
	include("PP_header.php");
?>

<script language="JavaScript" type="text/JavaScript">
<!--
function MM_openBrWindow(theURL,winName,features) { //v2.0
  window.open(theURL,winName,features);
}

function openURL(theLINK)
{
	
	// grab index number of the selected option
	selInd = document.theForm.choose_list.selectedIndex;
	if(selInd==''){alert('Please, select a tariff'); return false;}
	// get value of the selected option
	goURL = document.theForm.choose_list.options[selInd].value;
	  
	definecredit = document.theForm.definecredit.value;
	// redirect browser to the grabbed value (hopefully a URL)	  
	self.location.href = theLINK + goURL + "&definecredit="+definecredit ; //+ "&opt="+opt;
}



//-->
</script>


<?php
	echo $CC_help_sim_ratecard;
?>
<br>

<?php  if (false){ ?>
	  <center>
	  <?php  if (is_string ($sim_mytariffname)) echo "<font size=\"3\">".gettext("THE CURRENT RATECARD")." : <b>$sim_mytariffname</b></font><br><br>"; ?>
	  
	  <!-- ** ** ** ** ** Part for the research ** ** ** ** ** -->
	
	<FORM METHOD=POST ACTION="<?php echo $PHP_SELF?>?s=1&t=0&order=<?php echo $order?>&sens=<?php echo $sens?>&current_page=<?php echo $current_page?>">
	<INPUT TYPE="hidden" NAME="posted" value=1>
	<INPUT TYPE="hidden" NAME="current_page" value=0>	
		<table class="bar-status" width="75%" border="0" cellspacing="1" cellpadding="2" align="center">
			<tbody>
			<tr>
				<td align="left" valign="top" bgcolor="#000033">					
					<font face="verdana" size="1" color="#ffffff"><b>&nbsp;&nbsp;<?php echo gettext("R A T E C A R D");?></b></font>
				</td>				
				<td class="bar-search" align="left" bgcolor="#acbdee">
				<table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#acbdee"><tr>
					<td width="50%" align="center">&nbsp;&nbsp;
						<select NAME="tariffplan" size="1"  style="border: 2px outset rgb(204, 51, 0); width=250">
								<option value=''><?php echo gettext("Choose a ratecard");?></option>
							
								<?php					 
								 foreach ($list_tariffname as $recordset){ 						 
								?>
									<option class=input value='<?php  echo $recordset[0]."-:-".$recordset[1]?>' <?php if ($recordset[0]==$tariffplan) echo "selected";?>><?php echo $recordset[1]?></option>                        
								<?php 	 }
								?>
						</select>						
					</td>
					<td class="bar-search" align="center" bgcolor="#cddeff" width="50%">
					<input type="image"  name="image16" align="top" border="0" src="../Images/button-search.gif" />
	  			</td>
				
				</tr></table></td>
			</tr>							
		</tbody></table>
	</FORM>
</center>
<?php  } ?>


	  <br>
	  <table width="<?php echo $FG_HTML_TABLE_WIDTH?>" border="0" align="center" cellpadding="0" cellspacing="0">

		<TR>
          <TD style="border-bottom: medium dotted #8888CC" colspan="2"> <B><?php echo gettext("RATECARD SIMULATOR");?></B></TD>
        </TR>
		<FORM NAME="theFormFilter" action="<?php echo $PHP_SELF?>">		
		<tr>			
            <td height="31" bgcolor="#8888CC" style="padding-left: 5px; padding-right: 3px;">
					<br><font color="white"><b><?php echo gettext("NUMBER YOU WISH TO CALL");?> :</b></font>
					<INPUT type="text" name="called" value="<?php echo $called;?>">
					<br>
					<?php if (false){ ?>
					<br>
					<font color="white"><b><?php echo gettext("YOUR BALANCE");?> :</b></font>
					<INPUT type="text" name="balance" value="<?php if (!isset($balance)) echo "10"; else echo $balance;?>"> 
					<?php } ?>
					
					<br>
					 <input class="form_enter" name="id_cc_card" size="30" maxlength="50" value="<?php echo $id_cc_card;?>"> 
						<a href="#" onclick="window.open('A2B_entity_card.php?popup_select=1&popup_formname=theFormFilter&popup_fieldname=id_cc_card' , 'CardNumberSelection','width=550,height=330,top=20,left=100');"><img src="../Images/icon_arrow_orange.gif"></a>
						
                       <?php echo gettext("Select the card number ID to use");?>.
					<br>
					
			</td>
			<td height="31" bgcolor="#8888CC" style="padding-left: 5px; padding-right: 3px;">
				<input type="SUBMIT" value="<?php echo gettext("SIMULATE");?>" style="border: 2px outset rgb(204, 51, 0);" class="form_enter"/>
			</td>
        </tr>
		
		</FORM>	
		<TR> 
          <TD style="border-bottom: medium dotted #8888CC"  colspan="2"><br></TD>
        </TR>
	  </table>
	  
	  
<?php if ( (is_array($RateEngine->ratecard_obj)) && (!empty($RateEngine->ratecard_obj)) ){

if ($FG_DEBUG == 1) print_r($RateEngine->ratecard_obj);

$arr_ratecard=array('tariffgroupname', 'lcrtype', 'idtariffgroup', 'cc_tariffgroup_plan.idtariffplan', 'tariffname', 'destination', 'cc_ratecard.id' , 'dialprefix', 'destination', 'buyrate', 'buyrateinitblock', 'buyrateincrement', 'rateinitial', 'initblock', 'billingblock', 'connectcharge', 'disconnectcharge', 'stepchargea', 'chargea', 'timechargea', 'billingblocka', 'stepchargeb', 'chargeb', 'timechargeb', 'billingblockb', 'stepchargec', 'chargec', 'timechargec', 'billingblockc', 'tp_id_trunk', 'tp_trunk', 'providertech', 'tp_providerip', 'tp_removeprefix');

$FG_TABLE_ALTERNATE_ROW_COLOR[0]='#FF6767';
$FG_TABLE_ALTERNATE_ROW_COLOR[1]='#FF7575';
?>
 <br>
	  <table width="65%" border="0" align="center" cellpadding="0" cellspacing="0">
		
		<TR> 
          <TD style="border-bottom: medium dotted #FF4444" colspan="2"> <B><font color="red" size="3"> <?php echo gettext("CONGRATS : SIMULATOR FOUND A RATE THAT MATCH");?></font></B></TD>
        </TR>
		
		<?php if (count($RateEngine->ratecard_obj)>1){ ?>
		<TR> 
          <td height="15" bgcolor="#5555CC" style="padding-left: 5px; padding-right: 3px;" colspan="2">
					<b><?php echo gettext("MORE THAN ONE ROUTE FOUND ON THE RATECARD");?></b>
			</td>
        </TR>		
		<?php } ?>
		<?php for($j=0;$j<count($RateEngine->ratecard_obj);$j++){ ?>
			<TR> 
          	<td height="15" bgcolor="" style="padding-left: 5px; padding-right: 3px;" colspan="2">
					
			</td>
        	</TR>
			<TR> 
          	<td height="15" bgcolor="#55CC55" style="padding-left: 5px; padding-right: 3px;" colspan="2">
					<b><?php echo gettext("PREFIX-RATECARD");?> : #<?php echo $j+1;?></b>
			</td>
        	</TR>
			<tr>
				<td height="15" bgcolor="<?php echo $FG_TABLE_ALTERNATE_ROW_COLOR[1]?>" style="padding-left: 5px; padding-right: 3px;">
						<font color="blue"><b><?php echo gettext("MAX DURATION FOR THE CALL");?></b></font>
						
				</td>
				<td height="15" bgcolor="<?php echo $FG_TABLE_ALTERNATE_ROW_COLOR[1]?>" style="padding-left: 5px; padding-right: 3px;">
						<font color="blue"><i><?php echo $RateEngine->ratecard_obj[$j]['timeout'];?><?php echo gettext("seconds");?> </i></font>
				</td>
			</tr>
			<?php for($i=0;$i<count($arr_ratecard);$i++){ ?>
			<tr>			
				<td height="15" bgcolor="<?php echo $FG_TABLE_ALTERNATE_ROW_COLOR[$i%2]?>" style="padding-left: 5px; padding-right: 3px;">
						<b><?php echo $arr_ratecard[$i];?></b>
						
				</td>
				<td height="15" bgcolor="<?php echo $FG_TABLE_ALTERNATE_ROW_COLOR[$i%2]?>" style="padding-left: 5px; padding-right: 3px;">
						<i><?php echo $RateEngine->ratecard_obj[$j][$i];?></i>
				</td>
			</tr>
			<?php  } ?>
			
		<?php } ?>
		
		<TR> 
          <TD style="border-bottom: medium dotted #8888CC"  colspan="2"><br></TD>
        </TR>
	  </table>
<?php  } ?>
	  
<br><br><br><br>
<?php
	include("PP_footer.php");
?>
