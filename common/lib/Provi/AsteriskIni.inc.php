<?php
require_once("Class.Provision.inc.php");
require_once("./lib/Class.A2Billing.inc.php");
require_once("./lib/Misc.inc.php");

class AsteriskIniProvi extends ProviEngine {
	protected $grpres;
	protected $itemres;
	protected $dbhandle;
	
	//protected $args=array();
	public function AsteriskIniProvi(){
		$this->dbhandle = A2Billing::DbHandle();
	}

	public function getMimeType(){
		return 'text/plain';
	}
	
	public function Init(array $args){
		$dbhandle = $this->dbhandle;
		//We have to fetch all group rows in one go, because
		// we are going to re-use them.
		$qry = str_dbparams($dbhandle, "SELECT * FROM provision_group ".
			" WHERE model = 'ast-ini-card' AND categ = %1 ORDER BY metric;",
			array($args['categ']));
		$this->out(LOG_DEBUG,"Query: $qry");
		$grprows= $dbhandle->GetAll($qry);
		if (!$grprows){
			$this->out(LOG_ERR,$dbhandle->ErrorMsg());
			throw new Exception("Cannot locate group");
		}elseif(count($grprows)==0){
			$this->out(LOG_WARNING,'No rows for provision_group');
		}
		
		$qry = str_dbparams($dbhandle,"SELECT * FROM cc_card WHERE id = %#1;",
			array($args['cardid']));
			
		$this->out(LOG_DEBUG,"Query: $qry");
		$itemres= $dbhandle->Execute($qry);
		if (!$itemres){
			$this->out(LOG_ERR,$dbhandle->ErrorMsg());
			throw new Exception("Cannot locate card");
		}elseif($itemres->EOF){
			$this->out(LOG_WARNING,'No rows for cc_card');
		}
		
		$this->grprows=$grprows;
		$this->itemres=$itemres;
	}
	
	public function genContent(&$outstream){
		fwrite($outstream,"Test!\n");
		
		while($crd=$this->itemres->fetchRow())
		foreach($this->grprows as $grp){
			$line='';
			$qry = str_dbparams($this->dbhandle,"SELECT * FROM provisions ".
				"WHERE grp_id = %#1 ORDER BY metric;",
				array($grp['id']));
			$this->out(LOG_DEBUG,"Query: $qry");
			$pres=$this->dbhandle->Execute($qry);
			if (!$pres){
				$this->out(LOG_ERR,$this->dbhandle->ErrorMsg());
				throw new Exception("Cannot locate provision");
			}elseif($itemres->EOF){
				$this->out(LOG_WARNING,'No rows for cc_card');
				continue;
			}
		
				// Write a header like [name] ..
			$line = '[';
			if( !empty($grp['sub_name']))
				$line .= str_alparams($grp['sub_name'],$crd);
			else
				$line .= $grp['name'];
			$line .= "]\n";
			fwrite($outstream,$line);
			
			while($row = $pres->fetchRow()){
				$line='';
				if(!empty($row['sub_name']))
					$line= str_alparams($row['sub_name'],$crd);
				else
					$line= $row['name'];
				$line.= '=';
				$line .= str_alparams($row['valuef'],$crd);
				$line .="\n";
				fwrite($outstream,$line);
			}
			fwrite($outstream,"\n");
		}
	}

};

?>
