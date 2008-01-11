<?php
require_once ("./lib/defines.php");
require_once ("./lib/module.access.php");
require_once (DIR_COMMON."Form.inc.php");
require_once (DIR_COMMON."Class.HelpElem.inc.php");
require_once (DIR_COMMON."Form/Class.ClauseField.inc.php");
require_once (DIR_COMMON."Form/Class.TimeField.inc.php");
require_once (DIR_COMMON."Form/Class.SqlRefField.inc.php");

$menu_section='menu_info';

HelpElem::DoHelp(_("These are the selling rates to each destination"));

$HD_Form= new FormHandler('cc_agent_current_rates_v',_("Rates"),_("Rate"));
$HD_Form->checkRights(ACX_ACCESS);
$HD_Form->init(null,false);
$HD_Form->views['list']=new ListView();
//$HD_Form->views['details'] = new DetailsView();

$PAGE_ELEMS[] = &$HD_Form;

//$HD_Form->model[] = new PKeyField(_("ID"),'id');
$HD_Form->model[] = new ClauseField('agentid',$_SESSION['agent_id']);

$HD_Form->model[] = new TextField(_("Destination"), "destination");

$HD_Form->model[] = new MoneyField(_("Rate"),'rateinitial');
$HD_Form->model[] = new MoneyField(_("Charge"),'charge_once');
$HD_Form->model[] = new SecondsField(_("Block"),'billingblock');
$HD_Form->model[] = new TextField(_("Tariff"), "tg_name");
end($HD_Form->model)->fieldexpr = "gettext(tg_name,'".getenv('LANG') ."')";

require("PP_page.inc.php");

?>
