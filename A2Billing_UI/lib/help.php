<?php

function show_help($str) {
	global $HD_Form;
	if (isset($HD_Form) && isset($HD_Form->FG_DEBUG) &&$HD_Form->FG_DEBUG > 1)
		return;
	
	switch ($str) {
	case 'list customer':
		$icon = "vcard.png";
		$text = gettext("Customers are listed below by card number. Each row corresponds to one customer, along with information such as their call plan, credit remaining, etc.</br>" .
				"The SIP and IAX buttons create SIP and IAX entries to allow direct VoIP connections to the Asterisk server without further authentication.");
		break;
	
	case 'refill customer':
		$icon = 'pipe.png';
		$text = gettext("Top up cards on this screen by selecting or typing in the card number directly, and enter the amount of credit to apply, then click ADD to confirm.");
		break;

	case 'create customer':
		$icon = 'vcard.png';
		$text = gettext("Create and edit the properties of each customer. Click <b>CONFIRM DATA</b> at the bottom of the page to save changes.");
		break;

	case 'generate_customer':
		$icon = 'vcard.png';
		$text =gettext("Bulk create cards and customers in a single step. <br> Set the properties of the batch such as initial credit, card type and currency, then click on the GENERATE CARDS button to create the batch.");
		break;

	case 'sipfriend_list':
		$icon = 'network_local.png';
		$text =gettext("SIP and IAX friend will create a SIP or IAX entry on the Asterisk server, so that a customer can set up a SIP or IAX client to connect directly to the asterisk server without the need to enter an account and pin each time a call is made. When done, click on the CONFIRM DATA button, then click reload to apply the changes on the Asterisk server.</br>" .
			"The customer must then enter the URL/IP address of the asterisk server into the SIP/IAX client, and use the Card Number and Secret as the username and password.");
		break;

	case 'sipfriend_reload':
		$icon = 'network_local.png';
		$text =gettext("Click reload to commit changes to Asterisk");
		break;

	case 'sipfriend_edit':
		$icon = 'network_local.png';
		$text =gettext("Each SIP/IAX client is identified by a number of paremeters.</br></br>" .
			"More details on how to configure clients are on the Wiki: <a href=\"http://voip-info.org/wiki-Asterisk+config+sip.conf\" target=\"_blank\">sip.conf</a> &amp; " .
			"<a href=\"http://voip-info.org/wiki-Asterisk+config+iax.conf\" target=\"_blank\">iax.conf</a> ");
		break;

	case 'callerid_list':
		$icon = 'vcard.png';
		$text =gettext("Set the caller ID so that the customer calling in is authenticated on the basis of the callerID rather than with account number");
		break;

	case 'agent_list':
		$icon = 'vcard.png';
		$text =;
		break;

	case 'money_situation':
		$icon = 'gnome-finance.png';
		$text =gettext("This screen shows refills and payments made against each account, along with the current credit on each card. The initial amount of credit applied to the card is not included. The amount owing is calculated by subtracting payments from refills");
		break;

	case 'view_payment':
		$icon = 'gnome-finance.png';
		$text = gettext("Payment history - The section below allows you to add payments against a customer. Note that this does not change the balance on the card. Click refill under customer list to top-up a card.");
		break;

	case 'view_paypal':
		$icon = 'paypal.png';
		$text =gettext("Paypal History - The section below shows all paypal receipts.").
			'<a href="https://www.paypal.com/es/mrb/pal=PGSJEXAEXKTBU">PayPal</a>';
		break;

	case 'add_payment':
		$icon = 'gnome-finance.png';
		$text =gettext("Add payments to a customer's account!");
		break;

	case 'list_tariffgroup':
		$icon = 'network.png';
		$text =gettext("List of Call Plans, a Call Plan is a collection of rate cards, click edit to add ratecards to the Call Plan");
		break;

	case 'add_tariffgroup':
		$icon = 'network.png';
		$text = gettext("A Call Plan is a collection of ratecards." .
			"The calling card system will choose the most appropriate rate according to the Call Plan settings (LCR or LCD).<br/>" .
			"LCR : Least Cost Routing - Find the trunk with the cheapest carrier cost. (buying rate)<br>".
			"LCD : Least Cost Dialing - Find the trunk with the cheapest retail rate (selling rate)");
		break;

	case 'list_ratecard':
		$icon = 'kspread_ksp.png';
		$text =gettext("List ratecards that have been created!<br>Ensure that the rate card is added into the call plan under 'List Ratecard'");
		break;

	case 'edit_ratecard':
		$icon = 'kspread_ksp.png';
		$text = gettext("Set the properties and attributes of the ratecard <br/>".
			"A ratecard is set of rates defined and applied according to the dialing prefix, for instance 441 & 442 : UK Landline. <br>" .
			"Each ratecard may have has many rates as you wish, however, if a dialing prefix cannot be matched when a call is made, then the call will be terminated. <br>".
			"A ratecard has a \"start date\", an \"expiry date\" and a you can define a default trunk, but if no trunk is defined, the ratecard default trunk will be used.");
		break;

	case 'def_ratecard':
		$icon = 'kspread_ksp.png';
		$text =gettext("Please select a ratecard and click on search to browse the different rates/dialing prefix of the selected ratecard.");
		break;

	case 'sim_ratecard':
		$icon = 'kspread_ksp.png';
		$text =gettext('Please select a calling card, enter the number you wish to call and press the "SIMULATE" button.');
		break;

	case 'add_rate':
		$icon = 'kspread_ksp.png';
		$text =gettext("Please fill in the fields below to set up the rate for each destination.");
		break;

	case 'import_ratecard':
		$icon = 'spreadsheet.png';
		$text = gettext("This section is a utility to import ratecards from a CSV file.<br>" .
			"Define the ratecard name, the trunk to use and the fields that you wish to include from your csv files. Finally, select the csv files and click on the \"Import Ratecard\" button.");
		break;

	case 'import_ratecard_analyse':
		$icon = 'spreadsheet.png';
		$text = gettext('This is the second step of the import ratecard! ' .
			'The first line of your csv files has been read and the values are displayed below according to the fields' .
			'you decided to import on the ratecard! You can check the values and if there are correct,'.
			'please select the same file and click on "Continue to Import the Ratecard" button...');
		break;

	case 'import_ratecard_confirm':
		$icon = 'spreadsheet.png';
		$text = gettext('Ratecard comfirmation page. ' .
			'Import results, how many new rates have been imported, and the line numbers of the CSV files that generated errors.');
		break;
	
	case 'trunk_list':
		$icon = 'hwbrowser.png';
		$text =gettext("Trunk List<br/>Trunks can be modified by clicking the edit button");
		break;
	
	case 'list_log':
		$icon = 'kdmconfig.png';
		$text = gettext("System log help you to keep track and event all event happening in your application. Log Level are the Importance Levels for the Events which are logged. '1' is lowest level and '3' is highest level. 1 if for Login, Logout, Page Visit, 2 if for Add, Import, Export. and 3 is for update and Delete.");
		break;

	case 'trunk_edit':
		$icon = 'hwbrowser.png';
		$text = gettext("Trunks are used to terminate the call!<br>" .
			"The trunk and ratecard is selected by the rating engine on the basis of the dialed digits.<br>" .
			"The trunk is used to dial out from your asterisk box which can be a zaptel interface or a voip provider.");
		break;
	
	case 'admin_list':
		$icon = 'kdmconfig.png';
		$text =gettext("Administrator list who have access to the calling card administrative interface.");
		break;
	
	case 'admin_edit':
		$icon = 'kdmconfig.png';
		$text =gettext("Edit administrator.");
		break;

	case 'list_voucher':
		$icon = 'vcard.png';
		$text = gettext("Listed below are the vouchers created on the system,.<br/>" .
			"Each row corresponds to a voucher and shows it's status, value and currency..");
		break;
	
	case 'create_voucher':
		$icon = 'vcard.png';
		$text = gettext("Create a single voucher, defining such properties as credit, tag, currency etc, click confirm when finished. <br/> The customer applies voucher credit to their card via the customer interface or via an IVR menu.");
		break;
		
	case 'generate_voucher':
		$icon = 'vcard.png';
		$text =gettext("Bulk generate a batch of vouchers, defining such properties as credit and currency etc, click Generate Vouchers when finished.<br/>The customer applies voucher credit to their card via the customer interface. ");
		break;

	case 'list_service':
		$icon = 'system-config-date.png';
		$text =gettext("Re-occuring services that decrement a card at timed intervals.");
		break;
	
	case 'edit_service':
		$icon = 'system-config-date.png';
		$text =gettext("Utility to apply a scheduled action on the card.<br>" .
			"For example if you want to remove 10 cents everyday on each single card, it can be defined here, alternatively, if you now want to remove 1 credit every week but only 7 times on each card, the different rules/parameters below will define this." );
		break;
	
	case 'list_cidgroup':
		$icon = 'connect_to_network.png';
		$text =gettext("CID Group list. CID can be chosen by customers through the customer interface.");
		break;

	case 'list_cid':
		$icon = 'connect_to_network.png';
		$text =gettext("Outbound CID list. CID can be added by customers through the customer interface.");
		break;

	case 'edit_cidgroup':
		$icon = 'connect_to_network.png';
		$text =gettext("CID group offers customers a group of CID numbers which can be selected for a ratecard for outgoing calls");
		break;
	
	case 'edit_cid':
		$icon = 'connect_to_network.png';
		$text =gettext("Outbound CID offers customers a number which will be selected randomly for a ratecard for outgoing calls");
		break;
	
	case 'currency':
		$icon = 'favorites.png';
		$text =gettext("Currency data are automaticaly updated from Yahoo Financial." .
			"<br>For more information please visite the website http://finance.yahoo.com.".
			"<br>The list below is based over your currency base :").
			' <b>'.BASE_CURRENCY.'</b>';
		break;

	case 'list_didgroup':
		$icon = 'connect_to_network.png';
		$text = gettext("DID (or DDI) Group list. DID can be chosen by customers through the customer interface.");
		break;
	
	case 'edit_didgroup':
		$icon = 'connect_to_network.png';
		$text =gettext("DID group offers customers a group of DID numbers which can be selected by the customer");
		break;

	case 'list_did':
		$icon = 'connect_to_network.png';
		$text =gettext("DID number list with destinations.");
		break;

	case 'edit_did':
		$icon = 'connect_to_network.png';
		$text =gettext("DID can be assigned to a card to re-route calls to a SIP/IAX client or a PSTN number. The Priority sets the order in which the calls are to be routed to allow for failover or follow-me.");
		break;
	
	case 'list_did_use':
		$icon = 'connect_to_network.png';
		$text =gettext("List the DIDs currently in use with the card id and their destination number <br/> You can use the search option to show the usage of a given DID or all DIDs");
		break;
	
	case 'release_did':
		$icon = 'connect_to_network.png';
		$text =gettext("Releasing DID put it in free stat and the user will not be monthly charged any more..");
		break;

	case 'edit_charge':
		$icon = 'wi0124-48.png';
		$text =gettext("Extra Charges are to allow the billing of one-off or re-occurring monthly charges. These may be used as setup or service charges, etc...".
		"Charges will appear to the user with the description you attach. Each charge that you create for a user will decrement his account.");
		break;
	
	case 'list_did_billing':
		$icon = 'connect_to_network.png';
		$text = gettext("DID list and billing list. " .
		"You will see which cards have used your DIDs in past months and the traffic (amount of seconds).");
		break;
	
	case 'list_misc':
		$icon = 'kate.png';
		$text =gettext("The MISC module allow new customers to register automatically and use the system immediately.")
		.gettext(' Click here <a target="_blank" href="../signup/"><b>Signup Pages</b></a> to access the signup page.')
		.gettext(" A mail is automatically sent when a new signup is completed. Configure the mail template below.<br>")
		.gettext("A Reminder email can be sent (see a2billing.conf) to customers having low credit.");
		break;

	case 'campaign':
		$icon = 'yast_remote.png';
		$text = gettext("This section will allow you to create and edit campaign. <br>".
			"A campaign will be attached to a user in order to let him use the predictive-dialer option. <br>".
			"Predictive dialer will browse all the phone numbers from the campaign and perform outgoing calls.");
		break;
	
	case 'phonelist':
		$icon = 'yast_PhoneTTOffhook.png';
		$text =gettext("Phonelist are all the phone numbers attached to a campaign. You can add, remove and edit the phone numbers.");
		break;

	case 'provider':
		$icon = 'yast_remote.png';
		$text = gettext("This section will allow you to create and edit VOIP Providers for reporting purposes. ".
		"A provider is the company/person that provides you with termination.");
		break;
	
	case 'database_restore':
		$icon = 'yast_HD.png';
		$text = gettext("This section will allow you to restore or download an existing database backup. ".
		"The restore proccess will delete the existing database and import the new one ...".
		"Also you can upload a database backup that you previously downloaded , ".
		"but be sure that is correct and use the same file format.".
		"The process of restore can take some time, during that time no calls will be accepted.");
		break;

	case 'database_backup':
		$icon = 'yast_HD.png';
		$text = gettext("This section will allow you to backup an existing database context. ".
		"Backup proccess will export whole database , so you can restore later... <br/>".
		"The process of backup can take some time, during that time some calls will not be accepted.");
		break;
	
	case 'ecommerce':
		$icon = 'yast_multihead.png';
		$text = gettext("This section will allow you to define the E-Commerce Production Setting." .
		"<br>This will be use by E-Commerce API to find out how the new card have to be created.");
		break;
	
	case 'speeddial':
		$icon = 'stock_init.png';
		$text =gettext("This section allows you to define the Speed dials for the customer. <br>")
			.gettext("A Speed Dial will be entered on the IVR in order to make a shortcut to their preferred dialed phone number.");
		break;
	
	case 'list_prefix':
		$icon = 'connect_to_network.png';
		$text = gettext("Prefix list with destinations.");
		break;
	
	case 'edit_prefix':
		$icon = 'connect_to_network.png';
		$text =gettext("Prefixe can be assigned to a Ratecard");
		break;

	case 'edit_alarm':
		$icon = 'system-config-date.png';
		$text = gettext("Utility to apply a scheduled monitor on trunks.<br>")
			.gettext("For example if you want to monitor ASR (answer seize ratio) or ALOC (average length of call) everyday on each single trunk, it can be defined here, the different parameters below will define the rules to apply the alarm.");
		break;
	
	case 'list_alarm':
		$icon = 'system-config-date.png';
		$text =gettext("Alarms that monitors trunks at timed intervals.");
		break;
	
	case 'logfile':
		$icon = 'cache.png';
		$text =gettext("Browse for log file.<br> Use to locate the log file on a remote Web server.<br>It can generate combined reports for all logs. This tool can be use for extraction and presentation of information from various logfiles.");
		break;

	case 'callback':
		$icon = 'cache.png';
		$text =gettext("Callback will offer you an easy way to connect any phone to our Asterisk platform." .
		"We handle a spool with all the callbacks that need to be running and you might be able to view here all the pending and performed callback with their current status. Different parameters determine the callback, the way to reach the user, the time when we need to call him, the result of the last attempts, etc...");
		break;

	case 'offer_package':
		$icon = 'kthememgr.png';
		$text = gettext("PACKAGES SYSTEM - FREE MINUTES, etc...");
		break;
	
	case 'list_subscription':
		$icon = 'config-date.png';
		$text =gettext("SUBSCRITION FEE - You can bill in a monthly, weekly or anytime period the user for being subscribed on your service. The fee amount is defined here and the period through the cront configuration.");
		break;

	case 'server':
		$icon = 'network_local.png';
		$text =;
		break;

	case 'server_group':
		$icon = 'yast_multihead.png';
		$text =;
		break;

	case 'transaction':
		$icon = 'kspread.png';
		$text =gettext("You can view all the transactions proceed through the different epayment system configured (Paypal, MoneyBookers, etc...). ");
		break;

	case 'payment_config':
		$icon = 'kspread.png';
		$text =gettext("You can configure your epayment method here. It helps you to enable or disable the payment method. You can define the currency settings.");
		break;
	
	case 'list_payment_methods':
		$icon = 'kspread.png';
		$text =gettext("Epayment methods help you to collect payments from your customers.");
		break;
	default:
		$icon = 'vcard.png';
		$text = "No help for '" .$str ."' !";
	}
	
	echo '<a href="#" target="_self"  onclick="imgidclick(\'img1000\',\'div1000\',\'help.png\',\'viewmag.png\');"><img id="img1000" src="./Css/kicons/viewmag.png" onmouseover="this.style.cursor=\'hand\';" WIDTH="16" HEIGHT="16"></a>'. "\n";
	echo '<div id="div1000" style="display:visible;">'."\n";
	echo '<div id="kiki"><div class="w1">' ."\n";
	echo "<img src=\"./Css/kicons/$icon\" class=\"kikipic\"/>\n";
	echo "<div class=\"w2\">\n";
	echo "\t\t" .$text ."\n";
	echo '<br/><br/> </div></div></div></div><br style=\'clear:both\'>';
}

?>
