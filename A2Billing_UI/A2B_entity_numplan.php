<?php
require_once ("./lib/defines.php");
require_once ("./lib/module.access.php");
require_once (DIR_COMMON."Form.inc.php");
require_once (DIR_COMMON."Class.HelpElem.inc.php");
require_once (DIR_COMMON."Form/Class.RevRef.inc.php");

$menu_section='menu_customers';


HelpElem::DoHelp(gettext("Numbering plans define the way a customer dials numbers. " .
	"They also define peer groups of numbers."),'vcard.png');

$HD_Form= new FormHandler('cc_numplan',_("Numplans"),_("Numplan"));
$HD_Form->checkRights(ACX_NUMPLAN);
$HD_Form->init();

$PAGE_ELEMS[] = &$HD_Form;
$PAGE_ELEMS[] = new AddNewButton($HD_Form);

$HD_Form->model[] = new PKeyFieldEH(_("ID"),'id');

$HD_Form->model[] = new TextFieldEH(_("Name"),'name',_('Name of group'));
//$HD_Form->model[] = new SqlRefFieldN(_("Alias Length"),'agentid','cc_agent','id','name', _("If cards belong to an agent."));
$HD_Form->model[] = new IntField(_("Alias length"),'aliaslen',_("Number of digits in useralias for such a customer."));

$HD_Form->model[] = new RevRefTxt(_("Patterns"),'pat','id','cc_numplan_pattern','nplan','nick',_("Dial patterns for this plan."));

$HD_Form->model[] = new DelBtnField();

require("PP_page.inc.php");

?>
