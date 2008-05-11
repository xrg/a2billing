<?php
require_once("lib/defines.php");
require_once("lib/module.access.php");

if (!isset($_SERVER['HTTPS'])){
	$safe_base="https://".$_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'];
	header ("Location: $safe_base");
	exit();
}

require_once (DIR_COMMON."Form.inc.php");
require_once (DIR_COMMON."Form/Class.SqlActionForm.inc.php");

$HD_Form= new SqlActionForm();
$HD_Form->checkRights(1);
$HD_Form->init();

$PAGE_ELEMS[] = &$HD_Form;

$HD_Form->model[] = new TextField(_("First Name"),'firstname');
$HD_Form->model[] = new TextField(_("Last Name"),'lastname');
$HD_Form->model[] = new TextField(_("E-mail"),'email');
$HD_Form->model[] = new TextField(_("Address"),'address');
$HD_Form->model[] = new TextField(_("City"),'city');
$HD_Form->model[] = new TextField(_("State"),'state');
$HD_Form->model[] = new TextField(_("Country"),'country');
$HD_Form->model[] = new TextField(_("Postal code"),'zipcode');
// language
// captcha

/* The signup->mail actions are defined here, into this ugly query, so that
   one different signup "mode" could call those SQL functions differently and
   alter this logic */
$HD_Form->QueryString= 'SELECT create_mail( \'' .DynConf::GetCfg(SIGNUP_CFG,'mail_template','signup') . '\','.
		'email, \''.getenv('LANG') ."', replace(replace(".
		"'firstname=' || firstname || '&lastname=' || lastname || '&loginkey=' || loginkey ||" .
		"'&username=' || username || '&userpass=' || userpass ".
		",' ','%%20'), E'\\n','%%0A') ) AS mailid ".
	", email" .
		" FROM gen_card_signup(" . DynConf::GetCfg(SIGNUP_CFG,'card_group','0',true) . ', '.
			DynConf::GetCfg(SIGNUP_CFG,'voip_group','NULL') .
			', %firstname, %lastname, %email, %address, %city, %state, %country, %zipcode, %lang);' ;
//'SELECT gen_cards(%#grp, %ser, %#num, %startn, %#ucfg) AS ncards;';

$HD_Form->expectRows = true;
$HD_Form->submitString = _("Sign up!");
$HD_Form->successString =  '';
//$HD_Form->contentString = 'Generated:<br>';
$HD_Form->rowString = _("Thank you for signing up.<br>An email has been sent to %email for confirmation!<br>");

require("PP_page.inc.php");
?>
