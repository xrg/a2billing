<?php

$FG_DEBUG=0;
require_once("./lib/Class.A2Billing.inc.php");
require_once("./lib/Class.Logger.inc.php");

if ($_SERVER['SERVER_NAME'] != 'localhost' && $_SERVER['SERVER_ADDR'] != '127.0.0.1'
	&& !isset($_SERVER['HTTPS'])){
	if ($FG_DEBUG==0)
		header ("HTTP/1.0 401 Unauthorized");
	else
		echo "Unauthorized! no ssl!\n\n";
	die();
}

if ((!isset($_SESSION['pr_login']) || !isset($_SESSION['pr_password']) || 
	!isset($_SESSION['rights']) || (isset($_POST["done"]) && $_POST["done"]=="submit_log") )){

	if ($FG_DEBUG == 1) echo "<br>0. HERE WE ARE";
	
	if ($_POST["done"]=="submit_log"){
	
		$DBHandle  = A2Billing::DBHandle();
		
		if ($FG_DEBUG == 1) echo "<br>1. ".$_POST["pr_login"].$_POST["pr_password"];
		$_POST["pr_login"] = access_sanitize_data($_POST["pr_login"]);
		$_POST["pr_password"] = access_sanitize_data($_POST["pr_password"]);
		
		$return = login ($_POST["pr_login"], $_POST["pr_password"]);
		if ($FG_DEBUG == 1) print_r($return);
		if ($FG_DEBUG == 1) echo "==>".$return['perms'];
		if (!is_array($return) || $return['perms']==0 ) {
			if ($FG_DEBUG==0){
				header ("HTTP/1.0 401 Unauthorized");
				header ("Location: $unsafe_base/index.php?error=1");
				}
			die();
		}	
		// if groupID egal 1, this user is a root
		if ($return['groupid']==0){
			//$return = true;
			$rights = ACX_ROOT;	
			
			$is_admin = 1;
			$pr_groupID = $return['groupid'];
			$admin_id = $return['userid'];
			
		}else{
			$pr_reseller_ID = $return['userid'];
			$rights = $return['perms'];
			if ($return['groupid']==1)
				$is_admin=1;
			else
				$is_admin=0;
			
			//if ($return['groupid'] == 3) $pr_reseller_ID = $return[4];
			
			$pr_groupID = $return['groupid'];
		}
		
		
		if ($_POST["pr_login"]){
		
			$pr_login = $_POST["pr_login"];
			$pr_password = $_POST["pr_password"];
			
			if ($FG_DEBUG == 1) echo "<br>3. $pr_login-$pr_password-$rights-$conf_addcust";
			$_SESSION["pr_login"]=$pr_login;
			$_SESSION["pr_password"]=$pr_password;
			$_SESSION['pr_userid']=$return['userid'];
			$_SESSION["rights"]=$rights;
			$_SESSION["is_admin"]=$is_admin;
			//$_SESSION["pr_reseller_ID"]=$pr_reseller_ID;
			$_SESSION["pr_groupID"]=$pr_groupID;
			$_SESSION["admin_id"] = $admin_id; // *-* must go..
			$_SESSION['readonly'] = (($return['readonly'] == 't') ||
							($return['readonly'] == '1')) ;
			
			$log = new Logger();
			$log -> insertLog($return['userid'], 1, "User Logged In", "User Logged in to website", '', $_SERVER['REMOTE_ADDR'], 'PP_Intro.php','');
			$log = null;

		}
		
	}else{
		$rights=0;
		
	}	
	
}


// 					FUNCTIONS
//////////////////////////////////////////////////////////////////////////////

function login ($user, $pass) { 
	global $DBHandle;
	
	if (strlen($user)>20 || strlen($pass)>20) return false;
	$QUERY = "SELECT userid, perms, confaddcust, groupid, readonly FROM cc_ui_authen WHERE login = '".$user."' AND password = '".$pass."'";

// 	error_log($QUERY);
	$res = $DBHandle -> query($QUERY);

	if (!$res) {
		$errstr = $DBHandle->ErrorMsg();
		error_log($errstr);
		return (false);
	}

	$row [] =$res -> fetchRow();

	return ($row[0]);
}

?>