<?php
require_once ("./lib/defines.php");
require_once ("./lib/module.access.php");
require_once (DIR_COMMON."Form.inc.php");
require_once (DIR_COMMON."Class.HelpElem.inc.php");
require_once (DIR_COMMON."Form/Class.SqlRefField.inc.php");
require_once (DIR_COMMON."Form/Class.TabField.inc.php");
require_once (DIR_COMMON."Form/Class.TimeField.inc.php");
require_once (DIR_COMMON."Class.JQuery.inc.php");

$menu_section='menu_queues';

HelpElem::DoHelp(_("Lists the users (aka. agents) which are subscribed into queues."));

$HD_Form= new FormHandler('ast_queue_member',_("Members"),_("Member"));
$HD_Form->checkRights(ACX_QUEUES);
$HD_Form->default_order='id';
$HD_Form->default_sens='ASC';
$HD_Form->init();

$PAGE_ELEMS[] = &$HD_Form;
$PAGE_ELEMS[] = new AddNewButton($HD_Form);

$HD_Form->model[] = new PKeyFieldEH(_("ID"),'id');
$HD_Form->model[] = new SqlRefField(_("Queue"),'que','ast_queue','id','name');

// SqlRefFieldToolTip
$HD_Form->model[] = new SqlBigRefField(_("User"), "usr","cc_ast_users_v", "id", "name");
	end($HD_Form->model)->SetRefEntity("A2B_entity_astuser.php");
	end($HD_Form->model)->SetRefEntityL("A2B_entity_astuser.php");
	end($HD_Form->model)->SetEditTitle(_("Ast. User"));
	//end($HD_Form->model)->SetCaptionTooltip(_("Information about the card holder :"));
// 	end($HD_Form->model)->SetRefTooltip("A2B_entity_astuser.php");

// TODO: refField
$HD_Form->model[] = new IntFieldN(_("Mod"),'mode',_("Mode ..."));
$HD_Form->model[] = new IntFieldN(_("Penalty"),'penalty',_("Penalty"));
$HD_Form->model[] = new BoolField(_("Paused"),'paused',_("If this member is currently paused."));


$HD_Form->model[] = new TextFieldN(_("Period"),'tperiod',_("Member will only participate at that time period."));
$HD_Form->model[] = dontList( new TextFieldN(_("Parameter"),'parm',_("Additional parameter for some modes.")));

$HD_Form->model[] = new GroupField(array(new EditBtnField(),new DelBtnField()));

require("PP_page.inc.php");
?>
