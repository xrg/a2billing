<?php
/** Voucher functions for a2billing AGI
    Copyright(C) Areski, 2007-8
*/

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

?>