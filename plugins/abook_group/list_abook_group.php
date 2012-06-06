<?php
/**
 * list_abook_group.php
 *
 * List abook groups for modify, delete or to list group members.
 * @copyright (c) 2002 Kelvin Ho
 * @copyright (c) 2002-2004 Jon Nelson <quincy at linuxnotes.net>
 * @copyright (c) 2004-2006 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id: list_abook_group.php,v 1.8 2006/09/13 16:36:42 tokul Exp $
 * @package sm-plugins
 * @subpackage abook_group
 */

/** @ignore */
if (!defined('SM_PATH'))define('SM_PATH','../../');

include_once(SM_PATH . 'include/validate.php');
include_once(SM_PATH . 'functions/addressbook.php');
include_once(SM_PATH . 'plugins/abook_group/abook_group_database.php');
include_once(SM_PATH . 'plugins/abook_group/abook_group_functions.php');

global $username;

/*  Start globals  */

//sqgetGlobalVar('username', $username, SQ_POST);
// we are not in mailbox
// sqgetGlobalVar('mailbox', $mailbox, SQ_POST);
sqgetGlobalVar('myparams', $myparams, SQ_POST);
sqgetGlobalVar('abookGroups', $abookGroups, SQ_POST);
sqgetGlobalVar('myGroups', $myGroups, SQ_POST);
sqgetGlobalVar('groupName', $groupName, SQ_POST);
sqgetGlobalVar('groupNameURL', $groupNameURL, SQ_POST);

/*  End globals  */



/* Initialize addressbook */
require_once("conf_database.php");
/*
$myparams = array();
$myparams['dsn'] = $addrbook_dsn;
$myparams['table'] = 'addressgroups';
$myparams['owner'] = $username;
*/
$abookGroups = new abook_group_database($myparams);
$myGroups = $abookGroups->list_group();

?>



<?php 
bindtextdomain('abook_group', SM_PATH . 'plugins/abook_group/locale');
textdomain('abook_group');
bindtextdomain('squirrelmail', SM_PATH . 'locale');
textdomain('squirrelmail');
?>
<ul>
<li class='nameGroup' onmousemove="changeBackGroup(this)" onclick="changeGroup('<?php echo $groupName;?>',this,true)" onmouseout='$(this).removeClass("backGroup")' class='nameGroup'><?php echo _("All");?></li>
<?php
if (count($myGroups)>0){
?>
  <?php
for ($i=0;$i<count($myGroups);$i++){
    $groupName = $myGroups[$i]['addressgroup'];
    $groupNameURL = urlencode ($groupName);
?>
    <li class='nameGroup' onmousemove="changeBackGroup(this)" onclick="changeGroup('<?php echo $groupName;?>',this,false)" onmouseout='$(this).removeClass("backGroup")'>
        <span onselectstart='return false' class="spanNameGroup"><?php echo $groupName ?></span>
        <?php if($abookGroups->countMembers($groupName))
            echo '<div class="labelNumCont">' . $abookGroups->countMembers($groupName) . '</div>';
        ?>
    </li>
    
  <?php }?>
</ul>
<?php
} else {
?>
<?php 
bindtextdomain('abook_group', SM_PATH . 'plugins/abook_group/locale');
textdomain('abook_group');
echo _("You Currently Do Not Have Any Groups");
bindtextdomain('squirrelmail', SM_PATH . 'locale');
textdomain('squirrelmail');
?>
<?php
}
//display_abook_group_footer();
?>

</body></html>
