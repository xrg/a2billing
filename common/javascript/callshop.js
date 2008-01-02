/* Asterisk2Billing Callshop functions
   Copyright (C) 2006-2008 P. Christeas <p_christeas@yahoo.com>

*/

var req_timer = false;
var call_obj = false;

function getXMLHttpRequest() {
	var ret = false;
	
	if (window.XMLHttpRequest) { // Mozilla, Safari, ...
		ret = new XMLHttpRequest();
		if (http_request.overrideMimeType) {
			ret.overrideMimeType('text/xml');
			// See note below about this line
		}
	} else if (window.ActiveXObject) { // IE
		throw "Get a decent browser!";
	}
	return ret;
}

var http_request = false; // Global: we don't want multiple requests here!

/** Starts some request.
*/
function startRequest(url,cb_fn){
	if (!http_request) {
		http_request=getXMLHttpRequest();
		if (!http_request){
			alert( "Cannot do AJAX request!");
			return;
		}
	}
	
	{
		if ((http_request.readyState>0) &&(http_request.readyState <4 ))
			http_request.abort();
		http_request.onreadystatechange = cb_fn;
		http_request.open('GET', url, true);
		http_request.send(null);
		document.getElementById("response").innerHTML="Start req" + url;
	}
	
}

function reqStateChanged2(){

	var resp ="";
	if (req_timer){
		clearTimeout(req_timer);
		req_timer=false;
		}
	switch (http_request.readyState) {
	case 1:
	case 2:
	case 3:
		resp=global_reqStates[http_request.readyState];
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
	var booth_tags= new Array("name","status", "credit", "mins", "button_sta",
		"button_stp", "button_en", "button_dis", "button_unl", "button_ld",
		"button_lr",  "button_emp", "button_pay", "refill");
	//booth_tags[i++]="button_ln",
	
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

function refill(booth,sum) {
	startRequest("booths.xml.php"+"?action=refill&actb=" + booth + "&sum=" + sum,reqStateChanged2);
}

//eof
