<?php

/** This Script will produce a full HTML page, based on variables already defined
    Everything that should appear at the page body (main pane) must be rendered by
    objects. Each object must have a Render() defined, which will just produce the
    text.
*/

if (! isset($PAGE_ELEMS))
	$PAGE_ELEMS=array();
else if ($FG_DEBUG)
	foreach($PAGE_ELEMS as $pe)
		if (! ($pe instanceof ElemBase)){
			error_log('Page element not canonical.');
			die();
		}

// Perform the actions..
foreach($PAGE_ELEMS as $elem){
	$res=$elem->PerformAction();
	if (is_string($res)){
		if ($FG_DEBUG>2){
		?>
<html><body>
	Redirecting to: <?= $res ?>
</body></html>
<?php
		exit();
		}
		Header('Location: ' . $res);
		exit();
	}
}

?>
<html><head>
<title>..:: Asterisk2Billing : CallingCard platform ::..</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<link rel="shortcut icon" href="./Images/favicon.ico">
<link rel="icon" href="./Images/animated_favicon1.gif" type="image/gif">

<link href="./css/standard.css" rel="stylesheet" type="text/css">
<link href="./css/other.css" rel="alternate stylesheet" title="<?= _("Other") ?>" type="text/css">

<?php
	foreach($PAGE_ELEMS as $elem)
		$elem->RenderHead();
?>
</head>
<body  leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<p class="version" align="right">v2.0 beta - Jan 2008</p>
<br>
<table>
<tr><td class="divleft">
	<?php require("PP_menu.inc.php");?>
	</td>
<td class="divright">
	<?php
	foreach($PAGE_ELEMS as $elem)
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