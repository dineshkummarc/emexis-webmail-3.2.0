<?php
/**
 * notification.php
 *
 * Notificação de novas mensagens FLAG
 *
 * @copyright 2010 BRConnection
 * @author Bruno Borges <bruno.borges@brc.com.br>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id: folders.php 13893 2010-01-25 02:47:41Z pdontthink $
 * @package Emexis-Webmail 3.0
 */

define('SM_PATH','../');
/** This is the right_main page */
define('PAGE_NAME', 'right_main');

/**
 * Path for SquirrelMail required files.
 * @ignore
 */
define('SM_PATH','../');

/* SquirrelMail required files. */
require_once(SM_PATH . 'include/validate.php');
require_once(SM_PATH . 'functions/imap.php');
require_once(SM_PATH . 'functions/utils.php');
require_once(SM_PATH . 'functions/date.php');
require_once(SM_PATH . 'functions/mime.php');
require_once(SM_PATH . 'functions/mailbox_display.php');
require_once(SM_PATH . 'functions/display_messages.php');
require_once(SM_PATH . 'functions/html.php');

sqgetGlobalVar('key',         $key,       SQ_COOKIE);
sqgetGlobalVar('onetimepad',  $onetimepad,SQ_SESSION);

//$msgs = fillMessageArray($imapConnection,$id,$end_loop, $show_num);

$box = $_POST['mailbox'];
$start_msg = $_POST['startmessage'];

global $imapConnection, $start_msg, $show_num,$num_msgs, $sort, $mbxresponse, $username, $key, $imapServerAddress, $imapPort;

$pass = OneTimePadDecrypt($key,$onetimepad);
$imapConnection = sqimap_login($username, $key, $imapServerAddress, $imapPort, 0);

$num_msgs = sqimap_get_num_messages ($imapConnection, $box);

$res = getEndMessage($start_msg, $show_num, $num_msgs);

$start_msg = $res[0];
$end_msg   = $res[1];

echo get_paginator_str($box, $start_msg, $end_msg, $num_msgs, $show_num, $sort);

?>