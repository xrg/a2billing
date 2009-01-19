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
	
	/** Get the data from that (db) row into the object.
	   This is a generic way to parse a query result into
	   the internal arrays of this structure. */
	abstract function PlotRow(array $row);
	
	/** Returns if the fetch query should feed this with raw data, too */
	public function NeedRaw() {
		return false;
	}
	
	/** Initialize some internal structures according to plot, query rows 
	    \param plot an array with plot settings. 'x', 'y' will be used
	    \param res the provided query fields. An array with string names
	            of each query row field.
	  */
	abstract function prepare(array $plot, array $resfs);
	
};

/** Store in array the diff of x, end(array), by date mode

   This helps in date graphs, where we don't need to repeat the date
   all the time (noisy).
*/
function putdiffX(array &$data, $new,$dateMode){

	if ($dateMode)
		$new = trim($new);

	if ($dateMode && !empty($data)){
		$lastdate = null;
		for($i = count($data);$i>0; $i--)
			if (($p = strpos($data[$i-1],' '))!==false){
				$lastdate = substr($data[$i-1],0,$p);
				break;
			}
		
		if (!empty($lastdate)){
			if (substr($new,0, strlen($lastdate)) == $lastdate)
				$new= substr($new,strlen($lastdate)+1);
		}
		
	}

	$data[] = $new;
}

/** Intermediate class for data that only has 2 or 3 dimensions */
abstract class DataObjXY extends DataObj{
	protected $xkey;
	protected $xrkey;
	protected $ykey;
	protected $yrkey;

	public function prepare(array $plot, array $resfs){
		$this->xrkey = $this->xkey = $plot['x'];
		$this->yrkey = $this->ykey = $plot['y'];
		
		if (empty($this->xkey) || empty($this->ykey))
			throw new Exception('Cannot locate x/y keys in plot settings');
		if (!in_array($this->xkey,$resfs))
			throw new Exception('Query row doesn\'t have x data');
		if (!in_array($this->ykey,$resfs))
			throw new Exception('Query row doesn\'t have y data');
		
		if (in_array($this->xkey.'_raw',$resfs))
			$this->xrkey=$this->xkey.'_raw';
		if (in_array($this->ykey.'_raw',$resfs))
			$this->yrkey=$this->ykey.'_raw';
	}
};

abstract class DataObjX2Y extends DataObj{
	protected $xkey;
	protected $xrkey;
	protected $ykey;
	protected $yrkey;
	protected $x2key;
	protected $x2rkey;
	

	public function prepare(array $plot, array $resfs){
		$this->xrkey = $this->xkey = $plot['x'];
		$this->yrkey = $this->ykey = $plot['y'];
		$this->x2rkey = $this->x2key = $plot['x2'];
		if (isset($plot['x2t']))
			$this->x2key=$plot['x2t'];
		
		if (empty($this->xkey) || empty($this->x2key)|| empty($this->ykey))
			throw new Exception('Cannot locate x/y keys in plot settings');
		if (!in_array($this->xkey,$resfs))
			throw new Exception('Query row doesn\'t have x data');
		if (!in_array($this->ykey,$resfs))
			throw new Exception('Query row doesn\'t have y data');
		
		if (in_array($this->xkey.'_raw',$resfs))
			$this->xrkey=$this->xkey.'_raw';
		if (in_array($this->ykey.'_raw',$resfs))
			$this->yrkey=$this->ykey.'_raw';
	}
};

/** Debug version of parent, dumps the data */
class DataObjXY_d extends DataObjXY {
	public function PlotRow(array $row){
		echo 'x='.htmlspecialchars($row[$this->xkey]).
		    ', y='.htmlspecialchars($row[$this->ykey])." <br>\n";
	}
	public function debug($str){
		echo "$str<br>\n";
	}
};

/** Debug version of parent, dumps the data */
class DataObjX2Y_d extends DataObjX2Y {
	public function PlotRow(array $row){
		echo 'x='.htmlspecialchars($row[$this->xkey]).
		     ', x2='.htmlspecialchars($row[$this->x2key]).
		    ', y='.htmlspecialchars($row[$this->ykey])." <br>\n";
	}
	public function debug($str){
		echo "$str<br>\n";
	}
};


class DataObjXYp extends DataObjXY {
	public $xdata=array();
	public $xrdata=array();
	public $ydata=array();
	public $yrdata=array();
	
	public function NeedRaw() {
		return true;
	}

	public function PlotRow(array $row){
		$this->xdata[]=$row[$this->xkey];
		$this->ydata[]=$row[$this->ykey];
		$this->xrdata[]=$row[$this->xrkey];
		$this->yrdata[]=$row[$this->yrkey];
	}
	public function debug($str){
	}
};

/** Class for data that only has many Y columns on one X */
abstract class DataObjXYm extends DataObj{
	protected $xkey;
	protected $xrkey;
	protected $ykeys;
	protected $yrkeys;

	public function prepare(array $plot, array $resfs){
		$this->xrkey = $this->xkey = $plot['x'];
		$this->yrkeys = $this->ykeys = $plot['yr'];
		
		if (empty($this->xkey) || empty($this->ykeys))
			throw new Exception('Cannot locate x/y keys in plot settings');
		if (!in_array($this->xkey,$resfs))
			throw new Exception('Query row doesn\'t have x data');
		if (in_array($this->xkey.'_raw',$resfs))
			$this->xrkey=$this->xkey.'_raw';
			
		foreach ($this->ykeys as $n => $key){
			if (!in_array($key,$resfs))
				throw new Exception('Query row doesn\'t have y data for '.$key);
			
			if (in_array($key.'_raw',$resfs))
				$this->yrkeys[$n]=$key.'_raw';
		}
	}
};

/** A data object which holds sets of y-data in x2 groups
*/
class DataObjX2Yp extends DataObjX2Y {
	public $xdata=array();
	public $xrdata=array();
	public $yzdata=array();
	public $x2data=array(); // assoc x2r ->x2 title
	public $dateMode = False;
		
	public function NeedRaw() {
		return true;
	}

	public function PlotRow(array $row){
		
		if (empty($this->xrdata) || (end($this->xrdata) != $row[$this->xrkey])){
			$this->xrdata[] = $row[$this->xrkey];
			putdiffX($this->xdata,$row[$this->xkey],$this->dateMode);
		}
		if (!isset($this->yzdata[$row[$this->x2rkey]])){
			$this->yzdata[$row[$this->x2rkey]]=array();
			$this->x2data[$row[$this->x2rkey]]=$row[$this->x2key];
		}
	
		end($this->xdata);
		$this->yzdata[$row[$this->x2rkey]][key($this->xdata)] = $row[$this->yrkey];

		foreach ($this->yzdata as $zkey => $yzdata){
			if (!isset($this->yzdata[$zkey][key($this->xdata)]))
				$this->yzdata[$zkey][key($this->xdata)] = 0;
		}

	}

	public function debug($str){
	}
	
};

class DataObjXYmp extends DataObjXYm {
	public $xdata=array();
	public $xrdata=array();
	public $ydata=null;
	public $yrdata=null;
	public $dateMode = False;
	
	public function NeedRaw() {
		return true;
	}

	public function PlotRow(array $row){
		putdiffX($this->xdata,$row[$this->xkey],$this->dateMode);
		$this->xrdata[]=$row[$this->xrkey];
		
		if ($this->ydata==null){
			$this->ydata=array();
			$this->yrdata=array();
			foreach($this->ykeys as $n => $key){
				$this->ydata[$n]=array();
				$this->yrdata[$n]=array();
			}
		}
		
		foreach($this->ykeys as $n => $key){
			$this->ydata[$n][]=$row[$key];
			$this->yrdata[$n][]=$row[$this->yrkeys[$n]];
		}
	}
	public function debug($str){
	}
};

class DataObjXYm_d extends DataObjXYm {
	private $first_row=false;
	public function PlotRow(array $row){
		/*if (!$this->first_row){
			echo " Ykeys: ".print_r($this->ykeys,true) .
				", yrkeys=". print_r($this->yrkeys,true) ."<br>\n";
			$this->first_row=true;
		}*/
		
		echo 'x='.htmlspecialchars($row[$this->xkey]);
		foreach($this->ykeys as $n => $key)
			echo ", y".$n."=" . htmlspecialchars($row[$key]);
		echo " <br>\n";
	}
	public function debug($str){
		echo "$str<br>\n";
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
			rowcolor => false, bggradient => false,
			maxxticks => 20,
			colors =>array('red','blue','green','magenta','yellow'),
			'accumplot-options' => array (
				color => array ('yellow@0.3', 'purple@0.3', 'green@0.3', 'blue@0.3', 'red@0.3')));
		
		if (!empty($this->gr_sty) && isset($GRAPH_STYLES[$this->gr_sty]))
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
		
		if (! empty($form->views[$this->view]->plots[$this->code]['title'])){
			$robj->title->Set($form->views[$this->view]->plots[$this->code]['title']);
			$robj->title->SetFont(FF_DEJAVU);
		}
		
		if (! empty($form->views[$this->view]->plots[$this->code]['subtitles'])){
			$robj->tabtitle->Set($form->views[$this->view]->plots[$this->code]['subtitles']);
			$robj->tabtitle->SetWidth(TABTITLE_WIDTHFULL);
			$robj->tabtitle->SetFont(FF_DEJAVU);
		}
		
		if ($this->styles['bggradient'])
			if ($this->styles['bggradient']['show'])
				if (is_array($this->styles['bggradient']['params']) && count($this->styles['bggradient']['params'])==4){
					$robj->SetBackgroundGradient($this->styles['bggradient']['params'][0],
						$this->styles['bggradient']['params'][1],
						$this->styles['bggradient']['params'][2],
						$this->styles['bggradient']['params'][3]);
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
		$this->apply_styles();
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
			if ($this instanceof AccumBarView)
				$dobj=new DataObjX2Y_d($this->code);
			else if ($this instanceof Line2View)
				$dobj=new DataObjXYm_d($this->code);
			else
				$dobj=new DataObjXY_d($this->code);
			$form->views[$this->view]->RenderSpecial('get-data',$form,$dobj);
		?>
		Render end.
		</div>
	<?php
	}

};

class LineView extends GraphView {

	public function RenderHeaderGraph (&$form, &$robj){
		require_once(DIR_COMMON."jpgraph_lib/jpgraph_line.php");
		$robj = new Graph($this->styles['width'],$this->styles['height'],"auto");
		$robj->legend->SetFont(FF_DEJAVU);
	}
	
	public function RenderGraph (&$form, &$robj){
		
		$data = new DataObjXYp($this->code);
		$form->views[$this->view]->RenderSpecial('get-data',$form,$data);
		
		if (! empty($this->styles['chart-options']['xlabelangle'])){
			$robj->xaxis->SetLabelAngle($this->styles['chart-options']['xlabelangle']);
			if ($this->styles['chart-options']['xlabelangle']<0)
				$robj->xaxis->SetLabelAlign('left');
		}
		
		{
			$font=$this->styles['chart-options']['xlabelfont'];
			if (empty($font))
				$font = FF_DEJAVU;
			$fontstyle= $this->styles['chart-options']['xlabelfontstyle'];
			if (empty($fontstyle))
				$fontstyle = FS_NORMAL;
			$fontsize= $this->styles['chart-options']['xlabelfontsize'];
			if (empty($fontsize))
				$fontsize = 8;
		
			$robj->xaxis->SetFont($font,$fontstyle,$fontsize);
		}
		
		$robj->xaxis->SetTickLabels($data->xdata);
		if (count($data->xdata)> $this->styles['maxxticks']+5)
			$robj->xaxis->SetTextTickInterval(count($data->xdata)/$this->styles['maxxticks']);
		
		$plot = new LinePlot($data->yrdata);
		// TODO: use ydata for y-labels
		
		if (! empty($this->styles['plot-options']['setfillcolor']))
			$plot->SetFillColor($this->styles['plot-options']['setfillcolor']);
		if (! empty($this->styles['plot-options']['setcolor']))
			$plot ->SetColor($this->styles['plot-options']['setcolor']);
		
		$robj->Add($plot);
	}

};

/** Multiple lines in one graph */
class Line2View extends GraphView {

	public function RenderHeaderGraph (&$form, &$robj){
		require_once(DIR_COMMON."jpgraph_lib/jpgraph_line.php");
		$robj = new Graph($this->styles['width'],$this->styles['height'],"auto");
		$robj->legend->SetFont(FF_DEJAVU);
	}
	
	public function RenderGraph (&$form, &$robj){
		$data = new DataObjXYmp($this->code);
		if ($form->views[$this->view]->plots[$this->code]['x_datemode'])
			$data->dateMode=True;
		$form->views[$this->view]->RenderSpecial('get-data',$form,$data);
		
		$robj->xaxis->SetPos('auto');
		if (! empty($this->styles['chart-options']['xlabelangle'])){
			$robj->xaxis->SetLabelAngle($this->styles['chart-options']['xlabelangle']);
			if ($this->styles['chart-options']['xlabelangle']<0)
				$robj->xaxis->SetLabelAlign('left');
		}
		
		{
			$font=$this->styles['chart-options']['xlabelfont'];
			if (empty($font))
				$font = FF_DEJAVU;
			$fontstyle= $this->styles['chart-options']['xlabelfontstyle'];
			if (empty($fontstyle))
				$fontstyle = FS_NORMAL;
			$fontsize= $this->styles['chart-options']['xlabelfontsize'];
			if (empty($fontsize))
				$fontsize = 8;
		
			$robj->xaxis->SetFont($font,$fontstyle,$fontsize);
		}
		
		$robj->xaxis->SetTickLabels($data->xdata);
		if (count($data->xdata)> $this->styles['maxxticks']+5)
			$robj->xaxis->SetTextTickInterval(count($data->xdata)/$this->styles['maxxticks']);
		
		$fillcols= $this->styles['plot-options']['fillcolors'];
		if (empty($fillcols))
			$fillcols=array();
			
		$colors=$this->styles['plot-options']['linecolors'];
		if (empty($colors))
			$colors=array();
		
		$bplots=array();
		$yrkeys=$form->views[$this->view]->plots[$this->code]['yr'];
		foreach ($yrkeys as $n => $key){
			$bplots[] = new LinePlot($data->yrdata[$n]/*,$data->xrdata*/);
			// TODO: use ydata for y-labels
			if(!empty($fillcols[$n]))
				end($bplots)->SetFillColor($fillcols[$n]);
			if(!empty($colors[$n]))
				end($bplots)->SetColor($colors[$n]);
			//TODO: legend
			
			$robj->Add(end($bplots));
		}

	}

};

class BarView extends GraphView {

	public function RenderHeaderGraph (&$form, &$robj){
		require_once(DIR_COMMON."jpgraph_lib/jpgraph_bar.php");
		$robj = new Graph($this->styles['width'],$this->styles['height'],"auto");
		$robj->legend->SetFont(FF_DEJAVU);
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
			$robj->xaxis->SetFont(FF_DEJAVU);
		
		$robj->xaxis->SetTickLabels($data->xdata);
		
		$plot = new BarPlot($data->yrdata);
		// TODO: use ydata for labels
		
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
		$robj->legend->SetFont(FF_DEJAVU);
	}
	
	public function RenderGraph (&$form, &$robj){
		
		$data = new DataObjX2Yp($this->code);
		$form->views[$this->view]->RenderSpecial('get-data', $form, $data);
		
		if (! empty($this->styles['chart-options']['xlabelangle'])){
			$robj->xaxis->SetLabelAngle($this->styles['chart-options']['xlabelangle']);
			if ($this->styles['chart-options']['xlabelangle']<0)
				$robj->xaxis->SetLabelAlign('left');
		}
		
		if (! empty($this->styles['chart-options']['xlabelfont']))
			$robj->xaxis->SetFont($this->styles['chart-options']['xlabelfont']);
		else
			$robj->xaxis->SetFont(FF_DEJAVU);
		
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
		$robj->legend->SetFont(FF_DEJAVU);
	}
	
	public function RenderGraph (&$form, &$robj){
		
		$data = new DataObjXYp($this->code);
		$form->views[$this->view]->RenderSpecial('get-data',$form,$data);
		
		$pieplot = new PiePlot3D($data->yrdata);
		
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
