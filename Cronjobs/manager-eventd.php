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

function log_queue($uniqueid,$queue,$membername,$action,$parm1,$parm2,$parm3){

	echo time();
	echo "|$uniqueid|$queue|$membername|$action|$parm1|$parm2|$parm3\n";
}

function idle_handler($event, $parameters, $server, $port){
}


function dump_handler($event, $parameters, $server, $port)
{
	echo "Event: $event at $server. " . print_r($parameters,true) ."\n";
}


function handle_handler($event, $parameters, $server, $port){
	$parm1=$parm2=$parm3='';
	switch($event){
	case 'queuememberstatus':
	case 'leave':
		//ignore it.
		break;
	case 'join':
		$action='ENTERQUEUE';
		$parm1= $parameters['Url'];
		$parm2= $parameters['CallerID'];
		//$parm3= $parameters['CallerIDName'];
		break;
	case 'queuecallerabandon';
		$action='ABANDON';
		$parm1= $parameters['Position'];
		$parm2= $parameters['OriginalPosition'];
		$parm3= $parameters['HoldTime'];
		break;
	case 'agentdump':
		$action='AGENTDUMP';
		break;
	case 'agentconnect':
		$action='CONNECT';
		$parm1= $parameters['HoldTime'];
		$parm2= $parameters['BridgedChannel'];
		break;
	case 'agentcomplete':
		if ($parameters['Reason']=='agent')
			$action='COMPLETEAGENT';
		elseif ($parameters['Reason']=='caller')
			$action='COMPLETECALLER';
		else $action='Complete'.$parameters['Reason']; // must never happen
		
		$parm1= $parameters['HoldTime'];
		$parm2= $parameters['TalkTime'];
		$parm3= $parameters['OriginalPosition'];
		break;
	case 'queuememberremoved':
	case 'queuememberadded':
	case 'queuememberpaused':
	case 'agentcalled':
	default:
		echo "How to handle: $event ?\n";
		return;
	}
	
	log_queue($parameters['Uniqueid'],$parameters['Queue'],$parameters['MemberName'],
		$action,$parm1,$parm2,$parm3);

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
$as->nolog=true;
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
$as-> add_event_handler('Join',handle_handler);
$as-> add_event_handler('Leave',idle_handler);
$as-> add_event_handler('QueueCallerAbandon',handle_handler);
$as-> add_event_handler('AgentCalled',handle_handler);
$as-> add_event_handler('AgentDump',handle_handler);
$as-> add_event_handler('AgentConnect',handle_handler);
$as-> add_event_handler('AgentComplete',handle_handler);
$as-> add_event_handler('QueueMemberRemoved',handle_handler);
$as-> add_event_handler('QueueMemberAdded',handle_handler);
$as-> add_event_handler('QueueMemberPaused',handle_handler);
$as-> add_event_handler('QueueMemberStatus',idle_handler);

$as-> add_event_handler('*',idle_handler);

while($res=$as->send_request('WaitEvent'))
	echo "WaitEvent: ".$res['Response']."\n";

echo $err_msg;

/* But why should we make our manager events look like the ast_queue_log ones?
  .. Stay tuned!
 */
?>
