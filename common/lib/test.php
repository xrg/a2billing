#!/usr/bin/php
<?php
define("DEFAULT_A2BILLING_CONFIG", '../../a2billing.conf');
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
	return (preg_match('/^\-?[0-9]+$/',$v)>=1);
}

$examples=array('1','500','','ab123','20 1234','-100','12-3','--600','9823748716249876194652');

foreach ($examples as $ex){
	echo "Example \"$ex\" -> ";
	print_r(isnumeric($ex));
	echo "\n";
}


?>