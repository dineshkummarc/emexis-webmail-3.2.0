<?php
/**
 * Plugin init file
 *
 * Copyright (c) 2005 The SquirrelMail Project Team
 * This file is part of SquirrelMail Select Language plugin.
 *
 * Select Language plugin is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Select Language plugin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Select Language plugin; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @version $Id: setup.php,v 1.4 2005/11/29 18:36:31 tokul Exp $
 * @package sm-plugins
 * @subpackage select_language
 */

/**
 * Init function
 */
function squirrelmail_plugin_init_select_language() {
    global $squirrelmail_plugin_hooks;
    $squirrelmail_plugin_hooks['login_form']['select_language']='select_language_form';
    $squirrelmail_plugin_hooks['login_verified']['select_language']='select_language_set';
}

/**
 * Show language selection form
 */
function select_language_form() {
    include_once(SM_PATH.'plugins/select_language/functions.php');
    return select_language_form_function();
}

/**
 * Set language
 */
function select_language_set() {
    include_once(SM_PATH.'plugins/select_language/functions.php');
    select_language_set_function();
}

/**
 * Show plugin version
 * @return string plugin version
 */
function select_language_version() {
    return '1.1';
}
?>
