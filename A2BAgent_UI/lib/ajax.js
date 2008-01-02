// AJAX Routines
// Copyright (C) P. Christeas <p_christeas@yahoo.com>  .. except for snippets copied from the internet!


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
