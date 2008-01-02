<?php

/** Booths xml code:
    Copyright (C) 2006-2008 P. Christeas <p_christeas@yahoo.com>
    */
// We must tell the mod_php to send the correct header..
header('Content-type: text/xml');

require("lib/defines.php");
require("lib/module.access.php");
require("lib/common/BoothsXML.inc.php");

if (! has_rights (ACX_AGENTS)){ 
	   header ("HTTP/1.0 401 Unauthorized");
	   die();
}

/* Here we handle all actions to the booths!
	NOTE that we always use the agent id *FROM THE SESSION*
	as a security feature, so that a foreign agent can't mess
	with us */

$dom = BoothsDom($_GET['action'],$_GET['actb'], '');
// Let ONLY this line produce any output!
echo $dom->saveXML();

?>
