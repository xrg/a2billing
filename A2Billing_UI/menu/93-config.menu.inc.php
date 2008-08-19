<?php
	/* Config menu. This will include sub-menu files
	*/
?>
		<div id='menu_config'>
		<a onclick="menu_toggle('menu_config');"><?= _("CONFIG");?></a>
		<ul>
<?php
foreach (glob("menu/*.config.inc.php") as $file) {
	// remember the { and } are necessary!
        include $file;
}
?>

		</ul>
		</div>
<?php
?>