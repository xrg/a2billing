<?php
require_once ("./lib/defines.php");
require_once ("./lib/module.access.php");
require_once (DIR_COMMON."Form.inc.php");
require_once (DIR_COMMON."Class.HelpElem.inc.php");
require_once (DIR_COMMON."Form/Class.SqlRefField.inc.php");
require_once (DIR_COMMON."Form/Class.TimeField.inc.php");

$menu_section='menu_agents';


HelpElem::DoHelp(gettext("Agent payments are money transactions between agents and us, the company."));

$HD_Form= new FormHandler('cc_agentpay',_("Payments"),_("Payment"));
$HD_Form->checkRights(ACX_AGENTS);
$HD_Form->default_order='id';
$HD_Form->default_sens='DESC';
$HD_Form->init();

$PAGE_ELEMS[] = &$HD_Form;
$PAGE_ELEMS[] = new AddNewButton($HD_Form);

$HD_Form->model[] = new PKeyFieldEH(_("ID"),'id');
$HD_Form->model[] = new SqlRefField(_("Agent"), "agentid","cc_agent", "id", "login");

$HD_Form->model[] = new DateTimeField(_("Date"),'date');
	end($HD_Form->model)->def_date='now';

$HD_Form->model[] = new MoneyField(_("Credit"),'credit');
$HD_Form->model[] = new SqlRefField(_("Type"), "pay_type","cc_texts", "id", "txt");
end($HD_Form->model)->refclause = "lang = 'C'";

$HD_Form->model[] = new SqlBigRefField(_("Invoice"), "invoice_id","cc_invoices", "id", "orderref");
//end($HD_Form->model)->refclause = "agentid IS NOT NULL";

$HD_Form->model[] = dontList( new TextAreaField(_("Description"),'descr'));

$HD_Form->model[] = new DelBtnField();


require("PP_page.inc.php");
?>
