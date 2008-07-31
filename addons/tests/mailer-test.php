#!/usr/bin/php -q
<?php
require_once("lib/Class.Mailer.inc.php");

$mai = new Mailer();

try {
	$mai->setFrom("Πάνος","my@mail");
	$mai->setTo("Κανένας", "nobody");
	$mai->setSubject("Μία δοκιμή", "UTF-8");
	$mai->body = new Mailer_TextBody("test test");
	$mai->PrintMail();
	$mai->SendMail();

} catch (Exception $ex){
	echo "\nException: ". $ex->getMessage()."\n";
}

?>