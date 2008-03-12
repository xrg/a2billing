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
	
	$vtimeout = getAGIconfig('voucher-timeoute', 8000);
	$vmaxlen = getAGIconfig('voucher-maxlen', 15);
	$vminlen = getAGIconfig('voucher-minlen', 5);
	$vprompt = getAGIconfig('voucher-prompt', 'prepaid-voucher_enter_number');
	$vprompt_nexist = getAGIconfig('voucher-prompt-nexist', 'prepaid-voucher_does_not_exist');
	$vprompt_refill = getAGIconfig('voucher-prompt-refill', 'prepaid-account_refill');
	$vprompt_no_entered = getAGIconfig('voucher-prompt-no-entered', 'prepaid-no-voucher-entered');
	$vprompt_invalid = getAGIconfig('voucher-prompt-invalid', 'prepaid-invalid-voucher');
	
	$agi->conlog('Voucher-ivr: asking for Voucher',4);
	$res_dtmf = $agi->get_data($vprompt, $vtimeout, $vmaxlen);
	
	$agi->conlog('Voucher-ivr: result ' . print_r($res_dtmf,true),3);
	if (!isset($res_dtmf['result'])){
		$agi->conlog('No Voucher entered',2);
		$agi-> stream_file($vprompt_no_entered, '#');
		return null;
	}
	$vouchernum = $res_dtmf['result'];
	if ((strlen($vouchernum) < $vminlen) || (strlen($vouchernum) > $vmaxlen)) {
		$agi->conlog('Invalid Voucher',2);
		$agi-> stream_file($vprompt_invalid, '#');
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
	$agi-> stream_file($vprompt_refill, '#');
	
	// TODO : play the Amount of credit added
	
	return true;
}

?>