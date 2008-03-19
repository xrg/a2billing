<?php
require_once ("./lib/defines.php");
require_once ("./lib/module.access.php");
require_once (DIR_COMMON."Form.inc.php");
require_once (DIR_COMMON."Form/Class.SqlActionForm.inc.php");
require_once (DIR_COMMON."Class.HelpElem.inc.php");
require_once (DIR_COMMON."Form/Class.SqlRefField.inc.php");

$menu_section='menu_billing';


// HelpElem::DoHelp(gettext("*-*"),'vcard.png');

$HD_Form= new SqlActionForm();
$HD_Form->checkRights(ACX_BILLING);
$HD_Form->init();

$PAGE_ELEMS[] = &$HD_Form;

$HD_Form->model[] = new SqlRefField(_("Card Group"), "grp","cc_card_group", "id", "name",_("Card group used for the generated vouchers. Will also define the currency, if exists."));

$ser_list = array();
$ser_list[]  = array("t", _("Consecutive numbers"));
$ser_list[]  = array("f", _("Random numbers"));

$activate_list = array();
$activate_list[]  = array("t", _("Voucher activated"));
$activate_list[]  = array("f", _("Voucher disabled"));

$HD_Form->model[] = new IntField(_("Count of vouchers"),'num');
end($HD_Form->model)->def_value=10;

$HD_Form->model[] = new RefField(_("Numbering"),'ser', $ser_list);
$HD_Form->model[] = new TextField(_("Start Number"),'startn');
end($HD_Form->model)->def_value=0;

$HD_Form->model[] = new IntField(_("Voucher Length"),'vlen');
end($HD_Form->model)->def_value=10;

$HD_Form->model[] = new TextField(_("Tag"),'vtag');

$HD_Form->model[] = new FloatField(_("Credit"),'vcredit',_("Money in the voucher. Positive is credit. It is in group's currency!"));
end($HD_Form->model)->def_value=0;

$HD_Form->model[] = new RefField(_("Activate"),'vactivate', $activate_list,_("Enable or disable the voucher"));

// gen_vouchers(s_crdgrp int4, s_serial bool, s_num int4, s_start text, s_voucherlen int4, s_tag text, s_credit "numeric", s_activated bool)
$HD_Form->QueryString= 'SELECT gen_vouchers(%#grp, %ser, %#num, %startn, %#vlen, %vtag, %vcredit, %vactivate) AS nvouchers;';

$HD_Form->expectRows = true;
$HD_Form->submitString = _("Generate Vouchers!");
$HD_Form->successString =  '';
$HD_Form->rowString = _("Generated %#nvouchers cards!<br>");

require("PP_page.inc.php");
?>
