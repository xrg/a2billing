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

getpost_ifset(array('customer', 'posted', 'Period', 'exporttype', 'choose_billperiod'));

$customer = $_SESSION["pr_login"];
$vat = $_SESSION["vat"];
//require (LANGUAGE_DIR.FILENAME_INVOICES);

if ($exporttype=="pdf") 
{	
	header("Content-Type: application/octet-stream");
	header("Content-Disposition: attachment; filename=BilledDetails_".date("d/m/Y-H:i").'.pdf');
//	header("Content-Length: ".filesize($dl_full));
	header("Accept-Ranges: bytes");
	header("Pragma: no-cache");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Content-transfer-encoding: binary");
			
	//@readfile($dl_full);
	
	//exit();

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

if ($Period=="Month"){
		
		
		if ($frommonth && isset($fromstatsmonth)) $date_clause.=" AND $UNIX_TIMESTAMP(t1.starttime) >= $UNIX_TIMESTAMP('$fromstatsmonth-01')";
		if ($tomonth && isset($tostatsmonth)) $date_clause.=" AND $UNIX_TIMESTAMP(t1.starttime) <= $UNIX_TIMESTAMP('".$tostatsmonth."-$lastdayofmonth 23:59:59')"; 
		
}else{
		if ($fromday && isset($fromstatsday_sday) && isset($fromstatsmonth_sday) && isset($fromstatsmonth_shour) && isset($fromstatsmonth_smin) ) $date_clause.=" AND $UNIX_TIMESTAMP(t1.starttime) >= $UNIX_TIMESTAMP('$fromstatsmonth_sday-$fromstatsday_sday $fromstatsmonth_shour:$fromstatsmonth_smin')";
		if ($today && isset($tostatsday_sday) && isset($tostatsmonth_sday) && isset($tostatsmonth_shour) && isset($tostatsmonth_smin)) $date_clause.=" AND $UNIX_TIMESTAMP(t1.starttime) <= $UNIX_TIMESTAMP('$tostatsmonth_sday-".sprintf("%02d",intval($tostatsday_sday))." $tostatsmonth_shour:$tostatsmonth_smin')";
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
if($choose_billperiod == "")
{
	$FG_TABLE_CLAUSE.="t1.starttime >(Select max(cover_startdate)  from cc_invoices) AND t1.stoptime <(Select max(cover_enddate) from cc_invoices) ";
}
else
{
	$FG_TABLE_CLAUSE.="t1.starttime >(Select cover_startdate  from cc_invoices where invoicecreated_date ='$choose_billperiod') AND t1.stoptime <(Select cover_enddate from cc_invoices where invoicecreated_date ='$choose_billperiod') ";
}

if (!$nodisplay){
	$list = $instance_table -> Get_list ($DBHandle, $FG_TABLE_CLAUSE, $order, $sens, null, null, $FG_LIMITE_DISPLAY, $current_page*$FG_LIMITE_DISPLAY);
}
$_SESSION["pr_sql_export"]="SELECT $FG_COL_QUERY FROM $FG_TABLE_NAME WHERE $FG_TABLE_CLAUSE";

/************************/
//$QUERY = "SELECT substring(calldate,1,10) AS day, sum(duration) AS calltime, count(*) as nbcall FROM cdr WHERE ".$FG_TABLE_CLAUSE." GROUP BY substring(calldate,1,10)"; //extract(DAY from calldate)


$QUERY = "SELECT substring(t1.starttime,1,10) AS day, sum(t1.sessiontime) AS calltime, sum(t1.sessionbill) AS cost, count(*) as nbcall FROM $FG_TABLE_NAME WHERE ".$FG_TABLE_CLAUSE."  AND t1.sipiax not in (2,3) GROUP BY substring(t1.starttime,1,10) ORDER BY day"; //extract(DAY from calldate)
//echo "$QUERY";


if (!$nodisplay){
		$res = $DBHandle -> Execute($QUERY);
		if ($res){
			$num = $res -> RecordCount();
			for($i=0;$i<$num;$i++)
			{				
				$list_total_day [] =$res -> fetchRow();				 
			}
		}



if ($FG_DEBUG == 3) echo "<br>Clause : $FG_TABLE_CLAUSE";
$nb_record = $instance_table -> Table_count ($DBHandle, $FG_TABLE_CLAUSE);
if ($FG_DEBUG >= 1) var_dump ($list);

}//end IF nodisplay


// GROUP BY DESTINATION FOR THE INVOICE


$QUERY = "SELECT destination, sum(t1.sessiontime) AS calltime, 
sum(t1.sessionbill) AS cost, count(*) as nbcall FROM $FG_TABLE_NAME WHERE ".$FG_TABLE_CLAUSE."  AND t1.sipiax not in (2,3) GROUP BY destination";

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


// 1. Billing Type:: All DID Calls that have DID Type 0 and 2

$QUERY = "SELECT t1.id_did, t2.fixrate, t2.billingtype, sum(t1.sessiontime) AS calltime, 
 sum(t1.sessionbill) AS cost, count(*) as nbcall FROM cc_call t1, cc_did t2 WHERE ".$FG_TABLE_CLAUSE." 
 AND t1.sipiax in (2,3) AND t1.id_did = t2.id GROUP BY t1.id_did ORDER BY t2.billingtype";
 
if (!$nodisplay)
{
	$res = $DBHandle -> Execute($QUERY);	
	if ($res){
		$num = $res -> RecordCount();
		for($i=0; $i<$num; $i++)
		{					
			$list_total_did [] =$res -> fetchRow();
		}
	}	
	if ($FG_DEBUG >= 1) var_dump ($list_total_did);
}//end IF nodisplay

/************************************************ END DID Billing Section *********************************************/


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


/*******************   TOTAL COSTS  *****************************************

$instance_table_cost = new Table($FG_TABLE_NAME, "sum(t1.costs), sum(t1.buycosts)");		
if (!$nodisplay){	
	$total_cost = $instance_table_cost -> Get_list ($DBHandle, $FG_TABLE_CLAUSE, null, null, null, null, null, null);
}
*/



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


$date_clause='';

if ($Period=="Month"){		
		if ($frommonth && isset($fromstatsmonth)) $date_clause.=" AND $UNIX_TIMESTAMP(t1.creationdate) >= $UNIX_TIMESTAMP('$fromstatsmonth-01')";
		if ($tomonth && isset($tostatsmonth)) $date_clause.=" AND  $UNIX_TIMESTAMP(t1.creationdate) <= $UNIX_TIMESTAMP('".$tostatsmonth."-$lastdayofmonth 23:59:59')"; 
}else{
		if ($fromday && isset($fromstatsday_sday) && isset($fromstatsmonth_sday) && isset($fromstatsmonth_shour) && isset($fromstatsmonth_smin) ) $date_clause.=" AND  $UNIX_TIMESTAMP(t1.creationdate) >= $UNIX_TIMESTAMP('$fromstatsmonth_sday-$fromstatsday_sday $fromstatsmonth_shour:$fromstatsmonth_smin')";
		if ($today && isset($tostatsday_sday) && isset($tostatsmonth_sday) && isset($tostatsmonth_shour) && isset($tostatsmonth_smin)) $date_clause.=" AND  $UNIX_TIMESTAMP(t1.creationdate) <= $UNIX_TIMESTAMP('$tostatsmonth_sday-".sprintf("%02d",intval($tostatsday_sday))." $tostatsmonth_shour:$tostatsmonth_smin')";
}


$QUERY = "SELECT substring(t1.creationdate,1,10) AS day, sum(t1.amount) AS cost, count(*) as nbcharge FROM cc_charge t1 ".
		 " WHERE id_cc_card='".$_SESSION["card_id"]."' $date_clause GROUP BY substring(t1.creationdate,1,10) ORDER BY day"; //extract(DAY from calldate)


if (!$nodisplay){
		$res = $DBHandle -> Execute($QUERY);
		if ($res){
			$num = $res -> RecordCount();
			for($i=0;$i<$num;$i++)
			{				
				$list_total_day_charge [] =$res -> fetchRow();				 
			}
		}
		if ($FG_DEBUG >= 1) var_dump ($list_total_day_charge);
}//end IF nodisplay


$QUERY = "Select t1.invoicecreated_date from cc_invoices t1, cc_card t2 where t2.id = t1.cardid and t2.username = '$customer' order by t1.invoicecreated_date DESC";

$res = $DBHandle -> Execute($QUERY);
if ($res){	
	$total_invoices = $res -> RecordCount();
	if ($total_invoices > 0)
	{
		$billperiod_list = $res;
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

$currencies_list = get_currencies();

//For DID DIAL & Fixed + Dial
$totalcost = 0;
if (is_array($list_total_did) && count($list_total_did)>0)
{
	$mmax = 0;
	$totalcall_did = 0;
	$totalminutes_did = 0;
	//echo "<br>Total Cost at Dial = ".$totalcost;
	foreach ($list_total_did as $data)
	{	
		if ($mmax < $data[3])
		{
			$mmax = $data[3];
		}
		$totalcall_did += $data[5];
		$totalminutes_did += $data[3];		
		if ($data[2] == 0)
		{			
			$totalcost += ($data[4] + $data[1]);
			//echo "<br>DID =".$data[0]."; Fixed Cost=".$data[1]."; Total Call Cost=".$data[4]."; Total = ".$totalcost;
		}
		if ($data[2] == 2)
		{				
			$totalcost += $data[4];
			//echo "<br>DID =".$data[0]."; Fixed Cost=0; Total Call Cost=".$data[4]."; Total = ".$totalcost;
		}
		if ($data[2] == 1)
		{			
			$totalcost += ($data[1]);
			//echo "<br>DID =".$data[0]."; Fixed Cost=".$data[1]."; Total = ".$totalcost;
		}
		if ($data[2] == 3)
		{
			$totalcost += 0;
			//echo "<br>DID =".$data[0]."; TYPE = FREE; Total = ".$totalcost;
		}
	}	
}

	$totalcost_did = $totalcost;

	if (is_array($list_total_destination) && count($list_total_destination)>0){
	$mmax=0;
	$totalcall=0;
	$totalminutes=0;

	foreach ($list_total_destination as $data){	
		if ($mmax < $data[1]) $mmax=$data[1];
		$totalcall+=$data[3];
		$totalminutes+=$data[1];
		$totalcost+=$data[2];
	
	}

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
<center>
  <h4><font color="#FF0000">Billed</font><font color="#FF0000"> Details for Card Number&nbsp;<?php echo $info_customer[0][1] ?> </font></h4>
</center>
<br>
<br>
	<table  cellspacing="0"  width="80%" align="center">
     
      <tr>
        <td bgcolor="#FFFFCC" colspan="2"><font size="5" color="#FF0000">Bill Details</font></td>
      </tr>
      <tr>
              <td width="35%">&nbsp; </td>
              <td width="65%">&nbsp; </td>
      </tr>
            <tr>
              <td width="35%" class="invoice_td"><font color="#003399">Name : </font></td>
              <td width="65%" class="invoice_td"><font color="#003399"><?php echo $info_customer[0][3] ." ".$info_customer[0][2] ?></font></td>
            </tr>
            <tr>
              <td width="35%" class="invoice_td"><font color="#003399">Card Number :</font></td>
              <td width="65%" class="invoice_td"><font color="#003399"><?php echo $info_customer[0][1] ?> </font></td>
            </tr>            
            <tr>
              <td width="35%" class="invoice_td"><font color="#003399">As of Date :</font></td>
              <td width="65%" class="invoice_td"><font color="#003399"><?php echo date('m-d-Y');?></font> </td>
            </tr>
			<tr>
              <td width="35%" class="invoice_td" valign="middle"><font color="#003399">Billing Period :</font></td>
              <td width="65%" class="invoice_td" valign="middle"><font color="#003399"><?php 
			  if ($choose_billperiod == "")
			  {
			  		$row1 = $billperiod_list->fetchRow();
					echo date('m-d-Y', strtotime($row1[0]));
			  }
			  else
			  {
			  	echo date('m-d-Y', strtotime($choose_billperiod));
			  }
			  ?>
			  </font>  
		</td>			 
            </tr>
            <tr>
              <td colspan="2">&nbsp; </td>
            </tr>
			
		</table>
			<table width="80%" align="center" cellpadding="0" cellspacing="0">
   				<tr>
				<td colspan="5" align="center"><font><b>By Destination</b></font> </td>
				</tr>

			<tr bgcolor="#CCCCCC">
              <td class="invoice_td" width="29%"><font color="#003399"><b>Destination</b></font> </td>
              <td width="27%" class="invoice_td"><font color="#003399"><b>Duration </b></font></td>
			
			  <td width="17%" class="invoice_td"><font color="#003399"><b>Calls </b></font></td>
              <td width="27%" class="invoice_td" align="right"><font color="#003399"><b>Amount (US $)</b></font> </td>
            </tr>
			<?php  		
				$i=0;
				foreach ($list_total_destination as $data){	
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
            <tr class="invoice_rows">
              <td width="29%" class="invoice_td"><font color="#003399"><?php echo $data[0]?></font></td>
              <td width="27%" class="invoice_td"><font color="#003399"><?php echo $minutes?> </font></td>
			  
			  <td width="17%" class="invoice_td"><font color="#003399"><?php echo $data[3]?></font> </td>
              <td width="27%" align="right" class="invoice_td"><font color="#003399"><?php  display_2bill($data[2]) ?></font></td>
            </tr>
			<?php 	 }	 	 	
	 	
			if ((!isset($resulttype)) || ($resulttype=="min")){  
				$total_tmc = sprintf("%02d",intval(($totalminutes/$totalcall)/60)).":".sprintf("%02d",intval(($totalminutes/$totalcall)%60));				
				$totalminutes = sprintf("%02d",intval($totalminutes/60)).":".sprintf("%02d",intval($totalminutes%60));
			}else{
				$total_tmc = intval($totalminutes/$totalcall);			
			}
			 ?>   
			 <tr >
              <td width="29%" class="invoice_td">&nbsp;</td>
              <td width="27%" class="invoice_td">&nbsp;</td>

			  <td width="17%" class="invoice_td">&nbsp; </td>
			  <td width="27%" class="invoice_td">&nbsp; </td>
			  
            </tr>
            <tr bgcolor="#CCCCCC">
              <td width="29%" class="invoice_td"><font color="#003399"><?php echo gettext("TOTAL");?> </font></td>
              <td width="27%" class="invoice_td" ><font color="#003399"><?php echo $totalminutes?></font></td>			  
			  <td width="17%" class="invoice_td"><font color="#003399"><?php echo $totalcall?></font> </td>
              <td width="27%" align="right" class="invoice_td"><font color="#003399"><?php  display_2bill($totalcost -$totalcost_did) ?> </font></td>
            </tr>            
            <tr >
              <td width="29%">&nbsp;</td>
              <td width="27%">&nbsp;</td>
			  <td width="17%">&nbsp; </td>
			  <td width="27%">&nbsp; </td>
			  
            </tr>			
			</table>	    
		    <table width="80%" align="center" cellpadding="0" cellspacing="0">
			<!-- Start Here ****************************************-->
			<?php 
				if (is_array($list_total_day) && count($list_total_day)>0){
				
				$mmax=0;
				$totalcall=0;
				$totalminutes=0;
				$totalcost_date=0;
				foreach ($list_total_day as $data){	
					if ($mmax < $data[1]) $mmax=$data[1];
					$totalcall+=$data[3];
					$totalminutes+=$data[1];
					$totalcost_date += $data[2];
				}
				?>
				<tr>
				<td colspan="5" align="center"><b>By Date</b> </td>
				</tr>
			  <tr bgcolor="#CCCCCC">
              <td class="invoice_td" width="29%"><font color="#003399"><b>Date</b></font> </td>
              <td width="27%" class="invoice_td"><font color="#003399"><b>Duration </b></font></td>

			  <td width="17%" class="invoice_td"><font color="#003399"><b>Calls </b></font></td>
              <td width="27%" class="invoice_td" align="right"><font color="#003399"><b>Cost (US $) </b></font></td>
            </tr>
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
            <tr class="invoice_rows">
              <td width="29%" class="invoice_td"><font color="#003399"><?php echo $data[0]?></font></td>
              <td width="27%" class="invoice_td"><font color="#003399"><?php echo $minutes?></font> </td>
			  <td width="17%" class="invoice_td"><font color="#003399"><?php echo $data[3]?></font> </td>
              <td width="27%" align="right" class="invoice_td"><font color="#003399"><?php  display_2bill($data[2]) ?></font></td>
            </tr>
			 <?php 	 }	 	 	
	 	
				if ((!isset($resulttype)) || ($resulttype=="min")){  
					$total_tmc = sprintf("%02d",intval(($totalminutes/$totalcall)/60)).":".sprintf("%02d",intval(($totalminutes/$totalcall)%60));				
					$totalminutes = sprintf("%02d",intval($totalminutes/60)).":".sprintf("%02d",intval($totalminutes%60));
				}else{
					$total_tmc = intval($totalminutes/$totalcall);			
				}
			 
			 ?>               
			 <tr >
              <td width="29%" class="invoice_td">&nbsp;</td>
              <td width="27%" class="invoice_td">&nbsp;</td>
			  <td width="17%" class="invoice_td">&nbsp; </td>
			  <td width="27%" class="invoice_td">&nbsp; </td>
			  
            </tr>
            <tr bgcolor="#CCCCCC">
              <td width="29%" class="invoice_td"><font color="#003399"><?php echo gettext("TOTAL");?> </font></td>
              <td width="27%" class="invoice_td"><font color="#003399"><?php echo $totalminutes?></font></td>			  
			  <td width="17%" class="invoice_td"><font color="#003399"><?php echo $totalcall?> </font></td>
              <td width="27%" align="right" class="invoice_td"><font color="#003399"><?php  display_2bill($totalcost_date) ?> </font></td>
            </tr>            
            <tr >
              <td width="29%">&nbsp;</td>
              <td width="27%">&nbsp;</td>
			  <td width="17%">&nbsp; </td>
			  <td width="27%">&nbsp; </td>
			  
            </tr>
				
				<?php
			 	}
				?>
			</table>
			<!-- -------------------------------END HERE ---------------------------------->
	<!-------------------------DID Billing Starts Here ---------------------------------->
	
	
		<table width="80%" cellpadding="0" cellspacing="0" border="0" align="center">
		<tr>
		<td>
		<table width="100%" align="left" cellpadding="0" cellspacing="0">
   				<tr>
				<td colspan="6" align="center"><font><b>DID Billing</b></font> </td>
				</tr>
			<tr  bgcolor="#CCCCCC">
              <td  width="20%"> <font color="#003399"><b>DID </b></font></td>
              <td width="14%" ><font color="#003399"><b>Duration </b></font></td>
			  <td width="16%" ><font color="#003399"><b>Fixed</b></font> </td>
			  <td width="14%" ><font color="#003399"><b>Calls </b></font></td>
  			  <td width="17%" ><font color="#003399"><b>Call Cost </b></font></td>
              <td width="19%"  align="right"><font color="#003399"><b>Amount (US $)</b></font> </td>
            </tr>
			<?php  		
				$i=0;
				if (is_array($list_total_did) && count($list_total_did)>0)
				{	
				foreach ($list_total_did as $data){	
				$fcost = 0;
				$ccost = 0;
				$i=($i+1)%2;		
				$tmc = $data[3]/$data[5];
				
				if ((!isset($resulttype)) || ($resulttype=="min")){  
					$tmc = sprintf("%02d",intval($tmc/60)).":".sprintf("%02d",intval($tmc%60));		
				}else{
				
					$tmc =intval($tmc);
				}
				
				if ((!isset($resulttype)) || ($resulttype=="min")){  
						$minutes = sprintf("%02d",intval($data[3]/60)).":".sprintf("%02d",intval($data[3]%60));
				}else{
						$minutes = $data[3];
				}
				if ($mmax>0) 	$widthbar= intval(($data[3]/$mmax)*200); 
			
			?>
			 <tr class="invoice_rows">
              <td width="20%" ><font color="#003399"><?php echo $data[0]?></font></td>
              <td width="14%" ><font color="#003399"><?php echo $minutes?></font> </td>
  			  <td width="16%" ><font color="#003399"><?php 
			  if($data[2] == 2 || $data[2] == 3)
			  {
			  	echo "None";
				$fcost = 0;
				
			  }
			  else
			  {
			  	echo $data[1];
				$fcost = $data[1];
			  }
			  ?></font></td>
			  <td width="14%" ><font color="#003399"><?php echo $data[5]?></font> </td>
			  <td width="17%" ><font color="#003399"><?php 
			  if($data[2] == 3 || $data[2] == 1)
			  {
			  	echo "None";
				$ccost = 0;
			  }
			  else
			  {
			  	echo $data[4];
				$ccost = $data[4];
			  }
			  ?></font></td>
              <td width="19%" align="right" ><font color="#003399"><?php  display_2bill($ccost + $fcost) ?></font></td>
            </tr>
			 <?php 	 }	 	 	
	 	
				if ((!isset($resulttype)) || ($resulttype=="min")){  				
					$total_tmc = sprintf("%02d",intval(($totalminutes_did/$totalcall_did)/60)).":".sprintf("%02d",intval(($totalminutes_did/$totalcall_did)%60));				
					$totalminutes_did = sprintf("%02d",intval($totalminutes_did/60)).":".sprintf("%02d",intval($totalminutes_did%60));
				}else{
					$total_tmc = intval($totalminutes_did/$totalcall_did);			
				}			 
			 ?>   
			 <tr >
              <td width="20%" >&nbsp;</td>
              <td width="14%" >&nbsp;</td>
              <td width="16%" >&nbsp; </td>
			  <td width="14%" >&nbsp; </td>
			  <td width="17%" >&nbsp; </td>
			  <td width="19%" >&nbsp; </td>
			  
            </tr>
            <tr bgcolor="#CCCCCC" >
              <td width="20%" ><font color="#003399"><?php echo gettext("TOTAL");?> </font></td>
              <td  colspan="2"><font color="#003399"><?php echo $totalminutes_did?></font></td>			  
			  <td width="14%" ><font color="#003399"><?php echo $totalcall_did?></font> </td>
			  <td width="17%" >&nbsp;</td>
              <td width="19%" align="right" ><font color="#003399"><?php  display_2bill($totalcost_did) ?></font> </td>
            </tr>    
			<?php }
			else
			{
			?>
			<tr >
              <td width="18%">&nbsp;</td>              
			  <td  colspan="4" align="center">No DID Calls data available.</td>
			  <td width="25%">&nbsp; </td>
			  
            </tr>
			<?php
			}
			?>               
            <tr >
              <td width="20%">&nbsp;</td>
              <td width="14%">&nbsp;</td>
              <td width="16%">&nbsp; </td>
			  <td width="14%">&nbsp; </td>
			  <td width="17%">&nbsp; </td>
			  <td width="19%">&nbsp; </td>			  
            </tr>		
		</table>		
		</td>
		</tr>
		<tr>
	 <td><img src="<?php echo Images_Path;?>/spacer.jpg" align="middle"></td>
	 </tr>
	 <tr bgcolor="#CCCCCC" >
	 <td  align="right"><font color="#003399"><b>Grand Total = <?php echo display_2bill($totalcost);?>&nbsp;</b></font></td>
	 </tr>
	 <tr>
	 <td><img src="<?php echo Images_Path;?>/spacer.jpg" align="middle"></td>
	 </tr>
		</table>
	
	<!-----------------------------DID Billing END HERE ------------------------------->		
			
     <table cellspacing="0" cellpadding="2" width="80%" align="center">
<tr>
			<td colspan="3">&nbsp;</td>
	   </tr>           			
			<tr>
              <td  align="left"><font color="#003399">Status :&nbsp;<?php if($info_customer[0][12] == 't') {?>
			  <img src="<?php echo Images_Path;?>/connected.jpg">
			  <?php }
			  else
			  {
			  ?>
			  <img src="<?php echo Images_Path;?>/terminated.jpg">
			  <?php }?> </font></td>              
            </tr>      
      <tr>	  
	  <td  align="left">&nbsp; <font color="#003399"><img src="<?php echo Images_Path;?>/connected.jpg"> &nbsp; Connected
	  &nbsp;&nbsp;&nbsp;<img src="<?php echo Images_Path;?>/terminated.jpg">&nbsp; Disconnected</font>
	  
	  
	  </td>
</table>
	<?php
	
	}	
	else
	{
	?>
	<table  cellspacing="0" class="invoice_main_table">
     
      <tr>
        <td class="invoice_heading">Bill Details</td>
      </tr>	  
	 <tr>
	 <td>&nbsp;</td>
	 </tr> 
	  <tr>
	 <td align="center">No invoice is billed to you yet!</td>
	 </tr> 
	  <tr>
	 <td>&nbsp;</td>
	 </tr> 
	 </table>
	<?php } ?>
	
<?php }
else
{
?>
	<table  cellspacing="0" class="invoice_main_table">
     
      <tr>
        <td class="invoice_heading">Bill Details</td>
      </tr>	  
	 <tr>
	 <td>&nbsp;</td>
	 </tr> 
	  <tr>
	 <td align="center">No invoice is billed to you yet!</td>
	 </tr> 
	  <tr>
	 <td>&nbsp;</td>
	 </tr> 
	 </table>

<?php

}
?>


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
	
	$pdf->Output('BilledDetails_'.date("d/m/Y-H:i").'.pdf', 'I');



} ?>
