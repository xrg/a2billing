<?php 
	include (dirname(__FILE__)."/lib/company_info.php");
	
	define ("WEBUI_DATE", 'Release : xxx');
	define ("WEBUI_VERSION", 'Asterisk2Billing - Version 1.3.x + Callshop - ');	
?>
<html><head>
<link rel="shortcut icon" href="../Images/favicon.ico">
<link rel="icon" href="../Images/animated_favicon1.gif" type="image/gif">

<title>..:: <?php echo CCMAINTITLE; ?> ::..</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link href="Css/Css_Ale.css" rel="stylesheet" type="text/css">
<link href="Css/menu.css" rel="stylesheet" type="text/css">
<link href="Css/style-def.css" rel="stylesheet" type="text/css">
<?php if (isset($USE_AJAX)) echo "<script type=\"text/javascript\" src=\"lib/ajax.js\" ></script>\n"; ?>
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
			document.all(imgID).src="Css/kicons/viewmag.png";			
		}
		else
		{			
			document.all(divID).style.display="None";			
			document.all(imgID).src="Css/kicons/help.png";			
		}
	}else{
		if 	(document.getElementById(divID).style.display == "none" )
		{			
			document.getElementById(divID).style.display="";			
			document.getElementById(imgID).src="Css/kicons/viewmag.png";
		}
		else
		{			
			document.getElementById(divID).style.display="None";
			document.getElementById(imgID).src="Css/kicons/help.png";			
		}
	}

	window.event.cancelBubble=true;
}


//-->
</script>

<style>
table.Booth td.name {
	font-size: 12px;
	color: black;
	text-align: center;
}

table.Booth td.state0 {
	text-align: center;
	color: white;
	background: black;
}

table.Booth td.state1 {
	text-align: center;
	color: white;
	background: yellow;
}

table.Booth td.state2 {
	text-align: center;
	color: white;
	background: #0000a0;
}

table.Booth td.state3 {
	text-align: center;
	color: white;
	background: blue;
}
table.Booth td.state4 {
	text-align: center;
	color: white;
	background: green;
}

table.Booth td.state5 {
	text-align: center;
	color: white;
	background: red;
}

table.Booth td.state6 {
	text-align: center;
	color: white;
	background: blue;
}

table.Booth td.buttons a {
	display: none;
	color: black;
	margin-right: 3px;
}

p.pay-btn {
	text-align: right ;
}

p.pay-back-btn {
	text-align: right ;
}
p.pay-back-btn a {
	font-size: 20pt;
	color: #008000;
}
p.pay-btn a {
	font-size: 20pt;
	color: #800000;
}

p.pay-title {
	font-size: 20pt;
	color: black;
}

p.pay-bb {
	font-size: 16pt;
	text-align: center;
}
p.pay-bb a{
	color: blue;
}
p.pay-message {
	font-size: 18pt;
	text-align: center;
	border: solid green 2px;
}

</style>
</head>
<body  leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<p class="version" align="right"><?php echo WEBUI_VERSION.WEBUI_DATE; ?></p>
<br>
