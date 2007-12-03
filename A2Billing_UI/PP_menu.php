<?php 
	require_once ("./lib/defines.php");
	require_once ("lib/module.access.php");

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

function menu_toggle(sect_str){
	//elmnt.parent.style.visibility="hidden";
	//alert(elmnt.style.visibility);
	var sect=document.getElementById(sect_str);
	var dom_ul=sect.getElementsByTagName("ul")[0];
	if (dom_ul.style.display=="inline")
		dom_ul.style.display="none";
	else
		dom_ul.style.display="inline";
}

function menu_show(sect_str){
	//elmnt.parent.style.visibility="hidden";
	//alert(elmnt.style.visibility);
	var sect=document.getElementById(sect_str);
	var dom_ul=sect.getElementsByTagName("ul")[0];
	dom_ul.style.display="inline";
}

//-->
</script>

<style type="text/css">
/* *-* Must go.. */

div.menu div a {
	display: block;
	font-size: 11px;
	padding: 2px 10px;
	text-decoration: none;
	background: #EDF2F2;
	border-bottom: 1px solid #ddd;
	border-top: 1px solid #fff;
	border-right: 1px solid #ddd;

}

div.menu ul li{
	padding: 0;
}

div.menu ul li a {
	padding: 0px 18px 0px 20px;
	font-size: 10px;
	margin: 0;
	border: none;
/* 	height : 11px; */
}

div.menu div ul {
	display: none;
	position: static;
	padding: 0;
	margin: 0;
	border: 0;
}
</style>

<div id="dummydiv"></div>


<div id="menu" class="menu" >
	
	<?php   if ( has_rights (ACX_CUSTOMER) ){ 	?>
	<div id='menu_customers'>
	<a onclick="menu_toggle('menu_customers');"><?= _("CUSTOMERS");?></a>
	<ul>
		<li><a href="A2B_entity_card.php"><?= _("List Customers");?></a></li>
		<li><a href="A2B_entity_card.php?form_action=ask-add"><?= _("Create Customers");?></a></li>
                <li><a href="CC_card_import.php"><?= _("Import Customers");?></a></li>
		<li><a href="A2B_entity_card_multi.php"><?= _("Generate Customers");?></a></li>
		<li><a href="A2B_entity_friend.php?atmenu=sipfriend"><?= _("List SIP-FRIEND");?></a></li>
		<li><a href="A2B_entity_friend.php?atmenu=sipfriend&form_action=ask-add"><?= _("Create SIP-FRIEND");?></a></li>
		<li><a href="A2B_entity_friend.php"><?= _("List IAX-FRIEND");?></a></li>
		<li><a href="A2B_entity_friend.php?form_action=ask-add"><?= _("Create IAX-FRIEND");?></a></li>
		<li><a href="A2B_entity_callerid.php"><?= _("List CallerID");?></a></li>
		<li><a href="A2B_entity_speeddial.php"><?= _("List Speed Dial");?></a></li>
		<li><a href="A2B_entity_speeddial.php?form_action=ask-add"><?= _("Create Speed Dial");?></a></li>
	</ul>
	</div>
	<?php   }  ?>
	<?php   if ( has_rights (ACX_AGENTS) ){ 	?>
	<div id='menu_agents'>
	<a onclick="menu_toggle('menu_agents');"><?= _("AGENTS");?></a>
	<ul>
		<li><a href="A2B_entity_agent.php"><?= _("List Agents");?></a></li>
		<li><a href="A2B_entity_agent.php?form_action=ask-add"><?= _("Create Agents");?></a></li>
		<li><a href="A2B_entity_regulars.php"><?= _("List Regulars");?></a></li>
		<li><a href="A2B_entity_card_multia.php"><?= _("Generate Regulars");?></a></li>
		<li><a href="A2B_entity_booths.php"><?= _("Booths");?></a></li>
		<li><a href="A2B_entity_booths.php?form_action=ask-add"><?= _("Create Booth");?></a></li>
		<li><a href="A2B_entity_agentpay.php?form_action=list"><?= _("List Payments");?></a></li>
		<li><a href="A2B_entity_agentpay.php?form_action=ask-add"><?= _("Add Payment");?></a></li>
		<li><a href="agent-money.php"><?= _("Money situation");?></a></li>
		<li><a href="CC_entity_sim_callshop.php"><?= _("Callshop Simulator");?></a></li>
	</ul>
	</div>
	<?php   }  ?>
	<?php   if ( has_rights (ACX_BILLING) ){ 	?>
	<div id='menu_billing'>
	<a onclick="menu_toggle('menu_billing');"><?= _("BILLING");?></a>
	<ul>
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
	<div id='menu_ratecard'>
	<a onclick="menu_toggle('menu_ratecard');"><?= _("RATECARD");?></a>
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
	<div id='menu_pkgoffer'>
	<a onclick="menu_toggle('menu_pkgoffer');"><?= _("PACKAGE OFFER");?></a>
		<ul>
			<li><a href="A2B_entity_package.php"><?= _("List Offer Package");?></a></li>
			<li><a href="A2B_entity_package.php?form_action=ask-add"><?= _("Add Offer Package");?></a></li>
			<li><a href="A2B_detail_package.php"><?= _("Details Package");?></a></li>
		</ul>
		</div>
	<?php   }  ?>
	<?php   if ( has_rights (ACX_OUTBOUNDCID) ){ 	?>
		<div id='menu_obcid'>
		<a onclick="menu_toggle('menu_obcid');"><?= _("OUTBOUND CID");?></a>
		<ul>
			<li><a href="A2B_entity_outbound_cidgroup.php?form_action=ask-add"><?= _("Create CIDGroup");?></a></li>
			<li><a href="A2B_entity_outbound_cidgroup.php"><?= _("List CIDGroup");?></a></li>
			<li><a href="A2B_entity_outbound_cid.php?form_action=ask-add"><?= _("Add CID");?></a></li>
			<li><a href="A2B_entity_outbound_cid.php"><?= _("List CID's");?></a></li>
		</ul>
		</div>
	<?php   }  ?>
	<?php   if ( has_rights (ACX_TRUNK) ){ 	?>
		<div id='menu_trunk'>
		<a onclick="menu_toggle('menu_trunk');"><?= _("TRUNK");?></a>
		<ul>
			<li><a href="A2B_entity_trunk.php"><?= _("List Trunk");?></a></li>
			<li><a href="A2B_entity_trunk.php?form_action=ask-add"><?= _("Add Trunk");?></a></li>
			<li><a href="A2B_entity_provider.php"><?= _("List Provider");?></a></li>
			<li><a href="A2B_entity_provider.php?form_action=ask-add"><?= _("Create Provider");?></a></li>
		</ul>
		</div>
	<?php  } ?>
	<?php   if ( has_rights (ACX_DID) ){ 	?>
		<div id='menu_did'>
		<a onclick="menu_toggle('menu_did');"><?= _("DID");?></a>
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
		<div id='menu_creport'>
		<a onclick="menu_toggle('menu_creport');"><?= _("CALL REPORT");?></a>
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
		<div id='menu_invoicing'>
		<a onclick="menu_toggle('menu_invoicing');"><?= _("INVOICING");?></a>
		<ul>
			<li><a href="A2B_entity_view_invoice.php"><?= _("View Invoices");?></a></li>
			<li><a href="A2B_entity_create_invoice.php"><?= _("Create Invoices");?></a></li>
			<li><a href="invoices.php?nodisplay=1"><?= _("Invoice");?></a></li>
			<li><a href="invoices_customer.php?nodisplay=1"><?= _("Invoices Customer");?></a></li>
			<li><a href="A2B_entity_invoices.php?invoicetype=billed"><?= _("View Billed Invoices");?></a></li>
			<li><a href="A2B_entity_invoices.php?invoicetype=unbilled"><?= _("View UnBilled Invoices");?></a></li>
			<li><a href="A2B_entity_agent_invoicev.php"><?= _("Agent Invoices");?></a></li>
		</ul>
		</div>
	<?php  } ?>
	<?php   if ( has_rights (ACX_CRONT_SERVICE) ){ 	?>
		<div id='menu_cront'>
		<a onclick="menu_toggle('menu_cront');"><?= _("CRON SERVICE");?></a>
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
		<div id='menu_callback'>
		<a onclick="menu_toggle('menu_callback');"><?= _("CALLBACK");?></a>
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
		<div id='menu_misc'>
		<a onclick="menu_toggle('menu_misc');"><?= _("MISC");?></a>
		<ul>
			<li><a href="A2B_entity_mailtemplate.php"><?= _("Show mail template");?></a></li>
			<li><a href="A2B_entity_mailtemplate.php?form_action=ask-add"><?= _("Create mail template");?></a></li>
			<li><a href="A2B_entity_prefix.php"><?= _("Browse Prefix");?></a></li>
			<li><a href="A2B_entity_prefix.php?form_action=ask-add"><?= _("Add Prefix");?></a></li>
		</ul>
		</div>
	<?php  } ?>
	<?php   if ( has_rights (ACX_ADMINISTRATOR) ){ 	?>
		<div id='menu_admin'>
		<a onclick="menu_toggle('menu_admin');"><?= _("ADMINISTRATOR");?></a>
		<ul>
			<li><a href="A2B_entity_user.php?groupID=0"><?= _("Show Administrator");?></a></li>
			<li><a href="A2B_entity_user.php?form_action=ask-add&groupID=0"><?= _("Add Administrator");?></a></li>
			<li><a href="A2B_entity_user.php?groupID=1"><?= _("Show ACL Admin");?></a></li>
			<li><a href="A2B_entity_user.php?form_action=ask-add&groupID=1"><?= _("Add ACL Admin");?></a></li>
			<li><a href="A2B_entity_backup.php?form_action=ask-add"><?= _("Database Backup");?></a></li>
			<li><a href="A2B_entity_restore.php"><?= _("Database Restore");?></a></li>
			<li><a href="A2B_entity_texts.php"><?= _("Localized texts");?></a></li>
			<li><a href="A2B_logfile.php"><?= _("Watch Log files"); ?></a></li>
			<li><a href="A2B_entity_log_viewer.php"><?= _("System Log");?></a></li>
		</ul>
		</div>
	<?php  } ?>	
	<?php   if ( has_rights (ACX_FILE_MANAGER) ){ 	?>
		<div id='menu_files'>
		<a onclick="menu_toggle('menu_files');"><?= _("FILE MANAGER");?></a>
		<ul>
			<li><a href="CC_musiconhold.php"><?= _("MusicOnHold");?></a></li>
			<li><a href="CC_upload.php"><?= _("Standard File");?></a></li>
		</ul>
		</div>
	<?php  } ?>

	<div><a style="color: #DD0000; font-weight: bold;" href="logout.php?logout=true" target="_top"><?= gettext("LOGOUT");?></a></div>

</div>
<br>
<table>
<tr>
	<td>
		<a href="index2.php?language=english" target="_parent"><img src="./Images/flags/us.png" border="0" title="English" alt="English"></a>
	</td>
</tr>
</table>
<script>
menu_show( '<?= $menu_section ?>',true);
</script>
