<?php
require_once ("./lib/defines.php");
require_once ("./lib/module.access.php");
require_once (DIR_COMMON."Form.inc.php");
require_once (DIR_COMMON."Class.HelpElem.inc.php");
require_once (DIR_COMMON."Form/Class.RevRef.inc.php");
require_once (DIR_COMMON."Form/Class.RevRefForm.inc.php");
require_once (DIR_COMMON."Form/Class.TimeField.inc.php");

$menu_section='menu_config';


HelpElem::DoHelp(_("Provisions are rules that define how a settings (provisioning) script will be generated."));

$HD_Form= new FormHandler('provision_group',_("Provision Groups"),_("Provision Group"));
$HD_Form->checkRights(ACX_ADMINISTRATOR);
$HD_Form->init();

$PAGE_ELEMS[] = &$HD_Form;
$PAGE_ELEMS[] = new AddNewButton($HD_Form);

$HD_Form->model[] = new PKeyFieldEH(_("ID"),'id');

$HD_Form->model[] = new TextFieldEH(_("Category"),'categ',_('Category, the "file" which will be generated.'));
$HD_Form->model[] = new TextField(_("Model"),'model',_('Engine used to generate the file.'));

$HD_Form->model[] = new TextField(_("Name"),'name',_('Name of group'));
$HD_Form->model[] = new TextField(_("Sub Name"),'sub_name',_('Second part of name'));
//$HD_Form->model[] = new SqlRefFieldN(_("Alias Length"),'agentid','cc_agent','id','name', _("If cards belong to an agent."));
$HD_Form->model[] = dontList(dontAdd( new IntField(_("Metric"),'metric',_("By adjusting the metrics, order of the generated groups can be enforced."))));
$HD_Form->model[] = dontList(dontAdd( new IntField(_("Options"),'options',_("Option flags for group."))));

$HD_Form->model[] = dontList( new DateTimeFieldN(_("Last changed"),'mtime',_("Last change of group data. May determine whether a provision is needed.")));

//$HD_Form->model[] = new RevRefTxt(_("Patterns"),'pat','id','cc_numplan_pattern','nplan','nick',_("Dial patterns for this plan."));

$HD_Form->model[] = new DelBtnField();

$tmp = new RevRefForm(_("Rule"),'rul','id','provisions','grp_id');
$HD_Form->meta_elems[] = $tmp;
	$tmp->Form->checkRights(ACX_ADMINISTRATOR);
	$tmp->Form->init();
	$tmp->Form->model[] = new PKeyFieldEH(_("ID"),'id');
	$tmp->Form->model[]= new TextFieldEH(_("Name"),'name',_('Name of setting'));
	$tmp->Form->model[]= new TextField(_("Sub Name"),'sub_name',_('Second part of setting name'));
	$tmp->Form->model[]= new TextField(_("Value"),'valuef',_('Value, pattern of setting'));
	$tmp->Form->model[] = new IntField(_("Metric"),'metric',_("By adjusting the metrics, order of the generated fields can be enforced."));
		end($tmp->Form->model)->def_value= 10;
	$tmp->Form->model[] = dontList(dontAdd( new IntField(_("Options"),'options')));
	$tmp->Form->model[] = new DelBtnField();
	$tmp->Form->meta_elems[] = new AddNewButton($tmp->Form);

require("PP_page.inc.php");

?>