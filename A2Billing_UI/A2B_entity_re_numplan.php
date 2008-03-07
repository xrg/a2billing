<?php
require_once ("./lib/defines.php");
require_once ("./lib/module.access.php");
require_once (DIR_COMMON."Form.inc.php");
require_once (DIR_COMMON."Class.HelpElem.inc.php");
require_once (DIR_COMMON."Form/Class.RevRef.inc.php");
require_once (DIR_COMMON."Form/Class.RevRefForm.inc.php");
require_once (DIR_COMMON."Form/Class.SqlRefField.inc.php");

$menu_section='menu_config';


HelpElem::DoHelp(_("Reverse numplans are sets of rules that define the callerid of our customers on an outgoing call."),'vcard.png');

$HD_Form= new FormHandler('cc_re_numplan',_("Reverse Numplans"),_("Reverse Numplan"));
$HD_Form->checkRights(ACX_NUMPLAN);
$HD_Form->init();

$PAGE_ELEMS[] = &$HD_Form;
$PAGE_ELEMS[] = new AddNewButton($HD_Form);

$HD_Form->model[] = new PKeyFieldEH(_("ID"),'id');

$HD_Form->model[] = new TextFieldEH(_("Name"),'name',_('Name of group'));

$HD_Form->model[] = new DelBtnField();

$tmp = new RevRefForm(_("Pattern"),'pat','id','cc_re_numplan_pattern','nplan');
$HD_Form->meta_elems[] = $tmp;
	$tmp->Form->checkRights(ACX_NUMPLAN);
	$tmp->Form->init();
	$tmp->Form->model[] = new PKeyFieldEH(_("ID"),'id');
	$tmp->Form->model[] = new SqlRefFieldN(_("Forward numplan"),'fplan','cc_numplan','id','name',_("If set, this pattern will only apply to calls originating from cards in that numplan."));
		end($tmp->Form->model)->fieldacr = _("F Nplan");
	$tmp->Form->model[]= new TextField(_("Find"),'find',_('Prefix of outgoing number to match. This will match the <b>translated</b> string by the forward numplan.'));
	$tmp->Form->model[]= new TextField(_("Replace"),'repl',_('String to replace the match prefix with. It may include special strings like %useralias, %nplan etc. See manual.'));
	$tmp->Form->model[]= new TextFieldEH(_("Name"),'nick',_('Name of pattern'));
	//$tmp->Form->model[] = new SqlRefField(_("Out CLID Group"),'oclid','cc_outbound_cgroup','id','name');
	//	end($tmp->Form->model)->fieldacr = _("OC GRP");

	$tmp->Form->model[] = new DelBtnField();
	$tmp->Form->meta_elems[] = new AddNewButton($tmp->Form);

require("PP_page.inc.php");

?>
