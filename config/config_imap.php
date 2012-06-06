<?php

/**
 * Imap config overrides.
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

$imapServerAddress = '192.168.253.5';
$imapPort               = 143;
$imap_server_type       = 'courier';
$invert_time            = false;
$optional_delimiter     = 'detect';
$encode_header_key      = '';
$imap_auth_mech = 'login';
$use_imap_tls = false;

?>
