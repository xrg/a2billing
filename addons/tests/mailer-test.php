#!/usr/bin/php -q
<?php
require_once("lib/Misc.inc.php");
require_once("lib/Class.Mailer.inc.php");

$cli_args = arguments($argv);

$mai = new Mailer();

try {
	switch ($cli_args['type']) {
	case 'simple':
	default:
		$mai->setFrom("me","my@mail");
		$mai->setTo("None", "panos");
		$mai->setSubject("Μία δοκιμή", "UTF-8");
		$mai->body = new Mailer_TextBody("test test");
		break;
	case 'utf':
		$mai->setFrom("Πάνος","my@mail");
		$mai->setTo("Κανένας", "panos");
		$mai->setSubject("Μία δοκιμή", "UTF-8");
		$mai->body = new Mailer_TextBody("Δοκιμή κειμένου");
		break;
	case 'alt':
		$mai->setFrom("Πάνος","my@mail");
		$mai->setTo("Κανένας", "panos");
		$mai->setSubject("Μία δοκιμή", "UTF-8");
		$mai->body = new Mailer_MultipartAlt();
		$mai->body->addPart(new Mailer_TextBody("Δοκιμή κειμένου"));
		$mai->body->addPart(new Mailer_HtmlBody("<html><body>Δοκιμή <u>κειμένου</u></body></html>"));
		break;
	
	}

	$mai->PrintMail();
	$mai->SendMail();

} catch (Exception $ex){
	echo "\nException: ". $ex->getMessage()."\n";
}

?>