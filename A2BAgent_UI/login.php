<?php
include ("./lib/defines.php");

$server= $_SERVER['SERVER_NAME'];
$self_uri=$_SERVER['PHP_SELF'];
$unsafe_base="http://".$server . dirname($self_uri);

include ("./lib/module.access.php");

//include (dirname(__FILE__)."/../lib/company_info.php");

header ("Location: $unsafe_base/index2.php");
?>
