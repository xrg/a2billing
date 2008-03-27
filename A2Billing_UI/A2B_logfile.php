<?php
require_once ("./lib/defines.php");
require_once ("./lib/module.access.php");
require_once (DIR_COMMON."Form.inc.php");
require_once (DIR_COMMON."Class.HelpElem.inc.php");
require_once (DIR_COMMON."Form/Class.SqlRefField.inc.php");
require_once (DIR_COMMON."Form/Class.TimeField.inc.php");
require_once (DIR_COMMON."Form/Class.ClauseField.inc.php");
require_once (DIR_COMMON."Form/Class.TextSearchField.inc.php");
require_once (DIR_COMMON."Form/Class.SelectionForm.inc.php");

$menu_section='menu_admin';

HelpElem::DoHelp(_("Log entries of the system"),'vcard.png');

$SEL_Form = new SelectionForm();
$SEL_Form->init();
$SEL_Form->model[] = new DateTimeField(_("Period from"),'date_from');
	end($SEL_Form->model)->does_add = false;
	end($SEL_Form->model)->def_date = '00:00 last month';
	end($SEL_Form->model)->fieldexpr = 'creationdate';
$SEL_Form->model[] = new DateTimeField(_("Period to"),'date_to');
	end($SEL_Form->model)->does_add = false;
	end($SEL_Form->model)->def_date = 'now';
	end($SEL_Form->model)->fieldexpr = 'creationdate';
$SEL_Form->search_exprs['date_from'] = '>=';
$SEL_Form->search_exprs['date_to'] = '<=';
$SEL_Form->fallbackClause=array('date_from');

$SEL_Form->model[] =dontAdd(new SqlRefFieldN(_("User"),'iduser','cc_ui_authen','userid','login', _("User of the interface.")));

$SEL_Form->model[] = new TextSearchField(_("Action"),'action');
$SEL_Form->model[] = new TextSearchField(_("Data"),'data');
$SEL_Form->model[] = new TextSearchField(_("Table"),'tablename');
$SEL_Form->model[] = new TextSearchField(_("Page"),'pagename');
$SEL_Form->model[] = new TextSearchField(_("IP Address"),'ipaddress');
$SEL_Form->model[] = dontAdd(new IntField(_("Level"),'loglevel'));

/*$SEL_Form->model[] = new SqlRefField(_("Plan"),'idrp','cc_retailplan','id','name', _("Retail plan"));
	end($SEL_Form->model)->does_add = false;*/


$HD_Form= new FormHandler('cc_system_log',_("Logs"),_("Log"));
$HD_Form->checkRights(ACX_ADMINISTRATOR);
$HD_Form->default_order='creationdate';
$HD_Form->default_sens='DESC';
$HD_Form->init(null,false);
$HD_Form->views['list'] = new ListView();
$HD_Form->views['details'] = new DetailsView();

$PAGE_ELEMS[] = &$HD_Form;
// put the selection form *below* the table!
$PAGE_ELEMS[] = &$SEL_Form;

$HD_Form->model[] = new PKeyField(_("ID"),'id');
$HD_Form->model[] = new DateTimeFieldDH(_("Date"), "creationdate");
$HD_Form->model[] = new SqlRefFieldN(_("User"),'iduser','cc_ui_authen','userid','login', _("User of the interface."));
$HD_Form->model[] = new IntField(_("Level"),'loglevel');

$HD_Form->model[] = new TextField(_("Action"),'action');
$HD_Form->model[] = new TextFieldN(_("Description"),'description');
$HD_Form->model[] = dontList(new TextFieldN(_("Data"),'data'));
$HD_Form->model[] = dontList(new TextFieldN(_("Table Name"),'tablename'));
$HD_Form->model[] = new TextFieldN(_("Page Name"),'pagename');
$HD_Form->model[] = new TextFieldN(_("IP Addr"),'ipaddress');

$SEL_Form->appendClauses($HD_Form);

require("PP_page.inc.php");
?>
