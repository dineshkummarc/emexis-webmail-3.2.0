<?php
/**
 * config_default.php - Default abook_import_export configuration file
 *
 * Copyright (c) 2005-2006 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 * @version $Id: config_default.php,v 1.6 2006/07/18 08:20:08 tokul Exp $
 * @package sm-plugins
 * @subpackage abook_import_export
 */

/**
 * Controls maximum size of imported csv file in bytes.
 *
 * WARNING: don't set it very big value. Plugin uses session to store
 * imported data and data size should not exceed session storage limits.
 * @global integer $aie_csv_maxsize
 * @since 0.9
 */
$aie_csv_maxsize=15120;

/**
 * Array with character sets that can be used for input conversion
 *
 * Default value is set to character sets supported by standard SquirrelMail 
 * 1.4.6 installation.
 * @global array $aie_input_charsets
 * @since 1.0
 */
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
    'iso-8859-1',
    'iso-8859-2',
    'iso-8859-3',
    'iso-8859-4',
    'iso-8859-5',
    'iso-8859-6',
    'iso-8859-7',
    'iso-8859-8',
    'iso-8859-9',
    'iso-8859-10',
    'iso-8859-11',
    'iso-8859-13',
    'iso-8859-14',
    'iso-8859-15',
    'iso-8859-16',
    'iso-ir-111',
    'koi8-r',
    'koi8-u',
    'ns_4551_1',
    'tis-620',
    'us-ascii',
    'utf-8');

/**
 * Allows to disable error message that is displayed when file uploads are not enabled.
 * @global boolean $aie_hide_upload_error
 * @since 1.0
 */
$aie_hide_upload_error=false;
