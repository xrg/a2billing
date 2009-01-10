#!/usr/bin/php -q
<?php
set_time_limit(0);
error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));

require("lib/Misc.inc.php");
require('lib/adodb/adodb.inc.php');
require("logimport.php");

function getDB(){
	$dbhandle = NewADOConnection("pgsql://dbname=nmtest");
	if (!$dbhandle)
		throw new Exception("Cannot connect to db!");
		return false;
	
	$dbhandle->setFetchMode(ADODB_FETCH_ASSOC);
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
	$simp->parseContent($fil);
	fclose($fil);
}
?>
