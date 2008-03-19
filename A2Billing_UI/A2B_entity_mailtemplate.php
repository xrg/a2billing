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

require_once(DIR_COMMON."Form/Class.ImportView.inc.php");
require_once(DIR_COMMON."Class.DynConf.inc.php");

$HD_Form->views['ask-import'] = new AskImportView();
$HD_Form->views['import-analyze'] = new ImportMailAView($HD_Form->views['ask-import']);
// $HD_Form->views['import'] = new ImportView($HD_Form->views['ask-import']);

//$HD_Form->views['ask-import']->common = array('idrp');
$HD_Form->views['ask-import']->csvmode = false;
$HD_Form->views['ask-import']->mandatory = array('mtype','lang', 'fromemail','subject','message');
$HD_Form->views['ask-import']->optional = array('fromname','defargs');
$HD_Form->views['ask-import']->bodyfield = 'message';

// $HD_Form->views['ask-import']->examples = array( array(_('Simple'), "importsamples.php?sample=RateCard_Simple"),
// 					     array(_('Complex'),"importsamples.php?sample=RateCard_Complex"));

$HD_Form->views['import-analyze']->allowed_mimetypes=array('text/plain','application/octet-stream');

require("PP_page.inc.php");
?>
