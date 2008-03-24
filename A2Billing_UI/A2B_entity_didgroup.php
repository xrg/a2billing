<?php
require_once ("./lib/defines.php");
require_once ("./lib/module.access.php");
require_once (DIR_COMMON."Form.inc.php");
require_once (DIR_COMMON."Class.HelpElem.inc.php");
require_once (DIR_COMMON."Form/Class.RevRef2.inc.php");

$menu_section='menu_did';

HelpElem::DoHelp(_("DID groups allow a group of customers to use incoming calls."));

$HD_Form= new FormHandler('cc_didgroup',_("DID Groups"),_("DID group"));
$HD_Form->checkRights(ACX_DID);
$HD_Form->init();


$PAGE_ELEMS[] = &$HD_Form;
$PAGE_ELEMS[] = new AddNewButton($HD_Form);

$HD_Form->model[] = new PKeyFieldEH(_("ID"),'id');

$HD_Form->model[] = new TextFieldEH(_("Name"),'name');
$HD_Form->model[] = new TextField(_("Code"),'code',_("Code matched against the source (trunk) that delivers the DID."));

$HD_Form->model[] = new RevRef2(_("Batches"),'bts','id','did_group_batch','btid','dbid','did_batch','id','name',_("These DID batches will be available and/or matched against the incoming DIDs."));

//$HD_Form->model[] = new TextField(_("xx"),'xx');
$HD_Form->model[] = new RevRef2(_("Sell plans"),'tplans','id','did_group_sell','btid','rtid','cc_retailplan','id','name',_("Calls on this DID will use that retail plan to get billed."));


$HD_Form->model[] = new DelBtnField();

require("PP_page.inc.php");
?>
