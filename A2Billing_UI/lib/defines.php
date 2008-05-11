<?php
define('DIR_COMMON',dirname(__FILE__)."/common/");

// Override below, if you want an alternative dir..
//define(DEFAULT_A2BILLING_CONFIG, '/etc/a2billing.conf');

$FG_DEBUG=4;

define ('MESSAGE_DOMAIN','admin');
define('TTF_DIR','/usr/share/fonts/TTF/'); // Mandriva dir for ttf fonts.


// UI INFORMATION
define ("TEXTCONTACT", gettext("This software has been created by Areski under GPL licence. For futher information, feel free to contact me:"));
define ("EMAILCONTACT", "areski@gmail.com");
define ("COPYRIGHT", gettext(" This software is under GPL licence. For further information, please visit : <a href=\"http://www.asterisk2billing.org\" target=\"_blank\">asterisk2billing.org</a>"));
define ("CCMAINTITLE", gettext("..:: Asterisk2Billing : CallingCard platform ::.."));
define ("RELEASE_INFO", gettext("v2.0 beta - d wish"));
?>
