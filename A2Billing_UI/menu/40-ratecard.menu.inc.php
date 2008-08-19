<?php   
	if ( has_rights (ACX_RATECARD) ){ 	?>
	<div id='menu_ratecard'>
	<a onclick="menu_toggle('menu_ratecard');"><?= _("RATECARD");?></a>
		<ul>
			<li><a href="A2B_entity_tariffgroup.php?"><?= _("TariffGroup");?></a></li>
			<li><a href="A2B_entity_tariffplan.php"><?= _("RateCard (buy)");?></a></li>
			<li><a href="A2B_entity_retailplan.php"><?= _("RetailPlan (sell)");?></a></li>
			<li><a href="A2B_entity_buyrate.php"><?= _("Buy Rate");?></a></li>
			<li><a href="A2B_entity_sellrate.php"><?= _("Sell Rate");?></a></li>
			<li><a href="A2B_entity_buyrate.php?action=ask-import"><?= _("Import Buy Rates");?></a></li>
			<li><a href="A2B_entity_sellrate.php?action=ask-import"><?= _("Import Sell Rates");?></a></li>
			<li><a href="CC_entity_sim_ratecard.php"><?= _("Ratecard Simulator");?></a></li>
			<li><a href="CC_entity_sim_ratecard.php"><?= _("DID Engine Simulator");?></a></li>
		</ul>
		</div>
	<?php   }
?>
