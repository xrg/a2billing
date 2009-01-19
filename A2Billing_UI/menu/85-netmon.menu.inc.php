<?php
	/* Servers menu */
?>
	<?php   if ( has_rights (ACX_NETMON) ){ 	?>
		<div id='menu_netmon'>
		<a onclick="menu_toggle('menu_netmon');"><?= _("NET MONITOR");?></a>
		<ul>
			<li><a href="A2B_entity_nmsystem.php"><?= _("Systems");?></a></li>
			<li><a href="A2B_entity_nm_node.php"><?= _("Nodes");?></a></li>
			<li><a href="A2B_entity_nm_values.php"><?= _("Values");?></a></li>
			<li><a href="netmon_stats.php"><?= _("Statistics");?></a></li>
		</ul>
</div>
	<?php  } ?>
<?php

?>
