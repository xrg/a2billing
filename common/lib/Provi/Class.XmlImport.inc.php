<?php
require_once('Class.Import.inc.php');

/** Class that can import from an XML stream

   \note Unfortunately, we have to load the entire file into a stream.
    Please reconsider that
*/

class XmlImport extends ImportEngine {
	public $args;
	
	public function getMimeTypes(){
		return array('text/plain');
	}
	
	public function Init(array $args){
		$this->args=$args;
	}
	
	public function parseContent(&$instream){
		try {
			$tmpstr = stream_get_contents($instream);
			if ($tmpstr ===FALSE)
				throw new Exception('Cannot read stream');
			
			$doc = DomDocument::loadXML($tmpstr,LIBXML_NONET);
			unset($tmpstr);
			
			
			return $this->importContent($doc);
		
		}catch (Exception $ex){
			$this->out(LOG_ERR,$ex->getMessage());
			return false;
		}
		return true;
	}
	
	/** Debugging implementation: dump tree */
	protected function importContent(DomDocument $doc){
		$this->out(LOG_DEBUG,$doc->saveXML());
	}	

};

class SpaXmlImport extends XmlImport {
	protected $dbhandle;
	
	public function Init(array $args){
		$this->args=$args;
		$this->dbhandle = A2Billing::DBHandle();
	}

	/** Inserts some provisioning group 
	   \return the id of the inserted record
	  */
	protected function getGroup( $confname, $name, $subname= NULL){
		$qry = str_dbparams($this->dbhandle,'INSERT INTO provision_group(categ,model,name, sub_name) '.
			'VALUES(%1,%2,%3,%!4) RETURNING id; ',
			array('spa-conf',$confname,$name, $subname));
			
		$res= $this->dbhandle->Execute($qry);
		if(!$res){
			$this->out(LOG_ERR,$this->dbhandle->ErrorMsg());
			throw new Exception('Cannot insert into database.');
		}elseif($res->EOF){
			$this->out(LOG_ERR,"No rows inserted!");
		}
		$row= $res->fetchRow();
		return $row['id'];
	}
	
	protected function importContent(DomDocument $doc) {
		$elem = $doc->documentElement;
		$grpid=NULL;
		if ($elem->tagName != 'flat-profile'){
			$this->out(LOG_ERR,'Invalid root element');
			return false;
		}
		$child = $elem->firstChild;
		$insqry = 'INSERT INTO provisions(grp_id, name, valuef) VALUES( ?, ?, ?);';
		while ($child !== NULL){
			switch($child->nodeType){
			case XML_TEXT_NODE:
				$val = trim($child->nodeValue);
				if (!empty($val))
				$this->out(LOG_DEBUG,"Got text: ".print_r($child->nodeValue,true));
				break;
			case XML_COMMENT_NODE:
				$txt=trim($child->data);
				if (preg_match('/options:/',$txt)>0)
					break;
				if (preg_match('/Title:/',$txt)>0)
					break;
				$this->out(LOG_DEBUG,"Got section: ". $txt);
				$grpid=$this->getGroup($this->args['confname'],'Spa conf',$txt);
				break;
			case XML_ELEMENT_NODE:
					// Create a 'generic' group, if none.
				if ($grpid ===NULL)
					$grpid=$this->getGroup($this->args['confname'],'Spa conf','');

				$this->out(LOG_DEBUG,"Got elem: ".$child->tagName .
					'= ' . trim($child->textContent));
				$res = $this->dbhandle->Execute($insqry,
					array($grpid,$child->tagName,trim($child->textContent)));
				if (!$res){
					$this->out(LOG_ERR,$this->dbhandle->ErrorMsg());
					throw new Exception('Cannot insert into database.');
				}elseif($this->dbhandle->Affected_Rows()!=1){
					$this->out(LOG_ERR,"No rows inserted!");
				}
				break;
			
			default:
				$this->out(LOG_ERR,'Unknown node type: '. $child->nodeType);
				return false;
			}
			$child=$child->nextSibling;
		}
		
		return true;
	}
};
?>