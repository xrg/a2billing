<?php
/** Code for standard mode calls in a2billing AGI
    Copyright(C) P. Christeas, Areski, 2007-8
*/


if (getAGIconfig('early_answer',false))
	$agi->answer();
else
	$agi->exec('Progress');
	
$agi->conlog('Standard mode',4);
$card=null;
$played_nec=false;
	
	// Repeat until we hangup
for($num_try = 0;$num_try<getAGIconfig('number_try',1);$num_try++){
	if (!$agi->is_alive)
		break;

	if (!$card){
		$card = getCard();
		$agi->conlog('Got card: ' . print_r($card,true),4);
	}
	
	else if (!empty($card['locked']))
		ReleaseCard($card);
	
	if ($card === false)
		break;

	if ($card === null)
		continue;
	
	//TODO: fix lang
	if ($card['status']==2){
		//if a new card, *only in standard mode*, activate it
		if(getAGIconfig('activate_on_call',true)){
			$QRY = str_dbparams($a2b->DBHandle(),'SELECT card_activate(%#1);',
				array($card['id']));
				
			$agi->conlog($QRY,3);
			$res = $a2b->DBHandle()->Execute($QRY);
				
			if (!$res){
				$agi->verbose('Activate card: query error!',2);
				$agi->conlog($a2b->DBHandle()->ErrorMsg(),2);
				if(getAGIconfig('say_errors',true))
					$agi-> stream_file('allison2'/*-*/, '#');
				$card=null;
				break;
			}
		} else {
			$agi->stream_file('prepaid-no-card-entered','#');
			$card=null;
			break;
		}
	}else if ($card['status']!=1){
		switch($card['status']){
		case 8: //disabled card in booth
			$agi->stream_file('prepaid-no-card-entered','#');
			break;
		case 5:
			$agi->stream_file('prepaid-card-expired','#');
			break;
		default:
			$agi->verbose('Card status: '.$card['status'] .', exiting.',2);
		}
		break;
	}

	// Here, we're authorized..
		
	/* We assume here that between consecutive attempts of calls, user's credit
		won't change! - we lock the card here */
	$card_money = CardGetMoney($card);
	if (!$card_money)
		continue;
	//TODO: play balance, intros
	
	
	if ($card_money['base'] < getAGIconfig('min_credit_2call',0.01)) {
		// not enough money!
		$agi->verbose('Not enough money!',2);
		$agi->conlog('Money: '. print_r($card_money,true),3);
		if(!$played_nec)
			$agi->stream_file('prepaid-no-enough-credit-make-call','#');
		$played_nec=true;
		ReleaseCard($card);
		$card=null;
		continue;
	}
	$played_nec=false;
	
	$dialnum = getDialNumber($card, ($num_try==0));
	if ($dialnum===false){
		$agi->stream_file('prepaid-invalid-digits','#');
		continue;
	}
	$agi->conlog("Dial number: ". $dialnum,4);
	
	// CHECK SPEEDDIAL
	getSpeedDial ($card, &$dialnum);
	
	$QRY = str_dbparams($a2b->DBHandle(),'SELECT * FROM RateEngine3(%#1, %2, %#3, now(), %4);',
		array($card['tgid'],$dialnum,$card['numplan'],$card_money['base']));
		
	$agi->conlog($QRY,3);
	$res = $a2b->DBHandle()->Execute($QRY);
	// If the rate engine has anything to Notice/Warn, display that..
	if ($notice = $a2b->DBHandle()->NoticeMsg())
		$agi->verbose('DB:' . $notice,2);
		
	if (!$res){
		$agi->verbose('Rate engine: query error!',2);
		$agi->conlog($a2b->DBHandle()->ErrorMsg(),2);
		if(getAGIconfig('say_errors',true))
			$agi-> stream_file('allison2'/*-*/, '#');
		ReleaseCard($card);
		$card=null;
		break;
	}elseif($res->EOF){
		$agi->verbose('Rate engine: no result.',2);
		$agi-> stream_file('prepaid-dest-unreachable', '#');
		continue;
	}
	$routes = $res->GetArray();
	$agi->conlog('Rate engine: found '.count($routes).' results.',3);
			
	// TODO: musiconhold
	// TODO: record_call
	// TODO: outbound cid
	
	try {
		$attempt = 1;
		$last_prob = '';
		$special_only = false;
	foreach ($routes as $route){
		if ($route['tmout'] < getAGIconfig('min_duration_2call',30)){
			$agi->conlog('Call will be too short: ',$route['tmout'],3);
			$last_prob = 'min-length';
			continue;
		}
		
		if ($route['tmout'] > getAGIconfig('max_call_duration',604800)){
			$route['tmout'] = getAGIconfig('max_call_duration',604800);
			$agi->conlog('Call truncated to: ',$route['tmout'],3);
		}
		// Check if trunk needs a feature subscription
		if(!empty($route['trunkfeat'])){
				// This field comes as a string, convert to array..
			if (!empty($card['features']) && !is_array($card['features']))
				$card['features']= sql_decodeArray($card['features']);
				
			if (empty($card['features']) || !in_array($route['trunkfeat'],$card['features'])){
				if (empty($last_prob))
					$last_prob='no-feature';
				$agi->conlog("Call is missing feature \"".$route['trunkfeat']."\", skipping route.",3);
				$agi->conlog("Features: ".print_r($card['features'],true),4);
				continue;
			}
			// feature found!
			$agi->conlog('Call using feature: '.$route['trunkfeat'],4);
		}

		$dialstr = formatDialstring($dialnum,$route, $card);
		if ($special_only && ($dialstr !==true))
			continue;
		
		if ($dialstr === null){
			$last_prob='unreachable';
			continue;
		}elseif (!$dialstr){
			$last_prob='no-dialstring';
			continue;
		}elseif($dialstr ===true){
			if (dialSpecial($dialnum,$route, $card,$card_money,$last_prob,$agi,$attempt))
				break;
			/*else if ($special_only)
				break;*/
			else
				continue;
		}
		
		if ($special_only)
			break;
		// Callerid
		if ($route['clidreplace']!== NULL){
			$new_clid = str_alparams($route['clidreplace'],
				array( useralias =>$card['useralias'],
					nplan => $card['numplan'],
					callernum => $agi->request['agi_callerid']));
		}else
			$new_clid = $agi->request['agi_callerid'];
			
		if ($route['trunkfmt'] == 15) { // Auto-answer feature for SIP
			if ($route['providertech'] == 'SIP'){
				$tmp_add_head=getAGIconfig('auto_answer_'.$route['trunkid'],'Call-Info: answer-after=0');
				
					//Hack: agi->exec doesn't like spaces, so we include the string into quotes
				if (strpos($tmp_add_head,' ') !==FALSE)
					$tmp_add_head='"'.$tmp_add_head.'"';
				if (!empty($tmp_add_head))
					$agi->exec('SIPAddHeader',$tmp_add_head);
			}
			else
				$agi->verbose("Don't know how to auto answer: ".$dialstr,3);
		}
		
			// we always reset the clid, because the previous rate
			// engine may have changed it.
		$agi->conlog("Setting clid to : $new_clid",3);
		$agi->set_variable('CALLERID(num)',$new_clid);
		
		if ($num_try==0)
			$uniqueid=$agi->request['agi_uniqueid'];
		else
			$uniqueid=$agi->request['agi_uniqueid'].'-'.$num_try;
			
		$res = $a2b->DBHandle()->Execute('INSERT INTO cc_call (cardid, attempt, cmode, '.
			'sessionid, uniqueid, nasipaddress, src, ' .
			'calledstation, destination, '.
			'srid, brid, tgid, trunk) '.
			'VALUES( ?,?,?,?,?,?,?,?,?,?,?,?,?) RETURNING id;',
			array($card['id'],$attempt, 'standard',
				$agi->request['agi_channel'],$uniqueid,NULL,$card['username'],
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
			continue;
		}elseif($res->EOF){
			$agi->verbose('Cannot mark call start in db: EOF!');
			$last_prob='call-insert';
			continue;
		}
		$call_id = $res->fetchRow();
		$agi->conlog('Start call '. $call_id['id'],4);
		
		$agi->conlog("Dial '". $route['destination']. "'@". $route['trunkcode'] . " : $dialstr",3);
		$attempt++;
		$call_res= $agi->exec('Dial',$dialstr);
		//TODO: if record, stop
		
		$hangupcause=$agi->get_variable('HANGUPCAUSE');
		
		$answeredtime = $agi->get_variable("ANSWEREDTIME");
		if (($answeredtime['result']== 0) || empty($answeredtime['data']))
			$answeredtime['data'] =0;
		$dialstatus = $agi->get_variable("DIALSTATUS");
		
		$dialedtime = $agi->get_variable("DIALEDTIME");
		if ($dialedtime['result']== 0)
			$dialedtime['data'] =0;
		
		$agi->conlog("Dial result: ".$dialstatus['data'].'('. $hangupcause['data']. ') after '. $answeredtime['data'].'sec.',2);
		//$agi->conlog("After dial, answertime: ".print_r($answeredtime,true));
		//TODO: SIP, ISDN extended status
		
		$can_continue = true;
		$cause_ext = '';
		switch ($dialstatus['data']){
		case 'BUSY':
			$last_prob='busy';
			$special_only=true;
			break;
		case 'ANSWERED':
		case 'ANSWER':
			$can_continue=false;
			$last_prob='';
			break;
		case 'CANCEL':
			$special_only=true;
			$last_prob='cancel';
			break;
		
		case 'CONGESTION':
		case 'CHANUNAVAIL':
			$last_prob='call-fail';
			break;
		case 'NOANSWER':
			$last_prob='no-answer';
			break;
		default:
			$agi->verbose("Unknown status: ".$dialstatus['data'],2);
			$special_only=true;
		}
		
		$res = $a2b->DBHandle()->Execute('UPDATE cc_call SET '.
			'stoptime = now(), sessiontime = ?, tcause = ?, hupcause = ?, '.
			'cause_ext =?, startdelay =? '.
				/* stopdelay */
			'WHERE id = ? ;',
			array( $answeredtime['data'],$dialstatus['data'],$hangupcause['data'],
				$cause_ext,
				($dialedtime['data'] - $answeredtime['data']),
				$call_id['id']));
		
		if ($notice = $a2b->DBHandle()->NoticeMsg())
			$agi->verbose('DB:' . $notice,2);

		if (!$res){
			$agi->verbose('Cannot mark call end in db! (will NOT bill)',0);
			$agi->conlog($a2b->DBHandle()->ErrorMsg(),2);
		}
	
		if (!$can_continue) //TODO: manual dialnum?
			break;

	}
		// After trying all routes, feed back the result only once.
	
		//TODO: set hangup cause accordingly
		switch($last_prob){
		case '':
			break;
		case 'min-length':
			$agi->stream_file('prepaid-no-enough-credit','#');
			break;
		case 'unreachable':
			$agi->stream_file('prepaid-dest-unreachable','#');
			break;
		default:
			$agi->conlog("Last problem: ",$last_prob);
		}
	}catch (Exception $ex){
		// Here we handle signals received
		$agi->verbose("Exception at dial:". $ex->getMessage());
		@syslog("Exception at dial:". $ex->getMessage());
		ReleaseCard($card);
		$card=null;
		break;
	}
	
}

if ($card && !empty($card['locked']))
	ReleaseCard($card);

$agi->conlog('Goodbye!',3);

if(getAGIconfig('say_goodbye',true) && $agi->is_alive)
	$agi-> stream_file('prepaid-final', '#');
$agi->hangup();

?>