<?php
include ("lib/defines.php");
include ("lib/module.access.php");

$USE_AJAX=1 ;

include ("PP_header.php");

if (! has_rights (ACX_ACCESS)){
	   Header ("HTTP/1.0 401 Unauthorized");
	   Header ("Location: PP_error.php?c=accessdenied");
	   die();
}



?>

<?php
	/** These states have to match the SQL logic */
	
<?php include ("PP_footer.php"); ?>
