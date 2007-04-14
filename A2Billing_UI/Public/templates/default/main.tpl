<HTML>
<HEAD>
	<link rel="shortcut icon" href="templates/{$SKIN_NAME}/images/favicon.ico">
	<link rel="icon" href="templates/{$SKIN_NAME}/images/animated_favicon1.gif" type="image/gif">
	<title>..:: {$CCMAINTITLE} ::..</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link href="templates/{$SKIN_NAME}/css/main.css" rel="stylesheet" type="text/css">
	<link href="templates/{$SKIN_NAME}/css/menu.css" rel="stylesheet" type="text/css">
	<link href="templates/{$SKIN_NAME}/css/style-def.css" rel="stylesheet" type="text/css">
</HEAD>

<BODY leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">

<p class="version" align="right">{$WEBUI_VERSION} - {$WEBUI_DATE}</p>
<br>

<DIV border="0" width="1000px">
{if ($popupwindow == 0)}
<div class="divleft">
{literal}
<script  language="javascript">
<!--
var mywin
var prevdiv="dummydiv"
function imgidclick_plus(imgID,divID)
{

	var agt=navigator.userAgent.toLowerCase();
    // *** BROWSER VERSION ***
    // Note: On IE5, these return 4, so use is_ie5up to detect IE5.
    var is_major = parseInt(navigator.appVersion);
    var is_minor = parseFloat(navigator.appVersion);

    // Note: Opera and WebTV spoof Navigator.  We do strict client detection.
    // If you want to allow spoofing, take out the tests for opera and webtv.
    var is_nav  = ((agt.indexOf('mozilla')!=-1) && (agt.indexOf('spoofer')==-1)
                && (agt.indexOf('compatible') == -1) && (agt.indexOf('opera')==-1)
                && (agt.indexOf('webtv')==-1) && (agt.indexOf('hotjava')==-1));
	var is_ie     = ((agt.indexOf("msie") != -1) && (agt.indexOf("opera") == -1));
	
	
	if (is_ie){			
		if 	(document.all(divID).style.display == "none" )		
		{		
			document.all(divID).style.display="";			
			document.all(imgID).src="templates/{/literal}{$SKIN_NAME}{literal}/images/minus.gif";			
		}
		else
		{			
			document.all(divID).style.display="None";			
			document.all(imgID).src="templates/{/literal}{$SKIN_NAME}{literal}/images/plus.gif";			
		}
		// Only for I.E
		window.event.cancelBubble=true;
	}else{
		if 	(document.getElementById(divID).style.display == "none" )
		{
			document.getElementById(divID).style.display="";			
			document.getElementById(imgID).src="templates/{/literal}{$SKIN_NAME}{literal}/images/minus.gif";
		}
		else
		{			
			document.getElementById(divID).style.display="None";
			document.getElementById(imgID).src="templates/{/literal}{$SKIN_NAME}{literal}/images/plus.gif";			
		}
	}
}

function imgidclick(imgID,divID, imgbase, imgchange)
{
	
	var agt=navigator.userAgent.toLowerCase();
    // *** BROWSER VERSION ***
    // Note: On IE5, these return 4, so use is_ie5up to detect IE5.
    var is_major = parseInt(navigator.appVersion);
    var is_minor = parseFloat(navigator.appVersion);

    // Note: Opera and WebTV spoof Navigator.  We do strict client detection.
    // If you want to allow spoofing, take out the tests for opera and webtv.
    var is_nav  = ((agt.indexOf('mozilla')!=-1) && (agt.indexOf('spoofer')==-1)
                && (agt.indexOf('compatible') == -1) && (agt.indexOf('opera')==-1)
                && (agt.indexOf('webtv')==-1) && (agt.indexOf('hotjava')==-1));
	var is_ie     = ((agt.indexOf("msie") != -1) && (agt.indexOf("opera") == -1));
	
	
	if (is_ie){			
		if 	(document.all(divID).style.display == "none" )		
		{		
			document.all(divID).style.display="";			
			document.all(imgID).src="templates/{/literal}{$SKIN_NAME}{literal}/images/kicons/"+imgchange;
		}
		else
		{			
			document.all(divID).style.display="None";
			document.all(imgID).src="templates/{/literal}{$SKIN_NAME}{literal}/images/kicons/"+imgchange;
		}
		window.event.cancelBubble=true;
	}else{
		if 	(document.getElementById(divID).style.display == "none" )
		{			
			document.getElementById(divID).style.display="";
			document.getElementById(imgID).src="templates/{/literal}{$SKIN_NAME}{literal}/images/kicons/"+imgchange;
		}
		else
		{			
			document.getElementById(divID).style.display="None";
			document.getElementById(imgID).src="templates/{/literal}{$SKIN_NAME}{literal}/images/kicons/"+imgchange;
		}
	}
}
//-->
</script>
{/literal}
	<div id="dummydiv"></div>


<ul id="nav" >	
	{if ($ACXCUSTOMER > 0) }
	<li>
	<a href="#" target="_self"  onclick="imgidclick_plus('img1','div1');"><img id="img1"  
	{if ($section == "1")}
	
	src="templates/{$SKIN_NAME}/images/minus.gif"
	 
	{else}
	
	src="templates/{$SKIN_NAME}/images/plus.gif"
	
	{/if}
 onmouseover="this.style.cursor='hand';" WIDTH="9" HEIGHT="9">&nbsp; <strong>{php} echo gettext("CUSTOMERS");{/php}</strong></a></li>
	<div id="div1"  
	{if ($section =="1")}
	
	style="">
	{else}
	style="display:none;">
	{/if}
	<ul>
		<li><ul>
				<li><a href="A2B_entity_card.php?atmenu=card&stitle=Customers_Card&section=1">{php} echo gettext("List Customers");{/php}</a></li>
				<li><a href="A2B_entity_card.php?form_action=ask-add&atmenu=card&stitle=Card&section=1">{php} echo gettext("Create Customers");{/php}</a></li>
                <li><a href="CC_card_import.php?atmenu=card&stitle=Card&section=1">{php} echo gettext("Import Customers");{/php}</a></li>
				<li><a href="A2B_entity_card_multi.php?stitle=Card&section=1">{php} echo gettext("Generate Customers");{/php}</a></li>
				<li><a href="A2B_entity_friend.php?atmenu=sipfriend&stitle=SIP+Friends&section=1">{php} echo gettext("List SIP-FRIEND");{/php}</a></li>
				<li><a href="A2B_entity_friend.php?form_action=ask-add&atmenu=sipfriend&stitle=SIP+Friends&section=1">{php} echo gettext("Create SIP-FRIEND");{/php}</a></li>
				<li><a href="A2B_entity_friend.php?atmenu=iaxfriend&stitle=IAX+Friends&section=1">{php} echo gettext("List IAX-FRIEND");{/php}</a></li>
				<li><a href="A2B_entity_friend.php?form_action=ask-add&atmenu=iaxfriend&stitle=IAX+Friends&section=1">{php} echo gettext("Create IAX-FRIEND");{/php}</a></li>
				<li><a href="A2B_entity_callerid.php?atmenu=callerid&stitle=CallerID&section=1">{php} echo gettext("List CallerID");{/php}</a></li>
				<li><a href="A2B_entity_speeddial.php?atmenu=speeddial&stitle=Speed+Dial&section=1">{php} echo gettext("List Speed Dial");{/php}</a></li>
				<li><a href="A2B_entity_speeddial.php?form_action=ask-add&atmenu=speeddial&stitle=Speed+Dial&section=1">{php} echo gettext("Create Speed Dial");{/php}</a></li>
		</ul></li>
	</ul>
	</div>
	{/if}
	{if ($ACXBILLING > 0)}

		<li><a href="#" target="_self"><a href="#" target="_self" onclick="imgidclick_plus('img2','div2');"><img id="img2" 
	{if ($section =="2")}
	
	src="templates/{$SKIN_NAME}/images/minus.gif"
	{else}
	src="templates/{$SKIN_NAME}/images/plus.gif"
	
	{/if}
	 onmouseover="this.style.cursor='hand';" WIDTH="9" HEIGHT="9">&nbsp; <strong>{php} echo gettext("BILLING");{/php}</strong></a></li>
	<div id="div2" 
	{if ($section =="2")}
	
	style="">
	{else}
	style="display:none;">	
	{/if}
		<ul>
			<li><ul>
				<li><a href="A2B_entity_paypal.php?atmenu=paypal&stitle=PaypalTransaction&form_action=list&section=2">{php} echo gettext("OLD-PayPal Transaction");{/php}</a></li>
				<li><a href="A2B_entity_payment_configuration.php?atmenu=payment&section=2">{php} echo gettext("View Payment Methods");{/php}</a></li>
                <li><a href="A2B_entity_transactions.php?atmenu=payment&stitle=Solde&section=2">{php} echo gettext("View Transactions");{/php}</a></li>				
				<li><a href="A2B_entity_moneysituation.php?atmenu=moneysituation&stitle=Money_Situation&section=2">{php} echo gettext("View money situation");{/php}</a></li>
				<li><a href="A2B_entity_payment.php?atmenu=payment&stitle=Solde&section=2">{php} echo gettext("View Payment");{/php}</a></li>
				<li><a href="A2B_entity_payment.php?stitle=Payment_add&form_action=ask-add&section=2">{php} echo gettext("Add new Payment");{/php}</a></li>
				<li><a href="A2B_entity_invoices.php?atmenu=payment&stitle=Solde&section=2&invoicetype=billed">{php} echo gettext("View Billed Invoices");{/php}</a></li>
				<li><a href="A2B_entity_invoices.php?atmenu=payment&stitle=Solde&section=2&invoicetype=unbilled">{php} echo gettext("View UnBilled Invoices");{/php}</a></li>
				<li><a href="A2B_entity_voucher.php?stitle=Voucher&section=2">{php} echo gettext("List Voucher");{/php}</a></li>
				<li><a href="A2B_entity_voucher.php?stitle=Voucher_add&form_action=ask-add&section=2">{php} echo gettext("Create Voucher");{/php}</a></li>
				<li><a href="A2B_entity_voucher_multi.php?stitle=Voucher_Generate&section=2">{php} echo gettext("Generate Vouchers");{/php}</a></li>
				<li><a href="A2B_currencies.php?section=2">{php} echo gettext("Currency List");{/php}</a></li>
				<li><a href="A2B_entity_charge.php?atmenu=charge&stitle=Charge&form_action=list&section=2">{php} echo gettext("List Charge");{/php}</a></li>
				<li><a href="A2B_entity_charge.php?form_action=ask-add&atmenu=charge&stitle=Charge&section=2">{php} echo gettext("Add Charge");{/php}</a></li>
				<li><a href="A2B_entity_ecommerce.php?atmenu=ecommerce&stitle=E-Commerce&section=2">{php} echo gettext("List E-Product");{/php}</a></li>
				<li><a href="A2B_entity_ecommerce.php?form_action=ask-add&atmenu=ecommerce&stitle=E-Commerce&section=2">{php} echo gettext("Add E-Product");{/php}</a></li>
			</ul></li>
		</ul>
		</div>
	{/if}
	{if ($ACXRATECARD > 0)}
		<li><a href="#" target="_self"><a href="#" target="_self" onclick="imgidclick_plus('img3','div3');"><img id="img3" 
	{if ($section =="3")}
	
	src="templates/{$SKIN_NAME}/images/minus.gif"
	{else}
	src="templates/{$SKIN_NAME}/images/plus.gif"
	
	{/if}
	  onmouseover="this.style.cursor='hand';" WIDTH="9" HEIGHT="9"> &nbsp;<strong>{php} echo gettext("RATECARD");{/php}</strong></a></li>
		<div id="div3" 
	{if ($section =="3")}
		style="">
	{else}
		style="display:none;">
	{/if}
		<ul>
			<li><ul>
				<li><a href="A2B_entity_tariffgroup.php?form_action=ask-add&atmenu=tariffgroup&stitle=Tariff+Group&section=3">{php} echo gettext("Create TariffGroup");{/php}</a></li>
				<li><a href="A2B_entity_tariffgroup.php?atmenu=tariffgroup&stitle=TariffGroup&section=3">{php} echo gettext("List TariffGroup");{/php}</a></li>
				<li><a href="A2B_entity_tariffplan.php?atmenu=tariffplan&stitle=Tariffplan&section=3">{php} echo gettext("List RateCard");{/php}</a></li>
				<li><a href="A2B_entity_tariffplan.php?form_action=ask-add&atmenu=tariffplan&stitle=RateCard&section=3">{php} echo gettext("Create new RateCard");{/php}</a></li>
				<li><a href="A2B_entity_def_ratecard.php?atmenu=ratecard&stitle=RateCard&section=3">{php} echo gettext("Browse Rates");{/php}</a></li>
				<li><a href="A2B_entity_def_ratecard.php?form_action=ask-add&atmenu=ratecard&stitle=RateCard&section=3">{php} echo gettext("Add Rate");{/php}</a></li>
				<li><a href="CC_ratecard_import.php?atmenu=ratecard&stitle=RateCard&section=3">{php} echo gettext("Import RateCard");{/php}</a></li>
				<li><a href="CC_entity_sim_ratecard.php?atmenu=ratecard&stitle=Ratecard+Simulator&section=3">{php} echo gettext("Ratecard Simulator");{/php}</a></li>
				<li><a href="A2B_entity_prefix.php?atmenu=prefixe&stitle=Prefix&section=3">{php} echo gettext("Browse Prefix");{/php}</a></li>
				<li><a href="A2B_entity_prefix.php?form_action=ask-add&atmenu=prefixe&stitle=Prefix&section=3">{php} echo gettext("Add Prefix");{/php}</a></li>
			</ul></li>
		</ul>
		</div>
	{/if}	
	{if ($ACXPACKAGEOFFER > 0)}	
		<li><a href="#" target="_self"><a href="#" target="_self" onclick="imgidclick_plus('img3_3','div3_3');"><img id="img3_3" 
	{if ($section =="3_3")}	
	src="templates/{$SKIN_NAME}/images/minus.gif"
	{else}
	src="templates/{$SKIN_NAME}/images/plus.gif"	
	{/if}
	  onmouseover="this.style.cursor='hand';" WIDTH="9" HEIGHT="9"> &nbsp;<strong>{php} echo gettext("PACKAGE OFFER");{/php}</strong></a></li>
		<div id="div3_3" 
	{if ($section =="3_3")}
		style="">
	{else}
		style="display:none;">
	{/if}
		<ul>
			<li><ul>				
				<li><a href="A2B_entity_package.php?atmenu=prefixe&stitle=Prefix&section=3_3">{php} echo gettext("List Offer Package");{/php}</a></li>
				<li><a href="A2B_entity_package.php?form_action=ask-add&atmenu=prefixe&stitle=Prefix&section=3_3">{php} echo gettext("Add Offer Package");{/php}</a></li>
				<li><a href="A2B_detail_package.php?section=3_3">{php} echo gettext("Details Package");{/php}</a></li>
			</ul></li>
		</ul>
		</div>
	{/if}	
	{if ($ACXOUTBOUNDCID > 0)}	
		
		<li><a href="#" target="_self"><a href="#" target="_self" onclick="imgidclick_plus('img3_5','div3_5');"><img id="img3_5" 
	{if ($section =="3_5")}	
	src="templates/{$SKIN_NAME}/images/minus.gif"
	{else}
	src="templates/{$SKIN_NAME}/images/plus.gif"	
	{/if}
	  onmouseover="this.style.cursor='hand';" WIDTH="9" HEIGHT="9"> &nbsp;<strong>{php} echo gettext("OUTBOUND CID");{/php}</strong></a></li>
		<div id="div3_5" 
	{if ($section =="3_5")}
		style="">
	{else}
		style="display:none;">
	{/if}
		<ul>
			<li><ul>
				<li><a href="A2B_entity_outbound_cidgroup.php?form_action=ask-add&atmenu=cidgroup&stitle=CIDGroup&section=3_5">{php} echo gettext("Create CIDGroup");{/php}</a></li>
				<li><a href="A2B_entity_outbound_cidgroup.php?atmenu=cidgroup&stitle=CIDGroup&section=3_5">{php} echo gettext("List CIDGroup");{/php}</a></li>
				<li><a href="A2B_entity_outbound_cid.php?form_action=ask-add&atmenu=cidgroup&stitle=CIDGroup&section=3_5">{php} echo gettext("Add CID");{/php}</a></li>
				<li><a href="A2B_entity_outbound_cid.php?atmenu=cid&stitle=CID&section=3_5">{php} echo gettext("List CID's");{/php}</a></li>
			</ul></li>
		</ul>
		</div>
	{/if}
	
	{if ($ACXTRUNK > 0)}
		<li><a href="#" target="_self"><a href="#" target="_self" onclick="imgidclick_plus('img4','div4');"><img id="img4"  
	{if ($section =="4")}
	
	src="templates/{$SKIN_NAME}/images/minus.gif"
	{else}
	src="templates/{$SKIN_NAME}/images/plus.gif"
	
	{/if} onmouseover="this.style.cursor='hand';" WIDTH="9" HEIGHT="9"> &nbsp;<strong>{php} echo gettext("TRUNK");{/php}</strong></a></li>
		<div id="div4"  
	{if ($section =="4")}
		style="">
	{else}
		style="display:none;">
	{/if}
		<ul>
			<li><ul>
				<li><a href="A2B_entity_trunk.php?stitle=Trunk&section=4">{php} echo gettext("List Trunk");{/php}</a></li>
				<li><a href="A2B_entity_trunk.php?stitle=Trunk&form_action=ask-add&section=4">{php} echo gettext("Add Trunk");{/php}</a></li>
				<li><a href="A2B_entity_provider.php?stitle=Provider&section=4">{php} echo gettext("List Provider");{/php}</a></li>
				<li><a href="A2B_entity_provider.php?stitle=Provider&form_action=ask-add&section=4">{php} echo gettext("Create Provider");{/php}</a></li>
			</ul></li>
		</ul>
		</div>
	{/if}
	{if ($ACXDID > 0)}
		<li><a href="#" target="_self"><a href="#" target="_self" onclick="imgidclick_plus('img41','div41');"><img id="img41"  
	{if ($section == "5")}
	
	src="templates/{$SKIN_NAME}/images/minus.gif"
	{else}
	src="templates/{$SKIN_NAME}/images/plus.gif"
	
	{/if}
	 onmouseover="this.style.cursor='hand';" WIDTH="9" HEIGHT="9"> &nbsp;<strong>{php} echo gettext("DID");{/php}</strong></a></li>
		<div id="div41"  
	{if ($section =="5")}
		style="">
	{else}
		style="display:none;">
	{/if}
		<ul>
			<li><ul>
				<li><a href="A2B_entity_didgroup.php?stitle=DID+Group&section=5">{php} echo gettext("List DID Group");{/php}</a>
				<li><a href="A2B_entity_didgroup.php?stitle=DID+Group&form_action=ask-add&section=5">{php} echo gettext("Add DID Group");{/php}</a></li>
				<li><a href="A2B_entity_did.php?stitle=DID&section=5">{php} echo gettext("List DID");{/php}</a></li>
				<li><a href="A2B_entity_did.php?stitle=DID&form_action=ask-add&section=5">{php} echo gettext("Add DID");{/php}</a></li>
                <li><a href="A2B_entity_did_import.php?stitle=DID&section=5">{php} echo gettext("Import DID");{/php}</a></li>
				<li><a href="A2B_entity_did_destination.php?stitle=DID+Destination&section=5">{php} echo gettext("List Destination");{/php}</a></li>
				<li><a href="A2B_entity_did_destination.php?stitle=DID+Destination&form_action=ask-add&section=5">{php} echo gettext("Add Destination");{/php}</a></li>
				<li><a href="A2B_entity_did_billing.php?atmenu=did_billing&stitle=DID+BILLING&section=5">{php} echo gettext("DID Billing");{/php}</a></li>
				<li><a href="A2B_entity_did_use.php?atmenu=did_use&stitle=DID+USE&section=5">{php} echo gettext("DID Usage");{/php}</a></li>
			</ul></li>
		</ul>
		</div>
	{/if}
	{if ($ACXCALLREPORT > 0)}
		<li><a href="#" target="_self"><a href="#" target="_self" onclick="imgidclick_plus('img5','div5');"><img id="img5" 
	{if ($section == "6")}
	
	src="templates/{$SKIN_NAME}/images/minus.gif"
	{else}
	src="templates/{$SKIN_NAME}/images/plus.gif"
	
	{/if} onmouseover="this.style.cursor='hand';" WIDTH="9" HEIGHT="9"> &nbsp;<strong>{php} echo gettext("CALL REPORT");{/php}</strong></a></li>
		<div id="div5" 
	{if ($section =="6")}
		style="">
	{else}
		style="display:none;">
	{/if}
		<ul>
			<li><ul>
					<li><a href="call-log-customers.php?stitle=Call_Report_Customers&nodisplay=1&posted=1&section=6">{php} echo gettext("CDR Report");{/php}</a></li>
					<li><a href="invoices.php?stitle=Invoice&nodisplay=1&section=6">{php} echo gettext("Invoice");{/php}</a></li>
					<li><a href="call-comp.php?section=6">{php} echo gettext("Calls Compare");{/php}</a></li>
					<li><a href="call-last-month.php?section=6">{php} echo gettext("Monthly Traffic");{/php}</a></li>
					<li><a href="call-daily-load.php?section=6">{php} echo gettext("Daily Load");{/php}</a></li>
					<li><a href="call-count-reporting.php?stitle=Call_Reporting&nodisplay=1&posted=1&section=6">{php} echo gettext("Report");{/php}</a></li>
			</ul></li>
		</ul>
		</div>
	{/if}
	
	{if ($ACXCRONTSERVICE  > 0)}
		<li><a href="#" target="_self"><a href="#" target="_self" onclick="imgidclick_plus('img9','div9');"><img id="img9" 
	{if ($section =="7")}
	
	src="templates/{$SKIN_NAME}/images/minus.gif"
	{else}
	src="templates/{$SKIN_NAME}/images/plus.gif"
	
	{/if} onmouseover="this.style.cursor='hand';" WIDTH="9" HEIGHT="9">&nbsp; <strong>{php} echo gettext("RECURRING SERVICE");{/php}</strong></a></li>
		<div id="div9" 
	{if ($section =="7")}
		style="">
	{else}
		style="display:none;">
	{/if}
		<ul>
			<li><ul>
				<li><a href="A2B_entity_autorefill.php?stitle=Auto+Refill&section=7">{php} echo gettext("AutoRefill Report");{/php}</a></li>
				<li><a href="A2B_entity_service.php?stitle=Recurring+Service&section=7">{php} echo gettext("List Recurring Service");{/php}</a></li>
				<li><a href="A2B_entity_service.php?stitle=Recurring+Service&form_action=ask-add&section=7">{php} echo gettext("Add Recurring Service");{/php}</a></li>
				<li><a href="A2B_entity_alarm.php?stitle=Alarm&section=7"> {php} echo gettext("List Alarm");{/php}</a></li>
				<li><a href="A2B_entity_alarm.php?stitle=Alarm&form_action=ask-add&section=7">{php} echo gettext("Add Alarm");{/php}</a></li>
				<li><a href="A2B_entity_subscription.php?stitle=Subscription&section=7">{php} echo gettext("List Subscription");{/php}</a></li>
				<li><a href="A2B_entity_subscription.php?stitle=Subscription&form_action=ask-add&section=7">{php} echo gettext("Add Subscription");{/php}</a></li>
			</ul></li>
		</ul>
		</div>

	{/if}
	
	
	{if ($ACXCALLBACK  > 0)}
		<li><a href="#" target="_self"><a href="#" target="_self" onclick="imgidclick_plus('img10','div10');"><img id="img10" 
	{if ($section =="12")}
	
	src="templates/{$SKIN_NAME}/images/minus.gif"
	{else}
	src="templates/{$SKIN_NAME}/images/plus.gif"
	
	{/if} onmouseover="this.style.cursor='hand';" WIDTH="9" HEIGHT="9">&nbsp; <strong>{php} echo gettext("CALLBACK");{/php}</strong></a></li>
	<div id="div10" 
	{if ($section =="12")}
		style="">
	{else}
		style="display:none;">
	{/if}
		<ul>
			<li><ul>
				<li><a href="A2B_entity_callback.php?section=12">{php} echo gettext("Show Callbacks");{/php}</a></li>
				<li><a href="A2B_entity_callback.php?form_action=ask-add&section=12">{php} echo gettext("Add new Callback");{/php}</a></li>
				<li><a href="A2B_entity_server_group.php?section=12">{php} echo gettext("Show Server Group");{/php}</a></li>
				<li><a href="A2B_entity_server_group.php?form_action=ask-add&section=12">{php} echo gettext("Add Server Group");{/php}</a></li>
				<li><a href="A2B_entity_server.php?section=12">{php} echo gettext("Show Server");{/php}</a></li>
				<li><a href="A2B_entity_server.php?form_action=ask-add&section=12">{php} echo gettext("Add Server");{/php}</a></li>
			</ul></li>
		</ul>
		</div>

	{/if}
	
	{if ($ACXSIGNUP  > 0)}
		<li><a href="#" target="_self"><a href="#" target="_self" onclick="imgidclick_plus('img6','div6');"><img id="img6" 
	{if ($section =="8")}
	
	src="templates/{$SKIN_NAME}/images/minus.gif"
	{else}
	src="templates/{$SKIN_NAME}/images/plus.gif"
	
	{/if}
	onmouseover="this.style.cursor='hand';" WIDTH="9" HEIGHT="9">&nbsp; <strong>{php} echo gettext("SIGNUP");{/php}</strong></a></li>
	<div id="div6"  
	{if ($section =="8")}
		style="">
	{else}
		style="display:none;">
	{/if}
		<ul>
			<li><ul>
				<li><a href="A2B_entity_mailtemplate.php?atmenu=mailtemplate&stitle=Mail+Tempalte&section=8">{php} echo gettext("Show mail template");{/php}</a></li>
				<li><a href="A2B_entity_mailtemplate.php?form_action=ask-add&atmenu=mailtemplate&stitle=Mail+Tempalte&section=8">{php} echo gettext("Create mail template");{/php}</a></li>
			</ul></li>
		</ul>
		</div>
	{/if}
	
	{if ($ACXADMINISTRATOR  > 0)}
		<li><a href="#" target="_self"><a href="#" target="_self" onclick="imgidclick_plus('img7','div7');"><img id="img7" 
	{if ($section =="10")}
	
	src="templates/{$SKIN_NAME}/images/minus.gif"
	{else}
	src="templates/{$SKIN_NAME}/images/plus.gif"
	
	{/if} onmouseover="this.style.cursor='hand';" WIDTH="9" HEIGHT="9">&nbsp; <strong>{php} echo gettext("ADMINISTRATOR");{/php}</strong></a></li>
		<div id="div7" 
	{if ($section =="10")}
		style="">
	{else}
		style="display:none;">
	{/if}
		<ul>
			<li><ul>
				<li><a href="A2B_entity_user.php?atmenu=user&groupID=0&stitle=Administrator+management&section=10">{php} echo gettext("Show Administrator");{/php}</a></li>
				<li><a href="A2B_entity_user.php?form_action=ask-add&atmenu=user&groupID=0&stitle=Administrator+management&section=10">{php} echo gettext("Add Administrator");{/php}</a></li>
				<li><a href="A2B_entity_user.php?atmenu=user&groupID=1&stitle=ACL+Admin+management&section=10">{php} echo gettext("Show ACL Admin");{/php}</a></li>
				<li><a href="A2B_entity_user.php?form_action=ask-add&atmenu=user&groupID=1&stitle=ACL+Admin+management&section=10">{php} echo gettext("Add ACL Admin");{/php}</a></li>
				<li><a href="A2B_entity_backup.php?form_action=ask-add&section=10">{php} echo gettext("Database Backup");{/php}</a></li>
				<li><a href="A2B_entity_restore.php?section=10">{php} echo gettext("Database Restore");{/php}</a></li>
				<li><a href="A2B_logfile.php?section=10">{php} echo gettext("Watch Log files");{/php}</a></li>
			</ul></li>
		</ul>
		</div>
	{/if}
	
	{if ($ACXFILEMANAGER  > 0)}
		<li><a href="#" target="_self"><a href="#" target="_self" onclick="imgidclick_plus('img8','div8');"><img id="img8" 
	{if ($section == "11")}
	
	src="templates/{$SKIN_NAME}/images/minus.gif"
	{else}
	src="templates/{$SKIN_NAME}/images/plus.gif"
	
	{/if} onmouseover="this.style.cursor='hand';" WIDTH="9" HEIGHT="9">&nbsp; <strong>{php} echo gettext("FILE MANAGER");{/php}</strong></a></li>
		<div id="div8" 
	{if ($section =="11")}
		style="">
	{else}
		style="display:none;">
	{/if}
		<ul>
			<li><ul>
				<li><a href="CC_musiconhold.php?section=11">{php} echo gettext("MusicOnHold");{/php}</a></li>
				<li><a href="CC_upload.php?section=11">{php} echo gettext("Standard File");{/php}</a></li>
			</ul></li>
		</ul>
		</div>
	{/if}

	<li><a href="#" target="_self"></a></a></li>

	<ul>
		<li><ul>
		<li><a href="logout.php?logout=true" target="_top"><font color="#DD0000"><b>&nbsp;&nbsp;{php} echo gettext("LOGOUT");{/php}</b></font></a></li>
		</ul></li>
	</ul>

</ul>
<br>
<table>
<tr>
	<td>
		<a href="PP_intro.php?language=english" target="_parent"><img src="templates/{$SKIN_NAME}/images/flags/gb.gif" border="0" title="English" alt="English"></a>
	</td>
</tr>
<tr>
		<td>
			<form action="{$PAGE_SELF}" method="post">
				<select name="cssname" class="form_input_select" >
					<option value="default" {checkseleted file="default"}>Default</option>
				</select>
				<input type="submit" value="Change" class="form_input_button" >
			</form>
		</td>
	</tr>
</table>


</div>

<div class="divright">

{else}
<div>
{/if}
