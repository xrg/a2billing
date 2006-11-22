<?php
include ("lib/defines.php");
include ("lib/module.access.php");
include (dirname(__FILE__)."/lib/company_info.php");

if (! has_rights (ACX_ACCESS)){ 
	   Header ("HTTP/1.0 401 Unauthorized");
	   Header ("Location: PP_error.php?c=accessdenied");	   
	   die();	   
}

//require (LANGUAGE_DIR.FILENAME_PP_MENU);

$templatemail = 0;
$displayservice = 1;

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>..:: :<?php echo CCMAINTITLE; ?>: ::..</title>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<link rel="stylesheet" type="text/css" href="Css/menu.css" media="all">

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
<base target="mainFrame">
</head>

<body  leftmargin="0" topmargin="0" marginwidth="0" marginheight="35">
<div id="dummydiv"></div>


<ul id="nav">
	
		
	<li><a href="booths.php"><strong><?php echo gettext("BOOTHS");?></strong></a></li>
	<li><a href=# target=_self></a></li>	
	
	<li><a href="A2B_entity_cards.php?form_action=list"><strong><?php echo gettext("CUSTOMERS");?></strong></a></li>
	<li><a href=# target=_self></a></li>
	
	<li><a href="A2B_entity_booths.php?form_action=list"><strong><?php echo gettext("EDIT BOOTHS");?></strong></a></li>
	<li><a href=# target=_self></a></li>	
	
	<li><a href="invoices_cust.php"><strong><?php echo gettext("PAYMENTS");?></strong></a></li>
	<li><a href=# target=_self></a></li>	
	
	<li><a href="invoices.php"><strong><?php echo gettext("REPORTS");?></strong></a></li>
	<li><a href=# target=_self></a></li>	

	<li><a href="booths.php"><strong><?php echo gettext("INFO PAGES");?></strong></a></li>
	<li><a href=# target=_self></a></li>	
	
	<li><a href=# target=_self></a></li>	
	<li><a href="balance.php"><strong><?php echo gettext("CALL HISTORY");?></strong></a></li>
	
	<?php if ($A2B->config["agentcustomerui"]['password']){ ?>
	<li><a href=# target=_self></a></li>
	<li><a href="A2B_entity_password.php?atmenu=password&form_action=ask-edit&stitle=Password"><strong><?php echo gettext("PASSWORD");?></strong></a></li>
	<?php  } ?>

	<li><a href=# target=_self></a></li>
	<li><a href="logout.php?logout=true" target="_parent"><font color="#DD0000"><strong><?php echo gettext("LOGOUT");?></strong></font></a></li>

</ul>

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
        <a href="index2.php?language=greek" target="_parent"><img src="images/flags/gr.gif" border="0" title="Greek" alt="Greek"></a>

<!--
		<a href="index2.php?language=brazilian" target="_parent"><img src="images/flags/br.gif" border="0"title="Brazilian" alt="Brazilian"></a>
		<a href="index2.php?language=portuguese" target="_parent"><img src="images/pt.gif" border="0"></a>
		<a href="index2.php?language=chinese" target="_parent"><img src="images/pt.gif" border="0"></a>
		<a href="index2.php?language=polish" target="_parent"><img src="images/pl.gif" border="0"></a>
-->
	</td>
</tr>
</table>
</body>
</html>
