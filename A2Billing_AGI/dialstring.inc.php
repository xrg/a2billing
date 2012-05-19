<?php
/** Dial string functions for a2billing AGI
    Copyright(C) P. Christeas, Areski, 2007-8
*/

function formatDialstring($dialn,&$route, &$card,$do_param = true){
	global $agi;

	switch ($route['trunkfmt']){
	case 1:
		$str = $route['providertech'].'/'.$route['providerip'].'/%dialnum';
		break;
	case 2:
		$str = $route['providertech'].'/%dialnum@'.$route['providerip'];
		break;
	case 3:
		$str = $route['providertech'].'/'.$route['providerip'];
		break;
	case 4:		// Local peer
	case 5:
	case 6:
	case 7:
	case 8:
	case 15:
		return formatDialstring_peer($dialn,$route,$card, $do_param);
		break;
	case 9:
	case 10:
	case 11:
	case 12:
	case 13:
	case 14:
		return true;
	default:
		$agi->verbose("Unknown trunk format: ".$route['trunkfmt'],2);
		return null;
	}
	
	if($route['stripdigits']>0)
		$dialnum=substr($route['dialstring'],$route['stripdigits']);
	else
		$dialnum=$route['dialstring'];
	if (isset($route['trunkprefix']) && strlen($route['trunkprefix']))
		$dialnum = $route['trunkprefix'] . $dialnum;

	if ($do_param){
		if ($agi->astmajor == "1.6")
			$str .= getAGIconfig('dialcommand_param',',60,iL(%timeout)%param');
		else
			$str .= getAGIconfig('dialcommand_param','|60|iL(%timeout)%param');
	}
	
	$str=str_alparams($str,array ('dialnum' => $dialnum, 'dialnumber' => $dialn, 'dialstring' => $route['dialstring'],
		'destination' => $route['destination'], 'trunkprefix' => $route['trunkprefix'], 'tech' => $route['providertech'],
		'providerip' => $route['providerip'], 'prefix' => $route['prefix'],'param' =>$route['trunkparm'],
		'cardnum' => $card['username'], 'stimeout' => $route['tmout'], 'timeout' => (1000*$route['tmout'])));
		
	return $str;
}

function formatDialstring_peer($dialn,&$route, &$card,$do_param = true){
	global $a2b;
	global $agi;
	$dbhandle = $a2b->DBHandle();
	
	if($route['stripdigits']>0)
		$dialnum=substr($route['dialstring'],$route['stripdigits']);
	else
		$dialnum=$route['dialstring'];
	
	$bind_str ='%dialtech/%dialname';
	switch($route['trunkfmt']){
	case 4:
		$qry = str_dbparams($dbhandle,'SELECT dialtech, dialname FROM cc_dialpeer_local_v '
			.'WHERE useralias = %1',array($dialnum));
		$bind_str ='%dialtech/%dialname';
		if (strlen($route['providertech']))
			$qry .= str_dbparams($dbhandle,' AND dialtech = %1',array($route['providertech']));
			
		// If the trunk specifies an "ip", aliases among the corresponding numplan will be queried
		// else, the numplan *must* be the same with that of the card.
		// It would be wrong not to specify a numplan, since aliases accross them are not unique!
		if (strlen($route['providerip']))
			$qry .= str_dbparams($dbhandle,' AND numplan_name = %1',array($route['providerip']));
		else
			$qry .= str_dbparams($dbhandle,' AND numplan = %#1', array($card['numplan']));
		break;
	case 6:
			// hardcode search into same numplan!
		$qry = str_dbparams($dbhandle,'SELECT * FROM cc_dialpeer_remote_v '
			.'WHERE useralias = %1 AND numplan = %#2',array($dialnum,$card['numplan']));
		
		$bind_str = $route['providertech'] .'/' . $route['providerip'];
		break;
	case 7:
	case 15:
		$dnum = explode('-',$dialnum);
		if ($dnum[0] == 'L')
			$dnum[0]=$card['numplan'];
		$qry = str_dbparams($dbhandle,'SELECT dialtech, dialname FROM cc_dialpeer_local_v '
			.'WHERE useralias = %2 AND numplan = %#1 ',$dnum);
		if (strlen($route['providertech']))
			$qry .= str_dbparams($dbhandle,' AND dialtech = %1',array($route['providertech']));
		$bind_str ='%dialtech/%dialname';
		
		$agi->conlog("Query: $qry",3);
		break;
	case 8:
		$dnum = explode('-',$dialnum);
		if ($dnum[0] == 'L')
			$dnum[0]=$card['numplan'];
		$qry = str_dbparams($dbhandle,'SELECT * FROM cc_dialpeer_remote_v '
			.'WHERE useralias = %2 AND numplan = %#1',$dnum);
		
		$agi->conlog("Query: $qry",3);
		$bind_str = $route['providertech'] .'/' . $route['providerip'];
		break;
	
	}
	
	$qry .= ';';
	//$agi->conlog("Find peer from ". $qry,4);

	if (!$bind_str)
		return false;
	$res = $dbhandle->Execute($qry);
	if (!$res){
		$agi->verbose('Cannot dial peer: '. $dbhandle->ErrorMsg());
		if(getAGIconfig('say_errors',true))
			$agi-> stream_file('allison2'/*-*/, '#');
		return false;
	}
	if ($res->EOF){
		$agi->verbose("Peer dial: cannot find peer ".$dialnum,2);
		//$agi-> stream_file("prepaid-dest-unreachable",'#');
		return null;
	}
	// Feature! If more than one registrations exist, call all of them in
	// parallel!
	$peer_rows = array();
	while( $row= $res->fetchRow())
		$peer_rows[] =str_alparams($bind_str,$row);
	
	$str='';
	if ($do_param){
		if ($agi->astmajor == "1.6")
			$str .= getAGIconfig('dialcommand_param',',60,iL(%timeout)%param');
		else
			$str .= getAGIconfig('dialcommand_param','|60|iL(%timeout)%param');
		$str = str_alparams($str,array ('dialnum' => $dialnum, 'dialnumber' => $dialn, 'dialstring' => $route['dialstring'],
		'destination' => $route['destination'], 'trunkprefix' => $route['trunkprefix'], 'tech' => $route['providertech'],
		'providerip' => $route['providerip'], 'prefix' => $route['prefix'], 'param' => $route['trunkparm'],
		'cardnum' => $card['username'], 'stimeout' => $route['tmout'], 'timeout' => (1000*$route['tmout'])));

	}
	
	return implode('&',$peer_rows).$str;
}

?>