<?php
/**
 * setup.php -- abook_group plugin
 * Plugin init script
 * @copyright (c) 2002 Kelvin Ho
 * @copyright (c) 2002-2004 Jon Nelson <quincy at linuxnotes.net>
 * @copyright (c) 2004-2007 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id: setup.php,v 1.18 2007/01/20 08:27:48 tokul Exp $
 * @package sm-plugins
 * @subpackage abook_group
 */

/**
 */
function squirrelmail_plugin_init_abook_group() {
    /* Standard initialization API. */
    global $squirrelmail_plugin_hooks;
    
    $squirrelmail_plugin_hooks['compose_button_row']['abook_group'] = 
        'abook_group_setup';
    $squirrelmail_plugin_hooks['optpage_register_block']['abook_group'] = 
        'abook_group_optpage_register_block';
    $squirrelmail_plugin_hooks['options_link_and_description']['abook_group'] = 
        'abook_group_options';
    $squirrelmail_plugin_hooks['right_main_after_header']['abook_group'] = 
        'abook_group_warning';
}

/**
 * Displays plugin version
 * @since 0.2
 * @return string
 */
function abook_group_version() {
    return '0.51.1';
}

/**
 * Registers plugin's option block.
 */
function abook_group_optpage_register_block() {
    // Gets added to the user's OPTIONS page.
    global $optpage_blocks;

    include_once(SM_PATH . 'plugins/abook_group/abook_group_functions.php');

    if (agroup_check_backends()) {

        bindtextdomain('abook_group', SM_PATH . 'plugins/abook_group/locale');
        textdomain('abook_group');
        /* Register abook_group with the $optionpages array. */
        $optpage_blocks[] = array(
            'name' => _("Address Groups"),
            'url'  => SM_PATH . 'plugins/abook_group/list_abook_group.php',
            'desc' => _("You can create address groups and add email addresses from your address book"),
            'js'   => true
            );

        bindtextdomain('squirrelmail', SM_PATH . 'locale');
        textdomain('squirrelmail');
    }
}

/**
 * adds button in compose
 */
function abook_group_setup() {
    global $javascript_on;
    include_once(SM_PATH . 'plugins/abook_group/abook_group_functions.php');
    /* Gets added to the COMPOSE buttons row. */
    if ( $javascript_on && agroup_check_backends(false)) {
        /*
         ** using document.write to hide this functionality from people
         ** with JavaScript turned off.        
         */
        bindtextdomain('abook_group', SM_PATH . 'plugins/abook_group/locale');
        textdomain('abook_group');
        echo "<script type=\"text/javascript\">\n".
            "<!--\n".
            'document.write("<input type=\"button\" value=\"' .
            _("Groups") . '\" onclick=\"window.open(\'../plugins/abook_group/abook_group_interface.php\', \'abookgroup\', \'status=yes,width=550,height=370,resizable=yes,scrollbars=yes\')\">");'. "\n" .
            "//-->\n".
            "</script>\n";
        bindtextdomain('squirrelmail', SM_PATH . 'locale');
        textdomain('squirrelmail');
    }
}

/**
 * Function that displays warnings about unsupported or incorrect squirrelmail setup.
 * @since 0.51
 */
function abook_group_warning() {
    global $color;
    include_once(SM_PATH . 'plugins/abook_group/abook_group_functions.php');
    if (! agroup_check_backends()) {
        include_once(SM_PATH . 'functions/display_messages.php');
        bindtextdomain('abook_group', SM_PATH . 'plugins/abook_group/locale');
        textdomain('abook_group');
        $error = _("Current address book setup is not supported by abook_group plugin.");
        bindtextdomain('squirrelmail', SM_PATH . 'locale');
        textdomain('squirrelmail');
        error_box($error,$color);
    }
}
