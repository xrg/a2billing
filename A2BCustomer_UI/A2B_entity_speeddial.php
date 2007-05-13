<?php
include ("./lib/defines.php");
include ("./lib/module.access.php");
include ("./lib/Form/Class.FormHandler.inc.php");
include ("./form_data/FG_var_speeddial.inc");


if (! has_rights (ACX_ACCESS)){
	   Header ("HTTP/1.0 401 Unauthorized");
	   Header ("Location: PP_error.php?c=accessdenied");
	   die();
}

if (!$A2B->config["webcustomerui"]['speeddial']) exit();


getpost_ifset(array('destination', 'choose_speeddial', 'name'));

$HD_Form -> setDBHandler (DbConnect());
$HD_Form -> init();


/************************************  ADD SPEED DIAL  ***********************************************/
if (strlen($destination)>0  && is_numeric($choose_speeddial)){
		
		$FG_SPEEDDIAL_TABLE  = "cc_speeddial";		
		$FG_SPEEDDIAL_FIELDS = "speeddial";
		$instance_sub_table = new Table($FG_SPEEDDIAL_TABLE, $FG_SPEEDDIAL_FIELDS);		
		
		if (DB_TYPE == "postgres"){
			$QUERY = "INSERT INTO cc_speeddial (id_cc_card, phone, name, speeddial) VALUES ('".$_SESSION["card_id"]."', '".$destination."', '".$name."', '".$choose_speeddial."')";
		}else{
			$QUERY = "INSERT INTO cc_speeddial (id_cc_card, phone, name, speeddial, creationdate) VALUES ('".$_SESSION["card_id"]."', '".$destination."', '".$name."', '".$choose_speeddial."', 'now()')";
		}

		$result = $instance_sub_table -> SQLExec ($HD_Form -> DBHandle, $QUERY, 0);
}
/***********************************************************************************/

if ($id!="" || !is_null($id)){
	$HD_Form -> FG_EDITION_CLAUSE = str_replace("%id", "$id", $HD_Form -> FG_EDITION_CLAUSE);
}

if (!isset($form_action))  $form_action="list"; //ask-add
if (!isset($action)) $action = $form_action;
$list = $HD_Form -> perform_action($form_action);
// #### HEADER SECTION
include("PP_header.php");
// #### HELP SECTION
if ($form_action == "list")
{
    // My code for Creating two functionalities in a page
    $HD_Form -> create_toppage ("ask-add");
    if (strlen($_GET["menu"])>0) $_SESSION["menu"] = $_GET["menu"];
 	if (isset($update_msg) && strlen($update_msg)>0) echo $update_msg; 	
?>
	  </center>	
	  <center><font color="red"><b><?php echo gettext("Enter the number which you wish to assign to the code here"); ?></b></font></center>
	   <table align="center"  border="0" width="85%" bgcolor="#eeeeee">
		<form name="theForm" action="<?php  $_SERVER["PHP_SELF"]?>">
		<tr bgcolor="#cccccc">
		<td align="left" valign="bottom">
		<font class="fontstyle_002"> <?php echo gettext("Speed Dial code");?> : </font><select NAME="choose_speeddial" class="form_input_select">
					<?php					 
				  	 foreach ($speeddial_list as $recordset){ 						 
					?>
						<option class=input value='<?php echo $recordset[1]?>' ><?php echo $recordset[1]?> </option>                        
					<?php 	 }
					?>
				</select>
		</td>
		<td align="left" valign="top">
				<font class="fontstyle_002"><?php echo gettext("Destination");?> :</font>
				<input class="form_enter" name="destination" size="15" maxlength="60" style="border: 2px inset rgb(204, 51, 0);">
				- <font class="fontstyle_002"><?php echo gettext("Name");?> :</font>
				<input class="form_enter" name="name" size="15" maxlength="40" style="border: 2px inset rgb(204, 51, 0);">
			</td>	
			<td align="center" valign="middle"> 
						<input class="form_enter" style="border: 2px outset rgb(204, 51, 0);" value="<?php echo gettext("ASSIGN NUMBER TO SPEEDDIAL");?>"  type="submit">
		</td>
        </tr>
	</form>
      </table>
	  <br>
	<?
    // END END END My code for Creating two functionalities in a page
}

// #### TOP SECTION PAGE
$HD_Form -> create_toppage ($form_action);


// #### CREATE FORM OR LIST
//$HD_Form -> CV_TOPVIEWER = "menu";
if (strlen($_GET["menu"])>0) $_SESSION["menu"] = $_GET["menu"];

$HD_Form -> create_form ($form_action, $list, $id=null) ;

// #### FOOTER SECTION
include("PP_footer.php");




?>
