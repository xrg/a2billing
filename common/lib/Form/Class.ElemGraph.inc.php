<?php
require_once(DIR_COMMON."jpgraph_lib/jpgraph.php");

/** This class collects data from other ElemBase objects through
    their RenderSpecial().
   */
abstract class DataObj{
	public $code;
	public function DataObj($co){
		$this->code=$co;
	}
	abstract function debug($str);
	
};

class DataLegend {
	public $legend=array();
	
	public function Addlegend($ind, $value){
		$this->legend[$ind]=$value;
	}
	public function debug($str){
	}
};

/** Intermediate class for data that only has 2 or 3 dimensions */

abstract class DataObjXY extends DataObj{
	abstract function PlotXY($x,$y);
};

abstract class DataObjXYZ extends DataObj{
	abstract function PlotXYZ($x, $y, $z);
};

/** Debug version of parent, dumps the data */
class DataObjXY_d extends DataObjXY {
	public function PlotXY($x,$y){
		echo "x=$x, y=$y <br>\n";
	}
	public function debug($str){
		echo "$str<br>\n";
	}
};

class DataObjXYp extends DataObjXY {
	public $xdata=array();
	public $ydata=array();
	
	public function PlotXY($x,$y){
		$this->xdata[]=$x;
		$this->ydata[]=$y;
	}
	public function debug($str){
	}
	
	public function xdata_add_suffix($suffix, $sep = ''){
		for ($i=0 ; $i < count($this->xdata); $i++){
			if (is_array($suffix))
				$this->xdata[$i] .= $sep . $suffix[$i];
			else
				$this->xdata[$i] .= $sep . $suffix;
		}
	}
};

class DataObjXYZp extends DataObjXYZ {
	public $xdata=array();
	public $yzdata=array();
	public $zdata=array();
	
	public function PlotXYZ_($x, $y, $z){
		$this->xdata[]=$x;
		$this->yzdata[$z][]=$y;
	}
	public function PlotXYZ($x, $y, $z){
		if (empty($this->xdata) || (end($this->xdata) != $x))
			$this->xdata[] = $x; // $xkey = starttime
		if (!isset($this->yzdata[$z]))
			$this->yzdata[$z]=array();
		
		end($this->xdata);
		$this->yzdata[$z][key($this->xdata)] = $y;
		// print_r($this->yzdata);
		foreach ($this->yzdata as $zkey => $yzdata){
			if (!isset($this->yzdata[$zkey][key($this->xdata)]))
				$this->yzdata[$zkey][key($this->xdata)] = 0;
		} 
	}
			
	public function debug($str){
	}
	
	public function xdata_add_suffix($suffix, $sep = ''){
		for ($i=0 ; $i < count($this->xdata); $i++){
			if (is_array($suffix))
				$this->xdata[$i] .= $sep . $suffix[$i];
			else
				$this->xdata[$i] .= $sep . $suffix;
		}
	}
};

/** A view that renders itself into a graph.
   This view will call some other view of the form, in order to fetch
   the data from it (using its RenderSpecial).
*/
class GraphView extends FormView {
	public $view;
	public $code;
	public $parms = array();
		/** This will hold every style-related info. At first, the array
		is initted with some dummy default data, and then it will be overriden
		by setting the style of the graph */
	public $styles;
	public $gr_sty;
	
	function GraphView($vi, $co, $sty=null){
		$this->view=$vi;
		$this->code=$co;
		$this->gr_sty=$sty;
	}
	
	public function RenderHeaderGraph (&$form, &$robj){
	}
	
	
	public function RenderGraph (&$form, &$robj){
		// For debugging purposes
		$data = new DataObjXYp($this->code);
		print_r ($data);
	}
	
	/** Compute the stylesheet for this object. 
	    Unfortunately this has to be called later than the initializer, because
	    default GRAPH_STYLES are defined in PP_graph.inc.php, much later than $this.
	*/
	protected function apply_styles(){
		if (!empty($this->styles))
			return ; // already set, nothing to do.
			
		global $GRAPH_STYLES;

		$defaults = array( width => 500, height => 300, 
			setscale => 'textlin', xsetgrace => 3, ysetgrace => 3, 
			setframe => true, margin => array('35', '35', '15', '35'),
			rowcolor => false, backgroundgradient => false,
			colors =>array('red','blue','green','magenta','yellow'),
			'accumplot-options' => array (
						color => array ('yellow@0.3', 'purple@0.3', 'green@0.3', 'blue@0.3', 'red@0.3')));
		
		if (($this->gr_sty) && isset($GRAPH_STYLES[$this->gr_sty]))
			$sty2=$GRAPH_STYLES[$this->gr_sty];
		elseif (empty($this->gr_sty) && isset($GRAPH_STYLES[0]))
			$sty2=$GRAPH_STYLES[0];
		else	$sty2=array();
		
		$this->styles=array_merge($defaults,$sty2,$this->parms);
		
	}
	
	public function RenderSpecial($rmode,&$form, &$robj){
		$this->apply_styles();

		if ($rmode=='create-graph'){
			$this -> RenderHeaderGraph($form, $robj);
			$this -> RenderHeadSpecial($form, $robj);
		}
		elseif ($rmode=='graph'){
			$this -> RenderGraph($form, $robj);
		}
	}
	
	public function RenderHeadSpecial(&$form, &$robj){
		
		//print_r ($this->styles);
		
		if (!empty($this->styles['setscale']))
			$robj->SetScale($this->styles['setscale']);
		
		if (is_array($this->styles['margin']) && count($this->styles['margin'])==4)
			$robj->SetMargin($this->styles['margin'][0],$this->styles['margin'][1],$this->styles['margin'][2],$this->styles['margin'][3]);
		
		if (!$this->styles['setframe'])
			$robj->SetFrame(false);
		
		if (! empty($form->views[$this->view]->plots[$this->code]['title']))
			$robj->title->Set($form->views[$this->view]->plots[$this->code]['title']);
		
		if (! empty($form->views[$this->view]->plots[$this->code]['subtitles'])){
			$robj->tabtitle->Set($form->views[$this->view]->plots[$this->code]['subtitles']);
			$robj->tabtitle->SetWidth(TABTITLE_WIDTHFULL);
		}
		
		if ($this->styles['backgroundgradient'])
			if ($this->styles['backgroundgradient']['show'])
				if (is_array($this->styles['backgroundgradient']['params']) && count($this->styles['backgroundgradient']['params'])==4){
					$robj->SetBackgroundGradient($this->styles['backgroundgradient']['params'][0],
						$this->styles['backgroundgradient']['params'][1],
						$this->styles['backgroundgradient']['params'][2],
						$this->styles['backgroundgradient']['params'][3]);
				}
		
		if (!empty($this->styles['chart-options']['xsetgrace']))
			$robj->yaxis->scale->SetGrace($this->styles['chart-options']['xsetgrace']);
		
		if (!empty($this->styles['chart-options']['ysetgrace']))
			$robj->yaxis->scale->SetGrace($this->styles['chart-options']['ysetgrace']);
		
		if ($this->styles['chart-options']['xgrid'])
			if ($this->styles['chart-options']['xgrid']['show'])
				if (is_array($this->styles['chart-options']['xgrid']['params'])){
					if (is_array($this->styles['chart-options']['xgrid']['params']['fill']))
						$robj->xgrid->SetFill(true, $this->styles['chart-options']['xgrid']['params']['fill'][0], $this->styles['chart-options']['xgrid']['params']['fill'][1]);
					if (!empty($this->styles['chart-options']['xgrid']['params']['color']))
						$robj->xgrid->SetColor($this->styles['chart-options']['xgrid']['params']['color']);
					if (!empty($this->styles['chart-options']['xgrid']['params']['linestyle']))
						$robj->xgrid->SetLineStyle($this->styles['chart-options']['xgrid']['params']['linestyle']);
					$robj->xgrid->Show(true);
				} 
		if ($this->styles['chart-options']['ygrid'])
			if ($this->styles['chart-options']['ygrid']['show'])
				if (is_array($this->styles['chart-options']['ygrid']['params'])){
					if (is_array($this->styles['chart-options']['ygrid']['params']['fill']))
						$robj->ygrid->SetFill(true, $this->styles['chart-options']['ygrid']['params']['fill'][0], $this->styles['chart-options']['ygrid']['params']['fill'][1]);
					if (!empty($this->styles['chart-options']['ygrid']['params']['color']))
						$robj->ygrid->SetColor($this->styles['chart-options']['ygrid']['params']['color']);
					if (!empty($this->styles['chart-options']['ygrid']['params']['linestyle']))
						$robj->ygrid->SetLineStyle($this->styles['chart-options']['ygrid']['params']['linestyle']);
					$robj->ygrid->Show(true);
				}
		
	}

	/** For debugging purposes, this function simulates the 
	  graph procedure but only renders the results into html text */
	function Render(&$form){
		if(!$form->FG_DEBUG)
			return true;
		?>
	<div class="debug">
	Here we are: debugging FormDataView
	<br>
		<?php
			$graph=null;
			$this->RenderSpecial('create-graph',$form,$graph);
			if ($graph instanceof Graph)
				echo "Created a graph object <br>\n";
			unset($graph);
			echo "Using view ".$this->view.", code=".$this->code." <br>\n";
			if (!isset($form->views[$this->view])){
				echo "View doesn't exist!!\n";
				echo "</div>";
				return false;
			}
		?>
		</div>
		<div class="debug">
			Style:
			<?= nl2br(htmlspecialchars(print_r($this->styles,true))) ?>
		</div>
		<div class="debug">
		<?php
			$dobj=new DataObjXY_d($this->code);
			$form->views[$this->view]->RenderSpecial('get-data',$form,$dobj);
		?>
		</div>
	<?php
	}

};

class LineView extends GraphView {

	public function RenderHeaderGraph (&$form, &$robj){
		require_once(DIR_COMMON."jpgraph_lib/jpgraph_line.php");
		$robj = new Graph($this->styles['width'],$this->styles['height'],"auto");
	}
	
	public function RenderGraph (&$form, &$robj){
		
		$data = new DataObjXYp($this->code);
		$form->views[$this->view]->RenderSpecial('get-data',$form,$data);
		
		if (! empty($this->styles['chart-options']['xlabelangle'])){
			$robj->xaxis->SetLabelAngle($this->styles['chart-options']['xlabelangle']);
			if ($this->styles['chart-options']['xlabelangle']<0)
				$robj->xaxis->SetLabelAlign('left');
		}
		
		if (! empty($this->styles['chart-options']['xlabelfont']))
			$robj->xaxis->SetFont($this->styles['chart-options']['xlabelfont']);
		else
			$robj->xaxis->SetFont(FF_VERA);
		
		$robj->xaxis->SetTickLabels($data->xdata);
		
		$plot = new LinePlot($data->ydata);
		
		if (! empty($this->styles['plot-options']['setfillcolor']))
			$plot->SetFillColor($this->styles['plot-options']['setfillcolor']);
		if (! empty($this->styles['plot-options']['setcolor']))
			$plot ->SetColor($this->styles['plot-options']['setcolor']);
		
		$robj->Add($plot);	
	}

};

class BarView extends GraphView {

	public function RenderHeaderGraph (&$form, &$robj){
		require_once(DIR_COMMON."jpgraph_lib/jpgraph_bar.php");
		$robj = new Graph($this->styles['width'],$this->styles['height'],"auto");
	}
	
	public function RenderGraph (&$form, &$robj){
		
		$data = new DataObjXYp($this->code);
		$form->views[$this->view]->RenderSpecial('get-data',$form,$data);
		
		if (! empty($this->styles['chart-options']['xlabelangle'])){
			$robj->xaxis->SetLabelAngle($this->styles['chart-options']['xlabelangle']);
			if ($this->styles['chart-options']['xlabelangle']<0)
				$robj->xaxis->SetLabelAlign('left');
		}
		
		if (! empty($this->styles['chart-options']['xlabelfont']))
			$robj->xaxis->SetFont($this->styles['chart-options']['xlabelfont']);
		else
			$robj->xaxis->SetFont(FF_VERA);
		
		$robj->xaxis->SetTickLabels($data->xdata);
		
		$plot = new BarPlot($data->ydata);
		
		if (! empty($this->styles['plot-options']['setfillcolor']))
			$plot->SetFillColor($this->styles['plot-options']['setfillcolor']);
		if (! empty($this->styles['plot-options']['setcolor']))
			$plot ->SetColor($this->styles['plot-options']['setcolor']);
		
		$robj->Add($plot);
	}

};

// accumulated bar plots
class AccumBarView extends GraphView {

	public function RenderHeaderGraph (&$form, &$robj){
		require_once(DIR_COMMON."jpgraph_lib/jpgraph_bar.php");
		$robj = new Graph($this->styles['width'],$this->styles['height'],"auto");
	}
	
	public function RenderGraph (&$form, &$robj){
		
		$obj_leg = new DataLegend();
		$data = new DataObjXYZp($this->code);
		$form->views[$this->view]->RenderSpecial('get-data', $form, $data, $obj_leg);
		
		if (! empty($this->styles['chart-options']['xlabelangle'])){
			$robj->xaxis->SetLabelAngle($this->styles['chart-options']['xlabelangle']);
			if ($this->styles['chart-options']['xlabelangle']<0)
				$robj->xaxis->SetLabelAlign('left');
		}
		
		if (! empty($this->styles['chart-options']['xlabelfont']))
			$robj->xaxis->SetFont($this->styles['chart-options']['xlabelfont']);
		else
			$robj->xaxis->SetFont(FF_VERA);
		
		$robj->xaxis->SetTickLabels($data->xdata);
		
		$i=0; 
		foreach($data->yzdata as $ykey => $ycol){
			$accplots[]= new BarPlot($ycol);
			if (is_array($this->styles['accumplot-options']['color']))
				end($accplots)->SetFillColor($this->styles['accumplot-options']['color'][$i++]);
			if (!empty($obj_leg->legend[$ykey]))
				end($accplots)->SetLegend($obj_leg->legend[$ykey]);
			else
				end($accplots)->SetLegend(_("(none)"));
		}
		
		$plot = new AccBarPlot($accplots);
		
		if (! empty($this->styles['plot-options']['setfillcolor']))
			$plot->SetFillColor($this->styles['plot-options']['setfillcolor']);
		if (! empty($this->styles['plot-options']['setcolor']))
			$plot ->SetColor($this->styles['plot-options']['setcolor']);
		
		$robj->Add($plot);
	}

};

class PieView extends GraphView {

	public function RenderHeaderGraph (&$form, &$robj){
		require_once(DIR_COMMON."jpgraph_lib/jpgraph_pie.php");
		require_once(DIR_COMMON."jpgraph_lib/jpgraph_pie3d.php");
		$robj = new PieGraph($this->styles['width'],$this->styles['height'],"auto");
	}
	
	public function RenderGraph (&$form, &$robj){
		
		$data = new DataObjXYp($this->code);
		$form->views[$this->view]->RenderSpecial('get-data',$form,$data);
		
		$data->xdata_add_suffix ($data->ydata, ' : ');
		$data->xdata_add_suffix ($form->views[$this->view]->plots[$this->code]['ylabel']);
		
		$pieplot = new PiePlot3D($data->ydata);
		
		if (is_array($this->styles['pie-options'])){
			if (! empty($this->styles['pie-options']['explodeslice']))
				$pieplot->ExplodeSlice($this->styles['pie-options']['explodeslice']);
			if (! empty($this->styles['pie-options']['setcenter']))
				$pieplot->SetCenter($this->styles['pie-options']['setcenter']);
		}
		$pieplot->SetLegends(array_reverse($data->xdata));
		
		$robj->Add($pieplot);

	}
};
