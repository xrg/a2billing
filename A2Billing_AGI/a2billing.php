#!/usr/bin/php -q
<?php   
/***************************************************************************
 *
 * a2billing.php : PHP A2Billing Core
 * Written for PHP 5.X versions.
 *
 * A2Billing -- Asterisk billing solution.
 * Copyright (C) 2007, P. Christeas <p_christeas A yahoo.com>
 * Copyright (C) 2004, 2007 Belaid Arezqui <areski _atl_ gmail com>
 *
 * See http://www.asterisk2billing.org for more information about
 * the A2Billing project. 
 *
 * This software is released under the terms of the GNU Lesser General Public License v2.1
 * A copy of which is available from http://www.gnu.org/copyleft/lesser.html
 *
 ****************************************************************************/

/* Dev's note: use "conlog()" for debug-only messages, "verbose()" for things that
  will always output. */

declare(ticks = 1);

function sig_handler($signo)
{

     switch ($signo) {
         case SIGTERM:
             // handle shutdown tasks
             throw new Exception("Term signal!",SIGTERM);
             break;
         case SIGHUP:
             // Better ignore it..
             //throw new Exception("Hangup signal!",SIGHUP);
             break;
         case SIGINT:
             throw new Exception("Interrupt signal!",SIGINT);
             break;
         case SIGUSR1:
             echo "Caught SIGUSR1...\n";
             break;
         default:
             echo "Caught sighal $signo ..\n";
             // handle all other signals
     }

}

// Required!
pcntl_signal(SIGHUP, 'sig_handler');
pcntl_signal(SIGTERM, 'sig_handler');
pcntl_signal(SIGINT, 'sig_handler');

error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));

define(AGI_LIBDIR, dirname(__FILE__)."/libs_a2billing/");

require_once(AGI_LIBDIR.'Class.Config.inc.php');
require_once(AGI_LIBDIR."Class.A2Billing.inc.php");
require_once(AGI_LIBDIR."Misc.inc.php");
require_once(AGI_LIBDIR.'Class.DynConf.inc.php');
require_once(AGI_LIBDIR."/phpagi/phpagi.php");
// include (dirname(__FILE__)."/libs_a2billing/phpagi_2_14/phpagi-asmanager.php");
// include (dirname(__FILE__)."/libs_a2billing/Misc.php");

$charge_callback=0;
$G_startime = time();
$agi_date = "Release : you'd wish";
$agi_version = "Asterisk2Billing - Version v200/xrg - Alpha";
$conf_file = NULL;

if ($argc > 1 && ($argv[1] == '--version' || $argv[1] == '-V'))
{
	echo "A2Billing - Version $agi_version - $agi_date\n";
	exit;
}
$verbose_mode = false;

if ($argc > 1 && ($argv[1] == '--verbose' || $argv[1] == '-v')){
	AGI::verbose_s("Verbose mode!",0);
	error_reporting(E_ALL);
	$verbose_mode = true;
	array_shift($argv);
	$argc--;
}

if ($argc > 1 && ($argv[1] == '--test')){
	AGI::verbose_s("Testing mode!",0);
	define('DEFAULT_A2BILLING_CONFIG', "../a2billing.conf");
	array_shift($argv);
	$argc--;
}

// create the objects
$a2b = A2Billing::instance();
if(!$a2b->load_res_dbsettings('/etc/asterisk/res_pgsql.conf')){
	@syslog(LOG_ERR,"Cannot fetch settings from res_pgsql.conf");
	exit(2);
}
$dynconf = DynConf::instance();

if ($argc > 1 && is_numeric($argv[1]) && $argv[1] >= 0){
	$idconfig = $argv[1];
}else{
	$idconfig = 1;
}

try {
	$dynconf->init();
	$dynconf->PrefetchGroup('agiconf'.$idconfig);
} catch (Exception $ex){
	error_log($ex->getMessage());
	@syslog(LOG_ERR,"Cannot Fetch config!");
	@syslog(LOG_ERR,$ex->getMessage());
	exit();
}

if ($verbose_mode)
	$dynconf->SetDefVar('agiconf'.$idconfig,'debug',true);

$agi = new AGI($dynconf,'agiconf'.$idconfig);

if (!$agi->is_alive)
	exit();

$mode = 'standard';
// get the running mode -> DeadAGI(a2billing.php|1|voucher)
if ($argc > 2 && strlen($argv[2]) > 0 )
	switch ($argv[2]) {
	case 'did':
	case 'callback':
	case 'cid-callback':
	case 'all-callback':
	case 'predictivedialer':
	case 'voucher':
		$mode = $argv[2];
		break;
	default:
		$mode = 'standard';
	}

// get the area code for the cid-callback & all-callback
if ($argc > 3 && strlen($argv[3]) > 0) $caller_areacode = $argv[3];

function getAGIconfig($var,$default){
	global $dynconf;
	global $idconfig;
	return $dynconf->GetCfgVar('agiconf'.$idconfig,$var,$default);
}

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

/** Match and return string from prefix.
   \param $match_empty  Succeed if $prefix is empty or fail..
   \param $prefix the string $str must start from
*/
function str_match($str, $prefix, $match_empty =false) {
	$len = strlen($prefix);
	if ($len<1){
		if ($match_empty)
			return $str;
		else
			return false;
	}
	
	if (strncmp($str,$prefix,$len)==0)
		return substr($str,$len);
	else
		return false;
}

function getDialNumber(&$card, $num_try){
	global $agi;
	
	if (getAGIconfig('use_dnid',true) && ($num_try==1)){
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
			
			$agi->conlog('GetDestination: result ' . print_r($res_dtmf,true),3);
			if (!isset($res_dtmf['result'])){
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


/**
 *	Function refill_card_with_voucher
 **/
function getVoucher ($card){
	global $a2b;
	global $agi;
	
	$dbhandle =$a2b->DBHandle();
	
	$agi->conlog('Voucher refill with card',4);
	
	$voucher_prompt_enter = getAGIconfig('voucher-prompt-enter', 'prepaid-voucher_enter_number');
	$voucher_timeout = getAGIconfig('voucher-timeoute', 8000);
	$voucher_maxlen = getAGIconfig('voucher-maxlen', 15);
	$voucher_minlen = getAGIconfig('voucher-minlen', 5);
	$voucher_prompt = getAGIconfig('voucher-prompt', 'prepaid-voucher_enter_number');
	$voucher_prompt_doesnt_exist = getAGIconfig('voucher-prompt-doesnt-exist', 'prepaid-voucher_does_not_exist');
	$voucher_prompt_account_refill = getAGIconfig('voucher-prompt-account-refill', 'prepaid-account_refill');
	$voucher_prompt_no_voucher_entered = getAGIconfig('voucher-prompt-no-voucher-entered', 'prepaid-no-voucher-entered');
	$voucher_prompt_invalid_voucher = getAGIconfig('voucher-prompt-invalid-voucher', 'prepaid-invalid-voucher');
	
	$agi->conlog('Voucher-ivr: asking for Voucher',4);
	$res_dtmf = $agi->get_data($voucher_prompt, $voucher_timeout, $voucher_maxlen);
	
	$agi->conlog('Voucher-ivr: result ' . print_r($res_dtmf,true),3);
	if (!isset($res_dtmf['result'])){
		$agi->conlog('No Voucher entered',2);
		$agi-> stream_file($voucher_prompt_no_voucher_entered, '#');
		return null;
	}
	$vouchernum = $res_dtmf['result'];
	if ((strlen($vouchernum) < $voucher_minlen) || (strlen($vouchernum) > $voucher_maxlen)) {
		$agi->conlog('Invalid Voucher',2);
		$agi-> stream_file($voucher_prompt_invalid_voucher, '#');
		return null;
	}
	
	// CALL STORED PROCEDURE FOR VOUCHER
	$QRY = str_dbparams($a2b->DBHandle(),'SELECT * FROM  card_use_voucher (%1, %2);',
		array($card['id'],$vouchernum));
		
	$agi->conlog($QRY,3);
	$res = $a2b->DBHandle()->Execute($QRY);
	// If the rate engine has anything to Notice/Warn, display that..
	if ($notice = $a2b->DBHandle()->NoticeMsg())
		$agi->verbose('DB:' . $notice,2);
	
	if (!$res){
		$emsg = $dbhandle->ErrorMsg();
		if (substr($emsg,0,23) =='ERROR:  card_use_voucher'){
			$msga= explode('|',$emsg);
			$agi->verbose('Could not use voucher: '. $msga[3]);
			//$agi->conlog("Message: " . print_r($msga,true),4);
			switch ($msga[1]){
			case 'voucher-no-find':
				//$agi->stream_file('prepaid-card-in-use','#');
				break;
			case 'voucher-zero':
			case 'conv_currency-failed':
			case 'conv_currency-failed-zero':
				//TODO
				break;
			default:
				$agi->conlog('Unknown result from card_use_voucher: ' . $msga[1],3);
			}
		}else $agi->verbose('Could not use voucher : '. $emsg );
		
		$agi-> stream_file($voucher_prompt_invalid_voucher, '#');
		return null;
	}
	if ($res->EOF){
		$agi->verbose('No used voucher in card_use_voucher(), why?');
		return null;
	}
	
	$agi->conlog('Unknown result from card_use_voucher: ' . $msga[1],3);
	
	$row= $res->fetchRow();
	if (empty($row['card_use_voucher'])){
		$agi->verbose('Fail to fetch on voucher ! ');
		return false;	
	}
	
	$agi->conlog('Voucher used. Amount of credit added : ' . $row['card_use_voucher'], 3);
	$agi-> stream_file($voucher_prompt_account_refill, '#');
	
	// TODO : play the Amount of credit added
	
	return true;
}

function formatDialstring($dialn,&$route, &$card){
	global $agi;
	$do_param = true;
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
		return formatDialstring_peer($dialn,$route,$card);
		break;
	case 10:
	case 11:
	case 12:
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

	if ($do_param)
		$str .= getAGIconfig('dialcommand_param','|60|iL(%timeout)');
	$str .= $route['trunkparm'];
	
	return str_alparams($str,array ('dialnum' => $dialnum, 'dialnumber' => $dialn, 'dialstring' => $route['dialstring'],
		'destination' => $route['destination'], 'trunkprefix' => $route['trunkprefix'], 'tech' => $route['providertech'],
		'providerip' => $route['providerip'], 'prefix' => $route['prefix'],
		'cardnum' => $card['username'], 'stimeout' => $route['tmout'], 'timeout' => (1000*$route['tmout'])));
}

function formatDialstring_peer($dialn,&$route, &$card){
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
		$dnum = explode('-',$dialnum);
		if ($dnum[0] == 'L')
			$dnum[0]=$card['numplan'];
		$qry = str_dbparams($dbhandle,'SELECT dialtech, dialname FROM cc_dialpeer_local_v '
			.'WHERE useralias = %2 AND numplan = %#1 ',$dnum);
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
	return implode('&',$peer_rows);
}

/** Special dial modes, like VoiceMail etc */
function dialSpecial($dialnum,$route, $card,$last_prob,$agi){
	global $a2b;
	$dbhandle =$a2b->DBHandle();
	
	if($route['stripdigits']>0)
		$dialnum=substr($route['dialstring'],$route['stripdigits']);
	else
		$dialnum=$route['dialstring'];

	$dialn = null;
	switch ($route['trunkfmt']){
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


if (($mode == 'standard') || ($mode == 'voucher')){

	if (getAGIconfig('early_answer',false))
		$agi->answer();
	else
		$agi->exec('Progress');
		
	$agi->conlog('Standard mode',4);
		// Repeat until we hangup
	$card=null;
	
	for($num_try = 0;$num_try<getAGIconfig('number_try',1);$num_try++){
		if (!$card)
			$card = getCard();
		else if (!empty($card['locked']))
			ReleaseCard($card);
		
		if ($card === false)
			break;

		if ($card === null)
			continue;
		
		$agi->conlog('Card: ' . print_r($card,true),4);
		
		//TODO: fix lang
		if ($card['status']!=1){
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
		
		if ($mode == 'voucher') {
			getVoucher ($card);
			break;
		}
		
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
			$agi->stream_file('prepaid-no-enough-credit','#');
			ReleaseCard($card);
			$card=null;
			continue;
		}
		
		$dialnum = getDialNumber($card, $num_try);
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
		foreach ($routes as $route){
			if ($route['tmout'] < getAGIconfig('min_duration_2call',30)){
				$agi->conlog('Call will be too short: ',$route['tmout'],3);
				$last_prob = 'min-length';
				continue;
			}
			
			$dialstr = formatDialstring($dialnum,$route, $card);
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
			if ($route['clidreplace']!== NULL){
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
				
			$res = $a2b->DBHandle()->Execute('INSERT INTO cc_call (cardid, attempt, cmode, '.
				'sessionid, uniqueid, nasipaddress, src, ' .
				'calledstation, destination, '.
				'srid, brid, tgid, trunk) '.
				'VALUES( ?,?,?,?,?,?,?,?,?,?,?,?,?) RETURNING id;',
				array($card['id'],$attempt, 'standard',
					$agi->request['agi_channel'],$agi->request['agi_uniqueid'],NULL,$card['username'],
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
	exit(0);


} // running mode

//exit();
?>
