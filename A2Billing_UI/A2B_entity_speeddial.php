<?php
require_once ("./lib/defines.php");
require_once ("./lib/module.access.php");
require_once (DIR_COMMON."Form.inc.php");
require_once (DIR_COMMON."Class.HelpElem.inc.php");
require_once (DIR_COMMON."Form/Class.SqlRefField.inc.php");
require_once (DIR_COMMON."Form/Class.TimeField.inc.php");
require_once (DIR_COMMON."Class.JQuery.inc.php");

$menu_section='menu_customers';

HelpElem::DoHelp(gettext("Speed Dial <br> This section allows you to define the Speed dials for the customer. A Speed Dial will be entered on the IVR in order to make a shortcut to their preferred dialed phone number."));

$HD_Form= new FormHandler('speeddials',_("SpeedDials"),_("SpeedDial"));
$HD_Form->checkRights(ACX_CUSTOMER);
$HD_Form->init();

$PAGE_ELEMS[] = &$HD_Form;
$PAGE_ELEMS[] = new AddNewButton($HD_Form);


$HD_Form->model[] = new PKeyFieldEH(_("ID"),'id');
$HD_Form->model[] = new TextFieldEH(_("SpeedDial"),'speeddial');
$HD_Form->model[] = new TextFieldEH(_("Phone Number"),'phone',_("Enter the phone number for the speed dial."));
$HD_Form->model[] = new TextFieldEH(_("Name"),'name',_("Enter the name or label that will identify this speed dial."));


//$HD_Form->model[] = new SqlBigRefField(_("CardNumber"), "card_id","cc_card", "id", "username");
$HD_Form->model[] = new SqlRefFieldToolTip(_("CardNumber"), "card_id","cc_card", "id", "username");
	end($HD_Form->model)->SetRefEntity("A2B_entity_card.php");
	end($HD_Form->model)->SetRefEntityL("A2B_entity_card.php");
	end($HD_Form->model)->SetEditTitle(_("Card ID"));
	end($HD_Form->model)->SetCaptionTooltip(_("Information about the card holder :"));
	end($HD_Form->model)->SetRefTooltip("A2B_entity_card.php");
	
$HD_Form->model[] = dontAdd(dontEdit(new DateTimeField(_("Creation date"), "creationdate", _("Date the card was created (entered into this system)"))));

$HD_Form->model[] = new DelBtnField();

require("PP_page.inc.php");
?>