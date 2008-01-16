<?php
include ("./lib/defines.php");
include ("./lib/module.access.php");
include (DIR_COMMON."Form.inc.php");
include (DIR_COMMON."Class.HelpElem.inc.php");
include (DIR_COMMON."Form/Class.SqlRefField.inc.php");

$menu_section='menu_admin';

HelpElem::DoHelp(_("Adminstrators"));

$HD_Form= new FormHandler('cc_ui_authen',_("Administrators"),_("Administrator"));
$HD_Form->checkRights(ACX_ADMINISTRATOR);
$HD_Form->init();

$PAGE_ELEMS[] = &$HD_Form;
$PAGE_ELEMS[] = new AddNewButton($HD_Form);

$HD_Form->model[] = new PKeyFieldEH(_("ID"),'userid','5%');
$HD_Form->model[] = new TextFieldEH(_("Login"),'login');
$HD_Form->model[] = dontList(new PasswdField(_("Password"),'password'));
$HD_Form->model[] = new TextField(_("Name"),'name');
$HD_Form->model[] = new IntField(_("Group"),'groupid');

$HD_Form->model[] = new TextField(_("Direction"),'direction');


// $HD_Form->model[] = new GroupField(array(new EditBtnField(),new DelBtnField()));
$HD_Form->model[] = new DelBtnField();

require("PP_page.inc.php");

?>
