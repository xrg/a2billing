<?php
require_once ("./lib/defines.php");
require_once ("./lib/module.access.php");
require_once (DIR_COMMON."Form.inc.php");
require_once (DIR_COMMON."Form/Class.SqlRefField.inc.php");
require_once (DIR_COMMON."Form/Class.VolField.inc.php");
require_once (DIR_COMMON."Form/Class.TimeField.inc.php");

require_once (DIR_COMMON."Class.HelpElem.inc.php");

$menu_section='menu_did';
HelpElem::DoHelp(_("DID batches contain the settings of ranges of DIDs."));

$HD_Form= new FormHandler('did_batch',_("DID Batches"),_("DID batch"));
$HD_Form->checkRights(ACX_DID);
$HD_Form->init();


$PAGE_ELEMS[] = &$HD_Form;
$PAGE_ELEMS[] = new AddNewButton($HD_Form);

$HD_Form->model[] = new PKeyFieldEH(_("ID"),'id');

$HD_Form->model[] = new TextFieldEH(_("Name"),'name');
$HD_Form->model[] = new TextField(_("Public Name"),'pname',_("Public name is displayed to customers, agents. You may want it to be different than the internal name above."));
	end($HD_Form->model)->fieldacr= _("P Name");

$cs_list = array();
$cs_list[]  = array("0", _("Inactive"));
$cs_list[]  = array("1", _("Active"));
//$cs_list[]  = array("2", _("..."));
$HD_Form->model[] = dontAdd(new RefField(_("Status"),'status', $cs_list));

$dmode_list = array();
$dmode_list[] = array('1',_('Public DID'));
$dmode_list[] = array('2',_('Trunk head'));
//$dmode_list[] = array('3','<Tech>/<IP>');

$HD_Form->model[] = dontList(new RefField(_("Mode"), "dmode", $dmode_list,_("Select the operation mode.")));

$HD_Form->model[] = new SqlRefFieldN(_("Provider"), "provider","cc_provider", "id", "provider_name");
$HD_Form->model[] = new IntField(_("Metric"), "metric", _("Weight of trunk in rate engine"));
	end($HD_Form->model)->does_add = false;

$HD_Form->model[] = new TextField(_("Dial Head"),'dialhead',_("The first, common digits of the DID range."));
$HD_Form->model[] = dontList(new TextField(_("Dial Add"),'dialadd',_("Add these digits.")));
$HD_Form->model[] = dontList(new TextField(_("Dial Fld2"),'dialfld2'));
$HD_Form->model[] = dontList(new IntField(_("Dial Len"),'diallen',_("Length of remaining digits in DID.")));

$HD_Form->model[] = dontAdd(dontList(new DateTimeField(_("Creation date"), "creationdate", _("Date the card was created (entered into this system)"))));
$HD_Form->model[] = dontAdd(dontList(new DateTimeFieldN(_("Expire date"), "expiredate", _("Date the card should expire"))));

$HD_Form->model[] = new SqlRefField(_("Buy plan"), "idtp","cc_tariffplan", "id", "tariffname",_("This defines the rules for rating the DID usage."));
$HD_Form->model[] = new SqlRefFieldN(_("Numbering plan"),'nplan','cc_numplan','id','name', _("Numbering plan"));
	end($HD_Form->model)->fieldacr = _("Nplan");

$HD_Form->model[] = dontList(new IntField(_("Flags"),'flags',_("Mode-specific flags.")));

$HD_Form->model[] = new SecVolField(_("Seconds used"), "secondsused", _("Duration of calls through DID batch."));
	end($HD_Form->model)->fieldacr=_("Used");

// $HD_Form->model[] = dontList(new SqlRefFieldN(_("CLID Rules"), "rnplan","cc_re_numplan", "id", "name"));

$HD_Form->model[] = new DelBtnField();

require("PP_page.inc.php");

?>
