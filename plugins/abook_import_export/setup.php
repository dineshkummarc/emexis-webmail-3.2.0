<?php
/**
 * setup.php
 *
 * Copyright (c) 1999-2006 The SquirrelMail Project Team
 * Copyright (c) 2007 Tomas Kuliavas <tokul@users.sourceforge.net>
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * Uses standard plugin format to create a couple of forms to
 * enable import/export of CSV files to/from the address book.
 * @version $Id: setup.php,v 1.14 2007/06/09 08:38:42 tokul Exp $
 * @package sm-plugins
 * @subpackage abook_import_export
 */

/**
 * Init plugin
 */
function squirrelmail_plugin_init_abook_import_export() {
    global $squirrelmail_plugin_hooks;
    $squirrelmail_plugin_hooks["addressbook_import_export"]["abook_import_export"] = "abook_import_export";
}

/**
 * Displays plugin's version
 * @return string version number
 */
function abook_import_export_version() {
    return '1.1';
}

/**
 * Adds import/export form to addresses page
 */
function abook_import_export() {
    include_once(SM_PATH . 'plugins/abook_import_export/functions.php');
    aie_create_form();
}
