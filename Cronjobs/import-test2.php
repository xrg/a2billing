#!/usr/bin/php
<?php
require_once("lib/Class.A2Billing.inc.php");
require_once("lib/Misc.inc.php");
require_once("lib/Provi/Class.XmlImport.inc.php");

set_time_limit(0);
error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));

$verbose = 1;
$dry_run = false;
$confname = 'test1';

$cli_args = arguments($argv);

if (!empty($cli_args['dry-run']) || !empty($cli_args['n']))
	$dry_run=true;

if (!empty($cli_args['debug']) || !empty($cli_args['d']))
	$verbose=3;
else if (!empty($cli_args['verbose']) || !empty($cli_args['v']))
	$verbose=2;
else if (!empty($cli_args['silent']) || !empty($cli_args['q']))
	$verbose=0;

if (!empty($cli_args['confname']))
	$confname=$cli_args['confname'];

if (!empty($cli_args['config']))
	define('DEFAULT_A2BILLING_CONFIG',$cli_args['config']);

// Get the periods
$files = $cli_args['input'];

if (empty($files)){
	echo "No file specified!\n";
	exit(1);
}

$res= fopen($files[0],'r');
if (!$res){
	echo "Could not open ".$files[0]." .\n";
	exit(2);
}

$gen = new SpaXmlImport();

$gen->Init(array(name=>$confname));

$gen->parseContent($res);

?>
