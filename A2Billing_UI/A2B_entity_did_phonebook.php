<?php
require_once ("./lib/defines.php");
require_once ("./lib/module.access.php");
require_once (DIR_COMMON."Form.inc.php");
require_once (DIR_COMMON."Form/Class.SqlRefField.inc.php");
require_once (DIR_COMMON."Form/Class.VolField.inc.php");
require_once (DIR_COMMON."Form/Class.TimeField.inc.php");
require_once (DIR_COMMON."Form/Class.RevRefForm.inc.php");

require_once (DIR_COMMON."Class.HelpElem.inc.php");

$menu_section='menu_did';
HelpElem::DoHelp(_("DID phonebooks help associate incoming CLIDs with names"));

$HD_Form= new FormHandler('did_phonebook',_("DID Phonebooks"),_("DID phonebook"));
$HD_Form->checkRights(ACX_DID);
$HD_Form->init();


$PAGE_ELEMS[] = &$HD_Form;
$PAGE_ELEMS[] = new AddNewButton($HD_Form);

$HD_Form->model[] = new PKeyFieldEH(_("ID"),'id');

$HD_Form->model[] = new TextFieldEH(_("Name"),'name');
$HD_Form->model[] = new TextFieldN(_("Code"),'code',_("A code, for syncing it with foreign ones."));
$HD_Form->model[] = dontList(new SqlRefFieldN(_("CLID Rules"), "rnplan","cc_re_numplan", "id", "name",_("Use this book in those CLIDs only")));
$HD_Form->model[] = dontList(new SqlRefFieldN(_("Card Group"), "card_group","cc_card_group", "id", "name",_("Use this book in that card group only")));
$HD_Form->model[] = dontList(new SqlBigRefField(_("Card"), "cardid","cc_card", "id", "name",_("Use this book only for that card: private one.")));

$HD_Form->model[] = new DelBtnField();

$tmp = new RevRefForm(_("Entries"),'ints','id','did_pb_entry','pb');
$HD_Form->meta_elems[] = $tmp;
	$tmp->Form->checkRights(ACX_DID);
	$tmp->Form->init();
	$tmp->Form->model[] = new PKeyFieldEH(_("ID"),'id');

	$tmp->Form->model[]= new TextField(_("Name"),'name');
	$tmp->Form->model[]= new TextField(_("Number"),'dnum',_('Number, in intermediate incoming format.'));
	$tmp->Form->model[] = new DelBtnField();
	$tmp->Form->meta_elems[] = new AddNewButton($tmp->Form);

require("PP_page.inc.php");

?>
