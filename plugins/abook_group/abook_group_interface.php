<?php
/**
 * addrbook_group_interface.php
 * 
 * Handle inserting of group members into the to, cc or bcc field
 *
 * Needs Javascript
 * @copyright (c) 2002 Kelvin Ho
 * @copyright (c) 2002-2004 Jon Nelson <quincy at linuxnotes.net>
 * @copyright (c) 2004-2007 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id: abook_group_interface.php,v 1.12 2007/01/28 10:54:47 tokul Exp $
 * @package sm-plugins
 * @subpackage abook_group
 */

/** @ignore */
if (!defined('SM_PATH'))define('SM_PATH','../../');

include_once(SM_PATH . 'include/validate.php');
include_once(SM_PATH . 'functions/date.php');
include_once(SM_PATH . 'functions/display_messages.php');
include_once(SM_PATH . 'functions/addressbook.php');
include_once(SM_PATH . 'plugins/abook_group/abook_group_database.php');
include_once(SM_PATH . 'plugins/abook_group/abook_group_functions.php');

/* Insert hidden data */
function addr_insert_hidden() {
    global $body, $subject, $send_to, $send_to_cc, $send_to_bcc, $mailbox,
           $identity;

   echo '<input type=hidden value="';
   if (substr($body, 0, 1) == "\r")
       echo "\n";
   echo htmlspecialchars($body) . '" name=body>' . "\n" .
        '<input type=hidden value="' . htmlspecialchars($subject) .
        '" name=subject>' . "\n" .
        '<input type=hidden value="' . htmlspecialchars($send_to) .
        '" name=send_to>' . "\n" .
        '<input type=hidden value="' . htmlspecialchars($send_to_cc) .
        '" name=send_to_cc>' . "\n" .
        '<input type=hidden value="' . htmlspecialchars($send_to_bcc) .
        '" name=send_to_bcc>' . "\n" .
        '<input type=hidden value="' . htmlspecialchars($identity) .
        '" name=identity>' . "\n" .
        '<input type=hidden name=mailbox value="' . htmlspecialchars($mailbox) .
        "\">\n" . '<input type=hidden value="true" name=from_htmladdr_search>' .
        "\n";
   }


/* List search results */
function addr_display_result($res, $includesource = true) {
    global $color, $PHP_SELF;

    if (sizeof($res) <= 0) return;

    echo '<form method="post" action="' . $PHP_SELF . "\">\n" .
         '<input type="hidden" name="html_addr_search_done" value="true">' . "\n";
    addr_insert_hidden();
    $line = 0;

    bindtextdomain('abook_group', SM_PATH . 'plugins/abook_group/locale');
    textdomain('abook_group');
    // FIXME: RTL error - left aligned table header
    echo '<table border="0" width="98%" align="center">' .
         '<tr bgcolor="' . $color[9] . '"><th align="left">&nbsp;</th>' .
         '<th align="left">&nbsp;' . _("Name") . '</th>' .
         '<th align="left">&nbsp;' . _("E-mail") .'</th>' .
         '<th align="left">&nbsp;' . _("Info") .'</th>';

    if ($includesource) {
        echo '<th align="left" width="10%">&nbsp;' . _("Source") . '</th>';
    }

    echo "</tr>\n";

    foreach ($res as $row) {
        echo '<tr';
        if ($line % 2) { echo ' bgcolor="' . $color[0] . '"'; }
        echo ' nowrap><td nowrap align=center width="5%">' .
            '<input type=checkbox name="send_to_search[T' . $line . ']" value = "' .
            htmlspecialchars($row['email']) . '">&nbsp;' . _("To") . '&nbsp;' .
            '<input type=checkbox name="send_to_search[C' . $line . ']" value = "' .
            htmlspecialchars($row['email']) . '">&nbsp;' . _("Cc") . '&nbsp;' .
            '<input type=checkbox name="send_to_search[B' . $line . ']" value = "' .
            htmlspecialchars($row['email']) . '">&nbsp;' . _("Bcc") . '&nbsp;' . 
            '</td><td nowrap>&nbsp;' . $row['name'] . '&nbsp;</td>' .
            '<td nowrap>&nbsp;' . $row['email'] . '&nbsp;</td>' .
            '<td nowrap>&nbsp;' . $row['label'] . '&nbsp;</td>';
        if ($includesource) {
            echo '<td nowrap>&nbsp;' . $row['source'] . '&nbsp;</td>';
        }
        echo "</tr>\n";
        $line ++;
    }
    echo '<tr><td align="center" colspan=';
    if ($includesource) { echo '4'; } else { echo '5'; }
    echo '><input type="submit" name="addr_search_done" VALUE="' .
        _("Use Addresses") . '"></td></tr>' .
        '</table>' .
        '<input type="hidden" value="1" name="html_addr_search_done">' .
        '</form>';
    bindtextdomain('squirrelmail', SM_PATH . 'locale');
    textdomain('squirrelmail');
}

/* --- End functions --- */

global $mailbox, $username,$color;
displayHtmlHeader();
// FIXME: check if $color is not empty
echo "<body text=\"$color[8]\" bgcolor=\"$color[4]\" link=\"$color[7]\" vlink=\"$color[7]\" alink=\"$color[7]\">\n\n";

bindtextdomain('abook_group', SM_PATH . 'plugins/abook_group/locale');
textdomain('abook_group');

/*i Initialize addressbook */

require_once("conf_database.php");
/*
$myparams = array();
$myparams['dsn'] = $addrbook_dsn;
$myparams['table'] = 'addressgroups';
$myparams['owner'] = $username;
*/
$abookGroups = new abook_group_database($myparams);
$abookGroups->personal_abook_table = "address";
$abookGroups->global_abook_table = "globaladdress";

$myGroups = $abookGroups->list_group();
// FIXME: check if $myGroups is array and not error

insert_javascript();
// FIXME: combine three tables into one.
?>
<table width="98%" align="center" cellpadding="2" cellspacing="2" border="0">
<tr><td bgcolor="<?php echo $color[0] ?>">
   <center><b><?php echo _("Groups"); ?></b></center>
</td></tr></table>
<?php 
if (count($myGroups)>0) { 
    for ($i=0;$i<count($myGroups);$i++){
        $groupName = $myGroups[$i]['addressgroup'];
        $myGroupMembers = $abookGroups->list_groupMembers($groupName);
        // FIXME: check if $myGroupMembers is array and not boolean.
        $to_address = "";
        $cc_address = "";
        $bcc_address = "";
        for ($j=0;$j<count($myGroupMembers);$j++){
            // single quotes and slashes are escaped with \, 
            // then string is sanitized with htmlspecialchars (catches double quotes and amp)
            $address = htmlspecialchars(addcslashes($myGroupMembers[$j]['email'],"'\\"));
            $to_address .= "to_address('" . $address . "');";
            $cc_address .= "cc_address('" . $address . "');";
            $bcc_address .= "bcc_address('" . $address . "');";
        }
?>
<table><tr>
   <td><a href="javascript:void(0)" onclick="<?php echo $to_address ?>"><?php echo _("To"); ?></a> 
       <a href="javascript:void(0)" onclick="<?php echo $cc_address ?>"><?php echo _("Cc"); ?></a> 
       <a href="javascript:void(0)" onclick="<?php echo $bcc_address ?>"><?php echo _("Bcc"); ?></a>
   </td>
   <td><?php echo $groupName ?>
   </td>
</tr>
<?php 
} // end of for myGroups 
?>
</table>
<?php
} else{
    echo  _("There are no groups available for selection");
}
echo '<table align="center" bgcolor="'.$color[0].'" width="98%" border="0">'."\n" 
    .'<tr><td>&nbsp;</td>'
    .html_tag( 'td','<input type="button" name="Close" onClick="return self.close()" value='._("Close").'>', 'right' )
    .'</tr>'."\n";
?>
</body></html>
