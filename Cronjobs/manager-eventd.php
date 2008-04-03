#!/usr/bin/php
<?php
require_once("lib/Class.A2Billing.inc.php");
require_once("lib/Misc.inc.php");
require_once("lib/phpagi/phpagi-asmanager.php");
//require_once("lib/Provi/Class.IniImport.inc.php");

set_time_limit(0);
error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));

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

function idle_handler($event, $parameters, $server, $port){
}

function dump_handler($event, $parameters, $server, $port)
{
	echo "Event: $event at $server. " . print_r($parameters,true) ."\n";

}
// // Get the periods
// $files = $cli_args['input'];
// 
// if (empty($files)){
// 	echo "No file specified!\n";
// 	exit(1);
// }
// 
// $res= fopen($files[0],'r');
// if (!$res){
// 	echo "Could not open ".$files[0]." .\n";
// 	exit(2);
// }

$host='localhost';
$uname='a2billing';
$password='a2bman';

$as = new AGI_AsteriskManager();
// && CONNECTING  connect($server=NULL, $username=NULL, $secret=NULL)
$res = $as->connect($host, $uname, $password);

if (!$res) {
	$err_msg .= str_params( _("Cannot connect to asterisk manager @%1<br>Please check manager configuration..."),
		array($host),1);
	echo $err_str;

	//return false;
	exit();
}
//$res = $as->Ping();
$as->Events('agent,call');
$as-> add_event_handler('Join',dump_handler);
$as-> add_event_handler('Leave',dump_handler);
$as-> add_event_handler('*',idle_handler);

while($res=$as->send_request('WaitEvent'))
	echo "WaitEvent: ".$res['Response']."\n";

echo $err_msg;


?>
