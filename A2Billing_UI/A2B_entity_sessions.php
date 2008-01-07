<?php
require_once ("./lib/defines.php");
require_once ("./lib/module.access.php");
require_once (DIR_COMMON."Form.inc.php");
require_once (DIR_COMMON."Class.HelpElem.inc.php");
require_once (DIR_COMMON."Form/Class.SqlRefField.inc.php");
require_once (DIR_COMMON."Form/Class.TimeField.inc.php");

$menu_section='menu_agents';


HelpElem::DoHelp(gettext("Callshop essions. Each session starts when the customer enters a booth and ends when he leaves (preferably having paid the account)."));

$HD_Form= new FormHandler('cc_shopsessions',_("Sessions"),_("Session"));
$HD_Form->checkRights(ACX_AGENTS);
$HD_Form->init();

$PAGE_ELEMS[] = &$HD_Form;
$PAGE_ELEMS[] = new AddNewButton($HD_Form);

$HD_Form->model[] = new PKeyField(_("ID"),'id');
$HD_Form->model[] = new DateTimeField(_("Start"),'starttime');
$HD_Form->model[] = new DateTimeField(_("End"),'endtime');

$HD_Form->model[] = new TextField(_("State"),'state');

$HD_Form->model[] = new SqlRefField(_("Booth"), "booth","cc_booth", "id", "name");
$HD_Form->model[] = new SqlRefField(_("Card"), "card","cc_card", "id", "username");

$detbtn = new OtherBtnField();
$detbtn->title = _("Details");
$detbtn->url = "invoices_cshop.php?";
$detbtn->extra_params = array('sid' => 'id');
$HD_Form->model[] = &$detbtn;

//$HD_Form->model[] = new DelBtnField();


require("PP_page.inc.php");

?>
