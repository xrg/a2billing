<?php
require ("lib/defines.php");
require("lib/module.access.php");
require("lib/company_info.php");


if (! has_rights (ACX_ACCESS)){ 
	   Header ("HTTP/1.0 401 Unauthorized");
	   Header ("Location: PP_error.php?c=accessdenied");
	   die();
}
Header ("Location: booths.php");
?>