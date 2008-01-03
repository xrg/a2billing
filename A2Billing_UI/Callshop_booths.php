<?php
require_once ("./lib/defines.php");
require_once ("./lib/module.access.php");
require_once (DIR_COMMON."Class.CallShop.inc.php");
require_once (DIR_COMMON."Form/Class.SelectionForm.inc.php");
require_once (DIR_COMMON."Form/Class.SqlRefField.inc.php");

$menu_section='menu_agents';

$CS_Form = new CallshopPage();
$CS_Form->checkRights(ACX_AGENTS);
$CS_Form->init();

// TODO: select agent!
$CS_Form->ask_agent=true;
$SEL_Form = new SelectionForm();
$SEL_Form->init();
$SEL_Form->model[] = new SqlRefField(_("Agent"),'agentid','cc_agent','id','name');
$CS_Form->agentid=$SEL_Form->getpost_single('agentid');

$PAGE_ELEMS[] = &$SEL_Form;
$PAGE_ELEMS[] = &$CS_Form;

require("PP_page.inc.php");

?>
