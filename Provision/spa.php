<?php
require_once("lib/Class.A2Billing.inc.php");
require_once("lib/Misc.inc.php");
require_once("lib/Provi/SpaXml_Provi.inc.php");
require_once("lib/Class.ElemBase.inc.php");
require_once("lib/Class.html.inc.php");

$fp = fopen('php://temp','r+');
if (!$fp){
	if ($FG_DEBUG)
		echo "Cannot open temp stream.\n";
	return;
}
$dbg_elem = new DbgElem();

$gen = new SpaXmlProvi();
$gen->dbg_elem=&$dbg_elem;

if (!$gen->Init(array(mac => $_GET['m'], sec=> $_GET['s']))){
	Header ("HTTP/1.0 401 Unauthorized");
	echo "Unauthorized!";
	die();
}

header('Content-type: '. $gen->getMimeType());

$gen->genContent($fp);


rewind($fp);
echo stream_get_contents($fp);

fclose($fp);

?>