<?php 
	include_once (dirname(__FILE__)."/../lib/defines.php");
	include_once (dirname(__FILE__)."/../lib/module.access.php");

	//$section = "";  No, it's specified inside the container entity
// 	if($_GET["section"]!="")
// 	{
// 		$section = $_GET["section"];		
// 	}

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
	<img id="img1" src="../Images/plus.gif" onmouseover="this.style.cursor='hand';" WIDTH="9" HEIGHT="9"> <strong><?= _("CUSTOMERS");?></strong></a></li>
	<div id="div1" style="display:none;">
	<ul>
		<li><a href="A2B_entity_card.php"><?= _("List Customers");?></a></li>
		<li><a href="A2B_entity_card.php?form_action=ask-add"><?= _("Create Customers");?></a></li>
                <li><a href="CC_card_import.php"><?= _("Import Customers");?></a></li>
		<li><a href="A2B_entity_card_multi.php"><?= _("Generate Customers");?></a></li>
		<li><a href="A2B_entity_friend.php"><?= _("List SIP-FRIEND");?></a></li>
		<li><a href="A2B_entity_friend.php?form_action=ask-add"><?= _("Create SIP-FRIEND");?></a></li>
		<li><a href="A2B_entity_friend.php"><?= _("List IAX-FRIEND");?></a></li>
		<li><a href="A2B_entity_friend.php?form_action=ask-add"><?= _("Create IAX-FRIEND");?></a></li>
		<li><a href="A2B_entity_callerid.php"><?= _("List CallerID");?></a></li>
		<li><a href="A2B_entity_speeddial.php"><?= _("List Speed Dial");?></a></li>
		<li><a href="A2B_entity_speeddial.php?form_action=ask-add"><?= _("Create Speed Dial");?></a></li>
	</ul>
	</div>
	<?php   }  ?>
	<?php   if ( has_rights (ACX_AGENTS) ){ 	?>
	<li><a href="#" target="_self"  onclick="imgidclick('img2','div2');">
	<img id="img2" src="../Images/plus.gif" onmouseover="this.style.cursor='hand';" WIDTH="9" HEIGHT="9"> <strong><?= gettext("AGENTS");?></strong></a></li>
	<div id="div2" style="display:none;">
	<ul>
		<li><a href="A2B_entity_agent.php"><?= _("List Agents");?></a></li>
		<li><a href="A2B_entity_agent.php?form_action=ask-add"><?= _("Create Agents");?></a></li>
		<li><a href="A2B_entity_regulars.php"><?= _("List Regulars");?></a></li>
		<li><a href="A2B_entity_card_multia.php"><?= _("Generate Regulars");?></a></li>
		<li><a href="A2B_entity_booths.php"><?= _("Booths");?></a></li>
		<li><a href="A2B_entity_booths.php?form_action=ask-add"><?= _("Create Booth");?></a></li>
		<li><a href="A2B_entity_agentpay.php?form_action=list"><?= _("List Payments");?></a></li>
		<li><a href="A2B_entity_agentpay.php?form_action=ask-add"><?= _("Add Payment");?></a></li>
		<li><a href="CC_entity_sim_callshop.php"><?= _("Callshop Simulator");?></a></li>
	</ul>
	</div>
	<?php   }  ?>
	<?php   if ( has_rights (ACX_BILLING) ){ 	?>
	<li><a href="#" target="_self"><a href="#" target="_self" onclick="imgidclick('img3','div3');"><img id="img3" src="../Images/plus.gif" onmouseover="this.style.cursor='hand';" WIDTH="9" HEIGHT="9"> <strong><?= _("BILLING");?></strong></a></li>
	<div id="div3" style="display:none;">
	<ul>
		<li><a href="A2B_entity_paypal.php?form_action=list"><?= _("OLD PayPal Transaction");?></a></li>
		<li><a href="A2B_entity_payment_configuration.php"><?= _("View Payment Methods") ?></a></li>
                <li><a href="A2B_entity_transactions.php"><?= _("View Transactions"); ?></a></li>
		<li><a href="A2B_entity_moneysituation.php"><?= _("View money situation");?></a></li>
		<li><a href="A2B_entity_payment.php"><?= _("View Payment");?></a></li>
		<li><a href="A2B_entity_payment.php?form_action=ask-add"><?= _("Add new Payment");?></a></li>
		<li><a href="A2B_entity_voucher.php"><?= _("List Voucher");?></a></li>
		<li><a href="A2B_entity_voucher.php?form_action=ask-add"><?= _("Create Voucher");?></a></li>
		<li><a href="A2B_entity_voucher_multi.php"><?= _("Generate Vouchers");?></a></li>
		<li><a href="A2B_currencies.php"><?= _("Currency Table");?></a></li>
		<li><a href="A2B_entity_charge.php?form_action=list"><?= _("List Charge");?></a></li>
		<li><a href="A2B_entity_charge.php?form_action=ask-add"><?= _("Add Charge");?></a></li>
		<li><a href="A2B_entity_ecommerce.php"><?= _("List E-Product");?></a></li>
		<li><a href="A2B_entity_ecommerce.php?form_action=ask-add"><?= _("Add E-Product");?></a></li>
	</ul>
	</div>
	<?php   }  ?>
	<?php   if ( has_rights (ACX_RATECARD) ){ 	?>
		<li><a href="#" target="_self"><a href="#" target="_self" onclick="imgidclick('img4','div4');"><img id="img4" src="../Images/plus.gif" onmouseover="this.style.cursor='hand';" WIDTH="9" HEIGHT="9"> <strong><?= _("RATECARD");?></strong></a></li>
		<div id="div4" style="display:none;">
		<ul>
			<li><a href="A2B_entity_tariffgroup.php?form_action=ask-add"><?= _("Create TariffGroup");?></a></li>
			<li><a href="A2B_entity_tariffgroup.php?"><?= _("List TariffGroup");?></a></li>
			<li><a href="A2B_entity_tariffplan.php"><?= _("List RateCard");?></a></li>
			<li><a href="A2B_entity_tariffplan.php?form_action=ask-add"><?= _("Create new RateCard");?></a></li>
			<li><a href="A2B_entity_def_ratecard.php"><?= _("Browse Rates");?></a></li>
			<li><a href="A2B_entity_def_ratecard.php?form_action=ask-add"><?= _("Add Rate");?></a></li>
			<li><a href="CC_ratecard_import.php"><?= _("Import RateCard");?></a></li>
			<li><a href="CC_entity_sim_ratecard.php"><?= _("Ratecard Simulator");?></a></li>
		</ul>
		</div>
	<?php   }  ?>
	<?php   if ( has_rights (ACX_PACKAGEOFFER) ){ 	?>
		<li><a href="#" target="_self"><a href="#" target="_self" onclick="imgidclick('img5','div5');"><img id="img5" src="../Images/plus.gif" onmouseover="this.style.cursor='hand';" WIDTH="9" HEIGHT="9"> <strong><?= _("PACKAGE OFFER");?></strong></a></li>
		<div id="div5" style="display:none;">
		<ul>
			<li><a href="A2B_entity_package.php"><?= _("List Offer Package");?></a></li>
			<li><a href="A2B_entity_package.php?form_action=ask-add"><?= _("Add Offer Package");?></a></li>
			<li><a href="A2B_detail_package.php"><?= _("Details Package");?></a></li>
		</ul>
		</div>
	<?php   }  ?>
	<?php   if ( has_rights (ACX_OUTBOUNDCID) ){ 	?>
		<li><a href="#" target="_self"><a href="#" target="_self" onclick="imgidclick('img6','div6');"><img id="img6" src="../Images/plus.gif" onmouseover="this.style.cursor='hand';" WIDTH="9" HEIGHT="9"> <strong><?= _("OUTBOUND CID");?></strong></a></li>
		<div id="div6" style="display:none;">
		<ul>
			<li><a href="A2B_entity_outbound_cidgroup.php?form_action=ask-add"><?= _("Create CIDGroup");?></a></li>
			<li><a href="A2B_entity_outbound_cidgroup.php"><?= _("List CIDGroup");?></a></li>
			<li><a href="A2B_entity_outbound_cid.php?form_action=ask-add"><?= _("Add CID");?></a></li>
			<li><a href="A2B_entity_outbound_cid.php"><?= _("List CID's");?></a></li>
		</ul>
		</div>
	<?php   }  ?>
	<?php   if ( has_rights (ACX_TRUNK) ){ 	?>
		<li><a href="#" target="_self"><a href="#" target="_self" onclick="imgidclick('img7','div7');"><img id="img7" src="../Images/plus.gif" onmouseover="this.style.cursor='hand';" WIDTH="9" HEIGHT="9"> <strong><?= _("TRUNK");?></strong></a></li>
		<div id="div7" style="display:none;">
		<ul>
			<li><a href="A2B_entity_trunk.php"><?= _("List Trunk");?></a></li>
			<li><a href="A2B_entity_trunk.php?form_action=ask-add"><?= _("Add Trunk");?></a></li>
			<li><a href="A2B_entity_provider.php"><?= _("List Provider");?></a></li>
			<li><a href="A2B_entity_provider.php?form_action=ask-add"><?= _("Create Provider");?></a></li>
		</ul>
		</div>
	<?php  } ?>
	<?php   if ( has_rights (ACX_DID) ){ 	?>
		<li><a href="#" target="_self"><a href="#" target="_self" onclick="imgidclick('img8','div8');"><img id="img8" src="../Images/plus.gif" onmouseover="this.style.cursor='hand';" WIDTH="9" HEIGHT="9"> <strong><?= _("DID");?></strong></a></li>
		<div id="div8" style="display:none;">
		<ul>
			<li><a href="A2B_entity_didgroup.php?"><?= _("List DID Group");?></a>
			<li><a href="A2B_entity_didgroup.php?form_action=ask-add"><?= _("Add DID Group");?></a></li>
			<li><a href="A2B_entity_did.php?"><?= _("List DID");?></a></li>
			<li><a href="A2B_entity_did.php?form_action=ask-add"><?= _("Add DID");?></a></li>
			<li><a href="A2B_entity_did_import.php?"><?= _("Import DID");?></a></li>
			<li><a href="A2B_entity_did_destination.php"><?= _("List Destination");?></a></li>
			<li><a href="A2B_entity_did_destination.php?form_action=ask-add"><?= _("Add Destination");?></a></li>
			<li><a href="A2B_entity_did_billing.php"><?= _("DID BILLING");?></a></li>
			<li><a href="A2B_entity_did_use.php"><?= _("DID use");?></a></li>
		</ul>
		</div>
	<?php  } ?>
	<?php   if ( has_rights (ACX_CALL_REPORT) ){ 	?>
		<li><a href="#" target="_self"><a href="#" target="_self" onclick="imgidclick('img9','div9');"><img id="img9" src="../Images/plus.gif" onmouseover="this.style.cursor='hand';" WIDTH="9" HEIGHT="9"> <strong><?= _("CALL REPORT");?></strong></a></li>
		<div id="div9" style="display:none;">
		<ul>
			<li><a href="call-log-customers.php?nodisplay=1&posted=1"><?= _("CDR Report");?></a></li>
			<li><a href="call-comp.php?"><?= _("Calls Compare");?></a></li>
			<li><a href="call-last-month.php?"><?= _("Monthly Traffic");?></a></li>
			<li><a href="call-daily-load.php?"><?= _("Daily Load");?></a></li>
			<li><a href="call-count-reporting.php?nodisplay=1&posted=1&"><?= _("Report");?></a></li>
		</ul>
		</div>
	<?php  } ?>
	<?php   if ( has_rights (ACX_INVOICING) ){ 	?>
		<li><a href="#" target="_self"><a href="#" target="_self" onclick="imgidclick('img10','div10');"><img id="img10" src="../Images/plus.gif" onmouseover="this.style.cursor='hand';" WIDTH="9" HEIGHT="9"> <strong><?= _("CALL REPORT");?></strong></a></li>
		<div id="div10" style="display:none;">
		<ul>
			<li><a href="A2B_entity_view_invoice.php"><?= _("View Invoices");?></a></li>
			<li><a href="A2B_entity_create_invoice.php"><?= _("Create Invoices");?></a></li>
			<li><a href="invoices.php?nodisplay=1"><?= _("Invoice");?></a></li>
			<li><a href="invoices_customer.php?nodisplay=1"><?= _("Invoices Customer");?></a></li>
			<li><a href="A2B_entity_invoices.php?invoicetype=billed"><?= _("View Billed Invoices");?></a></li>
			<li><a href="A2B_entity_invoices.php?invoicetype=unbilled"><?= _("View UnBilled Invoices");?></a></li>				
		</ul>
		</div>
	<?php  } ?>
	<?php   if ( has_rights (ACX_CRONT_SERVICE) ){ 	?>
		<li><a href="#" target="_self"><a href="#" target="_self" onclick="imgidclick('img11','div11');"><img id="img11" src="../Images/plus.gif" onmouseover="this.style.cursor='hand';" WIDTH="9" HEIGHT="9"> <strong><?= _("CRONT SERVICE");?></strong></a></li>
		<div id="div11" style="display:none;" >
		<ul>
			<li><a href="A2B_entity_autorefill.php"><?= _("AutoRefill Report");?></a></li>
			<li><a href="A2B_entity_service.php"><?= _("List Recurring Service");?></a></li>
			<li><a href="A2B_entity_service.php?form_action=ask-add"><?= _("Add Recurring Service");?></a></li>
			<li><a href="A2B_entity_alarm.php"><?= _("List Alarm");?></a></li>
			<li><a href="A2B_entity_alarm.php?form_action=ask-add"><?= _("Add Alarm");?></a></li>
			<li><a href="A2B_entity_subscription.php"><?= _("List Subscription");?></a></li>
			<li><a href="A2B_entity_subscription.php?form_action=ask-add"><?= _("Add Subscription");?></a></li>
		</ul>
		</div>

	<?php  } ?>
	<?php   if ( has_rights (ACX_CALLBACK) ){ 	?>
		<li><a href="#" target="_self"><a href="#" target="_self" onclick="imgidclick('img12','div12');"><img id="img12" src="../Images/plus.gif" onmouseover="this.style.cursor='hand';" WIDTH="9" HEIGHT="9"> <strong><?= _("SIGNUP");?></strong></a></li>
		<div id="div12" style="display:none;">
		<ul>
			<li><a href="A2B_entity_callback.php"><?= _("Show Callbacks");?></a></li>
			<li><a href="A2B_entity_callback.php?form_action=ask-add"><?= _("Add new Callback");?></a></li>
			<li><a href="A2B_entity_server_group.php"><?= _("Show Server Group");?></a></li>
			<li><a href="A2B_entity_server_group.php?form_action=ask-add"><?= _("Add Server Group");?></a></li>
			<li><a href="A2B_entity_server.php"><?= _("Show Server");?></a></li>
			<li><a href="A2B_entity_server.php?form_action=ask-add"><?= _("Add Server");?></a></li>
		</ul>
		</div>
	<?php  } ?>

	
	
	<?php   if ( has_rights (ACX_MISC) ){ 	?>
		<li><a href="#" target="_self"><a href="#" target="_self" onclick="imgidclick('img13','div13');"><img id="img13" src="../Images/plus.gif" onmouseover="this.style.cursor='hand';" WIDTH="9" HEIGHT="9"> <strong><?= _("SIGNUP");?></strong></a></li>
		<div id="div13" style="display:none;">
		<ul>
			<li><a href="A2B_entity_mailtemplate.php"><?= _("Show mail template");?></a></li>
			<li><a href="A2B_entity_mailtemplate.php?form_action=ask-add"><?= _("Create mail template");?></a></li>
			<li><a href="A2B_entity_prefix.php"><?= _("Browse Prefix");?></a></li>
			<li><a href="A2B_entity_prefix.php?form_action=ask-add"><?= _("Add Prefix");?></a></li>
		</ul>
		</div>
	<?php  } ?>
	<?php   if ( has_rights (ACX_ADMINISTRATOR) ){ 	?>
		<li><a href="#" target="_self"><a href="#" target="_self" onclick="imgidclick('img20','div20');"><img id="img20" src="../Images/plus.gif" onmouseover="this.style.cursor='hand';" WIDTH="9" HEIGHT="9"> <strong><?= _("ADMINISTRATOR");?></strong></a></li>
		<div id="div20" style="display:none;">
		<ul>
			<li><a href="A2B_entity_user.php?groupID=0"><?= _("Show Administrator");?></a></li>
			<li><a href="A2B_entity_user.php?form_action=ask-add&groupID=0"><?= _("Add Administrator");?></a></li>
			<li><a href="A2B_entity_user.php?groupID=1"><?= _("Show ACL Admin");?></a></li>
			<li><a href="A2B_entity_user.php?form_action=ask-add&groupID=1"><?= _("Add ACL Admin");?></a></li>
			<li><a href="A2B_entity_backup.php?form_action=ask-add"><?= _("Database Backup");?></a></li>
			<li><a href="A2B_entity_restore.php"><?= _("Database Restore");?></a></li>
			<li><a href="A2B_entity_texts.php"><?= _("Localized texts");?></a></li>
			<li><a href="A2B_logfile.php"><?= _("Watch Log files");?></a></li>
		</ul>
		</div>
	<?php  } ?>	
	<?php   if ( has_rights (ACX_FILE_MANAGER) ){ 	?>
		<li><a href="#" target="_self"><a href="#" target="_self" onclick="imgidclick('img21','div21');"><img id="img21" src="../Images/plus.gif" onmouseover="this.style.cursor='hand';" WIDTH="9" HEIGHT="9"> <strong><?= _("FILE MANAGER");?></strong></a></li>
		<div id="div21" style="display:none;">
		<ul>
			<li><a href="CC_musiconhold.php"><?= _("MusicOnHold");?></a></li>
			<li><a href="CC_upload.php"><?= _("Standard File");?></a></li>
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
imgidclick( <?= '\'img'.$menu_section.'\', \'div'.$menu_section.'\'' ?> );
</script>
