<?php
require_once ("./lib/defines.php");
require_once ("./lib/module.access.php");
require_once (DIR_COMMON."Form.inc.php");
require_once (DIR_COMMON."Form/Class.SqlActionForm.inc.php");
require_once (DIR_COMMON."Class.HelpElem.inc.php");
require_once (DIR_COMMON."Form/Class.SqlRefField.inc.php");
require_once (DIR_COMMON."Class.DynConf.inc.php");

$menu_section='menu_servers';

class GenPeerActionForm extends SqlActionForm {
	public function PerformAction(){
		global $PAGE_ELEMS;
		$this->verifyRights();
		
		if ($this->action != 'true')
			return;
		
		$dbg_elem = new DbgElem();
		$dbhandle = $this->a2billing->DBHandle();
				
		if ($this->FG_DEBUG>0)
			array_unshift($this->pre_elems,$dbg_elem);
	
		if ($this->getpost_single('reset') == 't') {
			$query = str_aldbparams($dbhandle,"SELECT cc_a2b_server.id AS srvid, 
				sip_update_static_peers(cc_agent.id,cc_a2b_server.id,%do_sip,%do_iax) AS foo 
				FROM cc_a2b_server, cc_agent 
				WHERE cc_a2b_server.grp = %#srvgrp AND cc_agent.id = %#agentid;",$this->_dirty_vars);
			
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
			}
		} //reset
		if ($this->getpost_single('do_sip')=='t'){
			$query = str_aldbparams($dbhandle,"SELECT cc_a2b_server.id AS srvid, cc_a2b_server.host AS srv_host,
				peer.*
				FROM cc_a2b_server, static_sip_peers AS peer
				WHERE cc_a2b_server.grp = %#srvgrp 
				  AND cc_a2b_server.id = peer.srvid ORDER BY cc_a2b_server.id, peer.name ;",$this->_dirty_vars);
			
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
				$this->sip_qryres = &$res;
			}
		
		}
	}

	function RenderContent(){
		echo '<div class="content">'."\n";
		if (isset($this->contentString))
			echo $this->contentString;
		
		if (isset($this->sip_qryres)){
			// now, process SIP conf files..
			$cur_srv = null;
			echo _("Generating sip_xx.conf files.<br>");
			$confdir= DynConf::GetCfg('global','peer_dir','/var/tmp');
			
			$fd = null;
			while ($row = $this->sip_qryres->fetchRow()){
				if ($cur_srv != $row['srvid']){
					$srvhost = str_replace(' ','_',strtolower($row['srv_host']));
					$cur_srv = $row['srvid'];
					echo "<br>\n"; // for previous line
					
					$filename = "$confdir/sip_$srvhost.conf";
					echo "Trying: $filename: ";
					$fd = fopen($filename,"wb");
					if (!$fd){
						echo "<font style='color: red'>" . _("Could not open buddy file") ."</font><br>\n";
						continue;
						}
					if (fwrite($fd,"; Additional peers for host " .$row['srv_host']."\n\n") ===false){
						echo "<font style='color: red'>" . _("Cannot write to file!") ."</font><br>\n";
						continue;
						fclose($fd); //abandon writing
						$fd = null;
					}
					
				}elseif (!$fd)
					continue;
				
				if (!isset($row['name']))
					continue;
				
				fwrite($fd,"\n[".$row['name']."]\n");
				if ($this->FG_DEBUG>2)
					echo  "<br>" .htmlspecialchars("[".$row['name']. "]")."<br>\n";
				foreach($row as $key => $val){
					if (in_array($key,array('srvid','srv_host','name','realtime_id')))
						continue;
					if (empty($val))
						continue;
					fwrite($fd,"$key=$val\n");
					if ($this->FG_DEBUG>2)
						echo htmlspecialchars("$key=$val")."<br>\n";
				}
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

HelpElem::DoHelp(_("Here you can generate the peer.conf files for the statically set users/servers."),'vcard.png');

$HD_Form= new GenPeerActionForm();
$HD_Form->checkRights(ACX_CUSTOMER);
$HD_Form->init();

$PAGE_ELEMS[] = &$HD_Form;

$HD_Form->model[] = new SqlRefField(_("Server Group"), "srvgrp","cc_server_group", "id", "name",_("Generate configs for all servers in group."));
$HD_Form->model[] = new BoolField(_("Reset instances"),'reset',_("If true, the db will also be reset with the corresponding cards/booths"));
$HD_Form->model[] = new SqlRefField(_("Agent id"), "agentid","cc_agent", "id", "name",_("Put all cards/booths of agent in conf."));
$HD_Form->model[] = new BoolField(_("Do SIP"),'do_sip',_("Use SIP, generate additional_sip.conf"));
	end($HD_Form->model)->def_value='t';
$HD_Form->model[] = new BoolField(_("Do IAX"),'do_iax',_("Use, generate IAX"));
	end($HD_Form->model)->def_value='t';

require("PP_page.inc.php");

?>