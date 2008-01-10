<?php
require_once ("./lib/defines.php");
require_once ("./lib/module.access.php");
require_once (DIR_COMMON."Form.inc.php");
require_once (DIR_COMMON."Class.HelpElem.inc.php");
require_once (DIR_COMMON."Form/Class.SqlRefField.inc.php");
require_once (DIR_COMMON."Form/Class.VolField.inc.php");
require_once (DIR_COMMON."Form/Class.ClauseField.inc.php");

$menu_section='menu_booths';


HelpElem::DoHelp(_("Booths are the physical phones where a customer can enter and make calls."));

$HD_Form= new FormHandler('cc_booth_v',_("Booths"),_("Booth"));
$HD_Form->checkRights(ACX_ACCESS);
$HD_Form->init(null,false);
$HD_Form->views['list']=new ListView();
$HD_Form->views['details'] = new DetailsView();

$PAGE_ELEMS[] = &$HD_Form;

$HD_Form->model[] = new PKeyField(_("ID"),'id');
$HD_Form->model[] = new FreeClauseField('agentid = \''. $_SESSION['agent_id'].'\'');

$HD_Form->model[] = new TextField(_("Name"),'name');
$HD_Form->model[] = new TextAreaField(_("Location"),'location');

$cs_list = array();
$cs_list[]  = array('0',_("N/A"));
$cs_list[]  = array('1',_("Empty"));
$cs_list[]  = array('2',_("Idle"));
$cs_list[]  = array('3',_("Ready"));
$cs_list[]  = array('4',_("Active"));
$cs_list[]  = array('5',_("Disabled"));
$cs_list[]  = array('6',_("Stopped"));

$HD_Form->model[] = dontEdit(new RefField(_("State"),'state', $cs_list));
$HD_Form->model[] = dontEdit(new MoneyField(_("Credit"),'credit',_("Money now in the card inside the booth.")));

require("PP_page.inc.php");
?>