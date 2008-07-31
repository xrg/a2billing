#!/usr/bin/php -q
<?php
require_once("lib/Class.Mailer.inc.php");

$mai = new Mailer();

try {
	$mai->body = new Mailer_TextBody("test test");
	$mai->PrintMail();

} catch (Exception $ex){
	echo "\nException: ". $ex->getMessage()."\n";
}

?>