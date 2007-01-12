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
	
	<li><a href="reports_choice.php"><strong><?php echo gettext("REPORTS");?></strong></a></li>
	<li><a href=# target=_self></a></li>	

	<li><a href="info_choice.php"><strong><?php echo gettext("INFO PAGES");?></strong></a></li>
	<li><a href=# target=_self></a></li>
	
	
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
<?php
foreach($language_list as $lang)
	if ($lang['flag']!=null)
	echo "		<a href=\"index2.php?language=" .$lang['cname'] . "\" target=\"_parent\"><img src=\"images/flags/" . $lang['flag'] . "\" border=\"0\" title=\"" . $lang['name'] ."\" alt=\"" .$lang['name']."\"></a>\n";
?>
	</td>
</tr>
</table>
</body>
</html>
