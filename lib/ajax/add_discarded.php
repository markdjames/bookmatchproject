<?php
require_once '../amazon_bootstrap.php';
require_once '../../core/lib/bootstrap.php';

$user = $u->getUser($_SESSION['userid']);

$discarded = json_decode($user['discarded']);
$discarded[] = $_POST['id'];

$field['id'] 			= $user['id'];
$values['discarded'] 	= json_encode($discarded);
$db->update('users', $field, $values);
$db->doCommit();