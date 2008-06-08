<?php
require_once ("./lib/defines.php");
require_once ("./lib/module.access.php");
require_once (DIR_COMMON."Form.inc.php");
require_once (DIR_COMMON."Class.HelpElem.inc.php");
require_once (DIR_COMMON."Form/Class.SqlRefField.inc.php");

$menu_section='menu_config';


HelpElem::DoHelp(gettext("Asterisk User Configurations. <br>Asterisk users/peers are grouped into these configuration groups."));

$HD_Form= new FormHandler('cc_ast_users_config',_("Configurations"),_("Configuration"));
$HD_Form->checkRights(ACX_ADMINISTRATOR);
$HD_Form->init();

$arr_type = array ( array ('friend', "Friend"), array ('peer','Peer'), array('user', 'User'));
$arr_yesno = array ( array ('yes', _("Yes")), array ('no',_('No')));
$arr_yesnoN = array (array(null,_('Default')), array ('yes', _("Yes")), array ('no',_('No')) );
$arr_dtmf = array ( array ('auto', "Auto"), array ('info','SIP info'), 
	array('rfc2833', 'rfc2833'),array('inband','Inband'));
$arr_ynn = array (array(null,_('Default')), array ('yes', _("Yes")), array ('no',_('No')), array ('never',_('Never')));

$arr_nat = array (array(null,_('Default')), array ('yes', _("Yes")), array ('no',_('No')),
	array ('never',_('Never')), array('route','Route'));

$arr_canreinvite = array (array(null,_('Default')), array ('yes', _("Yes")), array ('no',_('No')),
	array ('nonat','No NAT'), array('update','Update'));

$arr_ixfer = array (array(null,_('Default')), array ('yes', _("Yes")), array('mediaonly',_('Media only')), array ('no',_('No')));
$arr_iauth = array (array ('md5', _("MD5")), array ('plaintext',_('Plain text')), array ('rsa',_('RSA keys')));

$PAGE_ELEMS[] = &$HD_Form;
$PAGE_ELEMS[] = new AddNewButton($HD_Form);

$HD_Form->model[] = new PKeyFieldEH(_("ID"),'id');

$HD_Form->model[] = new TextFieldEH(_("Name"),'cfg_name',_("Human readable name"));
$HD_Form->model[] = new RefField(_("Type"), "type", $arr_type,_("Peer type"));
$HD_Form->model[] = new TextField(_("Context"),'context');
$HD_Form->model[] = new RefField(_("Trunking"), "trunk", $arr_yesno,_("Trunking (channel bonding) for IAX2"));
$HD_Form->model[] = new RefField(_("Video"), "videosupport", $arr_yesnoN,_("Allow Video calls"));

$HD_Form->model[] = new TextFieldN(_("Codecs allow"),'allow',_("Allow audio codecs, comma separated w/o space"));
$HD_Form->model[] = new TextFieldN(_("Codecs disallow"),'disallow');

$HD_Form->model[] = new TextFieldN(_("From Domain"),'fromdomain',_("Force 'from domain' at header"));
$HD_Form->model[] = new TextField(_("AMA flags"),'amaflags');
$HD_Form->model[] = new RefField(_("DTMF mode"), "dtmfmode", $arr_dtmf,_("DTMF mode"));
$HD_Form->model[] = new RefField(_("Progress Inband"), "progressinband", $arr_ynn,_("Send inband audio tone when ringing"));

$HD_Form->model[] = new IntFieldN(_("Incoming limit"),'incominglimit',_("Incoming calls limit"));
$HD_Form->model[] = new IntFieldN(_("Outgoing limit"),'outgoinglimit',_("Outgoing calls limit"));
$HD_Form->model[] = new RefField(_("NAT"), "nat", $arr_nat,_("NAT support"));

$HD_Form->model[] = new RefField(_("IAX Auth"), "iax_auth", $arr_iauth,_("Authentication method over IAX2. 'rsa' needs inkeys/outkey"));
$HD_Form->model[] = new RefField(_("IAX Transfer"), "iax_xfer", $arr_ixfer,_("Allow native IAX2 transfers. Choosing 'yes' will circumvent billing!"));
$HD_Form->model[] = new RefField(_("Jitter buffer"),'jitterbuffer',$arr_yesnoN,_("IAX2 jitter buffer"));

$HD_Form->model[] = new RefField(_("Canreinvite"), "canreinvite", $arr_canreinvite,_("Attempt to bridge the media path"));
$HD_Form->model[] = new TextFieldN(_("Insecure"),'insecure');

$HD_Form->model[] = new IntFieldN(_("RTP timeout"),'rtptimeout',_("Hang up call if x seconds of RTP inactivity"));
$HD_Form->model[] = new IntFieldN(_("RTP keepalive"),'rtpkeepalive',_("Send keepalive over RTP every x secs"));
$HD_Form->model[] = new IntFieldN(_("RTP Hold timeout"),'rtpholdtimeout',_("Terminate call on hold, after x seconds of RTP inactivity"));

$HD_Form->model[] = new TextFieldN(_("Qualify"),'qualify',_("Poke peer every x msec"));

$HD_Form->model[] = new IntFieldN(_("Port"),'defport',_("Default port"));
$HD_Form->model[] = new TextFieldN(_("Permit"),'permit',_("IP/netmask of hosts to permit"));
$HD_Form->model[] = new TextFieldN(_("Deny"),'deny',_("IP/netmask of hosts to deny"));

$HD_Form->model[] = new TextFieldN(_("Call group"),'callgroup',_("When this device is called, set the call group so that others can pick it up."));
$HD_Form->model[] = new TextFieldN(_("Pickup group"),'pickupgroup',_("Allow this device to pick up calls made to those groups."));

$HD_Form->model[] = new RefField(_("Call FWD"), "cancallforward", $arr_yesnoN,_("Allow user to forward calls"));
$HD_Form->model[] = new TextFieldN(_("MOH"),'musiconhold',_("Music on hold class"));
$HD_Form->model[] = new TextFieldN(_("Set var"),'setvar',_("Set some custom var"));

for ($i=7;$i<count($HD_Form->model);$i++){
	$HD_Form->model[$i]->does_add = false;
	$HD_Form->model[$i]->does_list = false;
}

$HD_Form->model[] = new DelBtnField();


require("PP_page.inc.php");

?>
