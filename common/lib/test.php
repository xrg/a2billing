#!/usr/bin/php
<?php
define("DEFAULT_A2BILLING_CONFIG", '../../a2billing.conf');
// echo  DEFAULT_A2BILLING_CONFIG;
// echo "\n";
require_once('Class.Logger.php');

print_r(A2Billing::DBHandle());

$loog = new Logger();
$loog-> insertLog(0, 1, "TEST", "test entry!", 'aa','bb','cc');
?>