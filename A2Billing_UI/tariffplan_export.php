<?php
include ("../lib/defines.php");
require_once("../lib/iam_csvdump.php");
include ("../lib/module.access.php");

if (!has_rights (ACX_RATECARD)){
	   Header ("HTTP/1.0 401 Unauthorized");
	   Header ("Location: PP_error.php?c=accessdenied");
	   die();
}

getpost_ifset(array('id_tp','export_style'));

  #  Set the parameters: SQL Query, hostname, databasename, dbuser and password                                       #
  #####################################################################################################################
  $dumpfile = new iam_csvdump;

  #  Call the CSV Dumping function and THAT'S IT!!!!  A file named dump.csv is sent to the user for download          #
  #####################################################################################################################

if (strlen($id_tp)<1)
{
	echo gettext("ERROR CSV EXPORT");
}
else
{
	$log = new Logger();
	$DBHandle=DbConnect();
	$export_fields = array('dialprefix', 'destination', 'rateinitial');
	$sql_str="ABORT;";
	switch ($export_style){
	case 'peer-full-csv':
		array_push($export_fields, 'buyrate', 'buyrateinitblock', 'buyrateincrement', 'rateinitial',
			'initblock', 'billingblock', 'connectcharge', 'disconnectcharge', 'stepchargea',
			'chargea', 'timechargea', 'billingblocka', 'stepchargeb', 'chargeb', 'timechargeb',
			'billingblockb', 'stepchargec', 'chargec', 'timechargec', 'billingblockc'); 
		$sql_str =str_dbparams($DBHandle,'SELECT '.implode(', ',$export_fields) . ' FROM cc_ratecard WHERE idtariffplan = %1;',
			array($id_tp));
		$log_str ="Ratecard #%0 exported in csv format, all fields in peer format";
		$myfileName="Ratecard_".$tp_id;
		$prolog ="# Export of tp #$id_tp\n";
		$prolog .="#fields: ".implode(';',$export_fields) ."\n";
		break;
	default:
		echo "Wrong export style:" . $export_style . "\n<br>\n";
		die();

	}
	$myfileName .=date("Y-m-d");
	$log->insertLog($_SESSION["admin_id"], 2, "FILE EXPORTED",str_params($log_str,array($id_tp,$export_style)),'', $_SERVER['REMOTE_ADDR'], $_SERVER['REQUEST_URI'],'');
	$dumpfile->sep =';';
	$dumpfile->prolog = $prolog;
	$dumpfile->dump($sql_str, $myfileName, "csv", DBNAME, USER, PASS, HOST, DB_TYPE );
	
	DBDisconnect($DBHandle);
  /*  if(strcmp($var_export_type,"type_csv")==0)
    {
		$myfileName = "Dump_". date("Y-m-d");
		$log -> insertLog($_SESSION["admin_id"], 2, "FILE EXPORTED", "A File in CSV Format is exported by User, File Name= ".$myfileName.".csv", '', $_SERVER['REMOTE_ADDR'], $_SERVER['REQUEST_URI'],'');
        $dumpfile->dump($_SESSION[$var_export], $myfileName, "csv", DBNAME, USER, PASS, HOST, DB_TYPE );
    }
    elseif(strcmp($var_export_type,"type_xml")==0)
    {
        $myfileName = "Dump_". date("Y-m-d");
		$log -> insertLog($_SESSION["admin_id"], 2, "FILE EXPORTED", "A File in XML Format is exported by User, File Name= ".$myfileName.".xml", '', $_SERVER['REMOTE_ADDR'], $_SERVER['REQUEST_URI'],'');
		$dumpfile->dump($_SESSION[$var_export], $myfileName, "xml", DBNAME, USER, PASS, HOST, DB_TYPE );
    }
	$log = null;*/
}

?>
