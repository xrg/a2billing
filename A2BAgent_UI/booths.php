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
?>
<script type="text/javascript">
function reqStateChanged2(){

	var resp ="";
	switch (http_request.readyState) {
	case 1:
		resp="Open";
		break;
	case 2:
		resp="Waiting for response";
		break;
	case 3:
		resp="Receiving";
		break;
	case 4:
		switch(http_request.status) { // HTTP response codes..
		case 200:
			resp="OK";
			//document.getElementById("result_f").innerHTML=http_request.responseText;
			parseBoothXML(http_request.responseXML);
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
	booth_tags[0]="name";
	booth_tags[1]="status";
	booth_tags[2]="credit";
	booth_tags[3]="mins";
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
				
			}
		}catch(err){
			alert(err); //debugging..
			alert(typeof(dom_booth))
		}
	}
}
</script>

<?php
	/** These states have to match the SQL logic */
	
	$DBHandle  = DbConnect();
	
	$QUERY="SELECT id FROM cc_booth_v WHERE owner = " . trim($_SESSION["agent_id"]) . " ORDER BY id;";
		
	echo $QUERY . "<br>"; 
	$res = $DBHandle -> query($QUERY);

	if (!$res){
?>
	<br></br>
            <table width="460" border="2" align="center" cellpadding="1" cellspacing="2" bordercolor="#eeeeff" bgcolor="#FFFFFF">
		<tr bgcolor=#4e81c4>
			<td>
			<div align="center"><b><font color="white" size=5><?php echo gettext("Error Page");?></font></b></div>
			</td>
		</tr>
		<tr>
			<td align="center" colspan=2>
                    <table width="100%" border="0" cellpadding="5" cellspacing="5">
                      <tr>
                        <td align="center"><br/>
				<img src="./Css/kicons/system-config-rootpassword.png">
				<br/>
				<b><font color=#3050c2 size=4><?php echo gettext("Cannot locate booths") ?></font></b><br/><br/><br/></td>
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
		echo "ndiv= " . $ndiv . "<br>\n";
		
		$num = $res -> numRows();
		if ($num==0) {
			echo gettext("No booths!<br>Please ask the administrators to create you some.");
		}
		else {
		?>
		<div id="message"> Welcome! </div>
		<br>
		<TABLE class='tableMbooths' border=0 cellPadding=2 cellSpacing=2 width="100%">
		<TBODY>
		<?php
			for($i=0;$i<$num;$i++)
			{
				$row = $res -> fetchRow();
				if ( $i % $ndiv == 0)
					echo "<tr>\n";

				echo "<td id=\"booth_" . $row[0] . "\" >";
				?><table width=100 cellPadding=2 cellSpacing=2><tbody>
				<tr><td id="name" colspan=3>Booth X</td></tr>
				<tr><td id="status" colspan=3> -- </td></tr>
				<tr><td><?php echo gettext("Credit:");?></td><td div id="credit"> </td><td id="mins"></td></tr>
				<tr><td id="buttons" colspan=3> [Start] [Stop] ... </td>
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
<a href='javascript:startRequest("booths.xml.php",reqStateChanged2)'> refresh</a>
<br>
Response: <span id='response' ></span>
<br>
Result: <pre id='result_f'> </pre>
<?php include ("PP_footer.php"); ?>
