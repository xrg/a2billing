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

HelpElem::DoHelp(_("Logs are the technical entries, directly from asterisk, of queue events."));

$HD_Form= new FormHandler('ast_queue_log',_("Logs"),_("Log"));
$HD_Form->checkRights(ACX_QUEUES);
$HD_Form->default_order='tstamp';
$HD_Form->default_sens='DESC';
$HD_Form->init();

$PAGE_ELEMS[] = &$HD_Form;
// $PAGE_ELEMS[] = new AddNewButton($HD_Form);

$HD_Form->model[] = new PKeyFieldEH(_("ID"),'id');
$HD_Form->model[] = new DateTimeFieldDH(_("Date"), "tstamp", _("Event date"));
$HD_Form->model[] = new TextFieldN(_("Call"),'callid',_("Asterisk call identifier."));
$HD_Form->model[] = new TextFieldN(_("Queue"),'queuename',_("Queue name."));
$HD_Form->model[] = new TextFieldN(_("Agent"),'agent',_("Agent which served the call, if any."));
$HD_Form->model[] = new TextFieldN(_("Event"),'event',_("Asterisk event code."));
$HD_Form->model[] = dontList(new TextFieldN(_("P 1"),'parm1',_("Event parameter 1.")));
$HD_Form->model[] = dontList(new TextFieldN(_("P 2"),'parm2',_("Event parameter 2.")));
$HD_Form->model[] = dontList(new TextFieldN(_("P 3"),'parm3',_("Event parameter 3.")));

// $HD_Form->model[] = new DelBtnField();


require("PP_page.inc.php");
?>
