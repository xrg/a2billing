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

<table cellpadding="0" cellspacing="1" border="0" width="30%" align="center">
	<tr>
	<td class="repchoice"><?= _("Please choose information page:")?></td>
	</tr>
	<tr class="repch"><td><a href="A2B_entity_ratecard.php" ><?= _("Current rates") ?></a></td></tr>
	<tr class="repch"><td><a href="usage.php" ><?= _("Usage instructions") ?></a></td></tr>
	<tr class="repch"><td><a href="contact.php" ><?= _("Contact information") ?></a></td></tr>
</table>

<?php
	include("PP_footer.php");
?>