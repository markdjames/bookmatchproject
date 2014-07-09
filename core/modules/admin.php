<?php
if ($is_admin) {
	$core_admin_modules = scan_dir($_SERVER['DOCUMENT_ROOT'].BASE."/core/modules/admin");
	$admin_modules = scan_dir($_SERVER['DOCUMENT_ROOT'].BASE."/modules/admin");
	
	$admin_mods = array_merge($core_admin_modules, $admin_modules);
	natsort($admin_mods);
	?>
	<ul>
	<?php
	foreach ($admin_mods as $modules) {
		if ($db->checkPermissions('edit_'.basename($modules,'.php'), $_SESSION['userid'])) {	
			echo "<li><a href='".str_replace($_SERVER['DOCUMENT_ROOT'].BASE."/core/modules", BASE, str_replace($_SERVER['DOCUMENT_ROOT'].BASE."/modules", BASE, str_replace('.php', "", $modules)))."'>".ucwords(str_replace("_", " ", basename($modules,'.php')))."</a></li>";
		}
	}
	?>
	</ul>
    <?php
}