<?php
include ("lib/defines.php");
include ("lib/module.access.php");

$USE_AJAX=1 ;

include ("PP_header.php");

if (! has_rights (ACX_ACCESS)){
	   Header ("HTTP/1.0 401 Unauthorized");
	   Header ("Location: PP_error.php?c=accessdenied");
	   die();
}

$refills = explode("|",AGENT_REFILLS);

?>
<script type="text/javascript">
var req_timer = false;
var call_obj = false;
function reqStateChanged2(){

	var resp ="";
	if (req_timer){
		clearTimeout(req_timer);
		req_timer=false;
		}
	switch (http_request.readyState) {
	case 1:
		resp="<?= _("Open"); ?>";
		break;
	case 2:
		resp="<?= _("Waiting for response");?>";
		break;
	case 3:
		resp="<?= _("Receiving")?>";
		break;
	case 4:
		if (call_obj){
			call_obj.window.close();
			call_obj=false;
		}
		switch(http_request.status) { // HTTP response codes..
		case 200:
			resp="OK";
			//document.getElementById("result_f").innerHTML=http_request.responseText;
			if (parseBoothXML(http_request.responseXML))
				req_timer=setTimeout("startRequest(\"booths.xml.php\",reqStateChanged2);",5*60*1000); 
			break;
		default:
			resp="Code: " + http_request.status;
		}
		break;
	default:
		resp="Unknown resp" +  http_request.readyState;
	}

	document.getElementById("response").innerHTML= resp;
}

function my_getHtmlElementById(dom,id){
	var elems = dom.getElementsByTagName("*");
	for (var i=0;i<elems.length;i++)
		if (elems[i].id==id)
		return (elems[i]);
	throw "Var " + dom.nodeName + " has no  descendant \"" + id + "\".";
}
function parseBoothXML(the_xml){
	var xml_dom= the_xml.getElementsByTagName("root").item(0);
	var xml_msg= xml_dom.getElementsByTagName("message").item(0);
	
	try {
		if (xml_msg.firstChild.nodeValue!= undefined)
			document.getElementById("message").innerHTML= xml_msg.firstChild.nodeValue;
	}
	catch (err){
		//alert(err); //debugging..
		window.status=xml_msg.textContent;
	}
	
	var booths=xml_dom.getElementsByTagName("booth");
	var booth_tags= new Array();
	i=0;
	booth_tags[i++]="name";
	booth_tags[i++]="status";
	booth_tags[i++]="credit";
	booth_tags[i++]="mins";
	booth_tags[i++]="button_sta";
	booth_tags[i++]="button_stp";
	booth_tags[i++]="button_en";
	booth_tags[i++]="button_dis";
	booth_tags[i++]="button_unl";
	booth_tags[i++]="button_ld";
	booth_tags[i++]="button_lr";
	//booth_tags[i++]="button_ln";
	booth_tags[i++]="button_emp";
	booth_tags[i++]="button_pay";
	booth_tags[i++]="refill";
	
	for (var i=0 ; i < booths.length; i++){
		var xml_booth=booths[i];
		try {
			dom_booth=document.getElementById(xml_booth.getAttribute("id"));
			//alert(typeof(dom_booth));
			for( vtag_x in booth_tags){
				vtag=booth_tags[vtag_x];
				var xml_obj=xml_booth.getElementsByTagName(vtag).item(0);
				if (xml_obj==undefined)
					continue;
				
				var xml_child=xml_obj.firstChild;
				dom_child=my_getHtmlElementById(dom_booth,vtag);
				while(xml_child !=null){
					if(xml_child.nodeType==3){
						dom_child.innerHTML=xml_child.nodeValue;
					}else if (xml_child.nodeType==2){
						alert("type 2");
// 						if (xml_child.nodeName == "class")
// 							dom_child.className=xml_child.nodeValue;
// 						dom_child.innerHTML=dom_child.innerHTML+ "class: " + xml_child.nodeValue;
					}
					
					xml_child=xml_child.nextSibling;
				}
				if (xml_obj.hasAttribute("class"))
					dom_child.className= xml_obj.getAttribute("class");
					
				if (xml_obj.hasAttribute("display"))
					dom_child.setAttribute("style","display: "+xml_obj.getAttribute("display"));
				
			}
		}catch(err){
			alert(err); //debugging..
			//alert(typeof(dom_booth))
			return false;
		}
	}
	return true;
}

function booth_action(booth,act) {
	startRequest("booths.xml.php"+"?action="+act + "&actb=" + booth,reqStateChanged2);
}

function booth_action2(booth,act,str2,co) {
	call_obj=co;
	startRequest("booths.xml.php"+"?action="+act + "&actb=" + booth + str2,reqStateChanged2);
}

function select_regular(booth) {
	//alert( "Select regular customer for booth " + booth );
	window.open( "A2B_entity_cards.php?popup_select=freg&booth=" + booth);
}


function refill(booth,sum) {
	startRequest("booths.xml.php"+"?action=refill&actb=" + booth + "&sum=" + sum,reqStateChanged2);
}

window.onload = function() { startRequest("booths.xml.php",reqStateChanged2)};
</script>

<?php
	/** These states have to match the SQL logic */
	
	$DBHandle  = DbConnect();
	
	$QUERY="SELECT id FROM cc_booth_v WHERE def_card_id IS NOT NULL AND owner = " . trim($_SESSION["agent_id"]) . " ORDER BY id;";
		
	$res = $DBHandle -> query($QUERY);

	if (!$res){
?>
	<br></br>
            <table width="460" border="2" align="center" cellpadding="1" cellspacing="2" bordercolor="#eeeeff" bgcolor="#FFFFFF">
		<tr bgcolor=#4e81c4>
			<td>
			<div align="center"><b><font color="white" size=5><?= gettext("Error Page");?></font></b></div>
			</td>
		</tr>
		<tr>
			<td align="center" colspan=2>
                    <table width="100%" border="0" cellpadding="5" cellspacing="5">
                      <tr>
                        <td align="center"><br/>
				<img src="./Css/kicons/system-config-rootpassword.png">
				<br/>
				<b><font color=#3050c2 size=4><?= gettext("Cannot locate booths") ?></font></b><br/><br/><br/></td>
                      </tr>
                    </table>
		</td>
              </tr>
            </table>
	<?php
	} else {

		$ndiv = $A2B->config["agentcustomerui"]['password'];
		if (!is_int($ndiv) || ($ndiv < 1) || ($ndiv>20))
			$ndiv=4;
		
		$num = $res -> numRows();
		if ($num==0) {
			echo gettext("No booths!<br>Please ask the administrators to create you some.");
		}
		else {
		?>
		<!--Lang: <?=setlocale(LC_MESSAGES,0); ?>-->
		<div id="message"><?= _("Welcome!"); ?> </div>
		<br>
		<TABLE class='Booths' border=0 cellPadding=2 cellSpacing=2 width="100%">
		<TBODY>
		<?php
			$colwidth= 95/$ndiv;
			for($i=0;$i<$num;$i++)
			{
				$row = $res -> fetchRow();
				if ( $i % $ndiv == 0)
					echo "<tr>\n";

				echo "<td width='" . $colwidth . "%' id=\"booth_" . $row[0] . "\" >";
				?><table class="Booth" cellPadding=2 cellSpacing=2><tbody>
				<tr><td id="name" class="name" colspan=3>Booth X</td></tr>
				<tr><td id="status" class="state0" colspan=3> -- </td></tr>
				<tr><td><?=gettext("Credit:");?></td><td id="credit"> </td><td id="mins"></td></tr>
				<tr><td id="buttons" class="buttons" colspan=2> 
				<a href="javascript:booth_action(<?=$row[0]?>,'start');" id='button_sta' style='color:green;'><?=gettext("Start"); ?></a>
				<a href="javascript:booth_action(<?=$row[0]?>,'stop');" id='button_stp' style='color:red;'><?=gettext("Stop"); ?></a>
				<a href="invoices_cust.php?booth=<?=$row[0]?>&nobq=1" id='button_pay' style='color:blue;'><?=gettext("Pay"); ?></a>
				<a href="javascript:booth_action(<?=$row[0]?>,'enable');" id='button_en'><?=gettext("Enable"); ?></a>
				<a href="javascript:booth_action(<?=$row[0]?>,'disable');" id='button_dis'><?=gettext("Disable"); ?></a>
				<a href="javascript:booth_action(<?=$row[0]?>,'unload');" id='button_unl'><?=gettext("Unload"); ?></a>
				<a href="javascript:booth_action(<?=$row[0]?>,'load_def');" id='button_ld'><?=gettext("One-Time"); ?></a>
				<a href="javascript:booth_action(<?=$row[0]?>,'empty');" id='button_emp'><?=gettext("Empty"); ?></a>
				<a href="javascript:select_regular(<?=$row[0]?>);" id='button_lr'><?=gettext("Member"); ?></a>
				<!-- <a href="javascript:select_regular(<?=$row[0]?>);" id='button_ln'><?=gettext("New"); ?></a> -->
				&nbsp;</td>
				<td class="refill" id="refill" >
				<?= gettext("Add:"); ?>
				<?php foreach($refills as $rf) { ?>
				&nbsp;<a href="javascript:refill(<?=$row[0] .', ' . $rf ?>);"><?= ' '. $rf; ?></a>
				<?php } ?>
				</td>
				</tr>
				</tbody></table></td><?php
				if ( $i % $ndiv == $ndiv-1 )
					echo "</tr>\n";
			}
		?>
		</tr></TBODY></TABLE>
		<?php
		}
	}
	
?>
<br>
<a href='javascript:startRequest("booths.xml.php",reqStateChanged2)'><?= _("Refresh")?></a>
<br>
<?= _("Response:")?> <span id='response' ></span>
<?php include ("PP_footer.php"); ?>
