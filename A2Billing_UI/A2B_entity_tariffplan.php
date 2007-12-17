<?php
require_once ("./lib/defines.php");
require_once ("./lib/module.access.php");
require_once (DIR_COMMON."Form.inc.php");
require_once (DIR_COMMON."Class.HelpElem.inc.php");
require_once (DIR_COMMON."Form/Class.TimeField.inc.php");
require_once (DIR_COMMON."Form/Class.SqlRefField.inc.php");

$menu_section='menu_ratecard';

HelpElem::DoHelp(gettext("Tariff plan is the table of <b>Buying</b> rates, as given by our providers."));

$HD_Form= new FormHandler('cc_tariffplan',_("Tariff Plans"),_("Tariff plan"));
$HD_Form->checkRights(ACX_RATECARD);
$HD_Form->init();


$PAGE_ELEMS[] = &$HD_Form;
$PAGE_ELEMS[] = new AddNewButton($HD_Form);

$HD_Form->model[] = new PKeyFieldEH(_("ID"),'id');

$HD_Form->model[] = new TextFieldEH(_("Name"),'tariffname');
$HD_Form->model[] = new TextAreaField(_("Description"),'description');
$HD_Form->model[] = new IntField(_("Metric"),'metric',_("Weight of plan, lower metrics will be preferred at the rate engine."));
end($HD_Form->model)->def_value=10;
$HD_Form->model[] = new SqlRefField(_("Trunk"),'trunk','cc_trunk','id','trunkcode', _("Trunk used by these rates"));

$HD_Form->model[] = new DateTimeField(_("Start date"), "start_date", _("Date these rates are valid from"));
	end($HD_Form->model)->def_date='+1 day';
$HD_Form->model[] = new DateTimeField(_("Stop date"), "stop_date", _("Date these rates are valid until."));
	end($HD_Form->model)->def_date='+1 month 1 day';

$HD_Form->model[] = new TimeOWField(_("Period begin"), "starttime", _("Time of week the rate starts to apply"));
end($HD_Form->model)->def_value=0;
$HD_Form->model[] = new TimeOWField(_("Period end"), "endtime", _("Time of week the rate stops apply"));
end($HD_Form->model)->def_value=10079;
//$HD_Form->model[] = new TextField(_("xx"),'xx');


//RevRef2::html_body($action);

$HD_Form->model[] = new DelBtnField();


require("PP_page.inc.php");



//// *-*
if (false) {
	//TODO: styles
?>
<table align="center" border="0" width="65%"  cellspacing="1" cellpadding="2">
<tbody>
	<form name="updateForm" action="tariffplan_export.php" method="post">
	<INPUT type="hidden" name="id_tp" value="<?= $id_tp ?>">
	<tr> <td>#<?= $id_tp ?>
		<?= _("Type"); ?>&nbsp;: 
		<select name="export_style" size="1" class="form_input_select">
			<option value='peer-full-csv' selected><?= _("Peer Full CSV") ?></option>
			<option value='peer-full-xml'><?= _("Peer Full XML") ?></option>
			<option value='client-csv' ><?= _("Client CSV") ?></option>
		</select>
		</td>
	</tr>
	<tr><td align="right" >
		<input class="form_input_button" value="<?= _("EXPORT RATECARD");?>" type="submit">
	</td> </tr>
</form>
</table>

<?php
}

?>
