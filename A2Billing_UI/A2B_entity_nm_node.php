<?php
require_once ("./lib/defines.php");
require_once ("./lib/module.access.php");
require_once ("a2blib/Form.inc.php");
require_once ("a2blib/Class.HelpElem.inc.php");
require_once ("a2blib/Form/Class.SqlRefField.inc.php");
// require_once ("a2blib/Form/Class.TabField.inc.php");
require_once ("a2blib/Form/Class.TimeField.inc.php");

// require_once ("a2blib/Class.JQuery.inc.php");

$menu_section='menu_netmon';

HelpElem::DoHelp(_("Nodes make up the attributes of a system."));

$HD_Form= new FormHandler('nm.attr_node',_("Nodes"),_("Node"));
$HD_Form->checkRights(ACX_NETMON);
$HD_Form->default_order='id';
$HD_Form->default_sens='ASC';
$HD_Form->init();

$PAGE_ELEMS[] = &$HD_Form;
$PAGE_ELEMS[] = new AddNewButton($HD_Form);


$HD_Form->model[] = new PKeyFieldEH(_("ID"),'id');
$HD_Form->model[] = new SqlRefField(_("Class"),"clsid", "nm.class", "id","name");
$HD_Form->model[] = new TextFieldEH(_("Name"),'name');
//$HD_Form->model[] = new TextField(_("Code"),'code');
$HD_Form->model[] = new SqlRefFieldN(_("System"),"sysid", "nm.system", "id","name");
$HD_Form->model[] = new SqlRefFieldN(_("Parent"),"par_id", "nm.attr_node", "id","name");
	end($HD_Form->model)->combofield="id|| '. ' || name";
//$HD_Form->model[] = new TextAreaField(_("Comment"),'comment');
$HD_Form->model[] = new DateTimeField(_("Date"), "tstamp");

$HD_Form->model[] = new DelBtnField();

require("PP_page.inc.php");
?>
