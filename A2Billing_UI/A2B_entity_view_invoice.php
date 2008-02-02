<?php
require_once ("./lib/defines.php");
require_once ("./lib/module.access.php");
require_once (DIR_COMMON."Form.inc.php");
require_once (DIR_COMMON."Class.HelpElem.inc.php");
require_once (DIR_COMMON."Form/Class.SqlRefField.inc.php");
require_once (DIR_COMMON."Form/Class.TimeField.inc.php");
require_once (DIR_COMMON."Form/Class.ClauseField.inc.php");

$menu_section='menu_invoicing';

HelpElem::DoHelp(gettext("Invoices for regular clients"));

$HD_Form= new FormHandler('cc_invoices',_("Invoices"),_("Invoice"));
$HD_Form->checkRights(ACX_INVOICING);
$HD_Form->default_order = 'id';
$HD_Form->default_sens = 'DESC';
$HD_Form->init();

$PAGE_ELEMS[] = &$HD_Form;
// $PAGE_ELEMS[] = new AddNewButton($HD_Form);

$HD_Form->model[] = new FreeClauseField('agentid IS NULL');
$HD_Form->model[] = new PKeyFieldEH(_("ID"),'id');
$HD_Form->model[] = new SqlRefField(_("Card"), "cardid","cc_card", "id", "username");
$HD_Form->model[] = new TextField(_("Ref"), "orderref");

$HD_Form->model[] = new DateTimeFieldDH(_("Start"),'cover_startdate');
$HD_Form->model[] = new DateTimeField(_("Finish"),'cover_enddate');
$HD_Form->model[] = dontList(new DateTimeField(_("Created"),'created',_("Date this invoice was registered")));

$HD_Form->model[] = dontList(new MoneyField(_("Amount"),'amount'));
$HD_Form->model[] = dontList(new MoneyField(_("Tax"),'tax'));

$HD_Form->model[] = new MoneyField(_("Total"),'total');
$HD_Form->model[] = new IntFieldN(_("Type"), "invoicetype" /*,"cc_texts", "id", "txt"*/);
//end($HD_Form->model)->refclause = "lang = 'C'";

$HD_Form->model[] = dontList(new TextFieldN(_("Filename"), "filename"));

//$HD_Form->model[] = new SqlBigRefField(_("Invoice"), "invoice_id","cc_invoices", "id", "orderref");
//end($HD_Form->model)->refclause = "agentid IS NOT NULL";

//$HD_Form->model[] = dontList( new TextAreaField(_("Description"),'descr'));

$ilist = array();
$ilist[]  = array("0", _("Unpaid"));
$ilist[]  = array('1',_('Sent-unpaid'));
$ilist[]  = array('2',_('Sent-paid'));
$ilist[]  = array('3',_('Paid'));

$HD_Form->model[] = new RefField(_("Status"),'payment_status', $ilist);

$detBtn = new OtherBtnField();
$detBtn->title=_("View");
$detBtn->url = "invoices_card.php?";
$detBtn->extra_params=array('id' =>'id');

$HD_Form->model[] = new GroupField(array( $detBtn, new DelBtnField()));


require("PP_page.inc.php");
?>
