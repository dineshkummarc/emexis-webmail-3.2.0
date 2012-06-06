<?php
if (!defined('SM_PATH'))define('SM_PATH','../../');


include_once(SM_PATH . 'include/validate.php');
include_once(SM_PATH . 'functions/utils.php');
include_once('abook_group_database.php');

global $username;


require_once('conf_database.php');
/*
$myparams = array();
$myparams['dsn'] = $addrbook_dsn;
$myparams['table'] = 'addressgroups';
$myparams['owner'] = $username;
*/
sqgetGlobalVar('group', $NewGroup, SQ_GET);

$abookGroups = new abook_group_database($myparams);
if(!empty($NewGroup))
	$abookGroups->addGroup($NewGroup,$username);
?>
