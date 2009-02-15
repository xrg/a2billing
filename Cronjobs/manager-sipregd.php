#!/usr/bin/php
<?php
require_once("lib/Class.A2Billing.inc.php");
require_once("lib/Misc.inc.php");
require_once("lib/phpagi/phpagi-asmanager.php");
//require_once("lib/Provi/Class.IniImport.inc.php");

set_time_limit(0);
error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));

$dbh=null;
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


if (empty($cli_args['input'])){
	echo "Usage: $argv[0] <server> [<server> ...]\n";
	exit(1);
}

/// Manager connections, indexed by cc_a2b_server.id
$manager_connections = array();

function manager_multiconnect(array $srvnames) {
	global $manager_connections;
	global $dbh;
	global $verbose;
	
	if (empty($srvnames))
		return false;

	$srv_hostnames = array();
	$srv_grps = array();
	foreach( $srvnames  AS $serverhost) {
		echo "Want to connect to $serverhost.. \n";
		if ($serverhost[0] == '@')
			$srv_grps[] = $dbh->Quote(substr($serverhost,1));
		else
			$srv_hostnames[] = $dbh->Quote($serverhost);
	}
	
	if (empty($srv_hostnames))
		$qry_hostclause =  'FALSE';
	else
		$qry_hostclause = 'host IN('. implode($srv_hostnames,', ').')';

	if (empty($srv_grps))
		$qry_grpclause =  'FALSE';
	else
		$qry_grpclause = 'grp IN( SELECT id FROM cc_server_group '.
			'WHERE name IN ('. implode($srv_grps,', ').') )';
			
	$qry = 'SELECT DISTINCT id, host, ip, manager_username, manager_secret FROM cc_a2b_server '.
		' WHERE '.$qry_hostclause .' OR '.$qry_grpclause.';';
	
	if ($verbose>2)
		echo "Query: $qry \n";
	$res = $dbh->Execute($qry);
	
	if (!$res){
		echo $dbh->ErrorMsg() ."\n";
		return false;
	}elseif ($res->EOF) {
		if ($verbose>1)
			echo "No a2b servers found.\n";
		return false;
	}else{
		while ($row = $res->fetchRow()){
			if (!empty($row['ip']))
				$host = $row['ip'];
			else
				$host = $row['host'];
			
			$uname = $row['manager_username'];
			$as = new AGI_AsteriskManager();
			$as->nolog=true;
			if ($verbose>2)
				echo "Manager connect to $uname@$host..\n";
			if (!$as->connect($host, $uname, $row['manager_secret'])) {
				$err_msg .= str_params( _("Cannot connect to asterisk manager @%1<br>Please check manager configuration..."),
					array($host),1);
				if ($verbose)
					echo $err_str;
				continue;
			}
			$manager_connections[$row['id']] = $as;
		}
	}
	return (count($manager_connections)>0);
}


$dbh = A2Billing::DBHandle();

if (!manager_multiconnect($cli_args['input'])){
	echo "Could not connect to _any_ server, quitting..\n";
	exit(2);
}

// one time:

function iterate_regstates(){
	global $manager_connections;
	global $dbh;
	global $verbose;

	$qry = 'SELECT * FROM realtime16_sip_regstates WHERE sipiax = 5 AND reg_state >2 ;';


	if ($verbose>2)
		echo "Query: $qry \n";
	$res = $dbh->Execute($qry);
	
	if (!$res){
		echo $dbh->ErrorMsg() ."\n";
		return false;
	}elseif ($res->EOF) {
		if ($verbose>1)
			echo "No instances need update.\n";
		return false;
	}else{
		while ($row = $res->fetchRow()){
			if (empty($row['srvid']) || !isset($manager_connections[$row['srvid']])){
				if ($verbose>2)
					echo "Alterer entry belongs to other server.\n";
				continue;
			}
			$nextstate= NULL;
			switch($row['reg_state']){
			case '3': //new
				if ($verbose>2)
					echo "User " . $row['name'] .'@'.$row['regserver'] ." must be loaded\n";
				$mr = $manager_connections[$row['srvid']]->Command('sip qualify peer '.$row['name'] .' load');
				if (!$mr) {
					if ($verbose>1)
						echo "Command failed.\n";
					break;
				}
				$nextstate = 1;
				break;
			
			case '5': // to prune
				if ($verbose>2)
					echo "User " . $row['defaultuser'] .'@'.$row['host'] ." must be pruned\n";
				
				$mr = $manager_connections[$row['srvid']]->Command(
					'sip registry prune '.$row['defaultuser'] .'@'.$row['host']);
				if (!$mr) {
					if ($verbose>1)
						echo "Command failed.\n";
					break;
				}	
				$nextstate = 2;
				break;
			default:
				if ($verbose>1)
					echo "Unknown reg_state: ".$row['reg_state']."\n";
				
			}
			
			if ($nextstate !== NULL){
				// Update state
				$upd_qry = str_dbparams($dbh,'UPDATE cc_ast_instance SET reg_state = %#1 '.
					'WHERE userid = %#2 AND srvid = %#3 AND sipiax = %#4 ;' ,
					array($nextstate, $row['realtime_id'],$row['srvid'],$row['sipiax']));
				if ($verbose>2)
					echo "Query: $upd_qry \n";
				$ures = $dbh->Execute($upd_qry);
				
				if (!$ures){
					echo $dbh->ErrorMsg() ."\n";
					break;
				}
			}
		}
		return true;
	}
}

iterate_regstates();


?>
