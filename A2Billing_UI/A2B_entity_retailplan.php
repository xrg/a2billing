<?php
require_once ("./lib/defines.php");
require_once ("./lib/module.access.php");
require_once (DIR_COMMON."Form.inc.php");
require_once (DIR_COMMON."Class.HelpElem.inc.php");
require_once (DIR_COMMON."Form/Class.TimeField.inc.php");
require_once (DIR_COMMON."Form/Class.RevRef2.inc.php");

$menu_section='menu_ratecard';

HelpElem::DoHelp(gettext("Retail plan is the table of <b>Selling</b> rates offered to our customers"));

$HD_Form= new FormHandler('cc_retailplan',_("Retail Plans"),_("Retail plan"));
$HD_Form->checkRights(ACX_RATECARD);
$HD_Form->init();


$PAGE_ELEMS[] = &$HD_Form;
$PAGE_ELEMS[] = new AddNewButton($HD_Form);

$HD_Form->model[] = new PKeyFieldEH(_("ID"),'id');

$HD_Form->model[] = new TextFieldEH(_("Name"),'name');
$HD_Form->model[] = new TextAreaField(_("Description"),'description');
$HD_Form->model[] = new IntField(_("Metric"),'metric',_("Weight of plan, lower metrics will be preferred at the rate engine."));
end($HD_Form->model)->def_value=10;
$HD_Form->model[] = new DateTimeField(_("Start date"), "start_date", _("Date these rates are valid from"));
	end($HD_Form->model)->def_date='+1 day';
$HD_Form->model[] = new DateTimeField(_("Stop date"), "stop_date", _("Date these rates are valid until."));
	end($HD_Form->model)->def_date='+6 month 1 day';

$HD_Form->model[] = new TimeOWField(_("Period begin"), "starttime", _("Time of week the rate starts to apply"));
end($HD_Form->model)->def_value=0;
$HD_Form->model[] = new TimeOWField(_("Period end"), "endtime", _("Time of week the rate stops apply"));
end($HD_Form->model)->def_value=10079;
//$HD_Form->model[] = new TextField(_("xx"),'xx');
$HD_Form->model[] = new RevRef2(_("Buy plans"),'bplans','id','cc_rtplan_buy','rtid','tpid','cc_tariffplan','id','tariffname',_("Tables to buy from when selling through this plan."));


//RevRef2::html_body($action);

$HD_Form->model[] = new DelBtnField();


require("PP_page.inc.php");
?>
