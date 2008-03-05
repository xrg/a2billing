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

if ($cli_args['daemon']){
	$dbh = A2Billing::DBHandle(); // this instantiates things
	$do_loop = true;
	$notify_mode = function_exists('pg_wait_notify');
	if ($verbose>2){
		if ($notify_mode)
			echo "Notify (async) mode.\n";
		else
			echo "Poll (sync) mode.\n";
	}
	if ($notify_mode){
		$res = $dbh->Execute("LISTEN mail_pending;");
		if (!$res){
			if ($verbose>1)
				echo $dbh->ErrorMsg();
			if ($verbose)
				echo "Cannot setup notify listener. Polling.\n";
			$notify_mode=false;
		}
	}
	
	while ($do_loop){
		if ($verbose>2)
			echo "Mailer awaken.\n";

		if (!Send_Mails($verbose,$dry_run)){
			if ($verbose>1)
				echo "Mail sending failed.\n";
		}
		if ($notify_mode){
			pg_wait_notify($dbh->_connectionID, 300000);
			if (($nots=pg_get_notify($dbh->_connectionID)) !==false)
				if ($verbose>2)
					print_r($nots);
			//break;
		}else
			sleep(300);
	}

}else {
	// just run once
	if (Send_Mails($verbose,$dry_run))
		exit(0);
	else
		exit(1);
}
?>