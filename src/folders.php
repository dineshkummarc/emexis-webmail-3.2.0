<?php

/**
 * folders.php
 *
 * Handles all interaction between the user and the other folder
 * scripts which do most of the work. Also handles the Special
 * Folders.
 *
 * @copyright 1999-2010 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id: folders.php 13893 2010-01-25 02:47:41Z pdontthink $
 * @package squirrelmail
 */

/** This is the folders page */
define('PAGE_NAME', 'folders');

/**
 * Path for SquirrelMail required files.
 * @ignore
 */
define('SM_PATH','../');

/* SquirrelMail required files. */
require_once(SM_PATH . 'include/validate.php');
require_once(SM_PATH . 'functions/imap.php');
require_once(SM_PATH . 'functions/plugin.php');
require_once(SM_PATH . 'functions/html.php');
require_once(SM_PATH . 'functions/forms.php');

/* get globals we may need */

sqgetGlobalVar('username', $username, SQ_SESSION);
sqgetGlobalVar('key', $key, SQ_COOKIE);
sqgetGlobalVar('delimiter', $delimiter, SQ_SESSION);
sqgetGlobalVar('onetimepad', $onetimepad, SQ_SESSION);

sqgetGlobalVar('success', $success, SQ_GET);

/* end of get globals */


echo '<body class="folder">';
echo '<div style="height:350px;overflow:auto">';
echo '<br />' .
    html_tag( 'table', '', 'center', 'white', 'width="95%" cellpadding="1" cellspacing="0" border="0"' ) .
        html_tag( 'tr' ) .
            html_tag( 'td', '', 'center' ) . 
                html_tag( 'table', '', 'center', '', 'width="100%" cellpadding="5" cellspacing="0" border="0"' ) .
                    html_tag( 'tr' ) .
                        '<td style="text-align:center">';
                        

if ( isset($success) && $success ) {

    $td_str = '<b>';

    switch ($success)
    {
        case 'subscribe':
            $td_str .=  _("Subscribed successfully.");
            break;
        case 'unsubscribe':
            $td_str .=  _("Unsubscribed successfully.");
            break;
        case 'delete':
            $td_str .=  _("Deleted folder successfully.");
            break;
        case 'create':
            $td_str .=  _("Created folder successfully.");
            break;
        case 'rename':
            $td_str .=  _("Renamed successfully.");
            break;
        case 'subscribe-doesnotexist':
            $td_str .=  _("Subscription Unsuccessful - Folder does not exist.");
            break;
    }

    $td_str .= '</b><br />';


    echo html_tag( 'table',
                html_tag( 'tr',
                     html_tag( 'td', $td_str .
                               '<a href="../src/left_main.php" target="left">' .
                               _("refresh folder list") . '</a>' ,
                     'center' )
                ) ,
            'center', '', 'width="100%" cellpadding="4" cellspacing="0" border="0"' );
}

echo "\n<br />";

$imapConnection = sqimap_login ($username, $key, $imapServerAddress, $imapPort, 0);

// force retrieval of a non cached folderlist
$boxes = sqimap_mailbox_list($imapConnection,true);

/** CREATING FOLDERS **/
echo html_tag( 'table', '', 'center', '', 'width="70%" cellpadding="4" cellspacing="0" border="0"' ) .
            html_tag( 'tr',
                '<td class="titleMenuFolder"><b>' . _("Create Folder") . '</b></td>'
                //html_tag( 'td', '<b>' . _("Create Folder") . '</b>', 'center', $color[9] )
            ) .
            html_tag( 'tr' ) .
                '<td class="lineMenuFolder" style="text-align:center" width="50%">' .
     addForm('folders_create.php', 'POST', 'cf', '', '', '', TRUE).
     addInput('folder_name', '', 25).
     "<br />\n". _("as a subfolder of"). '<br />'.
     "<tt><select name=\"subfolder\">\n";

$show_selected = array();
$skip_folders = array();
$server_type = strtolower($imap_server_type);
if ( $server_type == 'courier' ) {
  if ( $default_folder_prefix == 'INBOX.' ) {
    array_push($skip_folders, 'INBOX');
  }
} elseif ( $server_type == 'bincimap' ) {
    if ( $default_folder_prefix == 'INBOX/' ) {
        // We don't need INBOX, since it is top folder
        array_push($skip_folders, 'INBOX');
    }
}

if ( $default_sub_of_inbox == false ) {
    echo '<option selected="selected" value="">[ '._("None")." ]</option>\n";
} else {
    echo '<option value="">[ '._("None")." ]</option>\n";
    $show_selected = array('inbox');
}

// Call sqimap_mailbox_option_list, using existing connection to IMAP server,
// the arrays of folders to include or skip (assembled above), 
// use 'noinferiors' as a mailbox filter to leave out folders that can not contain other folders.
// use the long format to show subfolders in an intelligible way if parent is missing (special folder)
echo sqimap_mailbox_option_list($imapConnection, $show_selected, $skip_folders, $boxes, 'noinferiors', true);

echo "</select></tt>\n";
if ($show_contain_subfolders_option) {
    echo '<br />'.
         addCheckBox('contain_subs', FALSE, '1') .' &nbsp;'
       . _("Let this folder contain subfolders")
       . '<br />';
}

echo "<input onclick=createFolder($('input[name=folder_name]').val(),$('select[name=subfolder]').val()) type=\"button\" value=\""._("Create")."\" />\n";
echo "</form></td></tr>\n";

echo html_tag( 'tr',
            '<td class="lineMenuFolder" style="text-align:left">'
        ) ."\n";

/** count special folders **/
foreach ($boxes as $index => $aBoxData) {
    if (! in_array($aBoxData['unformatted'],$skip_folders) && 
        isSpecialMailbox($aBoxData['unformatted'],false) ) {
        $skip_folders[] = $aBoxData['unformatted'];
    }
}

/** RENAMING FOLDERS **/
echo html_tag( 'tr',
            '<td class="titleMenuFolder"><b>' . _("Rename a Folder") . '</b></td>'
        ) .
        html_tag( 'tr' ) .
        '<td class="lineMenuFolder" style="text-align:center">';

if (count($skip_folders) < count($boxes)) {
    echo addForm('folders_rename_getname.php')
       . "<tt><select name=\"old\">\n"
       . '         <option value="">[ ' . _("Select a folder") . " ]</option>\n";

    // use existing IMAP connection, we have no special values to show, 
    // but we do include values to skip. Use the pre-created $boxes to save an IMAP query.
    // send NULL for the flag - ALL folders are eligible for rename!
    // use long format to make sure folder names make sense when parents may be missing.
    echo sqimap_mailbox_option_list($imapConnection, 0, $skip_folders, $boxes, NULL, true);

    echo "</select></tt>\n".
         '<input onclick=renameFolder() type="button" value="'.
         _("Rename").
         "\" />\n".
         "</form></td></tr>\n";
} else {
    echo _("No folders found") . '<br /><br /></td></tr>';
}
$boxes_sub = $boxes;

echo html_tag( 'tr',
            '<td class="lineMenuFolder" style="text-align:center">'
        ) ."\n";

/** DELETING FOLDERS **/
echo html_tag( 'tr',
            '<td class="titleMenuFolder"><b>' . _("Delete Folder") . '</b></td>'
        ) .
        html_tag( 'tr' ) .
        '<td class="lineMenuFolder" style="text-align:center">';

if (count($skip_folders) < count($boxes)) {
    echo addForm('folders_delete.php')
       . "<tt><select name=\"mailbox\">\n"
       . '         <option value="">[ ' . _("Select a folder") . " ]</option>\n";

    // send NULL for the flag - ALL folders are eligible for delete (except what we've got in skiplist)
    // use long format to make sure folder names make sense when parents may be missing.
    echo sqimap_mailbox_option_list($imapConnection, 0, $skip_folders, $boxes, NULL, true);

    echo "</select></tt>\n"
       . '<input onclick=confirmDeleteFolder($("select[name=mailbox]").val()) type="button" value="'
       . _("Delete")
       . "\" />\n"
       . "</form></td></tr>\n";
} else {
    echo _("No folders found") . "<br /><br /></td></tr>";
}

echo html_tag( 'tr',
            '<td class="lineMenuFolder" style="text-align:left">'
        ) ."</table>\n";


/** UNSUBSCRIBE FOLDERS **/
echo html_tag( 'table', '', 'center', '', 'width="70%" cellpadding="4" cellspacing="0" border="0"' ) .
            html_tag( 'tr',
               '<td colspan="2" style="text-align:center;" class="titleMenuFolder"><b>' . _("Unsubscribe") . '/' . _("Subscribe") . '</b></td>'
            ) .
            html_tag( 'tr' ) .
                '<td class="lineMenuFolder" style="text-align:center" width="50%">';
                

if (count($skip_folders) < count($boxes)) {
    echo addForm('folders_subscribe.php?method=unsub', 'post', '', '', '', '', TRUE)
       . "<tt><select class='selectUnsub'   name=\"mailbox[]\" multiple=\"multiple\" size=\"8\">\n";
    for ($i = 0; $i < count($boxes); $i++) {
        $use_folder = true;
        if (! isSpecialMailbox($boxes[$i]["unformatted"],false)) {
            $box = $boxes[$i]["unformatted-dm"];
            $box2 = str_replace(array(' ','<','>'), array('&nbsp;','&lt;','&gt;'),
                                imap_utf7_decode_local($boxes[$i]["unformatted-disp"]));
            echo "         <option value=\"$box\">$box2</option>\n";
        }
    }
    echo "</select></tt><br /><br />\n"
       . '<input type="button" onclick="unSubFolders()" type="button" value="'
       . _("Unsubscribe")
       . "\" />\n"
       . "</form></td>\n";
} else {
    echo _("No folders were found to unsubscribe from!") . '</td>';
}
$boxes_sub = $boxes;

/** SUBSCRIBE TO FOLDERS **/
echo '<td class="lineMenuFolder" style="text-align:center" width="50%">';
if(!$no_list_for_subscribe) {
  $boxes_all = sqimap_mailbox_list_all ($imapConnection);

  $box = $box2 = array();
  for ($i = 0, $q = 0; $i < count($boxes_all); $i++) {
    $use_folder = true;
    for ($p = 0; $p < count ($boxes); $p++) {
        if ($boxes_all[$i]['unformatted'] == $boxes[$p]['unformatted']) {
            $use_folder = false;
            continue;
        } else if ($boxes_all[$i]['unformatted-dm'] == $folder_prefix) {
            $use_folder = false;
        }
    }
    if ($use_folder == true) {
        $box[$q] = $boxes_all[$i]['unformatted-dm'];
        $box2[$q] = imap_utf7_decode_local($boxes_all[$i]['unformatted-disp']);
        $q++;
    }
  }
  
  if (count($box) > 0) {
    echo addForm('folders_subscribe.php?method=sub', 'post', '', '', '', '', TRUE)
       . '<tt><select class="selectSub" name="mailbox[]" multiple="multiple" size="8">';

    for ($q = 0; $q < count($box); $q++) {      
        echo '         <option value="' . $box[$q] . '">' .
            str_replace(array(' ','<','>'),array('&nbsp;','&lt;','&gt;'),$box2[$q])."</option>\n";
    }      
    echo '</select></tt><br /><br />'
       . '<input type="button" onclick="subFolders()" value="'. _("Subscribe") . "\" />\n"
       . "</form></td></tr></table><br />\n";
  } else {
    echo _("No folders were found to subscribe to!") . '</td></tr></table>';
  }
} else {
  /* don't perform the list action -- this is much faster */
  echo addForm('folders_subscribe.php?method=sub', 'post', '', '', '', '', TRUE)
     . _("Subscribe to:") . '<br />'
     . '<tt><input type="text" name="mailbox[]" size="35" />'
     . '<input type="submit" value="'. _("Subscribe") . "\" />\n"
     . "</form></td></tr></table>\n";
}
echo "\n<br /><br />\n";

do_hook('folders_bottom');
?>
    </td></tr>
    </table>
</td></tr>
</table>
</div>
<?php
   sqimap_logout($imapConnection);
?>
</body></html>
