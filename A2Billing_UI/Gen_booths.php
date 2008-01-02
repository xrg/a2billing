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

$menu_section='menu_agents';


// HelpElem::DoHelp(gettext("*-*"),'vcard.png');

$HD_Form= new SqlActionForm();
$HD_Form->checkRights(ACX_CUSTOMER);
$HD_Form->init();

$PAGE_ELEMS[] = &$HD_Form;

$HD_Form->model[] = new SqlRefField(_("Card Group"), "grp","cc_card_group", "id", "name",_("Card group used for the generated cards. Will also define the numplan and the agent, if exists."));
end($HD_Form->model)->combotable ='cc_card_group, cc_agent';
end($HD_Form->model)->combofield = 'cc_agent.name || \': \' || cc_card_group.name';
end($HD_Form->model)->comboclause ='cc_card_group.agentid = cc_agent.id';
end($HD_Form->model)->comboid ='cc_card_group.id';


$HD_Form->model[] = new IntField(_("Count of booths"),'num');
end($HD_Form->model)->def_value=4;

// $HD_Form->model[] = new RefField(_("Numbering"),'ser', $ser_list);
$HD_Form->model[] = new TextField(_("Start Number"),'startn');
end($HD_Form->model)->def_value=1;

$HD_Form->model[] = new SqlRefFieldN(_("VoIP Conf"), "ucfg","cc_ast_users_config", "id", "cfg_name",_("If set, use this configuration to also generate Asterisk peers."));

$HD_Form->QueryString= 'SELECT gen_booths(%#grp, %#num, %startn, %#ucfg) AS ncards;';

$HD_Form->expectRows = true;
$HD_Form->submitString = _("Generate Booths!");
$HD_Form->successString =  '';
//$HD_Form->contentString = 'Generated:<br>';
$HD_Form->rowString = _("Generated %#ncards booths!<br>");

require("PP_page.inc.php");
?>
