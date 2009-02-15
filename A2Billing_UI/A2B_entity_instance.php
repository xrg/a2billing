<?php
require_once ("./lib/defines.php");
require_once ("./lib/module.access.php");
require_once (DIR_COMMON."Form.inc.php");
require_once (DIR_COMMON."Class.HelpElem.inc.php");
require_once (DIR_COMMON."Form/Class.SqlRefField.inc.php");
require_once (DIR_COMMON."Form/Class.SelectionForm.inc.php");
require_once (DIR_COMMON."Form/Class.TextSearchField.inc.php");
require_once (DIR_COMMON."Form/Class.ClauseField.inc.php");

$menu_section='menu_customers';

HelpElem::DoHelp(_("Here you can see the current registration of VoIP devices in the system (in the servers that support realtime)."));

$HD_Form= new FormHandler('cc_ast_instance',_("Instances"),_("Instance"));
$HD_Form->checkRights(ACX_CUSTOMER);
$HD_Form->init();

$HD_Form->model[] = dontList(new PKeyFieldEH(_("UserID"),'userid'));
$HD_Form->model[] = dontList(new PKeyField(_("ServerID"),'srvid'));
$HD_Form->model[] = dontList(new PKeyField(_("Mode"),'sipiax'));

$HD_Form->model[] = new SqlRefField(_("Server"), "srvid","cc_a2b_server", "id", "host");
$HD_Form->model[] = new BoolField(_("Dynamic"), dyn);
	end($HD_Form->model)->def_value=false;
	end($HD_Form->model)->does_list=false;
	end($HD_Form->model)->does_edit=false;
//	end($HD_Form->model)->does_add=false;
	
$HD_Form->model[] = new SqlBigRefField(_("User"), "userid","cc_ast_users_v", "id", "name");

$HD_Form->model[] = new TextField(_("User Name"),'username');

$sipiax_list = array();
$sipiax_list[] = array('1','SIP');
$sipiax_list[] = array('2','IAX');
$sipiax_list[] = array('5','SIP register');
$HD_Form->model[] = new RefField(_("Mode"), "sipiax", $sipiax_list);

$HD_Form->model[] = new TextFieldN(_("IP"),'ipaddr');
$HD_Form->model[] = dontList(new IntFieldN(_("Port"),'port'));
$HD_Form->model[] = dontList(new EpochFieldN(_("Reg. Seconds"),'regseconds',_("The timestamp the registration expires"))); //TODO: epochfield!

$HD_Form->model[] = dontList( new TextFieldN(_("Contact"),'fullcontact'));
$HD_Form->model[] = dontList( new TextFieldN(_("Reg. server"),'regserver',_("The name of the server which registered it, as defined in asterisk.conf")));

$HD_Form->model[] = new TextAreaField(_("User Agent"),'useragent');

//RevRef2::html_body($action);

$HD_Form->model[] = new GroupField(array(new DelBtnField(), new DetailsBtnField()));

$SEL_Form = new SelectionForm();
$SEL_Form->init();
$SEL_Form->model[] = new SqlRefField(_("Server"),'srvid','cc_a2b_server','id','host');
	end($SEL_Form->model)->does_add = false;
$dyn_list = array();
$dyn_list[] = array('t','Dynamic');
$dyn_list[] = array('f','Static');
$SEL_Form->model[] = new RefField(_("Dynamic"), "dyn", $dyn_list);
	end($SEL_Form->model)->does_add = false;
$SEL_Form->model[] = new RefField(_("Mode"), "sipiax", $sipiax_list);
	end($SEL_Form->model)->does_add = false;
$SEL_Form->model[] = new TextSearchField(_("Username"),'username');
$SEL_Form->model[] = new TextSearchField(_("User Agent"),'useragent');
//$CS_Form->agentid=$SEL_Form->getpost_single('agentid');

$PAGE_ELEMS[] = &$SEL_Form;
$PAGE_ELEMS[] = &$HD_Form;
$PAGE_ELEMS[] = new AddNewButton($HD_Form);

$clauses = $SEL_Form->buildClauses();
// 	$PAGE_ELEMS[] = new DbgElem(print_r($clauses,true));
foreach ($clauses as $clause)
	$HD_Form->model[] = new FreeClauseField($clause);


require("PP_page.inc.php");
?>
