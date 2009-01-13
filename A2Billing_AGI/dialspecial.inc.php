<?php
/** Special dial functions for a2billing AGI
    Copyright(C) P. Christeas, Areski, 2007-8
*/

/** Special dial modes, like VoiceMail etc */
function dialSpecial($dialnum,$route, $card,$card_money,&$last_prob,$agi, $attempt){
	global $a2b;
	global $mode;
	global $did_clidname;
	$dbhandle =$a2b->DBHandle();
	
	if($route['stripdigits']>0)
		$dialnum=substr($route['dialstring'],$route['stripdigits']);
	else
		$dialnum=$route['dialstring'];

	$dialn = null;
	switch ($route['trunkfmt']){
	case 9: // Group dial
		require_once("groupdial.inc.php");
		return groupDial($dialnum,$route, $card,$card_money,$last_prob,$agi);
		break; // group 
	case 10:
		$dialn = array($card['numplan'],$dialnum);
	case 11:
		if (!$dialn){ // case 11
			$dialn = explode('-',$dialnum);
			if ($dialn[0] == 'L')
				$dialn[0]=$card['numplan'];
		}
		//todo: locale field!
		$qry = str_dbparams($dbhandle,"SELECT email, 'C' AS locale FROM cc_card, cc_card_group
			WHERE cc_card.grp = cc_card_group.id AND cc_card_group.numplan = %#1
			  AND cc_card.useralias = %2 AND cc_card.status =1;", $dialn);
		$res = $dbhandle->Execute($qry);
		if (!$res){
			$agi->verbose('Cannot query peer: '. $dbhandle->ErrorMsg());
			return false;
		}else if ($res->EOF){
			$agi->conlog("Query: $qry",5);
			$agi->verbose("Peer email: cannot find peer ".$dialnum,2);
			return false;
		}
		$row= $res->fetchRow();
		if (empty($row['email'])){
			$agi->conlog("User at $dialnum, has no email, skipping.",3);
			return true;
		}
		if (empty($route['providerip']))
			$tmpl='missed-call';
		else
			$tmpl=$route['providerip'];
			// TODO: put more data in params..
			// TODO: do NOT issue callerid, when callingpres prohibits is. It is bad !
		if (!empty($did_clidname))
			$clname=$did_clidname;
		else
			$clname = $agi->request['agi_calleridname'];
		$params = array( clid => $agi->request['agi_callerid'], clname=> $clname,
			 last => $last_prob);
		$res = $dbhandle->Execute( "SELECT create_mail(?,?,?,?);",
			array($tmpl,$row['email'],$row['locale'], arr2url($params)));
		if (!$res){
			$agi->verbose('Cannot create mail: '. $dbhandle->ErrorMsg(),2);
			return false;
		}
		$str = $dbhandle->NoticeMsg();
		if ($str)
			$agi->verbose($str,3);
		// FIXME: how well should the email be quoted before fed to the AGI?
		$agi->conlog("Mail notification queued for ". str_replace("\n",'',$row['email']));
		return true;
		
	case 12: // local voicemail
		$dialn = array($card['numplan'],$dialnum);
	case 13: // cross-nplan voicemail
			// We obviously cannot get any voicemail if the caller has
			// hung up.
		if ($last_prob =='cancel')
			return false;
		if (!$dialn){ // case 11
			$dialn = explode('-',$dialnum);
			if ($dialn[0] == 'L')
				$dialn[0]=$card['numplan'];
		}
		//todo: locale field!
		$qry = str_dbparams($dbhandle,"SELECT username, email, 'C' AS locale FROM cc_card, cc_card_group
			WHERE cc_card.grp = cc_card_group.id AND cc_card_group.numplan = %#1
			  AND cc_card.useralias = %2 AND cc_card.status =1;", $dialn);
		$res = $dbhandle->Execute($qry);
		if (!$res){
			$agi->verbose('Cannot query peer: '. $dbhandle->ErrorMsg());
			return false;
		}else if ($res->EOF){
			$agi->conlog("Query: $qry",5);
			$agi->verbose("Peer voicemail: cannot find peer ".$dialnum,2);
			return false;
		}
		$row= $res->fetchRow();
		$vmcontext=getAGIconfig('vmcontext','default');
		$mailbox=$row['username'];
		if (!empty($vmcontext))
			$mailbox.='@'.$vmcontext;
		
		$agi->conlog("Voicemail for $mailbox",3);
		if ($last_prob=='busy')
			$mopts='b';
		else
			$mopts='u';
		$mopts .= $route['trunkparm'];  // usually, the 's'
		$uniqueid=$agi->request['agi_uniqueid']; // . $attempt;
		
		$res = $a2b->DBHandle()->Execute('INSERT INTO cc_call (cardid, attempt, cmode, '.
			'sessionid, uniqueid, nasipaddress, src, ' .
			'calledstation, destination, '.
			'srid, brid, tgid, trunk) '.
			'VALUES( ?,?,?,?,?,?,?,?,?,?,?,?,?) RETURNING id;',
			array($card['id'], $attempt, $mode, $agi->request['agi_channel'],$uniqueid,NULL,$card['username'],
				$dialnum,$route['destination'],
				$route['srid'],$route['brid'],$route['tgid'],$route['trunkid']));
		
		if ($notice = $a2b->DBHandle()->NoticeMsg())
			$agi->verbose('DB:' . $notice,2);

		if (!$res){
			$agi->verbose('Cannot mark call start in db!');
			$agi->conlog($a2b->DBHandle()->ErrorMsg(),2);
				// This error may mean that trunk is in use etc.
				// If call cannot be billed, we'd better abort it.
				$last_prob='call-insert';
			return false;
		}elseif($res->EOF){
			$agi->verbose('Cannot mark call start in db: EOF!');
			$last_prob='call-insert';
			return false;
		}
		$call_id = $res->fetchRow();
		
		$agires= $agi->exec('VoiceMail',array($mailbox,$mopts));
		
		// $agi->conlog("VM result: ". print_r($agires,true),5);
		
		$vmstatus=$agi->get_variable('VMSTATUS');
		$agi->conlog("VM status: ". print_r($vmstatus,true),5);

		$res = $dbhandle->Execute('UPDATE cc_call SET '.
			'stoptime = now(), sessiontime = EXTRACT(epoch from (now() - starttime))::INTEGER, tcause = ?, hupcause = 0 '.
			'WHERE id = ? ;',
			array( $vmstatus['data'], $call_id['id']));
		
		if ($notice = $dbhandle->NoticeMsg())
			$agi->verbose('DB:' . $notice,2);

		if (!$res){
			$agi->verbose('Cannot mark call end in db! (will NOT bill)',0);
			$agi->conlog($dbhandle->ErrorMsg(),2);
		}else if ($dbhandle->Affected_Rows()<1){
			$agi->verbose('Could not mark call end! (will NOT bill)',1);
			
		}
		
		if ($vmstatus['data']!='SUCCESS')
			return false;

		// TODO: here, try to create an email with the voicemail...
/*		if (empty($route['providerip']))
			$tmpl='missed-call';
		else
			$tmpl=$route['providerip'];
			// TODO: put more data in params..
			// TODO: do NOT issue callerid, when callingpres prohibits is. It is bad !
		$params = array( clid => $agi->request['agi_callerid'], clname=> $agi->request['agi_calleridname'],
			 last => $last_prob);
		$res = $dbhandle->Execute( "SELECT create_mail(?,?,?,?);",
			array($tmpl,$row['email'],$row['locale'], arr2url($params)));
		if (!$res)
			$agi->verbose('Cannot create mail: '. $dbhandle->ErrorMsg(),2);
			return false;
		}
		$str = $dbhandle->NoticeMsg();
		if ($str)
			$agi->verbose($str,3);
		// FIXME: how well should the email be quoted before fed to the AGI?
		$agi->conlog("Mail notification queued for ". str_replace("\n",'',$row['email']));
		*/
		return true;
	
	case 14: // voicemail Main (user's menu)
		$vmcontext=getAGIconfig('vmcontext','default');
		$mailbox=$card['username'];
		if (!empty($vmcontext))
			$mailbox.='@'.$vmcontext;
		
		$agi->conlog("Voicemail Main for $mailbox",3);
		$uniqueid=$agi->request['agi_uniqueid'];
		
		$res = $a2b->DBHandle()->Execute('INSERT INTO cc_call (cardid, attempt, cmode, '.
			'sessionid, uniqueid, nasipaddress, src, ' .
			'calledstation, destination, '.
			'srid, brid, tgid, trunk) '.
			'VALUES( ?,?,\'vm-main\',?,?,?,?,?,?,?,?,?,?) RETURNING id;',
			array($card['id'], $attempt, $agi->request['agi_channel'],$uniqueid,NULL,$card['username'],
				$dialnum,$route['destination'],
				$route['srid'],$route['brid'],$route['tgid'],$route['trunkid']));
		
		if ($notice = $a2b->DBHandle()->NoticeMsg())
			$agi->verbose('DB:' . $notice,2);

		if (!$res){
			$agi->verbose('Cannot mark call start in db!');
			$agi->conlog($a2b->DBHandle()->ErrorMsg(),2);
				// This error may mean that trunk is in use etc.
				// If call cannot be billed, we'd better abort it.
				$last_prob='call-insert';
			return false;
		}elseif($res->EOF){
			$agi->verbose('Cannot mark call start in db: EOF!');
			$last_prob='call-insert';
			return false;
		}
		$call_id = $res->fetchRow();

		$agires= $agi->exec('VoiceMailMain',array($mailbox,$route['trunkparm']));
				
// 		$hangupcause=$agi->get_variable('HANGUPCAUSE');
// 		
// 		$answeredtime = $agi->get_variable("ANSWEREDTIME");
// 		if ($answeredtime['result']== 0)
// 			$answeredtime['data'] =0;
// 		$dialstatus = $agi->get_variable("DIALSTATUS");
// 		
// 		$dialedtime = $agi->get_variable("DIALEDTIME");
// 		if ($dialedtime['result']== 0)
// 			$dialedtime['data'] =0;
// 		
// 		$agi->conlog("Dial result: ".$dialstatus['data'].'('. $hangupcause['data']. ') after '. $answeredtime['data'].'sec.',2);

		$res = $dbhandle->Execute('UPDATE cc_call SET '.
			'stoptime = now(), sessiontime = EXTRACT(epoch from (now() - starttime))::INTEGER, tcause = \'ANSWER\', hupcause = 0 '.
			'WHERE id = ? ;',
			array( $call_id['id']));
		
		if ($notice = $dbhandle->NoticeMsg())
			$agi->verbose('DB:' . $notice,2);

		if (!$res){
			$agi->verbose('Cannot mark call end in db! (will NOT bill)',0);
			$agi->conlog($dbhandle->ErrorMsg(),2);
		}else if ($dbhandle->Affected_Rows()<1){
			$agi->verbose('Could not mark call end! (will NOT bill)',1);
			
		}

		//if ($vmstatus['data']!='SUCCESS')
		//	return false;
		
		return true;
	default:
		$agi->verbose("Cannot dial special with format ".$route['trunkfmt'],3);
		return false;
	}
}
?>