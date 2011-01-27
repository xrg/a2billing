<?php
require_once ("./lib/defines.php");
require_once ("./lib/module.access.php");
require_once ("a2blib/Form.inc.php");
require_once ("a2blib/Form/Class.SqlActionForm.inc.php");
require_once ("a2blib/Class.HelpElem.inc.php");
require_once ("a2blib/Form/Class.SqlRefField.inc.php");
require_once ("a2blib/Class.DynConf.inc.php");

$menu_section='menu_servers';

class GenDplanActionForm extends SqlActionForm {
	public function PerformAction(){
		global $PAGE_ELEMS;
		$this->verifyRights();
		
		if ($this->action != 'true')
			return;
		
		$dbg_elem = new DbgElem();
		$dbhandle = $this->a2billing->DBHandle();
				
		if ($this->FG_DEBUG>0)
			array_unshift($this->pre_elems,$dbg_elem);
	
		$query = str_aldbparams($dbhandle,"SELECT * FROM static_dplan_v
			WHERE srvgrp = %#srvgrp ;",$this->_dirty_vars);
		
		$dbg_elem->content .= $query . "\n";
		
		$res = $dbhandle->Execute($query);
		
		if (!$res){
			$this->action = 'ask';
			$this->pre_elems[] = new ErrorElem(str_params($this->failureString,array(_("database error")),1));
			$dbg_elem->content.= $dbhandle->ErrorMsg() ."\n";
// 			throw new Exception( $err_str);
			return;
		}elseif ($this->expectRows && ($dbhandle->Affected_Rows()<1)){
			// No result rows: update clause didn't match
			$dbg_elem->content.= ".. EOF, no rows!\n";
			$dbg_elem->content.= $dbhandle->ErrorMsg() ."\n";
			$dbg_elem->obj = $dbhandle->Affected_Rows();
			$this->pre_elems[] = new ErrorElem(str_params($this->failureString,array(_("no rows")),1));
			$this->action = 'ask';
			return;
		} else {
			$dbg_elem->content.= "Success: Rows: ". $dbhandle->Affected_Rows() . "\n";
			$dbg_elem->content.= $dbhandle->ErrorMsg() ."\n";
			if (strlen($this->successString))
				$this->pre_elems[] = new StringElem(str_params($this->successString,
					array($dbhandle->Affected_Rows()),1));
			$this->action = 'display';
			$this->qryres = &$res;
		}
	
	}

	function RenderContent(){
		echo '<div class="content">'."\n";
		if (isset($this->contentString))
			echo $this->contentString;
		
		if (isset($this->qryres)){
			// now, process SIP conf files..
			$cur_srv = null;
			$cur_nplan = null;
			echo _("Generating extension_xx.ael files.<br>");
			$confdir= DynConf::GetCfg('global','peer_dir','/var/tmp');
			$dialparms= DynConf::GetCfg('peerconf','dialparams','|60|iRl(3600000)');
			$fd = null;
			while ($row = $this->qryres->fetchRow()){
				if ($cur_srv != $row['srvid']){
					$srvhost = str_replace(' ','_',strtolower($row['srv_host']));
					$cur_srv = $row['srvid'];
					echo "<br>\n"; // for previous line
					
					$filename = "$confdir/extensions_$srvhost.ael";
					echo "Trying: $filename: ";
					$fd = fopen($filename,"wb");
					if (!$fd){
						echo "<font style='color: red'>" . _("Could not open buddy file") ."</font><br>\n";
						continue;
						}
					if (fwrite($fd,"// Additional ael extensions for host " .$row['srv_host']."\n\n") ===false){
						echo "<font style='color: red'>" . _("Cannot write to file!") ."</font><br>\n";
						continue;
						fclose($fd); //abandon writing
						$fd = null;
					}
					
				}elseif (!$fd)
					continue;
				
				if (!isset($row['useralias']))
					continue;
				
				if ($cur_nplan != $row['nplan']) {
					if (!empty($cur_nplan)){
						$line = "};\n\n";
						if ($this->FG_DEBUG >2)
							echo nl2br(htmlspecialchars($line));
						fwrite($fd,$line);
					}
					$cur_nplan = $row['nplan'];
					$line="context dial-nplan-$cur_nplan {\n";
					if (!empty($row['npname']))
						$line .= "\t\t//Dial peers of numplan ". $row['npname']."\n";
					if ($this->FG_DEBUG >2)
						echo nl2br(htmlspecialchars($line));
					fwrite($fd,$line);
				}
				
				$line = "\t".$row['useralias']." => Dial(";
				if ($row['sipiax'] ==1 )
					$line .= "SIP";
				elseif ($row['sipiax'] ==2 )
					$line .= "IAX2";
				else {
					if ($this->FG_DEBUG)
						echo "Unknown sipiax ". $row['sipiax']."!<br>\n";
					continue;
				}
				$line .="/". $row['peername'].$dialparms.");\n";
				if ($this->FG_DEBUG >2)
					echo nl2br(htmlspecialchars($line));
				fwrite($fd,$line);
			}
			if (!empty($cur_nplan)){
				$line = "};\n\n";
				if ($this->FG_DEBUG >2)
					echo nl2br(htmlspecialchars($line));
				fwrite($fd,$line);
			}
			echo _("Success!")."<br>\n";
			if ($this->FG_DEBUG>2)
				echo "<br>";
			if ($fd) fclose($fd);
		}
		if (isset($this->afterContentString))
			echo $this->afterContentString;
		
		echo '</div>'."\n";
	}

};

HelpElem::DoHelp(_("Here you can generate the extension.ael files for the statically set users/servers."),'vcard.png');

$HD_Form= new GenDplanActionForm();
$HD_Form->checkRights(ACX_CUSTOMER);
$HD_Form->init();

$PAGE_ELEMS[] = &$HD_Form;

$HD_Form->model[] = new SqlRefField(_("Server Group"), "srvgrp","cc_server_group", "id", "name",_("Generate configs for all servers in group."));
//$HD_Form->model[] = new BoolField(_("Do IAX"),'do_iax',_("Use, generate IAX"));
//	end($HD_Form->model)->def_value='t';

require("PP_page.inc.php");

?>