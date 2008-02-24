#!/usr/bin/php -q
<?php 

/** Send all pending e-mails 
*/

set_time_limit(0);
error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));

require_once("lib/Send_Mail.inc.php");
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

if (Send_Mails($verbose,$dry_run))
	exit(0);
else
	exit(1);

?>