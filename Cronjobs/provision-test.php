#!/usr/bin/php
<?php
require_once("lib/Provi/AsteriskIni.inc.php");

$res= STDOUT;

$gen = new AsteriskIniProvi();

$gen->Init(array(cardid => 3, categ=>'sip-peer'));

$gen->genContent($res);

?>
