<?php

function fmt_minutes($sessiontime){
	$ret='';
	if ($sessiontime>=3600){
		$ret=sprintf('%dh ',intval($sessiontime/3600));
		$sessiontime%=3600;
	}
	//$ret .= sprintf("%2d",intval($sessiontime/60)).":".sprintf("%02d",intval($sessiontime%60));
	$ret .= sprintf("%2d:%02ds",intval($sessiontime/60),intval($sessiontime%60));
	return $ret;
}



function BoothsDom($action, $actb, $agent_clause = 'AND false'){
	global $FG_DEBUG;
	$dbhandle = &A2Billing::instance()->DBHandle();
	// Prepare the XML DOM structure
	$dom = new DOMDocument("1.0","utf-8");
	
		// $_SESSION["pr_login"];
	
	$dom_root = $dom->createElement("root");
	$dom->appendChild($dom_root);
	
	$dom_message= $dom->createElement("message");
	$dom_root->appendChild($dom_message);
	if (!empty($agent_clause))
		$aclause = ' AND ' .$agent_clause;
	else
		$aclause = '';

	$booth_states = array();
	$booth_states[0] = array(_("N/A"), _("Not available, no cards configured."));
	$booth_states[1] = array(_("Empty"), _("No customer attached."));
	$booth_states[2] = array(_("Idle"),_("Customer attached, inactive"));
	$booth_states[3] = array(_("Ready"),_("Waiting for calls"));
	$booth_states[4] = array(_("Active"),_("Calls made, charged"));
	$booth_states[5] = array(_("Disabled"),_("Disabled by the agent"));
	$booth_states[6] = array(_("Stopped"),_("Calls made, charged, stopped"));

	// Perform the SQL query
	$message = '';
	
	if (!empty($action)) {
		/* Here we handle all actions to the booths!
		 */
		$get_booth = -1;
		if (!empty($actb)){
			$get_booth= (integer) $actb;
			switch ($action) {
			case 'disable':
				$message= _("Booth disabled");
				break;
			case 'stop':
				//$DBHandle->debug = true;
				$res=$dbhandle->Execute("UPDATE cc_booth_v SET state = 2 WHERE id = ? $aclause;",
					 array($get_booth));
				
				if ($res)
					$message= _("Booth stopped"  );
				else {
					$message= _("Action failed:");
					//if ($FG_DEBUG)
					$message .=  $dbhandle->ErrorMsg();
				}
				break;
			case 'start':
				$res=$dbhandle->Execute("UPDATE cc_booth_v SET state = 3 WHERE id = ? $aclause;",
					array($get_booth));
				
				if ($res && $dbhandle->Affected_Rows())
					$message= _("Booth started"  );
				else {
					$message= _("Action failed:");
					$message = $message . $dbhandle->ErrorMsg();
					$message_class="msg_error";
				}
				break;
			case 'load_def':
				$res=$dbhandle->Execute("UPDATE cc_booth SET cur_card_id = def_card_id WHERE id = ? $aclause;",
					array($get_booth));
				
				if ($res && $dbhandle->Affected_Rows())
					$message= _("Booth started"  );
				else {
					$message= _("Action failed:");
					$message = $message . $dbhandle->ErrorMsg();
					$message_class="msg_error";
				}
				break;
			case 'load_reg':
				$res=$dbhandle->Execute("UPDATE cc_booth SET cur_card_id = ? WHERE id = ? $aclause;",
					array($_GET['card'],$get_booth));
				
				if ($res && $dbhandle->Affected_Rows())
					$message= _("Booth started"  );
				else {
					$message= _("Action failed:");
					$message .= $dbhandle->ErrorMsg();
					// $message .= "<br>Query: " . $query;
					$message_class="msg_error";
				}
				break;
			case 'refill':
				$rf = (float) $_GET['sum'];
				if ($rf <= 0.0 || $rf > 50.0 /* *-* AGENT_MAX_REFILL*/){
					$message= _("Invalid sum for refill");
					$message_class="msg_error";
				}else {
					$get_booth= (integer) $actb;
					
					$query = "INSERT INTO cc_agentrefill(agentid, boothid, credit, pay_type) ".
						"SELECT agentid, cc_booth.id, conv_currency_from(?,  cc_agent.currency), ".
							"(SELECT id FROM cc_paytypes WHERE preset = 'prepay') " .
						"FROM cc_booth, cc_agent WHERE cc_booth.id = ? AND cc_agent.id = cc_booth.agentid $aclause;";
					$res=$dbhandle->Execute( $query , array( $rf , $get_booth));
					 if ($res && $dbhandle->Affected_Rows()){
					 	$message = _("Credit added to booth");
					 	$message_class="msg_success";
					 } else{
						$message= _("Refill failed: do you have enough credit?");
						if ($FG_DEBUG)
							$message .= "<br>" . $dbhandle->ErrorMsg();
						if ($FG_DEBUG >2)
							$message .= " <br>QUERY= " . $query;
						$message_class="msg_error";
					}
					
				}
				break;
			case 'empty':
				$res=$dbhandle->Execute("UPDATE cc_booth SET cur_card_id = NULL WHERE id = ? $aclause;",
					array($get_booth));
				
				if ($res && $dbhandle->Affected_Rows())
					$message= _("Booth emptied"  );
				else {
					$message= _("Action failed:");
					$message .= $DBHandle->ErrorMsg();
					//$message .= "<br>Query: " . $query;
					$message_class="msg_error";
				}

				break;
			default:
				$message="Unknown request";
				$message_class="msg_error";
			}
		}else switch ($action){
		default:
			$message="Incorrect request";
		}
	}


	$QUERY="SELECT id, name, state, secs, format_currency(COALESCE(credit,0), currency) AS credit, in_now ";
	$QUERY.=" FROM cc_booth_v WHERE def_card_id IS NOT NULL $aclause ORDER BY id;";

	$res = $dbhandle->query($QUERY);

// 	$dom_message->appendChild($dom->createTextNode($QUERY));
// 	$dom_message->setAttribute("class","msg_errror");

	if (!$res){
		$message=_("Database query failed!");
		if ($FG_DEBUG)
			$message .= $dbhandle->ErrorMsg();
		if ($FG_DEBUG >2)
			$message .= '<br>' . htmlspecialchars($QUERY);
		$dom_message->appendChild($dom->createTextNode($message));
		$dom_message->setAttribute("class","msg_errror");
	}elseif ($res->EOF){
		$message=_("Database query failed!");
		if ($FG_DEBUG)
			$message .= "No rows returned!";
		$dom_message->appendChild($dom->createTextNode($message));
		$dom_message->setAttribute("class","msg_errror");
	} else {
		$dom_message->appendChild($dom->createTextNode($message));
		if (isset($message_class)) $dom_message->setAttribute("class",$message_class);

// 		if (!isset($currencies_list[strtoupper($customer_info [14])][2]) || !is_numeric($currencies_list[strtoupper($customer_info [14])][2])) $mycur = 1;
// 		else $mycur = $currencies_list[strtoupper($customer_info [14])][2];

		$buttons = array();
		$buttons['sta'] = false;
		$buttons['stp'] = false;
		$buttons['pay'] = false;
		$buttons['en'] = false;
		$buttons['dis'] = false;
		$buttons['unl'] = false;
		$buttons['ld'] = false;
		$buttons['lr'] = false;
		$buttons['emp'] = false;
		$buttons['ln'] = false;
		
		$num = $res->numRows();
		for ($i=0;$i<$num;$i++){
			$row=$res->fetchRow();
			$dom_booth=$dom->createElement("booth");
			$dom_root->appendChild($dom_booth);
			$dom_booth->setAttribute("id","booth_".$row['id']);
			
			$tmp=$dom->createElement("name");
			$name=$row['name'];
			if (!empty($row['in_now'])) $name .= ' (' . $row['in_now'] . ')';
			$tmp->appendChild($dom->createTextNode($name));
			$dom_booth->appendChild($tmp);
			
			
			$tmp=$dom->createElement("status");
			$row_state=$row['state'];
			if (($row_state<0) || ($row_state>6))
					$row_state=0;
			$tmp->appendChild($dom->createTextNode($booth_states[$row_state][0]));
			//$tmp->setAttribute("alt",$booth_states[$row_state][1]);
			$tmp->setAttribute("class","state".$row_state);
			$dom_booth->appendChild($tmp);
			
			$tmp=$dom->createElement("mins");
			$tmp->appendChild($dom->createTextNode(fmt_minutes($row['secs'])));
			$dom_booth->appendChild($tmp);
			
			$tmp=$dom->createElement("credit");
			$tmp->appendChild($dom->createTextNode($row['credit']));
			$dom_booth->appendChild($tmp);
			
			// switch off all buttons
			foreach(  $buttons as &$bu)
				$bu=false;
				
			$td_refill=false;
				// select the ones that will be visible
			switch ($row_state){
			case 0:
				break;
			case 1:
				$buttons["ld"]=true;
				$buttons["lr"]=true;
				//$buttons["ln"]=true;
				//$buttons["dis"]=true;
				break;
			case 2:
				$buttons["sta"]=true;
				$buttons["emp"]=true;
				//$buttons["lr"]=true;
				//$buttons["ld"]=true;
				$td_refill=true;
				break;
			case 3:
				$buttons["stp"]=true;
				$buttons['emp']=true;
				$td_refill=true;
				break;
			case 4:
				$buttons["pay"]=true;
				$buttons["stp"]=true;
				break;
			case 5:
				$buttons["en"]=true;
				break;
			case 6:
				$buttons["pay"]=true;
				$buttons["sta"]=true;
				$td_refill=true;
			}
			
			foreach ($buttons as $key => $bu){
				$tmp=$dom->createElement("button_".$key);
				$tmp->setAttribute("display",$bu?"inline":"none");
				$dom_booth->appendChild($tmp);
			}
			$tmp=$dom->createElement("refill");
			$tmp->setAttribute("display",$td_refill?"inline":"none");
			$dom_booth->appendChild($tmp);
		}
	}
	
	return $dom;
}
?>