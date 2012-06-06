<?php
/**
 * listMembers.php
 *
 * List Group members
 * @copyright (c) 2002 Kelvin Ho
 * @copyright (c) 2002-2004 Jon Nelson <quincy at linuxnotes.net>
 * @copyright (c) 2004-2006 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id: listMembers.php,v 1.12 2007/01/20 08:27:48 tokul Exp $
 * @package sm-plugins
 * @subpackage abook_group
 */

/** @ignore */
if (!defined('SM_PATH'))define('SM_PATH','../../');

include_once(SM_PATH . 'include/validate.php');
include_once(SM_PATH . 'functions/date.php');
include_once(SM_PATH . 'functions/display_messages.php');
require_once(SM_PATH . 'functions/html.php');
require_once(SM_PATH . 'functions/forms.php');
include_once(SM_PATH . 'functions/addressbook.php');
include_once(SM_PATH . 'plugins/abook_group/abook_group_database.php');
include_once(SM_PATH . 'plugins/abook_group/abook_group_functions.php');


/* --- End functions --- */

//global $username;

/* Set globals */
//sqgetGlobalVar('username', $username, SQ_POST);
// we are not in mailbox
// sqgetGlobalVar('mailbox', $mailbox, SQ_POST);
//sqgetGlobalVar('myparams', $myparams, SQ_POST);
//sqgetGlobalVar('abook', $abook, SQ_POST);

sqgetGlobalVar('userData', $userData, SQ_POST);
sqgetGlobalVar('groupName', $groupName, SQ_GET);
//sqgetGlobalVar('myGroupMembers', $myGroupMembers, SQ_POST);
//sqgetGlobalVar('abookGroups', $abookGroups, SQ_POST);

sqgetGlobalVar('name', $name, SQ_POST);
sqgetGlobalVar('nickName', $nickName, SQ_POST);
sqgetGlobalVar('backendName', $backendName, SQ_POST);

/* remove operation vars */
sqgetGlobalVar('remove', $remove, SQ_POST);
sqgetGlobalVar('nickNameBackEnd', $nickNameBackEnd, SQ_POST);
// $groupname is already extracted
//sqgetGlobalVar('message', $message, SQ_POST);
/* End globals */


/* Initialize addressbook */
$abook = addressbook_init(true,true);

require_once("conf_database.php");
/*
$myparams = array();
$myparams['dsn'] = $addrbook_dsn;
$myparams['table'] = 'addressgroups';
$myparams['owner'] = $username;
*/
$abookGroups = new abook_group_database($myparams);

if (isset($remove) && !empty($remove)){
    if (isset($nickNameBackEnd) && !empty($nickNameBackEnd)){
        $userData = explodeUserArray($nickNameBackEnd);
        $abookGroups->removeFromGroup($userData, $groupName);

        if ($abookGroups->error){
            $message = $abookGroups->error;
        } else{
            bindtextdomain('abook_group', SM_PATH . 'plugins/abook_group/locale');
            textdomain('abook_group');
            $message = _("Remove Successful");
            bindtextdomain('squirrelmail', SM_PATH . 'locale');
            textdomain('squirrelmail');
        }
    }
}

$myGroupMembers = $abookGroups->list_groupMembers($groupName);

?>


<?php 
/*bindtextdomain('abook_group', SM_PATH . 'plugins/abook_group/locale');
textdomain('abook_group');
echo _("Members");
bindtextdomain('squirrelmail', SM_PATH . 'locale');
textdomain('squirrelmail');*/
?>

        <?php
        if (isset($message)) { 
            echo '<p align="center"><b>' . $message . '</b></p>';
        }
        ?>
<?php 
if(is_array($myGroupMembers) && count($myGroupMembers)>0) { 
?>	
    <?php
    for ($j=0;$j<count($myGroupMembers);$j++){
        $name = $myGroupMembers[$j]['name'];
        $nickName = $myGroupMembers[$j]['nickname'];
        $backendName = $abook->backends[$myGroupMembers[$j]['backend']]->bnum;  

        /*echo '<pre>';
        var_dump($myGroupMembers[$j]);
        echo '</pre>';*/

        echo  '<div class="contactPerson" onclick="showInfoContact(this)" onmouseout=$(this).removeClass("backContact") onmousemove="changeBackContact(this)">'
        . addCheckBox('sel[' . $count . ']', $selected, $myGroupMembers[$j]['backend'] . ':' . $myGroupMembers[$j]['nickname'], ' id="'
        . $myGroupMembers[$j]['backend']
        . '_' . utf8_decode(urlencode($myGroupMembers[$j]['nickname'])) . '"')
        . utf8_decode(htmlspecialchars($myGroupMembers[$j]['name']))
        . '<input type="hidden" value="' . utf8_decode($myGroupMembers[$j]['firstname']) . '" class="firstname">'
        . '<input type="hidden" value="' . utf8_decode($myGroupMembers[$j]['lastname']) . '" class="lastname">'
        . '<input type="hidden" value="' . utf8_decode($myGroupMembers[$j]['nickname']) . '" class="nick">'
        . '<input type="hidden" value="' . utf8_decode($row['label']) . '" class="extra">'
        . '<input type="hidden" value="' . utf8_decode($myGroupMembers[$j]['email']) . '" class="email">'
        . '</div></ br>';
            
    }
?>

<?php
//end count > 0
} elseif (! empty($abookGroups->error)) {
     error_box($abookGroups->error,$color);
} else{
    bindtextdomain('abook_group', SM_PATH . 'plugins/abook_group/locale');
    textdomain('abook_group');
    echo "<p><center>"._("You Currently Do Not Have Any Members In This Group")."</center></p>";
    bindtextdomain('squirrelmail', SM_PATH . 'locale');
    textdomain('squirrelmail');
}
?>


       <?php
        /*bindtextdomain('abook_group', SM_PATH . 'plugins/abook_group/locale');
        textdomain('abook_group');
        echo _("Remove");
        bindtextdomain('squirrelmail', SM_PATH . 'locale');
        textdomain('squirrelmail');*/
       ?> 
</body></html>
