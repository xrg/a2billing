<?php
require_once("Class.Import.inc.php");

class GenericIniImport extends ImportEngine {
	public $args;
	public $cur_header;
	
	public function getMimeTypes(){
		return array('text/plain');
	}
	
	public function Init(array $args){
		$this->args=$args;
	}
	
	/** Reset the internal state of the parser */
	protected function reset(){
		$cur_header=null;
	}
	
	protected function reg_header($hdr){
		$this->out(LOG_DEBUG,"Found header:". $hdr);
	}
	
	protected function reg_line($key,$val){
		$this->out(LOG_DEBUG,"Found line: $key=\"$val\"");
	}
	
	/** Allow special handling of other lines */
	protected function reg_special2($line2){
		return false;
	}

	public function parseContent(&$instream){
		$this->reset();
		/*We do parse the stream manually, rather than parse_ini_file,
		because we want control over the errors etc. */
		
		try {
		while($line=fgets($instream)){
			$line2=trim($line);
			$tokens=array();
			if(empty($line2))
				continue;

			if ($line2[0]==';' || $line2[0]=='#')
				continue;
			elseif (preg_match('/\[(\S+)\]/',$line2,$tokens)>0){
				$this->cur_header=$tokens[1];
				$this->reg_header($tokens[1]);
			}elseif (preg_match('/^(\S+)\s*=\s*(.*)$/',$line2,$tokens)>0){
				$this->reg_line($tokens[1],$tokens[2]);
			}elseif ($this->reg_special2($line2)==false)
				$this->out(LOG_WARNING,"Unknown line: $line2");
		}
		}catch (Exception $ex){
			$this->out(LOG_ERR,$ex->getMessage());
			return false;
		}
		return true;
	}
};

class ProviIniImport extends GenericIniImport {

};

class ConfigIniImport extends GenericIniImport {
	protected $dbhandle;
	
	public function Init(array $args){
		$this->args=$args;
		$this->dbhandle = A2Billing::DBHandle();
	}
	
	/** Reset the internal state of the parser */
	protected function reset(){
		parent::reset();
		$cur_header=null;
	}
	
	protected function reg_header($hdr){
		parent::reg_header($hdr);
	}
	
	protected function reg_line($key,$val){
		parent::reg_line($key,$val);
		if ($this->cur_header == false)
			return;
		$qry = str_dbparams($this->dbhandle,"INSERT INTO cc_sysconf(grp,name,val) ".
			"VALUES(%1,%2,%3);",
			array($this->cur_header,$key,$val));
		$res= $this->dbhandle->Execute($qry);
		if(!$res){
			$this->out(LOG_ERR,$this->dbhandle->ErrorMsg());
			throw new Exception('Cannot insert into database.');
		}elseif($this->dbhandle->Affected_Rows()!=1){
			$this->out(LOG_ERR,"No rows inserted!");
		}
		return true;
	}
	
	/** Allow special handling of other lines */
	protected function reg_special2($line2){
		$tokens=array();
		if (preg_match('/\[auto (.+)\s*\]/',$line2,$tokens)>0){
			$this->out(LOG_DEBUG,"Found auto header:".$tokens[1]);
			$qry=str_dbparams($this->dbhandle,'SELECT DISTINCT grp FROM cc_sysconf WHERE grp LIKE %1 ;',
				array($tokens[1].'%'));
			$this->out(LOG_DEBUG,$qry);
			$rows=$this->dbhandle->GetAll($qry);
			if($rows===false){
				$this->out(LOG_ERR,$this->dbhandle->ErrorMsg());
				throw new Exception('Cannot query database');
			}
			for ($i=1;$i<100;$i++)
				if(!in_array(array(grp=> $tokens[1].$i),$rows)){
					$this->cur_header=$tokens[1].$i;
					$this->out(LOG_DEBUG,"Will use ".$this->cur_header);
					return true;
				}
			$this->out(LOG_WARNING,"Cannot find useful group for ".$tokens[1]."%x");
			$this->cur_header=false; //set it so that we skip the section!
			return true;
		}
		return false;
	}

};
?>