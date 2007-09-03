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
	<td class="repchoice"><?= _("Please choose report type:")?></td>
	</tr>
	<tr class="repch"><td><a href="invoices.php" ><?= _("All calls") ?></a></td></tr>
	<tr class="repch"><td><a href="agent-money.php"><?= _("Agent money situation") ?></a></td></tr>
	<tr class="repch"><td><a href="phone-stats.php"><?= _("Phone statistics") ?></a></td></tr>
	
	<tr>
	<td class="repchoice"> &nbsp;</td>
	</tr>
	<tr class="repch"><td><a href="A2B_entity_charge.php"><?= _("Customer charges") ?></a></td></tr>
	<tr class="repch"><td><a href="A2B_entity_agentpay.php"><?= _("Agent payments") ?></a></td></tr>
	<tr class="repch"><td><a href="A2B_entity_agent_invoicev.php"><?= _("Agent invoices") ?></a></td></tr>

</table>

<?php
	include("PP_footer.php");
?>