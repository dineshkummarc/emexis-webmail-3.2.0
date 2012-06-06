<?php

/**
 * display_messages.php
 *
 * This contains all messages, including information, error, and just
 * about any other message you can think of.
 *
 * @copyright 1999-2010 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id: display_messages.php 13893 2010-01-25 02:47:41Z pdontthink $
 * @package squirrelmail
 */

/**
 * including plugin functions
 */
require_once(SM_PATH . 'functions/plugin.php');

function error_message($message, $mailbox, $sort, $startMessage, $color) {

    global $default_folder_prefix;

    $urlMailbox = urlencode($mailbox);
    $string = '<tr><td align="center">' . $message . '</td></tr>'.
              '<tr><td align="center">'.
              '<a href="'.sqm_baseuri()."src/right_main.php?sort=$sort&amp;startMessage=$startMessage&amp;mailbox=$urlMailbox\">";

    if (!empty($default_folder_prefix)) {
        if (strpos($mailbox, $default_folder_prefix) === 0)
            $mailbox = substr($mailbox, strlen($default_folder_prefix));
    }
    
    $string .= sprintf (_("Click here to return to %s"),
                  htmlspecialchars(imap_utf7_decode_local($mailbox))).
              '</a></td></tr>';
    error_box($string, $color);
}

function plain_error_message($message, $color) {
    error_box($message, $color);
}

function logout_error( $errString, $errTitle = '' ) {
    include_once( SM_PATH . 'functions/utils.php' );
        echo msgErrorPage($errString);   
}

function error_box($string, $color) {
    global $pageheader_sent, $org_title;

    if ( !isset( $color ) ) {
        $color = array();
        $color[0]  = '#dcdcdc';  /* light gray    TitleBar               */
        $color[1]  = '#800000';  /* red                                  */
        $color[2]  = '#cc0000';  /* light red     Warning/Error Messages */
        $color[4]  = '#ffffff';  /* white         Normal Background      */
        $color[7]  = '#0000cc';  /* blue          Links                  */
        $color[8]  = '#000000';  /* black         Normal text            */
        $color[9]  = '#ababab';  /* mid-gray      Darker version of #0   */
    }
    if ( !isset( $org_title ) ) {
        $org_title = "SquirrelMail";
    }

    $err = _("ERROR");

    $ret = concat_hook_function('error_box', $string);
    if($ret != '') {
        $string = $ret;
    }

    /* check if the page header has been sent; if not, send it! */
    if(!isset($pageheader_sent) && !$pageheader_sent) {
        /* include this just to be sure */
        include_once( SM_PATH . 'functions/page_header.php' );
        displayHtmlHeader($org_title.': '.$err);
        $pageheader_sent = TRUE;
        echo "<body text=\"$color[8]\" bgcolor=\"$color[4]\" link=\"$color[7]\" vlink=\"$color[7]\" alink=\"$color[7]\">\n\n";
    }

    echo '<table width="100%" cellpadding="1" cellspacing="0" align="center" border="0" bgcolor="'.$color[9].'">'.
         '<tr><td>'.
         '<table width="100%" cellpadding="0" cellspacing="0" align="center" border="0" bgcolor="'.$color[4].'">'.
         '<tr><td align="center" bgcolor="'.$color[0].'">'.
         '<font color="'.$color[2].'"><b>' . $err . ':</b></font>'.
         '</td></tr><tr><td>'.
         '<table cellpadding="1" cellspacing="5" align="center" border="0">'.
         '<tr>' . html_tag( 'td', $string."\n", 'left') . '</tr></table>'.
         '</td></tr></table></td></tr></table>';
}

/**
 * Adds message that informs about non fatal error that can happen while saving preferences
 * @param string $message error message
 * @since 1.5.1 and 1.4.5
 */
function error_option_save($message) {
    global $optpage_save_error;

    if (! is_array($optpage_save_error) )
        $optpage_save_error=array();

    $optpage_save_error=array_merge($optpage_save_error,array($message));
}
