<?php
require_once("lib/defines.php");
require_once("lib/module.access.php");

// session_name("UISIGNUP");
// session_start();


if (!isset($_SESSION["date_activation"]) || (time()-$_SESSION["date_activation"]) > 10) {
	$_SESSION["date_activation"]=time();
} else {
	//echo "Act:". time()." - ". $_SESSION["date_activation"] ."\n";
	sleep(7);
	echo gettext("Sorry the activation has been sent already, please wait 1 minute before making any other try !");
	exit();
}


require_once (DIR_COMMON."Form.inc.php");
require_once (DIR_COMMON."Form/Class.SqlActionForm.inc.php");

$HD_Form= new SqlActionForm();
$HD_Form->checkRights(1);
$HD_Form->init();

$PAGE_ELEMS[] = &$HD_Form;

$HD_Form->model[] = new TextField(_("User Name"),'username');
$HD_Form->model[] = new TextField(_("Activation key"),'loginkey');

/* The signup->mail actions are defined here, into this ugly query, so that
   one different signup "mode" could call those SQL functions differently and
   alter this logic */
$HD_Form->QueryString= 'SELECT create_mail( \'' .DynConf::GetCfg(SIGNUP_CFG,'mail_act_template','signup-activated') . '\','.
		'email, \''.getenv('LANG') ."', replace(replace(".
		"'firstname=' || firstname || '&lastname=' || lastname ||" .
		"'&username=' || username || '&userpass=' || userpass ".
		",' ','%%20'), E'\\n','%%0A') ) AS mailid, card.* ".
		" FROM card_signup_activate(" . DynConf::GetCfg(SIGNUP_CFG,'card_group','0',true) . ', '.
			'%username, %loginkey) AS card;' ;
//'SELECT gen_cards(%#grp, %ser, %#num, %startn, %#ucfg) AS ncards;';

$HD_Form->expectRows = true;
$HD_Form->submitString = _("Activate!");
$HD_Form->successString =  '';
//$HD_Form->contentString = 'Generated:<br>';
$HD_Form->rowString = _("Thank you for signing up.<br>An email has been sent to %email with details about using our services!<br>");

require("PP_page.inc.php");

?>
