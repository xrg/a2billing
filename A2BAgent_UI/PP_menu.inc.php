<?php
require_once("lib/company_info.php");


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


//-->
</script>
<div id="dummydiv"></div>


<ul id="nav">
	<li><a href="booths.php"><strong><?= _("BOOTHS");?></strong></a></li>
	<li><a href=# target=_self></a></li>	
	
	<li><a href="A2B_entity_cards.php"><strong><?= _("CUSTOMERS");?></strong></a></li>
	<li><a href=# target=_self></a></li>
	
	<li><a href="A2B_entity_booths.php"><strong><?= _("EDIT BOOTHS");?></strong></a></li>
	<li><a href=# target=_self></a></li>	
	
	<li><a href="invoices_cust.php"><strong><?= _("PAYMENTS");?></strong></a></li>
	<li><a href=# target=_self></a></li>	
	
	<li><a href="reports_choice.php"><strong><?= _("REPORTS");?></strong></a></li>
	<li><a href=# target=_self></a></li>	

	<li><a href="info_choice.php"><strong><?= _("INFO PAGES");?></strong></a></li>
	<li><a href=# target=_self></a></li>
	
	
	<?php if ($A2B->ini_cfg["agentcustomerui"]['password']){ ?>
	<li><a href=# target=_self></a></li>
	<li><a href="A2B_entity_password.php?atmenu=password&action=ask-edit"><strong><?= gettext("PASSWORD"); ?></strong></a></li>
	<?php  } ?>

	<li><a href=# target=_self></a></li>
	<li><a href="logout.php?logout=true" target="_parent"><font color="#DD0000"><strong><?= _("LOGOUT");?></strong></font></a></li>

</ul>

<?php
foreach($language_list as $lang)
	if ($lang['flag']!=null)
	echo "		<a href=\"index2.php?language=" .$lang['cname'] . "\" target=\"_parent\"><img src=\"images/flags/" . $lang['flag'] . "\" border=\"0\" title=\"" . $lang['name'] ."\" alt=\"" .$lang['name']."\"></a>\n";
?>
