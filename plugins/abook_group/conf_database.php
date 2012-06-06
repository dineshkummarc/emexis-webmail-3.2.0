<?php
if (!defined('SM_PATH'))define('SM_PATH','../../');


include_once(SM_PATH . 'include/validate.php');
include_once(SM_PATH . 'functions/utils.php');
include_once('abook_group_database.php');

global $username;

$myparams = array();
$myparams['dsn'] = $addrbook_dsn;
$myparams['table'] = 'addressgroups';
$myparams['owner'] = $username;
?>
