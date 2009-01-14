<?php
include ("./lib/defines.php");
include ("./lib/module.access.php");
include (DIR_COMMON."Form.inc.php");
include (DIR_COMMON."Class.HelpElem.inc.php");
include (DIR_COMMON."Form/Class.SqlRefField.inc.php");
include (DIR_COMMON."Form/Class.ListBitField.inc.php");

$menu_section='menu_admin';

HelpElem::DoHelp(_("Adminstrators"));

$HD_Form= new FormHandler('cc_ui_authen',_("Administrators"),_("Administrator"));
$HD_Form->checkRights(ACX_ADMINISTRATOR);
$HD_Form->init();

$PAGE_ELEMS[] = &$HD_Form;
$PAGE_ELEMS[] = new AddNewButton($HD_Form);

$HD_Form->model[] = new PKeyFieldEH(_("ID"),'userid','5%');
$HD_Form->model[] = new TextFieldEH(_("Login"),'login');
$HD_Form->model[] = dontList(new PasswdField(_("Password"),'password','alnum'));
$HD_Form->model[] = new TextField(_("Name"),'name');
$HD_Form->model[] = new IntField(_("Group"),'groupid');
	end($HD_Form->model)->def_value = 1; 

$right_list[] = array( ACX_CUSTOMER, _("Customers"));
$right_list[] = array( ACX_BILLING, _("Billing"));
$right_list[] = array( ACX_RATECARD, _("Ratecard"));
$right_list[] = array( ACX_TRUNK, _("Trunk"));
$right_list[] = array( ACX_CALL_REPORT, _("Call report"));
$right_list[] = array( ACX_CRONT_SERVICE, _("Cron service"));
$right_list[] = array( ACX_ADMINISTRATOR, _("Administrator"));
$right_list[] = array( ACX_FILE_MANAGER, _("File manager"));
$right_list[] = array( ACX_MISC, _("Misc"));
$right_list[] = array( ACX_DID, _("Did"));
$right_list[] = array( ACX_CALLBACK, _("Call back"));
$right_list[] = array( ACX_OUTBOUNDCID, _("Outbound CID"));
$right_list[] = array( ACX_PACKAGEOFFER, _("Package offer"));
$right_list[] = array( ACX_PRED_DIALER, _("Predictive dialer"));
$right_list[] = array( ACX_INVOICING, _("Invoicing"));
$right_list[] = array( ACX_AGENTS, _("Agents"));
$right_list[] = array( ACX_NUMPLAN, _("Numplans"));
$right_list[] = array( ACX_SERVERS, _("Servers"));
$right_list[] = array( ACX_PRICING, _("Pricing"));
$right_list[] = array( ACX_QUEUES, _("Queues"));
$right_list[] = array( ACX_NETMON, _("Net Mon"));


$HD_Form->model[] = dontList( new ListBitField(_("Permissions"),'perms',$right_list));
$HD_Form->model[] = dontList(dontAdd(new BoolField(_("Read Only"),'readonly')));
$HD_Form->model[] = new TextField(_("Direction"),'direction');


// $HD_Form->model[] = new GroupField(array(new EditBtnField(),new DelBtnField()));
$HD_Form->model[] = new DelBtnField();

require("PP_page.inc.php");

?>
