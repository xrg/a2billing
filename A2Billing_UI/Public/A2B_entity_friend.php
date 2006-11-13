<?php
include ("../lib/defines.php");
include ("../lib/module.access.php");
include ("../lib/Form/Class.FormHandler.inc.php");
include ("./form_data/FG_var_friend.inc");


if (! has_rights (ACX_RATECARD)){ 
	   Header ("HTTP/1.0 401 Unauthorized");
	   Header ("Location: PP_error.php?c=accessdenied");
	   die();
}



/***********************************************************************************/

$HD_Form -> setDBHandler (DbConnect());

$HD_Form -> init();

/********************************* ADD SIP / IAX FRIEND ***********************************/
getpost_ifset(array("cardnumber", "useralias"));

if ( (isset ($cardnumber) && (is_numeric($cardnumber)  != "")) && ( $form_action == "add_sip" || $form_action == "add_iax") ){

	$_SESSION["is_sip_iax_change"]=1;
	
	$HD_Form -> FG_GO_LINK_AFTER_ACTION = "A2B_entity_card.php?atmenu=card&stitle=Customers_Card&id=";

	if ( $form_action == "add_sip" ) {
		$friend_param_update=" sip_buddy='1' ";
		$_SESSION["is_sip_changed"]=1;
	}
	else {
		$friend_param_update=" iax_buddy='1' ";
		$_SESSION["is_iax_changed"]=1;
	}
	
	$instance_table_friend = new Table('cc_card');
	$instance_table_friend -> Update_table ($HD_Form -> DBHandle, $friend_param_update, "username='$cardnumber'", $func_table = null);
	
	
	if ( $form_action == "add_sip" )
		$TABLE_BUDDY = 'cc_sip_buddies';
	else
		$TABLE_BUDDY = 'cc_iax_buddies';
	
	$instance_table_friend = new Table($TABLE_BUDDY,'*');	
	$list_friend = $instance_table_friend -> Get_list ($HD_Form -> DBHandle, "name='$cardnumber'", null, null, null, null);
	
	if (is_array($list_friend) && count($list_friend)>0){ Header ("Location: ".$HD_Form->FG_GO_LINK_AFTER_ACTION); exit();}

	$form_action = "add";
		
	$_POST['accountcode'] = $_POST['username']= $_POST['name']= $_POST['cardnumber'] = $cardnumber;
	$_POST['allow']='g729,ulaw,alaw,gsm';
	$_POST['context']='a2billing';
	$_POST['nat']='yes';
	$_POST['amaflags']='billing';
	$_POST['regexten']=$cardnumber;
	$_POST['callerid']=$useralias;
	$_POST['qualify']='yes';
	$_POST['host']='dynamic';   
	$_POST['dtmfmode']='RFC2833';
	$_POST['secret'] = MDP_NUMERIC(10);
	
	// for the getProcessed var
	$HD_Form->_vars = array_merge($_GET, $_POST);
}

/***********************************************************************************/




$HD_Form -> FG_EDITION_LINK	= $_SERVER['PHP_SELF']."?form_action=ask-edit&atmenu=$atmenu&id=";
$HD_Form -> FG_DELETION_LINK = $_SERVER['PHP_SELF']."?form_action=ask-delete&atmenu=$atmenu&id=";


if ($id!="" || !is_null($id)){	
	$HD_Form -> FG_EDITION_CLAUSE = str_replace("%id", "$id", $HD_Form -> FG_EDITION_CLAUSE);	
}


if (!isset($form_action))  $form_action="list"; //ask-add
if (!isset($action)) $action = $form_action;

// CHECK THE ACTION AND SET THE IS_SIP_IAX_CHANGE IF WE ADD/EDIT/REMOVE A RECORD
if ( $form_action == "add" || $form_action == "edit" || $form_action == "delete" ){
	$_SESSION["is_sip_iax_change"]=1;
	if ($atmenu=='sipfriend'){
		$_SESSION["is_sip_changed"]=1;
	}else{
		$_SESSION["is_iax_changed"]=1;
	}
}

$list = $HD_Form -> perform_action($form_action);



// #### HEADER SECTION
include("PP_header.php");

// #### HELP SECTION
if ($form_action=='list'){ 
	echo '<br><br>'.$CC_help_sipfriend_list;
	
	if ( isset($_SESSION["is_sip_iax_change"]) && $_SESSION["is_sip_iax_change"]){ ?>
		  <table width="<?php echo $HD_Form -> FG_HTML_TABLE_WIDTH?>" border="0" align="center" cellpadding="0" cellspacing="0" >	  
			<TR><TD style="border-bottom: medium dotted #ED2525" align="center"> Changes detected on SIP/IAX Friends</TD></TR>
			<TR><FORM NAME="sipfriend">
				<td height="31" bgcolor="#ED2525" style="padding-left: 5px; padding-right: 3px;" align="center">			
				<font color=white><b>
				<?php  if ( isset($_SESSION["is_sip_changed"]) && $_SESSION["is_sip_changed"] ){ ?>
				SIP : <input class="form_enter" style="border: 2px outset rgb(204, 51, 0);" TYPE="button" VALUE=" GENERATE additional_a2billing_sip.conf " 
				onClick="self.location.href='./CC_generate_friend_file.php?atmenu=sipfriend';">
				<?php } 
				if ( isset($_SESSION["is_iax_changed"]) && $_SESSION["is_iax_changed"] ){ ?>
				IAX : <input class="form_enter" style="border: 2px outset rgb(204, 51, 0);" TYPE="button" VALUE=" GENERATE additional_a2billing_iax.conf " 
				onClick="self.location.href='./CC_generate_friend_file.php?atmenu=iaxfriend';">
				<?php } ?>	
				</b></font></td></FORM>
			</TR>
		   </table>
	<?php  } // endif is_sip_iax_change

}else echo '<br><br>'.$CC_help_sipfriend_edit;



// #### TOP SECTION PAGE
$HD_Form -> create_toppage ($form_action);


// #### CREATE FORM OR LIST
//$HD_Form -> CV_TOPVIEWER = "menu";
if (strlen($_GET["menu"])>0) $_SESSION["menu"] = $_GET["menu"];

$HD_Form -> create_form ($form_action, $list, $id=null) ;

// #### FOOTER SECTION
include("PP_footer.php");

?>
