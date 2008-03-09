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

$HD_Form->QueryString= 'SELECT create_mail( \'' .DynConf::GetCfg(SIGNUP_CFG,'mail_template','signup') . '\','.
		'email, \''.getenv('LANG') ."', replace(replace(".
		"'firstname=' || firstname || '&lastname=' || lastname || '&loginkey=' || loginkey ||" .
		"'&username=' || username || '&userpass=' || userpass ".
		",' ','%%20'), E'\\n','%%0A') ) ".
	", email" .
		"\n FROM gen_card_signup(" . DynConf::GetCfg(SIGNUP_CFG,'card_group','0',true) .
			', %firstname, %lastname, %email, %address, %city, %state, %country, %zipcode, %lang);' ;
//'SELECT gen_cards(%#grp, %ser, %#num, %startn, %#ucfg) AS ncards;';

$HD_Form->expectRows = true;
$HD_Form->submitString = _("Sign up!");
$HD_Form->successString =  '';
//$HD_Form->contentString = 'Generated:<br>';
$HD_Form->rowString = _("Thank you for signing up.<br>An email has been sent to %email for confirmation!<br>");

require("PP_page.inc.php");

/////////////////////////////////////
if (false) {
include ("../lib/defines.php");
include ("../lib/module.access.php");
include ("../lib/Form/Class.FormHandler.inc.php");
include ("./form_data/FG_var_index.inc");



if ($_GET["dotest"]){
	$_POST["lastname"] = $_POST["firstname"] = $_POST["address"] = $_POST["city"] = $_POST["state"] = $_POST["country"] = 'SIGN-'.MDP_STRING(5).'-'.MDP_NUMERIC(3);
	$_POST["email"] = MDP_STRING(10).'@sign-up.com';
	$_POST["zipcode"] = $_POST["phone"] = '12345667789';
}



/***********************************************************************************/
if (!$A2B->config["signup"]['enable_signup']) exit;

$HD_Form -> setDBHandler (DbConnect());
$HD_Form -> init();

if ($id!="" || !is_null($id)){
	$HD_Form -> FG_EDITION_CLAUSE = str_replace("%id", "$id", $HD_Form -> FG_EDITION_CLAUSE);
}

if (!isset($form_action))  $form_action="ask-add"; //ask-add
if (!isset($action)) $action = $form_action;


$list = $HD_Form -> perform_action($form_action);

if($form_action == "add")
{
	unset($_SESSION["cardnumber_signup"]);
	$_SESSION["language_code"] = $_POST["language"];
	$_SESSION["cardnumber_signup"] = $maxi;	
    Header ("Location: signup_confirmation.php");
}


// #### HEADER SECTION
include("PP_header.php");



// #### TOP SECTION PAGE
$HD_Form -> create_toppage ($form_action);


// #### CREATE FORM OR LIST
//$HD_Form -> CV_TOPVIEWER = "menu";
if (strlen($_GET["menu"])>0) $_SESSION["menu"] = $_GET["menu"];

$HD_Form -> create_form ($form_action, $list, $id=null);


// #### FOOTER SECTION
// include("PP_footer.php");
}
?>
