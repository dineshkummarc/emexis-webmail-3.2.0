<?php

/**
 * SquirrelMail Configuration File
 * Created using the configure script, conf.pl
 */


global $version;
$config_version = '1.4.21';

define('SM_PATH', '../');

@include SM_PATH . 'config/config_database.php';
@include SM_PATH . 'config/config_server.php';
@include SM_PATH . 'config/config_smtp.php';
@include SM_PATH . 'config/config_imap.php';
@include SM_PATH . 'config/config_folders.php';
@include SM_PATH . 'config/config_general.php';
@include SM_PATH . 'config/config_plugins.php';
@include SM_PATH . 'config/config_themes.php';
@include SM_PATH . 'config/config_ldap.php';
@include SM_PATH . 'config/config_abook.php';
@include SM_PATH . 'config/config_timezone.php';
if(file_exists(SM_PATH . 'config/config_wizard.php'))
	@include SM_PATH . 'config/config_wizard.php';

?>
