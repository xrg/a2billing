<?php
require_once ("./lib/defines.php");
require_once ("./lib/module.access.php");
require_once (DIR_COMMON."Form.inc.php");
require_once (DIR_COMMON."Class.HelpElem.inc.php");
require_once (DIR_COMMON."Form/Class.SqlRefField.inc.php");
require_once (DIR_COMMON."Form/Class.VolField.inc.php");
require_once (DIR_COMMON."Form/Class.TimeField.inc.php");
require_once (DIR_COMMON."Form/Class.ClauseField.inc.php");

$menu_section='menu_billing';

HelpElem::DoHelp(_("Charges are money transactions apart from calls. They are used to indicate that the customer should pay or receive extra money."));

$HD_Form= new FormHandler('cc_card_charge',_("Charges"),_("Charge"));
$HD_Form->checkRights(ACX_BILLING);
$HD_Form->init();

$PAGE_ELEMS[] = &$HD_Form;

$HD_Form->model[] = new PKeyFieldEH(_("ID"),'id');
$HD_Form->model[] = new SqlBigRefField(_("Card"), "card","cc_card", "id", "username");
$HD_Form->model[] = new DateTimeFieldDH(_("Date"),'creationdate');
	end($HD_Form->model)->def_date='now';
//$HD_Form->model[] = dontList(new SqlRefField(_("User"), "iduser","cc_ui_authen", "userid", "name"));
$HD_Form->model[] = dontList(new SqlRefFieldN(_("Agent"), "agentid","cc_agent", "id", "login"));
$HD_Form->model[] = dontList(new BoolField(_("From Agent"), "from_agent",_("If checked, this charge was input by the agent, else by an admin.")));
$HD_Form->model[] = dontList(new SqlRefFieldN(_("Checked"), "checked","cc_ui_authen", "userid", "name",_("The user that accepted this charge")));
$HD_Form->model[] = new SqlRefFieldN(_("Type"), "chargetype","cc_texts", "id", "txt");
$lng = getenv('LANG');
if (empty($lng) || ($lng == 'en_US')) $lng = 'C';
end($HD_Form->model)->refclause = "lang = '$lng'";

$HD_Form->model[] = new MoneyField(_("Amount"),'amount',_("Positive charge is when the customer should pay more. Negative means the customer gets refunded."));
$HD_Form->model[] = dontList(new TextAreaField(_("Description"),'description'));
$HD_Form->model[] = new SqlBigRefField(_("Invoice"), "invoice_id","cc_invoices", "id", "orderref");

$HD_Form->model[] = new DelBtnField();

$PAGE_ELEMS[] = new AddNewButton($HD_Form);

require("PP_page.inc.php");

?>
