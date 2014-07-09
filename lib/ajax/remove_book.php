<?php
require_once '../../core/lib/bootstrap.php';
require_once '../amazon_bootstrap.php';

if (!empty($_SESSION['userid'])) {
	$db->delete('book_shelf', array('id'), array($_POST['id']));
	$db->doCommit();
	
	$shelf = $_books->getUsersShelf($_SESSION['userid']);
	echo $_books->outputShelf($shelf);
}