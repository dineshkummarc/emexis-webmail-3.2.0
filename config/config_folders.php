<?php

/**
 * Folders config overrides.
 *
 * You can override the config.php settings here.
 * Don't do it unless you know what you're doing.
 * Use standard PHP syntax, see config.php for examples.
 *
 * @copyright &copy; 2009-2009 The BRconnection
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id: config_local.php 13549 2009-04-15 22:00:49Z jervfors $
 * @package emexis-webmail
 * @subpackage config
 */

$default_folder_prefix          = '';
$trash_folder                   = 'INBOX.Trash';
$sent_folder                    = 'INBOX.Sent';
$draft_folder                   = 'INBOX.Drafts';
$default_move_to_trash          = true;
$default_move_to_sent           = true;
$default_save_as_draft          = true;
$show_prefix_option             = false;
$list_special_folders_first     = true;
$use_special_folder_color       = true;
$auto_expunge                   = true;
$default_sub_of_inbox           = true;
$show_contain_subfolders_option = false;
$default_unseen_notify          = 2;
$default_unseen_type            = 1;
$auto_create_special            = true;
$delete_folder                  = false;
$noselect_fix_enable            = false;

?>
