<?php
	if ( has_rights (ACX_PACKAGEOFFER) ){ 	?>
	<div id='menu_pkgoffer'>
	<a onclick="menu_toggle('menu_pkgoffer');"><?= _("PACKAGE OFFER");?></a>
		<ul>
			<li><a href="A2B_entity_package.php"><?= _("List Offer Package");?></a></li>
			<li><a href="A2B_detail_package.php"><?= _("Details Package");?></a></li>
		</ul>
		</div>
	<?php   }  
?>
