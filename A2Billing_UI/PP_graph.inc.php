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

$GRAPH_STYLES[0] = array( width => 400, height => 250, xlabelangle => -45,
	rowcolor => false, backgroundgradient => false, setframe => false);

// day-line
$GRAPH_STYLES['style-chart'] = array( width => 500, height => 300, xlabelangle => -45,
	setframe => false,
	xgrid => array (
		show => true, color => 'gray@0.5', linestyle => 'dashed',
		params=> array( fill => array('#FFFFFF@0.5','#CDDEFF@0.7') ) ),
	ygrid => array (
		show => true, color => 'gray@0.5', linestyle => 'dashed',
		params=> array( fill => array('#FFFFFF@0.5','#CDDEFF:1.1') ) ),
	backgroundgradient => array (show=>true, params=> array('#FFFFFF','#CDDEFF:1.1',GRAD_HOR,BGRAD_MARGIN))
	);

$GRAPH_STYLES['style-pie'] = array( width => 600, height => 350, xlabelangle => -45,
	rowcolor => true, 
	backgroundgradient_params => array ('#FFFFFF','#CDDEFF:1.1',GRAD_VER,BGRAD_PLOT),
	setframe => false);


// First pass: create graph
$graph=null;
foreach ($PAGE_ELEMS as $pe)
	$pe->RenderSpecial('create-graph',$graph);

// Second pass, fill graph
foreach ($PAGE_ELEMS as $pe)
	$pe->RenderSpecial('graph',$graph);

$graph->stroke();
?>