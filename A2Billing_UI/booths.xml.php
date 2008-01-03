<?php

/** Booths xml code:
    Copyright (C) 2006-2008 P. Christeas <p_christeas@yahoo.com>
    */
// We must tell the mod_php to send the correct header..
header('Content-type: text/xml');

require("lib/defines.php");
require("lib/module.access.php");
require("lib/common/BoothsXML.inc.php");
require("lib/common/Misc.inc.php");

if (! has_rights (ACX_AGENTS)){ 
	   header ("HTTP/1.0 401 Unauthorized");
	   $dom = messageDom(_("Unauthorized: please log in again."),"msg_errror");
	   echo $dom->saveXML();
	   exit();
}

$aclause = '';
if (!empty($_GET['aid']))
	$aclause= str_dbparams(A2Billing::DBHandle(),'agentid = %#1',array($_GET['aid']));
	
$dom = BoothsDom($_GET['action'],$_GET['actb'], $aclause);
// Let ONLY this line produce any output!
echo $dom->saveXML();

?>
