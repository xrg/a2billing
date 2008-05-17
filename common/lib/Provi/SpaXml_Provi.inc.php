<?php
require_once("Class.Provision.inc.php");

class SpaXmlProvi extends ProviEngine {
	protected $grprows;
	protected $cardres;
	protected $dbhandle;
	protected $args ;
	protected $confname='spa-conf';
	
	//protected $args=array();
	public function SpaXmlProvi(){
		$this->dbhandle = A2Billing::DbHandle();
	}

	public function getMimeType(){
		return 'text/xml';
	}
	
	public function Init(array $args){
		$dbhandle = $this->dbhandle;
		$this->args = $args;
		//We have to fetch all group rows in one go, because
		// we are going to re-use them.
		
		$qry = str_dbparams($dbhandle,'SELECT DISTINCT cc_card.*, cc_ast_users.devmodel, '.
			' cc_ast_users.devsecret, cc_ast_users.callerid, cc_ast_users.defaultip, '.
			' cc_ast_users.provi_num, '.
			' COALESCE(cc_ast_users.peernameb, cc_card.username) AS ext_name, '.
			' COALESCE(cc_ast_users.secretb,cc_card.userpass) AS ext_password, ' .
			' to_char(now(), \'HH24/MI\') AS localtime, to_char(now(), \'MM/DD\') AS localdate '.
			' FROM cc_card, cc_ast_users '.
			' WHERE cc_ast_users.card_id = cc_card.id 
			  AND cc_ast_users.macaddr = %1
			  AND cc_ast_users.devsecret = %2
			  AND EXISTS (SELECT 1 FROM provision_group WHERE categ = %3 
			  		AND model = cc_ast_users.devmodel) 
			  ORDER BY provi_num;',
			array($args['mac'], $args['sec'],$this->confname));
			
		$this->out(LOG_DEBUG,"Query: $qry");
		$res= $dbhandle->Execute($qry);
		if (!$res){
			$this->out(LOG_ERR,$dbhandle->ErrorMsg());
			throw new Exception("Cannot locate card");
		}elseif($res->EOF){
			$this->out(LOG_WARNING,'No rows for cc_card');
			return false;
		}
		$this->out(LOG_DEBUG, "Card found");
		$this->cardres= &$res;
		
		return true;
	}
	
	private function genContentElems(&$outstream, &$resr, array $crd, $nnum=NULL){
		$n='';
		$line='';
		if (!empty($nnum))
			$n='_'.$nnum.'_';
		while($row = $resr->fetchRow()){
			$line='<'.$row['name'].$n.' ';
			if ($row['options']&0x01)
				$line .='ua="rw" ';
			else
				$line .='ua="na"';
			$line.='> ';
			$line .= str_alparams($row['valuef'],$crd);
			$line .='</'.$row['name'].$n.">\n";
			fwrite($outstream,$line);
		}
	
	}
	
	public function genContent(&$outstream){
		fwrite($outstream, "<flat-profile>\n");
		fwrite($outstream,"\t<!-- Generated content -->\n\n");
		$dbhandle = $this->dbhandle;
		$passed_gen = false;
		
		if ($this->args['firsttime'])
			$ftc = '';
		else
			$ftc = ' AND (options & 02 = 0 )';
		
		$unquery= "SELECT DISTINCT * FROM provision_group ".
			" WHERE categ = %2 " .
			" AND model = %1 AND options = 0 ;";
			
		$numquery= "SELECT DISTINCT * FROM provision_group ".
			" WHERE categ = %2 " .
			" AND model = %1 AND options = 1 ;";

		while ($cardrow = $this->cardres->fetchRow()){
			// find the unnumbered parameters:
			if (!$passed_gen){
				$qry = str_dbparams($dbhandle,$unquery,array($cardrow['devmodel'],$this->confname));
				$this->out(LOG_DEBUG,"Query: $qry");
				$gres=$dbhandle->Execute($qry);
				if (!$gres){
					$this->out(LOG_ERR,$this->dbhandle->ErrorMsg());
					throw new Exception("Cannot locate provision");
				}elseif($gres->EOF){
					$this->out(LOG_DEBUG,'No rows for provision groups');
				}else
					while ($grprow= $gres->fetchRow()){
					$qry = str_dbparams($this->dbhandle,"SELECT * FROM provisions ".
						"WHERE grp_id = %#1 AND (options & 16 = 0) $ftc ORDER BY metric;",
						array($grprow['id']));
					$this->out(LOG_DEBUG,"Query: $qry");
					$pres=$this->dbhandle->Execute($qry);
					if (!$pres){
						$this->out(LOG_ERR,$this->dbhandle->ErrorMsg());
						throw new Exception("Cannot locate provision");
					}elseif(!$itemres->EOF){
						if (!$grphead)
							fwrite($outstream,"\t<!-- ".$grprow['sub_name']." -->\n");
						$this->genContentElems($outstream,$pres,$cardrow);
						$passed_gen = true;
						$grphead=true;
					}
				}
			}
			
			// Query again for numbered groups:
			$qry = str_dbparams($dbhandle,$numquery,array($cardrow['devmodel'],$this->confname));
			$this->out(LOG_DEBUG,"Query: $qry");
			$gres=$dbhandle->Execute($qry);
			if (!$gres){
				$this->out(LOG_ERR,$this->dbhandle->ErrorMsg());
				throw new Exception("Cannot locate provision");
			}elseif($gres->EOF){
				$this->out(LOG_DEBUG,'No rows for provision groups');
			}else
				while ($grprow= $gres->fetchRow()){
				$grphead = false;
				
				if (empty($cardrow['provi_num']))
					continue;
				// And one for the numbered params
				$qry = str_dbparams($this->dbhandle,"SELECT * FROM provisions ".
					"WHERE grp_id = %#1 $ftc ORDER BY metric;",
					array($grprow['id']));
				$this->out(LOG_DEBUG,"Query: $qry");
				
				$pres=$this->dbhandle->Execute($qry);
				if (!$pres){
					$this->out(LOG_ERR,$this->dbhandle->ErrorMsg());
					throw new Exception("Cannot locate provision");
				}elseif(!$itemres->EOF){
					if (!$grphead)
						fwrite($outstream,"\t<!-- ".$grprow['sub_name']." -->\n");
					$this->genContentElems($outstream,$pres,$cardrow,$cardrow['provi_num']);
					$grphead=true;
				}

			}
			
		}
		
		fwrite($outstream,"\n</flat-profile>\n");
	}

};

?>
