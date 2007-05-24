<?php
include_once("lib/defines.php");
include_once("lib/module.access.php");
include_once(dirname(__FILE__)."/lib/company_info.php");

if (! has_rights (ACX_ACCESS)){ 
	   Header ("HTTP/1.0 401 Unauthorized");
	   Header ("Location: PP_error.php?c=accessdenied");
	   die();
}

//require (LANGUAGE_DIR.FILENAME_PP_MENU);

$templatemail = 0;
$displayservice = 1;

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
			document.all(imgID).src="images/minus.gif";			
		}
		else
		{			
			document.all(divID).style.display="None";			
			document.all(imgID).src="images/plus.gif";			
		}
	}else{
		if 	(document.getElementById(divID).style.display == "none" )
		{			
			document.getElementById(divID).style.display="";			
			document.getElementById(imgID).src="images/minus.gif";
		}
		else
		{			
			document.getElementById(divID).style.display="None";
			document.getElementById(imgID).src="images/plus.gif";			
		}
	}

	window.event.cancelBubble=true;
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

//-->
</script>
<style type="text/css">
/* *-* Must go.. */
div.menu div ul {
	display: none;
	position: static;
}
</style>

<div id="dummydiv"></div>


	<div id="nav_before"></div>
	<div class="menu">
	
       <div><a href="userinfo.php?"><?= _("ACCOUNT INFO");?></a></div>
<?php if ($A2B->config['webcustomerui']['sipiaxinfo']==1) { ?>
	<div><a href="A2B_entity_sipiax_info.php?"><?= _("SIP/IAX INFO");?></a></div>
<?php }
if ($A2B->config['webcustomerui']['cdr']==1) { ?>
	<div><a href="call-history.php"><?= _("CALL HISTORY");?></a></div>
<?php }
if ($A2B->config['webcustomerui']['voucher']==1) { ?>
       <div><a href="A2B_entity_voucher.php?form_action=list"><?= _("VOUCHER");?></a></div>
<?php }
if ($A2B->config['webcustomerui']['invoice']==1) { ?>
	<div id='menu_invoices'>
	<a onclick="menu_toggle('menu_invoices');"><?= _("INVOICES");?></a>
	<ul>
		<li><a href="A2B_entity_call_details.php"><?= _("Invoice Details");?></a></li>
		<li><a href="A2B_entity_view_invoice.php"><?= _("View Invoices");?></a></li>
		<li><a href="invoices_customer.php"><?= _("Current Invoice");?></a></li> 
	</ul>
	</div>
<?php }
if ($A2B->config['webcustomerui']['did']==1) { ?>
       <div><a href="A2B_entity_did.php?form_action=list"><?= _("DID");?></a></div>
<?php }
if ($A2B->config['webcustomerui']['speeddial']==1) { ?>
	<div><a href="A2B_entity_speeddial.php"><?= _("SPEED DIAL");?></a></div>
<?php }
if ($A2B->config['webcustomerui']['ratecard']==1) { ?>
	<div><a href="A2B_entity_ratecard.php?form_action=list"><?= _("RATECARD");?></a></div>
<?php }
if ($A2B->config['webcustomerui']['simulator']==1) {?>
	<div><a href="simulator.php"><?= _("SIMULATOR");?></a></div>
<?php }
if ($A2B->config['webcustomerui']['callback']==1) { ?>
	<div><a href="callback.php"><?= _("CALLBACK");?></a></div>
<?php }
if ($A2B->config['webcustomerui']['webphone']==1) { ?>
	<div><a href="webphone.php"><?= _("WEB-PHONE");?></a></div>
<?php }
if ($A2B->config['webcustomerui']['callerid']==1){ ?>
	<div><a href="A2B_entity_callerid.php"><?= _("ADD CALLER ID");?></a></div>
<?php }
if ($A2B->config['webcustomerui']['password']==1) { ?>
	<div><a href="A2B_entity_password.php?form_action=ask-edit"><?= _("PASSWORD");?></a></div>
<?php } ?>
	<div><a href="logout.php?logout=true" target="_parent"><font color="#DD0000"><?= _("LOGOUT");?></font></a></div>

</div>


<table>
<tr>
	<td>
		<a href="index2.php?language=espanol" target="_parent"><img src="images/flags/es.gif" border="0" title="Spanish" alt="Spanish"></a>
		<a href="index2.php?language=english" target="_parent"><img src="images/flags/us.gif" border="0" title="English" alt="English"></a>
		<a href="index2.php?language=french" target="_parent"><img src="images/flags/fr.gif" border="0" title="French" alt="French"></a>
		<a href="index2.php?language=romanian" target="_parent"><img src="images/flags/ro.gif" border="0" title="Romanian"alt="Romanian"></a>
		<a href="index2.php?language=chinese" target="_parent"><img src="images/flags/cn.gif" border="0" title="Chinese" alt="Chinese"></a>
		<a href="index2.php?language=polish" target="_parent"><img src="images/flags/pl.gif" border="0" title="Polish" alt="Polish"></a>
		<a href="index2.php?language=italian" target="_parent"><img src="images/flags/it.gif" border="0" title="Italian" alt="Italian"></a>
        <a href="index2.php?language=russian" target="_parent"><img src="images/flags/ru.gif" border="0" title="russian" alt="russian"></a>
		<a href="index2.php?language=turkish" target="_parent"><img src="images/flags/tr.gif" border="0" title="Turkish" alt="Turkish"></a>
        <a href="index2.php?language=portuguese" target="_parent"><img src="images/flags/pt.gif" border="0" title="Portuguese" alt="Portuguese"></a>
        <a href="index2.php?language=urdu" target="_parent"><img src="images/flags/pk.gif" border="0" title="Urdu" alt="Urdu"></a>

<!--
		<a href="index2.php?language=brazilian" target="_parent"><img src="images/flags/br.gif" border="0"title="Brazilian" alt="Brazilian"></a>
		<a href="index2.php?language=portuguese" target="_parent"><img src="images/pt.gif" border="0"></a>
		<a href="index2.php?language=chinese" target="_parent"><img src="images/pt.gif" border="0"></a>
		<a href="index2.php?language=polish" target="_parent"><img src="images/pl.gif" border="0"></a>
-->
	</td>
</tr>
</table>
