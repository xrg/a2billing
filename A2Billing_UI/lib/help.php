<?php

if (SHOW_HELP){

$CC_help_list_customer='
<a href="#" target="_self"  onclick="imgidclick(\'img1000\',\'div1000\',\'help.png\',\'viewmag.png\');"><img id="img1000" src="'.KICON_PATH.'/viewmag.png" onmouseover="this.style.cursor=\'hand\';" WIDTH="16" HEIGHT="16"></a>
<div id="div1000" style="display:visible;">
<div id="kiki"><div class="w1">
	<img src="'.KICON_PATH.'/vcard.gif" class="kikipic"/>
	<div class="w2">
'.gettext("Customers are listed below by card number. Each row corresponds to one customer, along with information such as their tariff group, credit remaining, etc.</br>")
.gettext("The SIP and IAX buttons create SIP and IAX entries to allow direct VoIP connections to the Asterisk server without further authentication.").'
<br/>
</div></div></div>
</div>';

$CC_help_refill_customer='
<a href="#" target="_self"  onclick="imgidclick(\'img1000\',\'div1000\',\'help.png\',\'viewmag.png\');"><img id="img1000" src="'.KICON_PATH.'/viewmag.png" onmouseover="this.style.cursor=\'hand\';" WIDTH="16" HEIGHT="16"></a>
<div id="div1000" style="display:visible;">
<div id="kiki"><div class="w1">
	<img src="'.KICON_PATH.'/pipe.gif" class="kikipic"/>
	<div class="w2">
'.gettext("Top up cards on this screen by selecting or typing in the card number directly, and enter the amount of credit to apply, then click ADD to confirm.").'
<br/><br/>
</div></div></div>
</div>';

$CC_help_create_customer='
<a href="#" target="_self"  onclick="imgidclick(\'img1000\',\'div1000\',\'help.png\',\'viewmag.png\');"><img id="img1000" src="'.KICON_PATH.'/viewmag.png" onmouseover="this.style.cursor=\'hand\';" WIDTH="16" HEIGHT="16"></a>
<div id="div1000" style="display:visible;">
<div id="kiki"><div class="w1">
	<img src="'.KICON_PATH.'/vcard.gif" class="kikipic"/>
	<div class="w2"><br>
'.gettext("Create and edit the properties of each customer. Click <b>CONFIRM DATA</b> at the bottom of the page to save changes.").'
<br/><br/>
</div></div></div>
</div>';

$CC_help_generate_customer='
<a href="#" target="_self"  onclick="imgidclick(\'img1000\',\'div1000\',\'help.png\',\'viewmag.png\');"><img id="img1000" src="'.KICON_PATH.'/viewmag.png" onmouseover="this.style.cursor=\'hand\';" WIDTH="16" HEIGHT="16"></a>
<div id="div1000" style="display:visible;">
<div id="kiki"><div class="w1">
	<img src="'.KICON_PATH.'/vcard.gif" class="kikipic"/>
	<div class="w2">
'.gettext("Bulk create cards and customers in a single step. <br> Set the properties of the batch such as initial credit, card type and currency, then click on the GENERATE CARDS button to create the batch.").'
<br/><br/>
</div></div></div>
</div>';

$CC_help_sipfriend_list ='
<a href="#" target="_self"  onclick="imgidclick(\'img1000\',\'div1000\',\'help.png\',\'viewmag.png\');"><img id="img1000" src="'.KICON_PATH.'/viewmag.png" onmouseover="this.style.cursor=\'hand\';" WIDTH="16" HEIGHT="16"></a>
<div id="div1000" style="display:visible;">
<div id="kiki"><div class="w1">
	<img src="'.KICON_PATH.'/network_local.gif" class="kikipic"/>
	<div class="w2">
'.gettext("SIP and IAX friend will create a SIP or IAX entry on the Asterisk server, so that a customer can set up a SIP or IAX client to connect directly to the asterisk server without the need to enter an account and pin each time a call is made. When done, click on the CONFIRM DATA button, then click reload to apply the changes on the Asterisk server.</br>")
.gettext("The customer must then enter the URL/IP address of the asterisk server into the SIP/IAX client, and use the Card Number and Secret as the username and password.").'
<br/>
</div></div></div>
</div>';

$CC_help_sipfriend_reload ='
<a href="#" target="_self"  onclick="imgidclick(\'img1000\',\'div1000\',\'help.png\',\'viewmag.png\');"><img id="img1000" src="'.KICON_PATH.'/viewmag.png" onmouseover="this.style.cursor=\'hand\';" WIDTH="16" HEIGHT="16"></a>
<div id="div1000" style="display:visible;">
<div id="kiki"><div class="w1">
	<img src="'.KICON_PATH.'/network_local.gif" class="kikipic"/>
	<div class="w2">
'.gettext("Click reload to commit changes to Asterisk").'<br>
<br/><br/>
</div></div></div>
</div>';

$CC_help_sipfriend_edit ='
<a href="#" target="_self"  onclick="imgidclick(\'img1000\',\'div1000\',\'help.png\',\'viewmag.png\');"><img id="img1000" src="'.KICON_PATH.'/viewmag.png" onmouseover="this.style.cursor=\'hand\';" WIDTH="16" HEIGHT="16"></a>
<div id="div1000" style="display:visible;">
<div id="kiki"><div class="w1">
	<img src="'.KICON_PATH.'/network_local.gif" class="kikipic"/>
	<div class="w2">

'.gettext("Each SIP/IAX client is identified by a number of paremeters.</br></br>")
.gettext("More details on how to configure clients are on the Wiki").' -> <a href="http://voip-info.org/wiki-Asterisk+config+sip.conf" target="_blank">sip.conf</a> &
<a href="http://voip-info.org/wiki-Asterisk+config+iax.conf" target="_blank">iax.conf</a>
<br/><br/>
</div></div></div>
</div>';

$CC_help_callerid_list ='
<a href="#" target="_self"  onclick="imgidclick(\'img1000\',\'div1000\',\'help.png\',\'viewmag.png\');"><img id="img1000" src="'.KICON_PATH.'/viewmag.png" onmouseover="this.style.cursor=\'hand\';" WIDTH="16" HEIGHT="16"></a>
<div id="div1000" style="display:visible;">
<div id="kiki"><div class="w1">
  <img src="'.KICON_PATH.'/vcard.gif" class="kikipic"/>
  <div class="w2"> 
'.gettext("CallerID, Set the Caller ID (CLI) that is delivered to the called party.</br>Set the callerID via the List Customer's screen").'<br>
<br/><br/>
</div></div></div>
</div>';

$CC_help_money_situation ='
<a href="#" target="_self"  onclick="imgidclick(\'img1000\',\'div1000\',\'help.png\',\'viewmag.png\');"><img id="img1000" src="'.KICON_PATH.'/viewmag.png" onmouseover="this.style.cursor=\'hand\';" WIDTH="16" HEIGHT="16"></a>
<div id="div1000" style="display:visible;">
<div id="kiki"><div class="w1">
	<img src="'.KICON_PATH.'/gnome-finance.gif" class="kikipic"/>
	<div class="w2">
'.gettext("This screen shows refills and payments made against each account, along with the current credit on each card. The initial amount of credit applied to the card is not included. The amount owing is calculated by subtracting payments from refills").'

<br/>
</div></div></div>
</div>';

$CC_help_view_payment ='
<a href="#" target="_self"  onclick="imgidclick(\'img1000\',\'div1000\',\'help.png\',\'viewmag.png\');"><img id="img1000" src="'.KICON_PATH.'/viewmag.png" onmouseover="this.style.cursor=\'hand\';" WIDTH="16" HEIGHT="16"></a>
<div id="div1000" style="display:visible;">
<div id="kiki"><div class="w1">
	<img src="'.KICON_PATH.'/gnome-finance.gif" class="kikipic"/>
	<div class="w2"><br>
'.gettext("Payment history - The section below shows all payments that have been received.").'
<br/>
<br/>
</div></div></div>
</div>';

$CC_help_view_paypal ='
<a href="#" target="_self"  onclick="imgidclick(\'img1000\',\'div1000\',\'help.png\',\'viewmag.png\');"><img id="img1000" src="'.KICON_PATH.'/viewmag.png" onmouseover="this.style.cursor=\'hand\';" WIDTH="16" HEIGHT="16"></a>
<div id="div1000" style="display:visible;">
<div id="kiki"><div class="w1">
	<img src="'.KICON_PATH.'/paypal.gif" class="kikipic"/>
	<div class="w2"><br>
'.gettext("Paypal History - The section below shows all paypal receipts.").'
<a href="https://www.paypal.com/es/mrb/pal=PGSJEXAEXKTBU">PayPal</a>
<br/><br/>
<br/>
</div></div></div>
</div>
<center>
<!-- Begin PayPal Logo --><A HREF="https://www.paypal.com/es/mrb/pal=PGSJEXAEXKTBU" target="_blank"><IMG  SRC="http://images.paypal.com/en_US/i/bnr/paypal_mrb_banner.gif" BORDER="0" ALT="Sign up for PayPal and start accepting credit card payments instantly."></A><!-- End PayPal Logo -->
</center>
';

$CC_help_add_payment ='
<a href="#" target="_self"  onclick="imgidclick(\'img1000\',\'div1000\',\'help.png\',\'viewmag.png\');"><img id="img1000" src="'.KICON_PATH.'/viewmag.png" onmouseover="this.style.cursor=\'hand\';" WIDTH="16" HEIGHT="16"></a>
<div id="div1000" style="display:visible;">
<div id="kiki"><div class="w1">
	<img src="'.KICON_PATH.'/gnome-finance.gif" class="kikipic"/>
	<div class="w2"><br/>
&nbsp; &nbsp;'.gettext("Add payments to a customer's account!").'
<br/>
<br/>
</div></div></div>
</div>';

$CC_help_list_tariffgroup ='
<a href="#" target="_self"  onclick="imgidclick(\'img1000\',\'div1000\',\'help.png\',\'viewmag.png\');"><img id="img1000" src="'.KICON_PATH.'/viewmag.png" onmouseover="this.style.cursor=\'hand\';" WIDTH="16" HEIGHT="16"></a>
<div id="div1000" style="display:visible;">
<div id="kiki"><div class="w1">
	<img src="'.KICON_PATH.'/network.gif" class="kikipic"/>
	<div class="w2"><br/>'.gettext("List of tariffgroups, a tariffgroup is a collection of rate cards, click edit to add ratecards to the tariffgroup").'
<br/><br/>
<br/>
</div></div></div>
</div>';

$CC_help_add_tariffgroup ='
<a href="#" target="_self"  onclick="imgidclick(\'img1000\',\'div1000\',\'help.png\',\'viewmag.png\');"><img id="img1000" src="'.KICON_PATH.'/viewmag.png" onmouseover="this.style.cursor=\'hand\';" WIDTH="16" HEIGHT="16"></a>
<div id="div1000" style="display:visible;">
<div id="kiki"><div class="w1">
	<img src="'.KICON_PATH.'/network.gif" class="kikipic"/>
	<div class="w2"> 
'.gettext("A tariffgroup is a collection of ratecards.")
.gettext("The calling card system will choose the most appropriate rate according to the tariffgroup settings (LCR or LCD).<br/>")
.gettext("LCR : Least Cost Routing - Find the trunk with the cheapest carrier cost. (buying rate)<br>")
.gettext("LCD : Least Cost Dialing - Find the trunk with the cheapest retail rate (selling rate)").'
<br/>
</div></div></div>
</div>';

$CC_help_list_ratecard ='
<a href="#" target="_self"  onclick="imgidclick(\'img1000\',\'div1000\',\'help.png\',\'viewmag.png\');"><img id="img1000" src="'.KICON_PATH.'/viewmag.png" onmouseover="this.style.cursor=\'hand\';" WIDTH="16" HEIGHT="16"></a>
<div id="div1000" style="display:visible;">
<div id="kiki"><div class="w1">
	<img src="'.KICON_PATH.'/kspread_ksp.gif" class="kikipic"/>
	<div class="w2"><br/> '.gettext("List ratecards that have been created").'
<br/><br/>
<br/>
</div></div></div>
</div>';

$CC_help_edit_ratecard ='
<a href="#" target="_self"  onclick="imgidclick(\'img1000\',\'div1000\',\'help.png\',\'viewmag.png\');"><img id="img1000" src="'.KICON_PATH.'/viewmag.png" onmouseover="this.style.cursor=\'hand\';" WIDTH="16" HEIGHT="16"></a>
<div id="div1000" style="display:visible;">
<div id="kiki"><div class="w1">
	<img src="'.KICON_PATH.'/kspread_ksp.gif" class="kikipic"/>
	<div class="w2"> '.gettext("Set the properties and attributes of the ratecard").'<br/>'
	.gettext("A ratecard is set of rates defined and applied according to the dialing prefix, for instance 441 & 442 : UK Landline.").'<br>'
	.gettext("Each ratecard may have has many rates as you wish, however, if a dialing prefix cannot be matched when a call is made, then the call will be terminated.").'<br>'
	.gettext('A ratecard has a "start date", an "expiry date" and a you can define a default trunk, but if no trunk is defined, the ratecard default trunk will be used.').'
<br/>
</div></div></div>
</div>';

$CC_help_def_ratecard ='
<a href="#" target="_self"  onclick="imgidclick(\'img1000\',\'div1000\',\'help.png\',\'viewmag.png\');"><img id="img1000" src="'.KICON_PATH.'/viewmag.png" onmouseover="this.style.cursor=\'hand\';" WIDTH="16" HEIGHT="16"></a>
<div id="div1000" style="display:visible;">
<div id="kiki"><div class="w1">
	<img src="'.KICON_PATH.'/kspread_ksp.gif" class="kikipic"/>
	<div class="w2"> </br>'.gettext("Please select a ratecard and click on search to browse the different rates/dialing prefix of the selected ratecard.").'<br/>

<br/>
</div></div></div>
</div>';

$CC_help_sim_ratecard ='
<a href="#" target="_self"  onclick="imgidclick(\'img1000\',\'div1000\',\'help.png\',\'viewmag.png\');"><img id="img1000" src="'.KICON_PATH.'/viewmag.png" onmouseover="this.style.cursor=\'hand\';" WIDTH="16" HEIGHT="16"></a>
<div id="div1000" style="display:visible;">
<div id="kiki"><div class="w1">
	<img src="'.KICON_PATH.'/kspread_ksp.gif" class="kikipic"/>
	<div class="w2"><br>'.gettext('Please select a calling card, enter the number you wish to call and press the "SIMULATE" button.').'<br/>
<br/>
</div></div></div>
</div>';

$CC_help_add_rate ='
<a href="#" target="_self"  onclick="imgidclick(\'img1000\',\'div1000\',\'help.png\',\'viewmag.png\');"><img id="img1000" src="'.KICON_PATH.'/viewmag.png" onmouseover="this.style.cursor=\'hand\';" WIDTH="16" HEIGHT="16"></a>
<div id="div1000" style="display:visible;">
<div id="kiki"><div class="w1">
	<img src="'.KICON_PATH.'/kspread_ksp.gif" class="kikipic"/>
	<div class="w2"><br>'
    .gettext("Please fill in the fields below to set up the rate for each destination.").'
<br><br>
</div></div></div>
</div>';

$CC_help_import_ratecard ='
<a href="#" target="_self"  onclick="imgidclick(\'img1000\',\'div1000\',\'help.png\',\'viewmag.png\');"><img id="img1000" src="'.KICON_PATH.'/viewmag.png" onmouseover="this.style.cursor=\'hand\';" WIDTH="16" HEIGHT="16"></a>
<div id="div1000" style="display:visible;">
<div id="kiki"><div class="w1">
	<img src="'.KICON_PATH.'/spreadsheet.gif" class="kikipic"/>
	<div class="w2">'
    .gettext("This section is a utility to import ratecards from a CSV file.<br>")
	.gettext('Define the ratecard name, the trunk to use and the fields that you wish to include from your csv files. Finally, select the csv files and click on the "Import Ratecard" button.').'
	<br>
	<br>
</div></div></div>
</div>';

$CC_help_import_ratecard_analyse ='
<a href="#" target="_self"  onclick="imgidclick(\'img1000\',\'div1000\',\'help.png\',\'viewmag.png\');"><img id="img1000" src="'.KICON_PATH.'/viewmag.png" onmouseover="this.style.cursor=\'hand\';" WIDTH="16" HEIGHT="16"></a>
<div id="div1000" style="display:visible;">
<div id="kiki"><div class="w1">
	<img src="'.KICON_PATH.'/spreadsheet.gif" class="kikipic"/>
	<div class="w2">'
    .gettext('This is the second step of the import ratecard! <br>')
	.gettext('The first line of your csv files has been read and the values are displayed below according to the fields')
	.gettext('you decided to import on the ratecard! You can check the values and if there are correct,')
    .gettext('please select the same file and click on "Continue to Import the Ratecard" button...').'
	<br><br>
</div></div></div>
</div>';

$CC_help_import_ratecard_confirm ='
<a href="#" target="_self"  onclick="imgidclick(\'img1000\',\'div1000\',\'help.png\',\'viewmag.png\');"><img id="img1000" src="'.KICON_PATH.'/viewmag.png" onmouseover="this.style.cursor=\'hand\';" WIDTH="16" HEIGHT="16"></a>
<div id="div1000" style="display:visible;">
<div id="kiki"><div class="w1">
	<img src="'.KICON_PATH.'/spreadsheet.gif" class="kikipic"/>
	<div class="w2">.'
    .gettext('Ratecard comfirmation page. <br>')
	.gettext('Import results, how many new rates have been imported, and the line numbers of the CSV files that generated errors.').'
	<br><br>
</div></div></div>
</div>';

$CC_help_trunk_list ='
<a href="#" target="_self"  onclick="imgidclick(\'img1000\',\'div1000\',\'help.png\',\'viewmag.png\');"><img id="img1000" src="'.KICON_PATH.'/viewmag.png" onmouseover="this.style.cursor=\'hand\';" WIDTH="16" HEIGHT="16"></a>
<div id="div1000" style="display:visible;">
<div id="kiki"><div class="w1">
	<img src="'.KICON_PATH.'/hwbrowser.gif" class="kikipic"/>
	<div class="w2">
'.gettext("Trunk List<br/>Trunks can be modified by clicking the edit button").'

<br/><br/>
</div></div></div>
</div>';

$CC_help_trunk_edit ='
<a href="#" target="_self"  onclick="imgidclick(\'img1000\',\'div1000\',\'help.png\',\'viewmag.png\');"><img id="img1000" src="'.KICON_PATH.'/viewmag.png" onmouseover="this.style.cursor=\'hand\';" WIDTH="16" HEIGHT="16"></a>
<div id="div1000" style="display:visible;">
<div id="kiki"><div class="w1">
	<img src="'.KICON_PATH.'/hwbrowser.gif" class="kikipic"/>
	<div class="w2">
'.gettext("Trunks are used to terminate the call!<br>")
.gettext("The trunk and ratecard is selected by the rating engine on the basis of the dialed digits.")
.gettext("The trunk is used to dial out from your asterisk box which can be a zaptel interface or a voip provider.").'
<br/>
</div></div></div>
</div>';

$CC_help_admin_list ='
<a href="#" target="_self"  onclick="imgidclick(\'img1000\',\'div1000\',\'help.png\',\'viewmag.png\');"><img id="img1000" src="'.KICON_PATH.'/viewmag.png" onmouseover="this.style.cursor=\'hand\';" WIDTH="16" HEIGHT="16"></a>
<div id="div1000" style="display:visible;">
<div id="kiki"><div class="w1">
	<img src="'.KICON_PATH.'/kdmconfig.gif" class="kikipic"/>
	<div class="w2">
	<br/>'
	.gettext("Administrator list who have access to the calling card administrative interface.").'
<br/><br/>
</div></div></div>
</div>';

$CC_help_admin_edit ='
<a href="#" target="_self"  onclick="imgidclick(\'img1000\',\'div1000\',\'help.png\',\'viewmag.png\');"><img id="img1000" src="'.KICON_PATH.'/viewmag.png" onmouseover="this.style.cursor=\'hand\';" WIDTH="16" HEIGHT="16"></a>
<div id="div1000" style="display:visible;">
<div id="kiki"><div class="w1">
	<img src="'.KICON_PATH.'/kdmconfig.gif" class="kikipic"/>
	<div class="w2"><br>'
.gettext("Add administrator.").'
<br/><br/>
</div></div></div>
</div>';

$CC_help_list_voucher='
<a href="#" target="_self"  onclick="imgidclick(\'img1000\',\'div1000\',\'help.png\',\'viewmag.png\');"><img id="img1000" src="'.KICON_PATH.'/viewmag.png" onmouseover="this.style.cursor=\'hand\';" WIDTH="16" HEIGHT="16"></a>
<div id="div1000" style="display:visible;">
<div id="kiki"><div class="w1">
	<img src="'.KICON_PATH.'/vcard.gif" class="kikipic"/>
	<div class="w2"><br>'
.gettext("Listed below are the vouchers created on the system,.<br/>")
.gettext("Each row corresponds to a voucher and shows it's status, value and currency..").'
<br/><br/>
</div></div></div>
</div>';

$CC_help_create_voucher='
<a href="#" target="_self"  onclick="imgidclick(\'img1000\',\'div1000\',\'help.png\',\'viewmag.png\');"><img id="img1000" src="'.KICON_PATH.'/viewmag.png" onmouseover="this.style.cursor=\'hand\';" WIDTH="16" HEIGHT="16"></a>
<div id="div1000" style="display:visible;">
<div id="kiki"><div class="w1">
	<img src="'.KICON_PATH.'/vcard.gif" class="kikipic"/>
	<div class="w2">
<br/>'
.gettext("Create a single voucher, defining such properties as credit, tag, currency etc, click confirm when finished. <br/> The customer applies voucher credit to their card via the customer interface.").'
<br/><br/>
</div></div></div>
</div>';

$CC_help_generate_voucher='
<a href="#" target="_self"  onclick="imgidclick(\'img1000\',\'div1000\',\'help.png\',\'viewmag.png\');"><img id="img1000" src="'.KICON_PATH.'/viewmag.png" onmouseover="this.style.cursor=\'hand\';" WIDTH="16" HEIGHT="16"></a>
<div id="div1000" style="display:visible;">
<div id="kiki"><div class="w1">
	<img src="'.KICON_PATH.'/vcard.gif" class="kikipic"/>
	<div class="w2">'
.gettext("Bulk generate a batch of vouchers, defining such properties as credit and currency etc, click Generate Vouchers when finished.<br/>The customer applies voucher credit to their card via the customer interface. ").'
<br/><br/>
</div></div></div>
</div>';

$CC_help_list_service ='
<a href="#" target="_self"  onclick="imgidclick(\'img1000\',\'div1000\',\'help.png\',\'viewmag.png\');"><img id="img1000" src="'.KICON_PATH.'/viewmag.png" onmouseover="this.style.cursor=\'hand\';" WIDTH="16" HEIGHT="16"></a>
<div id="div1000" style="display:visible;">
<div id="kiki"><div class="w1">
	<img src="'.KICON_PATH.'/system-config-date.gif" class="kikipic"/>
	<div class="w2">
	<br/>'
	.gettext("Re-occuring services that decrement a card at timed intervals.").'
<br/><br/>
</div></div></div>
</div>';

$CC_help_edit_service ='
<a href="#" target="_self"  onclick="imgidclick(\'img1000\',\'div1000\',\'help.png\',\'viewmag.png\');"><img id="img1000" src="'.KICON_PATH.'/viewmag.png" onmouseover="this.style.cursor=\'hand\';" WIDTH="16" HEIGHT="16"></a>
<div id="div1000" style="display:visible;">
<div id="kiki"><div class="w1">
	<img src="'.KICON_PATH.'/system-config-date.gif" class="kikipic"/>
	<div class="w2">'
.gettext("Utility to apply a scheduled action on the card.<br>")
.gettext("For example if you want to remove 10 cents everyday on each single card, it can be defined here, alternatively, if you now want to remove 1 credit every week but only 7 times on each card, the different rules/parameters below will define this.").'
<br/>
</div></div></div>
</div>';

$CC_help_list_cidgroup ='
<a href="#" target="_self"  onclick="imgidclick(\'img1000\',\'div1000\',\'help.png\',\'viewmag.png\');"><img id="img1000" src="'.KICON_PATH.'/viewmag.png" onmouseover="this.style.cursor=\'hand\';" WIDTH="16" HEIGHT="16"></a>
<div id="div1000" style="display:visible;">
<div id="kiki"><div class="w1">
	<img src="'.KICON_PATH.'/connect_to_network.gif" class="kikipic"/>
	<div class="w2">
	<br/>
	'.gettext("CID Group list. CID can be chosen by customers through the customer interface.").'<br/>
<br/>
</div></div></div>
</div>';

$CC_help_list_cid ='
<a href="#" target="_self"  onclick="imgidclick(\'img1000\',\'div1000\',\'help.png\',\'viewmag.png\');"><img id="img1000" src="'.KICON_PATH.'/viewmag.png" onmouseover="this.style.cursor=\'hand\';" WIDTH="16" HEIGHT="16"></a>
<div id="div1000" style="display:visible;">
<div id="kiki"><div class="w1">
	<img src="'.KICON_PATH.'/connect_to_network.gif" class="kikipic"/>
	<div class="w2">
	<br/>
	'.gettext("Outbound CID list. CID can be added by customers through the customer interface.").'<br/>
<br/>
</div></div></div>
</div>';


$CC_help_edit_cidgroup ='
<a href="#" target="_self"  onclick="imgidclick(\'img1000\',\'div1000\',\'help.png\',\'viewmag.png\');"><img id="img1000" src="'.KICON_PATH.'/viewmag.png" onmouseover="this.style.cursor=\'hand\';" WIDTH="16" HEIGHT="16"></a>
<div id="div1000" style="display:visible;">
<div id="kiki"><div class="w1">
	<img src="'.KICON_PATH.'/connect_to_network.gif" class="kikipic"/>
	<div class="w2">
<br/>'
.gettext("CID group offers customers a group of CID numbers which can be selected for a ratecard for outgoing calls").'<br>
<br/>
</div></div></div>
</div>';

$CC_help_edit_cid ='
<a href="#" target="_self"  onclick="imgidclick(\'img1000\',\'div1000\',\'help.png\',\'viewmag.png\');"><img id="img1000" src="'.KICON_PATH.'/viewmag.png" onmouseover="this.style.cursor=\'hand\';" WIDTH="16" HEIGHT="16"></a>
<div id="div1000" style="display:visible;">
<div id="kiki"><div class="w1">
	<img src="'.KICON_PATH.'/connect_to_network.gif" class="kikipic"/>
	<div class="w2">
<br/>'
.gettext("Outbound CID offers customers a number which will be selected randomly for a ratecard for outgoing calls").'<br>
<br/>
</div></div></div>
</div>';

$CC_help_currency ='
<a href="#" target="_self"  onclick="imgidclick(\'img1000\',\'div1000\',\'help.png\',\'viewmag.png\');"><img id="img1000" src="'.KICON_PATH.'/viewmag.png" onmouseover="this.style.cursor=\'hand\';" WIDTH="16" HEIGHT="16"></a>
<div id="div1000" style="display:visible;">
<div id="kiki"><div class="w1">
	<img src="'.KICON_PATH.'/favorites.gif" class="kikipic"/>
	<div class="w2">'
.gettext("Currency data are automaticaly updated from Yahoo Financial.")
.gettext("<br>For more information please visite the website http://finance.yahoo.com.")
.gettext("<br>The table below is based over your currency base :").' <b>'.BASE_CURRENCY.'</b>'
.gettext("<br>Sorry for all these stars, you will have to deal with it :P ").'
<br>
</div></div></div>
</div>';

$CC_help_list_didgroup ='
<a href="#" target="_self"  onclick="imgidclick(\'img1000\',\'div1000\',\'help.png\',\'viewmag.png\');"><img id="img1000" src="'.KICON_PATH.'/viewmag.png" onmouseover="this.style.cursor=\'hand\';" WIDTH="16" HEIGHT="16"></a>
<div id="div1000" style="display:visible;">
<div id="kiki"><div class="w1">
	<img src="'.KICON_PATH.'/connect_to_network.gif" class="kikipic"/>
	<div class="w2">
	<br/>
	'.gettext("DID (or DDI) Group list. DID can be chosen by customers through the customer interface.").'<br/>
<br/>
</div></div></div>
</div>';

$CC_help_edit_didgroup ='
<a href="#" target="_self"  onclick="imgidclick(\'img1000\',\'div1000\',\'help.png\',\'viewmag.png\');"><img id="img1000" src="'.KICON_PATH.'/viewmag.png" onmouseover="this.style.cursor=\'hand\';" WIDTH="16" HEIGHT="16"></a>
<div id="div1000" style="display:visible;">
<div id="kiki"><div class="w1">
	<img src="'.KICON_PATH.'/connect_to_network.gif" class="kikipic"/>
	<div class="w2">
<br/>'
.gettext("DID group offers customers a group of DID numbers which can be selected by the customer").'<br>
<br/>
</div></div></div>
</div>';

$CC_help_list_did ='
<a href="#" target="_self"  onclick="imgidclick(\'img1000\',\'div1000\',\'help.png\',\'viewmag.png\');"><img id="img1000" src="'.KICON_PATH.'/viewmag.png" onmouseover="this.style.cursor=\'hand\';" WIDTH="16" HEIGHT="16"></a>
<div id="div1000" style="display:visible;">
<div id="kiki"><div class="w1">
	<img src="'.KICON_PATH.'/connect_to_network.gif" class="kikipic"/>
	<div class="w2">
	<br/>'
	.gettext("DID number list with destinations.").'<br/>

<br/>
</div></div></div>
</div>';

$CC_help_edit_did ='
<a href="#" target="_self"  onclick="imgidclick(\'img1000\',\'div1000\',\'help.png\',\'viewmag.png\');"><img id="img1000" src="'.KICON_PATH.'/viewmag.png" onmouseover="this.style.cursor=\'hand\';" WIDTH="16" HEIGHT="16"></a>
<div id="div1000" style="display:visible;">
<div id="kiki"><div class="w1">
	<img src="'.KICON_PATH.'/connect_to_network.gif" class="kikipic"/>
	<div class="w2">
<br/>'
.gettext("DID can be assigned to a card to re-route calls to a SIP/IAX client!").'<br>
<br/>
</div></div></div>
</div>';

$CC_help_list_did_use ='
<a href="#" target="_self"  onclick="imgidclick(\'img1000\',\'div1000\',\'help.png\',\'viewmag.png\');"><img id="img1000" src="'.KICON_PATH.'/viewmag.png" onmouseover="this.style.cursor=\'hand\';" WIDTH="16" HEIGHT="16"></a>
<div id="div1000" style="display:visible;">
<div id="kiki"><div class="w1">
	<img src="'.KICON_PATH.'/connect_to_network.gif" class="kikipic"/>
	<div class="w2">
	<br/>'
	.gettext("DID currently in use list with user card id and number <br> You can use the search option to dshow the use history of a given did or all dids.").'<br/>
<br/>
</div></div></div>
</div>';

$CC_help_release_did ='
<a href="#" target="_self"  onclick="imgidclick(\'img1000\',\'div1000\',\'help.png\',\'viewmag.png\');"><img id="img1000" src="'.KICON_PATH.'/viewmag.png" onmouseover="this.style.cursor=\'hand\';" WIDTH="16" HEIGHT="16"></a>
<div id="div1000" style="display:visible;">
<div id="kiki"><div class="w1">
	<img src="'.KICON_PATH.'/connect_to_network.gif" class="kikipic"/>
	<div class="w2">
	<br/>'
	.gettext("Releasing DID put it in free stat and the user will not be monthly charged any more..").'<br/>
<br/>
</div></div></div>
</div>';

$CC_help_edit_charge ='
<a href="#" target="_self"  onclick="imgidclick(\'img1000\',\'div1000\',\'help.png\',\'viewmag.png\');"><img id="img1000" src="'.KICON_PATH.'/viewmag.png" onmouseover="this.style.cursor=\'hand\';" WIDTH="16" HEIGHT="16"></a>
<div id="div1000" style="display:visible;">
<div id="kiki"><div class="w1">
	<img src="'.KICON_PATH.'/wi0124-48.gif" class="kikipic"/>
	<div class="w2">'
.gettext("Extra Charges are to allow the billing of one-off or re-occurring monthly charges. These may be used as setup or service charges, etc...")
.gettext("Charges will appear to the user with the description you attach. Each charge that you create for a user will decrement his account.").'
<br/>
</div></div></div>
</div>';

$CC_help_list_did_billing ='
<a href="#" target="_self"  onclick="imgidclick(\'img1000\',\'div1000\',\'help.png\',\'viewmag.png\');"><img id="img1000" src="'.KICON_PATH.'/viewmag.png" onmouseover="this.style.cursor=\'hand\';" WIDTH="16" HEIGHT="16"></a>
<div id="div1000" style="display:visible;">
<div id="kiki"><div class="w1">
	<img src="'.KICON_PATH.'/connect_to_network.gif" class="kikipic"/>
	<div class="w2">'
	.gettext("DID list and billing list. ")
    .gettext("You will see which cards have used your DIDs in past months and the traffic (amount of seconds).").'
<br/><br/>
</div></div></div>
</div>';

$CC_help_list_signup ='
<a href="#" target="_self"  onclick="imgidclick(\'img1000\',\'div1000\',\'help.png\',\'viewmag.png\');"><img id="img1000" src="'.KICON_PATH.'/viewmag.png" onmouseover="this.style.cursor=\'hand\';" WIDTH="16" HEIGHT="16"></a>
<div id="div1000" style="display:visible;">
<div id="kiki"><div class="w1">
	<img src="'.KICON_PATH.'/kate.gif" class="kikipic"/>
	<div class="w2">
		'.gettext("The SIGNUP module allow new customers to register automatically and use the system immediately.")
    	.gettext(' Click here <a target="_blank" href="../signup/"><b>Signup Pages</b></a> to access the signup page.')
    	.gettext(" A mail is automatically sent when a new signup is completed. Configure the mail template below.<br>")
    	.gettext("A Reminder email can be sent (see a2billing.conf) to customers having low credit.").'
<br/>
</div></div></div>
</div>';

$CC_help_campaign ='
<a href="#" target="_self"  onclick="imgidclick(\'img1000\',\'div1000\',\'help.png\',\'viewmag.png\');"><img id="img1000" src="'.KICON_PATH.'/viewmag.png" onmouseover="this.style.cursor=\'hand\';" WIDTH="16" HEIGHT="16"></a>
<div id="div1000" style="display:visible;">
<div id="kiki"><div class="w1">
	<img src="'.KICON_PATH.'/yast_remote.gif" class="kikipic"/>
	<div class="w2">'
	.gettext("This section will allow you to create and edit campaign. ")
    .gettext("A campaign will be attached to a user in order to let him use the predictive-dialer option. ")
    .gettext("Predictive dialer will browse all the phone numbers from the campaign and perform outgoing calls.").'
<br/><br/>
</div></div></div>
</div>';

$CC_help_phonelist ='
<a href="#" target="_self"  onclick="imgidclick(\'img1000\',\'div1000\',\'help.png\',\'viewmag.png\');"><img id="img1000" src="'.KICON_PATH.'/viewmag.png" onmouseover="this.style.cursor=\'hand\';" WIDTH="16" HEIGHT="16"></a>
<div id="div1000" style="display:visible;">
<div id="kiki"><div class="w1">
	<img src="'.KICON_PATH.'/yast_PhoneTTOffhook.gif" class="kikipic"/>
	<div class="w2"><br/>'
	.gettext("Phonelist are all the phone numbers attached to a campaign. You can add, remove and edit the phone numbers.").'
	<br/><br/>
</div></div></div>
</div>';

$CC_help_provider ='
<a href="#" target="_self"  onclick="imgidclick(\'img1000\',\'div1000\',\'help.png\',\'viewmag.png\');"><img id="img1000" src="'.KICON_PATH.'/viewmag.png" onmouseover="this.style.cursor=\'hand\';" WIDTH="16" HEIGHT="16"></a>
<div id="div1000" style="display:visible;">
<div id="kiki"><div class="w1">
	<img src="'.KICON_PATH.'/yast_remote.gif" class="kikipic"/>
	<div class="w2"><br/>'
	.gettext("This section will allow you to create and edit VOIP Providers for reporting purposes. ")
    .gettext("A provider is the company/person that provides you with termination.").'
<br/><br/>
</div></div></div>
</div>';

$CC_help_database_restore ='
<a href="#" target="_self"  onclick="imgidclick(\'img1000\',\'div1000\',\'help.png\',\'viewmag.png\');"><img id="img1000" src="'.KICON_PATH.'/viewmag.png" onmouseover="this.style.cursor=\'hand\';" WIDTH="16" HEIGHT="16"></a>
<div id="div1000" style="display:visible;">
<div id="kiki"><div class="w1">
<img src="'.KICON_PATH.'/yast_HD.gif" class="kikipic"/>
        <div class="w2">'
        .gettext("This section will allow you to restore or download an existing database backup. ")
        .gettext("The restore proccess will delete the existing database and import the new one ...")
        .gettext("Also you can upload a database backup that you previously downloaded , ")
        .gettext("but be sure that is correct and use the same file format.")
        .gettext("The process of restore can take some time , during that time no calls will be accepted.").'
<br/>
</div></div></div>
</div>';

$CC_help_database_backup='
<a href="#" target="_self"  onclick="imgidclick(\'img1000\',\'div1000\',\'help.png\',\'viewmag.png\');"><img id="img1000" src="'.KICON_PATH.'/viewmag.png" onmouseover="this.style.cursor=\'hand\';" WIDTH="16" HEIGHT="16"></a>
<div id="div1000" style="display:visible;">
<div id="kiki"><div class="w1">
<img src="'.KICON_PATH.'/yast_HD.gif" class="kikipic"/>
        <div class="w2">'
		.gettext("This section will allow you to backup an existing database context. ")
		.gettext("Backup proccess will export whole database , so you can restore later... <br/>")
		.gettext("The process of backup can take some time, during that time some calls will not be accepted.").'
		<br/>
</div></div></div>
</div>';
				
$CC_help_ecommerce ='
<a href="#" target="_self"  onclick="imgidclick(\'img1000\',\'div1000\',\'help.png\',\'viewmag.png\');"><img id="img1000" src="'.KICON_PATH.'/viewmag.png" onmouseover="this.style.cursor=\'hand\';" WIDTH="16" HEIGHT="16"></a>
<div id="div1000" style="display:visible;">
<div id="kiki"><div class="w1">
	<img src="'.KICON_PATH.'/yast_multihead.gif" class="kikipic"/>
	<div class="w2">
	<br/>'
	.gettext("This section will allow you to define the E-Commerce Production Setting.")
	.gettext("<br>This will be use by E-Commerce API to find out how the new card have to be created.").'
<br/><br/>
</div></div></div>
</div>';

$CC_help_speeddial ='
<a href="#" target="_self"  onclick="imgidclick(\'img1000\',\'div1000\',\'help.png\',\'viewmag.png\');"><img id="img1000" src="'.KICON_PATH.'/viewmag.png" onmouseover="this.style.cursor=\'hand\';" WIDTH="16" HEIGHT="16"></a>
<div id="div1000" style="display:visible;">
<div id="kiki"><div class="w1">
	<img src="'.KICON_PATH.'/stock_init.gif" class="kikipic"/>
	<div class="w2">'
	.gettext("This section allows you to define the Speed dials for the customer. <br>")
	.gettext("A Speed Dial will be entered on the IVR in order to make a shortcut to their preferred dialed phone number.").'
<br/><br/>
</div></div></div>
</div>';

$CC_help_list_prefix='
<a href="#" target="_self"  onclick="imgidclick(\'img1000\',\'div1000\',\'help.png\',\'viewmag.png\');"><img id="img1000" src="'.KICON_PATH.'/viewmag.png" onmouseover="this.style.cursor=\'hand\';" WIDTH="16" HEIGHT="16"></a>
<div id="div1000" style="display:visible;">
<div id="kiki"><div class="w1">
	<img src="'.KICON_PATH.'/connect_to_network.gif" class="kikipic"/>
	<div class="w2">
	<br/>'
	.gettext("Prefixe list with destinations.").'<br/>
<br/>
</div></div></div>
</div>';

$CC_help_edit_prefix ='
<a href="#" target="_self"  onclick="imgidclick(\'img1000\',\'div1000\',\'help.png\',\'viewmag.png\');"><img id="img1000" src="'.KICON_PATH.'/viewmag.png" onmouseover="this.style.cursor=\'hand\';" WIDTH="16" HEIGHT="16"></a>
<div id="div1000" style="display:visible;">
<div id="kiki"><div class="w1">
	<img src="'.KICON_PATH.'/connect_to_network.gif" class="kikipic"/>
	<div class="w2">
<br/>'
.gettext("Prefixe can be assigned to a Ratecard").'<br>
<br/>
</div></div></div>
</div>';

$CC_help_edit_alarm='<a href="#" target="_self"  onclick="imgidclick(\'img1000\',\'div1000\',\'help.png\',\'viewmag.png\');"><img id="img1000" src="'.KICON_PATH.'/viewmag.png" onmouseover="this.style.cursor=\'hand\';" WIDTH="16" HEIGHT="16"></a>
<div id="div1000" style="display:visible;">
<div id="kiki"><div class="w1">
	<img src="'.KICON_PATH.'/system-config-date.gif" class="kikipic"/>
	<div class="w2">'
.gettext("Utility to apply a scheduled monitor on trunks.<br>")
.gettext("For example if you want to monitor ASR or ALOC everyday on each single trunk, it can be defined here, the different parameters below will define the rules to apply the alarm.").'
<br/>
</div></div></div>
</div>
';

$CC_help_list_alarm='<a href="#" target="_self"  onclick="imgidclick(\'img1000\',\'div1000\',\'help.png\',\'viewmag.png\');"><img id="img1000" src="'.KICON_PATH.'/viewmag.png" onmouseover="this.style.cursor=\'hand\';" WIDTH="16" HEIGHT="16"></a>
<div id="div1000" style="display:visible;">
<div id="kiki"><div class="w1">
	<img src="'.KICON_PATH.'/system-config-date.gif" class="kikipic"/>
	<div class="w2">'
.gettext("Alarms that monitor trunks at timed intervals.<br>").'
<br/>
</div></div></div>
</div>
';

$CC_help_logfile='<a href="#" target="_self"  onclick="imgidclick(\'img1000\',\'div1000\',\'help.png\',\'viewmag.png\');"><img id="img1000" src="'.KICON_PATH.'/viewmag.png" onmouseover="this.style.cursor=\'hand\';" WIDTH="16" HEIGHT="16"></a>
<div id="div1000" style="display:visible;">
<div id="kiki"><div class="w1">
	<img src="'.KICON_PATH.'/cache.gif" class="kikipic"/>
	<div class="w2">'
.gettext("Browse for log file.<br> Use to locate the log file on a remote Web server.<br>It can generate combined reports for all logs. This tool can be use for extraction and presentation of information from various logfiles.").'
<br/>
</div></div></div>
</div>
';

$CC_help_callback='<a href="#" target="_self"  onclick="imgidclick(\'img1000\',\'div1000\',\'help.png\',\'viewmag.png\');"><img id="img1000" src="'.KICON_PATH.'/viewmag.png" onmouseover="this.style.cursor=\'hand\';" WIDTH="16" HEIGHT="16"></a>
<div id="div1000" style="display:visible;">
<div id="kiki"><div class="w1">
	<img src="'.KICON_PATH.'/cache.gif" class="kikipic"/>
	<div class="w2">'
.gettext("Callback will offer you an easy way to connect any phone to our Asterisk platform.
We handle a spool with all the callbacks that need to be running and you might be able to view here all the pending and performed callback with their current status. Different parameters determine the callback, the way to reach the user, the time when we need to call him, the result of the last attempts, etc...").'
<br/>
</div></div></div>
</div>
';

$CC_help_offer_package='<a href="#" target="_self"  onclick="imgidclick(\'img1000\',\'div1000\',\'help.png\',\'viewmag.png\');"><img id="img1000" src="'.KICON_PATH.'/viewmag.png" onmouseover="this.style.cursor=\'hand\';" WIDTH="16" HEIGHT="16"></a>
<div id="div1000" style="display:visible;">
<div id="kiki"><div class="w1">
	<img src="'.KICON_PATH.'/kthememgr.gif" class="kikipic"/>
	<div class="w2">'
.gettext("PACKAGES SYSTEM - FREE MINUTES, etc...").'
<br/><br/><br/>
</div></div></div>
</div>
';

$CC_help_list_subscription='<a href="#" target="_self"  onclick="imgidclick(\'img1000\',\'div1000\',\'help.png\',\'viewmag.png\');"><img id="img1000" src="'.KICON_PATH.'/viewmag.png" onmouseover="this.style.cursor=\'hand\';" WIDTH="16" HEIGHT="16"></a>
<div id="div1000" style="display:visible;">
<div id="kiki"><div class="w1">
	<img src="'.KICON_PATH.'/config-date.gif" class="kikipic"/>
	<div class="w2">'
.gettext("SUBSCRITION FEE - You can bill in a monthly, weekly or anytime period the user for being subscribed on your service. The fee amount is defined here and the period through the cront configuration.").'
<br/><br/><br/>
</div></div></div>
</div>
';

} //ENDIF SHOW_HELP
?>
