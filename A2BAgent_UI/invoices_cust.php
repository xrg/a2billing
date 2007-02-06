<?php
include ("lib/defines.php");
include ("lib/module.access.php");

if (! has_rights (ACX_ACCESS)){
      Header ("HTTP/1.0 401 Unauthorized");
      Header ("Location: PP_error.php?c=accessdenied");
      die();
}


getpost_ifset(array('card','booth','nobq', 'posted',  'stitle', 'atmenu', 'current_page', 'order', 'sens', 'dst', 'src', 'srctype', 'src', 'choose_currency','exporttype'));

//$customer = $_SESSION["pr_login"];
$vat = $_SESSION["vat"];

//require (LANGUAGE_DIR.FILENAME_INVOICES);

if (($_GET[download]=="file") && $_GET[file] ) 
{
	
	$value_de=base64_decode($_GET[file]);
	$dl_full = MONITOR_PATH."/".$value_de;
	$dl_name=$value_de;

	if (!file_exists($dl_full))
	{ 
		echo gettext("ERROR: Cannot download file $dl_full , it does not exist").'<br>';
		exit();
	}
	
	header("Content-Type: application/octet-stream");
	header("Content-Disposition: attachment; filename=$dl_name");
	header("Content-Length: ".filesize($dl_full));
	header("Accept-Ranges: bytes");
	header("Pragma: no-cache");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Content-transfer-encoding: binary");
			
	@readfile($dl_full);
	
	exit();

}



if (!isset ($current_page) || ($current_page == "")){	
		$current_page=0; 
	}


// this variable specifie the debug type (0 => nothing, 1 => sql result, 2 => boucle checking, 3 other value checking)
$FG_DEBUG = 2;

// The variable FG_TABLE_NAME define the table name to use
$FG_TABLE_NAME="cc_session_invoice";

// THIS VARIABLE DEFINE THE COLOR OF THE HEAD TABLE
$FG_TABLE_HEAD_COLOR = "#D1D9E7";

$FG_TABLE_EXTERN_COLOR = "#7F99CC"; //#CC0033 (Rouge)
$FG_TABLE_INTERN_COLOR = "#EDF3FF"; //#FFEAFF (Rose)

// THIS VARIABLE DEFINE THE COLOR OF THE HEAD TABLE
$FG_TABLE_ALTERNATE_ROW_COLOR[] = "#FFFFFF";
$FG_TABLE_ALTERNATE_ROW_COLOR[] = "#E2E8EF";

$yesno = array();
$yesno["1"]  = array( "Yes", "1");
$yesno["0"]  = array( "No", "0");

$DBHandle  = DbConnect();

$nodisplay = true;
$card_username = '';

if (isset($card) && ($card > 0)){ // find by card
	$QUERY= str_dbparams($DBHandle,"SELECT cc_shopsessions.id FROM cc_agent_cards, cc_shopsessions " .
		" WHERE cc_shopsessions.card = cc_agent_cards.card_id AND cc_agent_cards.agentid = %1 AND cc_shopsessions.card = %2 " .
		" ORDER BY starttime DESC LIMIT 1;", array($_SESSION['agent_id'],$card));
	$res = $DBHandle -> query($QUERY);
	
	if ($res){
	if ($FG_DEBUG>=2)
		echo "Query: " . htmlspecialchars($QUERY) . "<br>";
		$nodisplay=false;
		$row=$res->FetchRow();
		$session_sid=$row[0];
	}
	else if ($FG_DEBUG >0){
		echo "Query: " . htmlspecialchars($QUERY) . "<br>";
		echo "Error: " . htmlspecialchars($DBHandle->ErrorMsg()) ."<br>";
	}

}else{ //no card, booth mode
	// Find the available/selected booths first..
	$QUERY= 'SELECT cc_booth.id, cc_booth.name, cc_shopsessions.id, cc_card.username FROM cc_booth,cc_shopsessions, cc_card ' .
		'WHERE cc_booth.id = cc_shopsessions.booth AND cc_booth.agentid =' . $DBHandle->Quote($_SESSION['agent_id']).
		' AND cc_card.id = cc_shopsessions.card ' .
		' AND cc_shopsessions.endtime IS NULL ORDER BY cc_shopsessions.id DESC ;';
	
	$res = $DBHandle -> query($QUERY);
	if ($res) 
		$booth_list = $res;
	else {
		$booth_list = array();
		if ($FG_DEBUG >0){
			echo "Query: " . htmlspecialchars($QUERY) . "<br>";
			echo "Error: " . htmlspecialchars($DBHandle->ErrorMsg()) ."<br>";
		}
	}
	if (isset($booth))
		foreach($booth_list as $boo)
		if ($boo[0] == $booth){
			$nodisplay=false;
			$session_sid=$boo[2];
			break;
		}

}



// The variable Var_col would define the col that we want show in your table
// First Name of the column in the html page, second name of the field
$FG_TABLE_COL = array();



/*******
Calldate Clid Src Dst Dcontext Channel Dstchannel Lastapp Lastdata Duration Billsec Disposition Amaflags Accountcode Uniqueid Serverid
*******/

$FG_TABLE_COL[]=array (gettext("Calldate"), "starttime", "18%", "center", "SORT", "19", "", "", "", "", "", "display_dateformat");
$FG_TABLE_COL[]=array (gettext("Description"), "descr", "10%", "center", "SORT", "");
$FG_TABLE_COL[]=array ("", "f2", "18%", "left", "SORT", "", "", "", "", "", "", "");
$FG_TABLE_COL[]=array (gettext("Called Number"), "cnum", "18%", "left", "SORT", "30", "", "", "", "", "", "remove_prefix");
$FG_TABLE_COL[]=array (gettext("Duration"), "duration", "8%", "center", "SORT", "30", "", "", "", "", "", "display_minute");
$FG_TABLE_COL[]=array (gettext("Credit"), "pos_charge", "8%", "center", "SORT", "30", "", "", "", "", "", "");
$FG_TABLE_COL[]=array (gettext("Charge"), "neg_charge", "8%", "center", "SORT", "30", "", "", "", "", "", "");

// ??? cardID
$FG_TABLE_DEFAULT_ORDER = "starttime";
$FG_TABLE_DEFAULT_SENS = "ASC";
	
// This Variable store the argument for the SQL query

if (! isset($choose_currency) || ( $choose_currency == ''))
	$choose_currency = $_SESSION["currency"];

$FG_COL_QUERY=str_dbparams($DBHandle, 'starttime, gettext(descr,%3), gettext(f2,%3), cnum, duration, ' .
	'format_currency(pos_charge,%1, %2), format_currency(neg_charge,%1, %2)',
	array(strtoupper(BASE_CURRENCY),$choose_currency,getenv('LANG')));
//$FG_COL_QUERY_GRAPH='t1.callstart, t1.duration';

$FG_SUM_QUERY=str_dbparams($DBHandle, "SUM(duration),format_currency(SUM(pos_charge),%1, %2), ".
	"format_currency(SUM(neg_charge),%1, %2), ".
	"format_currency((SUM(pos_charge) - SUM(neg_charge)),%1, %2), ".
	"(SUM(pos_charge) - SUM(neg_charge)), ". /* and once the numeric value BEWARE: NOT in chosen currency! */
	"format_currency((SUM(neg_charge) - SUM(pos_charge)),%1, %2) ",
	array(strtoupper(BASE_CURRENCY),$choose_currency));


// The variable LIMITE_DISPLAY define the limit of record to display by page
$FG_LIMITE_DISPLAY=500;

// Number of column in the html table
$FG_NB_TABLE_COL=count($FG_TABLE_COL);

$FG_TABLE_CLAUSE= 'sid = '. $DBHandle->Quote($session_sid);

//This variable will store the total number of column
$FG_TOTAL_TABLE_COL = $FG_NB_TABLE_COL;
if ($FG_DELETION || $FG_EDITION) $FG_TOTAL_TABLE_COL++;

//This variable define the Title of the HTML table
$FG_HTML_TABLE_TITLE=" - Call Logs - ";

//This variable define the width of the HTML table
$FG_HTML_TABLE_WIDTH="90%";

	if ($FG_DEBUG >= 3) echo "<br>Table : $FG_TABLE_NAME  	- 	Col_query : $FG_COL_QUERY";
	$instance_table = new Table($FG_TABLE_NAME, $FG_COL_QUERY);
	$instance_table_sum = new Table($FG_TABLE_NAME, $FG_SUM_QUERY);


if ( is_null ($order) || is_null($sens) ){
	$order = $FG_TABLE_DEFAULT_ORDER;
	$sens  = $FG_TABLE_DEFAULT_SENS;
}


if (!$nodisplay){
	$list = $instance_table -> Get_list ($DBHandle, $FG_TABLE_CLAUSE, $order, $sens, null, null, $FG_LIMITE_DISPLAY, $current_page*$FG_LIMITE_DISPLAY);
	$list_sum = $instance_table_sum -> Get_list ($DBHandle, $FG_TABLE_CLAUSE);
}

$_SESSION["pr_sql_export"]="SELECT $FG_COL_QUERY FROM $FG_TABLE_NAME WHERE $FG_TABLE_CLAUSE";

/************************/
//$QUERY = "SELECT substring(calldate,1,10) AS day, sum(duration) AS calltime, count(*) as nbcall FROM cdr WHERE ".$FG_TABLE_CLAUSE." GROUP BY substring(calldate,1,10)"; //extract(DAY from calldate)


if ($FG_DEBUG >= 3) echo "<br>Clause : $FG_TABLE_CLAUSE";
$nb_record = $instance_table -> Table_count ($DBHandle, $FG_TABLE_CLAUSE);
if ($FG_DEBUG >= 4) var_dump ($list);



// GROUP BY DESTINATION FOR THE INVOICE


$QUERY = "SELECT destination, sum(t1.sessiontime) AS calltime, 
format_currency(sum(t1.sessionbill), '" .strtoupper(BASE_CURRENCY) . "','$choose_currency') AS cost, count(*) as nbcall FROM $FG_TABLE_NAME WHERE ".$FG_TABLE_CLAUSE." GROUP BY destination";

if (!$nodisplay){
		$res = $DBHandle -> query($QUERY);
		if ($res) $num = $res -> numRows();
		else $num = 0;
		for($i=0;$i<$num;$i++)
		{				
			$list_total_destination [] =$res -> fetchRow();
		}


if ($FG_DEBUG == 3) echo "<br>Clause : $FG_TABLE_CLAUSE";
if ($FG_DEBUG >= 4) var_dump ($list_total_destination);


}//end IF nodisplay

$QUERY = str_dbparams($DBHandle,"SELECT gettext(descr,%3), " .
	"format_currency(SUM(pos_charge), %1, %2), " .
	"format_currency(SUM(neg_charge), %1, %2) " .
	"FROM cc_session_invoice WHERE " . $FG_TABLE_CLAUSE .
	" GROUP BY descr",
	array(strtoupper(BASE_CURRENCY),$choose_currency,getenv('LANG')));

//extract(DAY from calldate)

if (!$nodisplay){
		$res = $DBHandle -> query($QUERY);
		if ($res) $num = $res -> numRows();
		else $num = 0;
		for($i=0;$i<$num;$i++)
		{
			$list_type_charge [] =$res -> fetchRow();
		}

		if ($FG_DEBUG >= 3) var_dump ($list_type_charge);

}//end IF nodisplay




if ($nb_record<=$FG_LIMITE_DISPLAY){
	$nb_record_max=1;
}else{ 
	if ($nb_record % $FG_LIMITE_DISPLAY == 0){
		$nb_record_max=(intval($nb_record/$FG_LIMITE_DISPLAY));
	}else{
		$nb_record_max=(intval($nb_record/$FG_LIMITE_DISPLAY)+1);
	}	
}


if ($FG_DEBUG == 3) echo "<br>Nb_record : $nb_record";
if ($FG_DEBUG == 3) echo "<br>Nb_record_max : $nb_record_max";




if ((isset($customer)  &&  ($customer>0)) || (isset($entercustomer)  &&  ($entercustomer>0))){

	$FG_TABLE_CLAUSE = "";
	if (isset($customer)  &&  ($customer>0)){		
		$FG_TABLE_CLAUSE =" username='$customer' ";
	}elseif (isset($entercustomer)  &&  ($entercustomer>0)){
		$FG_TABLE_CLAUSE =" username='$entercustomer' ";
	}



	$instance_table_customer = new Table("cc_card", "id,  username, lastname, firstname, address, city, state, country, zipcode, phone, email, fax");
	
	
	
	$info_customer = $instance_table_customer -> Get_list ($DBHandle, $FG_TABLE_CLAUSE, "id", "ASC", null, null, null, null);
	
	// if (count($info_customer)>0){
}

?>
<?php if($exporttype!="pdf"){?>

<?php
	include("PP_header.php");
?>
<script language="JavaScript" type="text/JavaScript">
<!--
function MM_openBrWindow(theURL,winName,features) { //v2.0
  window.open(theURL,winName,features);
}

//-->
</script>

<?php
}else{
   require('pdf-invoices/html2pdf/html2fpdf.php');
   ob_start();

} ?>

<table width="20%" align="center">
<tr>
<td> <img src="pdf-invoices/images/companylogo.gif"/> </td>
</tr>
</table>

<?php if (!isset($card) && ($nobq !=1)){ ?>
	<center>
	<FORM method=POST action="<?php echo $PHP_SELF?>?s=1&t=0&order=<?php echo $order?>&sens=<?php echo $sens?>&current_page=<?php echo $current_page?>">
	<INPUT TYPE="hidden" name="posted" value=1>
	<INPUT TYPE="hidden" name="current_page" value=0>	
	<table class="bar-status" width="95%" border="0" cellspacing="1" cellpadding="2" align="center">
	<tbody>
	<tr class='dark'>
	<td class="bar-search-2" align="left" bgcolor="#000033">&nbsp;&nbsp; </td>

		<td class="bar-search" align="center" bgcolor="#acbdee" colspan=2>
		<?= _("Select booth:") ?> 
		<select name="booth" size="1" class="form_enter_b" >
		<?php foreach($booth_list as $bb) {
			if ($bb[0] == $booth) $b_sel='selected';
				else $b_sel=''; ?>
			<option value='<?= $bb[0] ?>' <?= $b_sel ?> > <?= $bb[1] ?> ( <?= $bb[3] ?>) </option>
		<?php } ?>
		</select>
		</td>
	</tr>
	<tr>
		<td class="bar-search" align="left" bgcolor="#555577" width='30px'>
		<?= _("View") ?>
		</td>
		<td class="bar-search" align="left" bgcolor="#cddeff">
			<?php echo gettext("Result");?> : <?php echo gettext("Minutes");?><input type="radio" name="resulttype" value="min" <?php if((!isset($resulttype))||($resulttype=="min")){?>checked<?php }?>> - <?php echo gettext("Seconds");?> <input type="radio" NAME="resulttype" value="sec" <?php if($resulttype=="sec"){?>checked<?php }?>>
		</td>
		<td class="bar-search" align="left" bgcolor="#cddeff">
		<?php echo gettext("Currency");?> :
		<select name="choose_currency" size="1" class="form_enter_b" >
			<?php
				$currencies_list = get_currencies();
				foreach($currencies_list as $key => $cur_value) {
			?>
				<option value='<?php echo $key ?>' <?php if (($choose_currency==$key) || (!isset($choose_currency) && $key==strtoupper(BASE_CURRENCY))){?>selected<?php } ?>><?php echo $cur_value[1].' ('.$cur_value[2].')' ?>
				</option>
			<?php 	} ?>
		</select>
		</td>
	</tr>
	<tr>
		<td class="bar-search-2" align="left" bgcolor="#000033">&nbsp;&nbsp; </td>

		<td class="bar-search" align="center" bgcolor="#acbdee">
			<?php echo gettext("See Invoice in HTML");?><input type="radio" NAME="exporttype" value="html" <?php if((!isset($exporttype))||($exporttype=="html")){?>checked<?php }?>>
			<?php echo gettext("or Export PDF");?> <input type="radio" NAME="exporttype" value="pdf" <?php if($exporttype=="pdf"){?>checked<?php }?>>
		</td>
		<td class="bar-search-2" align="right" bgcolor="#acbdee">
			<input class="form_enter_b" value=" <?= _("Search");?> " type="submit">
		</td>
	</tr>
	</tbody></table>
	</FORM>
</center>

<br><br>
<?php } ?>

<table width="100%">
<tr>
<?php if (SHOW_ICON_INVOICE){?> <td align="left"><img src="pdf-invoices/images/kfind.gif"/> </td> <?php } ?>
<td align="center"  bgcolor="#fff1d1"><font color="#000000" face="verdana" size="5"> <b><?=  str_dblspace(gettext("CALLS DETAIL"));?></b> </td>
</tr>
</table>

<br><br>

<?php if (is_array($list) && count($list>0)) { 
// ---------------- Part to display the CDR -----------
?>


		<TABLE border=0 cellPadding=0 cellSpacing=0 width="<?=  $FG_HTML_TABLE_WIDTH?>" align="center">
                <TR bgColor=#F0F0F0>
		  <TD width="5%" class="tableBodyRight" style="PADDING-BOTTOM: 2px; PADDING-LEFT: 2px; PADDING-RIGHT: 2px; PADDING-TOP: 2px"><?=  gettext("nb");?></TD>
	<?php if (is_array($list) && count($list)>0){ 
		for($i=0;$i<$FG_NB_TABLE_COL;$i++){ ?>
                  <TD width="<?=  $FG_TABLE_COL[$i][2]?>" align=middle class="tableBody" style="PADDING-BOTTOM: 2px; PADDING-LEFT: 2px; PADDING-RIGHT: 2px; PADDING-TOP: 2px">
                    <center>
                    <?=  $FG_TABLE_COL[$i][0]?>
 
                  </center></TD>
				   <?php } ?>
                </TR>
				<?php
				  	 $ligne_number=0;
				  	 foreach ($list as $recordset){ 
						 $ligne_number++;
				?>
				
               		 <TR bgcolor="<?=  $FG_TABLE_ALTERNATE_ROW_COLOR[$ligne_number%2]?>">
						<TD align="<?=  $FG_TABLE_COL[$i][3]?>" class=tableBody><?php  echo $ligne_number+$current_page*$FG_LIMITE_DISPLAY; ?></TD>
				  		<?php for($i=0;$i<$FG_NB_TABLE_COL;$i++){
									$record_display = $recordset[$i];
							
							
							
							if ( is_numeric($FG_TABLE_COL[$i][5]) && (strlen($record_display) > $FG_TABLE_COL[$i][5])  ){
								$record_display = substr($record_display, 0, $FG_TABLE_COL[$i][5]-3)."";
							}
							
				 		 ?>
                 		 <TD align="<?=  $FG_TABLE_COL[$i][3]?>" class=tableBody><?php
                 		 		if (isset ($FG_TABLE_COL[$i][11]) && strlen($FG_TABLE_COL[$i][11])>1){
						 	call_user_func($FG_TABLE_COL[$i][11], $record_display);
						 }else{
						 	echo stripslashes($record_display);
						 }
						 ?></TD>
				 		 <?php  } ?>
                  
					</TR>
				<?php
					 }//foreach ($list as $recordset)
					 if ($ligne_number < $FG_LIMITE_DISPLAY)
					 	$ligne_number_end=$ligne_number +2;
					 while ($ligne_number < $ligne_number_end){
					 	$ligne_number++;
				?>
					<TR bgcolor="<?=  $FG_TABLE_ALTERNATE_ROW_COLOR[$ligne_number%2]?>">
				  		<?php for($i=0;$i<$FG_NB_TABLE_COL;$i++){ 
				 		 ?>
                 		 <TD>&nbsp;</TD>
				 		 <?php  } ?>
                 		 <TD align="center">&nbsp;</TD>
				</TR>
				<?php
					 } //END_WHILE ?>
				<tr class="sum_row"><td colspan=5 align=right><?= _("Partial sum:")?> &nbsp; &nbsp;</td>
				<?php $sum_line = $list_sum[0]; ?>
				<td class=tableBody><?php display_minute($sum_line[0]);?></td>
				<td class=tableBody><?= $sum_line[1]; ?></td><td class=tableBody><?= $sum_line[2]; ?></td>
				</tr>
				<tr class="sum_row"><td colspan=5 align=right><?= _("Sum:")?>  &nbsp; &nbsp; </td>
				<td >&nbsp;</td>
				<td colspan=2 class=tableBody><?= $sum_line[3]; ?></td></tr>
				<?php
				  }else{
				  		echo gettext("No data found !!!");
				  }//end_if
				 ?>
                             
            </TABLE>
			
			

<?php  if (is_array($list) && count($list)>0 && 3==4){ ?>
<!-- ************** TOTAL SECTION ************* -->
			<br/>
			<div style="padding-right: 15px;">
			<table cellpadding="1" bgcolor="#000000" cellspacing="1" width="<?php if ($_SESSION["is_admin"]==1){ ?>450<?php }else{?>200<?php }?>" align="right">
				<tbody>
                <tr class="form_head">
				   <td width="33%" align="center" class="tableBodyRight" bgcolor="#600101" style="padding: 5px;"><strong>TOTAL COSTS</strong></td>
				   <?php if ($_SESSION["is_admin"]==1){ ?><td width="33%" align="center" class="tableBodyRight" bgcolor="#600101" style="padding: 5px;"><strong><?=  gettext("TOTAL BUYCOSTS");?></strong></td><?php }?>
				   <?php if ($_SESSION["is_admin"]==1){ ?><td width="33%" align="center" class="tableBodyRight" bgcolor="#600101" style="padding: 5px;"><strong><?=  gettext("DIFFERENCE");?></strong></td><?php }?>
                </tr>
				<tr>
				  <td valign="top" align="center" class="tableBody" bgcolor="white"><b><?=  $total_cost[0][0]?></b></td>
				  <?php if ($_SESSION["is_admin"]==1){ ?><td valign="top" align="center" class="tableBody" bgcolor="#66FF66"><b><?=  $total_cost[0][1]?></b></td><?php }?>
				  <?php if ($_SESSION["is_admin"]==1){ ?><td valign="top" align="center" class="tableBody" bgcolor="#FF6666"><b><?=  $total_cost[0][0]-$total_cost[0][1]?></b></td><?php }?>

				</tr>
			</table>
			</div>
			<br/><br/>
					
<!-- ************** TOTAL SECTION ************* -->
<?php  } ?>

<!-- ** ** ** ** ** Part to display the GRAPHIC ** ** ** ** ** -->
<br><br>


<?php  }else{  ?>
	<center><h3><?=  gettext("No calls in your Selection");?>.</h3></center>
<?php  } ?>
</center>

<!-- FIN TITLE GLOBAL MINUTES //-->
		
<?php if (! $nodisplay) { ?>
<br><hr width="350"><br><br>

<table width="100%">
<tr>
<?php if (SHOW_ICON_INVOICE){?><td align="left"><img src="pdf-invoices/images/desktop.gif"/> </td><?php }?>
<td align="center"  bgcolor="#fff1d1"><font color="#000000" face="verdana" size="5"> <b><?=  str_dblspace(gettext("BILLING SERVICE"));?> : <?php  if (strlen($info_customer[0][2])>0) echo $info_customer[0][2]; ?> </b> </td>
</tr>
</table>
<table border="0" cellspacing="1" cellpadding="2" width="70%" align="center">
	<tr>	
		<td align="center" bgcolor="#600101"></td>
    	<td bgcolor="#b72222" align="center" colspan="4"><font color="#ffffff"><b><?=  gettext("CREDITS/CHARGES");?></b></font></td>
    </tr>
	<tr>

		<td align="center" bgcolor="#F2E8ED"><font face="verdana" size="1" color="#000000"><b><?=  gettext("TYPE");?></b></font></td>
        <td align="center"><font face="verdana" color="#000000" size="1"><b><?=  gettext("CREDIT");?></b></font></td>
		<td align="center"><font face="verdana" color="#000000" size="1"><b><?=  gettext("CHARGE");?></b></font></td>
 
<?php  		
		$i=0;
		foreach ($list_type_charge as $data){
		$i=($i+1)%2;
		 
		
	?>
	</tr>
	<tr>
		<td align="center" bgcolor="#D2D8ED"><font face="verdana" size="1" color="#000000"><?=  $data[0]?></font></td>

        <td bgcolor="<?=  $FG_TABLE_ALTERNATE_ROW_COLOR[$i]?>" align="center"><font face="verdana" color="#000000" size="1"><?=  $data[1]?></font></td>
        
		<td bgcolor="<?=  $FG_TABLE_ALTERNATE_ROW_COLOR[$i]?>" align="center"><font face="verdana" color="#000000" size="1"><?= $data[2] ?></font></td>
     <?php 	 }


	 ?>
	</tr>	
		
	<tr>		
		<td align="center" bgcolor="#BA5151" colspan="3"><font color="#ffffff">
		<b><?= _("TOTAL");?> = 

<?php echo $list_sum[0][3];
if ($vat>0) echo  " (" .gettext("includes VAT"). "$vat %)";
 ?>
 </b></font></td>
	</tr>
</table>
<?php  } ?>

<?php 
if (is_array($list_total_day) && count($list_total_day)>0){ ?>
<br><hr width="350"><br><br>

<table width="100%">
<tr>
<?php if (SHOW_ICON_INVOICE){?><td align="left"><img src="pdf-invoices/images/stock_landline-phone.gif"/> </td><?php } ?>
<td align="center"  bgcolor="#fff1d1"><font color="#000000" face="verdana" size="5"> <b><?=  str_dblspace(gettext("BILL EVOLUTION"));?></b> </td>
</tr>
</table>

<br><br>

<?php

$mmax=0;
$totalcall=0;
$totalminutes=0;
$totalcost=0;
foreach ($list_total_day as $data){	
	if ($mmax < $data[1]) $mmax=$data[1];
	$totalcall+=$data[3];
	$totalminutes+=$data[1];
	$totalcost+=$data[2];
}
?>
<!-- FIN TITLE GLOBAL MINUTES //-->
		
<table border="0" cellspacing="1" cellpadding="2" width="70%" align="center">
	<tr>	
	<td align="center" bgcolor="#600101"></td>
    	<td bgcolor="#b72222" align="center" colspan="4"><font color="#ffffff"><b></b></font></td>
    </tr>
	<tr>
		<td align="right" bgcolor="#F2E8ED"><font face="verdana" size="1" color="#000000"><?=  gettext("DATE");?></font></td>
		<td align="right"><font face="verdana" color="#000000" size="1"><?=  gettext("DUR");?> </font></td>
        <td align="center"><font face="verdana" color="#000000" size="1"><?=  gettext("GRAPHIC");?> </font> </td>
        <td align="right"><font face="verdana" color="#000000" size="1"><?=  gettext("CALL");?></font></td>
		<td align="right"><font face="verdana" color="#000000" size="1"><?=  gettext("TOTAL COST");?></font></td>
	 
<?php 
		$i=0;
		foreach ($list_total_day as $data){	
		$i=($i+1)%2;		
		$tmc = $data[1]/$data[3];
		
		if ((!isset($resulttype)) || ($resulttype=="min")){  
			$tmc = sprintf("%02d",intval($tmc/60)).":".sprintf("%02d",intval($tmc%60));		
		}else{
		
			$tmc =intval($tmc);
		}
		
		if ((!isset($resulttype)) || ($resulttype=="min")){  
				$minutes = sprintf("%02d",intval($data[1]/60)).":".sprintf("%02d",intval($data[1]%60));
		}else{
				$minutes = $data[1];
		}
		if ($mmax>0) 	$widthbar= intval(($data[1]/$mmax)*200); 
		
	?>
	</tr>
	<tr>
		<td align="right" bgcolor="#D2D8ED"><font face="verdana" size="1" color="#000000"><?=  $data[0]?></font></td>
		<td bgcolor="<?=  $FG_TABLE_ALTERNATE_ROW_COLOR[$i]?>" align="right"><font face="verdana" color="#000000" size="1"><?=  $minutes?> </font></td>
        <td bgcolor="<?= $FG_TABLE_ALTERNATE_ROW_COLOR[$i]?>" align="left">
        	<img src="pdf-invoices/images/sidenav-selected.gif" height="6" width="<?= $widthbar?>">
		</td>
        <td bgcolor="<?=  $FG_TABLE_ALTERNATE_ROW_COLOR[$i]?>" align="right"><font face="verdana" color="#000000" size="1"><?=  $data[3]?></font></td>
        
		<td bgcolor="<?=  $FG_TABLE_ALTERNATE_ROW_COLOR[$i]?>" align="right"><font face="verdana" color="#000000" size="1"><?= $data[2] ?></font></td>
     <?php 	 }	 	 	
	 	
		if ((!isset($resulttype)) || ($resulttype=="min")){  
			$total_tmc = sprintf("%02d",intval(($totalminutes/$totalcall)/60)).":".sprintf("%02d",intval(($totalminutes/$totalcall)%60));				
			$totalminutes = sprintf("%02d",intval($totalminutes/60)).":".sprintf("%02d",intval($totalminutes%60));
		}else{
			$total_tmc = intval($totalminutes/$totalcall);			
		}
	 
	 ?>
	</tr>	
	<tr bgcolor="#600101">
		<td align="right"><font color="#ffffff"><b><?=  gettext("TOTAL");?></b></font></td>
		<td align="center" colspan="2"><font color="#ffffff"><b><?=  $totalminutes?> </b></font></td>
		<td align="center"><font color="#ffffff"><b><?=  $totalcall?></b></font></td>
		<td align="center"><font color="#ffffff"><b><?= $totalcost ?></b></font></td>
	</tr>
	<tr>		
		<td align="center" bgcolor="#BA5151" colspan="5"><font color="#ffffff">
		<b>TOTAL = 

<?php  
$prvat = ($vat / 100) * $totalcost;

display_2bill($totalcost + $prvat);
if ($vat>0) echo  " (".$vat." % ".gettext("VAT").")";

 ?>
 </b></font></td>
	</tr>
</table>
<?php  } ?>

<?php  if($exporttype!="pdf"){ 
if ($list_sum[0][4] >0.0){ ?>
	<br><hr width="350"><br>
	<p class='pay-back-btn'> <a href='pay.php?sid=<?= $session_sid . '&choose_currency=' .$choose_currency; ?>&pback=1'> <?=_("Pay back:") . '&nbsp' . $list_sum[0][3] ; ?></a>
	<br><hr width="350"><br>
<?php }else if ($list_sum[0][4] <0.0){ ?>
	<br><hr width="350"><br>
	<p class='pay-btn'> <a href='pay.php?sid=<?= $session_sid .'&choose_currency=' .$choose_currency; ?>&pback=0'> <?=_("Pay:") . '&nbsp' . $list_sum[0][5] ; ?></a>
	<br><hr width="350"><br>
<?php } ?>
<br>
<?php
	include("PP_footer.php");
?>

<?php  }else{
// EXPORT TO PDF

	$html = ob_get_contents();
	// delete output-Buffer
	ob_end_clean();
	
	$pdf = new HTML2FPDF();
	
	$pdf -> DisplayPreferences('HideWindowUI');
	
	$pdf -> AddPage();
	$pdf -> WriteHTML($html);
	
	$html = ob_get_contents();
	
	$pdf->Output('CC_invoice_'.date("d/m/Y-H:i").'.pdf', 'I');



} ?>