// AJAX Routines
// Copyright (C) P. Christeas <p_christeas@yahoo.com>  .. except for snippets copied from the internet!

function getXMLHttpRequest() {
	var ret = false;
	
	if (window.XMLHttpRequest) { // Mozilla, Safari, ...
		ret = new XMLHttpRequest();
		if (http_request.overrideMimeType) {
			ret.overrideMimeType('text/xml');
			// See note below about this line
		}
	} else if (window.ActiveXObject) { // IE
		try {
			ret = new ActiveXObject("Msxml2.XMLHTTP");
		} catch (e) {
			try {
				ret = new ActiveXObject("Microsoft.XMLHTTP");
			} catch (e) {}
		}
	}
	return ret;
}

var http_request = false; // Global: we don't want multiple requests here!

/** Starts some request.
*/
function startRequest(url,cb_fn){
	if (!http_request) {
		http_request=getXMLHttpRequest();
		if (!http_request)
			alert( "Cannot do AJAX request!");
	}else{
		if ((http_request.readyState>0) &&(http_request.readyState <4 ))
			http_request.abort();
		http_request.onreadystatechange = cb_fn;
		http_request.open('GET', url, true);
		http_request.send(null);
		document.getElementById("response").innerHTML="Start req";
	}
	
}


function reqStateChanged(){

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


//eof
