<?php

/**
 * handlergroup.php
 *
 * Recebe requisi��es via ajax para alterar o grupo dos usu�rios
 *
 * @copyright (c) BRconnection 2009
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id: abook_group_functions.php,v 1.8 2007/01/20 08:27:48 tokul Exp $
 * @package sm-plugins
 * @subpackage abook_group
 */
define(SM_PATH, '../../');
require_once(SM_PATH . 'include/validate.php');
require_once(SM_PATH . 'functions/global.php');
require_once(SM_PATH . 'plugins/abook_group/handler.class.php');
include_once(SM_PATH . 'functions/addressbook.php');
include_once(SM_PATH . 'plugins/abook_group/abook_group_database.php');
include_once(SM_PATH . 'plugins/abook_group/abook_group_functions.php');
include_once(SM_PATH . 'plugins/abook_group/abook_group_PEAR_database.php');

define(ADD, 1);
define(REM, 2);
define(DELGROUP, 3);
define(RENGROUP, 4); //Renomear grupo
define(SELECTMAIL, 5);
define(SHOWGROUPS, 6);
define(SEARCH, 7);
define(COUNT_MEMBERS, 8);

global $username, $addrbook_dsn;

require_once('conf_database.php');
/*
$myparams = array();
$myparams['dsn'] = $addrbook_dsn;
$myparams['table'] = 'addressgroups';
$myparams['owner'] = $username;
*/

$obj = new HandlerGroup($username, $addrbook_dsn);

if ($_GET['action'] == ADD) {    
    if (strcmp($_GET['group'], 'undefined') != 0)        
        $obj->addGroup($_GET['user'], $_GET['group']);    
}

if ($_GET['action'] == REM)
    $obj->remGroup($_GET['user'], $_GET['group']);

if ($_GET['action'] == DELGROUP) {
    $obj->deleteGroup($_GET['group']);
    echo $obj->sql;
}
if ($_GET['action'] == RENGROUP) {
    $obj->renameGroup($_GET['newName'], $_GET['group']);
}
if ($_GET['action'] == SELECTMAIL) {
    echo $obj->selectMails(rtrim($_GET['group']));
}
if ($_GET['action'] == SHOWGROUPS) {   
    $abookGroups = new abook_group_database($myparams);
    $myGroups = $abookGroups->list_group();
    if (count($myGroups)) {
        foreach ($myGroups as $ind => $group) {
            if ((count($myGroups) - 1) == $ind){
                $strGroup .= '"' . $group['addressgroup'] . '"';
                $total .= '"' . $abookGroups->countMembers($group['addressgroup']) . '"';
            }else{
                $strGroup .= '"' . $group['addressgroup'] . '", ';
                $total .= '"' . $abookGroups->countMembers($group['addressgroup']) . '", ';
            }
        }
    }
    $json = sprintf('{"groups": [%s],"total": [%s]}', $strGroup,$total);
    echo $json;
}
if ($_GET['action'] == SEARCH) {
    $abookGroups = new abook_group_database($myparams);
    $contacts = $abookGroups->list_groupMembers($_GET['group']);
    foreach($contacts as $ind => $contact){
        if ((count($contacts) - 1) == $ind)
            $strName .= '"' . utf8_decode($contact['name']) . '"';
        else
            $strName .= '"' . utf8_decode($contact['name']) . '", ';
        
        if ((count($contacts) - 1) == $ind)
            $strMail .= '"' . $contact['email'] . '"';
        else
            $strMail .= '"' . $contact['email'] . '", ';

    }
    $json = sprintf('{"name": [%s], "email": [%s]}', $strName,$strMail);
    echo $json;
}
if ($_GET['action'] == COUNT_MEMBERS){
    $abookGroups = new abook_group_database($myparams);
    $total = $abookGroups->countMembers($_GET['group']);   
}


?>

