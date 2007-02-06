<?php

$FG_DEBUG=0;

if (!isset($_SERVER['HTTPS'])){
	if ($FG_DEBUG==0)
		header ("HTTP/1.0 401 Unauthorized");
	else
		echo "Unauthorized! no ssl!\n\n";
	die();
}

if ((!session_is_registered('pr_login') || !session_is_registered('pr_password') || !session_is_registered('rights') || (isset($_POST["done"]) && $_POST["done"]=="submit_log") )){

	if ($FG_DEBUG == 1) echo "<br>0. HERE WE ARE";
	
	if ($_POST["done"]=="submit_log"){
	
		$DBHandle  = DbConnect();
		
		if ($FG_DEBUG == 1) echo "<br>1. ".$_POST["pr_login"].$_POST["pr_password"];
		$_POST["pr_login"] = access_sanitize_data($_POST["pr_login"]);
		$_POST["pr_password"] = access_sanitize_data($_POST["pr_password"]);
		
		$return = login ($_POST["pr_login"], $_POST["pr_password"]);
		if ($FG_DEBUG == 1) print_r($return);
		if ($FG_DEBUG == 1) echo "==>".$return[1];
		if (!is_array($return) || $return[1]==0 ) {
			if ($FG_DEBUG==0){
				header ("HTTP/1.0 401 Unauthorized");
				header ("Location: $unsafe_base/index.php?error=1");
				}
			die();
		}	
		// if groupID egal 1, this user is a root
		if ($return[3]==0){
			$return = true;
			$rights = 65535;	
			
			$is_admin = 1;
			$pr_groupID = $return[3];
		}else{				
			$pr_reseller_ID = $return[0];
			$rights = $return[1];
			if ($return[3]==1) $is_admin=1;
			else $is_admin=0;
			
			if ($return[3] == 3) $pr_reseller_ID = $return[4];
			
			$pr_groupID = $return[3];			
		}		
		
		
		if ($_POST["pr_login"]){
		
			$pr_login = $_POST["pr_login"];
			$pr_password = $_POST["pr_password"];
			
			if ($FG_DEBUG == 1) echo "<br>3. $pr_login-$pr_password-$rights-$conf_addcust";			
			$_SESSION["pr_login"]=$pr_login;
			$_SESSION["pr_password"]=$pr_password;
			$_SESSION["rights"]=$rights;
			$_SESSION["is_admin"]=$is_admin;	
			$_SESSION["pr_reseller_ID"]=$pr_reseller_ID;
			$_SESSION["pr_groupID"]=$pr_groupID;
			
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
	$QUERY = "SELECT userid, perms, confaddcust, groupid FROM cc_ui_authen WHERE login = '".$user."' AND password = '".$pass."'";

	$res = $DBHandle -> query($QUERY);

	if (!$res) {
		$errstr = $DBHandle->ErrorMsg();
		return (false);
	}

	$row [] =$res -> fetchRow();

	return ($row[0]);
}

?>