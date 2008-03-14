<?php
/** Code for voucher-refill mode calls in a2billing AGI
    Copyright(C) P. Christeas, Areski, 2007-8
*/


if (getAGIconfig('early_answer',false))
	$agi->answer();
else
	$agi->exec('Progress');
	
$agi->conlog('Voucher mode',4);
$card=null;
	
	// Repeat until we hangup
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
	
	if (getVoucher ($card) !==null){
		// perhaps stream the credit?
		break;
	}
	// or continue, if voucher didn't succeed.
}

if ($card && !empty($card['locked']))
	ReleaseCard($card);

$agi->conlog('Goodbye!',3);

if(getAGIconfig('say_goodbye',true) && $agi->is_alive)
	$agi-> stream_file('prepaid-final', '#');
$agi->hangup();

?>