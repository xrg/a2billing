<?php
require_once ("./lib/defines.php");
require_once ("./lib/module.access.php");
require_once (DIR_COMMON."Form.inc.php");
require_once (DIR_COMMON."Class.HelpElem.inc.php");
require_once (DIR_COMMON."Form/Class.SqlRefField.inc.php");
require_once (DIR_COMMON."Form/Class.TimeField.inc.php");
require_once (DIR_COMMON."Form/Class.ClauseField.inc.php");

$menu_section='menu_charge';


HelpElem::DoHelp(_("Charges are money transactions apart from calls. They are used to indicate that the customer should pay or receive extra money."));

$HD_Form= new FormHandler('cc_card_charge',_("Charges"),_("Charge"));
$HD_Form->checkRights(ACX_ACCESS);
$HD_Form->init();
// $HD_Form->views['list']=new ListView();
// $HD_Form->views['details'] = new DetailsView();

$PAGE_ELEMS[] = &$HD_Form;
//$PAGE_ELEMS[] = new AddNewButton($HD_Form);
// TODO: put static fields and fix them!
$HD_Form->views['ask-del'] = $HD_Form->views['delete']= null;
$HD_Form->views['ask-add'] = $HD_Form->views['add']= null;

$HD_Form->model[] = new PKeyFieldEH(_("ID"),'id');
$HD_Form->model[] = new ClauseField('agentid', $_SESSION['agent_id']);
$HD_Form->model[] = new SqlBigRefField(_("Card"), "card","cc_card", "id", "username");
$HD_Form->model[] = new DateTimeFieldDH(_("Date"),'creationdate');
$HD_Form->model[] = new SqlRefField(_("Type"), "chargetype","cc_texts", "id", "txt");
$lng = getenv('LANG');
if (empty($lng) || ($lng == 'en_US')) $lng = 'C';
end($HD_Form->model)->refclause = "lang = '$lng'";

$HD_Form->model[] = new MoneyField(_("Amount"),'amount',_("Positive charge is when the customer should pay more. Negative means the customer gets refunded."));

$HD_Form->model[] = dontList(new TextAreaField(_("Description"),'description'));

$HD_Form->model[] = new SqlBigRefField(_("Invoice"), "invoice_id","cc_invoices", "id", "orderref");
	end($HD_Form->model)->does_list = false;
	end($HD_Form->model)->does_add = false;
	end($HD_Form->model)->does_edit = false;
require("PP_page.inc.php");

?>
