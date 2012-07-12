<?php
require("./lib/defines.php");
require("./lib/module.access.php");

$server= $_SERVER['SERVER_NAME'];
$self_uri=$_SERVER['PHP_SELF'];
if (isset($_POST['continue_ssl']) && $_POST['continue_ssl'] == '1')
	$unsafe_base = dirname($self_uri);
else if ($_SERVER['SERVER_PORT'] == 80 )
	$unsafe_base="http://".$server . dirname($self_uri);
else
	$unsafe_base="http://".$server.':'. $_SERVER['SERVER_PORT'] . dirname($self_uri);

require("./lib/module.login.php");
//include (dirname(__FILE__)."/../lib/company_info.php");

header ("Location: $unsafe_base/PP_intro.php");
?>