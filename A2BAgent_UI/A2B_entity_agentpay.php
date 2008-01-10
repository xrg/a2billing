<?php
require_once ("./lib/defines.php");
require_once ("./lib/module.access.php");
require_once (DIR_COMMON."Form.inc.php");
require_once (DIR_COMMON."Class.HelpElem.inc.php");
require_once (DIR_COMMON."Form/Class.ClauseField.inc.php");
require_once (DIR_COMMON."Form/Class.TimeField.inc.php");
require_once (DIR_COMMON."Form/Class.SqlRefField.inc.php");

$menu_section='menu_reports';


HelpElem::DoHelp(gettext("Agent payments are money transactions between agents and the phone company."));

$HD_Form= new FormHandler('cc_agentpay',_("Payments"),_("Payment"));
$HD_Form->checkRights(ACX_ACCESS);
$HD_Form->init(null,false);
$HD_Form->views['list']=new ListView();
$HD_Form->views['details'] = new DetailsView();

$PAGE_ELEMS[] = &$HD_Form;

$HD_Form->model[] = new PKeyField(_("ID"),'id');
$HD_Form->model[] = new ClauseField('agentid',$_SESSION['agent_id']);

$HD_Form->model[] = new DateTimeFieldDH(_("Date"),'date');
$HD_Form->model[] = new MoneyField(_("Credit"),'credit');
$HD_Form->model[] = new TextField(_("Type"), "pay_type");
end($HD_Form->model)->fieldexpr = "gettexti(pay_type,'".getenv('LANG') ."')";

$HD_Form->model[] = dontList(new SqlBigRefField(_("Invoice"), "invoice_id","cc_invoices", "id", "orderref"));
//end($HD_Form->model)->refclause = "agentid IS NOT NULL";

$HD_Form->model[] = dontList( new TextAreaField(_("Description"),'descr'));

require("PP_page.inc.php");
?>
