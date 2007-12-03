<?php
$menu_section = 'menu_agents';
include ("../lib/defines.php");
include ("../lib/module.access.php");

if (! has_rights (ACX_AGENTS)){
      Header ("HTTP/1.0 401 Unauthorized");
      Header ("Location: PP_error.php?c=accessdenied");
      die();
}


getpost_ifset(array('agent_id','nobq', 'posted',  'stitle', 'dst', 'src', 'srctype', 'src',  'choose_currency','exporttype','Period', 'frommonth', 'fromstatsmonth', 'tomonth', 'tostatsmonth', 'fromday', 'fromstatsday_sday', 'fromstatsmonth_sday', 'today', 'tostatsday_sday', 'tostatsmonth_sday','fromstatsmonth_sday', 'fromstatsmonth_shour', 'tostatsmonth_sday', 'tostatsmonth_shour','fromstatsmonth_smin','tostatsmonth_smin'));


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


// this variable specifie the debug type (0 => nothing, 1 => sql result, 2 => boucle checking, 3 other value checking)
$FG_DEBUG = 1;

// The variable FG_TABLE_NAME define the table name to use
$FG_TABLE_NAME="cc_agent_money_v";

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

$nodisplay = false;

// The variable Var_col would define the col that we want show in your table
// First Name of the column in the html page, second name of the field
$FG_TABLE_COL = array();

// Put all the sums information in one array, so that it can be laid in tables
$res_sums = array();


/*******
Calldate Clid Src Dst Dcontext Channel Dstchannel Lastapp Lastdata Duration Billsec Disposition Amaflags Accountcode Uniqueid Serverid
*******/

$FG_TABLE_COL[]=array (gettext("Calldate"), "date", "15%", "center", "SORT", "", "", "", "", "", "", "display_dateformat");
$FG_TABLE_COL[]=array (gettext("Type"), "pay_type", "10%", "left", "SORT", "");
$FG_TABLE_COL[]=array (gettext("Description"), "descr", "40%", "left", "SORT", "");
//$FG_TABLE_COL[]=array (gettext("Card"), "card_id", "8%", "center", "SORT", "30", "", "", "", "", "", "display_minute");
$FG_TABLE_COL[]=array (gettext("In"), "pos_credit", "8%", "center", "SORT", "30", "", "", "", "", "", "");
$FG_TABLE_COL[]=array (gettext("Out"), "neg_credit", "8%", "center", "SORT", "30", "", "", "", "", "", "");

// ??? cardID
$FG_TABLE_DEFAULT_ORDER = "date";
$FG_TABLE_DEFAULT_SENS = "ASC";
	
// This Variable store the argument for the SQL query

if (! isset($choose_currency) || ( $choose_currency == ''))
	$choose_currency = BASE_CURRENCY;

if (! isset($agent_id))
	$agent_id = -1;

$FG_COL_QUERY=str_dbparams($DBHandle, 'fmt_date(date), gettexti(pay_type,\'C\') AS pay_type_txt,descr, ' .
	'format_currency(pos_credit,%1, %2), format_currency(neg_credit,%1, %2)',
	array(strtoupper(BASE_CURRENCY),$choose_currency));
//$FG_COL_QUERY_GRAPH='t1.callstart, t1.duration';

$FG_SUM_QUERY=str_dbparams($DBHandle, "format_currency(SUM(pos_credit),%1, %2), ".
	"format_currency(SUM(neg_credit),%1, %2), ".
	"format_currency(SUM(credit),%1, %2), ".
	"SUM(credit) ", /* and once the numeric value BEWARE: NOT in chosen currency! */
	array(strtoupper(BASE_CURRENCY),$choose_currency));

$FG_DL_QUERY=str_dbparams($DBHandle, "SELECT format_currency(climit,%1, %2), days_left  FROM cc_calc_daysleft(%3,now(),interval '1 month');",
	array(strtoupper(BASE_CURRENCY),$choose_currency,$agent_id));


// The variable LIMITE_DISPLAY define the limit of record to display by page
$FG_LIMITE_DISPLAY=500;

// Number of column in the html table
$FG_NB_TABLE_COL=count($FG_TABLE_COL);

$FG_TABLE_CLAUSE= 'agentid = '. $DBHandle->Quote($agent_id);
$FG_TABLE_CLAUSE_NODATE = $FG_TABLE_CLAUSE;
// ------ Date clause

$date_clause=fmt_dateclause($DBHandle,"date");
$date_clause_c=fmt_dateclause_c($DBHandle,"date");

//echo "Date clause: " . $date_clause . " / " . $date_clause_c . "<br><br>\n";
if ($date_clause != "") $FG_TABLE_CLAUSE .= " AND " . $date_clause;
// --------- End date clause


//This variable will store the total number of column
$FG_TOTAL_TABLE_COL = $FG_NB_TABLE_COL;
if ($FG_DELETION || $FG_EDITION) $FG_TOTAL_TABLE_COL++;

//This variable define the Title of the HTML table
$FG_HTML_TABLE_TITLE=_(" - Transactions - ");

//This variable define the width of the HTML table
$FG_HTML_TABLE_WIDTH="90%";

	if ($FG_DEBUG >= 3) echo "<br>Table : $FG_TABLE_NAME  	- 	Col_query : $FG_COL_QUERY";
	$instance_table = new Table($FG_TABLE_NAME, $FG_COL_QUERY);
	$instance_table_sum = new Table($FG_TABLE_NAME, $FG_SUM_QUERY);
	$instance_table_carry = new Table($FG_TABLE_NAME, $FG_SUM_QUERY);
	if ($FG_DEBUG >= 2) {
		$instance_table->debug_st=1;
		$instance_table_sum->debug_st=1;
		$instance_table_carry->debug_st=1;
	}


if ( is_null ($order) || is_null($sens) ){
	$order = $FG_TABLE_DEFAULT_ORDER;
	$sens  = $FG_TABLE_DEFAULT_SENS;
}


if (!$nodisplay){
	$list = $instance_table -> Get_list ($DBHandle, $FG_TABLE_CLAUSE, $order, $sens, null, null, $FG_LIMITE_DISPLAY, $current_page*$FG_LIMITE_DISPLAY);
	$list_tmp = $instance_table_sum -> Get_list ($DBHandle, $FG_TABLE_CLAUSE_NODATE);
	if($list_tmp){
		$res_sums['pos_sums']=$list_tmp[0][0];
		$res_sums['neg_sums']=$list_tmp[0][1];
		$res_sums['all_sums']=$list_tmp[0][2];
		$res_sums['raw_credit']=$list_tmp[0][3];
	}else
		if ($FG_DEBUG>0) echo "Sums query failed." . $DBHandle->ErrorMsg() . "<br>";
	
	if ($date_clause_c != ''){
		$list_tmp = $instance_table_carry -> Get_list ($DBHandle, $FG_TABLE_CLAUSE_NODATE . " AND ". $date_clause_c);
		if($list_tmp){
			$res_sums['pos_carry']=$list_tmp[0][0];
			$res_sums['neg_carry']=$list_tmp[0][1];
			$res_sums['all_carry']=$list_tmp[0][2];
			$res_sums['raw_carry']=$list_tmp[0][3];
		}else
			if ($FG_DEBUG>0) echo "Carry query failed." . $DBHandle->ErrorMsg() . "<br>";
		
	}
	$res= $DBHandle->Query($FG_DL_QUERY);
	if($res){
		$list_tmp=$res->FetchRow();
		$res_sums['climit']=$list_tmp[0];
		$res_sums['days_left']=$list_tmp[1];
	}else
		if ($FG_DEBUG>0) echo "Days-left query failed." . $DBHandle->ErrorMsg() . "<br>";
	
	$query=str_dbparams($DBHandle, "SELECT format_currency(SUM(credit),%1, %2) FROM cc_card, cc_agent_cards WHERE cc_agent_cards.card_id= cc_card.id AND cc_agent_cards.agentid = %3 AND credit < 0;",
		array(strtoupper(BASE_CURRENCY),$choose_currency,$agent_id));
	$res= $DBHandle->Query($query);
	if($res){
		$list_tmp=$res->FetchRow();
		$res_sums['total_cdebit']=$list_tmp[0];
	}else
		if ($FG_DEBUG>0) echo "Total cdebit query failed." . $DBHandle->ErrorMsg() . "<br>";
	
	$query=str_dbparams($DBHandle, "SELECT format_currency(SUM(credit),%1, %2) FROM cc_card, cc_agent_cards WHERE cc_agent_cards.card_id= cc_card.id AND cc_agent_cards.agentid = %3 AND credit > 0;",
		array(strtoupper(BASE_CURRENCY),$choose_currency,$agent_id));
	$res= $DBHandle->Query($query);
	if($res){
		$list_tmp=$res->FetchRow();
		$res_sums['total_ccredit']=$list_tmp[0];
	}else
		if ($FG_DEBUG>0) echo "Total ccredit query failed." . $DBHandle->ErrorMsg() . "<br>";

	$query=str_dbparams($DBHandle, "SELECT format_currency(SUM(creditlimit),%1, %2) FROM cc_card, cc_agent_cards WHERE cc_agent_cards.card_id= cc_card.id AND cc_agent_cards.agentid = %3 AND creditlimit > 0;",
	array(strtoupper(BASE_CURRENCY),$choose_currency,$agent_id));
	$res= $DBHandle->Query($query);
	if($res){
		$list_tmp=$res->FetchRow();
		$res_sums['total_cclimit']=$list_tmp[0];
	}else
		if ($FG_DEBUG>0) echo "Total cclimit query failed." . $DBHandle->ErrorMsg() . "<br>";

	$dc2= fmt_dateclause($DBHandle,"cc_call.starttime");
	if ($dc2 != '' )
		$dc2=" AND " . $dc2;
	$query=str_dbparams($DBHandle, "SELECT format_currency(calls,%1, %2), format_currency( (calls * commission) ,%1, %2), format_currency( (calls * (1.0 - commission)) ,%1, %2)  FROM ( SELECT SUM(sessionbill) AS calls FROM cc_call, cc_card, cc_agent_cards WHERE cc_call.username = cc_card.username AND cc_agent_cards.card_id= cc_card.id AND cc_agent_cards.agentid = %3 ". $dc2 . ") AS sb, cc_agent WHERE cc_agent.id = %3;",
	array(strtoupper(BASE_CURRENCY),$choose_currency,$agent_id));
	$res= $DBHandle->Query($query);
	if($res){
		$list_tmp=$res->FetchRow();
		$res_sums['total_calls']=$list_tmp[0];
		$res_sums['total_calls_com']=$list_tmp[1];
		$res_sums['total_calls_wh']=$list_tmp[2];
	}else
		if ($FG_DEBUG>0) echo "Total calls query failed." . $DBHandle->ErrorMsg() . "<br>";

	if ($FG_DEBUG>=2) print_r($res_sums);
}

$_SESSION["pr_sql_export"]="SELECT $FG_COL_QUERY FROM $FG_TABLE_NAME WHERE $FG_TABLE_CLAUSE";

/************************/
//$QUERY = "SELECT substring(calldate,1,10) AS day, sum(duration) AS calltime, count(*) as nbcall FROM cdr WHERE ".$FG_TABLE_CLAUSE." GROUP BY substring(calldate,1,10)"; //extract(DAY from calldate)


if ($FG_DEBUG >= 3) echo "<br>Clause : $FG_TABLE_CLAUSE";
$nb_record = $instance_table -> Table_count ($DBHandle, $FG_TABLE_CLAUSE);
if ($FG_DEBUG >= 4) var_dump ($list);



// GROUP BY DESTINATION FOR THE INVOICE


// $QUERY = "SELECT destination, sum(t1.sessiontime) AS calltime, 
// format_currency(sum(t1.sessionbill), '" .strtoupper(BASE_CURRENCY) . "','$choose_currency') AS cost, count(*) as nbcall FROM $FG_TABLE_NAME WHERE ".$FG_TABLE_CLAUSE." GROUP BY destination";
// 
// if (!$nodisplay){
// 		$res = $DBHandle -> query($QUERY);
// 		if ($res) $num = $res -> numRows();
// 		else $num = 0;
// 		for($i=0;$i<$num;$i++)
// 		{				
// 			$list_total_destination [] =$res -> fetchRow();
// 		}
// 
// 
// if ($FG_DEBUG == 3) echo "<br>Clause : $FG_TABLE_CLAUSE";
// if ($FG_DEBUG >= 4) var_dump ($list_total_destination);
// 
// 
// }//end IF nodisplay

// Using MIN(descr) as we don't expect to have multiple descriptions
// for pay types.

$QUERY = str_dbparams($DBHandle,"SELECT MIN(descr) AS descr, " .
	"format_currency(SUM(pos_credit), %1, %2) AS pos_sum, " .
	"format_currency(SUM(neg_credit), %1, %2) AS neg_sum, pay_type, ".
	"gettexti(pay_type,'C') AS pay_type_txt " .
	"FROM cc_agent_money_v WHERE " . $FG_TABLE_CLAUSE .
	" GROUP BY pay_type,descr; ",
	array(strtoupper(BASE_CURRENCY),$choose_currency));

//extract(DAY from calldate)

if (!$nodisplay){
		$res = $DBHandle->Execute($QUERY);
		IF ((!$res) &&($FG_DEBUG)) {
			echo "Query failed: " . $QUERY ."<br>" . $DBHandle->ErrorMsg();
		}
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
<td> <img src="../Images/asterisk01.jpg"/> </td>
</tr>
</table>


<?php if (!isset($card) && ($nobq !=1)){ 
	$months_combo_array = array();
	$year_actual = date("Y");
	$monthname = getmonthnames();
	for ($i=$year_actual;$i >= $year_actual-1;$i--) {
		
		if ($year_actual==$i){
			$monthnumber = date("n")-1; // Month number without lead 0.
		}else{
			$monthnumber=11;
		}
		for ($j=$monthnumber;$j>=0;$j--){
			$month_formated = sprintf("%02d",$j+1);
			$months_combo_array[]= array("$i-$month_formated","$monthname[$j]-$i");
		}
	}

	$instance_table_agent = new Table("cc_agent", "id, name");
	$FG_TABLE_CLAUSE_AG = "";
	$list_agent = $instance_table_agent -> Get_list ($DBHandle, $FG_TABLE_CLAUSE_AG, "name", "ASC", null, null, null, null);

?>
	<center>
	<FORM method=POST action="<?php echo $PHP_SELF?>?s=1&t=0&order=<?php echo $order?>&sens=<?php echo $sens?>&current_page=<?php echo $current_page?>">
	<INPUT TYPE="hidden" name="posted" value=1>
	<INPUT TYPE="hidden" name="current_page" value=0>	
	<table class="bar-status" width="95%" border="0" cellspacing="1" cellpadding="2" align="center">
	<tbody>
	<tr><td class="bar-search" align="left" color="#ffffff" bgcolor="#000033"><b><?= gettext("Agent");?></b></td>
	    <td class="bar-search" bgcolor="#cddeff" colspan="2">
		<?php gen_Combo("agent_id",$agent_id,$list_agent); ?></td>
	</tr>
	<tr><td class="bar-search" align="left" bgcolor="#555577">

		<input type="radio" name="Period" value="none" <?php  if (($Period=="none") || !isset($Period)){ ?>checked="checked" <?php  } ?>> 
		<font face="verdana" size="1" color="#ffffff"><b><?= gettext("All transactions");?></b></font>
	</td><td class="bar-search" bgcolor="#cddeff" colspan="2">&nbsp;</td></tr>
	
	
	<tr><td class="bar-search" align="left" bgcolor="#555577">

		<input type="radio" name="Period" value="Month" <?php  if ($Period=="Month"){ ?>checked="checked" <?php  } ?>> 
		<font face="verdana" size="1" color="#ffffff"><b><?= gettext("Selection of the month");?></b></font>
	</td>
	<td class="bar-search" colspan=2 align="left" bgcolor="#cddeff">
		<table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#cddeff"><tr><td>
		<input type="checkbox" name="frommonth" value="true" <?php  if ($frommonth){ ?>checked<?php }?>> 
		<?= gettext("FROM");?> :
			<?php gen_Combo("fromstatsmonth",$fromstatsmonth,$months_combo_array); ?>
		</td><td>&nbsp;&nbsp;
		<input type="checkbox" name="tomonth" value="true" <?php  if ($tomonth){ ?>checked<?php }?>> 
		<?= gettext("TO");?> : 
		<?php gen_Combo("tostatsmonth",$tostatsmonth,$months_combo_array); ?>
		</td></tr></table>
		</td>
	</tr>
			
		<tr>
		<td align="left" bgcolor="#000033">
			<input type="radio" name="Period" value="Day" <?php  if ($Period=="Day"){ ?>checked="checked" <?php  } ?>> 
			<font face="verdana" size="1" color="#ffffff"><b><?= gettext("Selection of the day");?></b></font>
			</td>
		<td align="left" colspan=2 bgcolor="#acbdee">
			<table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#acbdee"><tr><td>
			<input type="checkbox" name="fromday" value="true" <?php  if ($fromday){ ?>checked<?php }?>> <?= gettext("FROM");?> :
			<select name="fromstatsday_sday">
				<?php  
					for ($i=1;$i<=31;$i++){
						if ($fromstatsday_sday==sprintf("%02d",$i)){$selected="selected";}else{$selected="";}
						echo '<option value="'.sprintf("%02d",$i)."\"$selected>".sprintf("%02d",$i).'</option>';
					}
				?>	
			</select>
			<?php gen_Combo("fromstatsmonth_sday",$fromstatsmonth_sday,$months_combo_array); ?>
			<select name="fromstatsmonth_shour">
			<?php  
				if (strlen($fromstatsmonth_shour)==0) $fromstatsmonth_shour='0';
				for ($i=0;$i<=23;$i++){	
					if ($fromstatsmonth_shour==sprintf("%02d",$i)){$selected="selected";}else{$selected="";}						
					echo '<option value="'.sprintf("%02d",$i)."\" $selected>".sprintf("%02d",$i).'</option>';
				}
			?>
			</select>:<select name="fromstatsmonth_smin">
			<?php  
				if (strlen($fromstatsmonth_smin)==0) $fromstatsmonth_smin='0';
				for ($i=0;$i<=59;$i++){	
					if ($fromstatsmonth_smin==sprintf("%02d",$i)){$selected="selected";}else{$selected="";}						
					echo '<option value="'.sprintf("%02d",$i)."\" $selected>".sprintf("%02d",$i).'</option>';
				}
			?>
			</select>
			</td><td>&nbsp;&nbsp;
			<input type="checkbox" name="today" value="true" <?php  if ($today){ ?>checked<?php }?>> <?= gettext("TO");?> :
			<select name="tostatsday_sday">
			<?php  
				for ($i=1;$i<=31;$i++){
					if ($tostatsday_sday==sprintf("%02d",$i)){$selected="selected";}else{$selected="";}
					echo '<option value="'.sprintf("%02d",$i)."\"$selected>".sprintf("%02d",$i).'</option>';
				}
			?>						
			</select>
			<?php gen_Combo("tostatsmonth_sday",$tostatsmonth_sday,$months_combo_array); ?>
			<select name="tostatsmonth_shour">
			<?php  
				if (strlen($tostatsmonth_shour)==0) $tostatsmonth_shour='23';
				for ($i=0;$i<=23;$i++){	
					if ($tostatsmonth_shour==sprintf("%02d",$i)){$selected="selected";}else{$selected="";}
					echo '<option value="'.sprintf("%02d",$i)."\" $selected>".sprintf("%02d",$i).'</option>';
				}
			?>					
			</select>:<select name="tostatsmonth_smin">
			<?php  
				if (strlen($tostatsmonth_smin)==0) $tostatsmonth_smin='59';
				for ($i=0;$i<=59;$i++){	
					if ($tostatsmonth_smin==sprintf("%02d",$i)){$selected="selected";}else{$selected="";}						
					echo '<option value="'.sprintf("%02d",$i)."\" $selected>".sprintf("%02d",$i).'</option>';
				}
			?>					
			</select>
			</td></tr></table>
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
		<td class="bar-search-2" align="left" bgcolor="#000033"><?= _("Mode") ?> </td>

		<td class="bar-search" align="center" bgcolor="#acbdee">
			<?php echo gettext("See Invoice in HTML");?><input type="radio" NAME="exporttype" value="html" <?php if((!isset($exporttype))||($exporttype=="html")){?>checked<?php }?>>
			<!-- <?php echo gettext("or Export PDF");?> <input type="radio" NAME="exporttype" value="pdf" <?php if($exporttype=="pdf"){?>checked<?php }?>> -->
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
<td align="center"  bgcolor="#fff1d1"><font color="#000000" face="verdana" size="5"> <b><?=  _("TRANSACTIONS DETAIL");?></b> </td>
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
			if (isset($res_sums['pos_carry'])) {
				?><tr class="sum_row"><td>&nbsp;</td><td colspan=2>&nbsp;</td><td align=left><?= _("Carry from previous period")?></td>
				<td class=tableBody><?= $res_sums['pos_carry']; ?></td><td class=tableBody><?= $res_sums['neg_carry']; ?></td></tr>
				<!--<tr><td colspan=4>&nbsp;</td>
				<td colspan=2 class=tableBody align=right><?= $res_sums['all_carry']; ?></td></tr> -->
			<?php }
			
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
				<tr class="sum_row"><td colspan=4 align=right><?= _("Partial sum:")?> &nbsp; &nbsp;</td>
				<td class=tableBody><?= $res_sums['pos_sums']; ?></td><td class=tableBody><?= $res_sums['neg_sums']; ?></td></tr>
				<tr><td colspan=4>&nbsp;</td>
				<td colspan=2 class=tableBody align=right><?= $res_sums['all_sums']; ?></td></tr>
				<?php
				  }else{
				  		echo gettext("No data found !!!");
				  }//end_if
				 ?>

            </TABLE>
			
			


<!-- ** ** ** ** ** Part to display the GRAPHIC ** ** ** ** ** -->
<br><br>


<?php  }else{  ?>
	<center><h3><?=  gettext("No calls in your Selection");?>.</h3></center>
<?php  } ?>
</center>

<!-- FIN TITLE GLOBAL MINUTES //-->
		
<?php if (! $nodisplay) { ?>
<br><hr width="350"><br><br>

<style>
table.total {
	/*background-color: ; */
	width: 70%;
	border: none;
}

table.total td.hdr1 {
	text-align: center;
	background-color: #600101;
	font-weight: bold;
	color: white;
}

table.total td.hdr2 {
	text-align: center;
	background-color: #b72222;
	color: white;
	font-weight: bold;
}
table.total td.hdr3 {
	text-align: center;
	background-color: #F2E8ED;
	color: black;
	font-family: Arial,Verdana;
	font-weight: bold;
	font-size: 9pt;
}

table.total td.col0 {
	text-align: left;
	background-color: #D2D8ED;
	color: black;
	font-family: Arial,Verdana;
	font-weight: normal;
	font-size: 9pt;
}

table.total td.col1 {
	text-align: center;
	background-color: #D2D8ED;
	color: black;
	font-family: Arial,Verdana;
	font-weight: normal;
	font-size: 9pt;
}

table.total td.row0 {
	text-align: center;
	background-color: <?= $FG_TABLE_ALTERNATE_ROW_COLOR[0] ?>;
	color: black;
	font-family: Arial,Verdana;
	font-weight: normal;
	font-size: 9pt;
}
table.total td.row1 {
	text-align: center;
	background-color: <?= $FG_TABLE_ALTERNATE_ROW_COLOR[1] ?>;
	color: black;
	font-family: Arial,Verdana;
	font-weight: normal;
	font-size: 9pt;
}

table.total td.total {
	text-align: center;
	background-color: #BA5151;
	color: white;
	font-family: Arial,Verdana;
	font-weight: bold;
}

</style>
<table width="100%">
<tr>
<?php if (SHOW_ICON_INVOICE){?><td align="left"><img src="pdf-invoices/images/desktop.gif"/> </td><?php }?>
<td align="center"  bgcolor="#fff1d1"><font color="#000000" face="verdana" size="5"> <b><?=  _("MONEY SITUATION:");?> </b> </td>
</tr>
</table>
<table class="total" cellspacing="1" cellpadding="2" align="center">
	<tr><td class="hdr1" colspan="4"><?= _("TRANSACTIONS") ?></td></tr>
	<tr><td class="hdr1" colspan="2">&nbsp;</td>
	<td class="hdr2" colspan="2"><?= _("IN/OUT") ?></td>
    </tr>
	<tr><td class="hdr3"><?= _("TYPE");?></td>
	<td class="hdr3"><?= _("DESCRIPTION");?></td>
        <td class="hdr3"><?= _("IN");?></td>
	<td class="hdr3"><?= _("OUT");?></td>
	</tr>
<?php
	$i=0;
	foreach ($list_type_charge as $data){
		$i=($i+1)%2; ?>
	<tr><td class="col0"><?= $data['pay_type_txt']?></td>
	    <td class="col1"><?= $data['descr']?></td>
	    <td class="row<?= $i?>"><?= $data['pos_sum']?></td>
	    <td class="row<?= $i?>"><?= $data['neg_sum'] ?></td>
	</tr>
     <?php } ?>

	<tr><td class="hdr1" colspan="4"><?= _("SITUATION") ?></td></tr>
	<tr><td class="hdr3" colspan="2"><?= _("DESCRIPTION") ?></td> <td class="hdr3" colspan="2"><?= _("SUM") ?></td></tr>
<?php if (isset($res_sums['all_carry'])){
?>	<tr><td class="col1" colspan="2"><?= _("Total sum carried from previous period"); ?></td>
		<td class="row0" colspan=2><?= $res_sums['all_carry'] ?></td><tr><?php } ?>
<?php /* if (isset($res_sums['per_agentcharge'])){
?>	<tr><td class="col1"><?= _("Sum of charges during the period"); ?></td>
		<td class="row0"><?= $res_sums['per_agentcharge'] ?></td><tr><?php } ?>
	<tr><td class="col1"><?= _("Sum paid to us"); ?></td>
		<td class="row0"><?= $res_sums['per_agentpay'] ?></td><tr>
	<tr><td>&nbsp;</td></tr><?php */ ?>
	<tr><td class="col1" colspan="2"><?= _("Total sum credited to customers"); ?></td>
		<td class="row0" colspan=2><?= $res_sums['total_ccredit'] ?></td><tr>
<?php if (isset($res_sums['total_cdebit'])){
?>	<tr><td class="col1" colspan="2"><?= _("Total sum debited from customers"); ?></td>
		<td class="row1" colspan=2><?= $res_sums['total_cdebit'] ?></td><tr><?php } ?>
<?php if (isset($res_sums['total_cclimit'])){
?>	<tr><td class="col1" colspan="2"><?= _("Total potential debit from customers"); ?></td>
		<td class="row0" colspan=2><?= $res_sums['total_cclimit'] ?></td><tr><?php } ?>
	<tr><td class="col1" colspan="2"><?= _("Total calls made by customers"); ?></td>
		<td class="row1" colspan=2><?= $res_sums['total_calls'] ?></td><tr>
	<tr><td class="col1" colspan="2"><?= _("Wholesale price of calls"); ?></td>
		<td class="row1" colspan=2><?= $res_sums['total_calls_wh'] ?></td><tr>
	<tr><td class="col1" colspan="2"><?= _("Estimated profit from calls"); ?></td>
		<td class="row1" colspan=2><?= $res_sums['total_calls_com'] ?></td><tr>
	<tr>
		<td class="total" colspan="4"><?= _("TOTAL");?> =
<?php echo $res_sums['all_sums'];
if ($vat>0) echo  " (" .gettext("includes VAT"). "$vat %)";
if (isset($res_sums['climit'])) 
	echo "<br>". _("CREDIT LIMIT:"). '&nbsp;' . $res_sums ['climit'];
if (isset($res_sums['days_left'])) 
	echo "<br>". _("EST. DAYS LEFT:"). '&nbsp;' . $res_sums ['days_left'];

 ?></td>
	</tr>
</table>
<?php  } ?>

<?php  if($exporttype!="pdf"){
	require("PP_footer.php");
 }else{
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



}
?>
