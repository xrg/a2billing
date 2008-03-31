<?php
require_once ("./lib/defines.php");
require_once ("./lib/module.access.php");
require_once (DIR_COMMON."Form.inc.php");
require_once (DIR_COMMON."Form/Class.SqlActionForm.inc.php");
require_once (DIR_COMMON."Class.HelpElem.inc.php");
/*require_once (DIR_COMMON."Form/Class.RevRef.inc.php");*/
require_once (DIR_COMMON."Form/Class.TimeField.inc.php");
require_once (DIR_COMMON."Form/Class.SqlRefField.inc.php");
require_once (DIR_COMMON."Class.JQuery.inc.php");

$menu_section='menu_ratecard';

HelpElem::DoHelp(_("Simulate DID engine behaviour: this page will assist you in debugging which number will be called when some DID call comes in."),'vcard.png');

$HD_Form= new SqlTableActionForm();
$HD_Form->checkRights(ACX_RATECARD);
$HD_Form->init();

$PAGE_ELEMS[] = &$HD_Form;

$HD_Form->model[] = new TextField(_("DID"),'didstring',_("Put here the string asterisk would deliver to the DID engine."));
$HD_Form->model[] = new TextField(_("Cod"),'didcode',_("If you are using 'codes' to differentiate DIDs, put here the code by which the AGI would be called."));
$HD_Form->model[] = new DateTimeField(_("Time"),'curtime',_("The date/time the call is supposed to take place. This helps us predict tomorrow's rates"));
	end($HD_Form->model)->def_date='now';

/* Note: the secondary tables (sellrate, buyrate) *must* be queried inline, in the fields clause,
 (rather than the tables clause), because we have to preserve the order of Re3 rows. */
$HD_Form->QueryString= 'SELECT de.*, username(de.card) AS crd_username, id(de.card) AS crd_id,
	id(de.card) AS crd,
	(SELECT name FROM cc_didgroup WHERE id = de.dgid) AS dgid_name,
	(SELECT name FROM cc_tariffgroup WHERE id = de.tgid) AS tgid_name,
	(SELECT name FROM cc_numplan WHERE id = de.nplan) AS nplan_name,
	(SELECT destination FROM cc_buyrate WHERE id = de.brid2) AS brid2_destination
	FROM DIDEngine(%didstring,%didcode,%curtime) AS de;' ;
// Wrong one: FROM ...,
// 	   cc_buyrate, cc_sellrate
// 	WHERE cc_buyrate.id = re3.brid AND cc_sellrate.id = re3.srid ;' ;

// 	card cc_card_dv,
// 	     --- Per-call fields
// 	tgid INTEGER,
// 	dgid INTEGER, /* DID group ID */

$HD_Form->expectRows = true;
$HD_Form->submitString = _("Calculate!");
$HD_Form->successString =  '';
$HD_Form->noRowsString =  _("No DIDs found!");
//$HD_Form->contentString = 'Generated:<br>';

$HD_Form->rmodel[] = new TextField(_('Dial'),'dialstring') ;
$HD_Form->rmodel[] = new SqlRefFieldToolTip(_('Card'),'crd','cc_card','id','username');
	end($HD_Form->rmodel)->SetRefTooltip3("A2B_entity_card.php",_("Information about the buy rate:"));

$HD_Form->rmodel[] = new SqlRefField(_('Numplan'),'nplan','cc_numplan','id','name');
//	end($HD_Form->rmodel)->SetRefTooltip3("A2B_entity_numplan.php",_("Information about the buy rate:"));

$HD_Form->rmodel[] = new SqlRefField(_('DID Group'),'dgid','cc_didgroup','id','name');
	end($HD_Form->rmodel)->fieldacr=_("DGrp");

$HD_Form->rmodel[] = new SqlRefField(_('Tariff Group'),'tgid','cc_tariffgroup','id','name');
	end($HD_Form->rmodel)->fieldacr=_("TGrp");

// $HD_Form->rmodel[] = new TextField(_('Destination'),'destination') ;
// $HD_Form->rmodel[] = new SecondsField(_('Timeout'),'tmout') ;
// end($HD_Form->rmodel)->fieldacr=_('Tm');

$HD_Form->rmodel[] = new IntField(_('Metric'),'metric') ;
end($HD_Form->rmodel)->fieldacr=_('M');
$HD_Form->rmodel[] = new SqlRefFieldToolTip(_('Buy'),'brid2','cc_buyrate','id','destination');
	end($HD_Form->rmodel)->SetRefTooltip3("A2B_entity_buyrate.php",_("Information about the buy rate:"));


require("PP_page.inc.php");

?>
