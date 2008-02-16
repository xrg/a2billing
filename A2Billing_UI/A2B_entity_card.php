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

$menu_section='menu_customers';


HelpElem::DoHelp(gettext("Customers are listed below by card number. Each row corresponds to one customer, along with information such as their call plan, credit remaining, etc.</br>" .
				"The SIP and IAX buttons create SIP and IAX entries to allow direct VoIP connections to the Asterisk server without further authentication."),'vcard.png');

$HD_Form= new FormHandler('cc_card',_("Customers"),_("Customer"));
$HD_Form->checkRights(ACX_CUSTOMER);
$HD_Form->init();

$HD_Form->model[] = new PKeyFieldEH(_("ID"),'id');

$HD_Form->model[] = new TextFieldEH(_("Card number"),'username',_('Card username. Also the PIN for callingcards'));
$HD_Form->model[] = new TextField(_("Card alias"),'useralias',_("Alias, also the number  *-*"));

$HD_Form->model[] = new SqlRefField(_("Group"), "grp","cc_card_group", "id", "name");

$HD_Form->model[] = dontList(new TextField(_("Card pass"),'userpass',_("PIN")));

$HD_Form->model[] = new FloatVolField(_("Credit"),'credit',_("Money now in the card. Positive is credit, negative owes us."));
$HD_Form->model[] = new FloatVolField(_("Credit Limit"),'creditlimit',_("Maximum (negative) credit this card can reach, if postpaid."));

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

$HD_Form->model[] = new RefField(_("Status"),'status', $cs_list);

$HD_Form->model[] = dontAdd(dontList(new DateTimeField(_("Creation date"), "creationdate", _("Date the card was created (entered into this system)"))));
$HD_Form->model[] = dontAdd(dontList(new DateTimeFieldN(_("First use"), "firstusedate", _("Date the card made its first call"))));
$HD_Form->model[] = dontAdd(dontList(new DateTimeFieldN(_("Last use"), "lastuse", _("Date the card was last used"))));
$HD_Form->model[] = dontAdd(dontList(new DateTimeFieldN(_("Expire date"), "expirationdate", _("Date the card should expire"))));

$HD_Form->model[] = new TextFieldN(_("First name"),'firstname');
$HD_Form->model[] = new TextFieldN(_("Last name"),'lastname');

$HD_Form->model[] = dontList(new TextAreaField(_("Address"),'address'));

$HD_Form->model[] = dontList(new TextFieldN(_("City"),'city'));
$HD_Form->model[] = dontList(new TextFieldN(_("State"),'state'));

$HD_Form->model[] = dontList(new TextFieldN(_("Country"),'country'));
$HD_Form->model[] = dontList(new TextFieldN(_("Zipcode"),'zipcode'));

$HD_Form->model[] = dontList(new TextFieldN(_("Phone"),'phone'));
$HD_Form->model[] = dontList(new TextFieldN(_("email"),'email'));
$HD_Form->model[] = dontList(new TextFieldN(_("Fax"),'fax'));

$HD_Form->model[] = dontAdd(new IntVolField(_("In use"),'inuse'));

$HD_Form->model[] = dontList(new SqlRefFieldN(_("Currency"),'currency','cc_currencies','currency','name', _("Default currency for new cards in this group. This can later change per card.")));

$HD_Form->model[] = dontAdd(dontList(new IntVolField(_("Times used"),'nbused',_("Total times the card has been used"))));


$HD_Form->model[] = dontList(new DateTimeFieldN(_("Last service"), "servicelastrun", _("Service last run then")));

// TODO fields:
//     "language" text DEFAULT 'en'::text,
//     redial text,
//     nbservice integer DEFAULT 0,
//     id_campaign integer DEFAULT 0,
//     num_trials_done integer DEFAULT 0,
//     callback text,
//     servicelastrun timestamp without time zone,
//     loginkey text,

//end($HD_Form->model)->fieldname ='agent';
// $HD_Form->model[] = new PasswdField(_("Password"),'passwd','alnum',_("Password used by agent to login into the web interface"));
// 
// $HD_Form->model[] = new IntField(gettext("OPTIONS"), "options", null, "7%");
// // $HD_Form->model[] = new RefField(_("LANGUAGE"), "language");
// $HD_Form->model[] = new FloatField(_("CREDIT"), "credit");
// $HD_Form->model[] = new FloatField(_("CLIMIT"), "climit",_("Credit limit of agent"));
// $HD_Form->model[] = new SqlRefField(_("TARIFFG"), "tariffgroup","cc_tariffgroup", "id", "tariffgroupname");
// // $HD_Form->model[] = new RefField(_("CURRENCY").gettext("CUR"), "currency", "5%");
// 
// $actived_list = array();
// $actived_list[] = array('t',gettext("Active"));
// $actived_list[] = array('f',gettext("Inactive"));
// 
// $HD_Form->model[] = new RefField(_("ACTIVATED"), "active", $actived_list,_("Allow the agent to operate"),"4%");
// end($HD_Form->model)->fieldacr =  gettext("ACT");

$HD_Form->model[] = new DelBtnField();

$SEL_Form = new SelectionForm();
$SEL_Form->init();
$SEL_Form->enable($HD_Form->getAction() == 'list');

// todo: search in use
$SEL_Form->model[] = new TextSearchField(_("Card number"),'username');
$SEL_Form->model[] = dontAdd(new SqlRefField(_("Group"), "grp","cc_card_group", "id", "name"));
$SEL_Form->model[] = dontAdd(new RefField(_("Status"),'status', $cs_list));
$SEL_Form->model[] = new TextSearchField(_("Last Name"),'lastname');

$PAGE_ELEMS[] = &$SEL_Form;
$PAGE_ELEMS[] = &$HD_Form;
$PAGE_ELEMS[] = new AddNewButton($HD_Form);

$clauses= $SEL_Form->buildClauses();
foreach($clauses as $cla)
	$HD_Form->model[] = new FreeClauseField($cla);

require("PP_page.inc.php");

?>