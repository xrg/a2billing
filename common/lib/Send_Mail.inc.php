<?php
require_once("Class.A2Billing.inc.php");
require_once("Misc.inc.php");

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
	
	$qry = "SELECT cc_mailings.id AS id, mtype, fromname, fromemail, subject, message, defargs, tomail, args
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

	while ($row = $res->fetchRow()){
		if ($dbg>2)
			echo "Sending ". $row['mtype'] ." to " . $row['tomail'] ."\n";
		if (empty($row['tomail'])){
			if ($dbg>2)
				echo "No recepient specified!\n";
			continue;
		}
		
		$to_hdr = $row['tomail'];
		
		// Format "From:" header
		$headers=array();
		if (!empty($row['fromemail'])){
			$str ='From: ';
			if (!empty($row['fromname']))
				$str .= $row['fromname'] . ' <';
			$str .= $row['fromemail'];
			if (!empty($row['fromname']))
				$str .= '>';
			$headers[] = $str;
		}
		
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
		
		$msg=str_alparams($row['message'],$args);
		$subject = str_alparams($row['subject'],$args);
		
		if ($dry){
		echo implode("\n", $headers) . "\n";
		echo "To: $to_hdr\n";
		echo "Subject: $subject\n\n";
		echo $msg;
		echo "\n\n";
		continue;
		}
		
		// Here, a real mail is sent..
		if ($dbg >2)
			echo "Sending mail..";
		if(@mail($to_hdr,$subject,$msg,implode("\r\n",$headers))){
			if ($dbg>2)
				echo " done.\n";
			update_mailing($dbhandle,$row['id'],true,$dbg);
		}else {
			if ($dbg>2)
				echo " failed.\n";
			update_mailing($dbhandle,$row['id'],false,$dbg);
		}
	}
	return true;
}

?>