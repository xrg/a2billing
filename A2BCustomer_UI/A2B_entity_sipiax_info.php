<?php
require_once ("./lib/defines.php");
require_once ("./lib/module.access.php");
require_once (DIR_COMMON."Form.inc.php");
require_once (DIR_COMMON."Form/Class.SqlActionForm.inc.php");
require_once (DIR_COMMON."Class.HelpElem.inc.php");
/*require_once (DIR_COMMON."Form/Class.RevRef.inc.php");*/
require_once (DIR_COMMON."Form/Class.TimeField.inc.php");
require_once (DIR_COMMON."Form/Class.SqlRefField.inc.php");

$menu_section='menu_sipiax';
if(!DynConf::GetCfg(CUSTOMER_CFG,'menu_sipiax',true))
	exit();

HelpElem::DoHelp(_("Here you can get example settings you can use in your devices. Select the kind of device (phone) you have and settings will appear."),'phone.png');

class ProvisionActionForm extends ActionForm{
	public $prentries;

	public function init($pr_entries,$sA2Billing= null){
		parent::init($sA2Billing);
		
		if ($pr_entries){
			$this->model[] = new RefField(_("Type"),'type', $pr_entries);
			$this->prentries = $pr_entries;
		}
	}
	
	public function PerformAction(){
		if ($this->action != 'true')
			return;
		$this->action='display';
	}
	
	public function RenderContent() {
		$t =$this->getpost_single('type');
		$entry=null;
		foreach($this->prentries as $e)
			if ($e[0] == $t){
				$entry = $e;
				break;
			}
		if ($entry==null){
			echo "<div class=\"error\">"._("Invalid type!")."</div>\n";
			return false;
		}
		
		if ($this->FG_DEBUG>2){
			echo "<div class=\"debug\">";
			echo "ProvisionActionForm::RenderContent(): ";
			print_r($entry);
			echo "</div>\n";
		}
		
		$proengine=null;
			// Now, process the entry and load the Provision engine
		try {
			switch($entry[2]){
			case 'ast-ini':
				require_once(DIR_COMMON."Provi/AsteriskIni.inc.php");
				
				$proengine = new AsteriskIniProvi();
				$proengine->Init(array(cardid => $_SESSION['card_id'], categ=>$entry[3]));
				break;
			default:
				if ($this->FG_DEBUG)
					echo "Invalid categ ".$entry[2]." specified in source.<br>\n";
				return;
			}
		}catch(Exception $e){
			echo $e->getMessage();
			echo "\n";
			return;
		}
		
		$fp = fopen('php://temp','r+');
		if (!$fp){
			if ($this->FG_DEBUG)
				echo "Cannot open temp stream.<br>\n";
			return;
		}
		
		$proengine->genContent($fp);
		
		// the temporary stream at $fp holds the data. Rewind it and print
		// properly
		rewind($fp);
		
		echo "<div>".str_params(_("Here is your example %1:"),array($entry[1]),1)."</div>\n";
		
		echo '<div class=\"provi\">';
		echo nl2br(htmlspecialchars(stream_get_contents($fp)));
		echo "</div>\n";
		
		fclose($fp);
	}
};

$pr_list = array();
$pr_list[]  = array("0", _("Asterisk sip friend"),'ast-ini','sip-peer');
$pr_list[]  = array("1", _("Asterisk iax friend"),'ast-ini','iax-peer');

$HD_Form= new ProvisionActionForm();
$HD_Form->checkRights(ACX_ACCESS);
$HD_Form->init($pr_list);

$PAGE_ELEMS[] = &$HD_Form;


if ($HD_Form->getAction()== 'true')
	switch($HD_Form->getpost_single('type')){
	case '0':
		break;
}


$HD_Form->model[] = 
//$HD_Form->model[] = new TextField(_("Dial"),'dialstring',_("The number you wish to dial."));


require("PP_page.inc.php");
?>
