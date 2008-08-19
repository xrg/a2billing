<?php
	/* Config menu. This will include sub-menu files
	*/
?>
		<div id='menu_config'>
		<a onclick="menu_toggle('menu_config');"><?= _("CONFIG");?></a>
		<ul>
	<?php   if ( has_rights (ACX_NUMPLAN) ){ 	?>
			<li><a href="A2B_entity_numplan.php"><?= _("Numbering Plans");?></a></li>
			<li><a href="A2B_entity_re_numplan.php"><?= _("Reverse NPlans");?></a></li>
	<?php  } ?>
	<?php   if ( has_rights (ACX_MISC) ){ 	?>
			<li><a href="A2B_entity_mailtemplate.php"><?= _("Mail template");?></a></li>
			<li><a href="A2B_entity_subscrtem.php"><?= _("Subscribe template");?></a></li>
	<?php  } ?>
	<?php   if ( has_rights (ACX_ADMINISTRATOR) ){ 	?>
			<li><a href="A2B_entity_ast_usercfg.php"><?= _("User cfgs");?></a></li>
			<li><a href="A2B_entity_provision.php"><?= _("Provisioning");?></a></li>
	<?php  } ?>
		</ul>
		</div>
<?php
?>