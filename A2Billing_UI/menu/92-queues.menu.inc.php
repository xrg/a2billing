<?php   
	if ( has_rights (ACX_QUEUES) ){ 	?>
		<div id='menu_queues'>
		<a onclick="menu_toggle('menu_queues');"><?= _("QUEUES");?></a>
		<ul>
			<li><a href="A2B_entity_ast_queue.php"><?= _("Configuration");?></a></li>
			<li><a href="A2B_entity_ast_queuemember.php"><?= _("Members");?></a></li>
			<li><a href="A2B_entity_queue_call.php"><?= _("Call status");?></a></li>
			<li><a href="A2B_entity_queue_log.php"><?= _("Raw log");?></a></li>
		</ul>
		</div>
	<?php  } 
?>
