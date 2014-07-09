<?php
require_once '../amazon_bootstrap.php';
require_once '../../core/lib/bootstrap.php';

$user = $u->getUser($_SESSION['userid']);

$read = json_decode($user['read']);
$read[] = $_POST['id'];

$field['id'] 		= $user['id'];
$values['read'] 	= json_encode($read);
$db->update('users', $field, $values);
$db->doCommit();