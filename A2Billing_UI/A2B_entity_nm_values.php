<?php
require_once ("./lib/defines.php");
require_once ("./lib/module.access.php");
require_once (DIR_COMMON."Form.inc.php");
require_once (DIR_COMMON."Class.HelpElem.inc.php");
require_once (DIR_COMMON."Form/Class.SqlRefField.inc.php");
require_once (DIR_COMMON."Form/Class.TimeField.inc.php");

require_once (DIR_COMMON."Form/Class.Import2View.inc.php");
require_once (DIR_COMMON."Provi/Class.SysLogImport.inc.php");

$menu_section='menu_netmon';

HelpElem::DoHelp(_("Values recorded in the NetMon module."));

$HD_Form= new FormHandler('nm.attr_value',_("Values"),_("Value"));
$HD_Form->checkRights(ACX_NETMON);
$HD_Form->default_order='id';
$HD_Form->default_sens='ASC';
$HD_Form->init();
$HD_Form->views['ask-import'] = new Ask2ImportView("Import some syslog data");
$HD_Form->views['import'] = new Import2View('SensorsLogImport');
$PAGE_ELEMS[] = &$HD_Form;
// $PAGE_ELEMS[] = new AddNewButton($HD_Form);


$HD_Form->model[] = new PKeyFieldEH(_("ID"),'id');
$HD_Form->model[] = new DateTimeField(_("Date"), "tstamp");
$HD_Form->model[] = new SqlRefFieldN(_("Parent"),"par_id", "nm.attr_node", "id","name");
	end($HD_Form->model)->combofield="id|| '. ' || name";
//$HD_Form->model[] = new TextAreaField(_("Comment"),'comment');

// $HD_Form->model[] = new DelBtnField(); No!, we don't want that..

require("PP_page.inc.php");
?>
