<?php
	if ( has_rights (ACX_CALLBACK) ){ 	?>
		<div id='menu_callback'>
		<a onclick="menu_toggle('menu_callback');"><?= _("CALLBACK");?></a>
		<ul>
			<li><a href="A2B_entity_callback.php"><?= _("Show Callbacks");?></a></li>
			<li><a href="A2B_entity_callback.php?form_action=ask-add"><?= _("Add new Callback");?></a></li>
			<li><a href="A2B_entity_server_group.php"><?= _("Show Server Group");?></a></li>
			<li><a href="A2B_entity_server_group.php?form_action=ask-add"><?= _("Add Server Group");?></a></li>
			<li><a href="A2B_entity_server.php"><?= _("Show Server");?></a></li>
			<li><a href="A2B_entity_server.php?form_action=ask-add"><?= _("Add Server");?></a></li>
		</ul>
		</div>
	<?php  }
?>
