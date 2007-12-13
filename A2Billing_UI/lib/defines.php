<?php
define(DIR_COMMON,dirname(__FILE__)."/common/");
// 
// The system will not log for Public/index.php and 
// signup/index.php

// Override below, if you want an alternative dir..
//define(DEFAULT_A2BILLING_CONFIG, '/etc/asterisk/a2billing.conf');

$URI = $_SERVER['REQUEST_URI'];
$restircted_url = substr($URI,-16);
if(!($restircted_url == "Public/index.php") && !($restircted_url == "signup/index.php") && isset($_SESSION["admin_id"])) {
	$log -> insertLog($_SESSION["admin_id"], 1, "Page Visit", "User Visited the Page", '', $_SERVER['REMOTE_ADDR'], $_SERVER['REQUEST_URI'],'');
}

$FG_DEBUG=4;

?>
