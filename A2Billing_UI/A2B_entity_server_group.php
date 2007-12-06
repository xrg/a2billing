<?php
include ("./lib/defines.php");
include ("./lib/module.access.php");
include (DIR_COMMON."Form.inc.php");
include (DIR_COMMON."Class.HelpElem.inc.php");


$menu_section='menu_servers';


HelpElem::DoHelp(gettext("Group of server define the set of servers that are going to be used by the callback system. A callback is bound to a group of server, those server will be used to dispatch the callback requests."),
	'yast_multihead.png');

$HD_Form= new FormHandler('cc_server_group',_("Server Groups"),_("Server group"));
$HD_Form->checkRights(ACX_ADMINISTRATOR);
$HD_Form->init();

$BODY_ELEMS[] = &$HD_Form;

$HD_Form->model[] = new PKeyFieldEH(_("ID"),'id','5%');

$HD_Form->model[] = new TextFieldEH(_("Name"),'name',_("Group name"));
$HD_Form->model[] = new TextAreaField(_("Description"),'description', 35);

// $HD_Form->model[] = new GroupField(array(new EditBtnField(),new DelBtnField()));
$HD_Form->model[] = new DelBtnField();

require("PP_page.inc.php");
?>
