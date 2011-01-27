<?php
require_once("Class.A2Billing.inc.php");
/** Logger can be used to log different events happening in the application.
*/ 
class Logger
{
	var $do_debug = 0;
	
	//constructor
	function Logger()
	{
	
	}
	//Function insertLog
	// Inserts the Log into table
	function insertLog_Add($userID, $logLevel, $actionPerformed, $description, $tableName, $ipAddress, $pageName, $param_add_fields, $param_add_value)
	{
		$DB_Handle = A2Billing::DBHandle();
		$pageName = basename($pageName);
		$pageName    = array_shift(explode('?', $pageName));
		$sfields = explode(',', $param_add_fields);
		$svalues = explode(',', $param_add_value);
		$num_records = count($sfields);
		$spairs= array();
		for($num = 0; $num < $num_records; $num++)
			$spairs[] = $sfields[$num]."= ". $svalues[$num];
		$sdata = implode('|',$spairs);
		
		$res= $DB_Handle->Execute ("INSERT INTO cc_system_log (iduser, loglevel, action, description, tablename, pagename, ipaddress, data) ".
				" VALUES(?,?,?,?,?,?,?,?);",
				array($userID, $logLevel, $actionPerformed, $description, $tableName, $pageName, $ipAddress, $sdata));
		
		if (!$res) @syslog(LOG_WARNING,"Cannot log: ". $DB_Handle->ErrorMsg());
	}
	
	function insertLog_Update($userID, $logLevel, $actionPerformed, $description, $tableName, $ipAddress, $pageName, $param_update)
	{
		$DB_Handle = A2Billing::DBHandle();
		$pageName = basename($pageName);
		$pageName = array_shift(explode('?', $pageName));
		
		$res= $DB_Handle->Execute ("INSERT INTO cc_system_log (iduser, loglevel, action, description, tablename, pagename, ipaddress, data) ".
				" VALUES(?,?,?,?,?,?,?,?);",
				array($userID, $logLevel, $actionPerformed, $description, $tableName, $pageName, $ipAddress, $param_update));
		
		if (!$res) @syslog(LOG_WARNING,"Cannot log: ". $DB_Handle->ErrorMsg());
	}	
	function insertLog($userID, $logLevel, $actionPerformed, $description, $tableName, $ipAddress, $pageName, $data='')
	{
		$DB_Handle = A2Billing::DBHandle();
		$pageName = basename($pageName);
		$pageArray = explode('?', $pageName);
		$pageName = array_shift($pageArray);
		
		$res= $DB_Handle->Execute ("INSERT INTO cc_system_log (iduser, loglevel, action, description, tablename, pagename, ipaddress, data) ".
				" VALUES(?,?,?,?,?,?,?,?);",
				array($userID, $logLevel, $actionPerformed, $description, $tableName, $pageName, $ipAddress, $data));
		
		if (!$res) @syslog(LOG_WARNING,"Cannot log: ". $DB_Handle->ErrorMsg());
	}
	
	//Funtion deleteLog
	//Delete the log from table
	function deleteLog($id = 0)
	{
		throw new Exception("Why delete from syslog?");
// 		$DB_Handle = A2Billing::DBHandle();
// 		$QUERY = "DELETE FROM cc_system_log WHERE id = ".$id;
// 		if ($this -> do_debug) echo $QUERY;		
// 		$table_log -> SQLExec($DB_Handle, $QUERY);
	}
}

?>