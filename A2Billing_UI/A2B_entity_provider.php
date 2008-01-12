<?php
require_once ("./lib/defines.php");
require_once ("./lib/module.access.php");
require_once (DIR_COMMON."Form.inc.php");
require_once (DIR_COMMON."Class.HelpElem.inc.php");
require_once (DIR_COMMON."Form/Class.RevRef2.inc.php");

$menu_section='menu_servers';

HelpElem::DoHelp(_("Providers are the companies that offer us trunks."));

$HD_Form= new FormHandler('cc_provider',_("Providers"),_("Provider"));
$HD_Form->checkRights(ACX_SERVERS);
$HD_Form->init();


$PAGE_ELEMS[] = &$HD_Form;
$PAGE_ELEMS[] = new AddNewButton($HD_Form);

$HD_Form->model[] = new PKeyFieldEH(_("ID"),'id');

$HD_Form->model[] = new TextFieldEH(_("Name"),'provider_name');
$HD_Form->model[] = new TextAreaField(_("Description"),'description');

//RevRef2::html_body($action);

$HD_Form->model[] = new DelBtnField();


require("PP_page.inc.php");
?>
