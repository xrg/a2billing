#!/usr/bin/php -q
<?php
/***************************************************************************
 *            a2billing_invoice_cront.php
 *
 *  13 April 2007
 *  Purpose: To greate invoices for Each User.
 *  Copyright  2007  User : Belaid Arezqui
 *  ADD THIS SCRIPT IN A CRONTAB JOB
 *
 *  The sample above will run the script every day of each month at 6AM
	crontab -e
	0 6 1 * * php /var/lib/asterisk/agi-bin/libs_a2billing/crontjob/a2billing_invoice_cront.php
	
	
	field	 allowed values
	-----	 --------------
	minute	 0-59
	hour		 0-23
	day of month	 1-31
	month	 1-12 (or names, see below)
	day of week	 0-7 (0 or 7 is Sun, or use names)
****************************************************************************/

set_time_limit(0);
error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
//dl("pgsql.so"); // remove "extension= pgsql.so !

include (dirname(__FILE__)."/../db_php_lib/Class.Table.php");
include (dirname(__FILE__)."/../Class.A2Billing.php");

function write_log($output, $tobuffer = 1){
	$string_log = "[".date("d/m/Y H:i:s")."]:$output\n";
	error_log ($string_log, 3, BATCH_LOG_FILE);
}

//Flag to show the debuging information
$verbose_level=1;

$groupcard = 5000;

$A2B = new A2Billing();
$A2B -> load_conf($agi, NULL, 0, $idconfig);

if (!$A2B -> DbConnect()){				
	echo "[Cannot connect to the database]\n";
	write_log("[Cannot connect to the database]");
	exit;
}

$instance_table = new Table();
$currencies_list = get_currencies($A2B -> DBHandle);

// CHECK COUNT OF CARD ON WHICH APPLY THE SERVICE
$QUERY = 'SELECT count(*) FROM cc_card';

$result = $instance_table -> SQLExec ($A2B -> DBHandle, $QUERY);
$nb_card = $result[0][0];
$nbpagemax = (intval($nb_card/$groupcard));

if ($verbose_level>=1) echo "===> NB_CARD : $nb_card - NBPAGEMAX:$nbpagemax\n";

if (!($nb_card>0)){
	if ($verbose_level>=1) echo "[No card to run the Invoice Billing Service]\n";
	write_log("[No card to run the Invoice Billing service]");
	exit();
}

if ($verbose_level>=1) echo ("[Invoice Billing Service analyze cards on which to apply service]");
write_log("[Invoice Billing Service analyze cards on which to apply service]");

for ($page = 0; $page <= $nbpagemax; $page++) 
{
	if ($verbose_level >= 1)  echo "$page <= $nbpagemax \n";
	$Query_Customers = "SELECT id, creationdate, firstusedate, expirationdate, enableexpire, expiredays, username, vat, invoiceday FROM cc_card ";
	
	if ($A2B->config["database"]['dbtype'] == "postgres")
	{
		$Query_Customers .= " LIMIT $groupcard OFFSET ".$page*$groupcard;
	}
	else
	{
		$Query_Customers .= " LIMIT ".$page*$groupcard.", $groupcard";
	}
	
	$resmax = $instance_table -> SQLExec ($A2B -> DBHandle, $Query_Customers);
	
	if (is_array($resmax)){
		$numrow = count($resmax);
		if($verbose_level >= 2) print_r($resmax[0]);
	}else{
		$numrow = 0;
	}
	if($verbose_level >= 1) echo "\n Total Customers Found: ".$numrow;
	
	if ($numrow == 0) {
		if ($verbose_level>=1) echo "\n[No card to run the Invoice Billing Service]\n";
		write_log("[No card to run the Invoice Billing service]");
		exit();
		
	}else{
		
	 	foreach($resmax as $Customer){
			
			// Check if this is the correct date to generate the invoice	
			$invoiceday = (is_numeric ($Customer[8]) && $Customer[8]>=1) ? $Customer[8] : 1 ;
			if ($verbose_level>=1) echo "\n Invoiceday = $invoiceday  -  Invoiceday db = ".$Customer[8];
			
			// the value of invoiceday is between 1..28, dont make sense to bill customer on 29, 30, 31
			if (date("j",time()) != $invoiceday || $invoiceday > 28){
				if ($verbose_level>=1) echo "\n We dont create an invoice today for this customer : ".$Customer[6];
				continue;
			}
			
			// Here we have to check for the Last Invoice date to set the Cover Start date. 
			// if a user dont have a Last invocie then we have to Set the Cover Start date to it Creation Date.
			$query_billdate = "SELECT CASE WHEN max(cover_enddate) is NULL THEN '0000-00-00 00:00:00' ELSE max(cover_enddate) END FROM cc_invoices WHERE cardid='$Customer[0]'";
			if ($verbose_level>=1) echo "\nQUERY_BILLDATE = $query_billdate";
			
			$resdate = $instance_table -> SQLExec ($A2B -> DBHandle, $query_billdate);
			if($verbose_level >= 2) print_r($resdate);
			if (is_array($resdate) && count($resdate)>0 && $result[0][0] != "0000-00-00 00:00:00"){
				// Customer Last Invoice Date
				$cover_startdate = $resdate[0][0];
			} else {
				// Customer Creation Date			
				$cover_startdate = $Customer[1];
			}
			if($verbose_level >= 1)	echo "\n Cover Start Date for '$Customer[6]': ".$cover_startdate;
			
			$FG_TABLE_CLAUSE = " t1.username='$Customer[6]' AND t1.starttime > '$cover_startdate'";
			
			
			// init totalcost
			$totalcost = 0;
			$totaltax = 0;
			$totalcall = 0;
			$totalminutes = 0;
			$totalcharge = 0;
			
			//************************************* CALLS SECTION *************************************************
			//$Query_Destinations = "SELECT destination, sum(t1.sessiontime) AS calltime, sum(t1.sessionbill) AS cost, count(*) AS nbcall FROM cc_call t1 WHERE (t1.sipiax<>2 AND t1.sipiax<>3) AND ".$FG_TABLE_CLAUSE." GROUP BY destination";		
			$Query_Destinations = "SELECT destination, sum(t1.sessiontime) AS calltime, sum(t1.sessionbill) AS cost, count(*) AS nbcall FROM cc_call t1 WHERE ".
								  $FG_TABLE_CLAUSE." GROUP BY destination";
			$list_total_destination = $instance_table -> SQLExec ($A2B -> DBHandle, $Query_Destinations);
			if (is_array($list_total_destination)){
				$num = count($list_total_destination);
			}else{
				$num = 0;
			}
			
			if($verbose_level >= 1){
				echo "\n Query_Destinations = $Query_Destinations";
				echo "\n Number of Destinatios for '$Customer[6]' Found: ".$num;
			}
			
			//Get the calls destination wise and calculate total cost			
			if (is_array($list_total_destination) && count($list_total_destination) > 0){
				foreach ($list_total_destination as $data){
					$totalcall+=$data[3];
					$totalminutes+=$data[1];
					$totalcost+=$data[2];
				}
			}
			if($verbose_level >= 1){
				echo "\n AFTER DESTINATION : totalcall = $totalcall - totalminutes = $totalminutes - totalcost = $totalcost ";
			}
			//************************************* DID SECTION *************************************************
			// SIPIAX :>> 0 = NORMAL CALL ; 1 = VOIP CALL (SIP/IAX) ; 2= DIDCALL + TRUNK ; 3 = VOIP CALL DID ; 4 = CALLBACK call
			/*
			$QUERYDID = "SELECT t1.id_did, t2.fixrate, t2.billingtype, sum(t1.sessiontime) AS calltime, 
				sum(t1.sessionbill) AS cost, count(*) AS nbcall FROM cc_call t1, cc_did t2 WHERE (t1.sipiax=2 OR t1.sipiax=3) AND ".$FG_TABLE_CLAUSE." 
				AND t1.sipiax in (2,3) AND t1.id_did = t2.id GROUP BY t1.id_did";
			$list_total_did = $instance_table -> SQLExec ($A2B -> DBHandle, $QUERYDID);
			$num  = 0;				
			$num = count($list_total_did);
			
			// echo "\n Total Cost Before DID = ".$totalcost;
			// For DID Calls
			if (is_array($list_total_did) && count($list_total_did)>0){
				$totalcall = 0;
				$totalminutes = 0;
				//echo "\n Total Cost at Dial = ".$totalcost;
				foreach ($list_total_did as $data)
				{
					$totalcall += $data[5];
					$totalminutes += $data[3];		
					if ($data[2] == 0)
					{
						$totalcost += ($data[4] + $data[1]);					
						if($verbose_level >= 1)	echo "\n DID =".$data[0]."; Fixed Cost=".$data[1]."; Total Call Cost=".$data[4]."; Total = ".$totalcost;
					}
					if ($data[2] == 2)
					{
						$totalcost += $data[4];
						if($verbose_level >= 1)	echo "\n DID =".$data[0]."; Fixed Cost=0; Total Call Cost=".$data[4]."; Total = ".$totalcost;
					}
					if ($data[2] == 1)
					{			
						$totalcost += ($data[1]);
						if($verbose_level >= 1)	echo "\n DID =".$data[0]."; Fixed Cost=".$data[1]."; Total = ".$totalcost;
					}
					if ($data[2] == 3)
					{
						$totalcost += 0;
						if($verbose_level >= 1)	echo "\n DID =".$data[0]."; TYPE = FREE; Total = ".$totalcost;
					}
				}
			}*/
			
			//************************************* CHARGE SECTION *************************************************
			// chargetype : 1 - connection charge for DID setup, 2 - Montly charge for DID use, 3 - Subscription fee, 4 - Extra Charge, etc...
			$FG_TABLE_CLAUSE = " id_cc_card='$Customer[0]' AND creationdate > '$cover_startdate'";
			$QUERY_CHARGE = "SELECT id, id_cc_card, iduser, creationdate, amount, chargetype, description, id_cc_did, currency, id_cc_subscription_fee FROM cc_charge".
							" WHERE $FG_TABLE_CLAUSE";
			$list_total_charge = $instance_table -> SQLExec ($A2B -> DBHandle, $QUERY_CHARGE, 1);
			$num  = 0;				
			$num = count($list_total_charge);
			if($verbose_level >= 1){
				echo "\n QUERY_CHARGE = $QUERY_CHARGE";
				echo "\n Number of Charge for '$Customer[6]' Found: ".$num;
			}
			
			//Get the calls destination wise and calculate total cost			
			if (is_array($list_total_charge) && count($list_total_charge) > 0){
				foreach ($list_total_charge as $data){
					$charge_amount = $data[4];
					$charge_currency = $data[8];
					$base_currency = $A2B->config['global']['base_currency'];
					$charge_converted = convert_currency ($currencies_list, $charge_amount, strtoupper($charge_currency), strtoupper($base_currency));
					if($verbose_level >= 1){
						echo "\n charge_amount = $charge_amount - charge_currency = $charge_currency ".
							 " - charge_converted=$charge_converted - base_currency=$base_currency";
					}
					$totalcharge+=1;
					$totalcost+=$charge_converted;
				}
			}
			if($verbose_level >= 1){
				echo "\n AFTER DESTINATION : totalcharge = $totalcharge - totalcost = $totalcost";
			}
			
			//************************************* INSERT INVOICE *************************************************			
			if ($Customer[7] > 0 && $totalcost > 0){
				$totaltax = ($totalcost / 100) * $Customer[7];
			}
			
			// Here we have to Create a Insert Statement to insert Records into the Invoices Table.
			$Query_Invoices = "INSERT INTO cc_invoices (cardid, orderref, invoicecreated_date, cover_startdate, cover_enddate, amount, tax, total, invoicetype,".
				"filename) VALUES ('$Customer[0]', NULL, NOW(), '$cover_startdate', NOW(), $totalcost, $totaltax, $totalcost + $totaltax, NULL, NULL)";
			$instance_table -> SQLExec ($A2B -> DBHandle, $Query_Invoices);
			
			if($verbose_level >= 1)
			{
				echo "\n Total Cost for '$Customer[0]': ".$totalcost;
				echo "\n Query_Invoices=$Query_Invoices \n";
				echo "\n ################################################################################# \n\n";
			}
			
	 	}// END foreach($resmax as $Customer)
	}
}



/***************************************************************************
 *            Function to handle the currencies
 ***************************************************************************/

	
function get_currencies($DBHandle)
{
	$instance_table = new Table();
	$QUERY =  "SELECT id,currency,name,value FROM cc_currencies ORDER BY id";
	$result = $instance_table -> SQLExec ($DBHandle, $QUERY);
	/*
		$currencies_list['ADF'][1]="Andorran Franc";
		$currencies_list['ADF'][2]="0.1339";
		[ADF] => Array ( [1] => Andorran Franc (ADF), [2] => 0.1339 )
	*/

	if (is_array($result)){
		$num_cur = count($result);
		for ($i=0;$i<$num_cur;$i++)
			$currencies_list[$result[$i][1]] = array (1 => $result[$i][2], 2 => $result[$i][3]);
	}	
	
	return $currencies_list;
}

function convert_currency ($currencies_list, $amount, $from_cur, $to_cur){
	
	if (!is_numeric($amount) || ($amount == 0)){
		return 0;
	}
	if ($from_cur == $to_cur){
		return $amount;
	}
	// EUR -> 1.19175 : MAD -> 0.10897		
	// FROM -> 2 - TO -> 0.5 =>>>> multiply 4
	
	$mycur_tobase = $currencies_list[strtoupper($from_cur)][2];		
	$mycur = $currencies_list[strtoupper($to_cur)][2];
	if ($mycur == 0) return 0;
	$amount = $amount * ($mycur_tobase / $mycur);		
	// echo "\n \n AMOUNT CONVERTED IN NEW CURRENCY $to_cur -> VALUE =".$amount;
	
	return $amount;
}

?>
