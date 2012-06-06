<?php

/**
 * page_header.php
 *
 * Prints the page header (duh)
 *
 * @copyright 1999-2010 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id: page_header.php 13904 2010-02-13 16:28:10Z kink $
 * @package squirrelmail
 */

/** Include required files from SM */
require_once(SM_PATH . 'functions/strings.php');
require_once(SM_PATH . 'functions/html.php');
require_once(SM_PATH . 'functions/imap_mailbox.php');
require_once(SM_PATH . 'functions/global.php');

/* Always set up the language before calling these functions */
function displayHtmlHeader( $title = 'SquirrelMail', $xtra = '', $do_hook = TRUE ) {
    global $squirrelmail_language;

    if ( !sqgetGlobalVar('base_uri', $base_uri, SQ_SESSION) ) {
        global $base_uri;
    }
    global $theme_css, $custom_css, $pageheader_sent;

    // prevent clickjack attempts
    // FIXME: should we use DENY instead?  We can also make this a configurable value, including giving the admin the option of removing this entirely in case they WANT to be framed by an external domain
    header('X-Frame-Options: SAMEORIGIN');

    echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">' .
         "\n\n" . html_tag( 'html' ,'' , '', '', '' ) . "\n<head>\n" .
         "<meta name=\"robots\" content=\"noindex,nofollow\">\n" .
         "<meta http-equiv=\"x-dns-prefetch-control\" content=\"off\">\n";

    // prevent clickjack attempts using JavaScript for browsers that
    // don't support the X-Frame-Options header...
    // we check to see if we are *not* the top page, and if not, check
    // whether or not the top page is in the same domain as we are...
    // if not, log out immediately -- this is an attempt to do the same
    // thing that the X-Frame-Options does using JavaScript (never a good
    // idea to rely on JavaScript-based solutions, though)
    echo '<script type="text/javascript" language="JavaScript">'
       . "\n<!--\n"
       . 'if (self != top) { try { if (document.domain != top.document.domain) {'
       . ' throw "Clickjacking security violation! Please log out immediately!"; /* this code should never execute - exception should already have been thrown since it\'s a security violation in this case to even try to access top.document.domain (but it\'s left here just to be extra safe) */ } } catch (e) { self.location = "'
       . sqm_baseuri() . 'src/signout.php"; top.location = "'
       . sqm_baseuri() . 'src/signout.php" } }'
       . "\n// -->\n</script>\n";


    if ( !isset( $custom_css ) || $custom_css == 'none' ) {
        if ($theme_css != '') {
            echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"$theme_css\">";
        }
    } else {
        echo '<link rel="stylesheet" type="text/css" href="' .
             $base_uri . 'themes/css/'.$custom_css.'">';
    }

    if ($squirrelmail_language == 'ja_JP') {
        // Why is it added here? Header ('Content-Type:..) is used in i18n.php
        echo "<!-- \xfd\xfe -->\n";
        echo '<meta http-equiv="Content-type" content="text/html; charset=euc-jp">' . "\n";
    }

    if ($do_hook) {
        do_hook('generic_header');
    }

    echo "\n<title>$title</title>$xtra\n";

    /* work around IE6's scrollbar bug */
    echo <<<ECHO
<!--[if IE 6]>
<style type="text/css">
/* avoid stupid IE6 bug with frames and scrollbars */
body {
    width: expression(document.documentElement.clientWidth - 30);
}
</style>
<![endif]-->

ECHO;

    echo "\n</head>\n\n";

    /* this is used to check elsewhere whether we should call this function */
    $pageheader_sent = TRUE;
}

function makeInternalLink($path, $text, $target='') {
    sqgetGlobalVar('base_uri', $base_uri, SQ_SESSION);
    if ($target != '') {
        $target = " target=\"$target\"";
    }
    return '<a class="linkInternal" onclick=openLink("'.$base_uri.$path.'",this)>' . $text  .'</a>';
    //return '<a href="'.$base_uri.$path.'"'.$target.'>'.$text.'</a>';
}

function displayInternalLink($path, $text, $target='') {
    echo makeInternalLink($path, $text, $target);
}

function displayPageHeader($color, $mailbox, $xtra='', $session=false) {

    global $hide_sm_attributions, $frame_top,
           $compose_new_win, $compose_width, $compose_height,
           $attachemessages, $provider_name, $provider_uri,
           $javascript_on, $default_use_mdn, $mdn_user_support,
           $startMessage, $org_title;

    sqgetGlobalVar('base_uri', $base_uri, SQ_SESSION );
    sqgetGlobalVar('delimiter', $delimiter, SQ_SESSION );
    if (!isset($frame_top)) {
        $frame_top = '_top';
    }

    if ($session) {
        $compose_uri = $base_uri.'src/compose.php?mailbox='.urlencode($mailbox).'&amp;session='."$session";
    } else {
        $compose_uri = $base_uri.'src/compose.php?newmessage=1';
        $session = 0;
    }

    // only output JavaScript if actually turned on
    if($javascript_on || strpos($xtra, 'new_js_autodetect_results.value') ) {
        if ( !defined('PAGE_NAME') ) define('PAGE_NAME', NULL);
        switch ( PAGE_NAME ) {
        case 'read_body':
            $js ='';

            // compose in new window code
            if ($compose_new_win == '1') {
                if (!preg_match("/^[0-9]{3,4}$/", $compose_width)) {
                    $compose_width = '640';
                }
                if (!preg_match("/^[0-9]{3,4}$/", $compose_height)) {
                    $compose_height = '550';
                }
                $js .= "function comp_in_new(comp_uri) {\n".
                     "       if (!comp_uri) {\n".
                     '           comp_uri = "'.$compose_uri."\";\n".
                     '       }'. "\n".
                     '    var newwin = window.open(comp_uri' .
                     ', "_blank",'.
                     '"width='.$compose_width. ',height='.$compose_height.
                     ',scrollbars=yes,resizable=yes,status=yes");'."\n".
                     "}\n\n";
            }

            // javascript for sending read receipts
            if($default_use_mdn && $mdn_user_support) {
                $js .= "function sendMDN() {\n".
                         "    mdnuri=window.location+'&sendreceipt=1';\n" .
                         "    window.location = mdnuri;\n" .
                       "\n}\n\n";
            }

            // if any of the above passes, add the JS tags too.
            if($js) {
                $js = "\n".'<script language="JavaScript" type="text/javascript">' .
                      "\n<!--\n" . $js . "// -->\n</script>\n";
            }

            displayHtmlHeader($org_title, $js);
            $onload = $xtra;
          break;
        case 'compose':
            $js = '<script language="JavaScript" type="text/javascript">' .
             "\n<!--\n" .
             "var alreadyFocused = false;\n" .
             "function checkForm() {\n" .
             "\n    if (alreadyFocused) return;\n";

            global $action, $reply_focus;
            if (strpos($action, 'reply') !== FALSE && $reply_focus)
            {
                if ($reply_focus == 'select') $js .= "document.forms['compose'].body.select();}\n";
                else if ($reply_focus == 'focus') $js .= "document.forms['compose'].body.focus();}\n";
                else if ($reply_focus == 'none') $js .= "}\n";
            }
            // no reply focus also applies to composing new messages
            else if ($reply_focus == 'none')
            {
                $js .= "}\n";
            }
            else
                $js .= "    var f = document.forms.length;\n".
                "    var i = 0;\n".
                "    var pos = -1;\n".
                "    while( pos == -1 && i < f ) {\n".
                "        var e = document.forms[i].elements.length;\n".
                "        var j = 0;\n".
                "        while( pos == -1 && j < e ) {\n".
                "            if ( document.forms[i].elements[j].type == 'text' ) {\n".
                "                pos = j;\n".
                "            }\n".
                "            j++;\n".
                "        }\n".
                "        i++;\n".
                "    }\n".
                "    if( pos >= 0 ) {\n".
                "        document.forms[i-1].elements[pos].focus();\n".
                "    }\n".
                "}\n";

            $js .= "// -->\n".
                 "</script>\n";
            $onload = 'onload="checkForm();"';
            displayHtmlHeader($org_title, $js);
            break;

        default:
            $js = '<script language="JavaScript" type="text/javascript">' .
             "\n<!--\n" .
             "function checkForm() {\n".
             "   var f = document.forms.length;\n".
             "   var i = 0;\n".
             "   var pos = -1;\n".
             "   while( pos == -1 && i < f ) {\n".
             "       var e = document.forms[i].elements.length;\n".
             "       var j = 0;\n".
             "       while( pos == -1 && j < e ) {\n".
             "           if ( document.forms[i].elements[j].type == 'text' " .
             "           || document.forms[i].elements[j].type == 'password' ) {\n".
             "               pos = j;\n".
             "           }\n".
             "           j++;\n".
             "       }\n".
             "   i++;\n".
             "   }\n".
             "   if( pos >= 0 ) {\n".
             "       document.forms[i-1].elements[pos].focus();\n".
             "   }\n".
             "   $xtra\n".
             "}\n";

            if ($compose_new_win == '1') {
                if (!preg_match("/^[0-9]{3,4}$/", $compose_width)) {
                    $compose_width = '640';
                }
                if (!preg_match("/^[0-9]{3,4}$/", $compose_height)) {
                    $compose_height = '550';
                }
                $js .= "function comp_in_new(comp_uri) {\n".
                     "       if (!comp_uri) {\n".
                     '           comp_uri = "'.$compose_uri."\";\n".
                     '       }'. "\n".
                     '    var newwin = window.open(comp_uri' .
                     ', "_blank",'.
                     '"width='.$compose_width. ',height='.$compose_height.
                     ',scrollbars=yes,resizable=yes,status=yes");'."\n".
                     "}\n\n";

            }
        $js .= "// -->\n". "</script>\n";


        $onload = 'onload="checkForm();"';
        displayHtmlHeader($org_title, $js);
      } // end switch module
    } else {
        // JavaScript off
        displayHtmlHeader($org_title);
        $onload = '';
    }

    echo "<body text=\"$color[8]\" bgcolor=\"$color[4]\" link=\"$color[7]\" vlink=\"$color[7]\" alink=\"$color[7]\" $onload>\n\n";
    /** Here is the header and wrapping table **/
    $shortBoxName = htmlspecialchars(imap_utf7_decode_local(
                      readShortMailboxName($mailbox, $delimiter)));
    if ( $shortBoxName == 'INBOX' ) {
        $shortBoxName = _("INBOX");
    }
    
    echo '<div id="optionSearch">' .
           '<input type="radio" id="radioBODY" name="where" value="BODY"><label for="radioBODY">' . _("Body")  . '</label></br>
            <input type="radio" id="radioTEXT" checked=checked name="where" value="TEXT"><label for="radioTEXT">' . _("Everywhere")  . '</label></br>
            <input type="radio" id="radioSUBJECT" name="where" value="SUBJECT"><label for="radioSUBJECT">' . _("Subject")  . '</label></br>
            <input type="radio" id="radioFROM" name="where" value="FROM"><label for="radioFROM">' . _("From")  . '</label></br>
            <input type="radio" id="radioCC" name="where" value="CC"><label for="radioCC">' . _("Cc")  . '</label>' .
        '</div>';

   //openLink(link,ele)
   echo '<div id="menuNew" class="menuDrop">'
   . '<ul>'
   . '<li><a id="linkNewMail" href="javascript:openCompose()">' . _("New mail") . '</a></li>'    
   . '<li class="liOpenContact">' . makeInternalLink('src/addressbook.php', _("New contact")) . '</li>'
   . '<li class="liNewFolder"><a href="javascript:newEventMenu()">' .  _("New event") . '</a></li>'
   . '<li class="liOpenGroup">' . makeInternalLink('src/addressbook.php', _("New group")) . '</li>'
   . '<li class="liNewFolder">' . makeInternalLink('src/addressbook.php', _("New folder")) . '</li>'   
   . '</ul>' 
   . '</div>';

   echo '<div id="menuReply" class="menuDrop">'
   . '<ul>'
   . '<li class="replyMenu"><a>' . _("Reply") . '</a></li>'
   . '<li class="replyAllMenu"><a>' . _("Reply all") . '</a></li>'
   . '</ul></div>';

   echo "<a name=\"pagetop\"></a>\n"
        . html_tag( 'table', '', '', $color[4], 'border="0" width="100%" cellspacing="0" cellpadding="2"' ) ."\n"
    /*    . html_tag( 'tr', '', '', $color[9] ) ."\n"
        . html_tag( 'td', '', 'left' ) ."\n";
    if ( $shortBoxName <> '' && strtolower( $shortBoxName ) <> 'none' ) {
        echo '         ' . _("Current Folder") . ": <b>$shortBoxName&nbsp;</b>\n";
    } else {
        echo '&nbsp;';
    }
    echo  "      </td>\n"
        . html_tag( 'td', '', 'right' ) ."<b>\n"
        . '<a href="../src/signout.php">' . _("Sign Out") . '</a>';
    
    echo "</b></td>\n"
        . "   </tr>\n"*/
        . html_tag( 'tr', '', '', $color[4] ) ."\n"
        . ($hide_sm_attributions ? html_tag( 'td', '', 'left', '', 'colspan="2"' )
                                 : html_tag( 'td', '', 'left','','style="padding:0;margin:0;"' ) )
        . "\n";
    $urlMailbox = urlencode($mailbox);
    $startMessage = (int)$startMessage;
    echo '<div class="background"><div class="mail"><a class="newOption">' . _('New')
    . '<img class="arrow" style="outline:none;border:0;" src="../images/arrowdown.png"></a></div>';
    //echo makeComposeLink('src/compose.php?mailbox='.$urlMailbox.'&amp;startMessage='.$startMessage);
     
    echo "<div class='updateMail' style='padding-left:35px' title='" . _('Update') . "' onclick='reloadWebmail()'>"
    . "&nbsp;&nbsp;</div>\n"
    . makeInternalLink('src/addressbook.php','<div title="' .  _('Contacts') . '" class="iconContact"></div>')
    . "<div title='" . _("Delete mails selected") . "'class='deleteMsgIcon'></div>"
    . "<div title='" . _("Print") . "'class='printMsgIcon'></div>";
    global $squirrelmail_language;    
    if($squirrelmail_language == 'en_US' or $squirrelmail_language == 'ja_JP'){
        echo "<div class='replyMsgIconEn'><a><img src='../images/replyicon.png'/>"
        . _('Reply') . "<img class='arrow' src='../images/arrowdown.png'></a></div>";
    }else{
        echo "<div class='replyMsgIcon'><a><img src='../images/replyicon.png'/>&nbsp;"
        . _('Reply') . "<img class='arrow' src='../images/arrowdown.png'></a></div>";
    }
    echo "<div class='forwardMsgIcon'><a><img src='../images/forwardicon.png'/>"
    . _('Forward') . "</a></div>";
    echo "<div class='btnDailyCalendar'><a><img src='../images/calendar.gif'/>&nbsp;"
    . _('Daily') . "</a></div>";
    echo "<div class='btnWeeklyCalendar'><a><img src='../images/calendar_semanal.gif'/>&nbsp;"
    . _('Weekly') . "</a></div>";
    echo "<div class='btnMonthlyCalendar'><a><img src='../images/calendar_mensal.gif'/>&nbsp;"
    . _('Monthly') . "</a></div>";
    echo "<div class='btnListCalendar'><a><img src='../images/calendar_mensal.gif'/>&nbsp;"
    . _('Commitments') . "</a></div>";
    echo "<div class='btnImportEvent'><a><img src='../images/import.png'/>&nbsp;" . _('Import event') . "</a></div>";
    echo "      </div></td>\n";
    echo '<td align=right><div style="float:left;" id="barmenucontrol">
    <div id="search"><img src="../images/iconOptionSearch.png">'
    . '<input class="searchEmpty" type="text" value="' . _("Search mail")
    . '" name="what" size="35"></div>
    <div id="searchEvent"><img src="../images/iconOptionSearch.png">'
    . '<input class="inputSearchEvent" class="searchEmpty" type="text" value="'
    . _("Search event")
    . '" name="searchEvent" size="35"></div>
    <div id="controlPage" style="float:left"></div></div>            '
    . '<div class="optionsWebmail"><img alt="Load" title="' . _('Loading')
    . '" id="preloadMini" src="../images/mini_load_icon.gif">'
    . ' <a title="' . _("Options") . '" onclick=openLink("options.php",this)><img src="../images/options.png"><span>&nbsp;'
    . _('Options') . '</span></a>&nbsp;&nbsp;</div>'
    . '<div id="logout" title="' . _("Signout")
    . '"><a href="../src/signout.php"><img id="logout" src="../images/logout.png"><span>&nbsp;'
    . _("Exit") . '</span></a>&nbsp;</div></td>';
    echo "   </tr>\n".
        "</table>\n\n";
}

/* blatently copied/truncated/modified from the above function */
function compose_Header($color, $mailbox) {

    global $delimiter, $hide_sm_attributions, $base_uri,
           $data_dir, $username, $frame_top, $compose_new_win;

    if (!isset($frame_top)) {
        $frame_top = '_top';
    }

    /*
        Locate the first displayable form element
    */
    if ( !defined('PAGE_NAME') ) define('PAGE_NAME', NULL);
    switch ( PAGE_NAME ) {
    case 'search':
        $pos = getPref($data_dir, $username, 'search_pos', 0 ) - 1;
        $onload = "onload=\"document.forms[$pos].elements[2].focus();\"";
        displayHtmlHeader (_("Compose"));
        break;
    default:
        $js = '<script language="JavaScript" type="text/javascript">' .
             "\n<!--\n" .
             "var alreadyFocused = false;\n" .
             "function checkForm() {\n" .
             "\n    if (alreadyFocused) return;\n";

            global $action, $reply_focus;
            if (strpos($action, 'reply') !== FALSE && $reply_focus)
            {
                if ($reply_focus == 'select') $js .= "document.forms['compose'].body.select();}\n";
                else if ($reply_focus == 'focus') $js .= "document.forms['compose'].body.focus();}\n";
                else if ($reply_focus == 'none') $js .= "}\n";
            }
            // no reply focus also applies to composing new messages
            else if ($reply_focus == 'none')
            {
                $js .= "}\n";
            }
            else
                $js .= "var f = document.forms.length;\n".
                "var i = 0;\n".
                "var pos = -1;\n".
                "while( pos == -1 && i < f ) {\n".
                    "var e = document.forms[i].elements.length;\n".
                    "var j = 0;\n".
                    "while( pos == -1 && j < e ) {\n".
                        "if ( document.forms[i].elements[j].type == 'text' ) {\n".
                            "pos = j;\n".
                        "}\n".
                        "j++;\n".
                    "}\n".
                "i++;\n".
                "}\n".
                "if( pos >= 0 ) {\n".
                    "document.forms[i-1].elements[pos].focus();\n".
                "}\n".
            "}\n";
        $js .= "// -->\n".
                 "</script>\n";
        $onload = 'onload="checkForm();"';
        displayHtmlHeader (_("Compose"), $js);
        break;

    }

    echo "<body text=\"$color[8]\" bgcolor=\"$color[4]\" link=\"$color[7]\" vlink=\"$color[7]\" alink=\"$color[7]\" $onload>\n\n";
    
}

