#!/usr/bin/php
<?php
require_once("lib/Class.A2Billing.inc.php");
require_once("lib/Misc.inc.php");
require_once("lib/Provi/SpaXml_Provi.inc.php");

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

if (!empty($cli_args['config']))
	define('DEFAULT_A2BILLING_CONFIG',$cli_args['config']);

// Get the periods
$macs = $cli_args['input'];

if (empty($macs)){
	echo "No MAC specified!\n";
	exit(1);
}

$res= STDOUT;

$gen = new SpaXmlProvi();

if (!$gen->Init(array(mac => $macs[0])))
	exit(2);

$gen->genContent($res);

?>
