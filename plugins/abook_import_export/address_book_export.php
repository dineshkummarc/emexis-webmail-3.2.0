<?php
/**
 * Address Book CSV Export script
 * Copyright (c) 1999-2006 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 * @version $Id: address_book_export.php,v 1.12 2006/07/19 09:39:47 tokul Exp $
 * @package sm-plugins
 * @subpackage abook_import_export
 */

/** SquirrelMail init */
if (file_exists('../../include/init.php')) {
    /* sm 1.5.2+*/
    /* main init script */
    include_once('../../include/init.php');
} else {
    /* sm 1.4.0+ */

    /** @ignore */
    define('SM_PATH', '../../');
    /* main init script */
    include_once(SM_PATH . 'include/validate.php');
}

/* load address book functions */
include_once(SM_PATH . 'functions/addressbook.php');
/* load SendDownloadHeaders() */
include_once(SM_PATH . 'functions/mime.php');
/* load sqm_baseuri() (sm 1.4.0-1.4.5,1.5.0) function */
include_once(SM_PATH . 'functions/display_messages.php');
/* load own functions */
include_once(SM_PATH . 'plugins/abook_import_export/functions.php');

/*
 * Main Code
 */

// activate address book
$abook = addressbook_init(false,false);
// check it
if (!empty($abook->error)) {
    // error should not happen here, because src/addressbook.php would
    // freak out first.
    aie_error_box(nl2br(htmlspecialchars($abook->error)),'',true);
}

if (sqGetGlobalVar('backend',$backend,SQ_POST)) {
    $backend = (int) $backend;
} else {
    $backend = $abook->localbackend;
}

/* Get field delimiter */
if (! sqgetGlobalVar('field_delimiter',$field_delimiter,SQ_POST) ||
    ! in_array($field_delimiter,array("'",'"',',',';','custom'))) {
    $field_delimiter = ',';
} elseif ($field_delimiter=='custom') {
    if (! sqgetGlobalVar('custom_field_delimiter',$field_delimiter,SQ_POST)) {
        $field_delimiter = ',';
    }
}

/* Get text delimiter */
if (! sqgetGlobalVar('text_delimiter',$text_delimiter,SQ_POST) ||
    ! in_array($text_delimiter,array("'",'"',',',';','custom'))) {
    $text_delimiter = '"';
} elseif ($text_delimiter=='custom') {
    if (! sqgetGlobalVar('custom_text_delimiter',$text_delimiter,SQ_POST)) {
        $text_delimiter = '"';
    }
}

/* list addresses */
$rows = $abook->list_addr($backend);

/* switch domain */
bindtextdomain('abook_import_export',SM_PATH . 'locale');
textdomain('abook_import_export');

if (function_exists('bind_textdomain_codeset')) {
    if ($squirrelmail_language == 'ja_JP') {
        bind_textdomain_codeset ('abook_import_export', 'EUC-JP');
    } else {
        bind_textdomain_codeset ('abook_import_export', $default_charset );
    }
}

/* Compare text and field delimiters */
if ($text_delimiter == $field_delimiter) {
    $error_msg = html_tag('p',_("You must use different symbols for text and field delimiters."))
        .html_tag('p',sprintf(_("Return to main %sAddress Book%s page."),
                              '<a href="' . sqm_baseuri() .  'src/addressbook.php">',
                              '</a>'), 'center');
    aie_error_box($error_msg,'',true);
}

/* count returned results */
if (! count($rows)) {
    $error_msg = html_tag('p',_("Selected address book is empty."))
        .html_tag('p',sprintf(_("Return to main %sAddress Book%s page."),
                              '<a href="' . sqm_baseuri() .  'src/addressbook.php">',
                              '</a>'), 'center');
    aie_error_box($error_msg,'',true);
}

// csv mime type is 'text/comma-separated-values' and not 'application/CSV'
SendDownloadHeaders('text', 'comma-separated-values', $username . '-addresses.csv', 1);

/* header row */
$first_key = true;
foreach(array_keys(current($rows)) as $abook_field) {
    if ($abook_field !='extra' && $abook_field!='backend' && $abook_field!='source') {
        if ($first_key) {
            $first_key = false;
        } else {
            echo $field_delimiter;
        }
        switch ($abook_field) {
        case 'firstname':
            $name = 'First Name';
            break;
        case 'lastname':
            $name = 'Last Name';
            break;
        case 'email':
            $name = 'E-mail Address';
            break;
        case 'name':
        case 'label':
        default:
            $name = $abook_field;
            break;
        }
        echo $text_delimiter
            .str_replace($text_delimiter,'\\' . $text_delimiter,$name)
            .$text_delimiter;
    }
}
echo "\r\n";

foreach($rows as $row) {
    /* remove internal SquirrelMail fields */
    unset($row['backend']); // backend id
    unset($row['source']); // backend name
    unset($row['extra']); // extra backend field

    $first_key = true;
    foreach($row as $key => $value) {
        if ($first_key) {
            $first_key = false;
        } else {
            echo $field_delimiter;
        }
        echo $text_delimiter;
        // escape text delimiter
        echo str_replace($text_delimiter,'\\' . $text_delimiter,$value);
        echo $text_delimiter;
    }
    echo "\r\n";
}
