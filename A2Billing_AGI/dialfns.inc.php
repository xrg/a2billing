<?php
/** Dial number functions for a2billing AGI
    Copyright(C) P. Christeas, Areski, 2007-8
*/

/** Retrieve the Dial number for standard calls
    \param $first_try Boolean. if true, we can use first-time-only methods,
    	like dnid.
*/

function getDialNumber(&$card, $first_try){
	global $agi;
	
	if (getAGIconfig('use_dnid',true) && $first_try){
		// TODO, conditional
		if ($agi->request['agi_extension']=='s')
			return $agi->request['agi_dnid'];
		else
			return $agi->request['agi_extension'];
	} else {
		if (!$agi->is_answered){
			$agi->conlog('Auth-ivr: answer',4);
			$agi->answer();
		}

		$num_try = getAGIconfig('destination-tries',3);
		$dprompt = getAGIconfig('destination-prompt',"prepaid-enter-dest");
		$dtimeout = getAGIconfig('destination-timeoute',6000);
		$dmaxlen = getAGIconfig('destination-maxlen',30);
		$dminlen = getAGIconfig('destination-minlen',1);
		
		$agi->conlog('GetDestination: asking for Destination, up to '. $num_try . ' tries.',4);
		for ($i = 0; $i < $num_try; $i++) {
			$res_dtmf = $agi->get_data($dprompt, $dtimeout, $dmaxlen);
			
			// TODO: bail out only on some results
			// NOTE: data='timeout' may happen when ivr times out, but still
			// have a valid number
			
			$agi->conlog('GetDestination: result ' . print_r($res_dtmf,true),3);
			if (!isset($res_dtmf['result']) || ($res_dtmf['result']== -1)){
				$agi->conlog('No Destination entered',2);
				// $agi-> stream_file("prepaid-invalid-digits", '#');
				return false;
			}
			if (strlen($res_dtmf['result'])< $dminlen) {
				$agi-> stream_file("prepaid-invalid-digits", '#');
			}
			else
				return $res_dtmf['result'];
		}
		
		$agi->verbose('No right destination entered through DTMF.',3);
	}
	
	return false;
}

function getSpeedDial ($card, &$dialnum){
	global $a2b;
	global $agi;
	
	// SPEED DIAL HANDLER
	if (($sp_prefix=getAGIconfig('speeddial_prefix',NULL))!=NULL) {
		if (strncmp($dialnum,$sp_prefix,strlen($sp_prefix))==0) {
			// translate the speed dial.
			$QRY = str_dbparams ($a2b->DBHandle() ,"SELECT phone, name FROM speeddials WHERE card_id = %#1 AND speeddial = %2", 
								array($card['id'], substr($dialnum,strlen($sp_prefix))));
		    $agi->conlog($QRY,3);
			$res = $a2b->DBHandle()->Execute($QRY);
			
			// If the rate engine has anything to Notice/Warn, display that..
			if ($notice = $a2b->DBHandle()->NoticeMsg())
				$agi->verbose('DB:' . $notice,2);
				
			if (!$res) {
				$agi->verbose('Speed Dial: query error!',2);
				$agi->conlog($a2b->DBHandle()->ErrorMsg(),2);
				if(getAGIconfig('say_errors',true))
					$agi-> stream_file('allison2'/*-*/, '#');
				break;
			} elseif ($res->EOF) {
				$agi->verbose('Speed Dial: no result.',2);
			}
			$arr_speeddial = $res->fetchRow();
			$agi->conlog('Speed Dial : found '.$arr_speeddial['phone'],4);
			$dialnum = $arr_speeddial['phone'];
		}
	}
}

?>
