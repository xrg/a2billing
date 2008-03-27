<?php
/** Code for DID mode calls in a2billing AGI
    Copyright(C) P. Christeas, Areski, 2007-8
*/

/* Retrieve a card structure from the DID engine query row */
function getDIDCard($didrow){
	return array( id => $didrow['card_id'],tgid =>$didrow['tgid'],
		username =>$didrow['username'], status=> $didrow['card_status'],
		numplan => $didrow['nplan'], useralias => $didrow['useralias']);
}

if (getAGIconfig('early_answer',false))
	$agi->answer();
else
	$agi->exec('Progress');
	
$did_extension = $agi->request['agi_extension'];
if ($argc > 3 && strlen($argv[3]) > 0 )
	$did_code = $argv[3];
else
	$did_code = '';

$agi->conlog('DID mode, ext: '.$did_extension .'@'.$did_code,4);
$card=null;

$QRY = str_dbparams($a2b->DBHandle(),'SELECT id(card) AS card_id, tgid, username(card), status(card) AS card_status, ' .
		'nplan, useralias(card), dialstring, dgid, brid2, buyrate2 '.
		' FROM DIDEngine(%1, %2, now());',
	array($did_extension,$did_code));
	
$agi->conlog($QRY,3);
$didres = $a2b->DBHandle()->Execute($QRY);
// If the rate engine has anything to Notice/Warn, display that..
// unfortunately, this only tell us the *last* notice
if ($notice = $a2b->DBHandle()->NoticeMsg())
	$agi->verbose('DB:' . $notice,2);
	
if (!$didres){
	$agi->verbose('DID engine: query error!',2);
	$agi->conlog($a2b->DBHandle()->ErrorMsg(),2);
	if(getAGIconfig('say_errors',true))
		$agi-> stream_file('allison2'/*-*/, '#');
	$card=null;
	break;
}elseif($didres->EOF){
	$agi->verbose('DID engine: no result.',2);
	$agi-> stream_file('prepaid-dest-unreachable', '#');
	continue;
}

$num_try=-1; // in a symmetric way, num_try will index the DID rows

/* The first level loop: iterate over DID engine results.
   It is /very/ hard to directly feed those results in the RateEngine, 
   because SETOF fns() cannot be fed into function arguments in SQL AFAIK */
while ($didrow = $didres->fetchRow()){

	// TODO: insert an attempt #0 call for the first leg here.
	// At early answer the start time should be the AGI starttime.
	
	$num_try++;
	$agi->conlog(print_r($didrow['card'],true),4);
	$card= getDIDCard($didrow);
	$agi->conlog('Got card: ' . print_r($card,true),4);
	if ($card === false)
		break;

	if ($card === null)
		continue;
	
	//TODO: fix lang
	if ($card['status']!=1){
		switch($card['status']){
		case 8:
			$last_prob = 'card-stopped';
			break;
		case 5:
			$last_prob = 'card-expired';
			break;
		default:
			$last_prob = 'card-status';
			$agi->verbose('Card status: '.$card['status'] .', exiting.',2);
		}
		break;
	}
	
	$card_money = CardGetMoney($card);
	if (!$card_money){
		ReleaseCard($card);
		$card=null;
		continue;
	}
	
	if ($card_money['base'] < getAGIconfig('min_credit_2did',0.00)) {
		// not enough money!
		$agi->verbose('Not enough money!',2);
		$agi->conlog('Money: '. print_r($card_money,true),3);
		// Never tell the caller about it!
		//$agi->stream_file('prepaid-no-enough-credit','#');
		ReleaseCard($card);
		$card=null;
		continue;
	}

	$QRY = str_dbparams($a2b->DBHandle(),'SELECT * FROM RateEngine2(%#1, %2, %#3, now(), %4);',
		array($didrow['tgid'],$didrow['dialstring'],$didrow['nplan'],$card_money['base']));
		
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

	try {
		$attempt = 1;
		$last_prob = '';
	foreach ($routes as $route){
		if ($route['tmout'] < getAGIconfig('min_duration_2did',30)){
			$agi->conlog('Call will be too short: ',$route['tmout'],3);
			$last_prob = 'min-length';
			continue;
		}
		
		if ($route['tmout'] > getAGIconfig('max_did_duration',604800)){
			$route['tmout'] = getAGIconfig('max_did_duration',604800);
			$agi->conlog('Call truncated to: ',$route['tmout'],3);
		}

		$dialstr = formatDialstring($didrow['dialstring'],$route, $card);
		if ($dialstr === null){
			$last_prob='unreachable';
			continue;
		}elseif (!$dialstr){
			$last_prob='no-dialstring';
			continue;
		}elseif($dialstr ===true){
			if (dialSpecial($dialnum,$route, $card,$last_prob,$agi))
				break;
			else
				continue;
		}
		
		// Callerid
		if ($route['clidreplace']!== NULL){ // *-* from route or did batch?
			$new_clid = str_alparams($route['clidreplace'],
				array( useralias =>$card['useralias'],
					nplan => $card['numplan'],
					callernum => $agi->request['agi_callerid']));
		}else
			$new_clid = $agi->request['agi_callerid'];
		
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
			array($card['id'],$attempt, 'did',
				$agi->request['agi_channel'],$uniqueid,NULL,$agi->request['agi_callerid'],
				$did_extension,$route['destination'],
				$route['srid'],$route['brid'],$didrow['tgid'],$route['trunkid']));
		
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
		if ($answeredtime['result']== 0)
			$answeredtime['data'] =0;
		$dialstatus = $agi->get_variable("DIALSTATUS");
		
		$dialedtime = $agi->get_variable("DIALEDTIME");
		if ($dialedtime['result']== 0)
			$dialedtime['data'] =0;
		
		$agi->conlog("Dial result: ".$dialstatus['data'].'('. $hangupcause['data']. ') after '. $answeredtime['data'].'sec.',2);
		//$agi->conlog("After dial, answertime: ".print_r($answeredtime,true));
		//TODO: SIP, ISDN extended status
		
		$can_continue = false;
		$cause_ext = '';
		switch ($dialstatus['data']){
		case 'BUSY':
			$last_prob='busy';
			break;
		case 'ANSWERED':
		case 'ANSWER':
		case 'CANCEL':
			$last_prob='';
			break;
		
		case 'CONGESTION':
		case 'CHANUNAVAIL':
			$last_prob='call-fail';
			$can_continue = true;
			break;
		case 'NOANSWER':
			$last_prob='no-answer';
			$can_continue = true;
			break;
		default:
			$agi->verbose("Unknown status: ".$dialstatus['data'],2);
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

	} //for
	}catch (Exception $ex){
		// Here we handle signals received
		$agi->verbose("Exception at dial:". $ex->getMessage());
		@syslog("Exception at dial:". $ex->getMessage());
		ReleaseCard($card);
		$card=null;
		break;
	}


} // while

//TODO: set hangup cause accordingly
if ($last_prob)
	$agi->conlog("Last problem: ".$last_prob,2);

if ($card && !empty($card['locked']))
	ReleaseCard($card);

$agi->conlog('Goodbye!',3);

if(getAGIconfig('say_did_goodbye',false) && $agi->is_alive)
	$agi-> stream_file('prepaid-final', '#');
$agi->hangup();
?>