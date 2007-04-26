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

getpost_ifset(array('customer', 'posted', 'choose_currency','exporttype', 'invoice_type'));

$customer = $_SESSION["pr_login"];
$vat = $_SESSION["vat"];

if ($invoice_type == "")
{	
	$invoice_type = 1;
}
if ($invoice_type == 1)
{
	$invoice_heading = gettext("Unbilled Summary");	
}
else
{
	$invoice_heading = gettext("Billed Summary");
}
// this variable specifie the debug type (0 => nothing, 1 => sql result, 2 => boucle checking, 3 other value checking)
$FG_DEBUG = 0;

// The variable FG_TABLE_NAME define the table name to use
$FG_TABLE_NAME="cc_call t1";

$DBHandle  = DbConnect();

// The variable Var_col would define the col that we want show in your table
// First Name of the column in the html page, second name of the field
$FG_TABLE_COL = array();

$FG_TABLE_COL[]=array (gettext("Calldate"), "starttime", "18%", "center", "SORT", "19", "", "", "", "", "", "display_dateformat");
$FG_TABLE_COL[]=array (gettext("Source"), "src", "10%", "center", "SORT", "30");
$FG_TABLE_COL[]=array (gettext("Callednumber"), "calledstation", "18%", "right", "SORT", "30", "", "", "", "", "", "");
$FG_TABLE_COL[]=array (gettext("Destination"), "destination", "18%", "center", "SORT", "30", "", "", "", "", "", "remove_prefix");
$FG_TABLE_COL[]=array (gettext("Duration"), "sessiontime", "8%", "center", "SORT", "30", "", "", "", "", "", "display_minute");

if (!(isset($customer)  &&  ($customer>0)) && !(isset($entercustomer)  &&  ($entercustomer>0))){
	$FG_TABLE_COL[]=array (gettext("Cardused"), "username", "11%", "center", "SORT", "30");
}

$FG_TABLE_COL[]=array (gettext("Cost"), "sessionbill", "9%", "center", "SORT", "30", "", "", "", "", "", "display_2bill");

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

//This variable will store the total number of column
$FG_TOTAL_TABLE_COL = $FG_NB_TABLE_COL;
if ($FG_DELETION || $FG_EDITION) $FG_TOTAL_TABLE_COL++;


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

  
if (strpos($SQLcmd, 'WHERE') > 0) { 
	$FG_TABLE_CLAUSE = substr($SQLcmd,6).$date_clause; 
}elseif (strpos($date_clause, 'AND') > 0){
	$FG_TABLE_CLAUSE = substr($date_clause,5); 
}


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

if($invoice_type == 1)
{
	$FG_TABLE_CLAUSE.="t1.starttime >(Select CASE WHEN max(cover_enddate) is NULL THEN '0001-01-01 01:00:00' ELSE max(cover_enddate) END from cc_invoices  WHERE cardid = ".$_SESSION["card_id"].")";
}
else
{
	$FG_TABLE_CLAUSE.="t1.starttime >(Select max(cover_startdate)  from cc_invoices WHERE cardid = ".$_SESSION["card_id"].") "." AND t1.stoptime <(Select max(cover_enddate) from cc_invoices WHERE cardid = ".$_SESSION["card_id"].")";
}

if (!$nodisplay){
	$list = $instance_table -> Get_list ($DBHandle, $FG_TABLE_CLAUSE, $order, $sens, null, null, $FG_LIMITE_DISPLAY, $current_page*$FG_LIMITE_DISPLAY);
}
$_SESSION["pr_sql_export"]="SELECT $FG_COL_QUERY FROM $FG_TABLE_NAME WHERE $FG_TABLE_CLAUSE";

// GROUP BY DESTINATION FOR THE INVOICE
/************************************************ DID Billing Section *********************************************/
// Fixed + Dial = 0
// Fixed = 1
// Dail = 2
// Free = 3


// Billing Type:: All DID Calls 
if($invoice_type==1)
{
	$QUERY = "SELECT t1.amount, t1.creationdate, t1.description, t3.countryname, t2.did ".
	" FROM cc_charge t1 LEFT JOIN (cc_did t2, cc_country t3 ) ON ( t1.id_cc_did = t2.id AND t2.id_cc_country = t3.id ) ".
	" WHERE (t1.chargetype = 1 OR  t1.chargetype = 2)  AND t1.id_cc_card = ".$_SESSION["card_id"].
	" AND t1.creationdate >(Select CASE  WHEN max(cover_enddate) IS NULL THEN '0001-01-01 01:00:00' ELSE max(cover_enddate) END from cc_invoices)";
}
else
{
	$QUERY = "SELECT t1.amount, t1.creationdate, t1.description, t3.countryname, t2.did ".
	" FROM cc_charge t1 LEFT JOIN (cc_did t2, cc_country t3 ) ON ( t1.id_cc_did = t2.id AND t2.id_cc_country = t3.id ) ".
	" WHERE (t1.chargetype = 1 OR t1.chargetype = 2) AND t1.id_cc_card = ".$_SESSION["card_id"].
	" AND t1.creationdate > (Select max(cover_startdate)  from cc_invoices) AND t1.creationdate <(Select max(cover_enddate) from cc_invoices) ";
}

if (!$nodisplay)
{
	$list_total_did = $instance_table->SQLExec ($DBHandle, $QUERY);
}//end IF nodisplay

/************************************************ END DID Billing Section *********************************************/

/*************************************************CHARGES SECTION START ************************************************/

// Charge Types

// Connection charge for DID setup = 1
// Monthly Charge for DID use = 2
// Subscription fee = 3
// Extra charge =  4

if ($invoice_type == 1)
{
	$QUERY = "SELECT t1.id_cc_card, t1.iduser, t1.creationdate, t1.amount, t1.chargetype, t1.id_cc_did, t1.currency, t1.description" .
	" FROM cc_charge t1, cc_card t2 WHERE (t1.chargetype <> 1 AND  t1.chargetype <> 2) " .
	" AND t2.username = '$customer' AND t1.id_cc_card = t2.id AND t1.creationdate >= (Select CASE WHEN max(cover_enddate) is NULL " .
	" THEN '0001-01-01 01:00:00' ELSE max(cover_enddate) END from cc_invoices) Order by t1.creationdate";
}
else
{
	$QUERY = "SELECT t1.id_cc_card, t1.iduser, t1.creationdate, t1.amount, t1.chargetype, t1.id_cc_did, t1.currency" .
	" FROM cc_charge t1, cc_card t2 WHERE (t1.chargetype <> 1 AND t1.chargetype <> 2) " .
	" AND t2.username = '$customer' AND t1.id_cc_card = t2.id AND " .
	" t1.creationdate >(Select max(cover_startdate)  from cc_invoices) " .
	" AND t1.creationdate <(Select max(cover_enddate) from cc_invoices)";
}

if (!$nodisplay)
{
	$list_total_charges = $instance_table->SQLExec ($DBHandle, $QUERY);
}//end IF nodisplay


/*************************************************CHARGES SECTION END ************************************************/

$QUERY = "SELECT destination, sum(t1.sessiontime) AS calltime, 
sum(t1.sessionbill) AS cost, count(*) as nbcall FROM $FG_TABLE_NAME WHERE ".$FG_TABLE_CLAUSE."  GROUP BY destination";
if (!$nodisplay)
{
	$list_total_destination = $instance_table->SQLExec ($DBHandle, $QUERY);
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
}

/************************************************************/


?>

<?php
$smarty->display( 'main.tpl');	
?>

<?php 
$currencies_list = get_currencies();
$totalcost = 0;
$totalcallmade = 0;
if (is_array($list_total_destination) && count($list_total_destination)>0)
{
	$totalcallmade = $totalcallmade + count($list_total_destination);
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
	$totalcallmade =  $totalcallmade + count($list_total_did);
	$mmax = 0;
	$totalcall = 0;
	$totalminutes = 0;
	//echo "<br>Total Cost at Dial = ".$totalcost;
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

<table  cellspacing="0" style="border-width:thin;border-color:#CCCCCC;border-style:solid;" width="95%" align="center">
     
      <tr>
        <td class="invoice_heading"><?php echo $invoice_heading; ?></td>
      </tr>
      <tr>
        <td valign="top"><table width="60%" align="left" cellpadding="0" cellspacing="0">
            <tr>
              <td width="35%">&nbsp; </td>
              <td width="65%">&nbsp; </td>
            </tr>
            <tr>
              <td width="35%" class="invoice_td"><?php echo gettext("Name")?>&nbsp; : </td>
              <td width="65%" class="invoice_td"><?php echo $info_customer[0][3] ." ".$info_customer[0][2] ?></td>
            </tr>
            <tr>
              <td width="35%" class="invoice_td"><?php echo gettext("Card Number")?>&nbsp; :</td>
              <td width="65%" class="invoice_td"><?php echo $info_customer[0][1] ?> </td>
            </tr>
            
            <tr>
              <td width="35%" class="invoice_td"><?php echo gettext("As of Date")?>&nbsp; :</td>
              <td width="65%" class="invoice_td"><?php echo date('m-d-Y');?> </td>
            </tr>            
        </table></td>
      </tr>
	  <?php if($exporttype != "pdf"){?>
	  <tr>
	  <td align="right">
	  <a href="A2B_entity_invoice_summary_pdf.php?exporttype=pdf&invoice_type=2"><img src="<?php echo Images_Path;?>/pdf.gif" height="20" width="20" title="Download as PDF."> </a>&nbsp;
	  </td>
	  </tr>
	  <?php }?>
      <tr>
        <td valign="top"><table width="100%" align="left" cellpadding="0" cellspacing="0">
            <tr class="invoice_subheading">
              <td class="invoice_td" width="40%"><?php echo gettext("Description")?> </td>
              <td width="30%" class="invoice_td">&nbsp; </td>
              <td width="30%" class="invoice_td" align="right"><?php echo gettext("Amount")." (".BASE_CURRENCY.")"; ?> </td>
            </tr>
            <tr class="invoice_rows">
              <td width="40%" class="invoice_td"><?php echo gettext("Previous Balance")?></td>
              <td width="30%" class="invoice_td">&nbsp; </td>
              <td width="30%" align="right" class="invoice_td">0.00 </td>
            </tr>
            <tr class="invoice_rows">
              <td width="40%" class="invoice_td"><?php echo gettext("Current Period Charges")?> </td>
              <td width="30%" class="invoice_td">&nbsp; </td>
              <td width="30%" align="right" class="invoice_td"><?php  															
															display_2bill($totalcost );
															 ?>
															 </td>
            </tr>
			 <tr class="invoice_rows">
              <td width="40%" class="invoice_td"><?php echo gettext("VAT")?> </td>
              <td width="30%" class="invoice_td">&nbsp; </td>
              <td width="30%" align="right" class="invoice_td"><?php  
															$prvat = ($vat / 100) * $totalcost;															
															display_2bill($prvat);
															 ?>
															 </td>
            </tr>
            <tr>
              <td  width="40%" class="invoice_td"><?php echo gettext("Total Payable Bill")?></td>
              <td width="30%" class="invoice_td">&nbsp;</td>
              <td width="30%"  align="right" class="invoice_td"><?php  
															display_2bill($totalcost + $prvat);
															 ?>
															 </td>
            </tr>
            <tr >
              <td width="40%">&nbsp;</td>
              <td width="30%">&nbsp;</td>
              <td width="30%">&nbsp; </td>
            </tr>
        </table></td>
      </tr>
	  <?php if ($exporttype != "pdf"){?>
      <tr>
        <td><table cellspacing="0" cellpadding="0">
            <tr>
              <td width="15%"><?php echo gettext("Status");?> :&nbsp; </td>
             <td width="20%"><?php if($info_customer[0][12] == 't') {?>
			  <img width="18" height="7" src="<?php echo Images_Path;?>/connected.gif">
			  <?php }
			  else
			  {
			  ?>
			  <img width="18" height="7" src="<?php echo Images_Path;?>/terminated.gif">
			  <?php }?></td>
              <td width="65%"><img width="18" height="7" src="<?php echo Images_Path;?>/connected.gif">&nbsp;<?php echo gettext("Connected");?>&nbsp;&nbsp;&nbsp;
			  <img width="22" height="7" src="<?php echo Images_Path;?>/terminated.gif">&nbsp; <?php echo gettext("DisConnected");?></td>
            </tr>
        </table></td>
      </tr>
      <tr>
        <td>&nbsp;</td>
      </tr><?php }?>
    </table>

<?php

$smarty->display( 'footer.tpl');
?>
