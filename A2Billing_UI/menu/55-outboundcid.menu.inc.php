<?php   
	if ( has_rights (ACX_OUTBOUNDCID) ){ 	?>
		<div id='menu_obcid'>
		<a onclick="menu_toggle('menu_obcid');"><?= _("OUTBOUND CID");?></a>
		<ul>
			<li><a href="A2B_entity_outbound_cidgroup.php"><?= _("List CIDGroup");?></a></li>
			<li><a href="A2B_entity_outbound_cid.php"><?= _("List CID's");?></a></li>
		</ul>
		</div>
	<?php   }  
?>
