<?php
	include (dirname(__FILE__)."/Class.A2Billing.php");
	require_once('adodb/adodb.inc.php'); // AdoDB
	include (dirname(__FILE__)."/Class.Table.php");
	
	$A2B = new A2Billing();
	
	// SELECT THE FILES TO LOAD THE CONFIGURATION
	$A2B -> load_conf($agi, AST_CONFIG_DIR."a2billing.conf", 1);	
	
	
	// DEFINE FOR THE DATABASE CONNECTION
	define ("HOST", isset($A2B->config['database']['hostname'])?$A2B->config['database']['hostname']:null);
	define ("PORT", isset($A2B->config['database']['port'])?$A2B->config['database']['port']:null);
	define ("USER", isset($A2B->config['database']['user'])?$A2B->config['database']['user']:null);
	define ("PASS", isset($A2B->config['database']['password'])?$A2B->config['database']['password']:null);
	define ("DBNAME", isset($A2B->config['database']['dbname'])?$A2B->config['database']['dbname']:null);
	define ("DB_TYPE", isset($A2B->config['database']['dbtype'])?$A2B->config['database']['dbtype']:null); 	
	
	define ("LEN_ALIASNUMBER", isset($A2B->config['global']['len_aliasnumber'])?$A2B->config['global']['len_aliasnumber']:null);
	define ("LEN_VOUCHER", isset($A2B->config['global']['len_voucher'])?$A2B->config['global']['len_voucher']:null);
	define ("BASE_CURRENCY", isset($A2B->config['global']['base_currency'])?$A2B->config['global']['base_currency']:null);
	
	define ("BUDDY_SIP_FILE", isset($A2B->config['webui']['buddy_sip_file'])?$A2B->config['webui']['buddy_sip_file']:null);
	define ("BUDDY_IAX_FILE", isset($A2B->config['webui']['buddy_iax_file'])?$A2B->config['webui']['buddy_iax_file']:null);
	define ("API_SECURITY_KEY", isset($A2B->config['webui']['api_security_key'])?$A2B->config['webui']['api_security_key']:null);
	
	// WEB DEFINE FROM THE A2BILLING.CONF FILE
	define ("EMAIL_ADMIN", isset($A2B->config['webui']['email_admin'])?$A2B->config['webui']['email_admin']:null);
	define ("NUM_MUSICONHOLD_CLASS", isset($A2B->config['webui']['num_musiconhold_class'])?$A2B->config['webui']['num_musiconhold_class']:null);
	define ("MANAGER_HOST", isset($A2B->config['webui']['manager_host'])?$A2B->config['webui']['manager_host']:null);
	define ("MANAGER_USERNAME", isset($A2B->config['webui']['manager_username'])?$A2B->config['webui']['manager_username']:null);
	define ("MANAGER_SECRET", isset($A2B->config['webui']['manager_secret'])?$A2B->config['webui']['manager_secret']:null);
	define ("SHOW_HELP", isset($A2B->config['webui']['show_help'])?$A2B->config['webui']['show_help']:null);	
	define ("MY_MAX_FILE_SIZE_IMPORT", isset($A2B->config['webui']['my_max_file_size_import'])?$A2B->config['webui']['my_max_file_size_import']:null);
	define ("DIR_STORE_MOHMP3",isset($A2B->config['webui']['dir_store_mohmp3'])?$A2B->config['webui']['dir_store_mohmp3']:null);
	define ("DIR_STORE_AUDIO", isset($A2B->config['webui']['dir_store_audio'])?$A2B->config['webui']['dir_store_audio']:null);
	define ("MY_MAX_FILE_SIZE_AUDIO", isset($A2B->config['webui']['my_max_file_size_audio'])?$A2B->config['webui']['my_max_file_size_audio']:null);
	$file_ext_allow = is_array($A2B->config['webui']['file_ext_allow'])?$A2B->config['webui']['file_ext_allow']:null;
	$file_ext_allow_musiconhold = is_array($A2B->config['webui']['file_ext_allow_musiconhold'])?$A2B->config['webui']['file_ext_allow_musiconhold']:null;
	define ("LINK_AUDIO_FILE", isset($A2B->config['webui']['link_audio_file'])?$A2B->config['webui']['link_audio_file']:null);
	define ("MONITOR_PATH", isset($A2B->config['webui']['monitor_path'])?$A2B->config['webui']['monitor_path']:null);
	define ("MONITOR_FORMATFILE", isset($A2B->config['webui']['monitor_formatfile'])?$A2B->config['webui']['monitor_formatfile']:null); 
	define ("SHOW_ICON_INVOICE", isset($A2B->config['webui']['show_icon_invoice'])?$A2B->config['webui']['show_icon_invoice']:null);
	define ("SHOW_TOP_FRAME", isset($A2B->config['webui']['show_top_frame'])?$A2B->config['webui']['show_top_frame']:null);
	define ("ADVANCED_MODE", isset($A2B->config['webui']['advanced_mode'])?$A2B->config['webui']['advanced_mode']:null);
	define ("CURRENCY_CHOOSE", isset($A2B->config['webui']['currency_choose'])?$A2B->config['webui']['currency_choose']:null);
	
	// PAYPAL	
	define ("PAYPAL_EMAIL", isset($A2B->config['paypal']['paypal_email'])?$A2B->config['paypal']['paypal_email']:null);
	define ("PAYPAL_FROM_EMAIL",isset( $A2B->config['paypal']['from_email'])?$A2B->config['paypal']['from_email']:null);
	define ("PAYPAL_FROM_NAME", isset($A2B->config['paypal']['from_name'])?$A2B->config['paypal']['from_name']:null);
	define ("PAYPAL_COMPANY_NAME", isset($A2B->config['paypal']['company_name'])?$A2B->config['paypal']['company_name']:null);
	define ("PAYPAL_ERROR_EMAIL", isset($A2B->config['paypal']['error_email'])?$A2B->config['paypal']['error_email']:null);
	define ("PAYPAL_ITEM_NAME", isset($A2B->config['paypal']['item_name'])?$A2B->config['paypal']['item_name']:null);
	define ("PAYPAL_CURRENCY_CODE", isset($A2B->config['paypal']['currency_code'])?$A2B->config['paypal']['currency_code']:null);
	define ("PAYPAL_NOTIFY_URL", isset($A2B->config['paypal']['notify_url'])?$A2B->config['paypal']['notify_url']:null);
	define ("PAYPAL_PURCHASE_AMOUNT", isset($A2B->config['paypal']['purchase_amount'])?$A2B->config['paypal']['purchase_amount']:null);
	define ("PAYPAL_FEES", isset($A2B->config['paypal']['paypal_fees'])?$A2B->config['paypal']['paypal_fees']:null); 
	

	// BACKUP
	define ("BACKUP_PATH", isset($A2B->config['backup']['backup_path'])?$A2B->config['backup']['backup_path']:null);
	define ("GZIP_EXE", isset($A2B->config['backup']['gzip_exe'])?$A2B->config['backup']['gzip_exe']:null);
	define ("GUNZIP_EXE", isset($A2B->config['backup']['gunzip_exe'])?$A2B->config['backup']['gunzip_exe']:null);
	define ("MYSQLDUMP", isset($A2B->config['backup']['mysqldump'])?$A2B->config['backup']['mysqldump']:null);
	define ("PG_DUMP", isset($A2B->config['backup']['pg_dump'])?$A2B->config['backup']['pg_dump']:null);
	define ("MYSQL", isset($A2B->config['backup']['mysql'])?$A2B->config['backup']['mysql']:null);
	define ("PSQL", isset($A2B->config['backup']['psql'])?$A2B->config['backup']['psql']:null);
	
	// SIP IAX FRIEND CREATION
	define ("FRIEND_TYPE", isset($A2B->config['peer_friend']['type'])?$A2B->config['peer_friend']['type']:null);
	define ("FRIEND_ALLOW", isset($A2B->config['peer_friend']['allow'])?$A2B->config['peer_friend']['allow']:null);
	define ("FRIEND_CONTEXT", isset($A2B->config['peer_friend']['context'])?$A2B->config['peer_friend']['context']:null);
	define ("FRIEND_NAT", isset($A2B->config['peer_friend']['nat'])?$A2B->config['peer_friend']['nat']:null);
	define ("FRIEND_AMAFLAGS", isset($A2B->config['peer_friend']['amaflags'])?$A2B->config['peer_friend']['amaflags']:null);
	define ("FRIEND_QUALIFY", isset($A2B->config['peer_friend']['qualify'])?$A2B->config['peer_friend']['qualify']:null);
	define ("FRIEND_HOST", isset($A2B->config['peer_friend']['host'])?$A2B->config['peer_friend']['host']:null);
	define ("FRIEND_DTMFMODE", isset($A2B->config['peer_friend']['dtmfmode'])?$A2B->config['peer_friend']['dtmfmode']:null);

	// INCLUDE FILES
	define ("FSROOT", substr(dirname(__FILE__),0,-3));
	define ("LIBDIR", FSROOT."lib/");	
	include (FSROOT."lib/Misc.php");
	
	
	/*
	 *		GLOBAL USED VARIABLE
	 */
	$PHP_SELF = $_SERVER["PHP_SELF"];
	

	$CURRENT_DATETIME = date("Y-m-d H:i:s");		
		
	/*
	 *		GLOBAL POST/GET VARIABLE
	 */		 
	getpost_ifset(array('form_action', 'atmenu', 'action', 'stitle', 'sub_action', 'IDmanager', 'current_page', 'order', 'sens', 'mydisplaylimit', 'filterprefix', 'cssname', 'popup_select'));

	/*
	 *		CONNECT / DISCONNECT DATABASE
	 */


     session_start();

    if(ini_get('register_globals'))
    {
        foreach($_REQUEST as $key => $value)
        {
            $$key = $value;
        }
    }

    if (!isset($_SESSION["language"]))
    {
        $_SESSION["language"]='english';
    }
    else if (isset($language))
    {
      $_SESSION["language"] = $language;
    }
    define ("LANGUAGE",$_SESSION["language"]);
    require("languageSettings.php");
    SetLocalLanguage();
	 
	function DbConnect($db= NULL)
	{
		if (DB_TYPE == "postgres"){
			$datasource = 'pgsql://'.USER.':'.PASS.'@'.HOST.'/'.DBNAME;
		}else{
			$datasource = 'mysql://'.USER.':'.PASS.'@'.HOST.'/'.DBNAME;
		}
		
		$DBHandle = NewADOConnection($datasource);

		if (!$DBHandle) die("Connection failed");

		return $DBHandle;
	}
	
	
	function DbDisconnect($DBHandle)
	{
		$DBHandle ->disconnect();
	}


	function get_currencies()
	{
		$handle = DbConnect();
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
			for ($i=0;$i<$num_cur;$i++)
				$currencies_list[$result[$i][1]] = array (1 => $result[$i][2], 2 => $result[$i][3]);
		}	
		
		if ((isset($currencies_list)) && (is_array($currencies_list)))	sort_currencies_list($currencies_list);		
		
		return $currencies_list;
	}
	
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
	getpost_ifset(array('cssname'));
		
	if(isset($cssname) && $cssname != "")
	{
		$_SESSION["stylefile"] = $cssname;
	}
	
	
	if(isset($cssname) && $cssname != "")
	{
		if ($_SESSION["stylefile"]!=$cssname){
			foreach (glob("./templates_c/*.*") as $filename)
			{
				unlink($filename);
			}			
		}
		$_SESSION["stylefile"] = $cssname;		
	}
	
	if(!isset($_SESSION["stylefile"]) || $_SESSION["stylefile"]==''){
		$_SESSION["stylefile"]='default';
	}
	
    //Images Path
    define ("Images_Path","../Public/templates/".$_SESSION["stylefile"]."/images");
	define ("Images_Path_Main","../Public/templates/".$_SESSION["stylefile"]."/images");
	define ("KICON_PATH","../Public/templates/".$_SESSION["stylefile"]."/images/kicons");
	
	define ("WEBUI_DATE", 'Release : Somewhere in March 2007');	 
	define ("WEBUI_VERSION", 'Asterisk2Billing - Version 1.3 - Beta (Yellowjacket)');
	
	//Enable Disable Captcha
	define ("CAPTCHA_ENABLE", isset($A2B->config["signup"]['enable_captcha'])?$A2B->config["signup"]['enable_captcha']:0);
	
	

	include (FSROOT."lib/help.php");
	
	
?>
