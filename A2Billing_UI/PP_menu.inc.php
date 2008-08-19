<?php 
require_once ("lib/defines.php");
require_once ("lib/module.access.php");

	//$section = "";  No, it's specified inside the container entity
// 	if($_GET["section"]!="")
// 	{
// 		$section = $_GET["section"];		
// 	}

?>
<script language="JavaScript">
<!--
var mywin
var prevdiv="dummydiv"
function imgidclick(imgID,divID)
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
			document.all(imgID).src="./Images/minus.png";
		}
		else
		{			
			document.all(divID).style.display="None";
			document.all(imgID).src="./Images/plus.png";
		}
		// Only for I.E
		window.event.cancelBubble=true;
	}else{
		if 	(document.getElementById(divID).style.display == "none" )
		{
			document.getElementById(divID).style.display="";
			document.getElementById(imgID).src="./Images/minus.png";
		}
		else
		{			
			document.getElementById(divID).style.display="None";
			document.getElementById(imgID).src="./Images/plus.png";
		}
	}
}

function menu_toggle(sect_str){
	//elmnt.parent.style.visibility="hidden";
	//alert(elmnt.style.visibility);
	var sect=document.getElementById(sect_str);
	var dom_ul=sect.getElementsByTagName("ul")[0];
	if (dom_ul.style.display=="inline")
		dom_ul.style.display="none";
	else
		dom_ul.style.display="inline";
}

function menu_show(sect_str){
	//elmnt.parent.style.visibility="hidden";
	//alert(elmnt.style.visibility);
	var sect=document.getElementById(sect_str);
	var dom_ul=sect.getElementsByTagName("ul")[0];
	dom_ul.style.display="inline";
}

//-->
</script>

<div id="dummydiv"></div>

<div id="menu" class="menu" >
	
	<div>
		<a href="PP_intro.php"><?= _("HOME") ?></a>
	</div>
<?php
foreach (glob("menu/*.menu.inc.php") as $file) {
	// remember the { and } are necessary!
        include $file;
}
?>
	<div><a style="color: #DD0000; font-weight: bold;" href="logout.php?logout=true" target="_top"><?= gettext("LOGOUT");?></a></div>

</div>
<br>
<table>
<!--<tr>
	<td>
		<a href="index2.php?language=english" target="_parent"><img src="./Images/flags/us.png" border="0" title="English" alt="English"></a>
	</td>
</tr>-->
</table>
<script>
menu_show( '<?= $menu_section ?>',true);
</script>
