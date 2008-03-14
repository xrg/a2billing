<?php
/** Card functions for a2billing AGI
    Copyright(C) P. Christeas, Areski, 2007-8
*/

/** Authorize and return an array with card data.
    \return array() with card fields, \b NULL if not found (retry mode) 
    or \b false if no card and cannot retry (eg. false callerid).
*/
function getCard(){
	global $agi;
	switch (getAGIconfig('auth-mode','callingcard')){
	case 'callerid':
		return getCard_clid();
		break;
	case 'callingcard':
		return getCard_ivr();
		break;
	case 'callerid-card':
		$card = getCard_clid();
		if (is_array($card))
			return $card;
		else
			return getCard_ivr();
		break;
	case 'accountcode':
		return getCard_acode();
		break;
	case 'pin-and-clid':
		// TODO
		// break;
	default:
		$agi->verbose('Unknown auth-mode: '. getAGIconfig('auth-mode','callingcard'));
		return null;
	}
}

function getCard_clid(){
	global $a2b;
	global $agi;
	$dbhandle =$a2b->DBHandle();
	
	$res = $dbhandle->Execute('SELECT card.id, tariffgroup AS tgid, card.username, card.status, ' .
		'card.numplan, card.useralias '.
		'FROM cc_card_dv AS card, cc_callerid '.
		'WHERE cc_callerid.cardid = card.id '.
		'AND cc_callerid.activated = true '.
		'AND cc_callerid.cid = ? LIMIT 1 ;',
		array($agi->request['agi_callerid']));
	
	if (!$res){
		$agi->verbose('Cannot auth-clid: '. $dbhandle->ErrorMsg());
		return false;
	}
	if ($res->EOF){
		$agi->verbose('No entry found for auth-clid');
		return false;
	}
	$agi->conlog('Auth-clid: found card for clid',4);
	return $res->fetchRow();
}


function getCard_ivr(){
	global $a2b;
	global $agi;
	$dbhandle =$a2b->DBHandle();
	
		// We have to answer, so that we have returning sound.
	if (!$agi->is_answered){
		$agi->conlog('Auth-ivr: answer',4);
		$agi->answer();
		}

	$pin_prompt = getAGIconfig('pin-prompt',"prepaid-enter-pin-number");
	$pin_timeout = getAGIconfig('pin-timeoute',6000);
	$pin_maxlen = getAGIconfig('pin-maxlen',15);
	
	$agi->conlog('Auth-ivr: asking for PIN',4);
	$res_dtmf = $agi->get_data($pin_prompt, $pin_timeout,$pin_maxlen);
	
	$agi->conlog('Auth-ivr: result ' . print_r($res_dtmf,true),3);
	if (!isset($res_dtmf['result'])){
		$agi->conlog('No PIN entered',2);
		$agi-> stream_file("prepaid-no-card-entered", '#');
		return null;
	}
	$pinnum = $res_dtmf['result'];
	if ((strlen($pinnum) < getAGIconfig('pin-minlen',10)) || (strlen($pinnum) > $pin_maxlen)) {
		$agi->conlog('Invalid PIN',2);
		$agi-> stream_file("prepaid-invalid-digits", '#');
		return null;
	}
	
	$res = $dbhandle->Execute('SELECT card.id, tariffgroup AS tgid, card.username, card.status, ' .
		'card.numplan, card.useralias '.
		'FROM cc_card_dv AS card '.
		'WHERE card.username = ? LIMIT 1 ;',
		array($pinnum));
	
	if (!$res){
		$agi->verbose('Cannot auth-ivr: '. $dbhandle->ErrorMsg());
		if(getAGIconfig('say_errors',true))
			$agi-> stream_file('allison2'/*-*/, '#');
		return null;
	}
	if ($res->EOF){
		$agi->verbose("Auth-ivr: no such username: '$pinnum' ",2);
		$agi-> stream_file("prepaid-auth-fail",'#');
		return null;
	}
	$agi->conlog('Auth-ivr: found card.',4);
	return $res->fetchRow();
}


function getCard_acode(){
	global $a2b;
	global $agi;
	$dbhandle =$a2b->DBHandle();
		
	if (!isset($agi->request['agi_accountcode']) || (strlen($agi->request['agi_accountcode'])<3)){
		$agi->verbose("No accountcode for auth",2);
		return false;
	}
	
	$acodes = explode(':',$agi->request['agi_accountcode']);

	switch($acodes[0]){
	case 'card':
		$res = $dbhandle->Execute('SELECT card.id, tariffgroup AS tgid, card.username, card.status, ' .
			'card.numplan, card.useralias '.
			'FROM cc_card_dv AS card '.
			'WHERE card.id = ? LIMIT 1 ;',
			array($acodes[1]));
		break;
	case 'booth':
		$res = $dbhandle->Execute('SELECT card.id, tariffgroup AS tgid, card.username, card.status, ' .
			'card.numplan, card_useralias '.
			'FROM cc_card_dv AS card, cc_booth '.
			'WHERE cc_booth.cur_card_id = card.id '.
			'AND cc_booth.id = ? LIMIT 1 ;',
			array($acodes[1]));
		break;
	case 'remote-agent':
			//used by remote hosts 
		$agid = $acodes[1];
		$anivar = $agi->get_variable('CALLERID(ANI)');
		if ($anivar['result'] ==0) {
			$agi->verbose('No ANI set for remote-agent auth');
			return false;
		}
		// TODO: after this, we MUST reset ANI to the proper value, or else
		// we may leak it to the provider!
		
		$acodes = explode(':',$anivar['data']);
		switch ($acodes[0]){
		case 'card':
			$res = $dbhandle->Execute('SELECT card.id, tariffgroup AS tgid, card.username, card.status, ' .
				'card.numplan, card.useralias '.
				'FROM cc_card_dv AS card '.
				'WHERE card.id = ? AND agentid = ? LIMIT 1 ;',
				array($acodes[1],$agid));
			break;
		case 'booth':
			$res = $dbhandle->Execute('SELECT card.id, tariffgroup AS tgid, card.username, card.status, ' .
				'card.numplan, card.useralias '.
				'FROM cc_card_dv AS card, cc_booth '.
				'WHERE cc_booth.cur_card_id = card.id '.
				'AND cc_booth.id = ? AND cc_booth.agentid = ? LIMIT 1 ;',
				array($acodes[1],$agid));
			break;
		default:
			$agi->verbose('Unknown accountcode at remote: '.$anivar['data']);
			return false;
		}
		break;
	default:
		$agi->verbose('Unknown accountcode: '.$agi->request['agi_accountcode']);
		return false;
	}
	
	if (!$res){
		$agi->verbose('Cannot auth-acode: '. $dbhandle->ErrorMsg());
		if(getAGIconfig('say_errors',true))
			$agi-> stream_file('allison2'/*-*/, '#');
		return false;
	}
	if ($res->EOF){
		$agi->verbose("Accountcode: no card for ".$acodes[0].": ".$acodes[1]." ",2);
		if ($acodes[0] =='booth')
			$agi-> stream_file("prepaid-no-card-entered",'#');
		else
			$agi-> stream_file("prepaid-auth-fail",'#');
		return false;
	}
	$agi->conlog('Auth-acode: found card.',4);
	return $res->fetchRow();
}

// Lock the card and return remaining money

function CardGetMoney(&$card){
	global $a2b;
	global $agi;
	$dbhandle =$a2b->DBHandle();
	
	$res = $dbhandle->Execute ('SELECT * FROM card_call_lock(?);',array($card['id']));
	if ($notice = $a2b->DBHandle()->NoticeMsg())
		$agi->verbose('DB:' . $notice,2);
	
	if (!$res){
		$emsg = $dbhandle->ErrorMsg();
		if (substr($emsg,0,17) =='ERROR:  call_lock'){
			$msga= explode('|',$emsg);
			$agi->verbose('Could not lock card: '. $msga[3]);
			//$agi->conlog("Message: " . print_r($msga,true),4);
			switch ($msga[1]){
			case 'in-use':
				$agi->stream_file('prepaid-card-in-use','#');
				break;
			case 'no-find':
				//TODO
				break;
			case 'wrong-status':
				//TODO
				break;
			default:
				$agi->conlog('Unknown result from card_call_lock: ' . $msga[1],3);
				
			}
		}else $agi->verbose('Could not lock card: '. $emsg );
		return null;
	}
	if ($res->EOF){
		$agi->verbose('No card from card_call_lock(), why?');
		return null;
	}
	
	$card['locked']=true;
	return $res->fetchRow();
}

//Unlock the card (decrease usage count)
function ReleaseCard(&$card){
	global $a2b;
	global $agi;
	$res = $a2b->DBHandle()->Execute ('SELECT card_call_release(?);',array($card['id']));
	if (!$res)
		$agi->verbose('Could not release card: '. $a2b->DBHandle()->ErrorMsg(),2);
	$card['locked']=false;
}

?>