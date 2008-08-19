<?php   

if ( has_rights (ACX_AGENTS) ){ 	?>
<div id='menu_agents'>
<a onclick="menu_toggle('menu_agents');"><?= _("AGENTS");?></a>
<ul>
	<li><a href="A2B_entity_agent.php"><?= _("Agents");?></a></li>
	<li><a href="A2B_entity_booths.php"><?= _("Booths");?></a></li>
	<li><a href="Gen_booths.php"><?= _("Generate Booths");?></a></li>
	<li><a href="Callshop_booths.php"><?= _("Callshop View");?></a></li>
	<li><a href="A2B_entity_sessions.php"><?= _("Sessions");?></a></li>
	<li><a href="A2B_entity_session_problems.php"><?= _("Session Problems");?></a></li>
	<li><a href="A2B_entity_agentpay.php"><?= _("Payments");?></a></li>
	<li><a href="agent-money.php"><?= _("Money situation");?></a></li>
</ul>
</div>
<?php   }  
?>
