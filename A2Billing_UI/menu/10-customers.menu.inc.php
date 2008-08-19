<?php
	if ( has_rights (ACX_CUSTOMER) ){ 	?>
	<div id='menu_customers'>
	<a onclick="menu_toggle('menu_customers');"><?= _("CUSTOMERS");?></a>
	<ul>
		<li><a href="A2B_entity_card.php"><?= _("List");?></a></li>
		<li><a href="Gen_cards.php"><?= _("Generate");?></a></li>
                <li><a href="CC_card_import.php"><?= _("Import");?></a></li>
                <li><a href="A2B_entity_card_group.php"><?= _("Groups");?></a></li>
		<li><a href="A2B_entity_astuser.php"><?= _("VoIP users");?></a></li>
		<li><a href="A2B_entity_instance.php"><?= _("VoIP regs");?></a></li>
		<li><a href="A2B_entity_callerid.php"><?= _("CallerID");?></a></li>
		<li><a href="A2B_entity_speeddial.php"><?= _("Speed Dial");?></a></li>
	</ul>
	</div>
	<?php   }
?>