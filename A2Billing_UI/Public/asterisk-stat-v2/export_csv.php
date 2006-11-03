<?php
include_once(dirname(__FILE__) . "../../lib/defines.php");
include_once(dirname(__FILE__) . "/lib/iam_csvdump.php");
include_once(dirname(__FILE__) . "/../../lib/module.access.php");


if (! has_rights (ACX_CALL_REPORT)){ 
	   Header ("HTTP/1.0 401 Unauthorized");
	   Header ("Location: ../PP_error.php?c=accessdenied");	   
	   die();	   
}

session_start();


  #  Set the parameters: SQL Query, hostname, databasename, dbuser and password                                       #
  #####################################################################################################################
  $dumpfile = new iam_csvdump;

  #  Call the CSV Dumping function and THAT'S IT!!!!  A file named dump.csv is sent to the user for download          #
  #####################################################################################################################

if (strlen($_SESSION["pr_sql_export"])<10){
	echo "ERROR CSV EXPORT";
}else{
	$dumpfile->dump($_SESSION["pr_sql_export"], "Report_cdr_". date("Y-m-d"), "csv", DBNAME, USER, PASS, HOST, DB_TYPE );
}

?>
