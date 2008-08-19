<?php   
	if ( has_rights (ACX_BILLING) ){ 	?>
	<div id='menu_billing'>
	<a onclick="menu_toggle('menu_billing');"><?= _("BILLING");?></a>
	<ul>
		<li><a href="A2B_currencies.php"><?= _("Currency Table");?></a></li>
		<li><a href="A2B_entity_payment_configuration.php"><?= _("Payment Methods") ?></a></li>
		<li><a href="A2B_entity_transactions.php"><?= _("Transactions"); ?></a></li>
		<li><a href="A2B_entity_moneysituation.php"><?= _("Money situation");?></a></li>
		<li><a href="A2B_entity_payment.php"><?= _("Payments");?></a></li>
		<li><a href="A2B_entity_voucher.php"><?= _("Vouchers");?></a></li>
		<li><a href="Gen_vouchers.php"><?= _("Generate Vouchers");?></a></li>
		<li><a href="A2B_entity_charge.php"><?= _("Charges");?></a></li>
		<li><a href="A2B_entity_ecommerce.php"><?= _("E-Products");?></a></li>
		<li><a href="A2B_entity_subscription.php"><?= _("Subscriptions");?></a></li>
	</ul>
	</div>
	<?php   }  
?>
