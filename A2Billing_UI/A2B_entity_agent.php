<?php
require_once ("./lib/defines.php");
require_once ("./lib/module.access.php");
require_once ("a2blib/Form.inc.php");
require_once ("a2blib/Class.HelpElem.inc.php");
require_once ("a2blib/Form/Class.SqlRefField.inc.php");
// include ("./form_data/FG_var_agent.inc");
// include ("./lib/help.php");

$menu_section='menu_agents';


HelpElem::DoHelp(gettext("Agents, callshops. <br>List or manipulate agents, which can deliver cards to customers."));

$HD_Form= new FormHandler('cc_agent',_("Agents"),_("Agent"));
$HD_Form->checkRights(ACX_AGENTS);
$HD_Form->init();

$PAGE_ELEMS[] = &$HD_Form;
$PAGE_ELEMS[] = new AddNewButton($HD_Form);

$HD_Form->model[] = new PKeyFieldEH(_("ID"),'id');

$HD_Form->model[] = new TextFieldEH(_("Name"),'name',_("Human readable name for the agent"));
$HD_Form->model[] = new TextField(_("Username"),'login',_("Login name"));
//end($HD_Form->model)->fieldname ='agent';
$HD_Form->model[] = new PasswdField(_("Password"),'passwd','alnum',_("Password used by agent to login into the web interface"));

$HD_Form->model[] = new IntField(gettext("OPTIONS"), "options", null, "7%");
// $HD_Form->model[] = new RefField(_("LANGUAGE"), "language");
$HD_Form->model[] = new MoneyField(_("CREDIT"), "credit");
$HD_Form->model[] = new MoneyField(_("CLIMIT"), "climit",_("Credit limit of agent"));
$HD_Form->model[] = new SqlRefField(_("TARIFFG"), "tariffgroup","cc_tariffgroup", "id", "name");
// $HD_Form->model[] = new RefField(_("CURRENCY").gettext("CUR"), "currency", "5%");

$actived_list = array();
$actived_list[] = array('t',_("Active"));
$actived_list[] = array('f',_("Inactive"));

$HD_Form->model[] = new RefField(_("ACTIVATED"), "active", $actived_list,_("Allow the agent to operate"),"4%");
end($HD_Form->model)->fieldacr =  gettext("ACT");

$HD_Form->model[] = new DelBtnField();


require("PP_page.inc.php");

?>
