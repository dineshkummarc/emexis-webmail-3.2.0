<?php

/**
 * Server config overrides.
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

$config_use_color = 2;

$org_name      = "WEBMAIL";
$org_logo      = SM_PATH . 'images/sm_logo.png';
$org_logo_width  = '308';
$org_logo_height = '111';
$org_title     = "emexis-webmail";
$signout_page  = '';
$frame_top     = '_top';

$provider_uri     = 'http://www.squirrelmail.org/';
$provider_name     = 'EMEXIS-WEBMAIL';
$motd = "";

$squirrelmail_default_language = 'pt_BR';
$domain                 = 'brc.com.br';

$default_charset       = 'iso-8859-1';
$lossy_encoding        = false;

?>
