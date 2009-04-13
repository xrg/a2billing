<?php
require_once("Class.A2Billing.inc.php");
require_once("Misc.inc.php");
require_once("Class.Mailer.inc.php");

function update_mailing(&$dbhandle,$id,$is_sent, $dbg){
	if ($is_sent)
		$state = 3;
	else
		$state = 4;

	$qry = str_dbparams($dbhandle,"UPDATE cc_mailings SET state = %#2 WHERE id = %1 ;",
		array($id,$state));
	$res = $dbhandle->Execute($qry);
	if ($dbg>2)
		echo "Update query: ". $qry . "\n";
	if (! $res){
		if ($dbg>0)
			echo "Query Failed: ". $dbhandle->ErrorMsg()."\n";
		return false;
	}
	return true;
}

function Send_Mails($dbg = 1,$dry = false){
	$dbhandle = A2Billing::DBHandle();
	
	if( $dbg>2)
		echo "Mailer: start\n";
	
	$sqlTimeFmt= _("YYYY-MM-DD HH24:MI:SS TZ");
	
	// TODO: not only select, but lock mails in 'sending' state.
	$qry = "SELECT cc_mailings.id AS id, mtype, fromname, fromemail, subject, 
		message, defargs, tomail, args, to_char(tstamp,'$sqlTimeFmt') AS mdate
		FROM cc_templatemail, cc_mailings
		WHERE cc_mailings.tmail_id = cc_templatemail.id
		AND (state = 1 OR state = 5);";
	
	$res= $dbhandle->Execute($qry);

	if (! $res){
		if ($dbg>0)
			echo "Query Failed: ". $dbhandle->ErrorMsg()."\n";
		return false;
	}elseif ($res->EOF){
		if ($dbg>2)
			echo "No mails need to be sent.\n";
		return true;
	}

	try {
	while ($row = $res->fetchRow()){
		if ($dbg>2)
			echo "Sending ". $row['mtype'] ." to " . $row['tomail'] ."\n";
		if (empty($row['tomail'])){
			if ($dbg>2)
				echo "No recepient specified!\n";
			continue;
		}
		$mai = new Mailer();
		$mai->setTo('', $row['tomail']);
		$mai->setFrom($row['fromname'] ,$row['fromemail']);
		
		// Format parameters
		$defargs = array();
		parse_str($row['defargs'],$defargs);
		$toargs = array();
		parse_str($row['args'],$toargs);
		
		$args = array_merge($defargs,$toargs);
		if ($dbg>2){
			echo "Arguments:";
			print_r($args);
			echo "\n";
		}
		
		$mai->setSubject(str_alparams($row['subject'],$args),"UTF-8");
		$mai->body=new Mailer_TextBody(str_alparams($row['message'],$args));
		
		if ($dry){
			$mai->PrintMail();
			continue;
		}
		
		try {
			if ($dbg >2)
				echo "Sending mail..";
			$mai->SendMail();
			if ($dbg>2)
				echo " done.\n";
			update_mailing($dbhandle,$row['id'],true,$dbg);
		}
		catch (Exception $ex){
			if ($dbg>2)
				echo " failed.\n";
			update_mailing($dbhandle,$row['id'],false,$dbg);
			throw $ex;
		
		}
	}
	}catch (Exception $ex) {
		if ($dbg>1)
			echo "Exception: ". $ex->getMessage();
	}
	return true;
}

?>