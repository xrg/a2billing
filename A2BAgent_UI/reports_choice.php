<?php
include ("lib/defines.php");
include ("lib/module.access.php");

if (! has_rights (ACX_ACCESS)){
      Header ("HTTP/1.0 401 Unauthorized");
      Header ("Location: PP_error.php?c=accessdenied");
      die();
}

include("PP_header.php");
?>
<p class='repchoice'>
	<a href="invoices.php" ><?= _("All calls") ?></a><br>
	<a href="A2B_entity_ratecard.php" ><?= _("Current rates") ?></a><br>
	<a href="agent-money.php"><?= _("Agent payments") ?></a><br>
	<a href="phone-stats.php"><?= _("Phone statistics") ?></a><br>
</p>

<?php
	include("PP_footer.php");
?>