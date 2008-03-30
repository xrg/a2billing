<?php
require_once ("./lib/defines.php");
require_once ("./lib/module.access.php");
require_once (DIR_COMMON."Form.inc.php");
require_once (DIR_COMMON."Form/Class.SqlRefField.inc.php");
require_once (DIR_COMMON."Form/Class.VolField.inc.php");
require_once (DIR_COMMON."Form/Class.TimeField.inc.php");

require_once (DIR_COMMON."Class.HelpElem.inc.php");

$menu_section='menu_billing';
HelpElem::DoHelp(_("Subscriptions are customers (cards) being attached to a recurring fee or special service."));

$HD_Form= new FormHandler('card_subscription',_("Subscriptions"),_("Subscription"));
$HD_Form->checkRights(ACX_BILLING);
$HD_Form->init();


$PAGE_ELEMS[] = &$HD_Form;
$PAGE_ELEMS[] = new AddNewButton($HD_Form);

$HD_Form->model[] = new PKeyFieldEH(_("ID"),'id');

$HD_Form->model[] = new SqlBigRefField(_("Card"), "card","cc_card", "id", "username");
$HD_Form->model[] = new SqlRefField(_("Kind"), "template","subscription_template", "id", "name");

$cs_list = array();
$cs_list[]  = array("0", _("Inactive"));
$cs_list[]  = array("1", _("Active"));
//$cs_list[]  = array("2", _("..."));
$HD_Form->model[] = dontAdd(new RefField(_("Status"),'status', $cs_list));


$HD_Form->model[] = dontAdd(dontList(new DateTimeField(_("Creation date"), "creationdate", _("Date the subscription was registered"))));
	end($HD_Form->model)->fieldacr=_("Creat");
$HD_Form->model[] = new DateTimeFieldN(_("Activation date"), "activedate", _("Date it becomes active"));
	end($HD_Form->model)->fieldacr=_("Activ");
$HD_Form->model[] = new DateTimeFieldN(_("Expire date"), "expiredate", _("After this date it is no longer charged or used."));
	end($HD_Form->model)->fieldacr=_("Exp.");

// $HD_Form->model[] = dontList(new SqlRefFieldN(_("CLID Rules"), "rnplan","cc_re_numplan", "id", "name"));

$HD_Form->model[] = new DelBtnField();

require("PP_page.inc.php");

?>
