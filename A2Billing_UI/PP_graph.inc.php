<?php
/*
require_once(DIR_COMMON."jpgraph_lib/jpgraph_line.php");
*/
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

$GRAPH_STYLES[0] = array( width => 500, height => 300, xlabelangle => -45,
	rowcolor => true, backgroundgradient => true, setframe => false);

// First pass: create graph
$graph=null;
foreach ($PAGE_ELEMS as $pe)
	$pe->RenderSpecial('create-graph',$graph);

// Second pass, fill graph
foreach ($PAGE_ELEMS as $pe)
	$pe->RenderSpecial('graph',$graph);

$graph->stroke();
?>