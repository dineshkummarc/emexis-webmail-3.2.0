<?php

/**
 * General config overrides.
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
$data_dir                 = '../data';
$attachment_dir           = '../attach';
$dir_hash_level           = 0;
$default_left_size        = '150';
$force_username_lowercase = false;
$default_use_priority     = true;
$hide_sm_attributions     = false;
$default_use_mdn          = true;
$edit_identity            = true;
$edit_name                = true;
$hide_auth_header         = false;
$allow_thread_sort        = false;
$allow_server_sort        = false;
$allow_charset_search     = true;
$uid_support              = true;

$no_list_for_subscribe = false;
$session_name = 'SQMSESSID';
$only_secure_cookies   = true;
$config_location_base     = '';

?>
