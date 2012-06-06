<?php
/**
 * unread_ajax.php
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

global $imapConnection, $start_msg, $show_num,$num_msgs, $sort, $mbxresponse, $username, $key, $imapServerAddress, $imapPort;

$pass = OneTimePadDecrypt($key,$onetimepad);

$imapConnection = sqimap_login($username, $key, $imapServerAddress, $imapPort, 0);
//$mbxresponse = sqimap_mailbox_select($imapConnection, "INBOX");
//$msgs = getSelfSortMessages($imapConnection,1, $show_num,50, $sort, $mbxresponse);

$status = sqimap_status_messages($imapConnection, $_POST['folder']);

if($status['UNSEEN'] > 0){
    echo "string = $('span[name={$_POST['folder']}]').html();
        if($('span[name={$_POST['folder']}] small').size() > 0)
            $('span[name={$_POST['folder']}] small').text('{$status['UNSEEN']}');
        else
            $('span[name={$_POST['folder']}]').html($('span[name={$_POST['folder']}]').text() + '<span class=\"labelNumMSg\"><small>&nbsp;&nbsp;{$status['UNSEEN']}&nbsp;&nbsp;</small></span>');";
}else{
    echo "string = $('span[name={$_POST['folder']}]').html();
    if($('span[name={$_POST['folder']}] small').size() > 0)
        $('span[name={$_POST['folder']}] small').remove();";

}





















/*foreach($msgs as $msg){
    if(!$msg['FLAG_SEEN']){
        $id = $msg["ID"];
        if(isset($_COOKIE['notification'])){
            if(!in_array($id,explode(':',$_COOKIE['notification']))){
                $text = $_COOKIE['notification'] . ':' .  $id;
                setcookie("notification",$text);
                if($msg["RECEIVED_TIME_STAMP"] > (time() - $minutes * 60)){
                    $sub = decodeHeader($msg['SUBJECT']);
                    $subject = sm_truncate_string(str_replace('&nbsp;',' ',$sub), $truncate_subject, '...', TRUE);
                    if($cont >= 5) break;
                    $ret .= "popupNotification('" .  addslashes($subject) ."','{$msg['FROM-SORT']}','{$msg['ID']}');" . PHP_EOL;
                    $ret .= "newEmail[$cont] = new objNewMsg('" . html_entity_decode(addslashes($subject))
                    . "','" .  $msg['FROM-SORT'] . "');" . PHP_EOL;
                    $cont++;
                }
            }
        }else{
            setcookie("notification",$id);
            if($msg["RECEIVED_TIME_STAMP"] > (time() - $minutes * 60)){
                $sub = decodeHeader($msg['SUBJECT']);
                $subject = sm_truncate_string(str_replace('&nbsp;',' ',$sub), $truncate_subject, '...', TRUE);
                $ret .= "popupNotification('" .  addslashes($subject) ."','{$msg['FROM-SORT']}','{$msg['ID']}');";
                $ret .= "newEmail[$cont] = new objNewMsg('" . html_entity_decode(addslashes($subject))
                . "','" .  $msg['FROM-SORT'] . "');" . PHP_EOL;
                $cont++;
            }
        }

    }
}

$ret .='var totalMsg=' . $cont . ';' . PHP_EOL;
$ret .= 'if(totalMsg > 1){
    $("#closeall:hidden").fadeIn("normal");
}else{
    $("#closeall:visible").fadeIn("normal");
}';
echo $ret;*/
?>
