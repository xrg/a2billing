<?php
/*
 File: Invoice Cront Service
 Purpose: To insert secords in the Invoice Tables for Each User.
 Date Created: November 26, 2006
*/

include ("lib/defines.php");
include ("lib/module.access.php");

$Query_Customers = "Select * from cc_card";
$DBHandler = DbConnect();
$currencies_list = get_currencies();

//Flag to show the debuging information
$debug = 1;

// Checking for Execution time from Config File;
if(isset($A2B->config["invoice_system"]['invoiceday']))
{
	$execution_DateTime = $A2B->config["invoice_system"]['invoiceday'];
}
else
{
	$execution_DateTime = 1;
}
if($debug == 1)
{
	echo "<br>Execution Date Time: ".$execution_DateTime;
}

// Matching to the Current time//

$resmax = $DBHandler -> query($Query_Customers);
$numrow = $resmax -> numRows();
if($debug == 1)
{	
	echo "<br>Total Customers Found: ".$numrow;
}
if ($numrow == 0) 
{
	exit();
}
else
{
 	foreach($resmax as $Customer)
 	{		
		$FG_TABLE_CLAUSE = "t1.username='$Customer[6]' AND t1.starttime >(Select CASE WHEN max(cover_enddate) is NULL THEN '0000-00-00 00:00:00' ELSE max(cover_enddate) END from cc_invoices)";
		
		$Query_Destinations = "SELECT destination, sum(t1.sessiontime) AS calltime, 
		sum(t1.sessionbill) AS cost, count(*) AS nbcall FROM cc_call t1 WHERE ".$FG_TABLE_CLAUSE." GROUP BY destination";		
		
		$res = $DBHandler -> query($Query_Destinations);		
		$num = $res -> numRows();
		if($debug == 1)
		{
			echo "<br><br>No of Destinatios for '$Customer[6]' Found: ".$num;
		}
		$list_total_destination = NULL;
		for($i=0;$i<$num;$i++)
		{				
			$list_total_destination [] = $res -> fetchRow();				 
		}	
		
		//*************************************DID SECTION*************************************************
		
		$QUERYDID = "SELECT t1.id_did, t2.fixrate, t2.billingtype, sum(t1.sessiontime) AS calltime, 
		sum(t1.sessionbill) AS cost, count(*) as nbcall FROM cc_call t1, cc_did t2 WHERE ".$FG_TABLE_CLAUSE." 
		AND t1.sipiax in (2,3) AND t1.id_did = t2.id GROUP BY t1.id_did";
	 
	 	//echo "<br><br>".$QUERYDID."<br><br>";
		//exit;
		$res = $DBHandler -> query($QUERYDID);
		$num  = 0;
		//echo "<br>Total Records = ".$num;	
		$num = $res -> numRows();
		$list_total_did = NULL;
		for($i=0; $i<$num; $i++)
		{				
			$list_total_did [] =$res -> fetchRow();
		}
		
		//*************************************END DID SECTION*********************************************
		$totalcost = 0;
		$totaltax = 0;
		//Get the calls destination wise and calculate total cost.
		if (is_array($list_total_destination) && count($list_total_destination) > 0)
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
		//echo "<br> Total Cost Befor DID = ".$totalcost;
		//For DID Calls
		if (is_array($list_total_did) && count($list_total_did)>0)
		{
			//echo "<br>I am here";
			$mmax = 0;
			$totalcall = 0;
			$totalminutes = 0;
			//echo "<br>Total Cost at Dial = ".$totalcost;
			foreach ($list_total_did as $data)
			{	
				if ($mmax < $data[3])
				{
					$mmax = $data[3];
				}
				$totalcall += $data[5];
				$totalminutes += $data[3];		
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
		//echo "<br> Total Cost After DID = ".$totalcost;
		
		if($debug == 1)
		{	
			echo "<br>Total Cost for '$Customer[0]': ".$totalcost;
		}
		// Here we have to check for the Last Invoice date to set the Cover Start date. 
		// if a user dont have a Last invocie then we have to Set the Cover Start date to it Creation Date.
		
		$Query_billdate = "SELECT CASE WHEN max(cover_enddate) is NULL THEN '0000-00-00 00:00:00' END FROM cc_invoices WHERE cardid='$Customer[0]'";
		$resdate = $DBHandler -> query($Query_billdate);
		$numdate = $resdate -> numRows();		
		$invoice = $resdate -> fetchRow();
		if($debug == 1)
		{
			echo "<br> Count Max End Date for Customer: " .$numdate;
		}
		if ($numdate > 0 && $invoice[0]!= "0000-00-00 00:00:00")
		{
			// Customer Last Invoice Date
			$cover_startdate = $invoice[0];
		}
		else
		{
			// Customer Creation Date			
			$cover_startdate = $Customer[1];
		}
		if($debug == 1)
		{
			echo "<br>Cover Start Date for '$Customer[6]': ".$cover_startdate;
		}
		// Here we have to Create a Insert Statement to insert Records into the Invoices Table.
		$Query_Invoices = "INSERT INTO cc_invoices (id, cardid, orderref, invoicecreated_date, cover_startdate,
						cover_enddate, amount, tax, total, invoicetype, filename) VALUES (NULL, '$Customer[0]', NULL, CURRENT_TIMESTAMP, '$cover_startdate', CURRENT_TIMESTAMP, $totalcost, $totaltax, $totalcost + $totaltax, NULL, NULL)";		
		//$DBHandler -> query($Query_Invoices);		
 	}
}
?>
