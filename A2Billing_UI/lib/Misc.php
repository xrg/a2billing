<?php
/***************************************************************************
 *            Misc.php
 *
 *  GPL : Belaid Arezqui
 *  Email : areski _atl_ gmail
 ****************************************************************************/



/* 
 * get_currencies 
 */
function get_currencies($handle = null)
{
	if (empty($handle)){
		$handle = DbConnect();
	}
	$instance_table = new Table();
	$QUERY =  "SELECT id,currency,name,value from cc_currencies order by id";
	$result = $instance_table -> SQLExec ($handle, $QUERY);
	/*
		$currencies_list['ADF'][1]="Andorran Franc";
		$currencies_list['ADF'][2]="0.1339";
		[ADF] => Array ( [1] => Andorran Franc (ADF), [2] => 0.1339 )
	*/

	if (is_array($result)){
		$num_cur = count($result);
		for ($i=0;$i<$num_cur;$i++){
			$currencies_list[$result[$i][1]] = array (1 => $result[$i][2], 2 => $result[$i][3]);
		}
	}
	
	if ((isset($currencies_list)) && (is_array($currencies_list)))	sort_currencies_list($currencies_list);		
	
	return $currencies_list;
}

/**
* Do Currency Conversion. 
* @param $currencies_list the List of currencies.
* @param $amount the amount to be converted.
* @param $from_cur Source Currency
* @param $to_cur Destination Currecny
*/
function convert_currency ($currencies_list, $amount, $from_cur, $to_cur){
	if (!is_numeric($amount) || ($amount == 0)){
		return 0;
	}
	if ($from_cur == $to_cur){
		return $amount;
	}
	// EUR -> 1.19175 : MAD -> 0.10897		
	// FROM -> 2 - TO -> 0.5 =>>>> multiply 4
	$mycur_tobase = $currencies_list[strtoupper($from_cur)][2];		
	$mycur = $currencies_list[strtoupper($to_cur)][2];
	if ($mycur == 0) return 0;
	$amount = $amount * ($mycur_tobase / $mycur);
	// echo "\n \n AMOUNT CONVERTED IN NEW CURRENCY $to_cur -> VALUE =".$amount;
	return $amount;
}


/* 
 * sort_currencies_list
 */
function sort_currencies_list(&$currencies_list){
	$first_array = array (strtoupper(BASE_CURRENCY), 'USD', 'EUR','GBP','AUD','HKD', 'JPY', 'NZD', 'SGD', 'TWD', 'PLN', 'SEK', 'DKK', 'CHF', 'COP', 'MXN', 'CLP');		
	foreach ($first_array as $element_first_array){
		if (isset($currencies_list[$element_first_array])){	
			$currencies_list2[$element_first_array]=$currencies_list[$element_first_array];
			unset($currencies_list[$element_first_array]);
		}
	}
	$currencies_list = array_merge($currencies_list2,$currencies_list);		
}


/* 
 * Write log into file 
 */
function write_log($logfile, $output){
	if (strlen($logfile) > 1){
		$string_log = "[".date("d/m/Y H:i:s")."]:[$output]\n";
		error_log ($string_log."\n", 3, $logfile);
	}
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

/** The opposite of getpost_ifset: create an array with those post vars
	@param arr Array of ("var name", ...)
	@param empty_null If true, treat empty vars as null
	@return array( var => val, ...)*/

function putpost_arr($test_vars, $empty_null = false){
	$ret = array();
	if (!is_array($test_vars)) {
		$test_vars = array($test_vars);
	}
	foreach($test_vars as $test_var) {
		global $$test_var;
		if (isset($$test_var) && ($$test_var != null) &&
			((!$empty_null) || $$test_var != '') )
			$ret[$test_var] = $$test_var;
	}
	return $ret;
}

/** Convert params in array to url string
   @param arr An array like (var1 => value1, ...)
   @return A url like var1=value1
   */
function arr2url ($arr) {
	if (!is_array($arr))
		return;
	$rar = array();
	foreach($arr as $key => $value) {
		if ($value == null)
			continue;
		$rar[] = "$key" . '=' . rawurlencode($value);
	}
	return implode('&',$rar);
}

/** Generate an html combo, with selected values etc. */
function gen_Combo($name, $value, $option_array){
	?> <select name="<?= $name?>" size="1" class="form_input_select">
	<?php
		foreach($option_array as $option){ ?>
		<option value="<?= $option[0] ?>"<?php if ($value == $option[0]) echo ' selected'; ?>><?= htmlspecialchars($option[1])?></option>
	<?php	}
	?>
	</select>
	<?php
	
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
	if ($mydate != "")
	{
		echo date("m/d/Y", strtotime($mydate));
	}
	else
	{
		echo $mydate;
	}
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
			  	echo "Old !!" ;
	
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
/**
* Validate the Uploaded Files.  Return the error string if any.
* @param $the_file the file to validate
* @param $the_file_type the file type
*/
function validate_upload($the_file, $the_file_type) {

	$registered_types = array(
                                        "application/x-gzip-compressed"         => ".tar.gz, .tgz",
                                        "application/x-zip-compressed"          => ".zip",
                                        "application/x-tar"                     => ".tar",
                                        "text/plain"                            => ".html, .php, .txt, .inc (etc)",
                                        "image/bmp"                             => ".bmp, .ico",
                                        "image/gif"                             => ".gif",
                                        "image/pjpeg"                           => ".jpg, .jpeg",
                                        "image/jpeg"                            => ".jpg, .jpeg",
                                        "image/png"                             => ".png",
                                        "application/x-shockwave-flash"         => ".swf",
                                        "application/msword"                    => ".doc",
                                        "application/vnd.ms-excel"              => ".xls",
                                        "application/octet-stream"              => ".exe, .fla (etc)",
										"text/x-comma-separated-values"			=> ".csv"
                                        ); # these are only a few examples, you can find many more!

	$allowed_types = array("text/plain", "text/x-comma-separated-values");


	$start_error = "\n<b>ERROR:</b>\n<ul>";
	$error = "";
	if ($the_file=="")
	{		
		$error .= "\n<li>".gettext("File size is greater than allowed limit.")."\n<ul>";
	}else
	{
        if ($the_file == "none") { 
                $error .= "\n<li>".gettext("You did not upload anything!")."</li>";
        }
        elseif ($_FILES['the_file']['size'] == 0)
        {
        	$error .= "\n<li>".gettext("Failed to upload the file, The file you uploaded may not exist on disk.")."!</li>";
        } 
        else        
        {
 			if (!in_array($the_file_type,$allowed_types))
 			{
 				$error .= "\n<li>".gettext("file type is not allowed")."\n<ul>";
                while ($type = current($allowed_types))
                {
                    $error .= "\n<li>" . $registered_types[$type] . " (" . $type . ")</li>";
                	next($allowed_types);
                }
                $error .= "\n</ul>";
            }                
        }
	}
	if ($error)
	{
		$error = $start_error . $error . "\n</ul>";
        return $error;
    }
    else 
    {
    	return false;
    }

} # END validate_upload


/** Calculate arguments in a string of the form "Test %1 or %4 .." 
	This function is carefully written, so that it could be used securely, for
	example, when 'eval(string_param(" echo %&0",array( $dangerous_str)))' is called.
	That is, we have some special prefixes:
		%#x means the x-th parameter as a number, 0 if nan
		%&x means the x-th parameter as a quoted string
		%% will become '%', as will %X where X not [1-9a-z]
	@param $str The input string
	@param $parm_arr An array with the parameters, so %1 will become $parm_arr[1]
	@param $noffset	The offset of the param. noffset=1 means %1 = $parm_arr[0],
			noffset=-2 means %0 = $parm_arr[2]
	@note This fn won't work for more than 10 params!	
*/

function str_params($str, $parm_arr, $noffset = 0){
	$strlen=strlen($str);
	$strp=0;
	$stro=0;
	$resstr='';
	do{
		$strp=strpos($str,"%",$stro);
		if($strp===false){
			$resstr=$resstr . substr($str,$stro);
			break;
		}
		$resstr=$resstr . substr($str,$stro,$strp-$stro);
		$strp++;
		if ($strp>=$strlen)
			break;
		$sm=0;
		if ($str[$strp] == '#'){
			$sm=1;
			$strp++;
		}
		else if ($str[$strp] =='&'){
			$sm=2;
			$strp++;
		}
		if (( $str[$strp]>='0')  && ( $str[$strp]<='9')){
			$pv=$str{$strp} - '0';
// 			echo "Var %$pv\n";
			if (isset($parm_arr[$pv - $noffset]))
				$v = $parm_arr[$pv - $noffset];
			else	$v = '';
			if ($sm==1)
				$v = (integer) $v;
			else if ($sm == 2)
				$v = addslashes($v);
			
			$resstr= $resstr . $v;
		}else
			$resstr= $resstr . $str[$strp];
		$stro=$strp+1;
	}while ($stro<$strlen);
		
	return $resstr;
}

/** Calculate arguments in a string of the form "Test %1 or %4 .." 
	This function is intended for database usage:
	eg. str_dbparams(dbh,"SELECT %1 , %2 ; ", array("me", "'DROP DATABASE sql_inject;'"));
	 will result in "SELECT 'me', '''DROP DATABASE sql_inject;''' ;" which is safe!
	 %#x means the x-th parameter as a number, 0 if nan
	Additionaly, parms in the form %!3 will result in "NULL" when parm is empty.
	
	@param $str The input string, say, the sql command
	@param $parm_arr An array with the parameters, so %1 will become $parm_arr[0]
	@param $dbh the db handle
	@note This fn won't work for more than 10 params!	
*/

function str_dbparams($dbh, $str, $parm_arr){
	$strlen=strlen($str);
	$strp=0;
	$stro=0;
	$resstr='';
	do{
		$strp=strpos($str,"%",$stro);
		if($strp===false){
			$resstr=$resstr . substr($str,$stro);
			break;
		}
		$resstr=$resstr . substr($str,$stro,$strp-$stro);
		$strp++;
		if ($strp>=$strlen)
			break;
		$sm=0;
		if ($str[$strp] == '!'){
			$sm=1;
			$strp++;
		}else
		if ($str[$strp] == '#'){
			$sm=2;
			$strp++;
		}
		if (( $str[$strp]>='0')  && ( $str[$strp]<='9')){
			$pv=$str{$strp} - '0';
// 			echo "Var %$pv\n";
			$v= null;
			if (isset($parm_arr[$pv - 1]))
				$v = $parm_arr[$pv - 1];
			if ($sm==1) {
				if ($v == '') $v = null;
				if ($v == null)
					$resstr .= 'NULL';
				else
					$resstr .= $dbh->Quote($v);
			} else if ($sm ==2) {
				if ($v == '') 
					$v = null;
				$v = (integer) $v;
				if ($v == null)
					$resstr .= '0';
				else
					$resstr .= $v;
			}
			else {
				if ($v == null) $v = '';
				$resstr .= $dbh->Quote($v);
			}
		}else
			$resstr .= $str[$strp];
		$stro=$strp+1;
	}while ($stro<$strlen);
		
	return $resstr;
}

/** For code clarity only: it will produce the string for an &lt;acronym&gt; element
		@param acr   The acronym, the short one
		@param title the explanation (usually a hint)
*/
function acronym($acr, $title){
	$res ="<acronym title=\"";
	$res .= $title;
	$res .= "\" >";
	$res .= $acr;
	$res .= "</acronym>";
	return $res;
}
?>
