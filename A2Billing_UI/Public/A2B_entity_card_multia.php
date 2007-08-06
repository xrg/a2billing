<?php
$menu_section='menu_agents';
include ("../lib/defines.php");
include ("../lib/module.access.php");
include ("../lib/Form/Class.FormHandler.inc.php");

if (! has_rights (ACX_CUSTOMER)){ 
	   Header ("HTTP/1.0 401 Unauthorized");
	   Header ("Location: PP_error.php?c=accessdenied");
	   die();
}

include ("./form_data/FG_var_card.inc");


$HD_Form -> FG_FILTER_SEARCH_FORM = false;
$HD_Form -> FG_EDITION = false;
$HD_Form -> FG_DELETION = false;
$HD_Form -> FG_OTHER_BUTTON1 = false;
$HD_Form -> FG_OTHER_BUTTON2 = false;
$HD_Form -> FG_FILTER_APPLY = false;
$HD_Form -> FG_DEBUG = 4;

getpost_ifset(array('choose_list', 'choose_agent', 'creditlimit', 'addcredit', 'gen_id', 'cardnum', 'choose_simultaccess', 'choose_typepaid', 'creditlimit', 'enableexpire', 'expirationdate', 'expiredays', 'gtype','guname','gnumtype','gnumstart'));


/***********************************************************************************/

$HD_Form -> setDBHandler (DbConnect());


// GENERATE CARDS


$nbcard = $choose_list;

$new_cards = array();

if ($nbcard>0){
	if (($gtype == 'def') || ($gtype == 'booth'))
		$pdef = 't' ;
	else
		$pdef = 'f';
	$QUERY = str_dbparams($HD_Form->DBHandle, "SELECT agent_gen_regular( %#1, '$pdef', %#2, %3 :: TEXT, %4, %#5, %#6, %#7, %8 ::TIMESTAMP , %#9 );",
	array( $choose_agent, $choose_typepaid, $guname,$gnumstart, LEN_CARDNUMBER,
		$creditlimit, $enableexpire, $expirationdate, $expiredays));
		
	if ($HD_Form->FG_DEBUG>0)
		echo $QUERY ." <br>\n";
		
	$SIP_CONSTS = str_dbparams ($HD_Form->DBHandle, " %1 AS type, %2 AS allow, %3 AS context, %4 AS nat, %5 AS amaflags, %6 AS qualify, %7 AS host, %8 AS dtmfmode ",	
	array(FRIEND_TYPE, FRIEND_ALLOW, FRIEND_CONTEXT, FRIEND_NAT, FRIEND_AMAFLAGS,
		FRIEND_QUALIFY, RIEND_HOST, FRIEND_DTMFMODE));

	for ($k=0;$k<$nbcard;$k++){
		$result = $HD_Form -> DBHandle->Execute($QUERY);
		
		if (($HD_Form->FG_DEBUG >2) || (! $result))
			echo "DB Err:" . $HD_Form -> DBHandle->ErrorMsg() . "<br>\n";
		if (! $result) {
			if ($HD_Form->FG_DEBUG >0)
				echo "Cannot create regular!<br>\n";
			break;
		}
		else {
			$row = $result->FetchRow();
			array_push($new_cards, $row[0]);
		}
		
		if ($gtype == 'booth'){
		
			$BOOTH_QUERY = str_dbparams($HD_Form -> DBHandle, "INSERT INTO cc_booth(agentid,  name, def_card_id, callerid)" .
			"SELECT %#1, 'Booth ' || useralias, %2, username ".
			"FROM cc_card WHERE id = %2 ;",
			array($choose_agent, $row[0]));
			
			$result = $HD_Form -> DBHandle->Execute($BOOTH_QUERY);
		
			if (($HD_Form->FG_DEBUG >2) || (! $result))
				echo "DB Err:" . $HD_Form -> DBHandle->ErrorMsg() . "<br>\n";
			if (! $result) {
				if ($HD_Form->FG_DEBUG >0)
					echo "Cannot create booth!<br>\n";
				break;
			}
			
			$SIP_QUERY = str_dbparams($HD_Form -> DBHandle, "INSERT INTO cc_sip_buddies(".
			"name, accountcode, regexten, callerid, username, secret, " .
			"type, allow, context, nat, amaflags, qualify, host, dtmfmode) ".
			" SELECT username, username, NULL, username, username, mkpasswd(8), " . $SIP_CONSTS .
			" FROM cc_card WHERE id = %#1;", array($row[0])) ;
			
			$result = $HD_Form -> DBHandle->Execute($SIP_QUERY);
		
			if (($HD_Form->FG_DEBUG >2) || (! $result))
				echo "DB Err:" . $HD_Form -> DBHandle->ErrorMsg() . "<br>\n";
			if (! $result) {
				if ($HD_Form->FG_DEBUG >0)
					echo "Cannot create sip_buddy!<br>\n";
				continue;
			}
			$_SESSION["is_sip_iax_change"]=1;
			$_SESSION["is_sip_changed"]=1;
		}
	}
	echo "New cards:";
	print_r($new_cards);
	echo "<br>\n";
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
//echo '<br><br>'.$CC_help_generate_customer; FIXME

$instance_table_agent = new Table("cc_agent", "id, name");
$FG_TABLE_CLAUSE = "";
$list_agent = $instance_table_agent -> Get_list ($HD_Form ->DBHandle, $FG_TABLE_CLAUSE, "name", "ASC", null, null, null, null);
$nb_agent = count($list_agent);

// FORM FOR THE GENERATION
?>


   <table align="center" bgcolor="#cccccc" border="0" width="75%">
        <tbody><tr>
	<form name="theForm" action="<?php echo $_SERVER['PHP_SELF'] ?>" method="POST">
          <td align="left" width="80%">
          <ol>
          
	  	<li> 
	  	<label> <?= _("Choose the number of cards to create: ");?></label>
	  	<input name="choose_list" size="4" class="form_enter" value="1" />
		</li>
				
		<li> 
		<select name="choose_agent" size="1" class="form_enter" style="border: 2px outset rgb(204, 51, 0);">
			<option value=''><?php echo gettext("Select the Agent");?></option>
		
		<?php
			foreach ($list_agent as $recordset){
		?>
			<option class=input value='<?php echo $recordset[0]?>' ><?php echo $recordset[1]?></option>                        
		<?php 	 }
			?>
		</select>
		</li>
		
		<li>
			<?= _("Type of cards");?>&nbsp; :
			<input name="gtype" value="reg" type="radio">&nbsp;<?=_ ("Regulars");?> 
			<input name="gtype" value="def" checked="checked" type="radio">&nbsp;<?= _("Default cards");?>
			<input name="gtype" value="booth" type="radio">&nbsp;<?= _("Booths (def+booth)");?>
			<br><?= _("Naming (defaults)") ?>:
			<input class="form_enter" name="guname" size="20" maxlength="30" value="phone%1.%2">
			<br><?= _("You can use placemarkers %1 = number, %2 = agent nick "); ?>
		
		</li>

		<li>
			<?= _("Numbering");?>&nbsp; :
			<input name="gnumtype" value="rnd" type="radio">&nbsp;<?=_ ("Random");?> 
			<input name="gnumtype" value="seq" checked="checked" type="radio">&nbsp;<?= _("Sequential");?>
			<br><?= _("First number to try") ?>:
			<input class="form_enter" name="gnumstart" size="10" maxlength="30" value="100">
		</li>
		
		<li>
		
		<li>
		<select NAME="choose_typepaid" size="1" class="form_enter" style="border: 2px inset rgb(204, 51, 0);">
			<option value='0' selected><?php echo gettext("PREPAID CARD");?></option>
			<option value='1'><?php echo gettext("POSTPAY CARD");?></option>
			</select>
		</li>
		
		<li>
		<?php echo gettext("Credit Limit of postpay");?> : <input class="form_enter" name="creditlimit" size="10" maxlength="16" style="border: 2px inset rgb(204, 51, 0);">
		</li>
		
		<li>
		<?php echo gettext("Enable expire");?>&nbsp;: <select name="enableexpire" class="form_enter" style="border: 2px inset rgb(204, 51, 0);">
		<option value="0" selected="selected"> <?php echo gettext("NO EXPIRATION");?></option>
		<option value="1"> <?php echo gettext("EXPIRE DATE");?></option>
		<option value="2"> <?php echo gettext("EXPIRE DAYS SINCE FIRST USE");?></option>
		<option value="3"> <?php echo gettext("EXPIRE DAYS SINCE CREATION");?></option>
		</select>
		</li>
		
		<li>
		<?php 
			$begin_date = date("Y");
			$begin_date_plus = date("Y")+10;	
			$end_date = date("-m-d H:i:s");
			$comp_date = "value='".$begin_date.$end_date."'";
			$comp_date_plus = "value='".$begin_date_plus.$end_date."'";
		?>
		<?php echo gettext("Expiry Date");?>&nbsp;: <input class="form_enter" style="border: 2px inset rgb(204, 51, 0);" name="expirationdate" size="40" maxlength="40" <?php echo $comp_date_plus; ?>>
		<br><?php echo gettext("(Format YYYY-MM-DD HH:MM:SS)");?>
		</li>
		
		<li>
		<?php echo gettext("Expiry days");?>&nbsp;: <input class="form_enter" style="border: 2px inset rgb(204, 51, 0);" name="expiredays" size="10" maxlength="6" value="0">
		</li>
	</ol></td>
		
		<td align="left" valign="bottom"> 
		<input class="form_enter" style="border: 2px outset rgb(204, 51, 0);" value="<?= _(" GENERATE CARDS ");?>" type="submit"> 
        </td>
	 </form>
        </tr>
      </tbody></table>
	  <br>


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
