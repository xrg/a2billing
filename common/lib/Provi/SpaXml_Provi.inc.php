<?php
require_once("Class.Provision.inc.php");

class SpaXmlProvi extends ProviEngine {
	protected $grprows;
	protected $cardinfo;
	protected $dbhandle;
	
	//protected $args=array();
	public function SpaXmlProvi(){
		$this->dbhandle = A2Billing::DbHandle();
	}

	public function getMimeType(){
		return 'text/plain';
	}
	
	public function Init(array $args){
		$dbhandle = $this->dbhandle;
		//We have to fetch all group rows in one go, because
		// we are going to re-use them.
		
		$qry = str_dbparams($dbhandle,'SELECT cc_card.*, cc_ast_users.devmodel '.
			' FROM cc_card, cc_ast_users '.
			' WHERE cc_ast_users.card_id = cc_card.id AND cc_ast_users.macaddr = %1;',
			array($args['mac']));
			
		$this->out(LOG_DEBUG,"Query: $qry");
		$res= $dbhandle->Execute($qry);
		if (!$res){
			$this->out(LOG_ERR,$dbhandle->ErrorMsg());
			throw new Exception("Cannot locate card");
		}elseif($res->EOF){
			$this->out(LOG_WARNING,'No rows for cc_card');
			return false;
		}
		$cardrow=  $res->fetchRow();
		
		$qry = str_dbparams($dbhandle, "SELECT * FROM provision_group ".
			" WHERE categ = 'spa-conf' AND model = %!1 ORDER BY metric;",
			array($cardrow['devmodel']));
		$this->out(LOG_DEBUG,"Query: $qry");
		$grprows= $dbhandle->GetAll($qry);
		if ($grprows === FALSE){
			$this->out(LOG_ERR,$dbhandle->ErrorMsg());
			throw new Exception("Cannot locate group");
		}elseif(count($grprows)==0){
			$this->out(LOG_WARNING,'No provision_group for model: "'.$cardrow['devmodel'].'"');
			return false;
		}

		$this->grprows=$grprows;
		$this->cardinfo=$cardrow;
		return true;
	}
	
	public function genContent(&$outstream){
		fwrite($outstream, '<flat-profile>\n');
		fwrite($outstream,"\t<!-- Generated content -->\n\n");
		
		$crd=$this->cardinfo;
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
				$this->out(LOG_WARNING,'No rows for provisions');
				continue;
			}
		
			while($row = $pres->fetchRow()){
				$line='<'.$row['name'].' > ';
				$line .= str_alparams($row['valuef'],$crd);
				$line .='</'.$row['name'].">\n";
				fwrite($outstream,$line);
			}
			fwrite($outstream,"\n</flat-profile>\n");
		}
	}

};

?>
