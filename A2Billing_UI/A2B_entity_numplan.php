<?php
require_once ("./lib/defines.php");
require_once ("./lib/module.access.php");
require_once (DIR_COMMON."Form.inc.php");
require_once (DIR_COMMON."Class.HelpElem.inc.php");
require_once (DIR_COMMON."Form/Class.RevRef.inc.php");
require_once (DIR_COMMON."Form/Class.RevRefForm.inc.php");

$menu_section='menu_config';


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

//$HD_Form->model[] = new RevRefTxt(_("Patterns"),'pat','id','cc_numplan_pattern','nplan','nick',_("Dial patterns for this plan."));

$HD_Form->model[] = new DelBtnField();

$tmp = new RevRefForm(_("Pattern"),'pat','id','cc_numplan_pattern','nplan');
$HD_Form->meta_elems[] = $tmp;
	$tmp->Form->checkRights(ACX_NUMPLAN);
	$tmp->Form->init();
	$tmp->Form->model[] = new PKeyFieldEH(_("ID"),'id');
	$tmp->Form->model[]= new TextField(_("Find"),'find',_('Prefix to match'));
	$tmp->Form->model[]= new TextField(_("Replace"),'repl',_('String to replace the match prefix with'));
	$tmp->Form->model[]= new TextFieldEH(_("Name"),'nick',_('Name of pattern'));
	$tmp->Form->model[] = new DelBtnField();
	$tmp->Form->meta_elems[] = new AddNewButton($tmp->Form);

require("PP_page.inc.php");

?>
