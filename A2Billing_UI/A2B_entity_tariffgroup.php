<?php
require_once ("./lib/defines.php");
require_once ("./lib/module.access.php");
require_once ("a2blib/Form.inc.php");
require_once ("a2blib/Class.HelpElem.inc.php");
require_once ("a2blib/Form/Class.RevRef2.inc.php");

$menu_section='menu_ratecard';

HelpElem::DoHelp(gettext("Tariff groups define which rates will apply to each group of customers."));

$HD_Form= new FormHandler('cc_tariffgroup',_("Tariff Groups"),_("Tariff group"));
$HD_Form->checkRights(ACX_RATECARD);
$HD_Form->init();


$PAGE_ELEMS[] = &$HD_Form;
$PAGE_ELEMS[] = new AddNewButton($HD_Form);

$HD_Form->model[] = new PKeyFieldEH(_("ID"),'id');

$HD_Form->model[] = new TextFieldEH(_("Name"),'name');
$HD_Form->model[] = new TextField(_("Public Name"),'pubname',_("Public name is displayed to customers, agents. You may want it to be different than the internal name above."));
end($HD_Form->model)->fieldacr= _("P Name");

$HD_Form->model[] = new IntField(_("LCR type"), "lcrtype", _("LCR mode") . " (deprecated?)");
//$HD_Form->model[] = new TextField(_("xx"),'xx');
$HD_Form->model[] = new RevRef2(_("Sell plans"),'tplans','id','cc_tariffgroup_plan','tgid','rtid','cc_retailplan','id','name',_("Customer of this group will use those selling plans."));


//RevRef2::html_body($action);

$HD_Form->model[] = new DelBtnField();


require("PP_page.inc.php");

?>
