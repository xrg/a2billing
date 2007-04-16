<?php
include ("lib/defines.php");
include ("lib/module.access.php");
include ("lib/smarty.php");



if (!$A2B->config["webcustomerui"]['invoice']) exit();

if (! has_rights (ACX_ACCESS)){ 
	   Header ("HTTP/1.0 401 Unauthorized");
	   Header ("Location: PP_error.php?c=accessdenied");	   
	   die();	   
}

getpost_ifset(array('customer', 'posted', 'Period', 'frommonth', 'fromstatsmonth', 'tomonth', 'tostatsmonth', 'fromday', 'fromstatsday_sday', 'fromstatsmonth_sday', 'today', 'tostatsday_sday', 'tostatsmonth_sday', 'dsttype', 'sourcetype', 'clidtype', 'channel', 'resulttype', 'stitle', 'atmenu', 'current_page', 'order', 'sens', 'dst', 'src', 'clid', 'fromstatsmonth_sday', 'fromstatsmonth_shour', 'tostatsmonth_sday', 'tostatsmonth_shour', 'srctype', 'src', 'choose_currency','exporttype'));

$customer = $_SESSION["pr_login"];
$vat = $_SESSION["vat"];
//require (LANGUAGE_DIR.FILENAME_INVOICES);

if ($exporttype=="pdf") 
{	
	header("Content-Type: application/octet-stream");
	header("Content-Disposition: attachment; filename=BilledSummary_".date("d/m/Y-H:i").'.pdf');
//	header("Content-Length: ".filesize($dl_full));
	header("Accept-Ranges: bytes");
	header("Pragma: no-cache");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Content-transfer-encoding: binary");
}
if (!isset ($current_page) || ($current_page == "")){	
		$current_page=0; 
	}
// this variable specifie the debug type (0 => nothing, 1 => sql result, 2 => boucle checking, 3 other value checking)
$FG_DEBUG = 0;

// The variable FG_TABLE_NAME define the table name to use
$FG_TABLE_NAME="cc_call t1";

// THIS VARIABLE DEFINE THE COLOR OF THE HEAD TABLE
$FG_TABLE_HEAD_COLOR = "#D1D9E7";

$FG_TABLE_EXTERN_COLOR = "#7F99CC"; //#CC0033 (Rouge)
$FG_TABLE_INTERN_COLOR = "#EDF3FF"; //#FFEAFF (Rose)

// THIS VARIABLE DEFINE THE COLOR OF THE HEAD TABLE
$FG_TABLE_ALTERNATE_ROW_COLOR[] = "#FFFFFF";
$FG_TABLE_ALTERNATE_ROW_COLOR[] = "#F2F8FF";

$yesno = array(); 	$yesno["1"]  = array( "Yes", "1");	 $yesno["0"]  = array( "No", "0");

$DBHandle  = DbConnect();

// The variable Var_col would define the col that we want show in your table
// First Name of the column in the html page, second name of the field
$FG_TABLE_COL = array();


/*******
Calldate Clid Src Dst Dcontext Channel Dstchannel Lastapp Lastdata Duration Billsec Disposition Amaflags Accountcode Uniqueid Serverid
*******/

$FG_TABLE_COL[]=array (gettext("Calldate"), "starttime", "18%", "center", "SORT", "19", "", "", "", "", "", "display_dateformat");
$FG_TABLE_COL[]=array (gettext("Source"), "src", "10%", "center", "SORT", "30");
$FG_TABLE_COL[]=array (gettext("Callednumber"), "calledstation", "18%", "right", "SORT", "30", "", "", "", "", "", "");
$FG_TABLE_COL[]=array (gettext("Destination"), "destination", "18%", "center", "SORT", "30", "", "", "", "", "", "remove_prefix");
$FG_TABLE_COL[]=array (gettext("Duration"), "sessiontime", "8%", "center", "SORT", "30", "", "", "", "", "", "display_minute");

if (!(isset($customer)  &&  ($customer>0)) && !(isset($entercustomer)  &&  ($entercustomer>0))){
	$FG_TABLE_COL[]=array (gettext("Cardused"), "username", "11%", "center", "SORT", "30");
}

//if ($_SESSION["is_admin"]==1) $FG_TABLE_COL[]=array ("Con_charg", "connectcharge", "12%", "center", "SORT", "30");
//if ($_SESSION["is_admin"]==1) $FG_TABLE_COL[]=array ("Dis_charg", "disconnectcharge", "12%", "center", "SORT", "30");
//if ($_SESSION["is_admin"]==1) $FG_TABLE_COL[]=array ("Sec/mn", "secpermin", "12%", "center", "SORT", "30");


//if ($_SESSION["is_admin"]==1) $FG_TABLE_COL[]=array ("Buycosts", "buycosts", "12%", "center", "SORT", "30");
//-- $FG_TABLE_COL[]=array ("InitialRate", "calledrate", "10%", "center", "SORT", "30", "", "", "", "", "", "display_2dec");
$FG_TABLE_COL[]=array (gettext("Cost"), "sessionbill", "9%", "center", "SORT", "30", "", "", "", "", "", "display_2bill");

//-- if (LINK_AUDIO_FILE == 'YES') 
//-- 	$FG_TABLE_COL[]=array ("", "uniqueid", "1%", "center", "", "30", "", "", "", "", "", "linkonmonitorfile");

// ??? cardID
$FG_TABLE_DEFAULT_ORDER = "t1.starttime";
$FG_TABLE_DEFAULT_SENS = "DESC";
	
// This Variable store the argument for the SQL query

$FG_COL_QUERY='t1.starttime, t1.src, t1.calledstation, t1.destination, t1.sessiontime  ';
if (!(isset($customer)  &&  ($customer>0)) && !(isset($entercustomer)  &&  ($entercustomer>0))){
	$FG_COL_QUERY.=', t1.username';
}
$FG_COL_QUERY.=', t1.sessionbill';
if (LINK_AUDIO_FILE == 'YES') 
	$FG_COL_QUERY .= ', t1.uniqueid';

$FG_COL_QUERY_GRAPH='t1.callstart, t1.duration';

// The variable LIMITE_DISPLAY define the limit of record to display by page
$FG_LIMITE_DISPLAY=500;

// Number of column in the html table
$FG_NB_TABLE_COL=count($FG_TABLE_COL);

// The variable $FG_EDITION define if you want process to the edition of the database record
$FG_EDITION=true;

//This variable will store the total number of column
$FG_TOTAL_TABLE_COL = $FG_NB_TABLE_COL;
if ($FG_DELETION || $FG_EDITION) $FG_TOTAL_TABLE_COL++;

//This variable define the Title of the HTML table
$FG_HTML_TABLE_TITLE=" - Call Logs - ";

//This variable define the width of the HTML table
$FG_HTML_TABLE_WIDTH="70%";

	if ($FG_DEBUG == 3) echo "<br>Table : $FG_TABLE_NAME  	- 	Col_query : $FG_COL_QUERY";
	$instance_table = new Table($FG_TABLE_NAME, $FG_COL_QUERY);
	$instance_table_graph = new Table($FG_TABLE_NAME, $FG_COL_QUERY_GRAPH);


if ( is_null ($order) || is_null($sens) ){
	$order = $FG_TABLE_DEFAULT_ORDER;
	$sens  = $FG_TABLE_DEFAULT_SENS;
}

if ($posted==1){
  
  function do_field($sql,$fld,$dbfld){
  		$fldtype = $fld.'type';
		global $$fld;
		global $$fldtype;		
        if ($$fld){
                if (strpos($sql,'WHERE') > 0){
                        $sql = "$sql AND ";
                }else{
                        $sql = "$sql WHERE ";
                }
				$sql = "$sql t1.$dbfld";
				if (isset ($$fldtype)){                
                        switch ($$fldtype) {							
							case 1:	$sql = "$sql='".$$fld."'";  break;
							case 2: $sql = "$sql LIKE '".$$fld."%'";  break;
							case 3: $sql = "$sql LIKE '%".$$fld."%'";  break;
							case 4: $sql = "$sql LIKE '%".$$fld."'";  break;
							case 5:	$sql = "$sql <> '".$$fld."'";  
						}
                }else{ $sql = "$sql LIKE '%".$$fld."%'"; }
		}
        return $sql;
  }  
  $SQLcmd = '';
  
  $SQLcmd = do_field($SQLcmd, 'src', 'src');
  $SQLcmd = do_field($SQLcmd, 'dst', 'calledstation');
}

$date_clause='';
// Period (Month-Day)
if (DB_TYPE == "postgres"){		
	 	$UNIX_TIMESTAMP = "";
}else{
		$UNIX_TIMESTAMP = "UNIX_TIMESTAMP";
}

$lastdayofmonth = date("t", strtotime($tostatsmonth.'-01'));

if (isset($customer)  &&  ($customer>0)){
	if (strlen($FG_TABLE_CLAUSE)>0) $FG_TABLE_CLAUSE.=" AND ";
	$FG_TABLE_CLAUSE.="t1.username='$customer'";
}else{
	if (isset($entercustomer)  &&  ($entercustomer>0)){
		if (strlen($FG_TABLE_CLAUSE)>0) $FG_TABLE_CLAUSE.=" AND ";
		$FG_TABLE_CLAUSE.="t1.username='$entercustomer'";
	}
}


if (strlen($FG_TABLE_CLAUSE)>0)
{
	$FG_TABLE_CLAUSE.=" AND ";
}
$FG_TABLE_CLAUSE.="t1.starttime >(Select max(cover_startdate)  from cc_invoices) AND t1.stoptime <(Select max(cover_enddate) from cc_invoices) ";
if (!$nodisplay){
	$list = $instance_table -> Get_list ($DBHandle, $FG_TABLE_CLAUSE, $order, $sens, null, null, $FG_LIMITE_DISPLAY, $current_page*$FG_LIMITE_DISPLAY);
}
$_SESSION["pr_sql_export"]="SELECT $FG_COL_QUERY FROM $FG_TABLE_NAME WHERE $FG_TABLE_CLAUSE";

// GROUP BY DESTINATION FOR THE INVOICE

$QUERY = "SELECT destination, sum(t1.sessiontime) AS calltime, 
sum(t1.sessionbill) AS cost, count(*) as nbcall FROM $FG_TABLE_NAME WHERE ".$FG_TABLE_CLAUSE."  GROUP BY destination";

if (!$nodisplay){
		$res = $DBHandle -> Execute($QUERY);
		if ($res){
			$num = $res -> RecordCount();
			for($i=0;$i<$num;$i++)
			{				
				$list_total_destination [] =$res -> fetchRow();				 
			}
		}

if ($FG_DEBUG == 3) echo "<br>Clause : $FG_TABLE_CLAUSE";
if ($FG_DEBUG >= 1) var_dump ($list_total_destination);


}//end IF nodisplay


/************************************************ DID Billing Section *********************************************/
// Fixed + Dial = 0
// Fixed = 1
// Dail = 2
// Free = 3


// Billing Type:: All DID Calls 

$QUERY = "SELECT t1.amount, t1.creationdate, t1.description, t3.countryname, t2.did ".
" FROM cc_charge t1 LEFT JOIN (cc_did t2, cc_country t3 ) ON ( t1.id_cc_did = t2.id AND t2.id_cc_country = t3.id ) ".
" WHERE t1.chargetype = 2 AND t1.id_cc_card = ".$_SESSION["card_id"].
" AND t1.creationdate > (Select max(cover_startdate)  from cc_invoices) AND t1.creationdate <(Select max(cover_enddate) from cc_invoices) ";

 
if (!$nodisplay)
{
	$res = $DBHandle -> Execute($QUERY);
	if ($res){
		$num = $res -> RecordCount();
		for($i=0;$i<$num;$i++)
		{					
			$list_total_did [] =$res -> fetchRow();
		}
	}
	
	if ($FG_DEBUG >= 1) var_dump ($list_total_did);
}//end IF nodisplay


/************************************************ END DID Billing Section *********************************************/

/*************************************************CHARGES SECTION START ************************************************/

// Charge Types

// Connection charge for DID setup = 1
// Monthly Charge for DID use = 2
// Subscription fee = 3
// Extra charge =  4
if($choose_billperiod == "")
{	
	$QUERY = "SELECT t1.id_cc_card, t1.iduser, t1.creationdate, t1.amount, t1.chargetype, t1.id_cc_did, t1.currency" .
	" FROM cc_charge t1, cc_card t2 WHERE t1.chargetype <> 2" .
	" AND t2.username = '$customer' AND t1.id_cc_card = t2.id AND " .
	" t1.creationdate >(Select max(cover_startdate)  from cc_invoices) " .
	" AND t1.creationdate <(Select max(cover_enddate) from cc_invoices)";
}
else
{
	$QUERY = "SELECT t1.id_cc_card, t1.iduser, t1.creationdate, t1.amount, t1.chargetype, t1.id_cc_did, t1.currency" .
	" FROM cc_charge t1, cc_card t2 WHERE t1.chargetype <> 2" .
	" AND t2.username = '$customer' AND t1.id_cc_card = t2.id AND " .
	" t1.creationdate >(Select cover_startdate  from cc_invoices where invoicecreated_date ='$choose_billperiod') " .
	" AND t1.creationdate <(Select cover_enddate from cc_invoices where invoicecreated_date ='$choose_billperiod')";
}
//echo "<br>".$QUERY."<br>";

if (!$nodisplay)
{
	$res = $DBHandle -> Execute($QUERY);
	if ($res){
		$num = $res -> RecordCount();
		for($i=0;$i<$num;$i++)
		{
			$list_total_charges [] =$res -> fetchRow();
		}
	}
	
	if ($FG_DEBUG >= 1) var_dump ($list_total_charges);
}//end IF nodisplay


/*************************************************CHARGES SECTION END ************************************************/


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

/*************************************************************/

if ((isset($customer)  &&  ($customer>0)) || (isset($entercustomer)  &&  ($entercustomer>0))){

	$FG_TABLE_CLAUSE = "";
	if (isset($customer)  &&  ($customer>0)){		
		$FG_TABLE_CLAUSE =" username='$customer' ";
	}elseif (isset($entercustomer)  &&  ($entercustomer>0)){
		$FG_TABLE_CLAUSE =" username='$entercustomer' ";
	}
	$instance_table_customer = new Table("cc_card", "id,  username, lastname, firstname, address, city, state, country, zipcode, phone, email, fax, activated");
	$info_customer = $instance_table_customer -> Get_list ($DBHandle, $FG_TABLE_CLAUSE, "id", "ASC", null, null, null, null);
	// if (count($info_customer)>0){
}
/************************************************************/
$QUERY = "Select t1.invoicecreated_date from cc_invoices t1, cc_card t2 where t2.id = t1.cardid and t2.username = '$customer' order by t1.invoicecreated_date";

$res = $DBHandle -> Execute($QUERY);
if ($res){
	$total_invoices = $res -> RecordCount();
	if ($total_invoices > 0)
	{
		$billperiod_list = $res->fetchRow();
	}
}

?>


<?php
//$smarty->display( 'main.tpl');
if($exporttype == "pdf")
{
	require('pdf-invoices/html2pdf/html2fpdf.php');
   	ob_start();
}

?>
<?php 
$currencies_list = get_currencies();
$totalcost = 0;
if (is_array($list_total_destination) && count($list_total_destination)>0)
{

	$mmax = 0;
	$totalcall = 0;
	$totalminutes = 0;	
	foreach ($list_total_destination as $data)
	{	
		if ($mmax < $data[1])
		{	
			$mmax=$data[1];
		}
		$totalcall+=$data[3];
		$totalminutes+=$data[1];
		$totalcost+=$data[2];
	}

}


//For DID Calls
if (is_array($list_total_did) && count($list_total_did)>0)
{
	foreach ($list_total_did as $data)
	{	
		$totalcost += $data[0];
	}
}
//For Extra Charges
$extracharge_total = 0;
if (is_array($list_total_charges) && count($list_total_charges)>0)
{	
	foreach ($list_total_charges as $data)
	{		
		
		$extracharge_total = $extracharge_total + convert_currency($currencies_list,$data[3], $data[6], BASE_CURRENCY) ;
	}	
}
$totalcost = $totalcost + $extracharge_total;
?>
<?php if ($total_invoices > 0)
		{
		?>
		
		
<table cellpadding="0"  align="center">
<tr>
<td align="center">
<img src="<?php echo Images_Path;?>/asterisk01.jpg" align="middle">
</td>
</tr>
</table>
<br>
<center><h4><font color="#FF0000"><?php echo gettext("Billed Invoice Summary for Card Number")?>&nbsp;<?php echo $info_customer[0][1] ?> </font></h4></center>
<br>
<br>
<table  cellspacing="0" class="invoice_main_table" width="80%" align="center">
     
      <tr>
        <td bgcolor="#FFFFCC" colspan="2"><font size="5" color="#FF0000"><?php echo gettext("Bill Summary")?></font></td>
      </tr>
      <tr>
              <td width="35%">&nbsp; </td>
              <td >&nbsp; </td>
            </tr>
            <tr>
              <td width="35%" ><font color="#003399" size="2"><?php echo gettext("Name")?>&nbsp; : </font></td>
              <td  ><font color="#003399" size="2"><?php echo $info_customer[0][3] ." ".$info_customer[0][2] ?></font></td>
            </tr>
            <tr>
              <td width="35%" ><font color="#003399" size="2"><?php echo gettext("Card Number")?>&nbsp; :</font></td>
              <td  ><font color="#003399" size="2"><?php echo $info_customer[0][1] ?> </font></td>
            </tr>
           
            <tr>
              <td width="35%" ><font color="#003399" size="2"><?php echo gettext("As of Date")?>&nbsp; :</font></td>
              <td  ><font color="#003399" size="2"><?php echo date("d/m/Y", strtotime($billperiod_list[0]));?></font> </td>
            </tr>
            <tr>
              <td colspan="2">&nbsp; </td>
            </tr>
</table>
	<table  cellspacing="0"  width="80%" align="center">			
	  <?php if($exporttype != "pdf"){?>
	  <tr>
	  <td align="right" colspan="3">
	  <a href="A2B_entity_billed_summary1.php?exporttype=pdf"><img src="<?php echo Images_Path;?>/pdf.gif" height="20" width="20" title="Download as PDF."> </a>&nbsp;
	  </td>
	  </tr>
	  <?php }?>
      <tr bgcolor="#CCCCCC">
              <td  width="37%"><font color="#003399" size="2"><?php echo gettext("Description")?></font></td>
              <td width="23%" >&nbsp; </td>
              <td align="right"><font color="#003399" size="2"><?php echo gettext("Amount (US $)")?> </font></td>
            </tr>
            <tr class="invoice_rows">
              <td width="37%" ><font color="#003399" size="2"><?php echo gettext("Previous Balance")?></font></td>
              <td width="23%" >&nbsp; </td>
              <td  align="right" ><font color="#003399" size="2">0.00 </font></td>
            </tr>
            <tr class="invoice_rows">
              <td width="37%" ><font color="#003399" size="2"><?php echo gettext("Current Period Charges")?> </font></td>
              <td width="23%" >&nbsp; </td>
              <td  align="right" ><font color="#003399" size="2"><?php  
															display_2bill($totalcost);
															 ?>
															</font> </td>
            </tr>
			<tr class="invoice_rows">
              <td width="37%" ><font color="#003399" size="2"><?php echo gettext("VAT")?> </font></td>
              <td width="23%" >&nbsp; </td>
			  <td  align="right" ><font color="#003399" size="2"><?php  
															$prvat = ($vat / 100) * $totalcost;
															display_2bill($prvat);
															 ?>
															</font> </td>
            </tr>
            <tr bgcolor="#CCCCCC">
              <td  width="37%" ><font color="#003399" size="2"><?php echo gettext("Total Payable Bill")?></font></td>
              <td width="23%" >&nbsp;</td>
              <td   align="right" ><font color="#003399" size="2"><?php  
															display_2bill($totalcost + $prvat);
														   ?>
															</font> </td>
            </tr>
            <tr >
              <td width="37%">&nbsp;</td>
              <td width="23%">&nbsp;</td>
              <td >&nbsp; </td>
            </tr>
	       
    </table>
	<br>
<table cellspacing="0" cellpadding="2" width="80%" align="center">
<tr>
			<td colspan="3">&nbsp;</td>
			</tr>           			
			<tr>
              <td  align="left"><font color="#003399" size="2"><?php echo gettext("Status")?>&nbsp; :&nbsp;<?php if($info_customer[0][12] == 't') {?>
			  <img src="<?php echo Images_Path;?>/connected.jpg">
			  <?php }
			  else
			  {
			  ?>
			  <img src="<?php echo Images_Path;?>/terminated.jpg">
			  <?php }?></font> </td>              
            </tr>      
      <tr>	  
	  <td  align="left"><font color="#003399" size="2">&nbsp; <img src="<?php echo Images_Path;?>/connected.jpg"> &nbsp; <?php echo gettext("Connected")?>
	  &nbsp;&nbsp;&nbsp;<img src="<?php echo Images_Path;?>/terminated.jpg">&nbsp;<?php echo gettext("Disconnected")?> 
	  </font>
	  
	  </td>
</table>
	<?php	
	}	
	else
	{
	?>
	<table  cellspacing="0" class="invoice_main_table">
     
      <tr>
        <td class="invoice_heading"><?php echo gettext("Bill Details")?></td>
      </tr>	  
	 <tr>
	 <td>&nbsp;</td>
	 </tr> 
	  <tr>
	 <td align="center"><?php echo gettext("No invoice is billed to you yet!")?></td>
	 </tr> 
	  <tr>
	 <td>&nbsp;</td>
	 </tr> 
	 </table>
	<?php } ?>
	
	

<?php  if($exporttype!="pdf"){ ?>

<?php
//$smarty->display( 'footer.tpl');
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
	
	$pdf->Output('BilledSummary_'.date("d/m/Y-H:i").'.pdf'.date("d/m/Y-H:i").'.pdf', 'I');



} ?>
