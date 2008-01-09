<?php
require_once("./lib/defines.php");

$server= $_SERVER['SERVER_NAME'];
$self_uri=$_SERVER['PHP_SELF'];
$unsafe_base="http://".$server . dirname($self_uri);

require("./lib/module.access.php");

$FG_DEBUG=0;
require_once(DIR_COMMON."Class.A2Billing.inc.php");

function access_sanitize_data($data){
	$lowerdata = strtolower ($data);
	$data = str_replace('--', '', $data);	
	$data = str_replace("'", '', $data);
	$data = str_replace('=', '', $data);
	$data = str_replace(';', '', $data);
	if (!(strpos($lowerdata, ' or ')===FALSE)){ return false;}
	if (!(strpos($lowerdata, 'table')===FALSE)){ return false;}

	return $data;
}

if ((!session_is_registered('pr_login') ||  !session_is_registered('pr_password') 
	|| !session_is_registered('cus_rights') 
	|| (isset($_POST["done"]) && $_POST["done"]=="submit_log"))){

	if (!isset($_SERVER['HTTPS'])){
		header ("HTTP/1.0 401 Unauthorized");
		header ("Location: index.php?error=1");
		trigger_error("Unauthorized! no ssl!",E_USER_ERROR);
		
		die();
	}

	if (! isset($unsafe_base))
		$unsafe_base=".";
		
	if ($FG_DEBUG == 1) echo "<br>0. HERE WE ARE";

	if ($_POST["done"]=="submit_log"){

		$DBHandle  = A2Billing::DBHandle();

		if ($FG_DEBUG == 1) echo "<br>1. ".$_POST["pr_login"].$_POST["pr_password"];
		$_POST["pr_login"] = access_sanitize_data($_POST["pr_login"]);
		$_POST["pr_password"] = access_sanitize_data($_POST["pr_password"]);

		$return = login ($_POST["pr_login"], $_POST["pr_password"]);
		if ($FG_DEBUG == 1) print_r($return);
		if ($FG_DEBUG == 1) echo "==>".$return[1];

		if (!is_array($return))
        	{
			sleep(2);
			header ("HTTP/1.0 401 Unauthorized");
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
			$_SESSION["agent_id"]=$return['id'];
			$_SESSION["tariff"]=$return['tariffgroup'];
			$_SESSION["currency"]=$return['currency'];
			$_SESSION['lang_db']=$return['language'];
			UseLanguage();

		}

	}else{
		$_SESSION["cus_rights"]=0;

	}

}


// Functions
function login ($user, $pass) {
	global $DBHandle;
	$user = trim($user);
	$pass = trim($pass);
	if (strlen($user)==0 || strlen($user)>=20 || strlen($pass)==0 || strlen($pass)>=20) return false;

	$QUERY = "SELECT login, credit, active, id, tariffgroup, currency, language FROM cc_agent WHERE login = '".$user."' AND passwd = '".$pass."'";

	$res = $DBHandle -> query($QUERY);

	if (!$res) {
		$errstr = $DBHandle->ErrorMsg();
		return (false);
	}
	if ($res->EOF)
		return false;

	$row [] =$res -> fetchRow();

	if( $row [0]['active'] != "t" && $row [0]['active'] != "1" ) {
		return -1;
	}
	

	return ($row[0]);
}

header ("Location: $unsafe_base/booths.php");
?>
