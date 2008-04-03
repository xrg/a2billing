<?php
require_once ("./lib/defines.php");
require_once ("./lib/module.access.php");
require_once (DIR_COMMON."Form.inc.php");
require_once (DIR_COMMON."Class.HelpElem.inc.php");
require_once (DIR_COMMON."Form/Class.SqlRefField.inc.php");
require_once (DIR_COMMON."Form/Class.TabField.inc.php");
require_once (DIR_COMMON."Class.JQuery.inc.php");

$menu_section='menu_queues';

HelpElem::DoHelp(_("Queues are the central element of call-center setups. When a call arrives, it is placed in the queue and some <i>member</i> of our staff will try to service the call."));

$HD_Form= new FormHandler('ast_queue',_("Queues"),_("Queue"));
$HD_Form->checkRights(ACX_QUEUES);
$HD_Form->init();

// $arr_type = array ( array ('friend', "Friend"), array ('peer','Peer'), array('user', 'User'));
$arr_yesno = array ( array ('yes', _("Yes")), array ('no',_('No')));
$arr_yesnoN = array (array(null,_('Default')), array ('yes', _("Yes")), array ('no',_('No')) );
$arr_tfN = array (array(null,_('Default')), array ('t', _("Yes")), array ('f',_('No')) );
// $arr_canreinvite = array (array(null,_('Default')), array ('yes', _("Yes")), array ('no',_('No')),
// 	array ('nonat','No NAT'), array('update','Update'));
// 
// $arr_ixfer = array (array(null,_('Default')), array ('yes', _("Yes")), array('mediaonly',_('Media only')), array ('no',_('No')));
// $arr_iauth = array (array ('md5', _("MD5")), array ('plaintext',_('Plain text')), array ('rsa',_('RSA keys')));

$PAGE_ELEMS[] = &$HD_Form;
$PAGE_ELEMS[] = new AddNewButton($HD_Form);

$HD_Form->model[] = new TabField(_("General"));
//                     ----------------

$HD_Form->model[] = new PKeyFieldEH(_("ID"),'id');
$HD_Form->model[] = new TextFieldEH(_("Name"),'name',_("Internal (asterisk) name, also used in the Queue() application."));
$HD_Form->model[] = new TextFieldN(_("Music class"),'musiconhold',_("Sets which music applies for this particular call queue."));
$HD_Form->model[] = new TextFieldN(_("Context"), "context",_("If specified and the user types a SINGLE digit extension 
while they are in the queue, they will be taken out
of the queue and sent to that extension in this context."));

$arr_strategy = array ( array ('ringall', _("Ring all available channels")), 
	array ('roundrobin',_('Ring each interface in turn')),
	array('leastrecent', _('Ring the least recently called')),
	array ('fewestcalls',_('Ring the one with fewest completed calls')),
	array ('random',_('Ring random interface')),
	array ('rrmemory',_('Round robin with memory'))
	);
$HD_Form->model[] = new RefField(_("Strategy"), "strategy", $arr_strategy,_("Which member(s) will ring when a call comes in."));
$HD_Form->model[] = new IntFieldN(_("Max Length"), "maxlen", _("Maximum number of people waiting in the queue (0 for unlimited)"));

$HD_Form->model[] = new TabField(_("Options"));
//                     ----------------
$HD_Form->model[] = new IntFieldN(_("Weight"), "weight", _("Weight of queue - when compared to other queues, higher weights get
first shot at available channels when the same channel is included in 
more than one queue."));

$HD_Form->model[] = new RefFieldN(_("Autofill"), "autofill", $arr_yesnoN,_("Autofill will follow queue strategy but push multiple calls through
at same time until there are no more waiting callers or no more
available members."));
$HD_Form->model[] = new RefFieldN(_("Auto Pause"), "autopause", $arr_yesnoN,_("Autopause will pause a queue member if they fail to answer a call"));
$HD_Form->model[] = new RefFieldN(_("Set Intf. Var"), "setinterfacevar", $arr_tfN,_("Set the MEMBERINTERFACE variable with the interface name (eg. Agent/1234)"));

$HD_Form->model[] = new TabField(_("Timeouts"));
//                     ----------------
$HD_Form->model[] = new IntFieldN(_("Timeout"), "timeout", _("How long do we let the phone ring before we consider this a timeout."));
$HD_Form->model[] = new IntFieldN(_("Retry"), "retry", _("How long do we wait before trying all the members again?"));
$HD_Form->model[] = new IntFieldN(_("Service Level"), "servicelevel", _("For statistics: we want to answer calls within so many seconds."));

$HD_Form->model[] = new RefFieldN(_("Avoid in use"), "ringinuse", $arr_tfN,_("If set to no, agents reported in use will not ring (SIP-wise)."));
$HD_Form->model[] = new IntFieldN(_("Member Delay"), "memberdelay",_("Seconds to delay before the member is connected to the caller."));
$HD_Form->model[] = new RefFieldN(_("Timeout restart"), "timeoutrestart",$arr_yesnoN, _("Reset timeout for an agent to answer if a BUSY or CONGESTION is received."));
$HD_Form->model[] = new IntFieldN(_("Wrap Up Time"), "wrapuptime", _("After a successful call, how long to wait before sending a potentially free member another call."));

$HD_Form->model[] = new TabField(_("Announce"));
//                     ----------------
$HD_Form->model[] = new IntFieldN(_("Announce freq"), "announce_frequency", _("How often to announce queue position and/or estimated holdtime to caller"));
$HD_Form->model[] = new IntFieldN(_("Periodic Announce fr."), "periodic_announce_frequency", _("How often to make any periodic announcement."));

$arr_yno = array (array(null,_('Default')), array ('yes', _("Yes")), array ('no',_('No')), array ('once',_('Once')));
$HD_Form->model[] = new RefFieldN(_("Announce holdtime"), "announce_holdtime", $arr_yno,_("Should we include estimated hold time in position announcements?"));
$HD_Form->model[] = new IntFieldN(_("Announce rounding"), "announce_round_seconds", _("; If this is non-zero, then we announce the seconds as well as the minutes rounded to this value. "));
$HD_Form->model[] = new TextFieldN(_("Announce"),'announce',_("An announcement may be specified which is played for the <b>member</b> assoon as they answer a call."));
$HD_Form->model[] = new RefFieldN(_("Hold time"), "reportholdtime", $arr_tfN,_("Report the caller's hold time to the member before they are
connected to the caller"));


$HD_Form->model[] = new TabField(_("Sound files"));
//                     ----------------
$HD_Form->model[] = new TextFieldN(_("You are next"), "queue_youarenext",_("You are now first in line."));
$HD_Form->model[] = new TextFieldN(_("There are"), "queue_thereare");
$HD_Form->model[] = new TextFieldN(_("Calls waiting"), "queue_callswaiting"); 
$HD_Form->model[] = new TextFieldN(_("Est. Holdtime"), "queue_holdtime",_("The current est. holdtime is"));
$HD_Form->model[] = new TextFieldN(_("Minutes"), "queue_minutes");
$HD_Form->model[] = new TextFieldN(_("Seconds"), "queue_seconds");
$HD_Form->model[] = new TextFieldN(_("Thank you"), "queue_thankyou",_("Thank you for your patience."));
$HD_Form->model[] = new TextFieldN(_("Less than"), "queue_lessthan");
$HD_Form->model[] = new TextFieldN(_("Hold time"), "queue_reporthold");
$HD_Form->model[] = new TextFieldN(_("Periodic"), "periodic_announce",_("All reps busy / wait for next"));


$HD_Form->model[] = new TabField(_("Monitoring"));
//                     ----------------
$arr_monfmt = array (array(null,_('None')), array ('gsm', _("GSM format")), array ('wav',_('WAV format')),
	array ('wav49',_('WAV compressed')));
$HD_Form->model[] = new RefFieldN(_("Format"), "monitor_format", $arr_monfmt,_("Record the calls. Please respect the law when using this."));

// $arr_montyp = array (array(null,_('Default')), array ('MixMonitor',"MixMonitor"), array ('Monitor',_('Monitor (not recommended)')));
// $HD_Form->model[] = new RefField(_("Mon. Type"), "monitor_type", $arr_monfmt,_("Record the calls. Please respect the law when using this."));


$HD_Form->model[] = new TabField(_("Empty queues"));
//                     ----------------
$arr_joinempty = array (array(null,_('Default')), array ('yes',_("Join a queue with no members")),
	 array ('no',_("No when no members")),
	 array ('strict',_("Strict: don't join when no members or no available")));

$HD_Form->model[] = new RefFieldN(_("Join Empty"), "joinempty", $arr_joinempty,_("Allow/reject new callers if no members can answer their calls."));
$arr_leaveempty = array (array(null,_('Default')), array ('yes',_("Leave the queue if empty")),
	 array ('no',_("Wait in an empty queue")),
	 array ('strict',_("Strict: don't join when no members or no available")));
$HD_Form->model[] = new RefFieldN(_("Leave Empty"), "leavewhenempty", $arr_leaveempty,_("Remove existing callers if no queue members."));

$HD_Form->model[] = new TabField(_("Manager Events"));

$arr_ewc = array (array(null,_('Default')), array ('yes', _("Yes")), array ('no',_('No')),
	array ('vars','Vars'));
$HD_Form->model[] = new RefFieldN(_("When called"), "eventwhencalled", $arr_ewc,_("Generate manager events when called."));
$HD_Form->model[] = new RefFieldN(_("Member status"), "eventmemberstatus", $arr_tfN,_("Report member status (generates MANY events)"));



for ($i=6;$i<count($HD_Form->model);$i++){
	$HD_Form->model[$i]->does_add = false;
	$HD_Form->model[$i]->does_list = false;
}

$HD_Form->model[] = new DelBtnField();


require("PP_page.inc.php");

?>
