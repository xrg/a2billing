<?php
require_once ("./lib/defines.php");
require_once ("./lib/module.access.php");
require_once (DIR_COMMON."Form.inc.php");
require_once (DIR_COMMON."Class.HelpElem.inc.php");
require_once (DIR_COMMON."Form/Class.SqlRefField.inc.php");
require_once (DIR_COMMON."Form/Class.RevRef.inc.php");
$menu_section='menu_ratecard';

HelpElem::DoHelp(gettext("Buy rates are the prices paid to the provider for some destination."));

$HD_Form= new FormHandler('cc_buyrate',_("Buy rates"),_("Buy rate"));
$HD_Form->checkRights(ACX_RATECARD);
$HD_Form->init();


$PAGE_ELEMS[] = &$HD_Form;
$PAGE_ELEMS[] = new AddNewButton($HD_Form);

$HD_Form->model[] = new PKeyFieldEH(_("ID"),'id');

$HD_Form->model[] = new TextFieldEH(_("Destination"),'destination');
$HD_Form->model[] = new SqlRefField(_("Plan"),'idtp','cc_tariffplan','id','tariffname', _("Tariff plan"));

$HD_Form->model[] = new FloatField(_("Rate"),'buyrate',_("Price paid to carrier, per minute"));
$HD_Form->model[] = new IntField(_("Init Block"),'buyrateinitblock',_("Set the minimum duration charged by the carrier. (i.e. 30 secs)"));
$HD_Form->model[] = new IntField(_("Increment"),'buyrateincrement',_("Set the billing increment, in seconds (billing block), that the carrier applies. (ie 30 secs)"));

$HD_Form->model[] = new FloatField(_("Quality"),'quality',"");
end($HD_Form->model)->does_add=false;

$HD_Form->model[] = new RevRefTxt(_("Prefixes"),'prefx','id','cc_buy_prefix','brid','dialprefix',_("Dial prefixes covered by this rate."));

//RevRef2::html_body($action);

$HD_Form->model[] = new DelBtnField();


require("PP_page.inc.php");
?>
