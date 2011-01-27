<?php
require_once ("./lib/defines.php");
require_once ("./lib/module.access.php");
require_once ("a2blib/Form.inc.php");
require_once ("a2blib/Form/Class.SqlRefField.inc.php");
require_once ("a2blib/Form/Class.VolField.inc.php");
require_once ("a2blib/Form/Class.TimeField.inc.php");

require_once ("a2blib/Class.HelpElem.inc.php");

$menu_section='menu_did';
HelpElem::DoHelp(_("DID reservations are DID numbers explicitly attached to customers."));

$HD_Form= new FormHandler('did_reservation',_("Reservations"),_("Reservation"));
$HD_Form->checkRights(ACX_DID);
$HD_Form->init();


$PAGE_ELEMS[] = &$HD_Form;
$PAGE_ELEMS[] = new AddNewButton($HD_Form);

$HD_Form->model[] = new PKeyFieldEH(_("ID"),'id');

$HD_Form->model[] = new TextFieldEH(_("DID"),'did',_("The last digits of the DID (after the batch head)."));
$HD_Form->model[] = new TextFieldN(_("Target"),'target',_("The number to call for that DID. Use internal format or leave blank for peer search."));

$HD_Form->model[] = new SqlBigRefField(_("Card"), "card","cc_card", "id", "username");
$HD_Form->model[] = new SqlRefField(_("Template"), "template","ONLY subscription_template", "id", "name");
$HD_Form->model[] = new SqlRefField(_("Batch"), "batch","did_batch", "id", "name");

$cs_list = array();
$cs_list[]  = array("0", _("Inactive"));
$cs_list[]  = array("1", _("Active"));
//$cs_list[]  = array("2", _("..."));
$HD_Form->model[] = dontAdd(new RefField(_("Status"),'status', $cs_list));


$HD_Form->model[] = dontAdd(dontList(new DateTimeField(_("Creation date"), "creationdate", _("Date the subscription was registered"))));
	end($HD_Form->model)->fieldacr=_("Creat");
$HD_Form->model[] = dontList(new DateTimeFieldN(_("Activation date"), "activedate", _("Date it becomes active")));
	end($HD_Form->model)->fieldacr=_("Activ");
$HD_Form->model[] =dontList( new DateTimeFieldN(_("Expire date"), "expiredate", _("After this date it is no longer charged or used.")));
	end($HD_Form->model)->fieldacr=_("Exp.");

$HD_Form->model[] = new SecVolField(_("Seconds used"), "secondused", _("Duration of calls through DID batch."));
	end($HD_Form->model)->fieldacr=_("Used");

// $HD_Form->model[] = dontList(new SqlRefFieldN(_("CLID Rules"), "rnplan","cc_re_numplan", "id", "name"));

$HD_Form->model[] = new DelBtnField();

require("PP_page.inc.php");
?>
