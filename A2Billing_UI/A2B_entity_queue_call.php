<?php
require_once ("./lib/defines.php");
require_once ("./lib/module.access.php");
require_once ("a2blib/Form.inc.php");
require_once ("a2blib/Class.HelpElem.inc.php");
require_once ("a2blib/Form/Class.SqlRefField.inc.php");
// require_once ("a2blib/Form/Class.TabField.inc.php");
require_once ("a2blib/Form/Class.TimeField.inc.php");

// require_once ("a2blib/Class.JQuery.inc.php");

$menu_section='menu_queues';

HelpElem::DoHelp(_("Lists the calls that have been processed by the queue module, and their result."));

$HD_Form= new FormHandler('ast_queue_callers',_("Calls"),_("Call"));
$HD_Form->checkRights(ACX_QUEUES);
$HD_Form->default_order='id';
$HD_Form->default_sens='DESC';
$HD_Form->init();

$PAGE_ELEMS[] = &$HD_Form;
// $PAGE_ELEMS[] = new AddNewButton($HD_Form);

$HD_Form->model[] = new PKeyFieldEH(_("ID"),'id');
$HD_Form->model[] = new SqlRefFieldN(_("Queue"),'queue','ast_queue','id','name');
$HD_Form->model[] = new DateTimeFieldDH(_("Join"), "ts_join", _("Time the call reached our system"));
$HD_Form->model[] = dontList(new DateTimeFieldN(_("Connect"), "ts_connect", _("When the call started being serviced by an agent.")));
$HD_Form->model[] = dontList(new DateTimeFieldN(_("End"), "ts_end", _("Time the call ended")));
$HD_Form->model[] = new TextFieldN(_("Status"),'status',_("Outcome of the call."));
$HD_Form->model[] = dontList(new TextFieldN(_("Hangup Cause"),'hupcause',_("Hangup cause, as reported by asterisk.")));
$HD_Form->model[] = new TextFieldN(_("CLID"),'callerid',_("Caller ID reported to asterisk."));
$HD_Form->model[] = dontList(new TextFieldN(_("UID"),'uniqueid',_("Asterisk unique call id.")));
$HD_Form->model[] = new TextFieldN(_("Agent"),'agent',_("Agent which serves the call, if any."));

$HD_Form->model[] = dontList(new SecondsField(_("Hold"),'holdtime',_("How much did the caller wait.")));
$HD_Form->model[] = dontList(new SecondsField(_("Talk"),'talktime',_("How long did the caller talk to our agent.")));
$HD_Form->model[] = dontList(new TextFieldN(_("Br Channel"),'brchannel',_("Technical name of bridged channel.")));

// $HD_Form->model[] = new DelBtnField();


require("PP_page.inc.php");
?>
