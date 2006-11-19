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

	$booth_states = array();
	$booth_states[0] = array(gettext("N/A"), gettext("Not available, no cards configured."));
	$booth_states[1] = array(gettext("Empty"), gettext("No customer attached."));
	$booth_states[2] = array(gettext("Idle"),gettext("Customer attached, inactive"));
	$booth_states[3] = array(gettext("Ready"),gettext("Waiting for calls"));
	$booth_states[4] = array(gettext("Active"),gettext("Calls made, charged"));
	$booth_states[5] = array(gettext("Disabled"),gettext("Disabled by the agent"));

	// Prepare the XML DOM structure
	$dom = new DomDocument("1.0","utf-8");
	
		// $_SESSION["pr_login"];
	
	$dom_root = $dom->createElement("root");
	$dom->appendChild($dom_root);
	
	$dom_message= $dom->createElement("message");
	$dom_root->appendChild($dom_message);

// TODO: add the processing here..

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
				$res=$DBHandle->Execute("UPDATE cc_booth_v SET cur_card_id = def_card_id WHERE owner = " .
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
			case 'refill':
				$rf = (float) $_GET['sum'];
				if ($rf <= 0.0 || $rf > AGENT_MAX_REFILL){
					$message= gettext("Invalid sum for refill");
					$message_class="msg_error";
				}else {
					$get_booth= (integer) $_GET["actb"];
					$query="INSERT INTO cc_agentrefill(agentid, boothid, credit)" .
					"VALUES( " . $DBHandle->Quote($_SESSION['agent_id'] ) . ", ".
					 $DBHandle->Quote($get_booth) . ', '.
					 $DBHandle->Quote($rf) . ') ;' ;
					$res=$DBHandle->Execute( $query );
					 if ($res){
					 	$message = gettext("Credit added to booth");
					 	$message_class="msg_success";
					 } else{
						$message= gettext("Refill failed: ");
						$message = $message . $DBHandle->ErrorMsg();
						//$message = $message . " <br>QUERY=" . $query;
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


	$QUERY="SELECT id, name, state, mins, format_currency(COALESCE(credit,0),'EUR', 'EUR') FROM cc_booth_v WHERE owner = " . trim($_SESSION["agent_id"]) . " ORDER BY id;";

	$res = $DBHandle -> query($QUERY);

	if (!$res){
		$dom_message->appendChild($dom->createTextNode(gettext("Database query failed!")));
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
		
		$num = $res -> numRows();
		for ($i=0;$i<$num;$i++){
			$row=$res->fetchRow();
			$dom_booth=$dom->createElement("booth");
			$dom_root->appendChild($dom_booth);
			$dom_booth->setAttribute("id","booth_".$row[0]);
			
			$tmp=$dom->createElement("name");
			$tmp->appendChild($dom->createTextNode($row[1]));
			$dom_booth->appendChild($tmp);
			
			
			$tmp=$dom->createElement("status");
			$row_state=$row[2];
			if (($row_state<0) || ($row_state>5))
					$row_state=0;
			$tmp->appendChild($dom->createTextNode($booth_states[$row_state][0]));
			//$tmp->setAttribute("alt",$booth_states[$row_state][1]);
			$tmp->setAttribute("class","state".$row_state);
			$dom_booth->appendChild($tmp);
			
			$tmp=$dom->createElement("mins");
			$tmp->appendChild($dom->createTextNode($row[3]));
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
				//$buttons["dis"]=true;
				break;
			case 2:
				$buttons["sta"]=true;
				$buttons["lr"]=true;
				$buttons["ld"]=true;
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
			}
			
			foreach ($buttons as $key => $bu){
				$tmp=$dom->createElement("button_".$key);
				$tmp->setAttribute("display",$bu?"inline":"hidden");
				$dom_booth->appendChild($tmp);
			}
			$tmp=$dom->createElement("td_refill");
			$tmp->setAttribute("display",$td_refill?"inline":"hidden");
			$dom_booth->appendChild($tmp);
		}
	}
// Let ONLY this line produce any output!
echo $dom->saveXML();

if ($DBHandle)
	DbDisconnect($DBHandle);

?>
