<?php
require_once(DIR_COMMON."jpgraph_lib/jpgraph.php");
require_once(DIR_COMMON."jpgraph_lib/jpgraph_line.php");
require_once(DIR_COMMON."jpgraph_lib/jpgraph_bar.php");
// include_once(dirname(__FILE__) . "/jpgraph_lib/jpgraph_line.php");
// include_once(dirname(__FILE__) . "/jpgraph_lib/jpgraph_bar.php");

if (! isset($PAGE_ELEMS))
	$PAGE_ELEMS=array();
else if ($FG_DEBUG)
	foreach($PAGE_ELEMS as $pe)
		if (! ($pe instanceof ElemBase)){
			error_log('Page element not canonical.');
			die();
		}

$graph = new Graph(600,450);
$graph->SetMargin(40,40,45,90);
$graph->SetFrame(false);

foreach ($PAGE_ELEMS as $pe)
	if ($pe->RenderGraph($graph))
		break;

if ($FG_DEBUG)
	echo "Stroke!";
else
	$graph->Stroke();

?>