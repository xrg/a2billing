#!/usr/bin/php
<?php
set_time_limit(0);
error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));

require("lib/Misc.inc.php");
require('lib/adodb/adodb.inc.php');
require("logimport.php");

function getDB(){
	$dbhandle = NewADOConnection("pgsql://dbname=nmtest host=localhost");
	if (!$dbhandle)
		throw new Exception("Cannot connect to db!");
	
	$dbhandle->setFetchMode(ADODB_FETCH_ASSOC);
	return $dbhandle;
}

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

$dbhandle = getDB();
$simp=new SensorsLogImport();
$simp->Init(array(db=>$dbhandle));

foreach ($cli_args['input'] as $fname){
	$fil = fopen($fname,"rb");
	if (!$fil){
		echo "Could not open $fname\n";
		continue;
	}
	$dbhandle->Execute("BEGIN;");
	$simp->parseContent($fil);
	$simp->out(LOG_DEBUG,"Did commit");
	$dbhandle->Execute("COMMIT;");
	//$simp->print_cache();
	fclose($fil);
}
?>
