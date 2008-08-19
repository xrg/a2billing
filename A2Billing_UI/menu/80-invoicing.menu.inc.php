<?php
if ( has_rights (ACX_INVOICING) ){ 	?>
		<div id='menu_invoicing'>
		<a onclick="menu_toggle('menu_invoicing');"><?= _("INVOICING");?></a>
		<ul>
			<li><a href="A2B_entity_view_invoice.php"><?= _("Card Invoices");?></a></li>
			<li><a href="A2B_entity_agent_invoicev.php"><?= _("Agent Invoices");?></a></li>
			<li><a href="Gen_invoice_card.php"><?= _("Create Card Inv.");?></a></li>
			<li><a href="Gen_invoice_agent.php"><?= _("Create Agent Inv.");?></a></li>
			<li><a href="call-unbilled.php"><?= _("Unbilled calls");?></a></li>
		</ul>
		</div>
	<?php  } 
?>
