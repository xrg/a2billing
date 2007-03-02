#!/usr/bin/php -q
<?php 
/***************************************************************************
 *            a2billing_subscription_fee.php
 *
 *  Fri Oct 27 14:17:10 2007 (in the train from Jemappes to Bruxelles)
 *  Copyright  2007  User : Areski
 *  ADD THIS SCRIPT IN A CRONTAB JOB
 *
	crontab -e
	0 10 1 * * php /var/lib/asterisk/agi-bin/libs_a2billing/crontjob/a2billing_subscription_fee.php
	
	field	 allowed values
	-----	 --------------
	minute	 0-59
	hour		 0-23
	day of month	 1-31
	month	 1-12 (or names, see below)
	day of week	 0-7 (0 or 7 is Sun, or use names)
	
	The sample above will run the script every 21 of each month at 10AM
	
	# crontab -l
	# DO NOT EDIT THIS FILE - edit the master and reinstall.
	# (/tmp/crontab.9543 installed on Fri Oct 28 04:44:10 2005)
	# (Cron version -- $Id: crontab.c,v 2.13 1994/01/17 03:20:37 vixie Exp $)
	0 6 1 * * php /var/lib/asterisk/agi-bin/libs_a2billing/crontjob/a2billing_subscription_fee.php
****************************************************************************/
	set_time_limit(0);
	error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
	//dl("pgsql.so"); // remove "extension= pgsql.so !
	
	include (dirname(__FILE__)."/../db_php_lib/Class.Table.php");
	include (dirname(__FILE__)."/../Class.A2Billing.php");
	
	
	$verbose_level=3;
	
	$groupcard=5000;
	
	function write_log($output, $tobuffer = 1){
		$string_log = "[".date("d/m/Y H:i:s")."]:$output\n";
		error_log ($string_log, 3, BATCH_LOG_FILE);
	}
	
	if ($A2B->config["database"]['dbtype'] == "postgres"){
		$UNIX_TIMESTAMP = "date_part('epoch',";
	}else{
	    $UNIX_TIMESTAMP = "UNIX_TIMESTAMP(";
	}
	
	write_log("[#### BATCH BEGIN ####]");
	
	
	$A2B = new A2Billing();
	$A2B -> load_conf($agi, NULL, 0, $idconfig);
	
	if (!$A2B -> DbConnect()){				
		echo "[Cannot connect to the database]\n";
		write_log("[Cannot connect to the database]");
		exit;						
	}
	
	$currencies_list = get_currencies($DBHandle);
	function convert_currency ($currencies_list, $amount, $from_cur, $to_cur){
		if (!is_numeric($amount) || ($amount == 0)){
			return 0;
		}
		if ($from_cur == $to_cur){
			return $amount;
		}
		if (strtoupper($this->agiconfig['base_currency']) != strtoupper($from_cur)){
		
		}
		
		// EUR -> 1.19175
		
		$mycur = $currencies_list[strtoupper($to_cur)][2];
		$amount_cur = $amount / $mycur;
		
		return $amount_cur;
		
	}
	
	
	$instance_table = new Table();
	
	// SELECT * FROM cc_card LEFT JOIN cc_subscription_fee ON cc_card.id_subscription_fee=cc_subscription_fee.id WHERE cc_subscription_fee.status=1

	// CHECK AMOUNT OF CARD ON WHICH APPLY THE SERVICE
	$QUERY = 'SELECT count(*) FROM cc_card LEFT JOIN cc_subscription_fee ON cc_card.id_subscription_fee=cc_subscription_fee.id WHERE cc_subscription_fee.status=1';

	$result = $instance_table -> SQLExec ($A2B -> DBHandle, $QUERY);
	$nb_card = $result[0][0];
	$nbpagemax=(intval($nb_card/$groupcard));
	if ($verbose_level>=1) echo "===> NB_CARD : $nb_card - NBPAGEMAX:$nbpagemax\n";
	
	if (!($nb_card>0)){
		if ($verbose_level>=1) echo "[No card to run the Subscription Fee service]\n";
		write_log("[No card to run the Subscription Feeservice]");
		exit();
	}
	
	
	
	
	exit;	

	//SELECT cc_card.id, username, credit, cc_card.currency, cc_subscription_fee.id, cc_subscription_fee.label, cc_subscription_fee.fee, cc_subscription_fee.currency, emailreport FROM cc_card LEFT JOIN cc_subscription_fee ON cc_card.id_subscription_fee=cc_subscription_fee.id WHERE cc_subscription_fee.status=1
	
	
	// CHECK THE SUBSCRIPTION SERVICES
	$QUERY = 'SELECT id, label, fee, currency, emailreport FROM cc_subscription_fee WHERE status=1';

	$result = $instance_table -> SQLExec ($A2B -> DBHandle, $QUERY);
	
	if ($verbose_level>=1) print_r ($result);
	
	if( !is_array($result)) {
			echo "[No Recurring service to run]\n";
			write_log("[ No Recurring service to run]");
			exit();
    }
	
	write_log("[Number of card found : $nb_card]");
	
	$oneday = 60*60*24;
	
	// mail variable for user notification
	
	// BROWSE THROUGH THE SERVICES 
	foreach ($result as $myservice) {
	
		$totalcardperform = 0;
		$totalcredit = 0;
		
		$myservice_id = $myservice[0];
		
		write_log("[Subscription Fee Service analyze cards on which to apply service ]");
		// BROWSE THROUGH THE CARD TO APPLY THE SUBSCRIPTION FEE SERVICE 
		for ($page = 0; $page <= $nbpagemax; $page++) {
			
			$sql = "SELECT id, credit, nbservice, username, email FROM cc_card WHERE id_subscription_fee='$myservice_id' ORDER BY id  ";
			if ($A2B->config["database"]['dbtype'] == "postgres"){
				$sql .= " LIMIT $groupcard OFFSET ".$page*$groupcard;
			}else{
				$sql .= " LIMIT ".$page*$groupcard.", $groupcard";
			}
			if ($verbose_level>=1) echo "==> SELECT CARD QUERY : $sql\n";
			$result_card = $instance_table -> SQLExec ($A2B -> DBHandle, $sql);
		
			foreach ($result_card as $mycard){
				if ($verbose_level>=1) print_r ($mycard);
				if ($verbose_level>=1) echo "------>>>  ID = ".$mycard[0]." - CARD =".$mycard[3]." - BALANCE =".$mycard[1]." \n";	
				
				
				
				$QUERY = "UPDATE cc_card SET credit=credit-'".$myservice[2]."' WHERE id=".$mycard[0];	
				$result = $instance_table -> SQLExec ($A2B -> DBHandle, $QUERY, 0);
				if ($verbose_level>=1) echo "==> UPDATE CARD QUERY: 	$QUERY\n";
				$totalcardperform ++;
				$totalcredit += $myservice[2];
				//exit();
			}
			// Little bit of rest
			sleep(15);
		}
	
		write_log("[Service finish]");
		
		// INSERT REPORT SERVICE INTO THE DATABASE
		$QUERY = "INSERT INTO cc_service_report (cc_service_id, totalcardperform, totalcredit, daterun) ".
				 "VALUES ('".$myservice[0]."', '$totalcardperform', '$totalcredit', now())";		
		$result_insert = $instance_table -> SQLExec ($A2B -> DBHandle, $QUERY, 0);
		if ($verbose_level>=1) echo "==> INSERT SERVICE REPORT QUERY=$QUERY\n";
	
		write_log("[Service report : 'totalcardperform=$totalcardperform', 'totalcredit=$totalcredit']");
		
		// UPDATE THE SERVICE		
		$QUERY = "UPDATE cc_service SET datelastrun=now(), numberofrun=numberofrun+1, totalcardperform=totalcardperform+".$totalcardperform.
				 ", totalcredit = totalcredit + '".$totalcredit."' WHERE id=".$myservice[0];	
		$result = $instance_table -> SQLExec ($A2B -> DBHandle, $QUERY, 0);
		if ($verbose_level>=1) echo "==> SERVICE UPDATE QUERY: 	$QUERY\n";
		
		
		// SEND REPORT
		if (strlen($myservice[12])>0){
			$mail_content = "SERVICE NAME = ".$myservice[1];
			$mail_content .= "\n\nTotal card updated = ".$totalcardperform;
			$mail_content .= "\nTotal credit removed = ".$totalcredit;
			mail($myservice[12], "A2BILLING RECURSING SERVICES : REPORT", $mail_content);
		}
	
	} // END FOREACH SERVICES
	
	if ($verbose_level>=1) echo "#### END RECURRING SERVICES \n";
	write_log("[#### BATCH PROCESS END ####]");
	
	
	
	
	
	function get_currencies($DBHandle)
	{
		$instance_table = new Table();
		$QUERY =  "SELECT id,currency,name,value from cc_currencies order by id";
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
		
		if ((isset($currencies_list)) && (is_array($currencies_list)))	sort_currencies_list($currencies_list);		
		
		return $currencies_list;
	}
?>
