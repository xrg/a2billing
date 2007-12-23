<?php
require_once ("./lib/defines.php");
require_once ("./lib/module.access.php");
require_once (DIR_COMMON."Form.inc.php");
require_once (DIR_COMMON."Class.HelpElem.inc.php");
require_once (DIR_COMMON."Form/Class.RevRef.inc.php");
require_once (DIR_COMMON."Form/Class.TimeField.inc.php");
require_once (DIR_COMMON."Form/Class.SqlRefField.inc.php");
require_once (DIR_COMMON."Form/Class.RevRefForm.inc.php");

$menu_section='menu_creport';


// HelpElem::DoHelp(gettext("*-*"),'vcard.png');

$HD_Form= new FormHandler('cc_call_v',_("Calls"),_("Call"));
$HD_Form->checkRights(ACX_CALL_REPORT);
$HD_Form->init();

$PAGE_ELEMS[] = &$HD_Form;

$HD_Form->model[] = DontList( new PKeyFieldTxt(_("Session ID"),'sessionid'));
$HD_Form->model[] = DontList( new PKeyFieldTxt(_("Unique ID"),'uniqueid'));
$HD_Form->model[] = DontList(new PKeyField("",'cardid'));
// nasipaddress  | text                        |
// qval          | double precision            |

$HD_Form->model[] = new DateTimeFieldDH(_("Start Time"),'starttime');
$HD_Form->model[] = DontList(new DateTimeField(_("Stop Time"),'stoptime'));
$HD_Form->model[] = new SqlRefFieldN(_("Card"),'cardid','cc_card','id','username');
$HD_Form->model[] = DontList(new SqlRefFieldN(_("Server"),'srvid','cc_a2b_server','id','host'));

$HD_Form->model[] = new TextField(_("Called station"),'calledstation');
$HD_Form->model[] = new TextField(_("Destination"),'destination');
$HD_Form->model[] = new IntField(_("Attempt"),'attempt');
end($HD_Form->model)->fieldacr=_("Atm");
$HD_Form->model[] = new IntField(_("Duration"),'sessiontime');
end($HD_Form->model)->fieldacr=_("Dur");
$HD_Form->model[] = DontList(new IntField(_("Start Delay"),'startdelay'));
$HD_Form->model[] = DontList(new IntField(_("Stop Delay"),'stopdelay'));

$HD_Form->model[] = DontList(new SqlRefFieldN(_("Sell Rate"),'srid','cc_sellrate','id','destination', _("Selling rate")));
$HD_Form->model[] = DontList(new SqlRefFieldN(_("Buy Rate"),'brid','cc_buyrate','id','destination', _("Buying rate")));

$HD_Form->model[] = new TextField(_("Result"),'tcause');
$HD_Form->model[] = DontList(new IntField(_("ISDN Hangup Cause"),'hupcause'));
$HD_Form->model[] = DontList(new TextField(_("Cause ext."),'cause_ext'));

$HD_Form->model[] = DontList(new SqlRefFieldN(_("Trunk"),'trunk','cc_trunk','id','trunkcode',
		 _("Trunk used for the call")));

$HD_Form->model[] = new FloatField(_("Bill"),'sessionbill',_("How much the customer was charged for the call."));
$HD_Form->model[] = DontList(new FloatField(_("Cost"),'buycost',_("How much we were charged for the call.")));

$HD_Form->model[] = DontList(new TextField(_("Source"),'src'));
$HD_Form->model[] = DontList(new SqlRefFieldN(_("Tariff group"),'tgid','cc_tariffgroup','id','name', _("Tariff group used by the rate engine.")));


$tmp = new RevRefForm(_("Details"),'dt','sessionid','cc_call','sessionid');
$HD_Form->meta_elems[] = $tmp;
	$tmp->at_action = 'details';
	$tmp->Form->checkRights(ACX_CALL_REPORT);
	$tmp->Form->init();
	$tmp->Form->model[] = new ClauseField('uniqueid',null,'uniqueid');
	$tmp->Form->model[] = new ClauseField('cardid',null,'cardid');
	
	$tmp->Form->model[] = new IntField(_("Attempt"),'attempt');
	end($tmp->Form->model)->fieldacr=_("At");
	$tmp->Form->model[] = new PKeyFieldTxt(_("ID"),'id');

	$tmp->Form->model[] = new DateTimeFieldDH(_("Start Time"),'starttime');
	$tmp->Form->model[] = new DateTimeField(_("Stop Time"),'stoptime');
	$tmp->Form->model[] = DontList(new SqlRefFieldN(_("Server"),'srvid','cc_a2b_server','id','host'));

	// $tmp->Form->model[] = new TextField(_("Called station"),'calledstation');
	$tmp->Form->model[] = DontList(new TextField(_("Destination"),'destination'));
	$tmp->Form->model[] = new IntField(_("Duration"),'sessiontime');
	end($tmp->Form->model)->fieldacr=_("Dur");
	$tmp->Form->model[] = new IntField(_("Start Delay"),'startdelay');
	end($tmp->Form->model)->fieldacr=_("SD");
	$tmp->Form->model[] = DontList(new IntField(_("Stop Delay"),'stopdelay'));
	end($tmp->Form->model)->fieldacr=_("TD");
	
	$tmp->Form->model[] = new SqlRefFieldN(_("Sell Rate"),'srid','cc_sellrate','id','destination', _("Selling rate"));
	end($tmp->Form->model)->fieldacr=_("SRate");
	$tmp->Form->model[] = new SqlRefFieldN(_("Buy Rate"),'brid','cc_buyrate','id','destination', _("Buying rate"));
	end($tmp->Form->model)->fieldacr=_("BRate");
	
	$tmp->Form->model[] = new TextField(_("Result"),'tcause');
	$tmp->Form->model[] = new IntField(_("ISDN Hangup Cause"),'hupcause');
	end($tmp->Form->model)->fieldacr=_("Hu");
	$tmp->Form->model[] = DontList(new TextField(_("Cause ext."),'cause_ext'));
	end($tmp->Form->model)->fieldacr=_("Ce");
	
	$tmp->Form->model[] = DontList(new SqlRefFieldN(_("Trunk"),'trunk','cc_trunk','id','trunkcode',
			_("Trunk used for the call")));
	
	$tmp->Form->model[] = new FloatField(_("Bill"),'sessionbill',_("How much the customer was charged for the call."));
	$tmp->Form->model[] = DontList(new FloatField(_("Cost"),'buycost',_("How much we were charged for the call.")));
	
	$tmp->Form->model[] = DontList(new TextField(_("Source"),'src'));

require("PP_page.inc.php");

?>
