#!/usr/bin/php -q
<?php 
/*
 * Generate bunch of CDR for testing purpose
 * 
 * USAGE :  ./generate-cdr.php --verbose=vvv 
 */

exit;

set_time_limit(0);
error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));

require("../../common/lib/Misc.inc.php");
require("../../common/lib/Class.A2Billing.inc.php");
require("../../common/lib/Class.Alarm.inc.php");

$verbose = 1;

$cli_args = arguments($argv);

if (!empty($cli_args['debug']) || !empty($cli_args['d']))
	$verbose=3;
else if (!empty($cli_args['verbose']) || !empty($cli_args['v']))
	$verbose=2;
else if (!empty($cli_args['silent']) || !empty($cli_args['q']))
	$verbose=0;
	
// print_r ($cli_args);
// echo "verbose = $verbose - startdate=$startdate\n ";


if (!empty($cli_args['config']))
	define('DEFAULT_A2BILLING_CONFIG',$cli_args['config']);

// Get the periods
$proc_periods = $cli_args['input'];
$A2B = A2Billing::instance();
$dbhandle = $A2B->DBHandle();


$back_days = 15;
$amount_cdr = 100;
$cdr_per_day = intval($amount_cdr / $back_days);
$cardid = 3;
$destination = 'Italy';
$calledstation = '397821933244';

for ($i=1 ; $i <= $back_days; $i++){
	echo "Day : $i...\n";
	
	for ($j=1 ; $j <= $cdr_per_day; $j++){
		$maxhour = sprintf("%02d",rand(0,23));
		$minhour = sprintf("%02d",rand(0,23));
		if ($maxhour<$minhour){
				$temp = $maxhour; $maxhour = $minhour; $minhour = $maxhour;
		}
		$startdate_toinsert = date("Y-m-d", strtotime("-$i day")).' '.$minhour.":".sprintf("%02d",rand(0,59));
		$enddate_toinsert = date("Y-m-d", strtotime("-$i day")).' '.$maxhour.":".sprintf("%02d",rand(0,59));
		$uniqueid = date("Y-m-d", strtotime("-$i day")).'_'.rand(0,10000000);
		$sessiontime = rand(0,500);
		
		$qry .= "INSERT INTO cc_call (cmode, sessionid, uniqueid, cardid, srvid, nasipaddress, starttime, stoptime, sessiontime, calledstation, startdelay, " .
				"stopdelay,tcause, hupcause, cause_ext, attempt, srid, sessionbill, destination, brid, tgid, trunk, qval, src, id_did, buycost, " .
				"id_card_package_offer, invoice_id) VALUES ('standard', 'IAX2/areskiax-3', '$uniqueid', $cardid, 1, NULL, '$startdate_toinsert', '$enddate_toinsert', " .
				"$sessiontime, '$calledstation', 2, NULL, 'ANSWER', 16, '', 1, 1, 1.2000, '$destination', 2, 1, 1, NULL, '1856254697', NULL, 0.40000, 0, NULL);\n\n";
		
	}
}


if ($verbose)
	echo "Processing CDR generation for $amount_cdr CDRs \n";
		
// RUN QUERY FOR INSERT
if ($verbose>2)
		echo "Query: $qry\n";
	$res = $dbhandle->Execute($qry);
	if (!$res){
		echo $dbhandle->ErrorMsg() ."\n";
		die();
	}
	
	
	

if ($verbose)
	echo "End of the process \n\n";
		
