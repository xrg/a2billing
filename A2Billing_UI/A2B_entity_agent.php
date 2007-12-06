<?php
include ("./lib/defines.php");
include ("./lib/module.access.php");
include (DIR_COMMON."Form.inc.php");
include (DIR_COMMON."Class.HelpElem.inc.php");
// include ("./form_data/FG_var_agent.inc");
// include ("./lib/help.php");

$menu_section='menu_agents';


HelpElem::DoHelp(gettext("Agents, callshops. <br>List or manipulate agents, which can deliver cards to customers."));

$HD_Form= new FormHandler('a2b_old.cc_agent',_("Agents"),_("Agent"));
$HD_Form->checkRights(ACX_AGENTS);
$HD_Form->init();

$BODY_ELEMS[] = &$HD_Form;
$BODY_ELEMS[] = new AddNewButton($HD_Form);

$HD_Form->model[] = new PKeyFieldEH(_("ID"),'id');

$HD_Form->model[] = new TextFieldEH(_("Name"),'name',_("Human readable name for the agent"));
$HD_Form->model[] = new TextField(_("Username"),'login',_("Login name"));
//end($HD_Form->model)->fieldname ='agent';

$HD_Form->model[] = new IntField(gettext("OPTIONS"), "options", null, "7%");
// $HD_Form->model[] = new RefField(_("LANGUAGE"), "language");
$HD_Form->model[] = new FloatField(_("CREDIT"), "credit");
$HD_Form->model[] = new FloatField(_("CLIMIT"), "climit",_("Credit limit of agent"));
// $HD_Form->model[] = new SqlRefField(_("TARIFFG"), "tariffgroup","cc_tariffgroup", "tariffgroupname", "id='%id'");
// $HD_Form->model[] = new RefField(_("CURRENCY").gettext("CUR"), "currency", "5%");
// $HD_Form->model[] = new RefField(_("ACTIVATED" gettext("ACT"), "activated", "4%", $actived_list);

$HD_Form->model[] = new DelBtnField();


require("PP_page.inc.php");

?>
