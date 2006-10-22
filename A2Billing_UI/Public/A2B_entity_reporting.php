<?php
include ("../lib/defines.php");
include ("../lib/module.access.php");
include ("../lib/Form/Class.FormHandler.inc.php");
include ("./form_data/FG_var_reporting.inc");


if (! has_rights (ACX_CUSTOMER)){ 
	   Header ("HTTP/1.0 401 Unauthorized");
	   Header ("Location: PP_error.php?c=accessdenied");	   
	   die();	   
}



/***********************************************************************************/

$HD_Form -> setDBHandler (DbConnect());
$HD_Form -> init();

if ($id!="" || !is_null($id)){
	$HD_Form -> FG_EDITION_CLAUSE = str_replace("%id", "$id", $HD_Form -> FG_EDITION_CLAUSE);
}


if (!isset($form_action))  $form_action="list"; //ask-add
if (!isset($action)) $action = $form_action;


$list = $HD_Form -> perform_action($form_action);



// #### HEADER SECTION
include("PP_header.php");




// #### TOP SECTION PAGE
$HD_Form -> create_toppage ($form_action);
$HD_Form -> create_search_form();

// #### CREATE FORM OR LIST
//$HD_Form -> CV_TOPVIEWER = "menu";
if (strlen($_GET["menu"])>0) $_SESSION["menu"] = $_GET["menu"];

$HD_Form -> create_form ($form_action, $list, $id=null) ;

$instance_table = new Table($HD_Form -> FG_TABLE_NAME, $HD_Form -> FG_COL_QUERY);

$clause=$HD_Form->FG_TABLE_CLAUSE;

if (strlen ($clause)>0)$clause="WHERE ".$clause;
$SQL = "select sum(sessiontime),sum(buyrate),sum(buycost),sum(count) from temp_tab ".$clause;

$TOTAL= $instance_table->SQLExec($HD_Form->DBHandle,$SQL,true);

?>
<br><br>
<table border="0" cellspacing="0" cellpadding="0"  width="95%">
<tbody><tr><td bgcolor="#000000">
	<!-- TOTAL -->
	<tr bgcolor="#600101">
		<TD aling="center" colspan="4" nowrap="nowrap"><font face="verdana" size="1" color="#ffffff"><b><?php echo gettext("TOTAL");?></b></font></TD>
		</TD>
	</tr>
	<tr bgcolor="c">
		<td align="center" nowrap="nowrap"><font face="verdana" size="1" color="#ffffff"><b><?php echo gettext("DURATION");?></b></font></td>
		<td align="center" nowrap="nowrap"><font face="verdana" size="1" color="#ffffff"><b><?php echo gettext("BUYRATE")?> </b></font></td>
		<td align="center" nowrap="nowrap"><font face="verdana" size="1" color="#ffffff"><b><?php echo gettext("BUYCOST")?></b></font></td>
		<td align="center" nowrap="nowrap"><font face="verdana" size="1" color="#ffffff"><b><?php echo gettext("NUMBER OF CALLS")?></b></font></td>   

	</tr>
	<tr bgcolor="#600101">
		<td align="center" nowrap="nowrap"><font face="verdana" size="1" color="#ffffff"><b><?php echo $TOTAL[0][0];?></b></font></td>
		<td align="center" nowrap="nowrap"><font face="verdana" size="1" color="#ffffff"><b><?php display_2bill($TOTAL[0][1])?> </b></font></td>
		<td align="center" nowrap="nowrap"><font face="verdana" size="1" color="#ffffff"><b><?php display_2bill($TOTAL[0][2]) ?> </b></font></td>
		<td align="center" nowrap="nowrap"><font face="verdana" size="1" color="#ffffff"><b><?php echo $TOTAL[0][3]?></b></font></td>   

	</tr>
	<!-- FIN TOTAL -->

	  </tbody></table>
<?php
// #### FOOTER SECTION
include("PP_footer.php");
?>
