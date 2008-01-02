<?php
require_once ("./lib/defines.php");
require_once ("./lib/module.access.php");
require_once (DIR_COMMON."Class.CallShop.inc.php");

$menu_section='menu_agents';

$CS_Form = new CallshopPage();
$CS_Form->checkRights(ACX_AGENTS);
$CS_Form->init();

// TODO: select agent!

$CS_Form->agentid=1;
$PAGE_ELEMS[] = &$CS_Form;

require("PP_page.inc.php");

?>
