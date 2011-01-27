<?php
require_once ("./lib/defines.php");
require_once ("./lib/module.access.php");
require_once ("a2blib/Form.inc.php");
require_once ("a2blib/Class.HelpElem.inc.php");
require_once ("a2blib/Form/Class.SqlRefField.inc.php");
require_once ("a2blib/Form/Class.RevRef.inc.php");
require_once ("a2blib/Form/Class.TextSearchField.inc.php");
require_once ("a2blib/Form/Class.ClauseField.inc.php");
require_once ("a2blib/Form/Class.SelectionForm.inc.php");
$menu_section='menu_ratecard';

HelpElem::DoHelp(gettext("Buy rates are the prices paid to the provider for some destination."));

$SEL_Form = new SelectionForm();
$SEL_Form->init();
$SEL_Form->model[] = new TextSearchField(_("Destination"),'destination');
$SEL_Form->model[] = new SqlRefField(_("Plan"),'idtp','cc_tariffplan','id','tariffname', _("Buy plan"));
	end($SEL_Form->model)->does_add = false;

$HD_Form= new FormHandler('cc_buyrate',_("Buy rates"),_("Buy rate"));
$HD_Form->checkRights(ACX_RATECARD);
$HD_Form->init();
$HD_Form->views['tooltip'] = new DetailsMcView();

$SEL_Form->enable($HD_Form->getAction() == 'list');

$PAGE_ELEMS[] = &$SEL_Form;
$PAGE_ELEMS[] = &$HD_Form;
$PAGE_ELEMS[] = new AddNewButton($HD_Form);

$HD_Form->model[] = new PKeyFieldEH(_("ID"),'id');
$clauses= $SEL_Form->buildClauses();
foreach($clauses as $cla)
	$HD_Form->model[] = new FreeClauseField($cla);

$HD_Form->model[] = new TextFieldEH(_("Destination"),'destination');
$HD_Form->model[] = new SqlRefField(_("Plan"),'idtp','cc_tariffplan','id','tariffname', _("Tariff plan"));

$HD_Form->model[] = new FloatField(_("Rate"),'buyrate',_("Price paid to carrier, per minute"));
$HD_Form->model[] = new IntField(_("Init Block"),'buyrateinitblock',_("Set the minimum duration charged by the carrier. (i.e. 30 secs)"));
	end($HD_Form->model)->fieldacr = _("IBlk");
$HD_Form->model[] = new IntField(_("Increment"),'buyrateincrement',_("Set the billing increment, in seconds (billing block), that the carrier applies. (ie 30 secs)"));
	end($HD_Form->model)->fieldacr = _("Incr");
$HD_Form->model[] = new FloatField(_("Quality"),'quality',"");
	end($HD_Form->model)->does_add=false;
	end($HD_Form->model)->fieldacr = _("Qual");

//if ($HD_Form->getAction()!='tooltip') not needed, RevRef only work on 'details' !
	$HD_Form->model[] = new RevRefTxt(_("Prefixes"),'prefx','id','cc_buy_prefix','brid','dialprefix',_("Dial prefixes covered by this rate."));

//RevRef2::html_body($action);

if ($HD_Form->getAction()!='tooltip')
	$HD_Form->model[] = new DelBtnField();

require_once("a2blib/Form/Class.ImportView.inc.php");
require_once("a2blib/Class.DynConf.inc.php");

$HD_Form->views['ask-import'] = new AskImportView();
$HD_Form->views['import-analyze'] = new ImportAView($HD_Form->views['ask-import']);
$HD_Form->views['import'] = new ImportView($HD_Form->views['ask-import']);

$HD_Form->views['ask-import']->common = array('idtp');
$HD_Form->views['ask-import']->mandatory = array('prefx','destination', 'buyrate');
$HD_Form->views['ask-import']->optional = array('buyrateinitblock','buyrateincrement');

// $HD_Form->views['ask-import']->examples = array( array(_('Simple'), "importsamples.php?sample=RateCard_Simple"),
// 					     array(_('Complex'),"importsamples.php?sample=RateCard_Complex"));

$HD_Form->views['import-analyze']->allowed_mimetypes=array('text/csv','text/x-csv');
$HD_Form->views['ask-import']->multiple[] = 'prefx';
$HD_Form->views['ask-import']->multi_sep = '|';

if($HD_Form->getAction()=='tooltip')
	require("PP_bare_page.inc.php");
else
	require("PP_page.inc.php");
?>
