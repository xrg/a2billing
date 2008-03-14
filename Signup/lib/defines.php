<?php
define('DIR_COMMON',dirname(__FILE__)."/common/");

// Override below, if you want an alternative dir..
//define(DEFAULT_A2BILLING_CONFIG, '/etc/asterisk/a2billing.conf');

$FG_DEBUG=4;

//Hint: this file could be used to distinguish between virtual hosts' 
// signups

define ('MESSAGE_DOMAIN','signup');

define ("COPYRIGHT", gettext(" This software is under GPL licence. For further information, please visit : <a href=\"http://www.asterisk2billing.org\" target=\"_blank\">asterisk2billing.org</a>"));
define ("CCMAINTITLE", gettext("Asterisk2Billing : CallingCard platform"));	

define ("COMPANYTITLE","The marvelous A2Billing team!");

// Override this to enable multiple signup configs.
define ("SIGNUP_CFG",'signup');
?>
