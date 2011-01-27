<?php
require_once ("./lib/defines.php");
require_once ("./lib/module.access.php");
require_once ("a2blib/Form.inc.php");
require_once ("a2blib/Class.HelpElem.inc.php");
require_once ("a2blib/Form/Class.SqlRefField.inc.php");
require_once ("a2blib/Form/Class.TimeField.inc.php");
//require_once ("a2blib/Form/Class.ClauseField.inc.php");

$menu_section='menu_agents';

HelpElem::DoHelp(gettext("Presumed inconsistencies in sessions."));

$HD_Form= new FormHandler('cc_session_problems',_("Problems"),_("Problem"));
$HD_Form->checkRights(ACX_AGENTS);
$HD_Form->default_order = 'starttime';
$HD_Form->default_sens = 'DESC';
$HD_Form->init(null,false);
$HD_Form->views['list'] = new ListView();

$PAGE_ELEMS[] = &$HD_Form;
// $PAGE_ELEMS[] = new AddNewButton($HD_Form);

//$HD_Form->model[] = new FreeClauseField('agentid IS NULL');
$HD_Form->model[] = new PKeyField(_("ID"),'sid');
$HD_Form->model[] = new DateTimeField(_("Start Time"),'starttime');
$HD_Form->model[] = new TextField(_("Problem"), "problem");
$HD_Form->model[] = new SqlBigRefField(_("Card"),'card','cc_card','id','username');
$HD_Form->model[] = new SqlRefFieldN(_("Agent"), "agentid","cc_agent", "id", "login");
$HD_Form->model[] = new IntFieldN(_("Role"), "agent_role" );

$detBtn = new OtherBtnField();
	$detBtn->title=_("View");
	$detBtn->url = "invoices_cshop.php?";
	$detBtn->extra_params=array('sid' =>'sid');
$HD_Form->model[] = $detBtn;

require("PP_page.inc.php");
?>