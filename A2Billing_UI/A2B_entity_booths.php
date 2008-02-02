<?php
require_once ("./lib/defines.php");
require_once ("./lib/module.access.php");
require_once (DIR_COMMON."Form.inc.php");
require_once (DIR_COMMON."Class.HelpElem.inc.php");
require_once (DIR_COMMON."Form/Class.SqlRefField.inc.php");
require_once (DIR_COMMON."Form/Class.TimeField.inc.php");

$menu_section='menu_agents';


HelpElem::DoHelp(gettext("Booths are public phones, where the customer can make calls using any/some of our cards."));

$HD_Form= new FormHandler('cc_booth',_("Booths"),_("Booth"));
$HD_Form->checkRights(ACX_AGENTS);
$HD_Form->init();

$PAGE_ELEMS[] = &$HD_Form;
$PAGE_ELEMS[] = new AddNewButton($HD_Form);

$HD_Form->model[] = new PKeyFieldEH(_("ID"),'id');
$HD_Form->model[] = new TextFieldEH(_("Name"),'name',_("Human readable name for the booth"));

$HD_Form->model[] = new TextAreaField(_("Location"),'location',30,_("Helps us remember where the booth is."));
$HD_Form->model[] = new SqlRefField(_("Agent"),'agentid','cc_agent','id','name', _("Agent the booth belongs to"));

$HD_Form->model[] = dontList( new DateTimeField(_("Creation"),'datecreation',_("When the booth was created.")));

$HD_Form->model[] = dontAdd( new BoolField(_("Disabled"),'disabled',_("If true, booth is unusable. eg. broken")));

$HD_Form->model[] = dontList( dontAdd( new IntFieldN(_("Default card"),'def_card_id',_("Default card. Must exist for booth to be usable."))));
$HD_Form->model[] = dontAdd( new IntFieldN(_("Current card"),'cur_card_id',_("Current card making calls in booth.")));
	end($HD_Form->model)->fieldacr = _("CurC");

$HD_Form->model[] = dontList( new TextField(_("Caller ID"),'callerid',_("*-*")));
$HD_Form->model[] = new TextField(_("Peer Name"),'peername',_("Asterisk peer name for authentication"));
$HD_Form->model[] = dontList(new TextField(_("Peer Secret"),'peerpass',_("Asterisk secret for peer")));

$HD_Form->model[] = new DelBtnField();

require("PP_page.inc.php");

?>