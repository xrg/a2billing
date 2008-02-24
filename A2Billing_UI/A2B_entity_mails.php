<?php
require_once ("./lib/defines.php");
require_once ("./lib/module.access.php");
require_once (DIR_COMMON."Form.inc.php");
require_once (DIR_COMMON."Class.HelpElem.inc.php");
require_once (DIR_COMMON."Form/Class.RefField.inc.php");
require_once (DIR_COMMON."Form/Class.SqlRefField.inc.php");

$menu_section='menu_admin';
HelpElem::DoHelp(_("Mails sent or queued."));

$HD_Form= new FormHandler('cc_mailings',_("Mails"),_("Mail"));
$HD_Form->checkRights(ACX_MISC);
$HD_Form->init();

$PAGE_ELEMS[] = &$HD_Form;
$PAGE_ELEMS[] = new AddNewButton($HD_Form);

$HD_Form->model[] = new PKeyFieldEH(_("ID"),'id');
$HD_Form->model[] = new TextFieldEH(_("To"),'tomail',_("Name and mail of recepient"));
$HD_Form->model[] = new SqlRefField(_("Type"),'tmail_id','cc_templatemail','id','mtype');
	end($HD_Form->model)->combofield = "mtype || '/' ||lang";

$mstates = array();
$mstates[] = array(1,_("New, unsent"));
$mstates[] = array(2,_("New, halt"));
$mstates[] = array(3,_("Sent"));
$mstates[] = array(4,_("Failed"));
$mstates[] = array(5,_("Waiting resend"));

$HD_Form->model[] = new RefField(_("State"),'state', $mstates);

$HD_Form->model[] = dontList(new TextAreaField(_("Args"),'args'));
$HD_Form->model[] = dontList(new TextAreaField(_("Comments"),'icomments',_("Machine comments, for internal use.")));

$HD_Form->model[] = new DelBtnField();

require("PP_page.inc.php");
?>
