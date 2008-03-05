<?php

class AgentCreditAlm extends A2BAlarm {
	function ProcessAlarm(AlmInstance $inst){
		$dbhandle = A2Billing::DBHandle();
		global $verbose;
		
		if ($inst->ar_id) // we cannot handle previous instances
			return;

		$margin = $inst->alm_params['margin'];
		if (!isset($margin))
			$margin = 0.0;
		
		$qry = str_dbparams($dbhandle,"SELECT cc_agent.id, credit, name, locale, email, climit, cc_alarm_run.id AS ar_id,
				cc_alarm_run.status AS ar_status
			FROM cc_agent LEFT JOIN cc_alarm_run ON ( cc_alarm_run.dataid = cc_agent.id
				AND cc_alarm_run.alid = %#1) 
			WHERE (climit + credit ) < %#2 ;",
			array($inst->id, $margin));
		if ($verbose >2)
			echo "Query: " .$qry."\n";
		$res= $dbhandle->Execute($qry);
		if (!$res){
			echo $dbhandle->ErrorMsg() ."\n";
		}else if ($res->EOF){
			if ($verbose >2)
				echo "All agents have credit.\n";
			$inst->Save(1);
			return;	
		}
		$neg_agents = array();
		while($row = $res->fetchRow()){
			if ($verbose>2)
				echo "Agent ". $row['name']. " is low on credit.\n";
			if(!empty($row['email']))
				$this->sendMail('agent-low-credit', $row['email'],$row['locale'],
					array(credit => $row['credit'], climit =>$row['climit']));
			$neg_agents[] = $row['name'].": ".$row['credit']."/".$row['climit'];
		}
		$this->sendSysMail('sys-agent-low-credit',$inst,array(low_agents => implode("\n",$neg_agents)));
		
		$inst->Save();
	}
};

// Register this alarm
$alarm_classes['agent-credit']= new AgentCreditAlm();

?>