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
		$agi->verbose("Unknown mode: ". $argv[2] );
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
	case 'callshop':
		return getCard_booth();
		break;
	case 'pin-and-clid':
		// *-*
		break;
	default:
		$agi->verbose('Unknown auth-mode: '. getAGIconfig('auth-mode','callingcard'));
		return null;
	}
}

function getCard_clid(){
	global $a2b;
	global $agi;
	$dbhandle =$a2b->DBHandle();
	
	$res = $dbhandle->Execute('SELECT card.id, card_group.tariffgroup AS tgid, card.username, card.status ' .
		'FROM cc_card_dv AS card, cc_card_group, cc_callerid '.
		'WHERE card.grp = cc_card_group.id AND cc_callerid.cardid = card.id ' .
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
	
	$pin_prompt = getAGIconfig('pin-prompt',"prepaid-enter-pin-number");
	$pin_timeout = getAGIconfig('pin-timeoute',6000);
	$pin_maxlen = getAGIconfig('pin-maxlen',15);
	
	$agi->conlog('Auth-ivr: asking for PIN',4);
	$res_dtmf = $agi->get_data($pin_prompt, $pin_timeout,$pin_maxlen);
	
	$agi->conlog('Auth-ivr: result' . print_r($res_dtmf,true),3);
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
	
	$res = $dbhandle->Execute('SELECT card.id, card_group.tariffgroup AS tgid, card.username, card.status ' .
		'FROM cc_card_dv AS card, cc_card_group'.
		'WHERE card.grp = cc_card_group.id ' .
		'AND card.username = ? LIMIT 1 ;',
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

function getCard_booth(){
	global $a2b;
	global $agi;
}

// Lock the card and return remaining money

function CardGetMoney(&$card){
	global $a2b;
	global $agi;
	$dbhandle =$a2b->DBHandle();
	
	$res = $dbhandle->Execute ('SELECT card_call_lock(?,?);',array($card['id'],BASE_CURRENCY));
	if (!$res){
		/*-* Parse message and play sound to user */
		$agi->verbose('Could not lock card: '. $dbhandle->ErrorMsg());
		return null;
	}
	if ($res->EOF){
		$agi->verbose('No card from card_call_lock(), why?');
		return null;
	}
	return $res->fetchRow();
}

//Unlock the card (decrease usage count)
function ReleaseCard(&$card){
	global $a2b;
	global $agi;
	$res = $a2b->DBHandle()->Execute ('SELECT card_call_release(?);',array($card['id']));
	if (!$res)
		$agi->verbose('Could not release card: '. $a2b->DBHandle()->ErrorMsg(),2);
}

function getDialNumber(){
	global $agi;
	// TODO, perform prefix manipulations etc.
	if ($agi->request['agi_extension']=='s')
		return $agi->request['agi_dnid'];
	else
		return $agi->request['agi_extension'];
}

function formatDialstring($dialnumber,$route, $card){
	global $agi;
	$do_param = true;
	switch ($route['trunkfmt']){
	case 1:
		$str = $route['providertech'].'/'.$route['providerip'].'/%dialnumber';
		break;
	case 2:
		$str = $route['providertech'].'/%dialnumber@'.$route['providerip'];
		break;
	case 3:
		$str = $route['providertech'].'/'.$route['providerip'];
		break;
	default:
		$agi->verbose("Unknown trunk format: ".$route['trunkfmt'],2);
		return null;
	}

	if ($do_param)
		$str .= getAGIconfig('dialcommand_param','|60|l(%timeout)');
	$str .= $route['trunkparm'];
		
	return str_alparams($str,array ('dialnumber' => $dialnumber, 'dialstring' => $route['dialstring'],
		'destination' => $route['destination'], 'trunkprefix' => $route['trunkprefix'], 'tech' => $route['providertech'],
		'providerip' => $route['providerip'], 'prefix' => $route['prefix'],
		'cardnum' => $card['username'], 'stimeout' => $route['tmout'], 'timeout' => (1000*$route['tmout'])));
}

if ($mode == 'standard'){

	if (getAGIconfig('early_answer',false))
		$agi->answer();
	else
		$agi->exec('Progress');
		
	$agi->conlog('Standard mode',4);
		// Repeat until we hangup
	for($num_try = 0;$num_try<getAGIconfig('number_try',1);$num_try++){
		$card = getCard();
		
		if ($card === false)
			break;

		if ($card === null)
			continue;
		
		//TODO: fix lang
		if ($card->status!=1){
			// *-* TODO!
		}

		// Here, we're authorized..
		//TODO: set callerid
		
		$card_money = CardGetMoney($card);
		//TODO: play balance, intros
		
		if (!$card_money || ($card_money['base'] < getAGIconfig('min_credit_2call',0.01))) {
			// not enough money!
			$agi->verbose('Not enough money!',2);
			ReleaseCard($card);
			continue;
		}
		
		$dialnumber = getDialNumber();
		
		$QRY = str_dbparams($a2b->DBHandle(),'SELECT * FROM RateEngine2(%#1, %2, now(), %3);',
			array($card['tgid'],$dialnumber,$card_money['base']));
			
		$agi->conlog($QRY,3);
		$res = $a2b->DBHandle()->Execute($QRY);
		if (!$res){
			$agi->verbose('Rate engine: query error!',2);
			$agi->conlog($a2b->DBHandle()->ErrorMsg(),2);
			if(getAGIconfig('say_errors',true))
				$agi-> stream_file('allison2'/*-*/, '#');
			ReleaseCard($card);
			break;
		}elseif($res->EOF){
			$agi->verbose('Rate engine: no result.',2);
			$agi-> stream_file('prepaid-dest-unreachable'/*-*/, '#');
			ReleaseCard($card);
			// if manual dialnum, continue.. TODO
			break;
		}
		$routes = $res->GetArray();
		$agi->conlog('Rate engine: found '.count($routes).' results.',3);
		
		// TODO: musiconhold
		//TODO: record_call
		//TODO: outbound cid
		
		try {
		foreach ($routes as $route){
			
			if ((strlen($route['removeprefix'])) &&(strncmp($dialnumber, $route['removeprefix'], strlen($route['removeprefix'])) == 0))
				$dialnumber= substr($dialnumber, strlen($route['removeprefix']));
			
			$dialstr = formatDialstring($dialnumber,$route, $card);
			if (!$dialstr)
				continue;
			$agi->conlog("Dial '". $route['destination']. "'@". $route['trunkcode'] . " : $dialstr",3);
			
			$call_res= $agi->exec('Dial',$dialstr);
			//TODO: if record, stop
			
			$answeredtime = $agi->get_variable("ANSWEREDTIME");
			if ($answeredtime['result']== 0)
				$answeredtime['data'] =0;
			$dialstatus = $agi->get_variable("DIALSTATUS");
			
			$agi->conlog("Dial result: ".$dialstatus['data'].' after '. $answeredtime['data'].'sec.',3);
			//$agi->conlog("After dial, answertime: ".print_r($answeredtime,true));
			//TODO: SIP, ISDN extended status
			
			$can_continue = false;
			switch ($dialstatus['data']){
			case 'BUSY':
			case 'ANSWERED':
			case 'ANSWER':
			case 'CANCEL':
				break;
			
			case 'CONGESTION':
			case 'CHANUNAVAIL':
				$can_continue = true;
				break;
			case 'NOANSWER':
				break;
			default:
				$agi->verbose("Unknown status: ".$dialstatus['data'],2);
			}
			
			if (!$can_continue) //TODO: manual dialnum?
				break;

		}
		}catch (Exception $ex){
			// Here we handle signals received
			$agi->verbose("Exception at dial:". $ex->getMessage());
			@syslog("Exception at dial:". $ex->getMessage());
			ReleaseCard($card);
			break;
		}
		
		ReleaseCard($card);
	}
	
	$agi->conlog('Goodbye!',3);

	if(getAGIconfig('say_goodbye',true) && $agi->is_alive)
		$agi-> stream_file('prepaid-final', '#');
	$agi->hangup();
	exit();

	
}// mode standard

exit();
?>
