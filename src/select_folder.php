<?php

/**
 * select_folder.php
 *
 * Select for create folder via AJAX
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



$imapConnection = sqimap_login ($username, $key, $imapServerAddress, $imapPort, 0);

// force retrieval of a non cached folderlist
$boxes = sqimap_mailbox_list($imapConnection,true);

/** CREATING FOLDERS **/



echo "<select name=\"subfolder\">\n";

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

echo "</select>\n";
if ($show_contain_subfolders_option) {
    echo '<br />'.
         addCheckBox('contain_subs', FALSE, '1') .' &nbsp;'
       . _("Let this folder contain subfolders")
       . '<br />';
}


/** count special folders **/
foreach ($boxes as $index => $aBoxData) {
    if (! in_array($aBoxData['unformatted'],$skip_folders) && 
        isSpecialMailbox($aBoxData['unformatted'],false) ) {
        $skip_folders[] = $aBoxData['unformatted'];
    }
}

