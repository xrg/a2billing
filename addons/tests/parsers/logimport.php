<?php
require_once("lib/Provi/Class.Import.inc.php");

class SyslogImport extends ImportEngine {
	protected $dbh;
	protected $fascility;
	protected $cur_par;
	protected $last_host;
	protected $last_date;
	
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
	
	protected function reg_msg(){
		$this->out(LOG_DEBUG,"Found msg:". $this->cur_par);
	}
	
	protected function reg_par($date, $host){
		$this->cur_par = "";
	}
	protected function reg_content($str){
		$this->cur_par .= $str."\n";
	
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
			elseif (preg_match('/^(.{15}) (\S+) (\S+): (.*)$/',$line2,$tokens)>0){
				// tokens now are 1:date 2:host 3: fascility 4: message
				if ($tokens[3] != $this->fascility)
					continue;
				if (($tokens[1] != $this->last_date) || ($tokens[2] != $this->last_host)){
					$this->reg_msg($this->cur_par);
					$this->reg_par($tokens[1], $tokens[2]);
					$this->last_date=$tokens[1];
					$this->last_host=$tokens[2];
					}
				if (!$this->reg_content($tokens[4]))
					$this->out(LOG_WARNING,"Malformed line: $line2");
			}elseif ($this->reg_special2($line2)==false)
				$this->out(LOG_WARNING,"Unknown line: $line2");
		}
		if ($this->cur_par)
			$this->reg_msg();
		}catch (Exception $ex){
			$this->out(LOG_ERR,$ex->getMessage());
			return false;
		}
		return true;
	}
};

class SensorsLogImport extends SyslogImport{
	public $args;
	protected $hosts_cache= array();
	
	function SensorsLogImport(){
		$this->fascility = "sensord";
	}
	public function Init(array $args){
		parent::Init($args);
		if (!isset($args['db']))
			throw new Exception("Please provide db in args!");
		$this->dbh = $args['db'];
	}
	function db_fetchone($qry,$parms = NULL){
		if ($parms)
			$res= $this->dbh->Execute(str_dbparams($this->dbh,$qry,$parms));
		else
			$res = $this->dbh->Execute($qry);
		
		if (!$res){
			$this->out(LOG_ERR,"Qry failed: $qry (".implode(', ',$parms).')');
			$this->out(LOG_ERR,$this->dbh->ErrorMsg());
			throw new Exception("Query failed: $qry");
		}
		$row =$res->FetchRow();
		if (!$row)
			throw new Exception("Query: \"$qry\": No results");
		return $row;
	}
	protected function find_host($host){
		if (!isset($this->hosts_cache[$host]))
			$hosts_cache[$host] = $this->db_fetchone("SELECT * FROM nm_system WHERE code = %1;",array($host));
		return $hosts_cache[$host]['id'];
	}
	protected function reg_msg(){
		$this->out(LOG_DEBUG,"Found msg:". print_r($this->cur_par,True));
	}
	
	protected function reg_par($date,$host){
		$this->cur_par = array('date'=>$date, 
			'sys' => $this->find_host($host),
			'chip'=> null, 'adapter'=>null, 'sensors'=> array());
	}
	protected function reg_content($str){
		//$this->cur_par .= $str."\n";
		$toks=array();
		if (preg_match('/^Chip: (\S+)$/',$str,$toks)>0)
			$this->cur_par['chip']=$toks[1];
		elseif (preg_match('/^Adapter: (.+)$/',$str,$toks)>0)
			$this->cur_par['adapter']=$toks[1];
		elseif (preg_match('/^ +-(.+)$/',$str,$toks)>0)
			return True;
		elseif (preg_match('/^ +(\S+): (\S+)$/',$str,$toks)>0)
			$this->cur_par['sensors'][$toks[1]]=$toks[2];
		else
			return False;
		return True;
	}

};

?>