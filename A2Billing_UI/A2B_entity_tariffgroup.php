<?php
require_once ("./lib/defines.php");
require_once ("./lib/module.access.php");
require_once (DIR_COMMON."Form.inc.php");
require_once (DIR_COMMON."Class.HelpElem.inc.php");
//require_once (DIR_COMMON."Form/Class.SqlRefField.inc.php");

$menu_section='menu_ratecard';

HelpElem::DoHelp(gettext("Tariff groups define which rates will apply to each group of customers."));

$HD_Form= new FormHandler('cc_tariffgroup',_("Tariff Groups"),_("Tariff group"));
$HD_Form->checkRights(ACX_RATECARD);
$HD_Form->init();


$PAGE_ELEMS[] = &$HD_Form;
$PAGE_ELEMS[] = new AddNewButton($HD_Form);

$HD_Form->model[] = new PKeyFieldEH(_("ID"),'id');

$HD_Form->model[] = new TextFieldEH(_("Name"),'name');
$HD_Form->model[] = new IntField(_("LCR type"), "lcrtype", _("LCR mode") . " (deprecated?)");
//$HD_Form->model[] = new TextField(_("xx"),'xx');


//RevRef2::html_body($action);

$HD_Form->model[] = new DelBtnField();


require("PP_page.inc.php");

?>
