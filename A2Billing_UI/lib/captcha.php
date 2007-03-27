<?php
session_name("UISIGNUP");
session_start();
include("defines.php");
$code = MDP_STRING(4);
$_SESSION["captcha_code"] = $code;
$seed = MDP_NUMERIC(4);

$captcha_gd = 1;
	if ($captcha_gd)
	{
		include('captcha/captcha_gd.php');
	}
	else
	{
		include('captcha/captcha_non_gd.php');
	}

	$captcha = new captcha();
	$captcha->execute($code, $seed);	
	exit;
?>
