<?php
require ("./lib/defines.php");
require ("./lib/module.access.php");

$server= $_SERVER['SERVER_NAME'];
$self_uri=$_SERVER['PHP_SELF'];
$unsafe_base="http://".$server . dirname($self_uri);

$FG_DEBUG=2;

function access_sanitize_data($data)
{
	$lowerdata = strtolower ($data);
	$data = str_replace('--', '', $data);	
	$data = str_replace("'", '', $data);
	$data = str_replace('=', '', $data);
	$data = str_replace(';', '', $data);
	if (!(strpos($lowerdata, ' or ')===FALSE)){ return false;}
	if (!(strpos($lowerdata, 'table')===FALSE)){ return false;}

	return $data;
}

if (!isset($_SERVER['HTTPS'])){
	if ($FG_DEBUG==0)
		header ("HTTP/1.0 401 Unauthorized");
	else
		echo "Unauthorized! no ssl!\n\n";
		die();
}

	if (! isset($unsafe_base))
		$unsafe_base=".";
		
if (!session_is_registered('pr_login') || !session_is_registered('pr_password') || 
	!session_is_registered('cus_rights') || 
	(isset($_POST["done"]) && $_POST["done"]=="submit_log") ){

	if ($FG_DEBUG > 1) echo "Login attempt:<br>";

	if ($_POST["done"]=="submit_log"){

		$return = login( access_sanitize_data($_POST["pr_login"]),
				access_sanitize_data($_POST["pr_password"]));
		
		if ($FG_DEBUG >= 1)
			echo "Return from login(): ". print_r($return,true)."<br>\n";
		

		if (!is_array($return))
        	{
			sleep(2);
			header ("HTTP/1.0 401 Unauthorized");
			if ($FG_DEBUG)
				die(); //early, leave messages on page.
			$err=4;
			if(is_int($return))
				$err=$return;
			Header ("Location: $unsafe_base/index.php?error=$err");
			die();
		}

		$cus_rights = 1;

		$_SESSION["cus_rights"]=1;
		$_SESSION['pr_login'] = $return['username'];
		$_SESSION['pr_status'] = $return['status'];
		$_SESSION['card_id'] = $return['id'];
		$_SESSION['lang_db'] = $return['language'];
		$_SESSION['card_grp'] = $return['grp'];
		$_SESSION['currency'] = $return['currency'];

	}else{
		$_SESSION["cus_rights"]=0;

	}

}


// Functions
function login ($user, $pass) {
	global $FG_DEBUG;
	
	$DBHandle = A2Billing::DBHandle();
	$user = trim($user);
	$pass = trim($pass);
	if (strlen($user)==0 || strlen($user)>=50 || strlen($pass)==0 || strlen($pass)>=50)
		return false;

	$nameclause ="";
	if (DynConf::GetCfg(CUSTOMER_CFG,'username_login',true))
		$nameclause = "username = %1";
	
	if (DynConf::GetCfg(CUSTOMER_CFG,'useralias_login',false)){
		if (!empty($nameclause))
			$nameclause .= ' OR ';
		$nameclause .= "useralias = %1";
	}
	
	if (DynConf::GetCfg(CUSTOMER_CFG,'email_login',false)){
		if (!empty($nameclause))
			$nameclause .= ' OR ';
		$nameclause .= "email = %1";
	}
	if (($cgrp = DynConf::GetCfg(CUSTOMER_CFG,'cardgroup_only',null))!=null)
		$group_clause = ' AND grp = %#3';

	$QUERY = str_dbparams($DBHandle,"SELECT id, username, status, currency, grp, language
		 FROM cc_card WHERE ($nameclause) AND userpass = %2 $group_clause ;" ,
		 array($user,$pass,$cgrp));
	$res = $DBHandle -> Execute($QUERY);

	if (!$res) {
		$errstr = $DBHandle->ErrorMsg();
		if ($FG_DEBUG)
			echo $errstr."<br>\n";
		return 4;
	}
	if ($res->EOF){
		// no such user!
		if ($FG_DEBUG>1)
			echo "Query: $QUERY <br>";
		return 1;
	}

	$row =$res -> fetchRow();

	if ($row['status'] != 1)
		return 0- intval( $row['status']);
	
//     if( ACTIVATEDBYUSER==1 && $row [0][7] != "t" && $row [0][7] != "1" ) {
// 		return -2;
// 	}

	return ($row);
}

//include (dirname(__FILE__)."/../lib/company_info.php");

header ("Location: $unsafe_base/index2.php");
?>
