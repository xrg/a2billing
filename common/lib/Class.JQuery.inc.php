<?php

/** A trivial class which will render the jquery includes. */
class JQueryHeaderElem extends ElemBase {
	
	public function Render(){
		//echo "<div>Jquery elem!</div>";
	}

	public function RenderHead() {
		if (isset($GLOBALS['PAGE_BARE']) && $GLOBALS['PAGE_BARE'])
			return;
	?>

<script type="text/javascript" src="./javascript/jquery/jquery-1.2.3.pack.js"></script>
<script type="text/javascript" src="./javascript/jquery/handler_jquery.js"></script>
<script type="text/javascript" src="./javascript/jquery/jtip.js"></script>
<script type="text/javascript" src="./javascript/jquery/ui.tabs.pack.js"></script>
<script type="text/javascript" src="./javascript/jquery/ui.tabs.ext.pack.js"></script>
<script type="text/javascript">
    $(function() {
        $('#rotate > ul').tabs({ fx: { opacity: 'toggle' } });
    });
</script>  

<?php if ($debug_jquery){ ?>
<script type="text/javascript" src="./javascript/jquery/jquery.debug.js"></script>
<script type="text/javascript" src="./javascript/jquery/ilogger.js"></script>
<?php
		}
	}
};

$PAGE_ELEMS[] = new JQueryHeaderElem();
?>
