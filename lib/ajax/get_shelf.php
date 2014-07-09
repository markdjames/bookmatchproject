<?php
require_once '../../core/lib/bootstrap.php';
require_once '../amazon_bootstrap.php';

if (!empty($_SESSION['userid'])) {

	$shelf = $_books->getUsersShelf($_SESSION['userid']);
	echo $_books->outputShelf($shelf);
}