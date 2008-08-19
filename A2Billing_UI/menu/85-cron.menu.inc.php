<?php
	if ( has_rights (ACX_CRONT_SERVICE) ){ 	?>
		<div id='menu_cront'>
		<a onclick="menu_toggle('menu_cront');"><?= _("CRON SERVICE");?></a>
		<ul>
			<li><a href="A2B_entity_autorefill.php"><?= _("AutoRefill Report");?></a></li>
			<li><a href="A2B_entity_service.php"><?= _("List Recurring Service");?></a></li>
			<li><a href="A2B_entity_service.php?form_action=ask-add"><?= _("Add Recurring Service");?></a></li>
			<li><a href="A2B_entity_alarm.php"><?= _("Alarms");?></a></li>
			<li><a href="A2B_entity_alarmrun.php"><?= _("Alarm Data");?></a></li>
		</ul>
		</div>

	<?php  }
?>
