<?php
require_once ("./lib/defines.php");
require_once ("./lib/module.access.php");
require_once (DIR_COMMON."Form.inc.php");
require_once (DIR_COMMON."Class.HelpElem.inc.php");
require_once (DIR_COMMON."Form/Class.SqlRefField.inc.php");
require_once (DIR_COMMON."Form/Class.VolField.inc.php");
require_once (DIR_COMMON."Form/Class.ClauseField.inc.php");

$menu_section='menu_customers';


HelpElem::DoHelp(_("Customers represent the accounts that get charged for calls."));

$HD_Form= new FormHandler('cc_card',_("Cards"),_("Card"));
$HD_Form->checkRights(ACX_ACCESS);
$HD_Form->init();

$PAGE_ELEMS[] = &$HD_Form;

$HD_Form->model[] = new PKeyFieldEH(_("ID"),'id');
$HD_Form->model[] = new FreeClauseField('grp IN (SELECT id FROM cc_card_group WHERE agentid = \''.
			$_SESSION['agent_id'].'\')');

$HD_Form->model[] = new TextRoFieldEH(_("Card number"),'username',_('Card username. Also the PIN for callingcards'));
$HD_Form->model[] = new TextRoFieldEH(_("Card alias"),'useralias',_("Alias, also the internal number"));

$HD_Form->model[] = new TextFieldN(_("First name"),'firstname');
$HD_Form->model[] = new TextFieldN(_("Last name"),'lastname');

$cs_list = array();
$cs_list[]  = array("0", _("CANCELLED"));
$cs_list[]  = array("1", _("ACTIVE"));
$cs_list[]  = array("2", _("NEW"));
$cs_list[]  = array("3", _("MAIL-WAIT"));
$cs_list[]  = array("4", _("RESERVED"));
$cs_list[]  = array("5", _("EXPIRED"));
$cs_list[]  = array("6", _("UNDERPAY"));
$cs_list[]  = array("7", _("LITIGATION"));
$cs_list[]  = array("8", _("STOPPED"));

$HD_Form->model[] = dontEdit(new RefField(_("Status"),'status', $cs_list));
$HD_Form->model[] = dontEdit(new MoneyField(_("Credit"),'credit',_("Money now in the card. Positive is credit, negative owes us.")));

$HD_Form->model[] = dontList(new TextAreaField(_("Address"),'address'));

$HD_Form->model[] = dontList(new TextFieldN(_("City"),'city'));
$HD_Form->model[] = dontList(new TextFieldN(_("State"),'state'));

$HD_Form->model[] = dontList(new TextFieldN(_("Country"),'country'));
$HD_Form->model[] = dontList(new TextFieldN(_("Zipcode"),'zipcode'));

$HD_Form->model[] = dontList(new TextFieldN(_("Phone"),'phone'));
$HD_Form->model[] = dontList(new TextFieldN(_("email"),'email'));
$HD_Form->model[] = dontList(new TextFieldN(_("Fax"),'fax'));

$HD_Form->model[] = dontEdit( new IntVolField(_("In use"),'inuse'));

$HD_Form->model[] = dontList(new SqlRefFieldN(_("Currency"),'currency','cc_currencies','currency','name', _("This currency affects the invoice and the audible balance information.")));

$HD_Form->views['ask-del'] = $HD_Form->views['delete']= null;
$HD_Form->views['ask-add'] = $HD_Form->views['add']= null;

require("PP_page.inc.php");
?>