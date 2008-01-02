<?php 
/** Booths xml code:
    Copyright (C) 2006-2008 P. Christeas <p_christeas@yahoo.com>
    */
// We must tell the mod_php to send the correct header..
header('Content-type: text/xml');

include ("lib/defines.php");
include ("lib/module.access.php");


if (! has_rights (ACX_ACCESS)){ 
	   header ("HTTP/1.0 401 Unauthorized");
	   die();
}

		/* Here we handle all actions to the booths!
		   NOTE that we always use the agent id *FROM THE SESSION*
		   as a security feature, so that a foreign agent can't mess
		   with us */

$dom = BoothsDom($_GET['action'],$_GET['actb'], 'agent = ...');
// Let ONLY this line produce any output!
echo $dom->saveXML();

if ($DBHandle)
	DbDisconnect($DBHandle);

?>
