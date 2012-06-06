<?php

/**
 * functions.php - Addressbook Import-Export functions
 *
 * Copyright (c) 1999-2006 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * Uses standard plugin format to create a couple of forms to
 * enable import/export of CSV files to/from the address book.
 * @version $Id: functions.php,v 1.24 2006/07/22 16:45:08 tokul Exp $
 * @package sm-plugins
 * @subpackage abook_import_export
 */
/** @ignore */
if (!defined('SM_PATH'))
    define('SM_PATH', '../../');

/** load sqm_baseuri() function */
include_once(SM_PATH . 'functions/display_messages.php');
/** load form functions */
include_once(SM_PATH . 'functions/forms.php');

/** set configuration globals */
global $aie_csv_maxsize, $aie_input_charsets, $aie_hide_upload_error;

/** load default configuration */
if (file_exists(SM_PATH . 'plugins/abook_import_export/config_default.php')) {
    include_once(SM_PATH . 'plugins/abook_import_export/config_default.php');
} else {
    // set default config values inside script, if file is removed.
    $aie_csv_maxsize = 5120;
    // input character sets
    $aie_input_charsets = array(
        'windows-1250',
        'windows-1251',
        'windows-1252',
        'windows-1253',
        'windows-1254',
        'windows-1255',
        'windows-1256',
        'windows-1257',
        'windows-1258',
        'cp855',
        'cp866',
        'iso-8859-10',
        'iso-8859-11',
        'iso-8859-13',
        'iso-8859-14',
        'iso-8859-15',
        'iso-8859-16',
        'iso-8859-1',
        'iso-8859-2',
        'iso-8859-3',
        'iso-8859-4',
        'iso-8859-5',
        'iso-8859-6',
        'iso-8859-7',
        'iso-8859-8',
        'iso-8859-9',
        'iso-ir-111',
        'koi8-r',
        'koi8-u',
        'ns_4551_1',
        'tis-620',
        'us_ascii',
        'utf-8');
    $aie_hide_upload_error = false;
}

/** site configuration */
if (file_exists(SM_PATH . 'config/abook_import_export_config.php')) {
    include_once(SM_PATH . 'config/abook_import_export_config.php');
} elseif (file_exists(SM_PATH . 'plugins/abook_import_export/config.php')) {
    include_once(SM_PATH . 'plugins/abook_import_export/config.php');
}

/* Sort input character sets */
natsort($aie_input_charsets);

/**
 * Add import/export form
 * (internal function)
 */
function aie_create_form() {
    global $color, $aie_csv_maxsize, $aie_input_charsets, $default_charset, $aie_hide_upload_error,
    $squirrelmail_language;

    // switch domain
    bindtextdomain('abook_import_export',SM_PATH . 'plugins/abook_import_export/locale');
    textdomain('abook_import_export');

    if (function_exists('bind_textdomain_codeset')) {
        if ($squirrelmail_language == 'ja_JP') {
            bind_textdomain_codeset('abook_import_export', 'EUC-JP');
        } else {
            bind_textdomain_codeset('abook_import_export', $default_charset);
        }
    }

    $aie_delimiter = array("'" => _("Single quotes (')"),
        '"' => _("Double quotes (\")"),
        ',' => _("Comma (,)"),
        ';' => _("Semicolon (;)"),
        'custom' => _("Custom delimiter"));

    // using php for html generation, because formating of mixed php/html code is not good.

      if ((bool) ini_get('file_uploads')) {
            if (isset($_GET['import'])) {
                echo html_tag('table',
                        html_tag('tr',
                                html_tag('td', '<strong>' . _("Address book import") . '</strong>' . "\n", 'center', $color[0])
                        ),
                        'center', '', 'width="100%"');

                echo "<!-- begin csv import form -->\n";
                // don't use MAX_FILE_SIZE input field or don't rely on it.
                // Size can't be controlled in place that can be modified by end user.
                echo '<form id="importForm" enctype="multipart/form-data" action="'
                . sqm_baseuri() . 'plugins/abook_import_export/address_book_import.php'
                . '" method="post">' . "\n";

                echo '<table width="90%" border="0" cellpadding="1" cellspacing="0" align="center">' . "\n";
                echo html_tag('tr',
                        html_tag('td', _("Select file:"), 'right') . "\n" .
                        html_tag('td', addHidden('MAX_FILE_SIZE', $aie_csv_maxsize) .
                                '<input name="smusercsv" type="file" />', 'left'));

                echo html_tag('tr',
                        html_tag('td', _("Max:"), 'right') . "\n" .
                        html_tag('td', aie_display_size($aie_csv_maxsize), 'left'));

                echo html_tag('tr',
                        html_tag('td', _("Input character set:"), 'right') . "\n" .
                        html_tag('td', addSelect('input_charset', $aie_input_charsets, $default_charset), 'left'));

                echo html_tag('tr',
                        html_tag('td', _("Field delimiter:"), 'right') . "\n" .
                        html_tag('td', addSelect('field_delimiter', $aie_delimiter, ',', true), 'left'));

                echo html_tag('tr',
                        html_tag('td', _("Custom field delimiter:"), 'right') . "\n" .
                        html_tag('td', addInput('custom_field_delimiter', ',', 1, 1), 'left'));

                /* fgetcsv enclosure option is available since 4.3.0 */
                if (check_php_version(4, 3, 0)) {
                    echo html_tag('tr',
                            html_tag('td', _("Text delimiter:"), 'right') . "\n" .
                            html_tag('td', addSelect('text_delimiter', $aie_delimiter, '"', true), 'left'));

                    echo html_tag('tr',
                            html_tag('td', _("Custom text delimiter:"), 'right') . "\n" .
                            html_tag('td', addInput('custom_text_delimiter', '"', 1, 1), 'left'));
                }

                echo html_tag('tr',
                        html_tag('td', addSubmit(_("Import CSV File")), 'center', '', 'colspan="2"'));

                echo "</table>\n";
                echo "</form>\n";
                echo "<!-- end csv import form -->\n";
            }
        } elseif (!$aie_hide_upload_error) {
            echo html_tag('table',
                    html_tag('tr',
                            html_tag('td', '<font color="' . $color[2] . '"><strong>' . _("ERROR") . '</strong></font>', 'center', $color[0])) .
                    html_tag('tr',
                            html_tag('td', _("Address book uploads are disabled."), 'center')),
                    'center', '', 'width="95%"');
        }
        
        if (isset($_GET['export'])) {
            echo html_tag('table',
                    html_tag('tr',
                            html_tag('td', '<strong>' . _("Address book export") . '</strong>' . "\n", 'center', $color[0])
                    ),
                    'center', '', 'width="95%"');

            echo "<!-- begin csv export form -->\n";
            echo '<form ENCTYPE="multipart/form-data" action="'
            . sqm_baseuri() . 'plugins/abook_import_export/address_book_export.php'
            . '" method="post">';

            echo '<table width="90%" border="0" cellpadding="1" cellspacing="0" align="center">' . "\n";

            echo html_tag('tr',
                    html_tag('td', _("Field delimiter:"), 'right') . "\n" .
                    html_tag('td', addSelect('field_delimiter', $aie_delimiter, ',', true), 'left'));

            echo html_tag('tr',
                    html_tag('td', _("Custom field delimiter:"), 'right') . "\n" .
                    html_tag('td', addInput('custom_field_delimiter', ',', 1, 1), 'left'));

            /**
             * fgetcsv enclosure option is available since 4.3.0.
             * plugin uses code that doesn't depend on 4.3+ functions.
             * we leave same options in order to create backwards compatible exports.
             */
            if (check_php_version(4, 3, 0)) {
                echo html_tag('tr',
                        html_tag('td', _("Text delimiter:"), 'right') . "\n" .
                        html_tag('td', addSelect('text_delimiter', $aie_delimiter, '"', true), 'left'));

                echo html_tag('tr',
                        html_tag('td', _("Custom text delimiter:"), 'right') . "\n" .
                        html_tag('td', addInput('custom_text_delimiter', '"', 1, 1), 'left'));
            }

            $form = aie_select_backend('list', $bcount);
            if ($bcount > 1) {
                echo html_tag('tr',
                        html_tag('td', _("Use address book:"), 'right') . "\n" .
                        html_tag('td', $form, 'left'));
            } else {
                echo $form;
            }

            echo html_tag('tr',
                    html_tag('td', addSubmit(_("Export to CSV File")), 'center', '', 'colspan="2"'));

            echo "</table>";
            echo "</form>\n";
            echo "<!-- end csv export form -->\n";
        }
        // revert domain
        textdomain('squirrelmail');
    }

    /**
     * returns size integer formated in bytes, Kbytes or Mbytes
     * @param integer $size size in bytes
     * @return string formated size string
     */
    function aie_display_size($size) {
        // make sure that it is integer.
        $size = (int) $size;

        $ret = '';

        if ($size >= (1024 * 1024)) {
            $ret = sprintf(_("%s MB"), round($size / (1024 * 1024), 1));
        } elseif ($size >= 1024) {
            $ret = sprintf(_("%s KB"), round($size / 1024, 1));
        } else {
            $ret = sprintf(_("%s B"), $size);
        }
        return $ret;
    }

    /**
     * Prints selection boxes in imported data table headers
     *
     * Send the field numbers entered in the text boxes by the user back to
     * this script for more processing
     * email is handled differently, not being an array
     * @param integer $csvmax max number of columns
     * @param integer $column column number
     */
    function aie_create_Select($csvmax, $column) {
        // $column is the one that should be selected out of the bunch
        echo "<select name=\"COL$column\">\n";

        if ($column > 5)
            $column = 5; // So we have only our normal choices.

            for ($temp = 0; $temp <= 5; $temp++) {
            echo "<option value=\"$temp\"";
            if ($column == $temp)
                echo " selected";
            if ($temp == 0)
                echo '>' . _("Nickname") . "</option>\n";
            if ($temp == 1)
                echo '>' . _("First Name") . "</option>\n";
            if ($temp == 2)
                echo '>' . _("Last Name") . "</option>\n";
            if ($temp == 3)
                echo '>' . _("Email") . "</option>\n";
            if ($temp == 4)
                echo '>' . _("Additional Info") . "</option>\n";
            if ($temp == 5)
                echo '>' . _("Do Not Include") . "</option>\n";
        }
        echo "</select>\n";
    }

    /**
     * @param string $row
     * @param string $text_delimiter (since 1.0) gets POST parameter to fix
     *  escaped text delimiters. Works only in PHP 4.3.0+
     * @param array $csvorder (since 1.0) controls order imported address book fields
     * @return mixed if string is returned - it contains fatal processing error.
     *  if array - processed csv data. Array keys should be counted. If count = 1,
     *  possible processing error.
     */
    function aie_CSVProcess($row, $text_delimiter, &$csvorder) {
        global $aie_input_charsets, $default_charset;

        // convert character set
        if (sqgetGlobalVar('input_charset', $input_charset, SQ_POST) &&
                function_exists('charset_convert') &&
                $input_charset != $default_charset &&
                in_array($input_charset, $aie_input_charsets)) {
            foreach ($row as $key => $value) {
                $row[$key] = charset_convert($input_charset, $value, $default_charset, false);
            }
        }

        // undo escaped text delimiters
        if (check_php_version(4, 3, 0)) {
            foreach ($row as $key => $value) {
                $row[$key] = str_replace('\\' . $text_delimiter, $text_delimiter, $value);
            }
        }

        // Make sure that it is not LDIF (use 'objectclass' attribute for detection)
        if (preg_match("/^objectclass(?:)$/", trim($row[0])) ||
                preg_match("/^objectclass:.*$/", trim($row[0]))) {
            return _("LDIF import is not supported.");
        }

        // detect header row
        if (preg_grep("/((?:First Name)|(?:Last Name)|(?:E-mail Address))/", $row)) {
            foreach ($row as $key => $value) {
                if ($value == "First Name") {
                    if (isset($csvorder[$key])) {
                        $csvorder[1] = $csvorder[$key];
                    } else {
                        $csvorder[1] = $key;
                    }
                }
                if ($value == "Last Name") {
                    if (isset($csvorder[$key])) {
                        $csvorder[2] = $csvorder[$key];
                    } else {
                        $csvorder[2] = $key;
                    }
                }
                if ($value == "E-mail Address") {
                    if (isset($csvorder[$key])) {
                        $csvorder[3] = $csvorder[$key];
                    } else {
                        $csvorder[3] = $key;
                    }
                }
            }
            return array();
        }

        if (count($csvorder) > 0) {
            // This is swapping elements to make firstname, last name, and email be in the 1,2,3 spot, respectively
            foreach ($csvorder as $key => $value) {
                // check if field is set (maybe csv has less fields).
                $temp = (isset($row[$key]) ? $row[$key] : '');
                $row[$key] = $row[$value];
                $row[$value] = $temp;
            }
            return $row;
        }
        return $row;
    }

    /**
     * fgetcsv wrapper to solve differences between 4.3.0+ and older
     * @param resource $handle
     * @param integer $length
     * @param string $delimiter
     * @param string $enclosure
     * @since 1.0
     */
    function aie_fgetcsv($handle, $length, $delimiter, $enclosure) {
        if (check_php_version(4, 3, 0)) {
            return fgetcsv($handle, $length, $delimiter, $enclosure);
        } else {
            return fgetcsv($handle, $length, $delimiter);
        }
    }

    /**
     * Creates address book selection options
     *
     * Tags use 'backend' input field.
     *
     * Backend ($v-bname, $v->listing and $v-writeable) specifics:
     *
     * local_file - writeable parameter is available. listing parameter
     *   is available since 1.5.1. Older listing behavior defaults to
     *   true
     *
     * global_file - backend is merged with local_file in 1.4.4 and 1.5.1.
     *   writeable parameter is available. listing parameter is 
     *   not available and defaults to true.
     *
     * database - writeable parameter is available. listing parameter 
     *   is available since 1.4.4 and 1.5.1. Older listing behavior
     *   defaults to true.
     *
     * ldap_server - writeable parameter is not available and backend is 
     *   read only. listing parameter is available since 1.5.1. Older 
     *   listing behavior defaults to false. number of returned results
     *   can be limited by backend options. backend can be uninitialized
     *   in some cases.
     *
     * Listing is evaluated by list_addr() function behavior. In some cases
     * backends might allow listing with wide search in search() method, but
     * such backend behavior is treated as unsupported and might be removed
     * in some SquirrelMail version.
     * @param string $listing_type all, write or list
     * @param integer $backend_count returns number of available backends.
     *  It allows to detect which forms tags are used. 0 = empty string, 
     *  1 = hidden input, 2 or more = select box
     *
     * abook_import_export gettext domain must be initialized before calling
     * this function, but code can use any domain
     * @return string html form tags (select or hidden input)
     * @since 1.0
     */
    function aie_select_backend($listing_type, &$backend_count) {
        global $abook;

        // save current gettext domain
        $current_textdomain = textdomain('');

        if (empty($abook) ||
                !is_object($abook) ||
                strtolower(get_class($abook)) != 'addressbook') {

            if ($current_textdomain != 'squirrelmail') {
                /**
                 * switch domain. use short switch because plugin
                 * depends on php gettext or 1.5.1 gettext implementation
                 * and short switches work in both
                 */
                textdomain('squirrelmail');
            }
            // init local and remote backends. don't show errors
            $abook = addressbook_init(false);
        }

        // address book init failed.
        if ($abook == false) {
            // restore domain
            textdomain($current_textdomain);
            // inform about backend counter
            $backend_count = 0;
            return '';
        }

        $available_abook = $abook->localbackend;
        if ($abook->numbackends > 1) {
            $backends = $abook->get_backend_list();

            while (list($undef, $v) = each($backends)) {
                switch ($v->bname) {
                    case 'ldap_server':
                        $writing_enabled = (isset($v->writeable) ? $v->writeable : false);
                        $listing_enabled = (isset($v->listing) ? $v->listing : false);
                        break;
                    default:
                        $writing_enabled = (isset($v->writeable) ? $v->writeable : true);
                        $listing_enabled = (isset($v->listing) ? $v->listing : true);
                }

                switch ($listing_type) {
                    case 'write':
                        if ($writing_enabled) {
                            // add each backend to array
                            $available_abooks[$v->bnum] = $v->sname;
                            // save backend number
                            $available_abook = $v->bnum;
                        }
                        break;
                    case 'list':
                        if ($listing_enabled) {
                            // add each backend to array
                            $available_abooks[$v->bnum] = $v->sname;
                            // save backend number
                            $available_abook = $v->bnum;
                        }
                        break;
                    default:
                        // add each backend to array
                        $available_abooks[$v->bnum] = $v->sname;
                        // save backend number
                        $available_abook = $v->bnum;
                        break;
                }
            }
            if (count($available_abooks) > 1) {
                // restore domain
                textdomain($current_textdomain);
                // inform about backend counter
                $backend_count = count($available_abooks);
                // we have more than one writeable backend
                return addSelect('backend', $available_abooks, null, true);
            }
        }
        // restore domain
        textdomain($current_textdomain);
        // inform about backend counter
        $backend_count = 1;
        // Only one backend exists or is writeable.
        return addHidden('backend', $available_abook);
    }

    /**
     * Own error message function
     *
     * Function provides better controls than internal SquirrelMail
     * error_box() function.
     * @param string $error_msg
     * @param string $error_title
     * @param boolean $close_html
     * @since 1.0
     */
    function aie_error_box($error_msg, $error_title='', $close_html=false) {
        global $pageheader_sent, $color;

        if (!isset($color)) {
            $color = array();
            $color[0] = '#dcdcdc';  /* light gray    TitleBar               */
            $color[1] = '#800000';  /* red                                  */
            $color[2] = '#cc0000';  /* light red     Warning/Error Messages */
            $color[4] = '#ffffff';  /* white         Normal Background      */
            $color[7] = '#0000cc';  /* blue          Links                  */
            $color[8] = '#000000';  /* black         Normal text            */
            $color[9] = '#ababab';  /* mid-gray      Darker version of #0   */
        }

        if (empty($error_title))
            $error_title = _("ERROR");

        /* check if the page header has been sent; if not, send it! */
        if (!isset($pageheader_sent) && !$pageheader_sent) {
            textdomain('squirrelmail');            
            $pageheader_sent = true;
            echo "<body text=\"$color[8]\" bgcolor=\"$color[4]\" link=\"$color[7]\" vlink=\"$color[7]\" alink=\"$color[7]\">\n\n";
        }

        echo '<table cellpadding="1" cellspacing="0" align="center" border="0" bgcolor="' . $color[9] . '">' .
        '<tr><td>' .
        '<table width="100%" cellpadding="0" cellspacing="0" align="center" border="0" bgcolor="' . $color[4] . '">' .
        '<tr><td align="center" bgcolor="' . $color[0] . '">' .
        '<font color="' . $color[2] . '"><b>' . $error_title . '</b></font>' .
        '</td></tr><tr><td>' .
        '<table cellpadding="1" cellspacing="5" align="center" border="0">' .
        '<tr>' . html_tag('td', $error_msg . "\n", 'left') . '</tr></table>' .
        '</td></tr></table></td></tr></table>';
        ?>

        
        <?php
        if ($close_html) {
            die('</body></html>');
        } else {
            // revert domain
            textdomain('abook_import_export');
        }
    }

    if (!function_exists('dgettext')) {

        /**
         * dgettext replacement for broken setups.
         * @ignore
         */
        function dgettext($domain, $str) {
            return $str;
        }

    }

