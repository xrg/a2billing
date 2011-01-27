<?php
require_once ("./lib/defines.php");
require_once ("./lib/module.access.php");
require_once ("a2blib/Form.inc.php");
require_once ("a2blib/Form/Class.SqlActionForm.inc.php");
require_once ("a2blib/Class.HelpElem.inc.php");
/*require_once ("a2blib/Form/Class.RevRef.inc.php");
require_once ("a2blib/Form/Class.TimeField.inc.php");*/
require_once ("a2blib/Form/Class.SqlRefField.inc.php");
// require_once ("a2blib/Form/Class.RevRefForm.inc.php");

$menu_section='menu_invoicing';

$HD_Form= new SqlActionForm();
$HD_Form->checkRights(ACX_BILLING);
$HD_Form->init();

$PAGE_ELEMS[] = &$HD_Form;

$per_list = array();
$per_list[] = array( '1 month', _("Monthly"));
$per_list[] = array( '1 year', _("Yearly"));
$per_list[] = array( '1 week', _("Weekly"));
$per_list[] = array( '3 months', _("Quarterly"));
$per_list[] = array( '1 day', _("Daily"));

$HD_Form->model[] = new SqlRefFieldN(_("Agent"),'agentid','cc_agent','id','name',
	_("The agent to create for, or <u>(null) for all agents.</u>"));


$HD_Form->model[] = new RefField(_("Period"), "period",$per_list,_("Billing period for invoices"));

$HD_Form->QueryString= 'SELECT agent_create_all_invoices(id, %period) FROM cc_agent '.
	' WHERE cc_agent.id = %#agentid OR %!agentid IS NULL;';

$HD_Form->expectRows = false;
$HD_Form->submitString = _("Generate Invoices!");
$HD_Form->successString =  _("Generated invoices!");
//$HD_Form->contentString = 'Generated:<br>';
//$HD_Form->rowString = 

require("PP_page.inc.php");
?>
