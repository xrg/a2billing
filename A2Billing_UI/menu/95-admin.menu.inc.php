<?php   if ( has_rights (ACX_ADMINISTRATOR) ){ 	?>
		<div id='menu_admin'>
		<a onclick="menu_toggle('menu_admin');"><?= _("ADMINISTRATOR");?></a>
		<ul>
			<li><a href="A2B_entity_config.php"><?= _("Config");?></a></li>
			<li><a href="A2B_entity_user.php?groupID=0"><?= _("Show Administrator");?></a></li>
			<li><a href="A2B_entity_user.php?form_action=ask-add&groupID=0"><?= _("Add Administrator");?></a></li>
			<li><a href="A2B_entity_user.php?groupID=1"><?= _("Show ACL Admin");?></a></li>
			<li><a href="A2B_entity_user.php?form_action=ask-add&groupID=1"><?= _("Add ACL Admin");?></a></li>
			<li><a href="A2B_entity_backup.php?form_action=ask-add"><?= _("Database Backup");?></a></li>
			<li><a href="A2B_entity_restore.php"><?= _("Database Restore");?></a></li>
			<li><a href="A2B_entity_texts.php"><?= _("Localized texts");?></a></li>
			<li><a href="A2B_logfile.php"><?= _("Watch Log files"); ?></a></li>
			<li><a href="A2B_entity_mails.php"><?= _("Mails");?></a></li>
			<li><a href="A2B_entity_log_viewer.php"><?= _("System Log");?></a></li>
		</ul>
		</div>
	<?php  }
?>
