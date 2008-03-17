<?php
require_once ("./lib/defines.php");
require_once ("./lib/module.access.php");
require_once (DIR_COMMON."Form.inc.php");
require_once (DIR_COMMON."Form/Class.SqlActionForm.inc.php");
require_once (DIR_COMMON."Class.HelpElem.inc.php");
/*require_once (DIR_COMMON."Form/Class.RevRef.inc.php");*/
require_once (DIR_COMMON."Form/Class.TimeField.inc.php");
require_once (DIR_COMMON."Form/Class.SqlRefField.inc.php");

$menu_section='menu_simulator';
if(!DynConf::GetCfg(CUSTOMER_CFG,'menu_simulator',true))
	exit();

HelpElem::DoHelp(_("Here you can simulate a phone call and see how much it would cost"),'vcard.png');

$HD_Form= new SqlTableActionForm();
$HD_Form->checkRights(ACX_ACCESS);
$HD_Form->init();

$PAGE_ELEMS[] = &$HD_Form;

$HD_Form->model[] = new TextField(_("Dial"),'dialstring',_("The number you wish to dial."));

$HD_Form->QueryString= str_dbparams(A2Billing::DBHandle(), 'SELECT re.*, %%dialstring AS init_dial, cc_sellrate.rateinitial,
	format_currency(sell_calc_fwd(INTERVAL \'5 min\', cc_sellrate.*)/5.0, %3) AS rate5min
	FROM (SELECT * FROM RateEngine3((SELECT tariffgroup FROM cc_card_group WHERE id = %#1), ' .
		'%%dialstring, (SELECT numplan FROM cc_card_group WHERE id = %#1), now(), 
		(SELECT credit FROM cc_card WHERE id = %#2)) LIMIT 1) AS re, cc_sellrate
		WHERE cc_sellrate.id = re.srid ;',
		array($_SESSION['card_grp'],$_SESSION['card_id'], $_SESSION['currency'])) ;

$HD_Form->expectRows = true;
$HD_Form->submitString = _("Calculate!");
$HD_Form->successString =  '';
$HD_Form->noRowsString =  _("No rates/destinations found!");
//$HD_Form->contentString = 'Generated:<br>';

$HD_Form->rmodel[] = new TextField(_('Dial'),'init_dial') ;
$HD_Form->rmodel[] = new TextField(_('Destination'),'destination') ;
$HD_Form->rmodel[] = new SecondsField(_('Max call duration'),'tmout') ;
end($HD_Form->rmodel)->fieldacr=_('Tm');

$HD_Form->rmodel[] = new IntField(_('Rate/min'),'rate5min') ;

// $HD_Form->rmodel[] = new TextField(_('Matched Prefix'),'prefix') ;
// end($HD_Form->rmodel)->fieldacr=_('Pr');

// $HD_Form->rmodel[] = new TextField(_('Clid Pattern'),'clidreplace') ;
// end($HD_Form->rmodel)->fieldacr=_('CLID');

require("PP_page.inc.php");
?>
