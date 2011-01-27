<?php
require_once ("./lib/defines.php");
require_once ("./lib/module.access.php");
require_once ("a2blib/Form.inc.php");
require_once ("a2blib/Class.HelpElem.inc.php");
require_once ("a2blib/Form/Class.SqlRefField.inc.php");
require_once ("a2blib/Form/Class.VolField.inc.php");

$menu_section='menu_servers';


HelpElem::DoHelp(gettext("Trunks are used to terminate the call!<br>" .
			"The trunk and ratecard is selected by the rating engine on the basis of the dialed digits.<br>" .
			"The trunk is used to dial out from your asterisk box which can be a zaptel interface or a voip provider."),
			'hwbrowser.png');

$HD_Form= new FormHandler('cc_trunk',_("Trunks"),_("Trunk"));
$HD_Form->checkRights(ACX_TRUNK);
$HD_Form->init();

$PAGE_ELEMS[] = &$HD_Form;
$PAGE_ELEMS[] = new AddNewButton($HD_Form);

$status_list = array();
$status_list[] = array('1',gettext("Active"));
$status_list[] = array('0',gettext("Inactive"));

$trunkfmt_list = array();
$trunkfmt_list[] = array('1','<Tech>/<IP>/<Number>');
$trunkfmt_list[] = array('2','<Tech>/<Number>@<IP>');
$trunkfmt_list[] = array('3','<Tech>/<IP>');
$trunkfmt_list[] = array('4','Local Peer: <[Tech]>/<@[IP:numplan]>');
$trunkfmt_list[] = array('5','Direct Peer: <[Tech]>/<@[IP:numplan]>');
$trunkfmt_list[] = array('6','Remote Peer: <[Tech]>/<@[IP:numplan]>');
$trunkfmt_list[] = array('7','Local Peer, cross Numplan');
$trunkfmt_list[] = array('8','Remote Peer, cross Numplan');
$trunkfmt_list[] = array('9','Group destination');

$trunkfmt_list[] = array('10','Email to local peer');
$trunkfmt_list[] = array('11','Email to cross numplan peer');

$trunkfmt_list[] = array('12','Voicemail to local peer');
$trunkfmt_list[] = array('13','Voicemail to cross numplan peer');
$trunkfmt_list[] = array('14','Voicemail Main');
$trunkfmt_list[] = array('15','Local Peer, auto answer');


$HD_Form->model[] = new PKeyFieldEH(_("ID"),'id');
$HD_Form->model[] = new TextFieldEH(_("Label"),'trunkcode',_("Human readable name for the agent"));
$HD_Form->model[] = new SqlRefFieldN(_("Provider"), "provider","cc_provider", "id", "provider_name");
$HD_Form->model[] = new IntField(_("Metric"), "metric", _("Weight of trunk in rate engine"));
end($HD_Form->model)->does_add = false;

$HD_Form->model[] = dontList(new TextField(_("Prefix"),'trunkprefix',_("Add a prefix to the dialled digits.")));
$HD_Form->model[] = dontList(new IntField(_("Strip Digits"), "stripdigits", _("Number of digits to strip from dialstring")));
end($HD_Form->model)->does_add = false;

// $HD_Form->model[] = new TextField(_("Remove Prefix"),'removeprefix',_("In case of the voip provider or the gateway doesnt want a dialed prefix (can be useful with local gateway)"));

$HD_Form->model[] = dontList(new RefField(_("Format"), "trunkfmt", $trunkfmt_list,_("Select the desired format for the Dial string")));
$HD_Form->model[] = new TextField(_("Tech"),'providertech',_("Technology used on the trunk (SIP,IAX2,ZAP,H323)"));
$HD_Form->model[] = new TextField(_("Provider IP"), "providerip", _("Set the IP or URL of the VoIP provider. Alternatively, put in the name of a previously defined trunk in Asterisk. (MyVoiPTrunk, ZAP4G etc.) You can use the following tags to as variables: *-* %dialingnumber%, %cardnumber%. ie g2/1644787890wwwwwwwwww%dialingnumber%"));
// end($HD_Form->model)->fieldacr =  gettext("ACT");

$HD_Form->model[] = dontList(new TextField(_("Additional parameter"), "addparameter", _("Define any additional parameters that will be used when running the Dial Command in Asterisk. Use the following tags as variables  *-* %dialingnumber%, %cardnumber%. ie 'D(ww%cardnumber%wwwwwwwwww%dialingnumber%)'")));
$HD_Form->model[] = dontList(new TextFieldN(_("Feature"),'feature',_("If set, only cards subscribed to this feature will be able to use the trunk.")));

$HD_Form->model[] = dontList(new SqlRefFieldN(_("CLID Rules"), "rnplan","cc_re_numplan", "id", "name"));

$HD_Form->model[] = new IntVolField(_("In use"), "inuse", _("Number of calls currently through this trunk"));
end($HD_Form->model)->does_add = false;
// $HD_Form->model[] = new TextField(_("Additional parameter"), "addparam", _());

$HD_Form->model[] = new RefField(_("Status"), "status", $status_list,_("Allow the agent to operate"),"4%");
end($HD_Form->model)->does_add = false;

$HD_Form->model[] = new SecVolField(_("Seconds used"), "secondusedreal", _("Duration of calls through trunk."));
	end($HD_Form->model)->fieldacr=_("Used");

$HD_Form->model[] = new DelBtnField();


require("PP_page.inc.php");

?>
