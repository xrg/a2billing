<?php
require_once ("./lib/defines.php");
require_once ("./lib/module.access.php");
require_once (DIR_COMMON."Form.inc.php");
require_once (DIR_COMMON."Form/Class.SqlRefField.inc.php");
require_once (DIR_COMMON."Form/Class.VolField.inc.php");
require_once (DIR_COMMON."Form/Class.TimeField.inc.php");
require_once (DIR_COMMON."Form/Class.SelectionForm.inc.php");

require_once (DIR_COMMON."Class.HelpElem.inc.php");

$menu_section='menu_config';
HelpElem::DoHelp(_("Templates for subscription services."));

/* We will be using different tables, fields, according to one selection box
   at the top of the page: */
$sub_cats = array();
$sub_cats[]  = array("all", _("All"));
$sub_cats[]  = array("only", _("Generic only"));
$sub_cats[]  = array("feature", _("Features"));

$SEL_Form = new SelectionForm();
$SEL_Form->init();
$SEL_Form->model[] = new RefField(_("Category"),'cat',$sub_cats, _("Select the category of templates."));

// Prepare the model for the base table.
$HD_Form= new FormHandler('subscription_template',_("Templates"),_("Template"));
$HD_Form->checkRights(ACX_MISC);
$HD_Form->init();

$HD_Form->addAllFollowParam($SEL_Form->prefix.'cat',$SEL_Form->getpost_dirty('cat'),false);

// Modify the model according to the selection:
switch($SEL_Form->getpost_single('cat')){
case 'all':
default:
	$HD_Form->views['ask-edit']= $HD_Form->views['ask-edit2'] = $HD_Form->views['edit'] =
		$HD_Form->views['ask-add'] = $HD_Form->views['ask-add2'] = $HD_Form->views['add'] =
		$HD_Form->views['ask-delete'] = $HD_Form->views['delete'] = new IdleView();
	break;	
case 'feature':
	$HD_Form->model_table='subscription_feature_templ';
	break;
case 'only':
	$HD_Form->model_table='ONLY subscription_template';
	break;
}

$PAGE_ELEMS[] = &$SEL_Form;
$PAGE_ELEMS[] = &$HD_Form;


$HD_Form->model[] = new PKeyFieldEH(_("ID"),'id');

$HD_Form->model[] = new TextFieldEH(_("Name"),'name',_("Internal name"));
$HD_Form->model[] = new TextField(_("Public Name"),'pubname',_("Public name is displayed to customers, agents. You may want it to be different than the internal name above."));
	end($HD_Form->model)->fieldacr= _("P Name");

$HD_Form->model[] = new BoolField(_("Invoiced"), "invoiced", _("If this subscription will be listed in customer's invoice."));
	end($HD_Form->model)->does_add = false;


// Modify again, for things that go last:
switch($SEL_Form->getpost_single('cat')){
case 'all':
default:
	break;
case 'feature':
	$HD_Form->model[] = new TextField(_("Feature"),'feature',_("The feature code this subscription provides."));
case 'only':
	$HD_Form->model[] = new DelBtnField();
	$PAGE_ELEMS[] = new AddNewButton($HD_Form);
	break;
}

require("PP_page.inc.php");
?>


