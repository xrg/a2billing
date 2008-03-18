<?php

require_once(DIR_COMMON."Form/Class.ActionForm.inc.php");

/** An action form which displays results of provision engines */

class ProvisionActionForm extends ActionForm{
	public $prentries;
	public $prov_args=array(); ///< Arguments to be fed in prov.'s Init

	public function init($pr_entries,$sA2Billing= null){
		parent::init($sA2Billing);
		
		if ($pr_entries){
			$this->model[] = new RefField(_("Type"),'type', $pr_entries);
			$this->prentries = $pr_entries;
		}
	}
	public function setArg($key,$val){
		$this->prov_args[$key]=$val;
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
		$dbg_elem = new DbgElem();
		if ($this->FG_DEBUG>1)
			$dbg_elem->content.="Args: " . print_r($this->prov_args,true)."\n";
		
			// Now, process the entry and load the Provision engine
		try {
			switch($entry[2]){
			case 'ast-ini':
				require_once(DIR_COMMON."Provi/AsteriskIni.inc.php");
				
				$proengine = new AsteriskIniProvi();
				$proengine->dbg_elem=&$dbg_elem;
				$proengine->Init(array_merge($this->prov_args,array(categ=>$entry[3])));
				break;
			default:
				if ($this->FG_DEBUG)
					echo "Invalid categ ".$entry[2]." specified in source.<br>\n";
				return;
			}
		}catch(Exception $e){
			echo $e->getMessage();
			echo "\n";
			if ($this->FG_DEBUG)
				$dbg_elem->Render();
			return;
		}
		
		$fp = fopen('php://temp','r+');
		if (!$fp){
			if ($this->FG_DEBUG)
				echo "Cannot open temp stream.<br>\n";
			return;
		}
		
		$proengine->genContent($fp);
		
		if ($this->FG_DEBUG)
			$dbg_elem->Render();

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

?>