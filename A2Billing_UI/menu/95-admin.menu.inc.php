<?php   if ( has_rights (ACX_ADMINISTRATOR) ){ 	?>
		<div id='menu_admin'>
		<a onclick="menu_toggle('menu_admin');"><?= _("ADMINISTRATOR");?></a>
		<ul>
<?php
foreach (glob("menu/*.admin.inc.php") as $file) {
	// remember the { and } are necessary!
        include $file;
}
?>
		</ul>
		</div>
	<?php  }
?>
