<?php
include ("../lib/defines.php");
require_once("../lib/iam_csvdump.php");
include ("../lib/module.access.php");

if (!has_rights (ACX_CALL_REPORT) && !has_rights (ACX_CUSTOMER)){
	   Header ("HTTP/1.0 401 Unauthorized");
	   Header ("Location: PP_error.php?c=accessdenied");
	   die();
}

getpost_ifset(array('var_export'));
getpost_ifset(array('var_export_type'));

if (strlen($var_export)==0)
{
     $var_export='pr_sql_export';
}

/*   DEBUG  *
echo "var_export = $var_export <br>";
echo "SESSION var_export =".$_SESSION[$var_export]."</br>";
echo "var_export_type = $var_export_type <br>";
exit; */

  #  Set the parameters: SQL Query, hostname, databasename, dbuser and password                                       #
  #####################################################################################################################
  $dumpfile = new iam_csvdump;

  #  Call the CSV Dumping function and THAT'S IT!!!!  A file named dump.csv is sent to the user for download          #
  #####################################################################################################################

if (strlen($_SESSION[$var_export])<10)
{
	echo gettext("ERROR CSV EXPORT");
}
else
{
    if(strcmp($var_export_type,"type_csv")==0)
    {
        $dumpfile->dump($_SESSION[$var_export], "Dump_". date("Y-m-d"), "csv", DBNAME, USER, PASS, HOST, DB_TYPE );
    }
    elseif(strcmp($var_export_type,"type_xml")==0)
    {
        $dumpfile->dump($_SESSION[$var_export], "Dump_". date("Y-m-d"), "xml", DBNAME, USER, PASS, HOST, DB_TYPE );
    }
}

?>
