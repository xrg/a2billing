<?php
require_once ("./lib/defines.php");
require_once ("./lib/module.access.php");
require_once (DIR_COMMON."Form.inc.php");
require_once (DIR_COMMON."Class.HelpElem.inc.php");
require_once (DIR_COMMON."Form/Class.SqlRefField.inc.php");
require_once (DIR_COMMON."Form/Class.VolField.inc.php");
require_once (DIR_COMMON."Form/Class.TimeField.inc.php");
require_once (DIR_COMMON."Form/Class.ClauseField.inc.php");
require_once (DIR_COMMON."Form/Class.TextSearchField.inc.php");
require_once (DIR_COMMON."Form/Class.SelectionForm.inc.php");
require_once (DIR_COMMON."Form/Class.ListTeXView.inc.php");
require_once (DIR_COMMON."Form/Class.ListCsvView.inc.php");

$menu_section='menu_billing';


HelpElem::DoHelp(gettext("Vouchers, Create a single voucher, defining such properties as credit, tag, currency etc, click confirm when finished. " .
						 "<br/> The customer applies voucher credit to their card via the customer interface or via an IVR menu."));

$HD_Form= new FormHandler('vouchers',_("Vouchers"),_("Voucher"));
$HD_Form->checkRights(ACX_BILLING);
$HD_Form->init();
$HD_Form->views['exportLT'] =new ListTeXView();
$HD_Form->views['exportCSV'] =new ListCsvView();

$HD_Form->model[] = new PKeyFieldEH(_("Id"),'id');
$HD_Form->model[] = new TextFieldEH(_("Voucher"),'voucher');
$HD_Form->model[] = new TextFieldEH(_("Tag"),'tag',_("Enter the tag."));
$HD_Form->model[] = new SqlRefField(_("Card group"),'card_grp','cc_card_group','id','name', _("Cards in this group will be able to use the voucher. Also set the currency here!"));
	/*end($HD_Form->model)->refexpr =*/ 
	end($HD_Form->model)->combofield = "name || COALESCE( ' (' || def_currency || ')', '')" ;
$HD_Form->model[] = new FloatField(_("Credit"),'credit',_("Money in the voucher. Positive is credit. It is in group's currency!"));
// $HD_Form->model[] = new SqlRefFieldN(_("Currency"),'currency','cc_currencies','currency','name', _("Default currency for this voucher."));


$HD_Form->model[] = new SqlBigRefField(_("Card Number"), "card_id","cc_card", "id", "username");
	end($HD_Form->model)->SetRefEntity("A2B_entity_card.php");
	end($HD_Form->model)->SetRefEntityL("A2B_entity_card.php");
	end($HD_Form->model)->SetEditTitle(_("Card ID"));

$HD_Form->model[] = dontAdd(dontEdit(new DateTimeField(_("Creation Date"), "creationdate", _("Date the voucher was created (entered into this system)"))));
$HD_Form->model[] = dontAdd(dontEdit(new DateTimeField(_("Used Date"), "usedate", _("Date the voucher has been used."))));
$HD_Form->model[] = new DateTimeFieldN(_("Expiry date"), "expirationdate", _("Date the voucher will expire."));
	end($HD_Form->model)->def_date='+6 month 1 day';
	
$actived_list = array();
$actived_list[] = array('t',_("Active"));
$actived_list[] = array('f',_("Inactive"));

$HD_Form->model[] = new RefField(_("Activated"), "activated", $actived_list,_("Enable or disable the voucher"),"4%");
end($HD_Form->model)->fieldacr =  gettext("ACT");

$HD_Form->model[] = new DelBtnField();



// SEARCH SECTION
$SEL_Form = new SelectionForm();
$SEL_Form->init();
$SEL_Form->enable($HD_Form->getAction() == 'list');

// todo: search in use
$SEL_Form->model[] = new TextSearchField(_("Voucher"),'voucher');
$SEL_Form->model[] = new TextSearchField(_("Tag"),'tag');
$SEL_Form->model[] = dontAdd(new SqlRefField(_("Group"), "card_grp","cc_card_group", "id", "name"));
//$SEL_Form->model[] = dontAdd(new RefField(_("Status"),'status', $cs_list));
$SEL_Form->model[] = dontAdd(new RefField(_("Activated"), "activated", $actived_list,_("Enable or disable the voucher"),"4%"));
$SEL_Form->model[] = new TextSearchField(_("Last Name"),'lastname');

$SEL_Form->appendClauses($HD_Form);

// BUILD PAGE ELEMENTS
$PAGE_ELEMS[] = &$SEL_Form;
$PAGE_ELEMS[] = &$HD_Form;
$PAGE_ELEMS[] = new AddNewButton($HD_Form);

if($HD_Form->getAction()=='exportLT')
	require("PP_LaTeX.inc.php");
elseif($HD_Form->getAction()=='exportCSV')
	require("PP_ExportCSV.inc.php");

else
	require("PP_page.inc.php");


/*
 *
// Code for the Export Functionality
//* Query Preparation.
$_SESSION[$HD_Form->FG_EXPORT_SESSION_VAR]= "SELECT ".$HD_Form -> FG_EXPORT_FIELD_LIST." FROM  $HD_Form->FG_TABLE_NAME";
if (strlen($HD_Form->FG_TABLE_CLAUSE)>1)
	$_SESSION[$HD_Form->FG_EXPORT_SESSION_VAR] .= " WHERE $HD_Form->FG_TABLE_CLAUSE ";
if (!is_null ($HD_Form->FG_ORDER) && ($HD_Form->FG_ORDER!='') && !is_null ($HD_Form->FG_SENS) && ($HD_Form->FG_SENS!=''))
	$_SESSION[$HD_Form->FG_EXPORT_SESSION_VAR].= " ORDER BY $HD_Form->FG_ORDER $HD_Form->FG_SENS";


 */

?>