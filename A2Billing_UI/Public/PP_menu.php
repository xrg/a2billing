<?php 
	include_once (dirname(__FILE__)."/../lib/defines.php");
	include_once (dirname(__FILE__)."/../lib/module.access.php");

	$section = "";
	if($_GET["section"]!="")
	{
		$section = $_GET["section"];		
	}

?>
<script language="JavaScript">
<!--
var mywin
var prevdiv="dummydiv"
function imgidclick(imgID,divID)
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
			document.all(imgID).src="../Images/minus.gif";			
		}
		else
		{			
			document.all(divID).style.display="None";			
			document.all(imgID).src="../Images/plus.gif";			
		}
		// Only for I.E
		window.event.cancelBubble=true;
	}else{
		if 	(document.getElementById(divID).style.display == "none" )
		{
			document.getElementById(divID).style.display="";			
			document.getElementById(imgID).src="../Images/minus.gif";
		}
		else
		{			
			document.getElementById(divID).style.display="None";
			document.getElementById(imgID).src="../Images/plus.gif";			
		}
	}
}


//-->
</script>
<div id="dummydiv"></div>


<ul id="nav" >
	
	<?php   if ( has_rights (ACX_CUSTOMER) ){ 	?>
	<li><a href="#" target="_self"  onclick="imgidclick('img1','div1');">
	<img id="img1" src="../Images/plus.gif" onmouseover="this.style.cursor='hand';" WIDTH="9" HEIGHT="9"> <strong><?php echo gettext("CUSTOMERS");?></strong></a></li>
	<div id="div1" style="display:none;">
	<ul>
		<li><ul>
				<li><a href="A2B_entity_card.php?atmenu=card&stitle=Customers_Card&section=1"><?php echo gettext("List Customers");?></a></li>
				<li><a href="A2B_entity_card.php?form_action=ask-add&atmenu=card&stitle=Card&section=1"><?php echo gettext("Create Customers");?></a></li>
                <li><a href="CC_card_import.php?atmenu=card&stitle=Card&section=1"><?php echo gettext("Import Customers");?></a></li>
				<li><a href="A2B_entity_card_multi.php?stitle=Card&section=1"><?php echo gettext("Generate Customers");?></a></li>
				<li><a href="A2B_entity_friend.php?atmenu=sipfriend&stitle=SIP+Friends&section=1"><?php echo gettext("List SIP-FRIEND");?></a></li>
				<li><a href="A2B_entity_friend.php?form_action=ask-add&atmenu=sipfriend&stitle=SIP+Friends&section=1"><?php echo gettext("Create SIP-FRIEND");?></a></li>
				<li><a href="A2B_entity_friend.php?atmenu=iaxfriend&stitle=IAX+Friends&section=1"><?php echo gettext("List IAX-FRIEND");?></a></li>
				<li><a href="A2B_entity_friend.php?form_action=ask-add&atmenu=iaxfriend&stitle=IAX+Friends&section=1"><?php echo gettext("Create IAX-FRIEND");?></a></li>
				<li><a href="A2B_entity_callerid.php?atmenu=callerid&stitle=CallerID&section=1"><?php echo gettext("List CallerID");?></a></li>
				<li><a href="A2B_entity_speeddial.php?atmenu=speeddial&stitle=Speed+Dial&section=1"><?php echo gettext("List Speed Dial");?></a></li>
				<li><a href="A2B_entity_speeddial.php?form_action=ask-add&atmenu=speeddial&stitle=Speed+Dial&section=1"><?php echo gettext("Create Speed Dial");?></a></li>
		</ul></li>
	</ul>
	</div>
	<?php   }  ?>
	<?php   if ( has_rights (ACX_AGENTS) ){ 	?>
	<li><a href="#" target="_self"  onclick="imgidclick('img12','div12');">
	<img id="img1a" src="../Images/plus.gif" onmouseover="this.style.cursor='hand';" WIDTH="9" HEIGHT="9"> <strong><?= gettext("AGENTS");?></strong></a></li>
	<div id="div12" style="display:none;">
	<ul>
		<li><ul>
			<li><a href="A2B_entity_agent.php?atmenu=card&stitle=Customers_Card&section=12"><?= _("List Agents");?></a></li>
			<li><a href="A2B_entity_agent.php?form_action=ask-add&atmenu=card&stitle=Card&section=12"><?= _("Create Agents");?></a></li>
			<li><a href="A2B_entity_regulars.php?atmenu=regular&stitle=Regular_Customers&section=12"><?= _("List Regulars");?></a></li>
			<li><a href="A2B_entity_card_multia.php?stitle=Card&section=12"><?= gettext("Generate Regulars");?></a></li>
			<li><a href="A2B_entity_booths.php?stitle=Booths&section=12"><?= gettext("Booths");?></a></li>
			<li><a href="A2B_entity_booths.php?form_action=ask-add&atmenu=booth&stitle=Booth&section=12"><?= _("Create Booth");?></a></li>
			<li><a href="A2B_entity_agentpay.php?atmenu=payment&stitle=Payment&form_action=list&section=12"><?= gettext("List Payments");?></a></li>
			<li><a href="A2B_entity_agentpay.php?form_action=ask-add&atmenu=payment&stitle=Payment&section=12"><?= gettext("Add Payment");?></a></li>
			<li><a href="CC_entity_sim_callshop.php?atmenu=ratecard&stitle=Callshop+Simulator&section=12"><?= gettext("Callshop Simulator");?></a></li>
		</ul></li>
	</ul>
	</div>
	<?php   }  ?>
	<?php   if ( has_rights (ACX_BILLING) ){ 	?>
	<li><a href="#" target="_self"><a href="#" target="_self" onclick="imgidclick('img2','div2');"><img id="img2" src="../Images/plus.gif" onmouseover="this.style.cursor='hand';" WIDTH="9" HEIGHT="9"> <strong><?php echo gettext("BILLING");?></strong></a></li>
		<div id="div2" style="display:none;">
		<ul>
			<li><ul>
				<li><a href="A2B_entity_paypal.php?atmenu=paypal&stitle=Paypal+Transaction&form_action=list&section=2"><?php echo gettext("PayPal Transaction");?></a></li>
				<li><a href="A2B_entity_moneysituation.php?atmenu=moneysituation&stitle=Money_Situation&section=2"><?php echo gettext("View money situation");?></a></li>
				<li><a href="A2B_entity_payment.php?atmenu=payment&stitle=Solde&section=2"><?php echo gettext("View Payment");?></a></li>
				<li><a href="A2B_entity_payment.php?stitle=Payment_add&form_action=ask-add&section=2"><?php echo gettext("Add new Payment");?></a></li>
				<li><a href="A2B_entity_voucher.php?stitle=Voucher&section=2"><?php echo gettext("List Voucher");?></a></li>
				<li><a href="A2B_entity_voucher.php?stitle=Voucher_add&form_action=ask-add&section=2"><?php echo gettext("Create Voucher");?></a></li>
				<li><a href="A2B_entity_voucher_multi.php?stitle=Voucher_Generate&section=2"><?php echo gettext("Generate Vouchers");?></a></li>
				<li><a href="A2B_currencies.php?section=2"><?php echo gettext("Currency Table");?></a></li>
				<li><a href="A2B_entity_charge.php?atmenu=charge&stitle=Charge&form_action=list&section=2"><?php echo gettext("List Charge");?></a></li>
				<li><a href="A2B_entity_charge.php?form_action=ask-add&atmenu=charge&stitle=Charge&section=2"><?php echo gettext("Add Charge");?></a></li>
				<li><a href="A2B_entity_ecommerce.php?atmenu=ecommerce&stitle=E-Commerce&section=2"><?php echo gettext("List E-Product");?></a></li>
				<li><a href="A2B_entity_ecommerce.php?form_action=ask-add&atmenu=ecommerce&stitle=E-Commerce&section=2"><?php echo gettext("Add E-Product");?></a></li>
			</ul></li>
		</ul>
		</div>
	<?php   }  ?>
	<?php   if ( has_rights (ACX_RATECARD) ){ 	?>
		<li><a href="#" target="_self"><a href="#" target="_self" onclick="imgidclick('img3','div3');"><img id="img3" src="../Images/plus.gif" onmouseover="this.style.cursor='hand';" WIDTH="9" HEIGHT="9"> <strong><?php echo gettext("RATECARD");?></strong></a></li>
		<div id="div3" style="display:none;">
		<ul>
			<li><ul>
				<li><a href="A2B_entity_tariffgroup.php?form_action=ask-add&atmenu=tariffgroup&stitle=Tariff+Group&section=3"><?php echo gettext("Create TariffGroup");?></a></li>
				<li><a href="A2B_entity_tariffgroup.php?atmenu=tariffgroup&stitle=TariffGroup&section=3"><?php echo gettext("List TariffGroup");?></a></li>
				<li><a href="A2B_entity_tariffplan.php?atmenu=tariffplan&stitle=Tariffplan&section=3"><?php echo gettext("List RateCard");?></a></li>
				<li><a href="A2B_entity_tariffplan.php?form_action=ask-add&atmenu=tariffplan&stitle=RateCard&section=3"><?php echo gettext("Create new RateCard");?></a></li>
				<li><a href="A2B_entity_def_ratecard.php?atmenu=ratecard&stitle=RateCard&section=3"><?php echo gettext("Browse Rates");?></a></li>
				<li><a href="A2B_entity_def_ratecard.php?form_action=ask-add&atmenu=ratecard&stitle=RateCard&section=3"><?php echo gettext("Add Rate");?></a></li>
				<li><a href="CC_ratecard_import.php?atmenu=ratecard&stitle=RateCard&section=3"><?php echo gettext("Import RateCard");?></a></li>
				<li><a href="CC_entity_sim_ratecard.php?atmenu=ratecard&stitle=Ratecard+Simulator&section=3"><?php echo gettext("Ratecard Simulator");?></a></li>
				<li><a href="A2B_entity_prefix.php?section=3"><?php echo gettext("Browse Prefix");?></a></li>
			</ul></li>
		</ul>
		</div>
	<?php   }  ?>
	<?php   if ( has_rights (ACX_TRUNK) ){ 	?>
		<li><a href="#" target="_self"><a href="#" target="_self" onclick="imgidclick('img4','div4');"><img id="img4" src="../Images/plus.gif" onmouseover="this.style.cursor='hand';" WIDTH="9" HEIGHT="9"> <strong><?php echo gettext("TRUNK");?></strong></a></li>
		<div id="div4" style="display:none;">
		<ul>
			<li><ul>
				<li><a href="A2B_entity_trunk.php?stitle=Trunk&section=4"><?php echo gettext("List Trunk");?></a></li>
				<li><a href="A2B_entity_trunk.php?stitle=Trunk&form_action=ask-add&section=4"><?php echo gettext("Add Trunk");?></a></li>
				<li><a href="A2B_entity_provider.php?stitle=Provider&section=4"><?php echo gettext("List Provider");?></a></li>
				<li><a href="A2B_entity_provider.php?stitle=Provider&form_action=ask-add&section=4"><?php echo gettext("Create Provider");?></a></li>
			</ul></li>
		</ul>
		</div>
	<?php  } ?>
	<?php   if ( has_rights (ACX_DID) ){ 	?>
		<li><a href="#" target="_self"><a href="#" target="_self" onclick="imgidclick('img41','div41');"><img id="img41" src="../Images/plus.gif" onmouseover="this.style.cursor='hand';" WIDTH="9" HEIGHT="9"> <strong><?php echo gettext("DID");?></strong></a></li>
		<div id="div41" style="display:none;">
		<ul>
			<li><ul>
				<li><a href="A2B_entity_didgroup.php?stitle=DID+Group&section=41"><?php echo gettext("List DID Group");?></a>
				<li><a href="A2B_entity_didgroup.php?stitle=DID+Group&form_action=ask-add&section=41"><?php echo gettext("Add DID Group");?></a></li>
				<li><a href="A2B_entity_did.php?stitle=DID&section=41"><?php echo gettext("List DID");?></a></li>
				<li><a href="A2B_entity_did.php?stitle=DID&form_action=ask-add&section=41"><?php echo gettext("Add DID");?></a></li>
                <li><a href="A2B_entity_did_import.php?stitle=DID&section=41"><?php echo gettext("Import DID");?></a></li>
				<li><a href="A2B_entity_did_destination.php?stitle=DID+Destination&section=41"><?php echo gettext("List Destination");?></a></li>
				<li><a href="A2B_entity_did_destination.php?stitle=DID+Destination&form_action=ask-add&section=41"><?php echo gettext("Add Destination");?></a></li>
				<li><a href="A2B_entity_did_billing.php?atmenu=did_billing&stitle=DID+BILLING&section=41"><?php echo gettext("DID BILLING");?></a></li>
				<li><a href="A2B_entity_did_use.php?atmenu=did_use&stitle=DID+USE&section=41"><?php echo gettext("DID use");?></a></li>
			</ul></li>
		</ul>
		</div>
	<?php  } ?>
	<?php   if ( has_rights (ACX_CALL_REPORT) ){ 	?>
		<li><a href="#" target="_self"><a href="#" target="_self" onclick="imgidclick('img5','div5');"><img id="img5" src="../Images/plus.gif" onmouseover="this.style.cursor='hand';" WIDTH="9" HEIGHT="9"> <strong><?php echo gettext("CALL REPORT");?></strong></a></li>
		<div id="div5" style="display:none;">
		<ul>
			<li><ul>
					<li><a href="call-log-customers.php?stitle=Call_Report_Customers&nodisplay=1&posted=1&section=5"><?php echo gettext("CDR Report");?></a></li>
					<li><a href="invoices.php?stitle=Invoice&nodisplay=1&section=5"><?php echo gettext("Invoice");?></a></li>
					<li><a href="asterisk-stat-v2/call-comp.php?section=5"><?php echo gettext("Calls Compare");?></a></li>
					<li><a href="asterisk-stat-v2/call-last-month.php?section=5"><?php echo gettext("Monthly Traffic");?></a></li>
					<li><a href="asterisk-stat-v2/call-daily-load.php?section=5"><?php echo gettext("Daily Load");?></a></li>
					<li><a href="call-count-reporting.php?stitle=Call_Reporting&nodisplay=1&posted=1&section=5"><?php echo gettext("Report");?></a></li>
			</ul></li>
		</ul>
		</div>
	<?php  } ?>
	<?php   if ( has_rights (ACX_CRONT_SERVICE) ){ 	?>
		<li><a href="#" target="_self"><a href="#" target="_self" onclick="imgidclick('img7','div7');"><img id="img7"  src="../Images/plus.gif" onmouseover="this.style.cursor='hand';" WIDTH="9" HEIGHT="9"> <strong><?php echo gettext("CRONT SERVICE");?></strong></a></li>
		<div id="div7" style="display:none;" >
		<ul>
			<li><ul>
				<li><a href="A2B_entity_autorefill.php?stitle=Auto+Refill&section=7"><?php echo gettext("AutoRefill Report");?></a></li>
				<li><a href="A2B_entity_service.php?stitle=Recurring+Service&section=7"><?php echo gettext("List Recurring Service");?></a></li>
				<li><a href="A2B_entity_service.php?stitle=Recurring+Service&form_action=ask-add&section=7"><?php echo gettext("Add Recurring Service");?></a></li>
			</ul></li>
		</ul>
		</div>

	<?php  } ?>
	<?php   if ( has_rights (ACX_SIGNUP) ){ 	?>
		<li><a href="#" target="_self"><a href="#" target="_self" onclick="imgidclick('img8','div8');"><img id="img8" src="../Images/plus.gif" onmouseover="this.style.cursor='hand';" WIDTH="9" HEIGHT="9"> <strong><?php echo gettext("SIGNUP");?></strong></a></li>
		<div id="div8" style="display:none;">
		<ul>
			<li><ul>
				<li><a href="A2B_entity_mailtemplate.php?atmenu=mailtemplate&stitle=Mail+Tempalte&section=8"><?php echo gettext("Show mail template");?></a></li>
				<li><a href="A2B_entity_mailtemplate.php?form_action=ask-add&atmenu=mailtemplate&stitle=Mail+Tempalte&section=8"><?php echo gettext("Create mail template");?></a></li>
			</ul></li>
		</ul>
		</div>
	<?php  } ?>
	<?php   if ( has_rights (ACX_DID) ){ 	?>
		<li><a href="#" target="_self"><a href="#" target="_self" onclick="imgidclick('img9','div9');"><img id="img9"  src="../Images/plus.gif" onmouseover="this.style.cursor='hand';" WIDTH="9" HEIGHT="9"> <strong><?php echo gettext("PREDICT-DIALER");?></strong></a></li>
		<div id="div9" style="display:none;">
		<ul>
			<li><ul>
				<li><a href="A2B_entity_campaign.php?atmenu=campaign&stitle=Campaign&section=9"><?php echo gettext("List Campaign");?></a></li>
				<li><a href="A2B_entity_campaign.php?form_action=ask-add&atmenu=campaign&stitle=Campaign&section=9"><?php echo gettext("Create Campaign");?></a></li>
				<li><a href="A2B_entity_phonelist.php?atmenu=phonelist&stitle=Phonelist&section=9"><?php echo gettext("Show Phonelist");?></a></li>
				<li><a href="A2B_entity_phonelist.php?atmenu=phonelist&stitle=Phonelist&form_action=ask-add&section=9"><?php echo gettext("Add PhoneNumber");?></a></li>
				<li><a href="CC_phonelist_import.php?atmenu=phonelist&stitle=Phonelist+Import&section=9"><?php echo gettext("Import Phonelist");?></a></li>
			</ul></li>
		</ul>
		</div>
	<?php  } ?>
	<?php   if ( has_rights (ACX_ADMINISTRATOR) ){ 	?>
		<li><a href="#" target="_self"><a href="#" target="_self" onclick="imgidclick('img10','div10');"><img id="img10" src="../Images/plus.gif" onmouseover="this.style.cursor='hand';" WIDTH="9" HEIGHT="9"> <strong><?php echo gettext("ADMINISTRATOR");?></strong></a></li>
		<div id="div10" style="display:none;">
		<ul>
			<li><ul>
				<li><a href="A2B_entity_user.php?atmenu=user&groupID=0&stitle=Administrator+management&section=10"><?php echo gettext("Show Administrator");?></a></li>
				<li><a href="A2B_entity_user.php?form_action=ask-add&atmenu=user&groupID=0&stitle=Administrator+management&section=10"><?php echo gettext("Add Administrator");?></a></li>
				<li><a href="A2B_entity_user.php?atmenu=user&groupID=1&stitle=ACL+Admin+management&section=10"><?php echo gettext("Show ACL Admin");?></a></li>
				<li><a href="A2B_entity_user.php?form_action=ask-add&atmenu=user&groupID=1&stitle=ACL+Admin+management&section=10"><?php echo gettext("Add ACL Admin");?></a></li>
				<li><a href="A2B_entity_backup.php?form_action=ask-add&section=10"><?php echo gettext("Database Backup");?></a></li>
				<li><a href="A2B_entity_restore.php?section=10"><?php echo gettext("Database Restore");?></a></li>
				<li><a href="A2B_entity_texts.php"><?= _("Localized texts");?></a></li>
			</ul></li>
		</ul>
		</div>
	<?php  } ?>	
	<?php   if ( has_rights (ACX_FILE_MANAGER) ){ 	?>
		<li><a href="#" target="_self"><a href="#" target="_self" onclick="imgidclick('img11','div11');"><img id="img11" src="../Images/plus.gif" onmouseover="this.style.cursor='hand';" WIDTH="9" HEIGHT="9"> <strong><?php echo gettext("FILE MANAGER");?></strong></a></li>
		<div id="div11" style="display:none;">
		<ul>
			<li><ul>
				<li><a href="CC_musiconhold.php?section=11"><?php echo gettext("MusicOnHold");?></a></li>
				<li><a href="CC_upload.php?section=11"><?php echo gettext("Standard File");?></a></li>
			</ul></li>
		</ul>
		</div>
	<?php  } ?>

	<li><a href=# target=_self></a></li>

	<ul>
		<li><ul>
		<li><a href="logout.php?logout=true" target="_top"><font color="#DD0000"><b>&nbsp;&nbsp;<?= gettext("LOGOUT");?></b></font></a></li>
		</ul></li>
	</ul>

</ul>
<br>
<table>
<tr>
	<td>
		<a href="index2.php?language=english" target="_parent"><img src="../Images/flags/us.gif" border="0" title="English" alt="English"></a>
	</td>
</tr>
</table>
<script>
imgidclick( <?= '\'img'.$section.'\', \'div'.$section.'\'' ?> );
</script>
