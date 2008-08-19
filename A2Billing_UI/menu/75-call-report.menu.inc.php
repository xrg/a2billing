<?php   
	if ( has_rights (ACX_CALL_REPORT) ){ 	?>
		<div id='menu_creport'>
		<a onclick="menu_toggle('menu_creport');"><?= _("CALL REPORT");?></a>
		<ul>
			<li><a href="A2B_entity_call.php?"><?= _("Calls");?></a></li>
			<li><a href="call-stats.php?"><?= _("Statistics");?></a></li>
			<li><a href="call-log-customers.php?nodisplay=1&posted=1"><?= _("CDR Report");?></a></li>
			<li><a href="call-comp.php?"><?= _("Calls Compare");?></a></li>
			<li><a href="call-last-month.php?"><?= _("Monthly Traffic");?></a></li>
			<li><a href="call-daily-load.php?"><?= _("Daily Load");?></a></li>
			<li><a href="call-count-reporting.php?nodisplay=1&posted=1&"><?= _("Report");?></a></li>
		</ul>
		</div>
	<?php  } 
?>
