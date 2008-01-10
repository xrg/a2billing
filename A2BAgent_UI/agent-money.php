<?php
require_once ("./lib/defines.php");
require_once ("./lib/module.access.php");
require_once (DIR_COMMON."Form.inc.php");
require_once (DIR_COMMON."Form/Class.TimeField.inc.php");
require_once (DIR_COMMON."Form/Class.ClauseField.inc.php");
require_once (DIR_COMMON."Form/Class.SqlRefField.inc.php");
require_once (DIR_COMMON."Form/Class.SelectionForm.inc.php");

require_once (DIR_COMMON."AgentMoney.inc.php");
$menu_section = 'menu_reports';

$SEL_Form = new SelectionForm();
$SEL_Form->init();
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

AgentMoney($_SESSION['agent_id'],$SEL_Form,true,ACX_ACCESS);

require("PP_page.inc.php");

?>
