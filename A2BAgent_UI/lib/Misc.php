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

?>
