#!/usr/bin/php -q
<?php 
/** Alarms processor.
    Launch this script with a period parameter like '5min' or '*' for all alarms
*/

set_time_limit(0);
error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));

require("lib/Misc.inc.php");
require("lib/Class.A2Billing.inc.php");
require("lib/Class.Alarm.inc.php");

$verbose = 1;
$dry_run = false;

$cli_args = arguments($argv);

if (!empty($cli_args['dry-run']) || !empty($cli_args['n']))
	$dry_run=true;

if (!empty($cli_args['debug']) || !empty($cli_args['d']))
	$verbose=3;
else if (!empty($cli_args['verbose']) || !empty($cli_args['v']))
	$verbose=2;
else if (!empty($cli_args['silent']) || !empty($cli_args['q']))
	$verbose=0;

if (!empty($cli_args['config']))
	define('DEFAULT_A2BILLING_CONFIG',$cli_args['config']);

// Get the periods
$proc_periods = $cli_args['input'];
$A2B = A2Billing::instance();
$dbhandle = $A2B->DBHandle();

$alarm_classes = array();
function processAlarm(array $row){
	global $verbose;
	global $A2B;
	global $alarm_classes;
		// We have to be strict here so that we don't end up
		// including arbitrary files
	if (!preg_match('/^[A-Za-z0-9\-\_]+$/',$row['atype'])){
		if($verbose)
			echo "Invalid characters in alm type: ".$row['atype']."\n";
		return;
	}
	$almclass = $row['atype'];
	if (! isset($alarm_classes[$almclass])){
		$ifile = "alarms/$almclass.inc.php";
		if ($verbose > 2)
			echo "Include <$ifile>\n";
		if (!include_once($ifile)){
			echo "File \"$ifile\" not found, cannot process alarm class.\n";
			return false;
		}
	}
	if (! isset($alarm_classes[$almclass])){
		echo "Alarm \"$almclass\" not found.\n";
		return false;
	}
	$instance = new AlmInstance($alarm_classes[$almclass],$row);
	$alarm_classes[$almclass]->ProcessAlarm($instance);
	return true;
}

if (!empty($proc_periods)){
	if ($verbose)
		echo "Processing alarms for: ".implode(', ',$proc_periods). "\n";
	$qry = 'SELECT * FROM cc_alarm WHERE status = 1';
	if (in_array('*', $proc_periods)){
		// do all periods!
	}else {
		$qry .= ' AND period = ANY(' . sql_encodearray($dbhandle,$proc_periods).')';
	}
	$qry .=";";
	if ($verbose>2)
		echo "Query: $qry\n";
	$res = $dbhandle->Execute($qry);
	if (!$res){
		echo $dbhandle->ErrorMsg() ."\n";
		die();
	}elseif ($res->EOF) {
		if ($verbose>1)
			echo "No alarms found.\n";
	}else{
		while($row = $res->fetchRow()){
			processAlarm($row);
		}
	}

}

if (true){
	if ($verbose)
		echo "Processing alarms on request.\n";
	$qry = "SELECT cc_alarm.*, cc_alarm_run.tcreate, cc_alarm_run.status AS ar_status, ".
		"cc_alarm_run.id AS ar_id, cc_alarm_run.params AS ar_params, cc_alarm_run.dataid ".
		"FROM cc_alarm, cc_alarm_run ".
		"WHERE cc_alarm_run.status = 10 AND cc_alarm.id = cc_alarm_run.alid ;";
	if ($verbose>2)
		echo "Query: $qry\n";
	$res = $dbhandle->Execute($qry);
	if (!$res){
		echo $dbhandle->ErrorMsg() ."\n";
		die();
	}elseif ($res->EOF) {
		if ($verbose>1)
			echo "No alarms found.\n";
	}else{
		while($row = $res->fetchRow()){
			processAlarm($row);
		}
	}
}
?>