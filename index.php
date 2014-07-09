<?php
/**
 * cmsFramework
 *
 * @author 	Mark James <markdjames@gmail.com>
 *
 * @version 	0.1
 * @copyright	Mark James
 */
 
/**
 * Strap in the classes, database connection and core functions
 */
require_once 'core/lib/bootstrap.php';

/**
 * check where url is pointing and that it is valid within schemea
 */
require_once 'core/engine/check_url.php';

/**
 * Check if we're viewing a sub-site (from /sites directory)
 */
require_once 'core/engine/get_site.php';

/**
 * check IP based location
 */
require_once 'core/engine/check_location.php';

/**
 * check if accessing from a mobile device
 */
require_once 'core/engine/check_mobile.php';

/**
 * If nessecary include processors
 */
require_once 'core/processors/processors.php';

/**
 * get user, check if url points at sub-site, get page data (module or dynamic database page)
 */
require_once 'core/engine/get_user.php';

/**
 * get the page based on the URL ID parameter
 */
require_once 'core/engine/get_page.php';


ob_start();
/**
 * load in HTML template for header 
 */
require_once 'core/engine/load_head.php';

/**
 * load in HTML template for navigation
 */
require_once 'core/engine/load_nav.php';

?>
<div id="main" role="main">
<?php 
	ob_start();
	/**
	 * Load page into output variable
	 */
	require_once 'core/engine/load_page.php';
	$output = ob_get_clean();
	
	$output = $b->addBlocks($output);
	$output = linkify($output);
	
	echo $output;
	?>
</div>
<?php
/**
 * output HTML footer template
 */
require_once 'core/engine/load_foot.php';

unset($_SESSION['error']);