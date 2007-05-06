#!/usr/bin/php -q
<?php   
/***************************************************************************
 *            a2billing.php
 *
 *  Fri Nov 18 21:03:00 2005
 *  Copyright  2005  - Belaid Arezqui
 *  Email : areski [alt] gmail [_dot] com
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  The GNU GPL license can be found at :http://www.gnu.org/copyleft/gpl.html
 *
 ****************************************************************************/

    declare(ticks = 1);
    if (function_exists('pcntl_signal')) {
		   pcntl_signal(SIGHUP,  SIG_IGN);
    }

	error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		
	include (dirname(__FILE__)."/libs_a2billing/db_php_lib/Class.Table.php");
	include (dirname(__FILE__)."/libs_a2billing/Class.A2Billing.php");
	include (dirname(__FILE__)."/libs_a2billing/Class.RateEngine.php");	    
	include (dirname(__FILE__)."/libs_a2billing/phpagi_2_14/phpagi.php");
	include (dirname(__FILE__)."/libs_a2billing/phpagi_2_14/phpagi-asmanager.php");


	$charge_callback=0;
	$G_startime = time();
	$agi_date = "Release : 13 August 2006";
	$agi_version = "1.2.3 (BrainCoral)";
	
	if ($argc > 1 && ($argv[1] == '--version' || $argv[1] == '-v'))
	{
		echo "A2Billing - Version $agi_version - $agi_date\n";
		exit;
	}
	
	
	/********** 	 CREATE THE AGI INSTANCE + ANSWER THE CALL		**********/
	$agi = new AGI();
	
	
	if ($argc > 1 && is_numeric($argv[1]) && $argv[1] >= 0)
		$idconfig = $argv[1];
	else
		$idconfig = 1;
	$agi->verbose('line:'.__LINE__.' - '."IDCONFIG : $idconfig \n");
	
	
	if ($argc > 2 && strlen($argv[2]) > 0 && $argv[2] == 'did')			$mode = 'did';
	elseif ($argc > 2 && strlen($argv[2]) > 0 && $argv[2] == 'callback')		$mode = 'callback';
	elseif ($argc > 2 && strlen($argv[2]) > 0 && $argv[2] == 'cid-callback')	$mode = 'cid-callback';	
	elseif ($argc > 2 && strlen($argv[2]) > 0 && $argv[2] == 'all-callback')	$mode = 'all-callback';
	elseif ($argc > 2 && strlen($argv[2]) > 0 && $argv[2] == 'predictivedialer')	$mode = 'predictivedialer';
	else $mode = 'standard';
	$agi->verbose('line:'.__LINE__.' - '."MODE : $mode \n");
	
	// get the area code for the cid-callback & all-callback
	if ($argc > 3 && strlen($argv[3]) > 0) $caller_areacode = $argv[3];
	
	
	$A2B = new A2Billing();
	$A2B -> load_conf($agi, NULL, 0, $idconfig);
	
	$A2B -> CC_TESTING = isset($A2B->agiconfig['debugshell']) && $A2B->agiconfig['debugshell'];	
	
	// TEST DID
	// if ($A2B -> CC_TESTING) $mode = 'did';
	
	//-- Print header
	if ($A2B->agiconfig['debug']>=1) $agi->verbose ('AGI Request:');
	if ($A2B->agiconfig['debug']>=1) $agi->verbose (print_r($agi->request, true));
	
	
	/* GET THE AGI PARAMETER */
	$A2B -> get_agi_request_parameter ($agi);
	
	//$A2B -> accountcode = '2222222222';
	
	if (!$A2B -> DbConnect()){
		$agi-> stream_file('prepaid-final', '#');
		exit;
	}
	
	$instance_table = new Table();
	$A2B -> set_instance_table ($instance_table);
		

	//GET CURRENCIES FROM DATABASE 
	
	$QUERY =  "SELECT id,currency,name,value from cc_currencies order by id";
	$result = $A2B -> instance_table -> SQLExec ($A2B->DBHandle, $QUERY);
	
	/*
		$currencies_list['ADF'][1]="Andorran Franc";
		$currencies_list['ADF'][2]="0.1339";
		[ADF] => Array ( [1] => Andorran Franc (ADF), [2] => 0.1339 )
	*/

	if (is_array($result)){
		$num_cur = count($result);
		for ($i=0;$i<$num_cur;$i++){
			$currencies_list[$result[$i][1]] = array (1 => $result[$i][2], 2 => $result[$i][3]);
		}
	}

	//if ($A2B -> CC_TESTING) $agi->verbose (print_r($currencies_list,true));
	
	$RateEngine = new RateEngine();
	
	if ($A2B -> CC_TESTING) {
		$RateEngine->debug_st=1;
		$accountcode = '2222222222';
	}
	
	// ??? $A2B->callingcard_auto_setcallerid($agi); for other modes	
	    
	if ($mode == 'standard'){
	
		$A2B -> play_menulanguage ($agi);
		
		
		/*************************   PLAY INTRO MESSAGE   ************************/
		if (strlen($A2B->agiconfig['intro_prompt'])>0)
			$agi-> stream_file($A2B->agiconfig['intro_prompt'], '#');
		
		if ($A2B->agiconfig['answer_call']==1){
			if ($A2B->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '.'[ANSWER CALL]');
			$agi->answer();
			$status_channel=6;
		}else{
			if ($A2B->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '.'[NO ANSWER CALL]');
			$agi->exec('Progress');
			$status_channel=4;
		}
		
		/* WE START ;) */	
		$cia_res = $A2B -> callingcard_ivr_authenticate($agi);
		$A2B -> write_log("[TRY : callingcard_ivr_authenticate]");
		if ($cia_res==0){
			
			
			$A2B->callingcard_auto_setcallerid($agi);
			//$A2B -> write_log("[callingcard_acct_start_inuse]");
			//$A2B->callingcard_acct_start_inuse($agi,1);
			
			for ($i=0;$i< $A2B->agiconfig['number_try'] ;$i++){
					
					$RateEngine->Reinit();
					$A2B-> Reinit();
					
					
					
					
					$stat_channel = $agi->channel_status($A2B-> channel);
					$A2B -> write_log('[CHANNEL STATUS : '.$stat_channel["result"].' = '.$stat_channel["data"].']');
					if ($A2B->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '.'[CHANNEL STATUS : '.$stat_channel["result"].' = '.$stat_channel["data"].']');
					
					
					$A2B -> write_log("[CREDIT STATUS : ".$A2B-> credit."]");
					if ($A2B->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '."[CREDIT STATUS : ".$A2B-> credit."]");
					if ($A2B->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '."[CREDIT MIN_CREDIT_2CALL : ".$A2B->agiconfig['min_credit_2call']."]");
					
					
					
					//if ($stat_channel["status"]!= "6" && $stat_channel["status"]!= "1"){	
					if ($stat_channel["result"]!= $status_channel && ($A2B -> CC_TESTING!=1)){
						$A2B->callingcard_acct_start_inuse($agi,0); 
						$A2B -> write_log("[STOP - EXIT]", 0);
						exit();
					}
				
					
					if ($i>0)   $A2B-> uniqueid=$A2B-> uniqueid+ 1000000000 ;
					
					
					if( $A2B->credit < $A2B->agiconfig['min_credit_2call'] && $A2B -> typepaid==0) {
							
							// SAY TO THE CALLER THAT IT DEOSNT HAVE ENOUGH CREDIT TO MAKE A CALL							
							$prompt = "prepaid-no-enough-credit-stop";
							$agi-> stream_file($prompt, '#');
							if ($A2B->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '."[STOP STREAM FILE $prompt]");
							
							
							if (($A2B->agiconfig['notenoughcredit_cardnumber']==1) && (($i+1)< $A2B->agiconfig['number_try'])){
							
								$A2B->callingcard_acct_start_inuse($agi,0); // REMOVE THE INUSE
								$inuse_removed = 1; // FLAG TO KNOW THAT THE CARD ARENT IN USE
								
								$A2B->agiconfig['cid_enable']=0;
								$A2B->agiconfig['use_dnid']=0;
								$A2B->agiconfig['cid_auto_assign_card_to_cid']=0;								
								$A2B->accountcode='';
								$A2B->username='';
								$A2B-> ask_other_cardnumber	= 1;
								
								$cia_res = $A2B -> callingcard_ivr_authenticate($agi);
								$A2B -> write_log("[NOTENOUGHCREDIT_CARDNUMBER - TRY : callingcard_ivr_authenticate]");
								if ($cia_res!=0) break;
								
								$A2B -> write_log("[NOTENOUGHCREDIT_CARDNUMBER - callingcard_acct_start_inuse]");
								
								$A2B->callingcard_acct_start_inuse($agi,1);
								continue;
								
							}else{								
								
								$send_reminder = 1;
								if ($A2B->agiconfig['debug']>=2) $agi->verbose('line:'.__LINE__.' - '."[SET MAIL REMINDER - NOT ENOUGH CREDIT]");
								break;
							}
					}
					
					if ($agi->request['agi_extension']=='s'){
						$A2B->dnid = $agi->request['agi_dnid'];
					}else{
						$A2B->dnid = $agi->request['agi_extension'];
					}
					
					if ($A2B->agiconfig['sip_iax_friends']==1){
					
						if ($A2B->agiconfig['sip_iax_pstn_direct_call']==1){	

							if ($A2B->agiconfig['use_dnid']==1 && !in_array ($A2B->dnid, $A2B->agiconfig['no_auth_dnid']) && strlen($A2B->dnid)>2 && $i==0 ){
					
								$A2B -> destination = $A2B->dnid;
								
							}else{
			
								$prompt_enter_dest = $agi->agiconfig['file_conf_enter_destination'];
								$res_dtmf = $agi->get_data($prompt_enter_dest, 4000, 20);
								if ($A2B->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '."RES sip_iax_pstndirect_call DTMF : ".$res_dtmf ["result"]);																											
								$A2B-> destination = $res_dtmf ["result"];
							}
							
							if ( (strlen($A2B-> destination)>0) && (strlen($A2B->agiconfig['sip_iax_pstn_direct_call_prefix'])>0) && (strncmp($A2B->agiconfig['sip_iax_pstn_direct_call_prefix'], $A2B-> destination,strlen($A2B->agiconfig['sip_iax_pstn_direct_call_prefix']))==0) ){
								$A2B-> dnid = $A2B-> destination;
								$A2B-> sip_iax_buddy = $A2B->agiconfig['sip_iax_pstn_direct_call_prefix'];
								$A2B-> agiconfig['use_dnid'] = 1;
								if ($A2B->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '."SIP 1. IAX - dnid : ".$A2B->dnid." - ".strlen($A2B->agiconfig['sip_iax_pstn_direct_call_prefix']));
								$A2B->dnid = substr($A2B->dnid,strlen($A2B->agiconfig['sip_iax_pstn_direct_call_prefix']));
								if ($A2B->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '."SIP 2. IAX - dnid : ".$A2B->dnid);
							}elseif (strlen($A2B->destination)>0){
								$A2B->dnid = $A2B->destination;
								$A2B->agiconfig['use_dnid'] = 1;
								if ($A2B->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '."TRUNK - dnid : ".$A2B->dnid." (".$A2B->agiconfig['use_dnid'].")");
							}
						}else{
					
							//$res_dtmf = $agi->agi_exec("GET DATA prepaid-sipiax-press9 2000 1");
							$res_dtmf = $agi->get_data('prepaid-sipiax-press9', 2000, 1);
							if ($A2B->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '."RES SIP_IAX_FRIEND DTMF : ".$res_dtmf ["result"]);
																			
							$A2B-> sip_iax_buddy = $res_dtmf ["result"];
						}
					}		
					
					
					if ( isset($A2B-> sip_iax_buddy) && ($A2B-> sip_iax_buddy == $A2B->agiconfig['sip_iax_pstn_direct_call_prefix'])) {
							
							if ($A2B->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '.'CALL SIP_IAX_BUDDY');
							$A2B -> write_log("[CALL SIP_IAX_BUDDY]");
							$cia_res = $A2B-> call_sip_iax_buddy($agi, $RateEngine,$i);
							
					}else{
							
							if ($A2B-> callingcard_ivr_authorize($agi, $RateEngine, $i)==1){
										
									// PERFORM THE CALL	
									$result_callperf = $RateEngine->rate_engine_performcall ($agi, $A2B-> destination, $A2B);

									if (!$result_callperf) {
										$prompt="prepaid-dest-unreachable";
										//$agi->agi_exec("STREAM FILE $prompt #");
										$agi-> stream_file($prompt, '#');
									}
									
									// INSERT CDR  & UPDATE SYSTEM
									$RateEngine->rate_engine_updatesystem($A2B, $agi, $A2B-> destination);
									
									
									if ($A2B->agiconfig['say_balance_after_call']==1){		
										$A2B-> fct_say_balance ($agi, $A2B-> credit);
									}
									
									$A2B -> write_log("[callingcard_acct_stop]");									
																				
							}
					}
					$A2B->agiconfig['use_dnid']=0;
			}//END FOR
			if (!isset($inuse_removed) || $inuse_removed != 1) $A2B->callingcard_acct_start_inuse($agi,0); // REMOVE THE INUSE
		}else{
				$A2B -> write_log("[AUTHENTICATION FAILED (cia_res:".$cia_res.")]");
		}
			
			
		/****************  SAY GOODBYE   ***************/
		if ($A2B->agiconfig['say_goodbye']==1) $agi-> stream_file('prepaid-final', '#');
	
	}elseif ($mode == 'did'){
	
	
					if ($A2B->agiconfig['answer_call']==1){
						if ($A2B->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '.'[ANSWER CALL]');
						$agi->answer();
					}else{
						if ($A2B->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '.'[NO ANSWER CALL]');
					}
					// TODO
					// CRONT TO CHARGE MONTLY
					
					
					$RateEngine -> Reinit();
					$A2B -> Reinit();
					
					
					$mydnid = $agi->request['agi_extension'];
					if ($A2B -> CC_TESTING) $mydnid = '11111111';
					
					if (strlen($mydnid) > 0){
						
						$A2B -> write_log("[DID CALL - [CallerID=".$A2B->CallerID."]:[DID=".$mydnid."]");
						if ($A2B->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '."[DID CALL - [CallerID=".$A2B->CallerID."]:[DID=".$mydnid."]");
						
						
						
						$QUERY =  "SELECT cc_did.id, cc_did_destination.id, billingtype, tariff, destination,  voip_call, username".
							" FROM cc_did, cc_did_destination,  cc_card ".
							" WHERE id_cc_did=cc_did.id and cc_card.id=id_cc_card and cc_did_destination.activated=1  and cc_did.activated=1 and did='$mydnid' ".
							" AND cc_did.startingdate<= CURRENT_TIMESTAMP AND (cc_did.expirationdate > CURRENT_TIMESTAMP OR cc_did.expirationdate IS NULL OR LENGTH(cc_did.expirationdate)<5) ".
							" ORDER BY priority ASC";
						
						if ($A2B->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '.$QUERY);
														
						$result = $A2B -> instance_table -> SQLExec ($A2B->DBHandle, $QUERY);
						if ($A2B->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '.$result);
						
						if (is_array($result)){
							
							$A2B->call_did($agi, $RateEngine, $result);
						
						
						}
					}
					
	}elseif ($mode == 'cid-callback'){
	
		$agi->verbose('line:'.__LINE__.' - '.'[MODE : CALLERID-CALLBACK - '.$A2B->CallerID.']');
				
		
		// END
		$agi->hangup();
		
		
		// MAKE THE AUTHENTICATION ACCORDING TO THE CALLERID
		$A2B->agiconfig['cid_enable']=1;
		$A2B->agiconfig['cid_askpincode_ifnot_callerid']=0;
		
		if (strlen($A2B->CallerID)>1 && is_numeric($A2B->CallerID)){
		
			
			/* WE START ;) */	
			$cia_res = $A2B -> callingcard_ivr_authenticate($agi);
			$A2B -> write_log("[TRY : callingcard_ivr_authenticate]");
			if ($cia_res==0){
								
			
					$RateEngine = new RateEngine();
					// $RateEngine -> webui = 0;
					// LOOKUP RATE : FIND A RATE FOR THIS DESTINATION
					
					
					$A2B ->agiconfig['use_dnid']=1;
					$A2B ->agiconfig['say_timetocall']=0;
					if (substr($A2B->CallerID,0,1)=='0'){
						$A2B ->dnid = $A2B ->destination = $caller_areacode.substr($A2B->CallerID,1);
					}else{
						$A2B ->dnid = $A2B ->destination = $caller_areacode.$A2B->CallerID;
					}
					$agi->verbose('line:'.__LINE__.' - '.'[destination: - '.$A2B->destination.']');
							
					$resfindrate = $RateEngine->rate_engine_findrates($A2B, $A2B ->destination, $A2B ->tariff);
					//echo "resfindrate=$resfindrate";
					$agi->verbose('line:'.__LINE__.' - '.'[resfindrate: - '.$resfindrate.']');
					
					
					// IF FIND RATE
					if ($resfindrate!=0){				
						//$RateEngine -> debug_st	=1;
						$res_all_calcultimeout = $RateEngine->rate_engine_all_calcultimeout($A2B, $A2B->credit);
						//echo ("RES_ALL_CALCULTIMEOUT ::> $res_all_calcultimeout");
						//print_r($RateEngine-> ratecard_obj);
					
						if ($res_all_calcultimeout){
						
							// MAKE THE CALL
							if ($RateEngine -> ratecard_obj[0][34]!='-1'){
								$usetrunk=34; $usetrunk_failover=1;
							}else {
								$usetrunk=29; $usetrunk_failover=0;
							}
							
							$prefix		= $RateEngine -> ratecard_obj[0][$usetrunk+1];
							$tech 		= $RateEngine -> ratecard_obj[0][$usetrunk+2];
							$ipaddress 	= $RateEngine -> ratecard_obj[0][$usetrunk+3];
							$removeprefix 	= $RateEngine -> ratecard_obj[0][$usetrunk+4];
							$timeout	= $RateEngine -> ratecard_obj[0]['timeout'];	
							$failover_trunk	= $RateEngine -> ratecard_obj[0][40+$usetrunk_failover];
							$addparameter	= $RateEngine -> ratecard_obj[0][42+$usetrunk_failover];
			
							$destination = $A2B ->destination;
							if (strncmp($destination, $removeprefix, strlen($removeprefix)) == 0)
								$destination= substr($destination, strlen($removeprefix));
							
							
							
							$pos_dialingnumber = strpos($ipaddress, '%dialingnumber%' );
							
							$ipaddress = str_replace("%cardnumber%", $A2B->cardnumber, $ipaddress);
							$ipaddress = str_replace("%dialingnumber%", $prefix.$destination, $ipaddress);
							
							
							if ($pos_dialingnumber !== false){					   
								   $dialstr = "$tech/$ipaddress".$dialparams;
							}else{
								if ($A2B->agiconfig['switchdialcommand'] == 1){
									$dialstr = "$tech/$prefix$destination@$ipaddress".$dialparams;
								}else{
									$dialstr = "$tech/$ipaddress/$prefix$destination".$dialparams;
								}
							}	
							
							//ADDITIONAL PARAMETER 			%dialingnumber%,	%cardnumber%	
							if (strlen($addparameter)>0){
								$addparameter = str_replace("%cardnumber%", $A2B->cardnumber, $addparameter);
								$addparameter = str_replace("%dialingnumber%", $prefix.$destination, $addparameter);
								$dialstr .= $addparameter;
							}
							
							
							$as = new AGI_AsteriskManager();
							
							$agi->verbose('line:'.__LINE__.' - '.'[manager_host: - '.$A2B->config["webui"]['manager_host'].']');
							
							$res = $as->connect($A2B->config["webui"]['manager_host'],$A2B->config["webui"]['manager_username'],$A2B->config["webui"]['manager_secret']);

							if	($res){
								
								$channel= $dialstr;
								$exten = $A2B -> config["callback"]['extension'];
								$context = $A2B -> config["callback"]['context_callback'];
								$priority=1;
								$timeout = $A2B -> config["callback"]['timeout']*1000;
								$application='';
								$callerid=$A2B->CallerID;
								$account=$A2B->accountcode;

								$variable = "CALLED=".$A2B ->destination."|MODE=CID";
																
								sleep($A2B -> config["callback"]['sec_wait_before_callback']);
								$res = $as->Originate($channel, $exten, $context, $priority, $application, $data, $timeout, $callerid, $variable, $account, $async, $actionid);
								//$res=array();
								//$res["Response"]='Error';
								//print_r($resy);
								
								if($res["Response"]=='Error'){
									
									if (is_numeric($failover_trunk) && $failover_trunk>=0){
										//echo "failover_trunk=$failover_trunk";
										
										
										$QUERY = "SELECT trunkprefix, providertech, providerip, removeprefix FROM cc_trunk WHERE id_trunk='$failover_trunk'";
										$A2B->instance_table = new Table();
										$result = $A2B->instance_table -> SQLExec ($A2B -> DBHandle, $QUERY);
										
										//echo "QUERY=$QUERY";
										//print_r($result);
										
										if (is_array($result) && count($result)>0){
												
												//DO SELECT WITH THE FAILOVER_TRUNKID
												
												$prefix			= $result[0][0];
												$tech 			= $result[0][1];
												$ipaddress 		= $result[0][2];
												$removeprefix 	= $result[0][3];
												
												
												$pos_dialingnumber = strpos($ipaddress, '%dialingnumber%' );
							
												$ipaddress = str_replace("%cardnumber%", $A2B->cardnumber, $ipaddress);
												$ipaddress = str_replace("%dialingnumber%", $prefix.$destination, $ipaddress);
												
												
												if (strncmp($destination, $removeprefix, strlen($removeprefix)) == 0) $destination= substr($destination, strlen($removeprefix));
															
												$dialparams = str_replace("%timeout%", $timeout *1000, $A2B->agiconfig['dialcommand_param']);
										
												$A2B->agiconfig['switchdialcommand']=1;
												$dialparams='';
												
												if ($pos_dialingnumber !== false){					   
													   $dialstr = "$tech/$ipaddress".$dialparams;
												}else{
													if ($A2B->agiconfig['switchdialcommand'] == 1){
														$dialstr = "$tech/$prefix$destination@$ipaddress".$dialparams;
													}else{
														$dialstr = "$tech/$ipaddress/$prefix$destination".$dialparams;
													}
												}	
												
												
								
												//echo ("<br><b>DIAL $dialstr</b></br>");
												$channel= $dialstr;
												
												//echo "</br>Originate($channel, $exten, $context, $priority, $application, $data, $timeout, $callerid, $variable, $account, $async, $actionid)<br>-------</br>";
												$res = $as->Originate($channel, $exten, $context, $priority, $application, $data, $timeout, $callerid, $variable, $account, $async, $actionid);
								
												
												
										}
										
									}
								}
								
								
								
								// && DISCONNECTING
								$as->disconnect();
							
							
							}else{
									$error_msg= "Cannot connect to the asterisk manager!\nPlease check the manager configuration...";
									$A2B -> write_log("[CALLBACK-CALLERID : CALLED=".$A2B ->destination." | $error_msg]");
								
							}
						
						}else{
							$error_msg = 'Error : You don t have enough credit to call you back !!!';
							$A2B -> write_log("[CALLBACK-CALLERID : CALLED=".$A2B ->destination." | $error_msg]");
						}
					}else{
						$error_msg = 'Error : There is no route to call back your phonenumber !!!';
						$A2B -> write_log("[CALLBACK-CALLERID : CALLED=".$A2B ->destination." | $error_msg]");
				
					}
			
			
			
			}else{
				$A2B -> write_log("[CALLBACK-CALLERID : CALLED=".$A2B ->destination." | Authentication failed]");
			}
		
		}else{
			$A2B -> write_log("[CALLBACK-CALLERID : CALLERID=".$A2B->CallerID." | error callerid]");
		}	
	
	}elseif ($mode == 'all-callback'){
	
		$agi->verbose('line:'.__LINE__.' - '.'[MODE : ALL-CALLBACK - '.$A2B->CallerID.']');
		
		
		// END
		$agi->hangup();
		
		$A2B ->credit = 1000;
		$A2B ->tariff = $A2B -> config["callback"]['all_callback_tariff'];
		
		if (strlen($A2B->CallerID)>1 && is_numeric($A2B->CallerID)){
		
			/* WE START ;) */	
			//$cia_res = $A2B -> callingcard_ivr_authenticate($agi);			
			if ($cia_res==0){
								
			
					$RateEngine = new RateEngine();
					// $RateEngine -> webui = 0;
					// LOOKUP RATE : FIND A RATE FOR THIS DESTINATION
					
					
					$A2B ->agiconfig['use_dnid']=1;
					$A2B ->agiconfig['say_timetocall']=0;						
					$A2B ->dnid = $A2B ->destination = $caller_areacode.$A2B->CallerID;
							
					$resfindrate = $RateEngine->rate_engine_findrates($A2B, $A2B ->destination, $A2B ->tariff);
					
					// IF FIND RATE
					if ($resfindrate!=0){				
						//$RateEngine -> debug_st	=1;
						$res_all_calcultimeout = $RateEngine->rate_engine_all_calcultimeout($A2B, $A2B->credit);
						//echo ("RES_ALL_CALCULTIMEOUT ::> $res_all_calcultimeout");
						//print_r($RateEngine-> ratecard_obj);
					
						if ($res_all_calcultimeout){
						
							// MAKE THE CALL
							if ($RateEngine -> ratecard_obj[0][34]!='-1'){
								$usetrunk=34; $usetrunk_failover=1;
							} else {
								$usetrunk=29; $usetrunk_failover=0;
							}
							
							$prefix		= $RateEngine -> ratecard_obj[0][$usetrunk+1];
							$tech 		= $RateEngine -> ratecard_obj[0][$usetrunk+2];
							$ipaddress 	= $RateEngine -> ratecard_obj[0][$usetrunk+3];
							$removeprefix 	= $RateEngine -> ratecard_obj[0][$usetrunk+4];
							$timeout	= $RateEngine -> ratecard_obj[0]['timeout'];	
							$failover_trunk	= $RateEngine -> ratecard_obj[0][40+$usetrunk_failover];
							$addparameter	= $RateEngine -> ratecard_obj[0][42+$usetrunk_failover];
			
							$destination = $A2B ->destination;
							if (strncmp($destination, $removeprefix, strlen($removeprefix)) == 0) $destination= substr($destination, strlen($removeprefix));
							
							
							
							$pos_dialingnumber = strpos($ipaddress, '%dialingnumber%' );
							
							$ipaddress = str_replace("%cardnumber%", $A2B->cardnumber, $ipaddress);
							$ipaddress = str_replace("%dialingnumber%", $prefix.$destination, $ipaddress);
							
							
							if ($pos_dialingnumber !== false){
								   $dialstr = "$tech/$ipaddress".$dialparams;
							}else{
								if ($A2B->agiconfig['switchdialcommand'] == 1){
									$dialstr = "$tech/$prefix$destination@$ipaddress".$dialparams;
								}else{
									$dialstr = "$tech/$ipaddress/$prefix$destination".$dialparams;
								}
							}	
							
							//ADDITIONAL PARAMETER 			%dialingnumber%,	%cardnumber%	
							if (strlen($addparameter)>0){
								$addparameter = str_replace("%cardnumber%", $A2B->cardnumber, $addparameter);
								$addparameter = str_replace("%dialingnumber%", $prefix.$destination, $addparameter);
								$dialstr .= $addparameter;
							}
							
							
							$as = new AGI_AsteriskManager();
							
							
							$res = $as->connect($A2B->config["webui"]['manager_host'],$A2B->config["webui"]['manager_username'],$A2B->config["webui"]['manager_secret']);

							if ($res){
								
								$channel= $dialstr;
								$exten = $A2B -> config["callback"]['extension'];
								if ($argc > 4 && strlen($argv[4]) > 0) $exten = $argv[4];
								$context = $A2B -> config["callback"]['context_callback'];
								$priority=1;
								$timeout = $A2B -> config["callback"]['timeout']*1000;
								$application='';
								$callerid=$A2B->destination;
								$account=$A2B->accountcode;

								$variable = "CALLED=".$A2B ->destination."|MODE=ALL|TARIFF=".$A2B ->tariff;
								
								
								// sleep($A2B -> config["callback"]['sec_wait_before_callback']);
								$res = $as->Originate($channel, $exten, $context, $priority, $application, $data, $timeout, $callerid, $variable, $account, $async, $actionid);
								//$res=array();
								//$res["Response"]='Error';
								//print_r($resy);
								
								if($res["Response"]=='Error'){
									
									if (is_numeric($failover_trunk) && $failover_trunk>=0){
										//echo "failover_trunk=$failover_trunk";
										
										
										$QUERY = "SELECT trunkprefix, providertech, providerip, removeprefix FROM cc_trunk WHERE id_trunk='$failover_trunk'";
										$A2B->instance_table = new Table();
										$result = $A2B->instance_table -> SQLExec ($A2B -> DBHandle, $QUERY);
										
										//echo "QUERY=$QUERY";
										//print_r($result);
										
										if (is_array($result) && count($result)>0){
												
												//DO SELECT WITH THE FAILOVER_TRUNKID
												
												$prefix			= $result[0][0];
												$tech 			= $result[0][1];
												$ipaddress 		= $result[0][2];
												$removeprefix 	= $result[0][3];
												
												
												$pos_dialingnumber = strpos($ipaddress, '%dialingnumber%' );
							
												$ipaddress = str_replace("%cardnumber%", $A2B->cardnumber, $ipaddress);
												$ipaddress = str_replace("%dialingnumber%", $prefix.$destination, $ipaddress);
												
												
												if (strncmp($destination, $removeprefix, strlen($removeprefix)) == 0) $destination= substr($destination, strlen($removeprefix));
															
												$dialparams = str_replace("%timeout%", $timeout *1000, $A2B->agiconfig['dialcommand_param']);
										
												$A2B->agiconfig['switchdialcommand']=1;
												$dialparams='';
												
												if ($pos_dialingnumber !== false){					   
													   $dialstr = "$tech/$ipaddress".$dialparams;
												}else{
													if ($A2B->agiconfig['switchdialcommand'] == 1){
														$dialstr = "$tech/$prefix$destination@$ipaddress".$dialparams;
													}else{
														$dialstr = "$tech/$ipaddress/$prefix$destination".$dialparams;
													}
												}	
												
												
								
												//echo ("<br><b>DIAL $dialstr</b></br>");
												$channel= $dialstr;
												
												//echo "</br>Originate($channel, $exten, $context, $priority, $application, $data, $timeout, $callerid, $variable, $account, $async, $actionid)<br>-------</br>";
												$res = $as->Originate($channel, $exten, $context, $priority, $application, $data, $timeout, $callerid, $variable, $account, $async, $actionid);
								
												
												
										}
										
									}
								}
								
								
								
								// && DISCONNECTING	
								$as->disconnect();
							
							}else{
									$error_msg= "Cannot connect to the asterisk manager!\nPlease check the manager configuration...";
									$A2B -> write_log("[CALLBACK-CALLERID : CALLED=".$A2B ->destination." | $error_msg]");
								
							}
						
						
						}else{
							$error_msg = 'Error : You don t have enough credit to call you back !!!';
							$A2B -> write_log("[CALLBACK-CALLERID : CALLED=".$A2B ->destination." | $error_msg]");
						}
					}else{
						$error_msg = 'Error : There is no route to call back your phonenumber !!!';
						$A2B -> write_log("[CALLBACK-CALLERID : CALLED=".$A2B ->destination." | $error_msg]");
				
					}	
			
			
			
			}else{
				$A2B -> write_log("[CALLBACK-CALLERID : CALLED=".$A2B ->destination." | Authentication failed]");
			}
		
		}else{
			$A2B -> write_log("[CALLBACK-CALLERID : CALLERID=".$A2B->CallerID." | error callerid]");
		}	
	
	
	// MODE CALLBACK
	}elseif ($mode == 'callback'){
		
		$agi->verbose('line:'.__LINE__.' - '.'[MODE : CALLBACK]');

		
		
		if ($A2B->agiconfig['answer_call']==1){
			if ($A2B->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '.'[ANSWER CALL]');
			$agi->answer();
			$status_channel=6; 
		}else{
			if ($A2B->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '.'[NO ANSWER CALL]');
			$status_channel=4;
		}
		
		
		$called_party = $agi->get_variable("CALLED");
		$called_party = $called_party['data'];
		
		$calling_party = $agi->get_variable("CALLING");
		$calling_party = $calling_party['data'];
		
		$callback_mode = $agi->get_variable("MODE");
		$callback_mode = $callback_mode['data'];
		
		$callback_tariff = $agi->get_variable("TARIFF");
		$callback_tariff = $callback_tariff['data'];
		
		// |MODEFROM=ALL-CALLBACK|TARIFF=".$A2B ->tariff;
		
		if ($callback_mode=='CID'){  
			$A2B->agiconfig['use_dnid'] = 0;			
			
		}elseif ($callback_mode=='ALL'){  
			$A2B->agiconfig['use_dnid'] = 0;
		}else{
		
			// FOR THE WEB-CALLBACK
			$A2B->agiconfig['number_try'] =1;
			$A2B->agiconfig['use_dnid'] =1;
			$A2B->agiconfig['say_balance_after_auth']=0;
			$A2B->agiconfig['cid_enable'] =0;		
			$A2B->agiconfig['say_timetocall']=0;
		}
		
		$A2B -> write_log("[GET VARIABLE : CALLED=$called_party | CALLING=$calling_party | MODE=$callback_mode | TARIFF=$callback_tariff]");
		if ($A2B->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '."[GET VARIABLE : CALLED=$called_party | CALLING=$calling_party | MODE=$callback_mode | TARIFF=$callback_tariff]");
		
		
			
		
		/* WE START ;) */	
		$cia_res = $A2B -> callingcard_ivr_authenticate($agi);
		$A2B -> write_log("[TRY : callingcard_ivr_authenticate]");
		if ($cia_res==0){
			
			$A2B -> write_log("[callingcard_acct_start_inuse]");
			
			$A2B->callingcard_auto_setcallerid($agi);
			$A2B->callingcard_acct_start_inuse($agi,1);
			
			for ($i=0;$i< $A2B->agiconfig['number_try'] ;$i++){
						
			
					$RateEngine->Reinit();
					$A2B-> Reinit();
									
					
					
					$stat_channel = $agi->channel_status($A2B-> channel);
					$A2B -> write_log('[CHANNEL STATUS : '.$stat_channel["result"].' = '.$stat_channel["data"].']');
					if ($A2B->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '.'[CHANNEL STATUS : '.$stat_channel["result"].' = '.$stat_channel["data"].']');
					
					
					$A2B -> write_log("[CREDIT STATUS : ".$A2B-> credit."]");
					if ($A2B->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '."[CREDIT STATUS : ".$A2B-> credit."]");
					if ($A2B->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '."[CREDIT MIN_CREDIT_2CALL : ".$A2B->agiconfig['min_credit_2call']."]");
					
					
					
					//if ($stat_channel["status"]!= "6" && $stat_channel["status"]!= "1"){	
					if ($stat_channel["result"]!= $status_channel && ($A2B -> CC_TESTING!=1)){
						break;
						//$A2B->callingcard_acct_start_inuse($agi,0); 
						//$A2B -> write_log("[STOP - EXIT]", 0);
						//exit();
					}
					
					
					
					if( $A2B->credit < $A2B->agiconfig['min_credit_2call'] && $A2B -> typepaid==0) {
							
							// SAY TO THE CALLER THAT IT DEOSNT HAVE ENOUGH CREDIT TO MAKE A CALL							
							$prompt = "prepaid-no-enough-credit-stop";
							$agi-> stream_file($prompt, '#');
							if ($A2B->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '."[STOP STREAM FILE $prompt]");
							
					}
					
								
					
					if ($A2B-> callingcard_ivr_authorize($agi, $RateEngine, $i)==1){
										
										
																	
									// PERFORM THE CALL	
									$result_callperf = $RateEngine->rate_engine_performcall ($agi, $A2B-> destination, $A2B);

									if (!$result_callperf) {
										$prompt="prepaid-dest-unreachable";
										//$agi->agi_exec("STREAM FILE $prompt #");
										$agi-> stream_file($prompt, '#');
									}
									
									// INSERT CDR  & UPDATE SYSTEM
									$RateEngine->rate_engine_updatesystem($A2B, $agi, $A2B-> destination);
									
									
									if ($A2B->agiconfig['say_balance_after_call']==1){		
										$A2B-> fct_say_balance ($agi, $A2B-> credit);
									}
									
									
									$charge_callback = 1;
					
									$arr_save_a2billing['countrycode']= $A2B-> countrycode;
									$arr_save_a2billing['subcode']= $A2B-> subcode;
									$arr_save_a2billing['myprefix']= $A2B-> myprefix;
									$arr_save_a2billing['ipaddress']= $A2B-> ipaddress;
									$arr_save_a2billing['rate']= $A2B-> rate;
									$arr_save_a2billing['destination']= $A2B-> destination;
									$arr_save_a2billing['sip_iax_buddy']= $A2B-> sip_iax_buddy;
									
									$arr_save_rateengine['number_trunk']= $RateEngine-> number_trunk;
									$arr_save_rateengine['answeredtime']= $RateEngine-> answeredtime;
									$arr_save_rateengine['dialstatus']= $RateEngine-> dialstatus;
									$arr_save_rateengine['usedratecard']= $RateEngine-> usedratecard;
									$arr_save_rateengine['lastcost']= $RateEngine-> lastcost;
									
																		
									$A2B -> write_log("=======>>>>>       [callingcard_acct_stop 1] RateEngine->usedratecard=".$RateEngine->usedratecard);
																				
					}
							
				}//END FOR
			if (!isset($inuse_removed) || $inuse_removed != 1) $A2B->callingcard_acct_start_inuse($agi,0); // REMOVE THE INUSE
			
		}else{
				$A2B -> write_log("[AUTHENTICATION FAILED (cia_res:".$cia_res.")]");
		}


	}elseif ($mode == 'predictivedialer'){
		
		$agi->verbose('line:'.__LINE__.' - '.'[MODE : PREDICTIVEDIALER]');

		$A2B->agiconfig['number_try'] = 10;
		$A2B->agiconfig['use_dnid'] =1;
		$A2B->agiconfig['say_balance_after_auth']=0;
		$A2B->agiconfig['say_timetocall']=0;
		$A2B->agiconfig['cid_enable'] =0;
		
		
		
		$agi->answer();
		
		/* WE START ;) */	
		$cia_res = $A2B -> callingcard_ivr_authenticate($agi);
		
		if ($A2B->id_campaign<=0){
			$A2B -> write_log("[NOT CAMPAIGN ASSOCIATE AT THIS CARD]"); 
			$agi->verbose('line:'.__LINE__.' - '."[NOT CAMPAIGN ASSOCIATE AT THIS CARD]"); 
			$cia_res=-3;		
		}
		
		$A2B -> write_log("[TRY : callingcard_ivr_authenticate]");
		if ($cia_res==0){
			
			$A2B -> write_log("[callingcard_acct_start_inuse]");
			
			$A2B->callingcard_auto_setcallerid($agi);
			$A2B->callingcard_acct_start_inuse($agi,1);
			
			for ($i=0;$i< $A2B -> config["callback"]['nb_predictive_call'] ;$i++){
			
			
					$RateEngine->Reinit();
					$A2B-> Reinit();
										
					
					
					$stat_channel = $agi->channel_status($A2B-> channel);
					$A2B -> write_log('[CHANNEL STATUS : '.$stat_channel["result"].' = '.$stat_channel["data"].']');
					if ($A2B->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '.'[CHANNEL STATUS : '.$stat_channel["result"].' = '.$stat_channel["data"].']');
					
					
					$A2B -> write_log("[CREDIT STATUS : ".$A2B-> credit."]");
					if ($A2B->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '."[CREDIT STATUS : ".$A2B-> credit."]");
					if ($A2B->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '."[CREDIT MIN_CREDIT_2CALL : ".$A2B->agiconfig['min_credit_2call']."]");
					
					
					
					//if ($stat_channel["status"]!= "6" && $stat_channel["status"]!= "1"){	
					if ($stat_channel["result"]!= "6" && ($A2B -> CC_TESTING!=1)){
						$A2B->callingcard_acct_start_inuse($agi,0); 
						$A2B -> write_log("[STOP - EXIT]", 0);
						exit();
					}
					
					
					/*
					if( $A2B->credit < $A2B->agiconfig['min_credit_2call'] && $A2B -> typepaid==0) {
							
							// SAY TO THE CALLER THAT IT DEOSNT HAVE ENOUGH CREDIT TO MAKE A CALL							
							$prompt = "prepaid-no-enough-credit-stop";
							$agi-> stream_file($prompt, '#');
							if ($A2B->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '."[STOP STREAM FILE $prompt]");
							
					}*/
					
					

					$today_date = date("Y-m-d"); // 2005-12-24
					
					// DEFINE HERE THE NUMBER OF DAY THAT A PHONENUMBER FROM THE LIST WILL LAST BEFORE BE CALL AGAIN
					$days_compare = $A2B -> config["callback"]['nb_day_wait_before_retry'];					
			
					if ($A2B->config["database"]['dbtype'] == "postgres"){	
						$UNIX_TIMESTAMP = ""; $sql_limit = " LIMIT 5 OFFSET 0";	
						$date_clause = " last_attempt < date'$today_date'- INTERVAL '$days_compare DAY' ";
						// last_attempt < date'2005-12-24'- INTERVAL '1 DAY' 
						
					
					}else{		
						$UNIX_TIMESTAMP = "UNIX_TIMESTAMP"; 	$sql_limit = " LIMIT 0,5";	 
						$date_clause = " last_attempt < SUBDATE('$today_date',INTERVAL $days_compare DAY)";  
						// last_attempt < SUBDATE('2005-12-24',INTERVAL 1 DAY)
						// SELECT id, numbertodial, name  FROM cc_phonelist WHERE enable=1 AND num_trials_done<10 AND inuse=0 AND id_cc_campaign=1 
						// AND ( last_attempt < SUBDATE('2005-12-24',INTERVAL 1 DAY) OR num_trials_done=0) 
					}

					// $date_clause = " $UNIX_TIMESTAMP(last_attempt) < $UNIX_TIMESTAMP('".$today_date." 00:00:00') ";
					
					$QUERY = "SELECT id, numbertodial, name  FROM cc_phonelist WHERE enable=1 AND num_trials_done<10 AND inuse=0 AND id_cc_campaign=".$A2B->id_campaign.
							" AND ( $date_clause OR num_trials_done=0) ORDER BY last_attempt DESC $sql_limit";
							
						
					if ($A2B->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '.$QUERY);
													
					$result = $A2B -> instance_table -> SQLExec ($A2B->DBHandle, $QUERY);
					// if ($A2B->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '.$result);
					
					if (!is_array($result)){
						$A2B->callingcard_acct_start_inuse($agi,0); 
						$agi->verbose('line:'.__LINE__.' - '."[PREDICTIVEDIALER]:[NO MORE NUMBER TO CALL]");
						$A2B -> write_log("[STOP - EXIT]", 0);
						exit();
					}else{					
						$id_phonelist = $result[0][0];
						
						$QUERY = "UPDATE cc_phonelist SET inuse='1', id_cc_card='".$A2B->id_card."' WHERE id='".$id_phonelist."'";
						$update_result = $A2B -> instance_table -> SQLExec ($A2B->DBHandle, $QUERY, 0);						
					}
					
					$A2B->dnid = $A2B-> destination = $result[0][1];
					if ($A2B->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '."[PREDICTIVEDIALER]:[NUMBER TO DIAL -> ".$A2B-> destination."]");
					
					
					//cause $i is the try_num and in callingcard_ivr_authorize if the try_num is upper than 1 we prompt for destination 
					if ($A2B-> callingcard_ivr_authorize($agi, $RateEngine, 0)==1){ 
										
									// PERFORM THE CALL	
									$result_callperf = $RateEngine->rate_engine_performcall ($agi, $A2B-> destination, $A2B, 1);

									if (!$result_callperf) {
										$prompt="prepaid-dest-unreachable";
										//$agi->agi_exec("STREAM FILE $prompt #");
										$agi-> stream_file($prompt, '#');
									}
									
									// INSERT CDR  & UPDATE SYSTEM
									$RateEngine->rate_engine_updatesystem($A2B, $agi, $A2B-> destination);
									
									
									if ($A2B->agiconfig['say_balance_after_call']==1){		
										$A2B-> fct_say_balance ($agi, $A2B-> credit);
									}
									
									$A2B -> write_log("[callingcard_acct_stop]");
					}
					
					$QUERY = "UPDATE cc_phonelist SET inuse='0', last_attempt=now(),  num_trials_done=num_trials_done+1, secondusedreal=secondusedreal+".$RateEngine->answeredtime." WHERE id='".$id_phonelist."'";
					$update_result = $A2B -> instance_table -> SQLExec ($A2B->DBHandle, $QUERY, 0);
							
				}//END FOR
			if (!isset($inuse_removed) || $inuse_removed != 1) $A2B->callingcard_acct_start_inuse($agi,0); // REMOVE THE INUSE
		}else{
				$A2B -> write_log("[AUTHENTICATION FAILED (cia_res:".$cia_res.")]");
		}


	}


	
	if ($charge_callback){
	
				
				
				$A2B-> countrycode = $arr_save_a2billing['countrycode'];
				$A2B-> subcode = $arr_save_a2billing['subcode'];
				$A2B-> myprefix = $arr_save_a2billing['myprefix'];
				$A2B-> ipaddress = $arr_save_a2billing['ipaddress'];
				$A2B-> rate = $arr_save_a2billing['rate'];
				$A2B-> destination = $arr_save_a2billing['destination'];
				$A2B-> sip_iax_buddy = $arr_save_a2billing['sip_iax_buddy'];
				
				$RateEngine-> number_trunk = $arr_save_rateengine['number_trunk'];
				$RateEngine-> answeredtime = $arr_save_rateengine['answeredtime'];
				$RateEngine-> dialstatus = $arr_save_rateengine['dialstatus'];
				$RateEngine-> usedratecard = $arr_save_rateengine['usedratecard'];
				$RateEngine-> lastcost = $arr_save_rateengine['lastcost'];
				
				
				// MAKE THE BILLING FOR THE 1ST LEG
				if ($callback_mode=='ALL'){  
					//IF IT S ALL THE BILLING TO APPLY COME FROM $callback_tariff
					$A2B -> tariff = $callback_tariff;
				}
				
				
				if ($A2B->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '."[MAKE BILLING FOR THE 1ST LEG - TARIFF:".$A2B -> tariff.";CALLED=$called_party]");
				$A2B->agiconfig['use_dnid'] =1;
				$A2B ->dnid = $A2B ->destination = $called_party;
				
				
				$resfindrate = $RateEngine->rate_engine_findrates($A2B, $called_party, $A2B -> tariff);


				// IF FIND RATE
				if ($resfindrate!=0 && is_numeric($RateEngine->usedratecard)){														
						$res_all_calcultimeout = $RateEngine->rate_engine_all_calcultimeout($A2B, $A2B->credit);
						
												
						if ($res_all_calcultimeout){
							// SET CORRECTLY THE CALLTIME FOR THE 1st LEG
							$RateEngine -> answeredtime  = time() - $G_startime;
							$A2B -> write_log("[RateEngine -> answeredtime=".$RateEngine -> answeredtime."]");
							
							// INSERT CDR  & UPDATE SYSTEM
							$RateEngine->rate_engine_updatesystem($A2B, $agi, $A2B-> destination, 1, 0, 1);
						}else{										
							$A2B -> write_log("[ERROR - BILLING FOR THE 1ST LEG - rate_engine_all_calcultimeout: CALLED=$called_party]");
							if ($A2B->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '."[ERROR - BILLING FOR THE 1ST LEG - rate_engine_all_calcultimeout: CALLED=$called_party]");
						}
				}else{
					$A2B -> write_log("[ERROR - BILLING FOR THE 1ST LEG - rate_engine_findrates: CALLED=$called_party - RateEngine->usedratecard=".$RateEngine->usedratecard."]");
					if ($A2B->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '."[ERROR - BILLING FOR THE 1ST LEG - rate_engine_findrates: CALLED=$called_party - RateEngine->usedratecard=".$RateEngine->usedratecard."]");
				}
	
	}
	

	// END
	$agi->hangup();
	
	
	
	// SEND MAIL REMINDER WHEN CREDIT IS TOO LOW
	if (isset($send_reminder) && $send_reminder == 1 && $A2B->agiconfig['send_reminder'] == 1) {
	
		if (strlen($A2B -> cardholder_email) > 5){
			$QUERY = "SELECT mailtype, fromemail, fromname, subject, messagetext, messagehtml FROM cc_templatemail WHERE mailtype='reminder' ";
			
			$listtemplate = $A2B -> instance_table -> SQLExec ($A2B->DBHandle, $QUERY);
						
			if (is_array($listtemplate)){
			
											
				list($mailtype, $from, $fromname, $subject, $messagetext, $messagehtml) = $listtemplate [0];
				
				$messagetext = str_replace('$name', $A2B -> cardholder_lastname, $messagetext);
				$messagetext = str_replace('$card_gen', $A2B -> username, $messagetext);
				$messagetext = str_replace('$password', $A2B -> cardholder_uipass, $messagetext);
				$messagetext = str_replace('$min_credit', $A2B->agiconfig['min_credit_2call'], $messagetext);
				
				
				$em_headers  = "From: ".$fromname." <".$from.">\n";		
				$em_headers .= "Reply-To: ".$from."\n";
				$em_headers .= "Return-Path: ".$from."\n";
				$em_headers .= "X-Priority: 3\n";
				
				mail($A2B -> cardholder_email, $subject, $messagetext, $em_headers);
				
				/* USE PHPMAILER
				include (dirname(__FILE__)."/libs_a2billing/mail/class.phpmailer.php");
				//  change class.phpmailer.php - hostname
							
				$mail = new phpmailer();
				$mail -> From     = $from;
				$mail -> FromName = $fromname;
				//$mail -> IsSendmail();
				$mail -> IsSMTP();
				$mail -> Subject  = $subject;
				$mail -> Body    = $messagetext ; //$HTML;
				//$mail -> AltBody = $messagetext;	// Plain text body (for mail clients that cannot read 	HTML)
				//$mail -> ContentType = "multipart/alternative";
				$mail->AddAddress($A2B -> cardholder_email);				
				$mail->Send();
				*/
				$A2B -> write_log("[SEND-MAIL REMINDER]:[TO:".$A2B -> cardholder_email." - FROM:$from - SUBJECT:$subject]");
				
			}	
			
		}
	}
	
	
	$A2B -> write_log("[exit]",0);
	exit;		
                                   
								   
/************** THIS IS THE END :D ****************/
?>
