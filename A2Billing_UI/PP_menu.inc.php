<?php 
require_once ("lib/defines.php");
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
			document.all(imgID).src="./Images/minus.png";
		}
		else
		{			
			document.all(divID).style.display="None";
			document.all(imgID).src="./Images/plus.png";
		}
		// Only for I.E
		window.event.cancelBubble=true;
	}else{
		if 	(document.getElementById(divID).style.display == "none" )
		{
			document.getElementById(divID).style.display="";
			document.getElementById(imgID).src="./Images/minus.png";
		}
		else
		{			
			document.getElementById(divID).style.display="None";
			document.getElementById(imgID).src="./Images/plus.png";
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

<div id="dummydiv"></div>

<div id="menu" class="menu" >
	
	<div>
		<a href="PP_intro.php"><?= _("HOME") ?></a>
	</div>
	<?php   if ( has_rights (ACX_CUSTOMER) ){ 	?>
	<div id='menu_customers'>
	<a onclick="menu_toggle('menu_customers');"><?= _("CUSTOMERS");?></a>
	<ul>
		<li><a href="A2B_entity_card.php"><?= _("List");?></a></li>
		<li><a href="Gen_cards.php"><?= _("Generate");?></a></li>
                <li><a href="CC_card_import.php"><?= _("Import");?></a></li>
                <li><a href="A2B_entity_card_group.php"><?= _("Groups");?></a></li>
		<li><a href="A2B_entity_astuser.php"><?= _("VoIP users");?></a></li>
		<li><a href="A2B_entity_instance.php"><?= _("VoIP regs");?></a></li>
		<li><a href="A2B_entity_callerid.php"><?= _("CallerID");?></a></li>
		<li><a href="A2B_entity_speeddial.php"><?= _("Speed Dial");?></a></li>
	</ul>
	</div>
	<?php   }  ?>
	<?php   if ( has_rights (ACX_AGENTS) ){ 	?>
	<div id='menu_agents'>
	<a onclick="menu_toggle('menu_agents');"><?= _("AGENTS");?></a>
	<ul>
		<li><a href="A2B_entity_agent.php"><?= _("Agents");?></a></li>
		<li><a href="A2B_entity_booths.php"><?= _("Booths");?></a></li>
		<li><a href="Gen_booths.php"><?= _("Generate Booths");?></a></li>
		<li><a href="Callshop_booths.php"><?= _("Callshop View");?></a></li>
		<li><a href="A2B_entity_sessions.php"><?= _("Sessions");?></a></li>
		<li><a href="A2B_entity_session_problems.php"><?= _("Session Problems");?></a></li>
		<li><a href="A2B_entity_agentpay.php"><?= _("Payments");?></a></li>
		<li><a href="agent-money.php"><?= _("Money situation");?></a></li>
	</ul>
	</div>
	<?php   }  ?>
	<?php   if ( has_rights (ACX_BILLING) ){ 	?>
	<div id='menu_billing'>
	<a onclick="menu_toggle('menu_billing');"><?= _("BILLING");?></a>
	<ul>
		<li><a href="A2B_currencies.php"><?= _("Currency Table");?></a></li>
		<li><a href="A2B_entity_payment_configuration.php"><?= _("View Payment Methods") ?></a></li>
                <li><a href="A2B_entity_transactions.php"><?= _("View Transactions"); ?></a></li>
		<li><a href="A2B_entity_moneysituation.php"><?= _("View money situation");?></a></li>
		<li><a href="A2B_entity_payment.php"><?= _("View Payment");?></a></li>
		<li><a href="A2B_entity_payment.php?form_action=ask-add"><?= _("Add new Payment");?></a></li>
		<li><a href="A2B_entity_voucher.php"><?= _("List Voucher");?></a></li>
		<li><a href="A2B_entity_voucher.php?form_action=ask-add"><?= _("Create Voucher");?></a></li>
		<li><a href="A2B_entity_voucher_multi.php"><?= _("Generate Vouchers");?></a></li>
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
			<li><a href="A2B_entity_tariffgroup.php?"><?= _("TariffGroup");?></a></li>
			<li><a href="A2B_entity_tariffplan.php"><?= _("RateCard (buy)");?></a></li>
			<li><a href="A2B_entity_retailplan.php"><?= _("RetailPlan (sell)");?></a></li>
			<li><a href="A2B_entity_buyrate.php"><?= _("Buy Rate");?></a></li>
			<li><a href="A2B_entity_sellrate.php"><?= _("Sell Rate");?></a></li>
			<li><a href="A2B_entity_buyrate.php?action=ask-import"><?= _("Import Buy Rates");?></a></li>
			<li><a href="A2B_entity_sellrate.php?action=ask-import"><?= _("Import Sell Rates");?></a></li>
			<li><a href="CC_entity_sim_ratecard.php"><?= _("Ratecard Simulator");?></a></li>
		</ul>
		</div>
	<?php   }  ?>
	<?php   if ( has_rights (ACX_PACKAGEOFFER) ){ 	?>
	<div id='menu_pkgoffer'>
	<a onclick="menu_toggle('menu_pkgoffer');"><?= _("PACKAGE OFFER");?></a>
		<ul>
			<li><a href="A2B_entity_package.php"><?= _("List Offer Package");?></a></li>
			<li><a href="A2B_detail_package.php"><?= _("Details Package");?></a></li>
		</ul>
		</div>
	<?php   }  ?>
	<?php   if ( has_rights (ACX_OUTBOUNDCID) ){ 	?>
		<div id='menu_obcid'>
		<a onclick="menu_toggle('menu_obcid');"><?= _("OUTBOUND CID");?></a>
		<ul>
			<li><a href="A2B_entity_outbound_cidgroup.php"><?= _("List CIDGroup");?></a></li>
			<li><a href="A2B_entity_outbound_cid.php"><?= _("List CID's");?></a></li>
		</ul>
		</div>
	<?php   }  ?>
		<div id='menu_servers'>
		<a onclick="menu_toggle('menu_servers');"><?= _("SERVERS");?></a>
		<ul>
	<?php   if ( has_rights (ACX_SERVERS) ){ 	?>
			<li><a href="A2B_entity_server_group.php"><?= _("Groups");?></a></li>
			<li><a href="A2B_entity_server.php"><?= _("Servers");?></a></li>
			<li><a href="Gen_peer_cfgs.php"><?= _("Gen. Configs");?></a></li>
			<li><a href="Gen_peer_dplans.php"><?= _("Gen. Peer Dial");?></a></li>
	<?php  } ?>
	<?php   if ( has_rights (ACX_TRUNK) ){ 	?>
			<li><a href="A2B_entity_provider.php"><?= _("Providers");?></a></li>
			<li><a href="A2B_entity_trunk.php"><?= _("Trunks");?></a></li>
	<?php  } ?>
		</ul>
		</div>
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
			<li><a href="A2B_entity_call.php?"><?= _("Calls");?></a></li>
			<li><a href="call-stats.php?"><?= _("Statistics");?></a></li>
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
			<li><a href="A2B_entity_view_invoice.php"><?= _("Card Invoices");?></a></li>
			<li><a href="A2B_entity_agent_invoicev.php"><?= _("Agent Invoices");?></a></li>
			<li><a href="Gen_invoice_card.php"><?= _("Create Card Inv.");?></a></li>
			<li><a href="Gen_invoice_agent.php"><?= _("Create Agent Inv.");?></a></li>
			<li><a href="call-unbilled.php"><?= _("Unbilled calls");?></a></li>
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
			<li><a href="A2B_entity_alarm.php"><?= _("Alarms");?></a></li>
			<li><a href="A2B_entity_alarmrun.php"><?= _("Alarm Data");?></a></li>
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

	
		<div id='menu_config'>
		<a onclick="menu_toggle('menu_config');"><?= _("CONFIG");?></a>
		<ul>
	<?php   if ( has_rights (ACX_NUMPLAN) ){ 	?>
			<li><a href="A2B_entity_numplan.php"><?= _("Numbering Plans");?></a></li>
	<?php  } ?>
	<?php   if ( has_rights (ACX_MISC) ){ 	?>
			<li><a href="A2B_entity_mailtemplate.php"><?= _("Mail template");?></a></li>
	<?php  } ?>
	<?php   if ( has_rights (ACX_ADMINISTRATOR) ){ 	?>
			<li><a href="A2B_entity_ast_usercfg.php"><?= _("User cfgs");?></a></li>
	<?php  } ?>
		</ul>
		</div>
	
	<?php   if ( has_rights (ACX_ADMINISTRATOR) ){ 	?>
		<div id='menu_admin'>
		<a onclick="menu_toggle('menu_admin');"><?= _("ADMINISTRATOR");?></a>
		<ul>
			<li><a href="A2B_entity_config.php"><?= _("Config");?></a></li>
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
<!--<tr>
	<td>
		<a href="index2.php?language=english" target="_parent"><img src="./Images/flags/us.png" border="0" title="English" alt="English"></a>
	</td>
</tr>-->
</table>
<script>
menu_show( '<?= $menu_section ?>',true);
</script>
