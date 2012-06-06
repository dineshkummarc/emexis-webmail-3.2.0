<?php
require_once('conf_database.php');

sqgetGlobalVar('newGroup', $NewGroup, SQ_POST);

$abookGroups = new abook_group_database($myparams);
if($abookGroups->addGroup($NewGroup,$username))
	header("Location:list_abook_group.php");

?>
