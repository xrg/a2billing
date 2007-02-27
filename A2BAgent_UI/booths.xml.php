<?php 
/** Booths xml code:
    Copyright (C) 2006 P. Christeas <p_christeas@yahoo.com>
    */
// We must tell the mod_php to send the correct header..
header('Content-type: text/xml');

include ("lib/defines.php");
include ("lib/module.access.php");


if (! has_rights (ACX_ACCESS)){ 
	   header ("HTTP/1.0 401 Unauthorized");
	   die();
}

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
	$booth_states = array();
	$booth_states[0] = array(gettext("N/A"), gettext("Not available, no cards configured."));
	$booth_states[1] = array(gettext("Empty"), gettext("No customer attached."));
	$booth_states[2] = array(gettext("Idle"),gettext("Customer attached, inactive"));
	$booth_states[3] = array(gettext("Ready"),gettext("Waiting for calls"));
	$booth_states[4] = array(gettext("Active"),gettext("Calls made, charged"));
	$booth_states[5] = array(gettext("Disabled"),gettext("Disabled by the agent"));
	$booth_states[6] = array(gettext("Stopped"),gettext("Calls made, charged, stopped"));
	
	// Prepare the XML DOM structure
	$dom = new DomDocument("1.0","utf-8");
	
		// $_SESSION["pr_login"];
	
	$dom_root = $dom->createElement("root");
	$dom->appendChild($dom_root);
	
	$dom_message= $dom->createElement("message");
	$dom_root->appendChild($dom_message);

	// Perform the SQL query
	$DBHandle  = DbConnect();
	$message = '';
	
	if (isset($_GET["action"])) {
		/* Here we handle all actions to the booths!
		   NOTE that we always use the agent id *FROM THE SESSION*
		   as a security feature, so that a foreign agent can't mess
		   with us */
		$get_booth = -1;
		if (isset($_GET["actb"])){
			$get_booth= (integer) $_GET["actb"];
			switch ($_GET["action"]) {
			case 'disable':
				
				$message="Booth disabled";
				break;
			case 'stop':
				//$DBHandle->debug = true;
				$res=$DBHandle->Execute("UPDATE cc_booth_v SET state = 2 WHERE owner = " .
					$DBHandle->Quote($_SESSION['agent_id'] ) . 
					" AND id = " . $DBHandle->Quote($get_booth) . " ;" );
				
				if ($res)
					$message= gettext("Booth stopped"  );
				else {
					$message= gettext("Action failed:");
					$message = $message . $DBHandle->ErrorMsg();
				}
				break;
			case 'start':
				$res=$DBHandle->Execute("UPDATE cc_booth_v SET state = 3 WHERE owner = " .
					$DBHandle->Quote($_SESSION['agent_id'] ) . 
					" AND id = " . $DBHandle->Quote($get_booth) . " ;" );
				
				if ($res)
					$message= gettext("Booth started"  );
				else {
					$message= gettext("Action failed:");
					$message = $message . $DBHandle->ErrorMsg();
					$message_class="msg_error";
				}
				break;
			case 'load_def':
				$res=$DBHandle->Execute("UPDATE cc_booth SET cur_card_id = def_card_id WHERE agentid = " .
					$DBHandle->Quote($_SESSION['agent_id'] ) . 
					" AND id = " . $DBHandle->Quote($get_booth) . " ;" );
				
				if ($res)
					$message= gettext("Booth started"  );
				else {
					$message= gettext("Action failed:");
					$message = $message . $DBHandle->ErrorMsg();
					$message_class="msg_error";
				}
				break;
			case 'load_reg':
				$query = str_dbparams($DBHandle,"UPDATE cc_booth SET cur_card_id = %1 WHERE agentid = %2 AND id = %3;",
					array((integer) $_GET['card'],$_SESSION['agent_id'],$get_booth));
				$res=$DBHandle->Execute($query);
				
				if ($res)
					$message= gettext("Booth started"  );
				else {
					$message= gettext("Action failed:");
					$message .= $DBHandle->ErrorMsg();
					$message .= "<br>Query: " . $query;
					$message_class="msg_error";
				}
				break;
			case 'refill':
				$rf = (float) $_GET['sum'];
				if ($rf <= 0.0 || $rf > AGENT_MAX_REFILL){
					$message= gettext("Invalid sum for refill");
					$message_class="msg_error";
				}else {
					$get_booth= (integer) $_GET["actb"];
					$query= str_dbparams($DBHandle, "INSERT INTO cc_agentrefill(agentid, boothid, credit)" .
						"VALUES( %1, %2, conv_currency(%3, %4, %5)); ", 
						array($_SESSION['agent_id'] ,$get_booth, $rf, $_SESSION['currency'], strtoupper(BASE_CURRENCY))); ;
					
					$res=$DBHandle->Execute( $query );
					 if ($res){
					 	$message = gettext("Credit added to booth");
					 	$message_class="msg_success";
					 } else{
						$message= gettext("Refill failed: do you have enough credit?");
						
						/*$message = $message . $DBHandle->ErrorMsg();
						$message = $message . " <br>QUERY=" . $query;*/
						$message_class="msg_error";
					}
					
				}
				break;
			default:
				$message="Unknown request";
				$message_class="msg_error";
			}
		}else switch ($_GET["action"]){
		default:
			$message="Incorrect request";
		}
	}


	$base_currency=strtoupper(BASE_CURRENCY);
	if ($base_currency == NULL)
		$base_currency = 'USD';
	$QUERY="SELECT id, name, state, secs, format_currency(COALESCE(credit,0),'$base_currency',currency), in_now ";
	$QUERY.=" FROM cc_booth_v WHERE owner = " . trim($_SESSION["agent_id"]) . " ORDER BY id;";

	$res = $DBHandle -> query($QUERY);

// 	$dom_message->appendChild($dom->createTextNode($QUERY));
// 	$dom_message->setAttribute("class","msg_errror");

	if (!$res){
		$message=gettext("Database query failed!");
		//$message .= '<br>' . htmlspecialchars($QUERY);
		$dom_message->appendChild($dom->createTextNode($message));
		$dom_message->setAttribute("class","msg_errror");
	}else {
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
		$buttons['ln'] = false;
		
		$num = $res -> numRows();
		for ($i=0;$i<$num;$i++){
			$row=$res->fetchRow();
			$dom_booth=$dom->createElement("booth");
			$dom_root->appendChild($dom_booth);
			$dom_booth->setAttribute("id","booth_".$row[0]);
			
			$tmp=$dom->createElement("name");
			$name=$row[1];
			if (isset($row[5])) $name .= ' (' . $row[5] . ')';
			$tmp->appendChild($dom->createTextNode($name));
			$dom_booth->appendChild($tmp);
			
			
			$tmp=$dom->createElement("status");
			$row_state=$row[2];
			if (($row_state<0) || ($row_state>6))
					$row_state=0;
			$tmp->appendChild($dom->createTextNode($booth_states[$row_state][0]));
			//$tmp->setAttribute("alt",$booth_states[$row_state][1]);
			$tmp->setAttribute("class","state".$row_state);
			$dom_booth->appendChild($tmp);
			
			$tmp=$dom->createElement("mins");
			$tmp->appendChild($dom->createTextNode(fmt_minutes($row[3])));
			$dom_booth->appendChild($tmp);
			
			$tmp=$dom->createElement("credit");
			$tmp->appendChild($dom->createTextNode($row[4]));
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
				//$buttons["lr"]=true;
				//$buttons["ld"]=true;
				$td_refill=true;
				break;
			case 3:
				$buttons["stp"]=true;
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
				$tmp->setAttribute("display",$bu?"inline":"hidden");
				$dom_booth->appendChild($tmp);
			}
			$tmp=$dom->createElement("refill");
			$tmp->setAttribute("display",$td_refill?"inline":"none");
			$dom_booth->appendChild($tmp);
		}
	}
// Let ONLY this line produce any output!
echo $dom->saveXML();

if ($DBHandle)
	DbDisconnect($DBHandle);

?>
