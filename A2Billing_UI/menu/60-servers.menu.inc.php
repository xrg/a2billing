<?php
	/* Servers menu */
?>
		<div id='menu_servers'>
		<a onclick="menu_toggle('menu_servers');"><?= _("SERVERS");?></a>
		<ul>
	<?php   if ( has_rights (ACX_SERVERS) ){ 	?>
			<li><a href="A2B_entity_server_group.php"><?= _("Groups");?></a></li>
			<li><a href="A2B_entity_server.php"><?= _("Servers");?></a></li>
			<li><a href="Gen_peer_cfgs.php"><?= _("Gen. Configs");?></a></li>
			<li><a href="Gen_peer_dplans.php"><?= _("Gen. Peer Dial");?></a></li>
	<?php  } ?>
	<?php   if ( has_rights (ACX_TRUNK) ){ 	?>
			<li><a href="A2B_entity_provider.php"><?= _("Providers");?></a></li>
			<li><a href="A2B_entity_trunk.php"><?= _("Trunks");?></a></li>
	<?php  } ?>
		</ul>
</div>
<?php

?>
