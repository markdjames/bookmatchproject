<div class='content'>
	<?php
	if (is_numeric($url_vars[0])) {
		$_POST['id'] = $url_vars[0];
		require 'lib/forms/book_info.php';
	}
	?>
</div>