<?php
include ("../lib/defines.php");
include ("../lib/module.access.php");
include ("../lib/Form/Class.FormHandler.inc.php");
include ("./form_data/FG_var_card.inc");



if (! has_rights (ACX_CUSTOMER)){ 
	   Header ("HTTP/1.0 401 Unauthorized");
	   Header ("Location: PP_error.php?c=accessdenied");	   
	   die();	   
}


$HD_Form -> FG_FILTER_SEARCH_FORM = false;
$HD_Form -> FG_EDITION = false;
$HD_Form -> FG_DELETION = false;
$HD_Form -> FG_OTHER_BUTTON1 = false;
$HD_Form -> FG_OTHER_BUTTON2 = false;
$HD_Form -> FG_FILTER_APPLY = false;
// $HD_Form -> FG_DEBUG = 4;

getpost_ifset(array('choose_list', 'choose_agent', 'creditlimit', 'addcredit', 'gen_id', 'cardnum', 'choose_simultaccess', 'choose_typepaid', 'creditlimit', 'enableexpire', 'expirationdate', 'expiredays'));


/***********************************************************************************/

$HD_Form -> setDBHandler (DbConnect());


// GENERATE CARDS


$nbcard = $choose_list;

if ($nbcard>0){
/*
'2465773443', '331', 'a', 't', 'LASTNAME', 'FIRSTNAME', 'email@kiki.net', 'adresse', 'city', 'state', 'countr', '1000', '65000000', '2465773443'
INSERT INTO card (myusername, credit, tariff, activated, lastname, firstname, email, address, city, state, 
country, zipcode, phone, userpass) values ('2465773443', '331', 'a', 't', 'LASTNAME', 'FIRSTNAME', 'email@domain.com', 
'adresse', 'city', 'state', 'country', '1000', '0000000000', '2465773443')
*/

	$currency = BASE_CURRENCY;
	$language = 'en';
	$tariff = 0;

	$instance_tmp_agent = new Table("cc_agent", "id, name,currency,language,tariffgroup");
	if ($HD_Form->FG_DEBUG>=4) $instance_tmp_agent->debug_st=1;
	$FG_TABLE_CLAUSE = "id = ". $HD_Form->DBHandle->Quote($choose_agent);
	$list_tmp_agent = $instance_tmp_agent -> Get_list ($HD_Form ->DBHandle, $FG_TABLE_CLAUSE);
	if (count($list_tmp_agent) !=1) {
		if ($HD_Form->FG_DEBUG>1)
			echo "Cannot locate agent with query: " . $FG_TABLE_CLAUSE;
		
	}
	else {
		echo " > " . $list_tmp_agent . "<br>";
		if ($HD_Form->FG_DEBUG>2)
			echo "Located agent \"".
				$list_tmp_agent[0][1] . "\" for generation\n<br>";
		$currency = $list_tmp_agent[0][2];
		$language = $list_tmp_agent[0][3];
		$tariff = $list_tmp_agent[0][4];
	}

		$FG_ADITION_SECOND_ADD_TABLE  = "cc_card";		
		$FG_ADITION_SECOND_ADD_FIELDS = "username, useralias, credit, tariff, activated, lastname,  userpass, currency, typepaid , creditlimit, enableexpire, expirationdate, expiredays, uipass";

		if (DB_TYPE != "postgres"){
		        $FG_ADITION_SECOND_ADD_FIELDS .= ",creationdate ";
		}
				
		
		
		$instance_sub_table = new Table($FG_ADITION_SECOND_ADD_TABLE, $FG_ADITION_SECOND_ADD_FIELDS);
				
		if ($HD_Form->FG_DEBUG>=4) $instance_sub_table->debug_st=1;
		$gen_id = time();
		$_SESSION["IDfilter"]=$gen_id;
		
		
		$creditlimit = is_numeric($creditlimit) ? $creditlimit : 0;
		if ($HD_Form->FG_DEBUG >1 ) echo "::> $currency, $choose_typepaid, $creditlimit <br>\n";
		for ($k=0;$k<$nbcard;$k++){
			 $arr_card_alias = gen_card_with_alias();
			 $cardnum = $arr_card_alias[0];
			 $useralias = $arr_card_alias[1];
			if (!is_numeric($addcredit)) $addcredit=0;
			$passui_secret = MDP_NUMERIC(10);
			$FG_ADITION_SECOND_ADD_VALUE  = "'$cardnum', '$useralias', '$addcredit', '$tariff', 'f', '$gen_id', '$cardnum',  '$currency', $choose_typepaid, $creditlimit, $enableexpire, '$expirationdate', $expiredays, '$passui_secret'";
			
			if (DB_TYPE != "postgres") $FG_ADITION_SECOND_ADD_VALUE .= ",now() ";

			$result_query = $instance_sub_table -> Add_table ($HD_Form ->DBHandle, $FG_ADITION_SECOND_ADD_VALUE, null, null, null);
			
 			
		}
		
		{
			$query2="INSERT INTO cc_agent_cards (card_id, agentid) SELECT id, " . $HD_Form->DBHandle->Quote($choose_agent) .
			" FROM cc_card WHERE lastname = " . $HD_Form->DBHandle->Quote($gen_id) ." ;" ;
			$result_query2 = $HD_Form->DBHandle->Execute($query2);
			//if (!$result_query2 ) echo $query2;
 		}




}
if (!isset($_SESSION["IDfilter"])) $_SESSION["IDfilter"]='NODEFINED';


$HD_Form -> FG_TABLE_CLAUSE = " lastname='".$_SESSION["IDfilter"]."'";

// END GENERATE CARDS



$HD_Form -> init();


if ($id!="" || !is_null($id)){	
	$HD_Form -> FG_EDITION_CLAUSE = str_replace("%id", "$id", $HD_Form -> FG_EDITION_CLAUSE);	
}


if (!isset($form_action))  $form_action="list"; //ask-add
if (!isset($action)) $action = $form_action;
	


$list = $HD_Form -> perform_action($form_action);



// #### HEADER SECTION
include("PP_header.php");

// #### HELP SECTION
echo '<br><br>'.$CC_help_generate_customer;



$instance_table_agent = new Table("cc_agent", "id, name");
$FG_TABLE_CLAUSE = "";
$list_agent = $instance_table_agent -> Get_list ($HD_Form ->DBHandle, $FG_TABLE_CLAUSE, "name", "ASC", null, null, null, null);
$nb_agent = count($list_agent);

// FORM FOR THE GENERATION
?>

	  
   <table align="center" bgcolor="#cccccc" border="0" width="65%">
        <tbody><tr>
	<form name="theForm" action="<?php echo $_SERVER['PHP_SELF'] ?>" method="POST">
          <td align="left" width="75%">
	  	<strong>1)</strong> 
	  	<label> <?= _("Choose the number of cards to create: ");?></label>
	  	<input name="choose_list" size="4" class="form_enter" value="1" />
		<br/>
				
		  	<strong>2)</strong> 
				<select name="choose_agent" size="1" class="form_enter" style="border: 2px outset rgb(204, 51, 0);">
					<option value=''><?php echo gettext("Select the Agent");?></option>
				
				<?php					 
			  	 foreach ($list_agent as $recordset){ 						 
				?>
					<option class=input value='<?php echo $recordset[0]?>' ><?php echo $recordset[1]?></option>                        
				<?php 	 }
					?>
				</select>
				<br/>
				
			  	<strong>3)</strong> 
				<?php echo gettext("Initial amount of credit");?> : 	<input class="form_enter" name="addcredit" size="10" maxlength="10" style="border: 2px inset rgb(204, 51, 0);">
				<br/>
				
				
				<strong>4)</strong>
				<select NAME="choose_typepaid" size="1" class="form_enter" style="border: 2px inset rgb(204, 51, 0);">
					<option value='0' selected><?php echo gettext("PREPAID CARD");?></option>
					<option value='1'><?php echo gettext("POSTPAY CARD");?></option>
				   </select>
				<br/>
				<strong>5)</strong>
				<?php echo gettext("Credit Limit of postpay");?> : <input class="form_enter" name="creditlimit" size="10" maxlength="16" style="border: 2px inset rgb(204, 51, 0);">
				<br/>
				<strong>6)</strong>
			   <?php echo gettext("Enable expire");?>&nbsp;: <select name="enableexpire" class="form_enter" style="border: 2px inset rgb(204, 51, 0);">
								<option value="0" selected="selected">
						                           <?php echo gettext("NO EXPIRATION");?>                            </option><option value="1">
						                             <?php echo gettext("EXPIRE DATE");?>                          </option><option value="2">
						                            <?php echo gettext("EXPIRE DAYS SINCE FIRST USE");?>                           </option><option value="3">
						                            <?php echo gettext("EXPIRE DAYS SINCE CREATION");?>                           </option></select>
				<br/>
				<?php 
					$begin_date = date("Y");
					$begin_date_plus = date("Y")+10;	
					$end_date = date("-m-d H:i:s");
					$comp_date = "value='".$begin_date.$end_date."'";
					$comp_date_plus = "value='".$begin_date_plus.$end_date."'";
				?>
				<strong>7)</strong>
				<?php echo gettext("Expiry Date");?>&nbsp;: <input class="form_enter" style="border: 2px inset rgb(204, 51, 0);" name="expirationdate" size="40" maxlength="40" <?php echo $comp_date_plus; ?>><?php echo gettext("(Format YYYY-MM-DD HH:MM:SS)");?>
				<br/>
				<strong>8)</strong>
			   <?php echo gettext("Expiry days");?>&nbsp;: <input class="form_enter" style="border: 2px inset rgb(204, 51, 0);" name="expiredays" size="10" maxlength="6" value="0">
				<br/>
		</td>	
		<td align="left" valign="bottom"> 
				<input class="form_enter" style="border: 2px outset rgb(204, 51, 0);" value="<?php echo gettext(" GENERATE CARDS ");?>" type="submit"> 
        </td>
	 </form>
        </tr>
      </tbody></table>
	  <br>
	    
	  
	  <?php  if (($_SESSION["is_admin"]==1) && (2==3)){ ?>
	  
	   <table width="<?php echo $FG_HTML_TABLE_WIDTH?>" border="0" align="center" bgcolor="#CCCCCC">
        <tr>
          <td align="left">
		   <form NAME="theForm">
			  	<select NAME="choose_list" size="1" class="form_enter" style="border: 2px outset rgb(204, 51, 0);">
					<option value=''><?php echo gettext("Choose a Reseller");?></option>
					<?php					 
				  	 foreach ($list_reseller as $recordset){ 						 
					?>
					<option class=input value='<?php echo $recordset[0]?>' <?php if ($recordset[0]==$IDmanager) echo "selected";?>><?php echo $recordset[1]?></option>                        
					<?php 	 }
					?>
				</select>
				<input class="form_enter" style="border: 2px outset rgb(204, 51, 0);" 
				TYPE="button" VALUE=" DISPLAY CUSTOMERS OF THIS RESELLER" onClick="openURL('./PP_entity_anacust.php?IDmanager=')"> 
		   </form>
        </td>
        </tr>
      </table>
	  <br/>
	   <?php  } ?>

<?php
// #### TOP SECTION PAGE
$HD_Form -> create_toppage ($form_action);


// #### CREATE FORM OR LIST
//$HD_Form -> CV_TOPVIEWER = "menu";
if (strlen($_GET["menu"])>0) $_SESSION["menu"] = $_GET["menu"];

$HD_Form -> create_form ($form_action, $list, $id=null) ;


$_SESSION[$HD_Form->FG_EXPORT_SESSION_VAR]= "SELECT ".$HD_Form -> FG_EXPORT_FIELD_LIST." FROM $HD_Form->FG_TABLE_NAME";
if (strlen($HD_Form->FG_TABLE_CLAUSE)>1) 
	$_SESSION[$HD_Form->FG_EXPORT_SESSION_VAR] .= " WHERE $HD_Form->FG_TABLE_CLAUSE ";
if (!is_null ($HD_Form->FG_ORDER) && ($HD_Form->FG_ORDER!='') && !is_null ($HD_Form->FG_SENS) && ($HD_Form->FG_SENS!='')) 
	$_SESSION[$HD_Form->FG_EXPORT_SESSION_VAR].= " ORDER BY $HD_Form->FG_ORDER $HD_Form->FG_SENS";




// #### FOOTER SECTION
include("PP_footer.php");
?>
