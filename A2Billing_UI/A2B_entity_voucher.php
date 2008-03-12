<?php
require_once ("./lib/defines.php");
require_once ("./lib/module.access.php");
require_once (DIR_COMMON."Form.inc.php");
require_once (DIR_COMMON."Class.HelpElem.inc.php");
require_once (DIR_COMMON."Form/Class.SqlRefField.inc.php");
require_once (DIR_COMMON."Form/Class.VolField.inc.php");
require_once (DIR_COMMON."Form/Class.TimeField.inc.php");

$menu_section='menu_billing';

/*
 *   id bigserial NOT NULL,
  creationdate timestamp DEFAULT now(),
  usedate timestamp,
  expirationdate timestamp,
  voucher text NOT NULL,
  card_id int8,
  tag text,
  credit numeric(12,4) NOT NULL,
  activated bool NOT NULL DEFAULT true,
  used int4 DEFAULT 0,
  currency varchar(3) DEFAULT 'USD'::character varying,

 */


HelpElem::DoHelp(gettext("Vouchers, Create a single voucher, defining such properties as credit, tag, currency etc, click confirm when finished. " .
						 "<br/> The customer applies voucher credit to their card via the customer interface or via an IVR menu."));

$HD_Form= new FormHandler('vouchers',_("Vouchers"),_("Voucher"));
$HD_Form->checkRights(ACX_BILLING);
$HD_Form->init();

$PAGE_ELEMS[] = &$HD_Form;
$PAGE_ELEMS[] = new AddNewButton($HD_Form);

$HD_Form->model[] = new PKeyFieldEH(_("ID"),'id');

$HD_Form->model[] = new TextFieldEH(_("Voucher"),'voucher');
$HD_Form->model[] = new TextFieldEH(_("Tag"),'tag',_("Enter the tag."));
$HD_Form->model[] = new FloatVolField(_("Credit"),'credit',_("Money in the voucher. Positive is credit."));
$HD_Form->model[] = new SqlRefFieldN(_("Currency"),'currency','cc_currencies','currency','name', _("Default currency for this voucher."));


$HD_Form->model[] = new SqlBigRefField(_("CardNumber"), "card_id","cc_card", "id", "username");
	end($HD_Form->model)->SetRefEntity("A2B_entity_card.php");
	end($HD_Form->model)->SetRefEntityL("A2B_entity_card.php");
	end($HD_Form->model)->SetEditTitle(_("Card ID"));

$HD_Form->model[] = dontAdd(dontEdit(new DateTimeField(_("Creation Date"), "creationdate", _("Date the voucher was created (entered into this system)"))));
$HD_Form->model[] = dontAdd(dontEdit(new DateTimeField(_("Used Date"), "usedate", _("Date the voucher has been used."))));
$HD_Form->model[] = new DateTimeFieldN(_("EXPIRY DATE"), "expirationdate", _("Date the voucher will expire."));
	end($HD_Form->model)->def_date='+6 month 1 day';
	
$actived_list = array();
$actived_list[] = array('t',_("Active"));
$actived_list[] = array('f',_("Inactive"));

$HD_Form->model[] = new RefField(_("ACTIVATED"), "activated", $actived_list,_("Enable or disable the voucher"),"4%");
end($HD_Form->model)->fieldacr =  gettext("ACT");

$yes_no_list = array();
$yes_no_list[] = array('1',_("Yes"));
$yes_no_list[] = array('0',_("No"));

$HD_Form->model[] = new RefField(_("USED"), "used", $yes_no_list);





$HD_Form->model[] = new DelBtnField();


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