<?php

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
		
		function display_money($value, $currency = BASE_CURRENCY){
			echo $value.' '.$currency;
		}
		
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
		
		function display_2bill($var, $currency = BASE_CURRENCY){	
				global $currencies_list, $choose_currency;
				if (isset($choose_currency) && strlen($choose_currency)==3) $currency=$choose_currency;
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
		
		function linkonmonitorfile($value){
					  
		   $myfile = $value.".".MONITOR_FORMATFILE;
		   $myfile = base64_encode($myfile);
		   echo "<a target=_blank href=\"call-log-customers.php?download=file&file=".$myfile."\">";
		   echo '<img src="./images/stock-mic.png" height="18" /></a>';
		   
		}



$lang['strfirst']=gettext('&lt;&lt; First');
$lang['strprev']=gettext('&lt; Prev');
$lang['strnext']=gettext('Next &gt;');
$lang['strlast']=gettext('Last &gt;&gt;');

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
				//echo "</p></center>\n";
			}
		}
		
		
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
		
	
		if ((isset($currencies_list)) && (is_array($currencies_list)))  sort_currencies_list($currencies_list);

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
				}else {
					if ($v == null) $v = '';
					$resstr .= $dbh->Quote($v);
				}
			}else
				$resstr .= $str[$strp];
			$stro=$strp+1;
		}while ($stro<$strlen);
			
		return $resstr;
	}

	function get_config($group, $field, $default_v = null) {
		if (isset($A2B->config[$group][$field]))
			return $A2B->config[$group][$field];
		else	return $default_v ;
	}
	
	// Couldn't this be done in css?
	function str_dblspace($str){
		$ret ='';
		$ret= "<span style=\"letter-spacing: 4px;\">" . $str .
			"</span>";
		return $ret; 
	}
?>
