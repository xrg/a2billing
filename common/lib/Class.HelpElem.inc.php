<?php
require_once("Class.ElemBase.inc.php");

	/** Help element
	    Display a 'help' section on a html page
        */


class HelpElem extends ElemBase{
	public $icon = 'vcard.png';
	public $text = 'No help available!';
	
	public function setIcon($ico){
		$this->icon=$ico;
	}
	
	public function setText($txt){
		$this->text = $txt;
	}
	
	/** One-stop fn to provide help
		Only call this, and all necessary fields will be filled.
		Note, that the position of the help section within the html page
		will depend on the order this fn() is called.
	*/
	static public function DoHelp($text, $icon=null){
		global $PAGE_ELEMS;
		$help_elem = new self();
		//$help_elem->setIcon('vcard.png');
		$help_elem->setText($text);
		$PAGE_ELEMS[] = &$help_elem;
	}
	
	public function Render(){
		if (isset($GLOBALS['FG_DEBUG']) &&$GLOBALS['FG_DEBUG'] > 1)
			return;
		?>
	<a href="#" target="_self" onclick="helpElemClick('img1000','div1000');"><img id="img1000" src="./Images/kicons/viewmag.png" onmouseover="this.style.cursor='hand';" width="16" height="16"></a>
	<div id="div1000" style="display:visible;">
		<div id="kiki"><div class="w1">
			<img src="./Images/kicons/<?= $this->icon ?>" class="kikipic"/>
			<div class="w2">
				<?= $this->text ?>
				<br/><br/>
			</div>
		</div> </div>
	</div>
	<br style="clear:both">
	<?php
	}
	
	public function RenderHead(){
		// Entering head code..!
	?>
	<script language="JavaScript">
<!--
var mywin
var prevdiv="dummydiv"
function helpElemClick(imgID,divID)
{
	
	var agt=navigator.userAgent.toLowerCase();
    // *** BROWSER VERSION ***
    // Note: On IE5, these return 4, so use is_ie5up to detect IE5.
    var is_major = parseInt(navigator.appVersion);
    var is_minor = parseFloat(navigator.appVersion);

    // Note: Opera and WebTV spoof Navigator.  We do strict client detection.
    // If you want to allow spoofing, take out the tests for opera and webtv.
    var is_nav  = ((agt.indexOf('mozilla')!=-1) && (agt.indexOf('spoofer')==-1)
                && (agt.indexOf('compatible') == -1) && (agt.indexOf('opera')==-1)
                && (agt.indexOf('webtv')==-1) && (agt.indexOf('hotjava')==-1));
	var is_ie     = ((agt.indexOf("msie") != -1) && (agt.indexOf("opera") == -1));
	
	
	if (is_ie){			
		if 	(document.all(divID).style.display == "none" )
		{		
			document.all(divID).style.display="";
			document.all(imgID).src="./Images/kicons/viewmag.png";
		}
		else
		{			
			document.all(divID).style.display="None";
			document.all(imgID).src="./Images/kicons/xmag.png";
		}
		window.event.cancelBubble=true;
	}else{
		if 	(document.getElementById(divID).style.display == "none" )
		{			
			document.getElementById(divID).style.display="";
			document.getElementById(imgID).src="./Images/kicons/viewmag.png";
		}
		else
		{			
			document.getElementById(divID).style.display="None";
			document.getElementById(imgID).src="./Images/kicons/xmag.png";
		}
	}
}
//-->
</script>
<?php
	}
};

?>