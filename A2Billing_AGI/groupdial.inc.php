<?php

/** Code for group dial functionality */

/* Analyze the groupstring */
function groupstr_analyze($gstr){
	$strp=0;
	$stro=0;

	$sarr=array();
	$ret=array();
	if (preg_match('/^\((.*)\)\s*$/',$gstr,$sarr)){
		$str=$sarr[1];
		$str1='';
		for($strp=0;$strp<strlen($str);$strp++)
			if ($str[$strp]==','){
				$ret[]=$str1;
				$str1='';
			}
			else
				$str1.=$str[$strp];
		if($str1)
			$ret[]=$str1;
	}
	else
		return array($gstr);
		
	return $ret;
}

function groupDial($dialnum,$route, $card,$card_money,&$last_prob,$agi){
	global $a2b;
	// First, parse *our* string, so that we can form the
	// destinations.
	$gstr = $route['providerip'];
		//TODO: need to combine providerIP & dialstring?
	if (empty($gstr))
		$gstr = $dialnum;
		
	$agi->conlog("Group dialnum: \"$gstr\"",5);
	$gdests = groupstr_analyze($gstr/*,$route, $card..*/);
	
	
	// Then, find all rateengine results for each destination
	$QRY = str_dbparams($a2b->DBHandle(),'SELECT * FROM RateEngine2(%#1, ?, %#2, now(), %3);',
		array($card['tgid'],$card['numplan'],$card_money['base']));
	
	$all_routes=array();
	foreach($gdests as $gdest){
		$agi->conlog($QRY." [?= $gdest]",4);
		$res = $a2b->DBHandle()->Execute($QRY,array($gdest));
		// If the rate engine has anything to Notice/Warn, display that..
		if ($notice = $a2b->DBHandle()->NoticeMsg())
			$agi->verbose('DB:' . $notice,2);
		
		if (!$res){
			$agi->verbose('Rate engine: query error!',2);
			$agi->conlog($a2b->DBHandle()->ErrorMsg(),3);
			break;
		}elseif($res->EOF){
			$agi->verbose('Rate engine: no result.',3);
			continue;
		}
		$routes = $res->GetArray();
		
		// now, find the first route that might work
		foreach ($routes as $aroute){
			if ($aroute['tmout'] < getAGIconfig('min_duration_2call',30)){
				$agi->conlog('Call will be too short: ',$aroute['tmout'],4);
				$last_prob = 'min-length';
				continue;
			}
			
			if ($aroute['tmout'] > getAGIconfig('max_call_duration',604800)){
				$aroute['tmout'] = getAGIconfig('max_call_duration',604800);
				$agi->conlog('Call truncated to: ',$aroute['tmout'],4);
			}
			// Check if trunk needs a feature subscription
			if(!empty($aroute['trunkfeat'])){
					// This field comes as a string, convert to array..
				if (!empty($card['features']) && !is_array($card['features']))
					$card['features']= sql_decodeArray($card['features']);
					
				if (empty($card['features']) || !in_array($aroute['trunkfeat'],$card['features'])){
					if (empty($last_prob))
						$last_prob='no-feature';
					$agi->conlog("Call is missing feature \"".$aroute['trunkfeat']."\", skipping route.",3);
					$agi->conlog("Features: ".print_r($card['features'],true),4);
					continue;
				}
				// feature found!
				$agi->conlog('Call using feature: '.$aroute['trunkfeat'],5);
			}
	
			$dialstr = formatDialstring($dialnum,$aroute, $card,false);
			if ($dialstr === null){
				$last_prob='unreachable';
				continue;
			}elseif (!$dialstr){
				$last_prob='no-dialstring';
				continue;
			}elseif($dialstr ===true){
				// We cannot use other special trunks in group.
				continue;
			}
			if ($aroute['clidreplace']!== NULL){
				$new_clid = str_alparams($aroute['clidreplace'],
					array( 'useralias' =>$card['useralias'],
						'nplan' => $card['numplan'],
						'callernum' => $agi->request['agi_callerid']));
			}else
				$new_clid = $agi->request['agi_callerid'];

			// add this route to the available ones
			$all_routes[]= array('r' =>$aroute, 'str'=>$dialstr, 'clid' =>$new_clid);
			
			 //but then, discard all other possibilities for this destination.
			break;
		}
	}

	//$agi->conlog("Group dialing routes: ".print_r($all_routes,true),4);
	
	// Now, iterate over all the routes to find some useful stuff
	$agg_route=array();
	$agg_strs= array();
	foreach($all_routes as $aroute){
			// Try to have a common CLID.
		if (!isset($agg_route['clid']))
			$agg_route['clid']=$aroute['clid'];
		elseif($agg_route['clid'] != $aroute['clid'])
			$agg_route['clid']='';
		
		if (!isset($agg_route['tmout']))
			$agg_route['tmout']=$aroute['r']['tmout'];
		elseif($agg_route['tmout']>$aroute['r']['tmout'])
			$agg_route['tmout']=$aroute['r']['tmout'];
			
		$agg_strs[]=$aroute['str'];
	}
	$agg_route['str']=implode('&',$agg_strs);
	
	//$agi->conlog("Group dialing routes: ".print_r($agg_route,true),4);
	$dialstr = $agg_route['str'] .
		str_alparams(getAGIconfig('dialcommand_group_param','|60|iL(%timeout)%param'),
			array ('dialstring' => $agg_route['str'],
				'trunkprefix' => $route['trunkprefix'], 
				'tech' => $route['providertech'],
				'providerip' => $route['providerip'], 'prefix' => $route['prefix'],
				'param' =>$route['trunkparm'], 'cardnum' => $card['username'], 
				'stimeout' => $route['tmout'], 'timeout' => (1000*$route['tmout'])));
	
	// Place the call!
	
	$agi->conlog("Setting clid to : ".$agg_route['clid'],3);
	$agi->set_variable('CALLERID(num)',$agg_route['clid']);
		
		// Construct a unique id with .. + trunkid.
	$uniqueid=$agi->request['agi_uniqueid'].'-'.$route['trunkid'];
			
	$res = $a2b->DBHandle()->Execute('INSERT INTO cc_call (cardid, attempt, cmode, '.
		'sessionid, uniqueid, nasipaddress, src, ' .
		'calledstation, destination, '.
		'srid, brid, tgid, trunk) '.
		'VALUES( ?,1,?,?,?,?,?,?,?,?,?,?,?) RETURNING id;',
		array($card['id'], 'standard',
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
			return false;
		}elseif($res->EOF){
			$agi->verbose('Cannot mark call start in db: EOF!');
			$last_prob='call-insert';
			return null;
		}
		$call_id = $res->fetchRow();
		$agi->conlog('Start call '. $call_id['id'],4);
		
		$agi->conlog("Dial group@". $route['trunkcode'] . " : $dialstr",3);
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
		
			// We are special, we need to learn who answered here!
		$dialedpeer = $agi->get_variable("DIALEDPEERNUMBER");
		
		$agi->conlog("Dial result: ".$dialstatus['data'].'@'.$dialedpeer['data'].
			'('. $hangupcause['data']. ') after '. $answeredtime['data'].'sec.',2);
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
			// Now, try to see who answered
			if (!empty($dialedpeer['data'])){
				$agi->conlog("Searching who answered..",4);
				foreach($all_routes as $aroute){
					$pos=strpos($aroute['str'],'/');
					if ($pos===false)
						$str2=$aroute['str'];
					else
						$str2=substr($aroute['str'],$pos+1);
						
					if ($dialedpeer['data']==$str2){
						$agi->conlog("Found: ".$aroute['str'],4);
						$route=$aroute['r'];
						break;
					}
				}
			}
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
			'cause_ext =?, startdelay =?, '.
			'destination =?, srid =?, brid = ?, trunk =? '.
				/* stopdelay */
			'WHERE id = ? ;',
			array( $answeredtime['data'],$dialstatus['data'],$hangupcause['data'],
				$cause_ext,
				($dialedtime['data'] - $answeredtime['data']),
				$route['destination'],$route['srid'],$route['brid'],$route['trunkid'],
				$call_id['id']));
		
		if ($notice = $a2b->DBHandle()->NoticeMsg())
			$agi->verbose('DB:' . $notice,2);

		if (!$res){
			$agi->verbose('Cannot mark call end in db! (will NOT bill)',0);
			$agi->conlog($a2b->DBHandle()->ErrorMsg(),2);
		}

	return (!$can_continue);
}

?>