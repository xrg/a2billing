#!/usr/bin/php -q
<?php
include (dirname(__FILE__)."/../Class.A2Billing.php");
include (dirname(__FILE__)."/../db_php_lib/Class.Table.php");

$A2B = new A2Billing();

// SELECT THE FILES TO LOAD THE CONFIGURATION
$A2B -> load_conf($agi, DEFAULT_A2BILLING_CONFIG, 1);	


// DEFINE FOR THE DATABASE CONNECTION
define ("BASE_CURRENCY", strtoupper($A2B->config["webui"]['base_currency']));

// get in a csv file USD to EUR and USD to CAD
// http://finance.yahoo.com/d/quotes.csv?s=USDEUR=X+USDCAD=X&f=l1


$A2B -> load_conf($agi, NULL, 0, $idconfig);
if (!$A2B -> DbConnect()){
	echo "[Cannot connect to the database]\n";
	write_log("[Cannot connect to the database]");
	exit;
}

$instance_table = new Table();
$A2B -> set_instance_table ($instance_table);

$QUERY =  "SELECT id,currency,basecurrency from cc_currencies order by id";
$result = $A2B -> instance_table -> SQLExec ($A2B->DBHandle, $QUERY);
	
$url = "http://finance.yahoo.com/d/quotes.csv?s=";

	/* result[index_result][field] */
	
	$index_base_currency = 0;

	if (is_array($result)){
		$num_cur = count($result);
		for ($i=0;$i<$num_cur;$i++){
		
			// Finish and add termination ? 
			if ($i+1 == $num_cur) $url .= BASE_CURRENCY.$result[$i][1]."=X&f=l1";
			else $url .= BASE_CURRENCY.$result[$i][1]."=X+";

			// Check what is the index of BASE_CURRENCY to save it 
			if (BASE_CURRENCY == $result[$i][1]) $index_base_currency = $result[$i][0];
		}

		// Create the script to get the currencies
		
		$f = fopen("/tmp/update.sh","w");
		$data = "#!/bin/sh\nwget '".$url."' -O /tmp/currencies.cvs";
		fwrite($f,$data);
		fclose($f);
		chmod("/tmp/update.sh",0755);

		// exec the script
		exec('/tmp/update.sh');
		sleep(5);

		// get the file with the currencies to update the database
		$currencies = file("/tmp/currencies.cvs");
		
		// update database
		foreach ($currencies as $id => $currency){
			$id++;
			// if the currency is BASE_CURRENCY the set to 1
			if ($id == $index_base_currency) $currency = 1;
			
			if ($currency!=0) $currency=1/$currency;
			$QUERY="UPDATE cc_currencies set value=".$currency;
			
			if (BASE_CURRENCY != $result[$i][2])
				$QUERY .= ",basecurrency='".BASE_CURRENCY."'";
			$QUERY .= " , lastupdate = CURRENT_TIMESTAMP WHERE id =".$id;
			
			$result = $A2B -> instance_table -> SQLExec ($A2B->DBHandle, $QUERY, 0);
		}	
	}
?>
