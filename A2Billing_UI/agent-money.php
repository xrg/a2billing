<?php
require_once ("./lib/defines.php");
require_once ("./lib/module.access.php");
require_once (DIR_COMMON."Form.inc.php");
require_once (DIR_COMMON."Form/Class.TimeField.inc.php");
require_once (DIR_COMMON."Form/Class.ClauseField.inc.php");
require_once (DIR_COMMON."Form/Class.SqlRefField.inc.php");
require_once (DIR_COMMON."Form/Class.SelectionForm.inc.php");

require_once (DIR_COMMON."AgentMoney.inc.php");
$menu_section = 'menu_agents';

$SEL_Form = new SelectionForm();
$SEL_Form->init();
$SEL_Form->model[] = new SqlRefField(_("Agent"),'agentid','cc_agent','id','name');
$SEL_Form->model[] = new DateTimeField(_("Period from"),'date_from');
	end($SEL_Form->model)->does_add = false;
	end($SEL_Form->model)->def_date = '00:00 today';
	end($SEL_Form->model)->def_value = 'aaa';
	end($SEL_Form->model)->fieldexpr = 'date';
$SEL_Form->model[] = new DateTimeField(_("Period to"),'date_to');
	end($SEL_Form->model)->does_add = false;
	end($SEL_Form->model)->def_date = 'now';
	end($SEL_Form->model)->fieldexpr = 'date';
$SEL_Form->search_exprs['date_from'] = '>=';
$SEL_Form->search_exprs['date_to'] = '<=';
//$CS_Form->agentid=$SEL_Form->getpost_single('agentid');

$PAGE_ELEMS[] = &$SEL_Form;

$s_agentid=$SEL_Form->getpost_single('agentid');
if (!empty($s_agentid))
	AgentMoney($s_agentid,$SEL_Form,false,ACX_AGENTS);

require("PP_page.inc.php");

?>
