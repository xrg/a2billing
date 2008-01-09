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

<link href="./css/print.css" rel="stylesheet" type="text/css">

<?php
	foreach($PAGE_ELEMS as $elem)
		$elem->RenderHead();
?>
</head>
<body  leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<?php
foreach($PAGE_ELEMS as $elem)
	$elem->Render();
?>
</body>
</html>