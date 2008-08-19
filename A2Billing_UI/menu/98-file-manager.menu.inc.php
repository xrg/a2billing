<?php   
	if ( has_rights (ACX_FILE_MANAGER) ){ 	?>
		<div id='menu_files'>
		<a onclick="menu_toggle('menu_files');"><?= _("FILE MANAGER");?></a>
		<ul>
			<li><a href="CC_musiconhold.php"><?= _("MusicOnHold");?></a></li>
			<li><a href="CC_upload.php"><?= _("Standard File");?></a></li>
		</ul>
		</div>
	<?php  } 
?>
