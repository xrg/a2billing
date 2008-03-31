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

// HelpElem::DoHelp(gettext("*-*"),'vcard.png');

$HD_Form= new SqlTableActionForm();
$HD_Form->checkRights(ACX_RATECARD);
$HD_Form->init();

$PAGE_ELEMS[] = &$HD_Form;

$HD_Form->model[] = new SqlRefField(_("Card Group"), "grp","cc_card_group", "id", "name",_("The call is made using a card of this group."));
$HD_Form->model[] = new TextField(_("Dial"),'dialstring',_("The number we wish to dial. It will be at the card's numplan"));
$HD_Form->model[] = new DateTimeField(_("Time"),'curtime',_("The date/time the call is supposed to take place. This helps us predict tomorrow's rates"));
	end($HD_Form->model)->def_date='now';
$HD_Form->model[] = new FloatField(_("Credit"),'money',_("Credit the customer will have for that call. Used to calculate the timeout"));
	end($HD_Form->model)->def_value=10.0;

/* Note: the secondary tables (sellrate, buyrate) *must* be queried inline, in the fields clause,
 (rather than the tables clause), because we have to preserve the order of Re3 rows. */
$HD_Form->QueryString= 'SELECT re3.*, (SELECT destination FROM cc_buyrate WHERE id = re3.brid) AS brid_destination,
		(SELECT destination FROM cc_sellrate WHERE id = re3.srid) AS srid_destination
	FROM RateEngine3((SELECT tariffgroup FROM cc_card_group WHERE id = %#grp), 
		%dialstring, (SELECT numplan FROM cc_card_group WHERE id = %#grp), %curtime, %money) AS re3 ;' ;
// Wrong one: FROM ...,
// 	   cc_buyrate, cc_sellrate
// 	WHERE cc_buyrate.id = re3.brid AND cc_sellrate.id = re3.srid ;' ;

$HD_Form->expectRows = true;
$HD_Form->submitString = _("Calculate!");
$HD_Form->successString =  '';
$HD_Form->noRowsString =  _("No rates/destinations found!");
//$HD_Form->contentString = 'Generated:<br>';
$HD_Form->rmodel[] = new TextField(_('Dial'),'dialstring') ;
$HD_Form->rmodel[] = new TextField(_('Destination'),'destination') ;
$HD_Form->rmodel[] = new SecondsField(_('Timeout'),'tmout') ;
end($HD_Form->rmodel)->fieldacr=_('Tm');

$HD_Form->rmodel[] = new SqlRefFieldToolTip(_('Sell'),'srid','cc_sellrate','id','destination');
	end($HD_Form->rmodel)->SetRefEntity("A2B_entity_sellrate.php");
	end($HD_Form->rmodel)->SetRefEntityL("A2B_entity_sellrate.php");
	end($HD_Form->rmodel)->SetCaptionTooltip(_("Information about the sell rate:"));
	end($HD_Form->rmodel)->SetRefTooltip("A2B_entity_sellrate.php");

$HD_Form->rmodel[] = new IntField(_('Metric'),'metric') ;
end($HD_Form->rmodel)->fieldacr=_('M');
$HD_Form->rmodel[] = new SqlRefFieldToolTip(_('Buy'),'brid','cc_buyrate','id','destination');
	end($HD_Form->rmodel)->SetRefEntity("A2B_entity_buyrate.php");
	end($HD_Form->rmodel)->SetRefEntityL("A2B_entity_buyrate.php");
	end($HD_Form->rmodel)->SetCaptionTooltip(_("Information about the buy rate:"));
	end($HD_Form->rmodel)->SetRefTooltip("A2B_entity_buyrate.php");

$HD_Form->rmodel[] = new TextField(_('Trunk'),'trunkcode') ;
// $HD_Form->rmodel[] = new SqlRefFieldToolTip(_('Trunk'),'trunk','cc_buyrate','id','destination');
end($HD_Form->rmodel)->fieldacr=_('TR');
// 	end($HD_Form->rmodel)->SetRefEntity("A2B_entity_buyrate.php");
// 	end($HD_Form->rmodel)->SetRefEntityL("A2B_entity_buyrate.php");
// 	end($HD_Form->rmodel)->SetCaptionTooltip(_("Information about the buy rate:"));
// 	end($HD_Form->rmodel)->SetRefTooltip("A2B_entity_buyrate.php");

$HD_Form->rmodel[] = new IntField(_('Trunk Free'),'trunkfree') ;
end($HD_Form->rmodel)->fieldacr=_('Tf');
$HD_Form->rmodel[] = new TextField(_('Feature'),'trunkfeat') ;

$HD_Form->rmodel[] = new TextField(_('Matched Prefix'),'prefix') ;
end($HD_Form->rmodel)->fieldacr=_('Pr');

$HD_Form->rmodel[] = new TextField(_('Clid Pattern'),'clidreplace') ;
end($HD_Form->rmodel)->fieldacr=_('CLID');

require("PP_page.inc.php");

?>
