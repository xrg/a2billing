<?php
require_once ("./lib/defines.php");
require_once ("./lib/module.access.php");
require_once (DIR_COMMON."Form.inc.php");
require_once (DIR_COMMON."Class.HelpElem.inc.php");
require_once (DIR_COMMON."Form/Class.SqlRefField.inc.php");
require_once (DIR_COMMON."Form/Class.VolField.inc.php");
require_once (DIR_COMMON."Form/Class.TimeField.inc.php");

$menu_section='menu_customers';


HelpElem::DoHelp(_("Asterisk users are the entries that provide SIP/IAX peers for asterisk."),'vcard.png');

$HD_Form= new FormHandler('cc_ast_users',_("Users"),_("User"));
$HD_Form->checkRights(ACX_CUSTOMER);
$HD_Form->init();

$PAGE_ELEMS[] = &$HD_Form;
$PAGE_ELEMS[] = new AddNewButton($HD_Form);

$HD_Form->model[] = new PKeyFieldEH(_("ID"),'id');

$HD_Form->model[] = new SqlBigRefField(_("Card"),'card_id','cc_card','id','username',_("Corresponding card"));
$HD_Form->model[] = new SqlBigRefField(_("Booth"),'booth_id','cc_booth','id','peername',_("Booth (if no card)"));

$HD_Form->model[] = new SqlRefField(_("Config"), "config","cc_ast_users_config", "id", "cfg_name");

$HD_Form->model[] = new BoolField(_("SIP"),'has_sip',_("If true, the peer will have a SIP entry"));
$HD_Form->model[] = new BoolField(_("IAX"),'has_iax',_("If true, the peer will have a IAX2 entry"));
$HD_Form->model[] = DontList(new TextFieldN(_("Default IP"),'defaultip',_("Default IP to ring user at.")));
$HD_Form->model[] = new TextField(_("Host"),'host',_("Statically bind user with some IP/DNS or 'dynamic' for users that will register."));

$HD_Form->model[] = DontList( new TextFieldN(_("From user"),'fromuser',_("Override user string.")));


$HD_Form->model[] = new DelBtnField();


require("PP_page.inc.php");

?>