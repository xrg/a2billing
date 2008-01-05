<?php
include ("./lib/defines.php");
include ("./lib/module.access.php");
include (DIR_COMMON."Form.inc.php");
include (DIR_COMMON."Class.HelpElem.inc.php");
include (DIR_COMMON."Form/Class.SqlRefField.inc.php");

$menu_section='menu_servers';

HelpElem::DoHelp(gettext("Server are used by the callback system through the asterisk manager in order to initiate the callback and outbound a call to your customers. You can add/modify the callback server here that are going to be use here. The AGI and callback mode need to be install on those machine."),
	'network_local.png');

$HD_Form= new FormHandler('cc_a2b_server',_("Servers"),_("Server"));
$HD_Form->checkRights(ACX_ADMINISTRATOR);
$HD_Form->init();

$PAGE_ELEMS[] = &$HD_Form;
$PAGE_ELEMS[] = new AddNewButton($HD_Form);

$HD_Form->model[] = new PKeyFieldEH(_("ID"),'id','5%');

$HD_Form->model[] = new TextFieldEH(_("Host"),'host',_("Host name"));
$HD_Form->model[] = new SqlRefField(_("Group"),'grp','cc_server_group','id','name', _("Server group"));

$HD_Form->model[] = new TextField(_("IP"),'ip',_("IPv4 address of server"));
$HD_Form->model[] = new TextField(_("Login"),'manager_username',_("Username of manager for that host"));
$HD_Form->model[] = new PasswdField(_("Secret"),'manager_secret','alnum');
$HD_Form->model[] = new TextField(_("DB user"),'db_username',_("The database username used by this host. This field must match the connection settings of the asterisk realtime."));


// $HD_Form->model[] = new GroupField(array(new EditBtnField(),new DelBtnField()));
$HD_Form->model[] = new DelBtnField();

require("PP_page.inc.php");


?>
