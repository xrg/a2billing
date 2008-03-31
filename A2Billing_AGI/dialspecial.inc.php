<?php
/** Special dial functions for a2billing AGI
    Copyright(C) P. Christeas, Areski, 2007-8
*/

/** Special dial modes, like VoiceMail etc */
function dialSpecial($dialnum,$route, $card,$card_money,&$last_prob,$agi){
	global $a2b;
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
		$params = array( clid => $agi->request['agi_callerid'], clname=> $agi->request['agi_calleridname'],
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
	default:
		$agi->verbose("Cannot dial special with format ".$route['trunkfmt'],3);
		return false;
	}
}
?>