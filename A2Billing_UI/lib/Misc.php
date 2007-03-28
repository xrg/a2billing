<?php
/***************************************************************************
 *            Misc.php
 *
 *  GPL : Belaid Arezqui
 *  Email : areski _atl_ gmail
 ****************************************************************************/

/*
 * function splitable_data
 */
function splitable_data ($splitable_value){
	
	$arr_splitable_value = explode(",", $splitable_value);
	foreach ($arr_splitable_value as $arr_value){
		$arr_value = trim ($arr_value);
		$arr_value_explode = explode("-", $arr_value,2);
		if (count($arr_value_explode)>1){
			if (is_numeric($arr_value_explode[0]) && is_numeric($arr_value_explode[1]) && $arr_value_explode[0] < $arr_value_explode[1] ){
				for ($kk=$arr_value_explode[0];$kk<=$arr_value_explode[1];$kk++){
					$arr_value_to_import[] = $kk;
				}
			}elseif (is_numeric($arr_value_explode[0])){
				$arr_value_to_import[] = $arr_value_explode[0];
			}elseif (is_numeric($arr_value_explode[1])){
				$arr_value_to_import[] = $arr_value_explode[1];
			}
			
		}else{
			$arr_value_to_import[] = $arr_value_explode[0];
		}
	}
	
	$arr_value_to_import = array_unique($arr_value_to_import);
	sort($arr_value_to_import);
	return $arr_value_to_import;
}

/*
 * function sanitize_data
 */
function sanitize_data($data){
	$lowerdata = strtolower ($data);
	//echo "----> $data ";
	$data = str_replace('--', '', $data);	
	$data = str_replace("'", '', $data);
	$data = str_replace('=', '', $data);
	$data = str_replace(';', '', $data);
	//$lowerdata = str_replace('table', '', $lowerdata);
	//$lowerdata = str_replace(' or ', '', $data);
	if (!(strpos($lowerdata, ' or ')===FALSE)){ return false;}
	if (!(strpos($lowerdata, 'table')===FALSE)){ return false;}
	//echo "----> $data<br>";
	return $data;
}


/*
 * function getpost_ifset
 */
function getpost_ifset($test_vars)
{
	if (!is_array($test_vars)) {
		$test_vars = array($test_vars);
	}
	foreach($test_vars as $test_var) { 
		if (isset($_POST[$test_var])) { 
			global $$test_var;
			$$test_var = $_POST[$test_var];
			$$test_var = sanitize_data($$test_var);
		} elseif (isset($_GET[$test_var])) {
			global $$test_var; 
			$$test_var = $_GET[$test_var];
			$$test_var = sanitize_data($$test_var);
		}
	}
} 


/*
 * function display_money
 */
function display_money($value, $currency = BASE_CURRENCY){			
	echo $value.' '.$currency;			
}


/*
 * function display_dateformat
 */
function display_dateformat($mydate){
	if (DB_TYPE == "mysql"){			
		if (strlen($mydate)==14){
			// YYYY-MM-DD HH:MM:SS 20300331225242
			echo substr($mydate,0,4).'-'.substr($mydate,4,2).'-'.substr($mydate,6,2);
			echo ' '.substr($mydate,8,2).':'.substr($mydate,10,2).':'.substr($mydate,12,2);				
			return;
		}
	}	
	echo $mydate;			
}

/*
 * function display_dateonly
 */
function display_dateonly($mydate)
{
	echo date("m/d/Y", strtotime($mydate));
}

/*
 * function res_display_dateformat
 */
function res_display_dateformat($mydate){
	
	if (DB_TYPE == "mysql"){			
		if (strlen($mydate)==14){
			// YYYY-MM-DD HH:MM:SS 20300331225242
			$res= substr($mydate,0,4).'-'.substr($mydate,4,2).'-'.substr($mydate,6,2);
			$res.= ' '.substr($mydate,8,2).':'.substr($mydate,10,2).':'.substr($mydate,12,2);				
			return $res;
		}
	}	
	
	return $mydate;			
}

/*
 * function display_minute
 */
function display_minute($sessiontime){
		global $resulttype;
		if ((!isset($resulttype)) || ($resulttype=="min")){  
				$minutes = sprintf("%02d",intval($sessiontime/60)).":".sprintf("%02d",intval($sessiontime%60));
		}else{
				$minutes = $sessiontime;
		}
		echo $minutes;
}

function display_2dec($var){		
		echo number_format($var,2);
}

function display_2dec_percentage($var){	
		if (isset($var))
		{	
			echo number_format($var,2)."%";
		}else
		{
			echo "n/a";
		}
}

function display_2bill($var, $currency = BASE_CURRENCY){	
		global $currencies_list, $choose_currency;
		if (isset($choose_currency) && strlen($choose_currency)==3) $currency=$choose_currency;
		if ( (!isset($currencies_list)) || (!is_array($currencies_list)) ) $currencies_list = get_currencies();
		$var = $var / $currencies_list[strtoupper($currency)][2];
		echo number_format($var,3).' '.$currency;
}

function remove_prefix($phonenumber){
		
		if (substr($phonenumber,0,3) == "011"){
					echo substr($phonenumber,3);
					return 1;
		}
		echo $phonenumber;
}

/*
 * function linkonmonitorfile
 */
function linkonmonitorfile($value){
			  
   $myfile = $value.".".MONITOR_FORMATFILE;
   $myfile = base64_encode($myfile);
   echo "<a target=_blank href=\"call-log-customers.php?download=file&file=".$myfile."\">";
   echo '<img src="'.Images_Path.'/stock-mic.png" height="18" /></a>';
   
}

/*
 * function MDP_STRING
 */
function MDP_STRING($chrs = LEN_CARDNUMBER){
	$pwd = ""  ;
	mt_srand ((double) microtime() * 1000000);
	while (strlen($pwd)<$chrs)
	{
		$chr = chr(mt_rand (0,255));
		if (eregi("^[0-9a-z]$", $chr))
		$pwd = $pwd.$chr;
	};
	return strtolower($pwd);
}

function MDP_NUMERIC($chrs = LEN_CARDNUMBER){
	$pwd = ""  ;
	mt_srand ((double) microtime() * 1000000);
	while (strlen($pwd)<$chrs)
	{
		$chr = mt_rand (0,9);
		if (eregi("^[0-9]$", $chr))
		$pwd = $pwd.$chr;
	};
	return strtolower($pwd);
}


function MDP($chrs = LEN_CARDNUMBER){
	$pwd = ""  ;
	mt_srand ((double) microtime() * 1000000);
	while (strlen($pwd)<$chrs)
	{
		$chr = chr(mt_rand (0,255));
		if (eregi("^[0-9]$", $chr))
		$pwd = $pwd.$chr;
	};
	return $pwd;
}


function gen_card($table = "cc_card", $len = LEN_CARDNUMBER, $field="username"){

	$DBHandle_max  = DbConnect();
	for ($k=0;$k<=200;$k++){
		$card_gen = MDP($len);
		if ($k==200){ echo "ERROR : Impossible to generate a $field not yet used!<br>Perhaps check the LEN_CARDNUMBER (value:".LEN_CARDNUMBER.")";exit();}

		$query = "SELECT ".$field." FROM ".$table." where ".$field."='$card_gen'";
		$resmax = $DBHandle_max -> Execute($query);
		$numrow = 0;
		if ($resmax)
			$numrow = $resmax -> RecordCount( );

		if ($numrow!=0) continue;
		return $card_gen;
	}	
}


function gen_card_with_alias($table = "cc_card", $api=0, $length_cardnumber=LEN_CARDNUMBER){	

	$DBHandle_max  = DbConnect();
	for ($k=0;$k<=200;$k++){			
		$card_gen = MDP($length_cardnumber);
		$alias_gen = MDP(LEN_ALIASNUMBER);
		if ($k==200){ 
			if ($api){
				global $mail_content, $email_alarm, $logfile;
				mail($email_alarm, "ALARM : API (gen_card_with_alias - CODE_ERROR 8)", $mail_content);
				error_log ("[" . date("Y/m/d G:i:s", mktime()) . "] "."[gen_card_with_alias] - CODE_ERROR 8"."\n", 3, $logfile);
				echo("500 Internal server error");
				exit();
			}else{
				echo "ERROR : Impossible to generate a Cardnumber & Aliasnumber not yet used!<br>Perhaps check the LEN_CARDNUMBER  (value:".LEN_CARDNUMBER.") & LEN_ALIASNUMBER (value:".LEN_ALIASNUMBER.")";
				exit();
			}
		}

		$query = "SELECT username FROM ".$table." where username='$card_gen' OR useralias='$alias_gen'";
		$numrow = 0;
		$resmax = $DBHandle_max -> Execute($query);
		if ($resmax)
			$numrow = $resmax -> RecordCount( );

		if ($numrow!=0) continue;
		$arr_val [0] = $card_gen;
		$arr_val [1] = $alias_gen;
		return $arr_val;
	}	
}
		
//Get productID and all parameter and retrieve info for card creation into cc_ecommerce_product
function get_productinfo($DBHandle, $instance_table, $productid, $email_alarm, $mail_content, $logfile){

	global $FG_DEBUG;
	$QUERY = 'SELECT  
				product_name, creationdate, description, expirationdate, enableexpire, expiredays, credit, tariff, id_didgroup, activated, simultaccess, currency,
				typepaid, creditlimit, language, runservice, sip_friend, iax_friend, cc_ecommerce_product.mailtype, fromemail, fromname, subject, messagetext,
				messagehtml
			  FROM cc_ecommerce_product, cc_templatemail 
			  WHERE cc_ecommerce_product.mailtype=cc_templatemail.mailtype AND id='.$productid;
			  
	
	$result = $instance_table -> SQLExec ($DBHandle, $QUERY);		
	if ($FG_DEBUG>0){ echo "<br><b>$QUERY</b><br>"; print_r ($result); echo "<hr><br>"; }
	
		
	if( !is_array($result)){
		if ($FG_DEBUG > 0) echo ("get_productinfo ERROR");
		mail($email_alarm, "ALARM : API (CODE_ERROR get_productinfo)", $mail_content);
		error_log ("[" . date("Y/m/d G:i:s", mktime()) . "] "."CODE_ERROR get_productinfo"."\n", 3, $logfile);
		echo("500 Internal server error");
		exit();	
	}
	
	return $result[0];
	
}


// *********************************
//  ONLY USER BY THE OLD FRAME WORK 
// *********************************

$lang['strfirst']='&lt;&lt; First';
$lang['strprev']='&lt; Prev';
$lang['strnext']='Next &gt;';
$lang['strlast']='Last &gt;&gt;';

/**
* Do multi-page navigation.  Displays the prev, next and page options.
* @param $page the page currently viewed
* @param $pages the maximum number of pages
* @param $url the url to refer to with the page number inserted
* @param $max_width the number of pages to make available at any one time (default = 20)
*/
function printPages($page, $pages, $url, $max_width = 20) {
	global $lang;
	
	$window = 8;
	
	if ($page < 0 || $page > $pages) return;
	if ($pages < 0) return;
	if ($max_width <= 0) return;
	
	if ($pages > 1) {
		//echo "<center><p>\n";
		if ($page != 1) {
			$temp = str_replace('%s', 1-1, $url);
			echo "<a class=\"pagenav\" href=\"{$temp}\">{$lang['strfirst']}</a>\n";
			$temp = str_replace('%s', $page - 1-1, $url);
			echo "<a class=\"pagenav\" href=\"{$temp}\">{$lang['strprev']}</a>\n";
		}
	
		if ($page <= $window) {
			$min_page = 1;
			$max_page = min(2 * $window, $pages);
		}
		elseif ($page > $window && $pages >= $page + $window) {
			$min_page = ($page - $window) + 1;
			$max_page = $page + $window;
		}
		else {
			$min_page = ($page - (2 * $window - ($pages - $page))) + 1;
			$max_page = $pages;
		}

		// Make sure min_page is always at least 1
		// and max_page is never greater than $pages
		$min_page = max($min_page, 1);
		$max_page = min($max_page, $pages);
		
		for ($i = $min_page; $i <= $max_page; $i++) {
			$temp = str_replace('%s', $i-1, $url);
			if ($i != $page) echo "<a class=\"pagenav\" href=\"{$temp}\">$i</a>\n";
			else echo "$i\n";
		}
		if ($page != $pages) {
			$temp = str_replace('%s', $page + 1-1, $url);
			echo "<a class=\"pagenav\" href=\"{$temp}\">{$lang['strnext']}</a>\n";
			$temp = str_replace('%s', $pages-1, $url);
			echo "<a class=\"pagenav\" href=\"{$temp}\">{$lang['strlast']}</a>\n";
		}
	}
}


?>
