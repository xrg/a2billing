#!/usr/bin/php
<?php
require_once("lib/Provi/AsteriskIni.inc.php");

$res= STDOUT;

$gen = new AsteriskIniProvi();

$gen->Init(array());

$gen->genContent($res);

?>
