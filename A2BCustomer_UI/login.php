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

	if ($FG_DEBUG > 1) echo "<br>0. HERE WE ARE";

	if ($_POST["done"]=="submit_log"){

		if ($FG_DEBUG == 1) echo "<br>1. ".$_POST["pr_login"].$_POST["pr_password"];
		$_POST["pr_login"] = access_sanitize_data($_POST["pr_login"]);
		$_POST["pr_password"] = access_sanitize_data($_POST["pr_password"]);

		$return = login ($_POST["pr_login"], $_POST["pr_password"]);
		if ($FG_DEBUG >= 1)
			echo "Return from login(): ". print_r($return,true)."<br>\n";
		

		if (!is_array($return))
        	{
			sleep(2);
			header ("HTTP/1.0 401 Unauthorized");
			if ($FG_DEBUG)
				die(); //early, leave messages on page.
			if(is_int($return))
			{
				if($return == -1)
					Header ("Location: $unsafe_base/index.php?error=3");
				else
					Header ("Location: $unsafe_base/index.php?error=2");
        		}
			else
			{
				Header ("Location: $unsafe_base/index.php?error=1");
			}
			die();
		}

		$cus_rights = 1;

		if ($_POST["pr_login"]){
			$pr_login = $return[0]; //$_POST["pr_login"];
			$pr_password = $_POST["pr_password"];

			if ($FG_DEBUG == 1) echo "<br>3. $pr_login-$pr_password-$cus_rights";
			$_SESSION["pr_login"]=$pr_login;
			$_SESSION["pr_password"]=$pr_password;
			$_SESSION["cus_rights"]=$cus_rights;
			$_SESSION["card_id"]=$return[3];
			$_SESSION["id_didgroup"]=$return[4];
			$_SESSION["tariff"]=$return[5];
			$_SESSION["vat"]=$return[6];
		}

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

	$QUERY = str_dbparams($DBHandle,"SELECT username, credit, activated, id, id_didgroup, tariff, vat, activatedbyuser FROM cc_card WHERE (email = %1 OR useralias = %1) AND uipass = %2" , array($user,$pass));
	$res = $DBHandle -> Execute($QUERY);

	if (!$res) {
		$errstr = $DBHandle->ErrorMsg();
		if ($FG_DEBUG)
			echo $errstr."<br>\n";
		return (false);
	}
	if ($res->EOF){
		// no such user!
		return false;
	}

	$row [] =$res -> fetchRow();

	if( $row [0][2] != "t" && $row [0][2] != "1" ) {
		return -1;
	}
	
    if( ACTIVATEDBYUSER==1 && $row [0][7] != "t" && $row [0][7] != "1" ) {
		return -2;
	}

	return ($row[0]);
}
//include (dirname(__FILE__)."/../lib/company_info.php");

header ("Location: $unsafe_base/index2.php");
?>
