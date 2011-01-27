<?php
include ("./lib/defines.php");
include ("./lib/module.access.php");
include ("a2blib/Form.inc.php");
include ("a2blib/Class.HelpElem.inc.php");
include ("a2blib/Form/Class.SqlRefField.inc.php");

$menu_section='menu_admin';

HelpElem::DoHelp(_("Configuration entries"),
	'network_local.png');

$HD_Form= new FormHandler('cc_sysconf',_("Configs"),_("Config"));
$HD_Form->checkRights(ACX_ADMINISTRATOR);
$HD_Form->init();

$PAGE_ELEMS[] = &$HD_Form;
$PAGE_ELEMS[] = new AddNewButton($HD_Form);

$HD_Form->model[] = new PKeyFieldEH(_("ID"),'id','5%');
$HD_Form->model[] = new TextField(_("Group"),'grp');
$HD_Form->model[] = new TextFieldEH(_("Name"),'name');

$HD_Form->model[] = new TextField(_("Value"),'val');


// $HD_Form->model[] = new GroupField(array(new EditBtnField(),new DelBtnField()));
$HD_Form->model[] = new DelBtnField();

require("PP_page.inc.php");
?>
