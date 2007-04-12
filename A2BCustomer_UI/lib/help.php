<?php

if (SHOW_HELP){

$CC_help_webphone='
<div id="div1000" style="display:visible;">
<div id="kiblue_header"><div class="w4">
	<img src="'.KICON_PATH.'/stock_landline-phone.gif" class="kikipic"/>
	<div class="w2">
<table width="90%">
<tr>
<td width="50%">'.gettext("From here, you can use the web based screen phone. You need microphone and speakers on your PC.").
'</br></br>
</td>
</tr>
</table>
</div></div></div>';

$CC_help_balance_customer='
<div id="div1000" style="display:visible;">
<div id="kiblue_header"><div class="w4">
	<img src="'.KICON_PATH.'/gnome-finance.gif" width="48" height="48" class="kikipic"/>
	<div class="w2">
<table width="90%">
<tr>
<td width="100%">

'.gettext("All calls are listed below. Search by month, day or status. Additionally, you can check the rate and price.").' 
<br></br>
</td>
</tr>
</table>
</div></div></div>&nbsp;';


$CC_help_simulator_rateengine='
<div id="div1000" style="display:visible;">
<div id="kiblue_header"><div class="w4">
	<img src="'.KICON_PATH.'/connect_to_network.gif" width="48" height="48" class="kikipic"/>
	<div class="w2">
<table width="90%">
<tr>
<td width="100%">
'.gettext("Simulate the calling process to discover the cost per minute of a call, and the number of minutes you can call that number with your current credit.").'
</td>
</tr>
</table>
</div></div></div>
&nbsp;
';

$CC_help_password_change ='
<div id="div1000" style="display:visible;">
<div id="kiblue_header"><div class="w4">
	<img src="'.KICON_PATH.'/connect_to_network.gif" width="48" height="48" class="kikipic"/>
	<div class="w2">
<table width="90%">
<tr>
<td width="100%">
'.gettext("On this page you will be able to change your password, You have to enter the New Password and Confirm it.").'
<br>&nbsp;
</td>
</tr>
</table>
</div></div></div>
&nbsp;
';

$CC_help_ratecard ='
<div id="div1000" style="display:visible;">
<div id="kiblue_header"><div class="w4">
	<img src="'.KICON_PATH.'/connect_to_network.gif" width="48" height="48" class="kikipic"/>
	<div class="w2">
<table width="90%"><tr><td width="100%">'.
gettext("Here you can view your ratecards").
'<br>&nbsp;
</td></tr></table>
</div></div></div>
';


$CC_help_list_voucher = '
<div id="div1000" style="display:visible;">
<div id="kiblue_header"><div class="w4">
	<img src="'.KICON_PATH.'/vcard.gif" width="50" height="50" class="kikipic"/>
	<div class="w2">
<table width="90%">
<tr height="55px">
<td width="100%">
'.gettext("Enter your voucher number to top up your card.").'
<br>&nbsp;
</td>
</tr>
</table>
</div></div></div>
';


$CC_help_list_did = '
<div id="div1000" style="display:visible;">
<div id="kiblue_header"><div class="w4">
	<img src="'.KICON_PATH.'/vcard.gif" width="50" height="50" class="kikipic"/>
	<div class="w2">
<table width="90%">
<tr height="55px">
<td width="100%">
'.gettext("Select the country below where you would like a DID, select a DID from the list and enter the destination you would like to assign it to.").'
<br>&nbsp;
</td>
</tr>
</table>
</div></div></div><br>
';


$CC_help_release_did ='
<a href="#" target="_self"  onclick="imgidclick(\'img1000\',\'div1000\',\'help.png\',\'viewmag.png\');"><img id="img1000" src="'.KICON_PATH.'/viewmag.png" onmouseover="this.style.cursor=\'hand\';" WIDTH="16" HEIGHT="16"></a>
<div id="div1000" style="display:visible;">
<div id="kiki"><div class="w1">
	<img src="'.KICON_PATH.'/connect_to_network.gif" class="kikipic"/>
	<div class="w2">
	<br/>'
	.gettext("WARNING !  <br> after confirmation, the release of the did will be done immediately and you will not be monthly charged any more.").'<br/>

<br/>
</div></div></div>
</div>';

} //ENDIF SHOW_HELP

?>
