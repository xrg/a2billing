<?php

/** This Script will produce a full HTML page, based on variables already defined
    Everything that should appear at the page body (main pane) must be rendered by
    objects. Each object must have a Render() defined, which will just produce the
    text.
*/

if (! isset($BODY_ELEMS))
	$BODY_ELEMS=array()
?>
<html><head>
<title>..:: Asterisk2Billing : CallingCard platform ::..</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<link rel="shortcut icon" href="./Images/favicon.ico">
<link rel="icon" href="./Images/animated_favicon1.gif" type="image/gif">

<link href="./Css/Css_Ale.css" rel="stylesheet" type="text/css">
<link href="./Css/style-def.css" rel="stylesheet" type="text/css">
<link href="./Css/menu.css" rel="stylesheet" type="text/css">

<?php
	if (isset($HEADER_ELEMS))
		foreach($HEADER_ELEMS as $elem)
			if (is_object($elem))
			$elem->RenderHead();
?>
</head>
<body  leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<p class="version" align="right">v2.0 alpha - Dec 2007</p>
<br>
<table>
<tr><td class="divleft">
	<?php require("PP_menu.inc.php");?>
	</td>
<td class="divright">
	<?php
	foreach($BODY_ELEMS as $elem)
		if (is_object($elem))
			$elem->Render();
	?>
	</td>
</tr>
<tr><td>&nbsp;</td>
    <td><div class="w1">This software is under GPL licence. For further information, 
    please visit : <a href="http://www.asterisk2billing.org" target="_blank">asterisk2billing.org</a>
    </td>
</tr>
</body>
</html>