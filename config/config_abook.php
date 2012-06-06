<?php

/**
 * Abook config overrides.
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

$default_use_javascript_addr_book = false;
$abook_global_file = '';
$abook_global_file_writeable = false;
$abook_global_file_listing = true;
$abook_file_line_length = 2048;

$addrbook_dsn = $dsn_pear;
$addrbook_table = 'address';

$prefs_dsn = $dsn_pear;
$prefs_table = 'userprefs';
$prefs_user_field = 'user';
$prefs_key_field = 'prefkey';
$prefs_val_field = 'prefval';
$addrbook_global_dsn = $dsn_pear;
$addrbook_global_table = 'global_abook';
$addrbook_global_writeable = false;
$addrbook_global_listing = false;
$myparams = array();
$myparams['dsn'] = $dsn_pear; 
// change this table name if you named it differently
$myparams['table'] = 'addressgroups';

?>
