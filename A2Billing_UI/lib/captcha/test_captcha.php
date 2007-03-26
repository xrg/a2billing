<?php

$code = "1234567890";
$seed = 1231413213;
$captcha_gd = 1;
	if ($captcha_gd)
	{
		include('./captcha_gd.php');
	}
	else
	{
		include('./includes/captcha/captcha_non_gd.php');
	}

	$captcha = new captcha();
	$captcha->execute($code, $seed);
	exit;


?>
