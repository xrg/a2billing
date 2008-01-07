#!/usr/bin/php -q
<?php 
/***************************************************************************
 *            currencies_update_yahoo.php
 *
 *  ADD THIS SCRIPT IN A CRONTAB JOB
 *
	crontab -e
	0 6 * * * php ...cronjobs/currencies_update_yahoo.php
	
	field	 allowed values
	-----	 --------------
	minute	 		0-59
	hour		 	0-23
	day of month	1-31
	month	 		1-12 (or names, see below)
	day of week	 	0-7 (0 or 7 is Sun, or use names)
	
	The sample above will run the script every day at 6AM
	

****************************************************************************/

set_time_limit(0);
error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
//dl("pgsql.so"); // remove "extension= pgsql.so !

// require("defines.php");
require("lib/Misc.inc.php");
require("lib/Class.A2Billing.inc.php");
// include_once ("/../Class.Table.php");

// define('DEBUG_CONF',1);
$verbose = 1;
$first_time = false;

$cli_args = arguments($argv);

function update_currency($cur, $value){
	global $FG_DEBUG;
	global $verbose;
	global $dbhandle;
	
	if (!is_numeric($value)) {
		if ($verbose)
			echo "Non-numeric value $value came for currency $cur .\n";
		return false;
	}
	if ($FG_DEBUG)
		echo "Updating rate of '$cur' to $value. ";
	$ures = $dbhandle->Execute('UPDATE cc_currencies SET value = ?, lastupdate = now() '.
		' WHERE currency = ?;',
			array($value,$cur));
	if (!$ures){
		if ($FG_DEBUG) echo "\n";
		echo $dbhandle->ErrorMsg() ."\n";
		return false;
	}elseif ($dbhandle->Affected_Rows()!=1){
		if ($verbose)
			echo "Warning: Affected rows:". $dbhandle->Affected_Rows(). ".\n";
		return false;
	}
	else {
		if ($FG_DEBUG)
			echo "OK.\n";
		return true;
	}
}

if (!empty($cli_args['debug']))
	$FG_DEBUG=$cli_args['debug'];
elseif(!empty($cli_args['d']))
	$FG_DEBUG=$cli_args['d'];

if (!empty($cli_args['verbose']))
	$verbose=2;
elseif(!empty($cli_args['v']))
	$verbose=2;

if (!empty($cli_args['silent']))
	$verbose=0;
elseif(!empty($cli_args['q']))
	$verbose=0;

if (!empty($cli_args['config']))
	define('DEFAULT_A2BILLING_CONFIG',$cli_args['config']);

$A2B = A2Billing::instance();

if ($cli_args['first-time'])
	$first_time=true;

// get in a csv file USD to EUR and USD to CAD
// http://finance.yahoo.com/d/quotes.csv?s=USDEUR=X+USDCAD=X&f=l1

if ($verbose)
	echo "Updating currencies for base: ". BASE_CURRENCY. "\n";

$dbhandle = $A2B->DBHandle();

// First, check the base currency!
if (BASE_CURRENCY != strtoupper(BASE_CURRENCY))
	die("Error: base_currency must be set in capital letters in cfg!\n");

$res = $dbhandle->Execute('SELECT value FROM cc_currencies WHERE currency = ?;', BASE_CURRENCY);

if (!$res){
	echo $dbhandle->ErrorMsg() ."\n";
	die();
}elseif ($res->EOF)
	echo "Warning: Base currency " . BASE_CURRENCY. " not found in database.\n";
else {
	if ($FG_DEBUG)
		echo "Base currency located in db, nice.\n";
	$row= $res->fetchRow();
	if ($row['value'] != 1.00){
		if ($first_time)
			echo "Warning: ";
		else	echo "Error: ";
		echo "Base currency rate is ".$row['value']." !\n";
		if (!$first_time)
			die();
		else
			if (!update_currency(BASE_CURRENCY, 1.0))
			die();
		
	}

}

$res = $dbhandle->Execute('SELECT id,currency FROM cc_currencies WHERE currency <> ? ;', BASE_CURRENCY);

if (!$res){
	echo $dbhandle->ErrorMsg() ."\n";
	die();
}elseif ($res->EOF){
	echo "Error: no currencies in db, weird.\n";
	exit();
}

$n=0;
$curr_list = array();
while ($cur_row=$res->fetchRow())
	$curr_list[] =$cur_row['currency'];

$conv_list=array();
foreach($curr_list as $cur)
	$conv_list[] = BASE_CURRENCY.$cur .'=X';
	
$url = "http://download.finance.yahoo.com/d/quotes.csv?s=";
$url .= implode('+',$conv_list);
$url .= '&f=sl1'; // 'sl1' means symbol+ value

if ($FG_DEBUG)
	echo "Fetch url: " . $url . "\n";

$outarr= array();
$outres= -1;
$tmpfname=tempnam("/tmp","currencies-");
$CMD='wget -q ' . escapeshellarg($url) . ' -O ' . $tmpfname;

// exec the script
exec($CMD,$outarr,$outres);
if ($outres!=0) {
	echo "Get currencies failed with code" . $outres . "\n";
	exit(1);
}
if ($verbose)
	echo "Currencies downloaded to $tmpfname \n";

$fil=fopen($tmpfname,'rt');
while($row = fgetcsv($fil,100)){
	if (empty($row[0]) || empty($row[1]))
		continue;
	$cur = substr($row[0],3,3);
	if (update_currency($cur,$row[1]))
		$n++;
}
fclose($fil);

unlink($tempnam);
if ($verbose)
	echo "Finished updating $n currencies.\n";

?>
