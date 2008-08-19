#!/usr/bin/php
<?php
define("DEFAULT_A2BILLING_CONFIG", '../../a2billing.conf.testing');
// echo  DEFAULT_A2BILLING_CONFIG;
// echo "\n";
$FG_DEBUG=2;

if (false){
	print_r(A2Billing::DBHandle());
}
if (false) {
	require_once('Class.Logger.php');
	$loog = new Logger();
	$loog-> insertLog(0, 1, "TEST", "test entry!", 'aa','bb','cc');
}

if (false) {
	require_once('Class.DynConf.php');
	$inst = DynConf::instance();
	$inst->PrefetchGroup('general');
	$inst->dbg_print_cached_config();
	DynConf::instance()->dbg_print_cached_config();
	$val = DynConf::GetCfg('general','test',123);
	print_r($val);
	echo "\n";
	print_r(DynConf::GetCfg('general','test2'));
	echo "\n";
	print_r(DynConf::GetCfg('general','test'));
	echo "\n";
	
}

function isnumeric($v){
	//return (preg_match('/^\-?[0-9]+$/',$v)>=1);
	return (preg_match('/^\-?[0-9]+([,.][0-9]*)?$/',$v)>=1);
}

if (false) {
	$examples=array('1','500','','ab123','20 1234','-100','12-3','--600','9823748716249876194652',
		'1.0','500,2','','ab,123','20, 1234','-100.0','12.3','-600.','98.23748716249876194652');
	
	foreach ($examples as $ex){
		echo "Example \"$ex\" -> ";
		print_r(isnumeric($ex));
		echo "\n";
	}
}

if (false){
	require_once('Class.A2Billing.inc.php');
	$dbh=A2Billing::DBHandle();
	
		// Execute a query with some results and some warnings..
	$res = $dbh->Execute('SELECT * from RateEngine2(1,\'+7831222\',now(),-1);');
	echo "Msg:" . print_r($dbh->ErrorMsg(),true) . "\n";
	echo "Msg 2:" . print_r($dbh->NoticeMsg(),true) . "\n";
	print_r($res->GetAll());
	//print_r($dbh);
}

if (false) {
	require_once('Class.A2Billing.inc.php');
	$dbh=A2Billing::DBHandle();
	print_r($dbh);
}

if (false){
	require_once('Class.A2Billing.inc.php');
	$dbh=A2Billing::DBHandle();
	echo "Connected!\n";
	$dbh->Execute("LISTEN test;");
	pg_wait_notify($dbh->_connectionID, 30000);
	echo "Done!\n";
}

if(true){
	require("../A2Billing_AGI/groupdial.inc.php");
	echo "Result:";
	print_r(groupstr_analyze($argv[1]));
	echo "\n";
}

?>