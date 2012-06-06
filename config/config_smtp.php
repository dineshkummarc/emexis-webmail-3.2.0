<?php

/**
 * Plugins config overrides.
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

$useSendmail            = false;
$smtpServerAddress      = 'mail.brc.com.br';
$smtpPort               = 25;
$sendmail_path          = '/usr/sbin/sendmail';
$sendmail_args          = '-i -t';
$pop_before_smtp        = false;
$pop_before_smtp_host   = 'false';

$smtp_auth_mech = 'login';
$smtp_sitewide_user = '';
$smtp_sitewide_pass = '';
$use_smtp_tls = false;

?>
