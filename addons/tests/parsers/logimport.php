<?php
require_once("lib/Provi/Class.Import.inc.php");

class NoDataException extends Exception {
};

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
					if ($this->cur_par)
						$this->reg_msg();
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
			throw $ex;
			return false;
		}
		return true;
	}
};

abstract class NMCache{
	protected $cache= array();
	protected $myid;
	protected $simp;
	protected $par;
	
	public function NMCache($myid, $par){
		$this->myid=$myid;
		$this->simp=$par->simp;
		$this->par=$par;
		
	}
	
	public function child($name){
		if(isset($this->cache[$name]))
			return $this->cache[$name];
		try {
			$res = $this->findChild($name);
			$this->cache[$name]=$res;
			return $res;
		}catch (NoDataException $ex){
			$res = $this->buildChild($name);
			$this->cache[$name]=$res;
			return $res;
		}
	}
	
	abstract protected function findChild($name);
	abstract protected function buildChild($name);
	
};

class HostsCache extends NMCache{
	
	public function HostsCache($simp){
		$this->myid=0;
		$this->simp=$simp;
		$this->par=null;
		
	}

	protected function findChild($name){
		$r=$this->simp->db_fetchone("SELECT id FROM nm.system WHERE code = %1;",
			array($name));
		return new SystemCache( $r['id'],$this);
	}
	
	protected function buildChild($name){
		throw new Exception("Cannot insert host \"$name\" into db!");
	}
};


class SystemCache extends NMCache{
	
	protected function findChild($name){
		$r=$this->simp->db_fetchone("SELECT id FROM nm.attr_node 
			WHERE sysid = %1 AND par_id IS NULL AND name = %2;",
			array($this->myid,$name));
		return new ChipCache( $r['id'],$this);
	}
	
	protected function buildChild($name){
		$r=$this->simp->db_fetchone("INSERT INTO nm.attr_node(sysid,clsid,atype,name) 
			VALUES(%1,1,'group',%2) RETURNING id;",
			array($this->myid,$name));
		return new ChipCache( $r['id'],$this);
	}
};

class ChipCache extends NMCache{
	
	protected function findChild($name){
		$r=$this->simp->db_fetchone("SELECT id FROM nm.attr_node 
			WHERE sysid = %1 AND par_id = %3 AND name = %2 AND atype = 'icreate';",
			array($this->par->myid,$name,$this->myid));
		return new SensorCache( $r['id'],$this);
	}
	
	protected function buildChild($name){
		$r=$this->simp->db_fetchone("INSERT INTO nm.attr_node(sysid,clsid,atype,name,par_id) 
			VALUES(%1,1,'icreate',%2,%3) RETURNING id;",
			array($this->par->myid,$name,$this->myid));
		return new SensorCache( $r['id'],$this);
	}
};

class SensorCache extends NMCache{
	protected $num=0;
	
	protected function findChild($name){
		throw new Exception("Nothing to find here");
	}
	
	protected function buildChild($name){
		throw new Exception("Nothing to build here");
	}
	
	public function regVal($date,$value){
		$res= $this->simp->db_fetchone("INSERT INTO nm.attr_float(par_id,tstamp,value) 
			VALUES(%1,%2,%3) RETURNING id;",
			array($this->myid,$date,$value));
	}
	
};
	

class SensorsLogImport extends SyslogImport{
	public $args;
	protected $hosts_cache;
	
	function SensorsLogImport(){
		$this->fascility = "sensord";
	}
	function print_cache(){
		$this->out(LOG_DEBUG,print_r($this->hosts_cache,true));
	}
	public function out($lvl,$str){
		parent::out($lvl,$str);
	}
	public function Init(array $args){
		parent::Init($args);
		if (!isset($args['db']))
			throw new Exception("Please provide db in args!");
		$this->dbh = $args['db'];
		$this->hosts_cache= new HostsCache($this);
		setlocale(LC_TIME,'C');
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
			throw new NoDataException("Query: \"$qry\": No results");
		return $row;
	}
	protected function reg_msg(){
		//Locate the chip:
		foreach ($this->cur_par['sensors'] as $key => $sens){
			//print_r($this->cur_par);
			$sobj = $this->cur_par['sys']->child($this->cur_par['chip']) ->
				child($key);
			//print_r($sobj);
			//echo "\n";
			$sobj->regVal($this->cur_par['date'],$sens);
		}
		
	}
	
	protected function reg_par($date,$host){
		$datea = strptime($date,'%b %e %T');
		// Guess the year:
		$cur_time = getdate();
		$datea['tm_year']=$cur_time['year'];
		$datea['tm_mon']+=1;
		$datea['tm_yday']+=1;
		if ($datea['tm_yday']>$cur_time['yday'])
			$datea['tm_year']-=1;
		$date2=sprintf('%d-%02d-%02d %d:%d:%d',$datea['tm_year'],$datea['tm_mon'],$datea['tm_mday'],
			$datea['tm_hour'],$datea['tm_min'],$datea['tm_sec']);
		$this->out(LOG_DEBUG,"Date $date: $date2");
		//throw new Exception("end here");
		$this->cur_par = array('date'=>$date2,
			'sys' => $this->hosts_cache->child($host),
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