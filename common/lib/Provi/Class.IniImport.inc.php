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
			elseif (preg_match('/\[(.+)\]/',$line2,$tokens)>0){
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

?>