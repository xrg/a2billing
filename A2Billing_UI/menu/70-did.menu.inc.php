<?php
	if ( has_rights (ACX_DID) ){ 	?>
		<div id='menu_did'>
		<a onclick="menu_toggle('menu_did');"><?= _("DID");?></a>
		<ul>
			<li><a href="A2B_entity_didgroup.php"><?= _("DID Group");?></a>
			<li><a href="A2B_entity_did.php"><?= _("DID Batch");?></a></li>
			
			<li><a href="A2B_entity_did_import.php?"><?= _("Import DID");?></a></li>
			<li><a href="A2B_entity_did_destination.php"><?= _("List Destination");?></a></li>
			<li><a href="A2B_entity_did_destination.php?form_action=ask-add"><?= _("Add Destination");?></a></li>
			<li><a href="A2B_entity_did_billing.php"><?= _("DID BILLING");?></a></li>
			<li><a href="A2B_entity_did_use.php"><?= _("DID use");?></a></li>
		</ul>
		</div>
	<?php  }
?>
