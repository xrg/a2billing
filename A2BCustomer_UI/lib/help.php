<?php

function show_help($str) {

	switch ($str) {
	case 'webphone':
		$icon='stock_landline-phone.png';
		$text=gettext("From here, you can use the web based screen phone. You need microphone and speakers on your PC.");
		break;
	case 'balance_customer':
		$icon='gnome-finance.png';
		$text=gettext("All calls are listed below. Search by month, day or status. Additionally, you can check the rate and price.");
		break;
	case 'simulator_rateengine':
		$icon='connect_to_network.png';
		$text=gettext("Simulate the calling process to discover the cost per minute of a call, and the number of minutes you can call that number with your current credit.");
		break;
	case 'sipiax_info':
		$icon='connect_to_network.png';
		$text=gettext("Configuration information for SIP and IAX Client. You can simply copy and paste it in your configuration files and can do neccessory modifications.");
		break;
	case 'password_change':
		$icon='connect_to_network.png';
		$text=gettext("On this page you will be able to change your password, You have to enter the New Password and Confirm it.");
		break;
	case 'ratecard':
		$icon='connect_to_network.png';
		$text=gettext("Here you can view your ratecards");
		break;
	case 'list_voucher':
		$icon='vcard.png';
		$text=gettext("Enter your voucher number to top up your card.");
		break;
	case 'list_did':
		$icon='vcard.png';
		$text=gettext("Select the country below where you would like a DID, select a DID from the list and enter the destination you would like to assign it to.");
		break;
	case 'card':
		$icon='personal.png';
		$text= gettext("Personal information. <br>" .
			"You can update your personal information here.");
		break;
	case 'payment_method':
		$icon='authorize.png';
		$text= 'authorize.png'; // *-*
		break;

	default:
		$icon = 'vcard.gif';
		$text = "No help for '" .$str ."' !";
	}
	
	echo '<a href="#" target="_self"  onclick="imgidclick(\'img1000\',\'div1000\',\'help.png\',\'viewmag.png\');"><img id="img1000" src="./Css/kicons/viewmag.png" onmouseover="this.style.cursor=\'hand\';" WIDTH="16" HEIGHT="16"></a>'. "\n";
	echo '<div id="div1000" style="display:visible;">'."\n";
	echo '<div id="kiki"><div class="w1">' ."\n";
	echo "<img src=\"./Css/kicons/$icon\" class=\"kikipic\"/>\n";
	echo "<div class=\"w2\">\n";
	echo "\t\t" .$text ."\n";
	echo '<br/><br/></div></div></div></div><br style=\'clear: both;\' />';
}

?>
