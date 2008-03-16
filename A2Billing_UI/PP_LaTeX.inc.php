<?php

/** This Script will produce a LaTeX document, based on variables already defined.
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
	Redirecting to: <?= $res ?>
<?php
		exit();
		}
		Header('Location: ' . $res);
		exit();
	}
}

header('Content-type: text/x-latex');
?>
\document{article}
<?php
	$robj = null;
	foreach($PAGE_ELEMS as $elem)
		$elem->RenderHeadSpecial('LaTeX',$robj);
?>
\begin{document}
% A2Billing version v2.0 beta - Mar 2008
<?php
	$robj = null;
	foreach($PAGE_ELEMS as $elem)
		$elem->RenderSpecial('LaTeX',$robj);
	?>
\end{document}
