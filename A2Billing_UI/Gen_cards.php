<?php
require_once ("./lib/defines.php");
require_once ("./lib/module.access.php");
require_once (DIR_COMMON."Form.inc.php");
require_once (DIR_COMMON."Form/Class.SqlActionForm.inc.php");
require_once (DIR_COMMON."Class.HelpElem.inc.php");
/*require_once (DIR_COMMON."Form/Class.RevRef.inc.php");
require_once (DIR_COMMON."Form/Class.TimeField.inc.php");*/
require_once (DIR_COMMON."Form/Class.SqlRefField.inc.php");
// require_once (DIR_COMMON."Form/Class.RevRefForm.inc.php");

$menu_section='menu_customers';


// HelpElem::DoHelp(gettext("*-*"),'vcard.png');

$HD_Form= new SqlActionForm();
$HD_Form->checkRights(ACX_CUSTOMER);
$HD_Form->init();

$PAGE_ELEMS[] = &$HD_Form;

$HD_Form->model[] = new SqlRefField(_("Card Group"), "grp","cc_card_group", "id", "name",_("Card group used for the generated cards. Will also define the numplan and the agent, if exists."));

$ser_list = array();
$ser_list[]  = array("t", _("Consecutive numbers"));
$ser_list[]  = array("f", _("Random numbers"));

$HD_Form->model[] = new IntField(_("Count of cards"),'num');
end($HD_Form->model)->def_value=10;

$HD_Form->model[] = new RefField(_("Numbering"),'ser', $ser_list);
$HD_Form->model[] = new TextField(_("Start Number"),'startn');
end($HD_Form->model)->def_value=0;

$HD_Form->model[] = new SqlRefFieldN(_("VoIP Conf"), "ucfg","cc_ast_users_config", "id", "cfg_name",_("If set, use this configuration to also generate Asterisk peers."));

$HD_Form->QueryString= 'SELECT gen_cards(%#grp, %ser, %#num, %startn, %#ucfg) AS ncards;';

$HD_Form->expectRows = true;
$HD_Form->submitString = _("Generate Cards!");
$HD_Form->successString =  '';
//$HD_Form->contentString = 'Generated:<br>';
$HD_Form->rowString = _("Generated %#ncards cards!<br>");

require("PP_page.inc.php");
?>
