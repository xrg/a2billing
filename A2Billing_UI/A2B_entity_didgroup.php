<?php
require_once ("./lib/defines.php");
require_once ("./lib/module.access.php");
require_once (DIR_COMMON."Form.inc.php");
require_once (DIR_COMMON."Class.HelpElem.inc.php");
require_once (DIR_COMMON."Form/Class.RevRef2.inc.php");
require_once (DIR_COMMON."Form/Class.SqlRefField.inc.php");

$menu_section='menu_did';

HelpElem::DoHelp(_("DID groups allow a group of customers to use incoming calls."));

$HD_Form= new FormHandler('cc_didgroup',_("DID Groups"),_("DID group"));
$HD_Form->checkRights(ACX_DID);
$HD_Form->init();


$PAGE_ELEMS[] = &$HD_Form;
$PAGE_ELEMS[] = new AddNewButton($HD_Form);

$HD_Form->model[] = new PKeyFieldEH(_("ID"),'id');

$HD_Form->model[] = new TextFieldEH(_("Name"),'name');
$HD_Form->model[] = new TextField(_("Code"),'code',_("Code matched against the source (trunk) that delivers the DID."));

$HD_Form->model[] = new RevRef2(_("Batches"),'bts','id','did_group_batch','btid','dbid','did_batch','id','name',_("These DID batches will be available and/or matched against the incoming DIDs."));

$HD_Form->model[] = new SqlRefField(_("Sell Tariff group"),'tgid','cc_tariffgroup','id','name',_("Tariff group which will define final retail prices and route availability."));
	end($HD_Form->model)->fieldacr = _("Sell");

$HD_Form->model[] = dontList(new SqlRefFieldN(_("CLID Rules"), "rnplan","cc_re_numplan", "id", "name",_("Reverse translating rules for incoming CLID")));
$HD_Form->model[] = DontList(new TextFieldN(_("Ring style"),'alert_info',_("Selects the ring pattern to use on the phone being dialled.")));

$HD_Form->model[] = new DelBtnField();

require("PP_page.inc.php");
?>
