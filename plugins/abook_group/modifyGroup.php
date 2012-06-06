<?php
/**
 * modifyGroup.php
 *
 * script to modify group
 * @copyright (c) 2002 Kelvin Ho
 * @copyright (c) 2002-2004 Jon Nelson <quincy at linuxnotes.net>
 * @copyright (c) 2004-2006 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id: modifyGroup.php,v 1.11 2006/09/13 16:36:42 tokul Exp $
 * @package sm-plugins
 * @subpackage abook_group
 */

/** @ignore */
if (!defined('SM_PATH'))define('SM_PATH','../../');

include_once(SM_PATH . 'include/validate.php');
include_once(SM_PATH . 'functions/display_messages.php');
include_once(SM_PATH . 'functions/addressbook.php');
include_once(SM_PATH . 'plugins/abook_group/abook_group_database.php');
include_once(SM_PATH . 'plugins/abook_group/abook_group_functions.php');

/* --- End functions --- */

global $mailbox, $username;

/*  Start globals */

sqgetGlobalVar('abook', $abook, SQ_POST);
// We are not in mailbox
// sqgetGlobalVar('mailbox', $mailbox, SQ_POST);
//sqgetGlobalVar('username', $username, SQ_POST);
//sqgetGlobalVar('color', $color, SQ_POST);
sqgetGlobalVar('myparams', $myparams, SQ_POST);
sqgetGlobalVar('message', $message, SQ_POST);
sqgetGlobalVar('groupName', $groupName, SQ_FORM);
//sqgetGlobalVar('groupName', $groupName, SQ_POST);
sqgetGlobalVar('opt', $opt, SQ_FORM);
//sqgetGlobalVar('opt', $opt, SQ_POST);
sqgetGlobalVar('abookGroups', $abookGroups, SQ_POST);
sqgetGlobalVar('newGroupName', $newGroupName, SQ_POST);
sqgetGlobalVar('modifyGroups', $modifyGroups, SQ_POST);
sqgetGlobalVar('groupConfirmed', $groupConfirmed, SQ_POST);
sqgetGlobalVar('groupBackout', $groupBackout, SQ_POST);

/*  End globals */

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

if (!$opt){
    $opt = "edit";
}

if ($groupConfirmed=='') $groupConfirmed=false;

if ($opt=="delete"){
    if(!$groupConfirmed) {
        displayPageHeader($color, 'None');

        bindtextdomain('abook_group', SM_PATH . 'plugins/abook_group/locale');
        textdomain('abook_group');

        echo '<br>' .
            html_tag( 'table', '', 'center', '', 'width="95%" border="0"' ) .
            html_tag( 'tr',
                html_tag( 'td', '<b>' . _("Delete Group") . '</b>', 'center', $color[0] ) 
            ) .
            html_tag( 'tr' ) .
            html_tag( 'td', '', 'center', $color[4] ) .
        _("Are you sure you want to delete ") . '<b>' . $groupName . '</b>?' . 
        '<FORM ACTION="modifyGroup.php" METHOD="POST"><p>'.

        '<INPUT TYPE=HIDDEN NAME="groupName" VALUE="'.$groupName."\">\n" .
        '<INPUT TYPE=SUBMIT NAME="groupConfirmed" VALUE="'._("Yes")."\">\n".
        '<INPUT TYPE=SUBMIT NAME="groupBackout" VALUE="'._("No")."\">\n".
        '</p></FORM><BR></td></tr></table>';
        
        bindtextdomain('squirrelmail', SM_PATH . 'locale');
        textdomain('squirrelmail');
        exit;
    }
}
if($groupConfirmed) {
     $abookGroups->deleteGroup($groupName);
     header("Location: list_abook_group.php");
}
elseif($groupBackout) {
     header("Location: list_abook_group.php");
}
elseif ($opt=="editexe"){
       $abookGroups->modifyGroup($groupName, $newGroupName);
       if ($modifyGroups->error){
           $message = $modifyGroups->error;
       }
}

if ($opt!="edit" and empty($mesage)){
    if ($abookGroups->error){
        $message = $abookGroups->error;
    } else{
        header("Location: list_abook_group.php");
    }
}

displayPageHeader($color, 'None');
?>

<table width="95%" align=center cellpadding=2 cellspacing=2 border=0>
<tr><td bgcolor="<?php echo $color[0] ?>">
   <center><b>
    <?php 
     bindtextdomain('abook_group', SM_PATH . 'plugins/abook_group/locale');
     textdomain('abook_group');
     echo _("Modify Group Name");
     bindtextdomain('squirrelmail', SM_PATH . 'locale');
     textdomain('squirrelmail');
    ?>
   </b></center>
</td></tr></table>
<form name="form" method="post" action="<?php echo $PHP_SELF ?>">
  <p align="center"><b>&nbsp; </b></p>
  <p align="center"><b>
    <?php echo $message ?>
    </b></p>
  <table width="95%" align="center">
    <tr>
     <?php 
      bindtextdomain('abook_group', SM_PATH . 'plugins/abook_group/locale');
      textdomain('abook_group');
      echo '<td width="50%">' . _("Old Group Name") . '</td>';
      bindtextdomain('squirrelmail', SM_PATH . 'locale');
      textdomain('squirrelmail');
     ?>
      <td width="50%"><b> 
        <?php echo $groupName ?>
        </b></td>
    </tr>
    <tr>
     <?php 
      bindtextdomain('abook_group', SM_PATH . 'plugins/abook_group/locale');
      textdomain('abook_group');
      echo '<td width="50%">' . _("New Group Name") . '</td>';
      bindtextdomain('squirrelmail', SM_PATH . 'locale');
      textdomain('squirrelmail');
     ?>
      <td width="50%">
        <input type="text" name="newGroupName" value="<?php echo $groupName  ?>">
      </td>
    </tr>
</table>
  <p align="center">
    <input type="hidden" name="opt" value="editexe">
    <input type="hidden" name="groupName" value="<?php echo $groupName ?>">
    <?php
     bindtextdomain('abook_group', SM_PATH . 'plugins/abook_group/locale');
     textdomain('abook_group');
     echo '<input type="submit" name="Modify" value="' . _("Modify") . '">';
     bindtextdomain('squirrelmail', SM_PATH . 'locale');
     textdomain('squirrelmail');
    ?>
  </p>
</form>
<?php display_abook_group_footer(); ?>
</body></html>
