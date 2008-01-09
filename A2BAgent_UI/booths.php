<?php
require_once ("./lib/defines.php");
require_once ("./lib/module.access.php");
require_once (DIR_COMMON."Class.CallShop.inc.php");
require_once (DIR_COMMON."Form/Class.SelectionForm.inc.php");
require_once (DIR_COMMON."Form/Class.SqlRefField.inc.php");

$menu_section='menu_booths';

$CS_Form = new CallshopPage();
$CS_Form->checkRights(ACX_ACCESS);
$CS_Form->init();

// TODO: select agent!
$CS_Form->ask_agent=false;
$CS_Form->agentid=$_SESSION['agent_id'];

$PAGE_ELEMS[] = &$CS_Form;

require("PP_page.inc.php");
?>