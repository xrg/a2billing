<?php
include ("../lib/defines.php");
include ("../lib/company_info.php");
include ("../lib/smarty.php");

$smarty->assign("error", $_GET["error"]);

$smarty->display('index.tpl');

?>
