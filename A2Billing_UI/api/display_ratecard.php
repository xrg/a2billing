<?php
/*
this ligne is an exemple of wath you must add into the main page to desplay the ratecard
include ("http://localhost/A2Billing_UI/api/display_ratecard.php?key=0951aa29a67836b860b0865bc495225c&page_url=localhost/index.php&field_to_display=t1.destination,t5.countryprefix,t1.dialprefix,t1.rateinitial&column_name=Destination,Country,Prefix,Rate/Min&field_type=,,money&".$_SERVER['QUERY_STRING']);


to change display set css_url in the include ligne
 you can change parameters in /Css/api_ratecard.css 


*/
include ("../lib/defines.php");
include ("../lib/module.access.php");

 	// The wrapper variables for security
 	$security_key = API_SECURITY_KEY;
	
	// The name of the log file
	$logfile = API_LOGFILE;
	
	// recipient email to send the alarm
	$email_alarm = EMAIL_ADMIN;	
	
	$FG_DEBUG = 0;


getpost_ifset(array('key', 'ratecardid', 'css_url', 'nb_display_lines', 'filter' ,'field_to_display', 'column_name', 'field_type', 'browse_letter', 'prefix_select', 'page_url', 'resulttitle', 'posted', 'stitle', 'current_page', 'order', 'sens', 'choose_currency', 'choose_country', 'letter', 'search', 'currency_select'));

/**variable to set rate display option

	?key 
	&ratecardid  "dispaly only this ratecard
	&css_url
	&nb_display_lines (maximum lignes per page)
	&filter (coutryname,prefix)
	&field_to_display i.e (countryname,sellingrate=money,buyrate=money, etc...)
	&field_type i.e ( ,money,money) (date or money ) is used for display
	&column_name      i.e (countryname,sellingrate,buyrate, etc...)
	&browse_letter  yes or no (A, B, C)
	&prefix_select i.e 32 (only prefix start by 32)
	&currency_select "cirency code i.e USD"
	&page_url i.e http://mysite.com/rates.php
*/
  $ip_remote = getenv('REMOTE_ADDR'); 
  $mail_content = "[" . date("Y/m/d G:i:s", mktime()) . "] "."Request asked from:$ip_remote with key:$key \n";
 // CHECK KEY
 if ($FG_DEBUG > 0) echo "<br> md5(".md5($security_key).") !== $key";
if (md5($security_key) !== $key  || strlen($security_key)==0)
 {
	  mail($email_alarm, "ALARM : RATE CARD API - CODE_ERROR 2", $mail_content);
	  if ($FG_DEBUG > 0) echo ("[" . date("Y/m/d G:i:s", mktime()) . "] "."[$productid] - CODE_ERROR 2"."\n");
	  error_log ("[" . date("Y/m/d G:i:s", mktime()) . "].CODE_ERROR 2"."\n", 3, $logfile);
	  echo("400 Bad Request");
	  exit();  
 } 

//**
//set  default values if not isset vars

if (!isset($nb_display_lines) || strlen($nb_display_lines)==0) $nb_display_lines=1;
if (!isset($resulttitle) || strlen($resulttitle)==0) $resulttitle="Rate list";
if (!isset($filter) || strlen($filter)==0) $filter="countryname,prefix";
//if (!isset($field_to_display) || strlen ($field_to_display)==0) $field_to_display="t1.destination,t1.dialprefix,t1.rateinitial";
if (!isset($field_type) || strlen ($field_to_type)==0) $field_type=",,,money";
//if (!isset($column_name) || strlen($column_name)==0) $column_name="Destination,Prefix,Rate/Min";
if (!isset($browse_letter) || strlen($browse_letter)==0) $browse_letter="yes";
if (!isset($prefix_select) || strlen($prefix_select)==0) $prefix_select="";
if (!isset($currency_select) || strlen($currency_select)==0) $currency_select=true;else $choose_currency=$currency_select;
if (!isset($css_url) || strlen($css_url)==0) $css_url=substr("http://".$_SERVER['SERVER_ADDR'].$_SERVER['PHP_SELF'],0,strlen("http://".$_SERVER['SERVER_ADDR'].$_SERVER['PHP_SELF'])-20)."api_ratecard.css";

if (!isset($page_url) || strlen($page_url)==0){ echo "Error : need to define page_url !!!"; exit; }
if ( (substr($page_url,0,7)!='http://') && (substr($page_url,0,8)!='https://') ){ echo "Error : page_url need to start by http:// or https:// "; exit; }


//end set default
trim($field_to_display);
trim($field_type);
$field=explode(",",$field_to_display);
$type=explode(",",$field_type);
$column=explode(",",$column_name);
$fltr=explode(",",$filter);

if (!isset ($current_page) || ($current_page == "")){	
	$current_page=0; 
}

if (!isset ($FG_TABLE_CLAUSE) || strlen($FG_TABLE_CLAUSE)==0){
	$FG_TABLE_CLAUSE="t3.id = t2.idtariffplan AND  t2.idtariffplan=t1.idtariffplan AND t4.prefixe=t1.dialprefix AND t4.id_cc_country=t5.id";
}

$FILTER_COUNTRY=false;
$FILTER_PREFIX=false;
$DISPLAY_LETTER=false;

for ($i=0;$i<count($fltr);$i++){
	switch ($fltr[$i]){
		case "countryname":
			$FILTER_COUNTRY=true;
			if (isset ($choose_country) && strlen($choose_country) != 0) $FG_TABLE_CLAUSE.=" AND t5.id='$choose_country'";
		break;
		case "prefix":
			$FILTER_PREFIX=true;
			if (isset ($search) && strlen($search) != 0) $FG_TABLE_CLAUSE.=" AND t4.prefixe LIKE '$search%'";
		break;
	}
}
if (isset($browse_letter) && strtoupper($browse_letter)=="YES") $DISPLAY_LETTER=true;
if (isset($letter) && strlen($letter)!=0) $FG_TABLE_CLAUSE.=" AND t5.countryname LIKE '".strtolower ($letter)."%'";

if (isset($ratecardid) && strlen($ratecardid)!=0) $FG_TABLE_CLAUSE.=" AND t1.id = '$ratecardid'";
if ($FILTER_COUNTRY || $DISPLAY_LETTER) {
	$nb_display_lines=5000;
	$current_page=0;
}

// this variable specifie the debug type (0 => nothing, 1 => sql result, 2 => boucle checking, 3 other value checking)
$FG_DEBUG = 0;

// The variable FG_TABLE_NAME define the table name to use
$FG_TABLE_NAME="cc_ratecard t1, cc_tariffgroup_plan t2, cc_tariffplan t3, cc_prefix t4, cc_country t5";

//$link = DbConnect();
$DBHandle  = DbConnect();


// First Name of the column in the html page, second name of the field
$FG_TABLE_COL = array();

if (count($column)==count($field) && count($field)==count($type) && count($column) != 0)
{	for ($i=0; $i<count($column); $i++){
		switch ($type[$i]) {
			case "money":
				$bill="display_2bill"; 
			break;
			case "date":
				$bill="display_dateformat";
			break;
			default:
				$bill="";
		}
		$FG_TABLE_COL[]=array (gettext($column[$i]), $field[$i], (100/count($column))."%", "center", "sort", "", "", "", "", "", "",$bill);
	}
}

$FG_COL_QUERY='DISTINCT '.$field_to_display;


$FG_TABLE_DEFAULT_ORDER = $field[0];
$FG_TABLE_DEFAULT_SENS = "DESC";
	
// The variable LIMITE_DISPLAY define the limit of record to display by page
$FG_LIMITE_DISPLAY=$nb_display_lines;

// Number of column in the html table
$FG_NB_TABLE_COL=count($FG_TABLE_COL);

//This variable will store the total number of column
$FG_TOTAL_TABLE_COL = $FG_NB_TABLE_COL;

//This variable define the Title of the HTML table
$FG_HTML_TABLE_TITLE=gettext($resulttitle);

//This variable define the width of the HTML table

if ($FG_DEBUG == 3) echo "<br>Table : $FG_TABLE_NAME  	- 	Col_query : $FG_COL_QUERY";


if ( is_null ($order) || is_null($sens) ){
	$order = $FG_TABLE_DEFAULT_ORDER;
	$sens  = $FG_TABLE_DEFAULT_SENS;
}

$instance_table = new Table($FG_TABLE_NAME, $FG_COL_QUERY);
$list = $instance_table -> Get_list ($DBHandle, $FG_TABLE_CLAUSE, $order, $sens, null, null, $FG_LIMITE_DISPLAY, $current_page*$FG_LIMITE_DISPLAY);

$country_table = new Table("cc_country","id,countryname");
$country_list = $country_table -> Get_list ($DBHandle);

$QUERY="SELECT $FG_COL_QUERY from $FG_TABLE_NAME where $FG_TABLE_CLAUSE";
$list_c=$instance_table->SQLExec($DBHandle,$QUERY,1);
$nb_record = count($list_c);
if ($nb_record<=$FG_LIMITE_DISPLAY){ 
	$nb_record_max=1;
}else{ 
	if ($nb_record % $FG_LIMITE_DISPLAY == 0){
		$nb_record_max=(intval($nb_record/$FG_LIMITE_DISPLAY));
	}else{
		$nb_record_max=(intval($nb_record/$FG_LIMITE_DISPLAY)+1);
	}	
}
?>

<html><head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<link href="<?php echo $css_url;?>" rel="stylesheet" type="text/css">


</head>

<div>
<?php echo "$page_url?order=$order&sens=$sens&current_page=$current_page&css_url=$css_url&page_url=$page_url"?>

<!-- ** ** ** ** ** Part for the research ** ** ** ** ** -->
	<FORM METHOD=GET name="myForm" ACTION="<?php echo "http://$page_url?order=$order&sens=$sens&current_page=$current_page&css_url=$css_url&page_url=$page_url"?>">
	<INPUT TYPE="hidden" NAME="posted" value=1>
	<INPUT TYPE="hidden" NAME="current_page" value=0>
	<div class="title"  align="left">
		<H1><?php echo gettext("Rate search");?></H1>
	</div>
	<div class="search">	
		<?php if ($FILTER_COUNTRY){ ?>
		<div class="searchelement"  align="left">
			<select NAME="choose_country" class="select" >
			<option value="" <?php if (!isset($choose_country)) {?>selected<?php } ?>><?php echo gettext("Select a country");?></option>
			<?php
				foreach($country_list as $country) {?>
					<option value='<?php echo $country[0] ?>' <?php if ($choose_country==$country[0]) {?>selected<?php } ?>><?php echo $country[1] ?><br>
					</option>
				<?php 	} ?></select>
		</div>
		<?php } if ($DISPLAY_LETTER){?>
		<div class="searchelement"  align="left">
			<?php echo gettext("select the first letter of the country you are looking for");?><br>
			<?php for ($i=65;$i<=90;$i++) {
 				$x = chr($i);
 				echo "<a href=\"http://$page_url?letter=$x&stitle=$stitle&current_page=$current_page&order=$order&sens=$sens&posted=$posted&choose_currency=$choose_currency&search=$search&choose_country=$choose_country&css_url=$css_url&page_url=$page_url\">$x</a>";
			}?></font>
		</div>
		<?php } if ($FILTER_PREFIX){ ?>
		<div class="searchelement"  align="left">
			<?php echo gettext("Enter dial code"); ?><br>
			<INPUT TYPE="text" NAME="search" class="textfield" value="<?php echo $search ?>"></INPUT>
		</div>
		<?php } if ($currency_select){ ?>
		<div class="searchelement"  align="left">
			<?php echo gettext("Select a currency");?><br>
			<select NAME="choose_currency" class="select">
				<?php
				$currencies_list = get_currencies();
				foreach($currencies_list as $key => $cur_value) {?>
				<option value="<?php echo $key ?>" <?php if (($choose_currency==$key) || (!isset($choose_currency) && $key==strtoupper(BASE_CURRENCY))){?>selected<?php } ?>><?php echo $cur_value[1] ?>
				</option>
				<?php 	} ?>
				</select>
		</div>
		<?php } ?>
		<div class="searchelement"  align="left">
			<input type="submit"  align="top" value="Search" class="button">
		</div>
	</div>
	</FORM>

<!-- ** ** ** ** ** Part to display the ratecard ** ** ** ** ** -->

	<div class="result" align="left">
	<table width="50%" border=0 cellPadding=0 cellSpacing=0>
	<TR> 
		<TD> 
			<?php echo $FG_HTML_TABLE_TITLE?>
		</TD>
	</TR>
	<TR>
		<TD>
		<TABLE width="100%" border=0 cellPadding=0 cellSpacing=0>
		<TBODY>
			<TR> 
				<?php if (is_array($list) && count($list)>0){
					for($i=0;$i<$FG_NB_TABLE_COL;$i++){ ?>
						<TH width="<?php echo $FG_TABLE_COL[$i][2]?>" class="table_title"> 
						<center><strong> 
						<?php  if (strtoupper($FG_TABLE_COL[$i][4])=="SORT"){?>
						<a href="<?php  echo "http://$page_url?stitle=$stitle&current_page=$current_page&order=".$FG_TABLE_COL[$i][1]."&sens=";if ($sens=="ASC"){echo"DESC";}else{echo"ASC";} echo "&posted=$posted&letter=$letter&choose_currency=$choose_currency&search=$search&choose_country=$choose_country&letter=$letter&css_url=$css_url&page_url=$page_url";?>"> 
						<?php  } ?>
						<?php echo $FG_TABLE_COL[$i][0]?> 
						<?php  if (strtoupper($FG_TABLE_COL[$i][4])=="SORT"){?>
							</a> 
						<?php }?>
						</strong></center></TH>
				   	<?php } ?>		
			</TR>
			<?php
				 $alternate=0;
				 foreach ($list as $recordset){ 
				 $alternate+=M_PI/2;
			?>
			<TR> 
				<?php for($i=0;$i<$FG_NB_TABLE_COL;$i++){
					$record_display = $recordset[$i];
					if ( is_numeric($FG_TABLE_COL[$i][5]) && (strlen($record_display) > $FG_TABLE_COL[$i][5])  ){
						$record_display = substr($record_display, 0, $FG_TABLE_COL[$i][5]-3)."";  
					} ?>
                 			<TD class="tabletr_<?echo intval(abs(cos($alternate)));?>" vAlign=top align="<?php echo $FG_TABLE_COL[$i][3]?>"><?php 
					if (isset ($FG_TABLE_COL[$i][11]) && strlen($FG_TABLE_COL[$i][11])>1){
						call_user_func($FG_TABLE_COL[$i][11], $record_display);
					}else{
						echo stripslashes($record_display);
					}?>
					</TD>
				<?php  }?>
			</TR>
			<?php
			}//foreach ($list as $recordset)
				}else{
					echo gettext("No rate found !!!");
				}//end_if
				?>
		</TBODY>
		</TABLE>
	</td>
	</tr>
	<TR>
	<TD> 
		<?php
		$c_url="http://$page_url?stitle=$stitle&order=$order&sens=$sens&current_page=%s&posted=$posted&letter=$letter&choose_currency=$choose_currency&search=$search&choose_country=$choose_country&css_url=$css_url&page_url=$page_url";
		printPages($current_page+1, $nb_record_max, $c_url); 
		?>
	</TD>
	</TD>
	</TR>
	</table>
	<div>
</div>
</html>
