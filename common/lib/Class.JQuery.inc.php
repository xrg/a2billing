<?php

/** A trivial class which will render the jquery includes. */
class JQueryHeaderElem extends ElemBase {
	
	public function Render(){
		//echo "<div>Jquery elem!</div>";
	}

	public function RenderHead() {
	?>
<script type="text/javascript" src="./javascript/jquery/jquery.js"></script>
<script type="text/javascript" src="./javascript/jquery/handler_jquery.js"></script>
<script type="text/javascript" src="./javascript/jquery/jtip.js"></script>

<?php 		if ($debug_jquery){ ?>
<script type="text/javascript" src="./javascript/jquery/jquery.debug.js"></script>
<script type="text/javascript" src="./javascript/jquery/ilogger.js"></script>
<?php
		}
	}
};

$PAGE_ELEMS[] = new JQueryHeaderElem();
?>
