<?php
include ("../lib/defines.php");
include ("../lib/module.access.php");
include (dirname(__FILE__)."/../lib/company_info.php");

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>..:: :<?php echo CCMAINTITLE; ?>: ::..</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" type="text/css" href="../Css/menu.css" media="all">

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
<base target="mainFrame">
</head>

<body  leftmargin="5" topmargin="30" marginwidth="5" marginheight="45">
<div id="dummydiv"></div>


<ul id="nav">
	
	<?php   if ( has_rights (ACX_CUSTOMER) ){ 	?>
	<li><a href="#" target="_self"  onclick="imgidclick('img1','div1');"><img id="img1" src="../Images/plus.gif" onmouseover="this.style.cursor='hand';" WIDTH="9" HEIGHT="9"> <strong><?php echo gettext("CUSTOMERS");?></strong></a></li>
	<div id="div1" style="display:none;">
	<ul>
		<li><ul>
				<li><a href="A2B_entity_card.php?atmenu=card&stitle=Customers_Card"><?php echo gettext("List Customers");?></a></li>
				<li><a href="A2B_entity_card.php?form_action=ask-add&atmenu=card&stitle=Card"><?php echo gettext("Create Customers");?></a></li>
                <li><a href="CC_card_import.php?atmenu=card&stitle=Card"><?php echo gettext("Import Customers");?></a></li>
				<li><a href="A2B_entity_card_multi.php?stitle=Card"><?php echo gettext("Generate Customers");?></a></li>
				<li><a href="A2B_entity_friend.php?atmenu=sipfriend&stitle=SIP+Friends"><?php echo gettext("List SIP-FRIEND");?></a></li>
				<li><a href="A2B_entity_friend.php?form_action=ask-add&atmenu=sipfriend&stitle=SIP+Friends"><?php echo gettext("Create SIP-FRIEND");?></a></li>
				<li><a href="A2B_entity_friend.php?atmenu=iaxfriend&stitle=IAX+Friends"><?php echo gettext("List IAX-FRIEND");?></a></li>
				<li><a href="A2B_entity_friend.php?form_action=ask-add&atmenu=iaxfriend&stitle=IAX+Friends"><?php echo gettext("Create IAX-FRIEND");?></a></li>
				<li><a href="A2B_entity_callerid.php?atmenu=callerid&stitle=CallerID"><?php echo gettext("List CallerID");?></a></li>
				<li><a href="A2B_entity_speeddial.php?atmenu=speeddial&stitle=Speed+Dial"><?php echo gettext("List Speed Dial");?></a></li>
				<li><a href="A2B_entity_speeddial.php?form_action=ask-add&atmenu=speeddial&stitle=Speed+Dial"><?php echo gettext("Create Speed Dial");?></a></li>
		</ul></li>
	</ul>
	</div>
	<?php   }  ?>
	<?php   if ( has_rights (ACX_BILLING) ){ 	?>

		<li><a href="#" target="_self"><a href="#" target="_self" onclick="imgidclick('img2','div2');"><img id="img2" src="../Images/plus.gif" onmouseover="this.style.cursor='hand';" WIDTH="9" HEIGHT="9"> <strong><?php echo gettext("BILLING");?></strong></a></li>
		<div id="div2" style="display:none;">
		<ul>
			<li><ul>
				<li><a href="A2B_entity_paypal.php?atmenu=paypal&stitle=Paypal+Transaction&form_action=list"><?php echo gettext("PayPal Transaction");?></a></li>
				<li><a href="A2B_entity_moneysituation.php?atmenu=moneysituation&stitle=Money_Situation"><?php echo gettext("View money situation");?></a></li>
				<li><a href="A2B_entity_payment.php?atmenu=payment&stitle=Solde"><?php echo gettext("View Payment");?></a></li>
				<li><a href="A2B_entity_payment.php?stitle=Payment_add&form_action=ask-add"><?php echo gettext("Add new Payment");?></a></li>
				<li><a href="A2B_entity_voucher.php?stitle=Voucher"><?php echo gettext("List Voucher");?></a></li>
				<li><a href="A2B_entity_voucher.php?stitle=Voucher_add&form_action=ask-add"><?php echo gettext("Create Voucher");?></a></li>
				<li><a href="A2B_entity_voucher_multi.php?stitle=Voucher_Generate"><?php echo gettext("Generate Vouchers");?></a></li>
				<li><a href="A2B_currencies.php"><?php echo gettext("Currency Table");?></a></li>
				<li><a href="A2B_entity_charge.php?atmenu=charge&stitle=Charge&form_action=list"><?php echo gettext("List Charge");?></a></li>
				<li><a href="A2B_entity_charge.php?form_action=ask-add&atmenu=charge&stitle=Charge"><?php echo gettext("Add Charge");?></a></li>
				<li><a href="A2B_entity_ecommerce.php?atmenu=ecommerce&stitle=E-Commerce"><?php echo gettext("List E-Product");?></a></li>
				<li><a href="A2B_entity_ecommerce.php?form_action=ask-add&atmenu=ecommerce&stitle=E-Commerce"><?php echo gettext("Add E-Product");?></a></li>
			</ul></li>
		</ul>
		</div>
	<?php   }  ?>
	<?php   if ( has_rights (ACX_RATECARD) ){ 	?>
		<li><a href="#" target="_self"><a href="#" target="_self" onclick="imgidclick('img3','div3');"><img id="img3" src="../Images/plus.gif"  onmouseover="this.style.cursor='hand';" WIDTH="9" HEIGHT="9"> <strong><?php echo gettext("RATECARD");?></strong></a></li>
		<div id="div3" style="display:none;">
		<ul>
			<li><ul>
				<li><a href="A2B_entity_tariffgroup.php?form_action=ask-add&atmenu=tariffgroup&stitle=Tariff+Group"><?php echo gettext("Create TariffGroup");?></a></li>
				<li><a href="A2B_entity_tariffgroup.php?atmenu=tariffgroup&stitle=TariffGroup"><?php echo gettext("List TariffGroup");?></a></li>
				<li><a href="A2B_entity_tariffplan.php?atmenu=tariffplan&stitle=Tariffplan"><?php echo gettext("List RateCard");?></a></li>
				<li><a href="A2B_entity_tariffplan.php?form_action=ask-add&atmenu=tariffplan&stitle=RateCard"><?php echo gettext("Create new RateCard");?></a></li>
				<li><a href="A2B_entity_def_ratecard.php?atmenu=ratecard&stitle=RateCard"><?php echo gettext("Browse Rates");?></a></li>
				<li><a href="A2B_entity_def_ratecard.php?form_action=ask-add&atmenu=ratecard&stitle=RateCard"><?php echo gettext("Add Rate");?></a></li>
				<li><a href="CC_ratecard_import.php?atmenu=ratecard&stitle=RateCard"><?php echo gettext("Import RateCard");?></a></li>
				<li><a href="CC_entity_sim_ratecard.php?atmenu=ratecard&stitle=Ratecard+Simulator"><?php echo gettext("Ratecard Simulator");?></a></li>
				<li><a href="A2B_entity_prefix.php"><?php echo gettext("Browse Prefix");?></a></li>
			</ul></li>
		</ul>
		</div>
	<?php   }  ?>
	<?php   if ( has_rights (ACX_TRUNK) ){ 	?>
		<li><a href="#" target="_self"><a href="#" target="_self" onclick="imgidclick('img4','div4');"><img id="img4" src="../Images/plus.gif" onmouseover="this.style.cursor='hand';" WIDTH="9" HEIGHT="9"> <strong><?php echo gettext("TRUNK");?></strong></a></li>
		<div id="div4" style="display:none;">
		<ul>
			<li><ul>
				<li><a href="A2B_entity_trunk.php?stitle=Trunk"><?php echo gettext("List Trunk");?></a></li>
				<li><a href="A2B_entity_trunk.php?stitle=Trunk&form_action=ask-add"><?php echo gettext("Add Trunk");?></a></li>
				<li><a href="A2B_entity_provider.php?stitle=Provider"><?php echo gettext("List Provider");?></a></li>
				<li><a href="A2B_entity_provider.php?stitle=Provider&form_action=ask-add"><?php echo gettext("Create Provider");?></a></li>
			</ul></li>
		</ul>
		</div>
	<?php  } ?>
	<?php   if ( has_rights (ACX_DID) ){ 	?>
		<li><a href="#" target="_self"><a href="#" target="_self" onclick="imgidclick('img41','div41');"><img id="img41" src="../Images/plus.gif" onmouseover="this.style.cursor='hand';" WIDTH="9" HEIGHT="9"> <strong><?php echo gettext("DID");?></strong></a></li>
		<div id="div41" style="display:none;">
		<ul>
			<li><ul>
				<li><a href="A2B_entity_didgroup.php?stitle=DID+Group"><?php echo gettext("List DID Group");?></a>
				<li><a href="A2B_entity_didgroup.php?stitle=DID+Group&form_action=ask-add"><?php echo gettext("Add DID Group");?></a></li>
				<li><a href="A2B_entity_did.php?stitle=DID"><?php echo gettext("List DID");?></a></li>
				<li><a href="A2B_entity_did.php?stitle=DID&form_action=ask-add"><?php echo gettext("Add DID");?></a></li>
                <li><a href="A2B_entity_did_import.php?stitle=DID"><?php echo gettext("Import DID");?></a></li>
				<li><a href="A2B_entity_did_destination.php?stitle=DID+Destination"><?php echo gettext("List Destination");?></a></li>
				<li><a href="A2B_entity_did_destination.php?stitle=DID+Destination&form_action=ask-add"><?php echo gettext("Add Destination");?></a></li>
				<li><a href="A2B_entity_did_billing.php?atmenu=did_billing&stitle=DID+BILLING"><?php echo gettext("DID BILLING");?></a></li>
			</ul></li>
		</ul>
		</div>
	<?php  } ?>
	<?php   if ( has_rights (ACX_CALL_REPORT) ){ 	?>
		<li><a href="#" target="_self"><a href="#" target="_self" onclick="imgidclick('img5','div5');"><img id="img5" src="../Images/plus.gif" onmouseover="this.style.cursor='hand';" WIDTH="9" HEIGHT="9"> <strong><?php echo gettext("CALL REPORT");?></strong></a></li>
		<div id="div5" style="display:none;">
		<ul>
			<li><ul>
					<li><a href="call-log-customers.php?stitle=Call_Report_Customers&nodisplay=1&posted=1"><?php echo gettext("CDR Report");?></a></li>
					<li><a href="invoices.php?stitle=Invoice&nodisplay=1"><?php echo gettext("Invoice");?></a></li>
					<li><a href="asterisk-stat-v2/call-comp.php"><?php echo gettext("Calls Compare");?></a></li>
					<li><a href="asterisk-stat-v2/call-last-month.php"><?php echo gettext("Monthly Traffic");?></a></li>
					<li><a href="asterisk-stat-v2/call-daily-load.php"><?php echo gettext("Daily Load");?></a></li>
			</ul></li>
		</ul>
		</div>
	<?php  } ?>
	<?php   if ( has_rights (ACX_CRONT_SERVICE) ){ 	?>
		<li><a href="#" target="_self"><a href="#" target="_self" onclick="imgidclick('img9','div9');"><img id="img9" src="../Images/plus.gif" onmouseover="this.style.cursor='hand';" WIDTH="9" HEIGHT="9"> <strong><?php echo gettext("CRONT SERVICE");?></strong></a></li>
		<div id="div9" style="display:none;">
		<ul>
			<li><ul>
				<li><a href="A2B_entity_autorefill.php?stitle=Auto+Refill"><?php echo gettext("AutoRefill Report");?></a></li>
				<li><a href="A2B_entity_service.php?stitle=Recurring+Service"><?php echo gettext("List Recurring Service");?></a></li>
				<li><a href="A2B_entity_service.php?stitle=Recurring+Service&form_action=ask-add"><?php echo gettext("Add Recurring Service");?></a></li>
			</ul></li>
		</ul>
		</div>

	<?php  } ?>
	<?php   if ( has_rights (ACX_SIGNUP) ){ 	?>
		<li><a href="#" target="_self"><a href="#" target="_self" onclick="imgidclick('img6','div6');"><img id="img6" src="../Images/plus.gif" onmouseover="this.style.cursor='hand';" WIDTH="9" HEIGHT="9"> <strong><?php echo gettext("SIGNUP");?></strong></a></li>
		<div id="div6" style="display:none;">
		<ul>
			<li><ul>
				<li><a href="A2B_entity_mailtemplate.php?atmenu=mailtemplate&stitle=Mail+Tempalte"><?php echo gettext("Show mail template");?></a></li>
				<li><a href="A2B_entity_mailtemplate.php?form_action=ask-add&atmenu=mailtemplate&stitle=Mail+Tempalte"><?php echo gettext("Create mail template");?></a></li>
			</ul></li>
		</ul>
		</div>
	<?php  } ?>
	<?php   if ( has_rights (ACX_DID) ){ 	?>
		<li><a href="#" target="_self"><a href="#" target="_self" onclick="imgidclick('img61','div61');"><img id="img61" src="../Images/plus.gif" onmouseover="this.style.cursor='hand';" WIDTH="9" HEIGHT="9"> <strong><?php echo gettext("PREDICT-DIALER");?></strong></a></li>
		<div id="div61" style="display:none;">
		<ul>
			<li><ul>
				<li><a href="A2B_entity_campaign.php?atmenu=campaign&stitle=Campaign"><?php echo gettext("List Campaign");?></a></li>
				<li><a href="A2B_entity_campaign.php?form_action=ask-add&atmenu=campaign&stitle=Campaign"><?php echo gettext("Create Campaign");?></a></li>
				<li><a href="A2B_entity_phonelist.php?atmenu=phonelist&stitle=Phonelist"><?php echo gettext("Show Phonelist");?></a></li>
				<li><a href="A2B_entity_phonelist.php?atmenu=phonelist&stitle=Phonelist&form_action=ask-add"><?php echo gettext("Add PhoneNumber");?></a></li>
				<li><a href="CC_phonelist_import.php?atmenu=phonelist&stitle=Phonelist+Import"><?php echo gettext("Import Phonelist");?></a></li>
			</ul></li>
		</ul>
		</div>
	<?php  } ?>
	<?php   if ( has_rights (ACX_ADMINISTRATOR) ){ 	?>
		<li><a href="#" target="_self"><a href="#" target="_self" onclick="imgidclick('img7','div7');"><img id="img7" src="../Images/plus.gif" onmouseover="this.style.cursor='hand';" WIDTH="9" HEIGHT="9"> <strong><?php echo gettext("ADMINISTRATOR");?></strong></a></li>
		<div id="div7" style="display:none;">
		<ul>
			<li><ul>
				<li><a href="A2B_entity_user.php?atmenu=user&groupID=0&stitle=Administrator+management"><?php echo gettext("Show Administrator");?></a></li>
				<li><a href="A2B_entity_user.php?form_action=ask-add&atmenu=user&groupID=0&stitle=Administrator+management"><?php echo gettext("Add Administrator");?></a></li>
				<li><a href="A2B_entity_user.php?atmenu=user&groupID=1&stitle=ACL+Admin+management"><?php echo gettext("Show ACL Admin");?></a></li>
				<li><a href="A2B_entity_user.php?form_action=ask-add&atmenu=user&groupID=1&stitle=ACL+Admin+management"><?php echo gettext("Add ACL Admin");?></a></li>
				<li><a href="A2B_entity_backup.php?form_action=ask-add"><?php echo gettext("Database Backup");?></a></li>
				<li><a href="A2B_entity_restore.php"><?php echo gettext("Database Restore");?></a></li>
			</ul></li>
		</ul>
		</div>
	<?php  } ?>	
	<?php   if ( has_rights (ACX_FILE_MANAGER) ){ 	?>
		<li><a href="#" target="_self"><a href="#" target="_self" onclick="imgidclick('img8','div8');"><img id="img8" src="../Images/plus.gif" onmouseover="this.style.cursor='hand';" WIDTH="9" HEIGHT="9"> <strong><?php echo gettext("FILE MANAGER");?></strong></a></li>
		<div id="div8" style="display:none;">
		<ul>
			<li><ul>
				<li><a href="CC_musiconhold.php"><?php echo gettext("MusicOnHold");?></a></li>
				<li><a href="CC_upload.php"><?php echo gettext("Standard File");?></a></li>
			</ul></li>
		</ul>
		</div>
	<?php  } ?>

	<li><a href=# target=_self></a></li>

	<ul>
		<li><ul>
		<li><a href="logout.php?logout=true" target="_top"><font color="#DD0000"><b>&nbsp;&nbsp;<?php echo gettext("LOGOUT");?></b></font></a></li>
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

</body>
</html>
