<?php
require_once ("./lib/defines.php");
require_once ("./lib/module.access.php");
require_once (DIR_COMMON."Form.inc.php");
require_once (DIR_COMMON."Class.HelpElem.inc.php");
require_once (DIR_COMMON."Form/Class.SqlRefField.inc.php");

$menu_section='menu_config';
HelpElem::DoHelp(_("Mail templates are preset messages to be sent on alerts etc."));

$HD_Form= new FormHandler('cc_templatemail',_("Templates"),_("Template"));
$HD_Form->checkRights(ACX_MISC);
$HD_Form->init();

$PAGE_ELEMS[] = &$HD_Form;
$PAGE_ELEMS[] = new AddNewButton($HD_Form);

$HD_Form->model[] = new PKeyFieldEH(_("ID"),'id');
$HD_Form->model[] = new TextFieldEH(_("Type"),'mtype');
$HD_Form->model[] = new RefField(_("Lang"),'lang', get_locales(true));
$HD_Form->model[] = dontList(new TextField(_("From Name"),'fromname',_("Human name of sender")));
$HD_Form->model[] = new TextField(_("From"),'fromemail',_("Email of sender"));
$HD_Form->model[] = new TextField(_("Subject"),'subject');
$HD_Form->model[] = dontList(new TextAreaField(_("Message"),'message',_("The message, arguments are %-quoted")));
$HD_Form->model[] = dontList(new TextAreaField(_("Default args."),'defargs',_("Default values for arguments, in URL parameter format.")));

$HD_Form->model[] = new DelBtnField();

require("PP_page.inc.php");
?>
