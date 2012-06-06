<?php
/**
 * addrbook_search.php
 *
 * Handle addressbook searching to add into address group
 * added toggle all.
 *
 * NOTE: Modified from addrbook_search from /functions/
 *
 * Version v0.11 - corrected the displayed text that is next to the
 *                 search box, since the search wasn't being performed
 *                 on the 'address' but on the 'name'
 *                   
 *                 Modified: May 11th 2004 - Bryan Loniewski
 *                                           brylon@jla.rutgers.edu
 *
 * @copyright (c) 2002 Kelvin Ho
 * @copyright (c) 2002-2004 Jon Nelson <quincy at linuxnotes.net>
 * @copyright (c) 2004-2006 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id: addrbook_search.php,v 1.12 2006/09/13 16:36:42 tokul Exp $
 * @package sm-plugins
 * @subpackage abook_group
 */

/** @ignore */
if (!defined('SM_PATH'))define('SM_PATH','../../');

include_once(SM_PATH . 'include/validate.php');

/* List search results */
function display_result($res, $includesource = true, $javascript_on=true) {
    global $color, $abook, $srch_rslt_lines;
    if(sizeof($res) <= 0) return;
        
    $line = 0;
    echo '<TABLE BORDER="0" WIDTH="98%" ALIGN=center>' .
         '<TR BGCOLOR="' . $color[9] . '"><TH ALIGN=left>&nbsp;';
/* add javascript for toggle all*/
    if ($javascript_on) {
        echo '<a href="#" onClick="CheckAll();">' . _("Toggle All") . "</a>\n";
    }
    echo '<TH ALIGN=left>&nbsp;' . _("Name") .
         '<TH ALIGN=left>&nbsp;' . _("E-mail") .
         '<TH ALIGN=left>&nbsp;' . _("Info");

    if ($includesource) {
        echo '<TH ALIGN=left WIDTH="10%">&nbsp;' . _("Source");
    }    
    echo "</TR>\n";
    
    while (list($undef, $row) = each($res)) {
        echo '<tr';
        if ($line % 2) { echo ' bgcolor="' . $color[0] . '"'; }
        echo ' nowrap><td valign=top nowrap align=center width="5%">' .
             '<input type="checkbox" name="nickNameBackEnd[]" value="'. $row['nickname'].",". $abook->backends[$row['backend']]->bnum .'"></td>'.
             '<td nowrap valign=top>&nbsp;' . $row['name'] . '&nbsp;</td><td nowrap valign=top>' .
             '&nbsp;' . $row['email'] . '&nbsp;</td>' .
             '<td valign=top>&nbsp;' . $row['label'] . '&nbsp;</td>';
        if ($includesource) {
            echo '<td nowrap valign=top>&nbsp;' . $row['source']. '&nbsp;</td>';
        }

        echo "</TR>\n";
        $line++;
    }
    $srch_rslt_lines = $line;
    echo '</TABLE>';
}

/* ================= End of functions ================= */
    
include_once(SM_PATH . 'functions/addressbook.php');
include_once(SM_PATH . 'plugins/abook_group/abook_group_database.php');
include_once(SM_PATH . 'plugins/abook_group/abook_group_functions.php');
    
/*  Start Globals  */

sqgetGlobalVar('srch_rslt_lines', $srch_rslt_lines, SQ_POST);
// system vars extracted from post. Incompatible with sm 1.5.1 and 1.4.5 sqgetglobal function.
//sqgetGlobalVar('color', $color, SQ_POST);
sqgetGlobalVar('query', $query, SQ_POST);
sqgetGlobalVar('show', $show, SQ_POST);
sqgetGlobalVar('listall', $listall, SQ_POST);
sqgetGlobalVar('addNewGroup', $addNewGroup, SQ_POST);
sqgetGlobalVar('addToGroup', $addToGroup, SQ_POST);
sqgetGlobalVar('backend', $backend, SQ_POST);
sqgetGlobalVar('newGroup', $newGroup, SQ_POST);
sqgetGlobalVar('nickNameBackEnd', $nickNameBackEnd, SQ_POST);
sqgetGlobalVar('abookGroups', $abookGroups, SQ_POST);
sqgetGlobalVar('userData', $userData, SQ_POST);
sqgetGlobalVar('myGroups', $myGroups, SQ_POST);
sqgetGlobalVar('groupName', $groupName, SQ_POST);
sqgetGlobalVar('group', $group, SQ_POST);

/*  End Globals  */

require_once("conf_database.php");

$abookGroups = new abook_group_database($myparams);
$abookGroups->personal_abook_table = "address";
$abookGroups->global_abook_table = "globaladdress";

displayPageHeader($color, 'None');

/* Initialize vars */
if (!isset($query) || isset($listall)) { $query = ''; }
if (!isset($show))  { $show  = ''; }

/* Initialize addressbook without remote backends */
$abook = addressbook_init(true,true);

/* add javascript for toggle all*/
if ($javascript_on) {
    echo '<script language="JavaScript">' .
        "\n<!-- \n" .
        "function CheckAll() {\n" .
        "   for (var i = 0; i < document.sform.elements.length; i++) {\n" .
        "       if( document.sform.elements[i].type == 'checkbox' ) {\n" .
        "           document.sform.elements[i].checked = !(document.sform.elements[i].checked);\n".
        "       }\n" .
        "   }\n" .
        "}\n" .
        "//-->\n" .
        '</script>';
}

bindtextdomain('abook_group', SM_PATH . 'plugins/abook_group/locale');
textdomain('abook_group');

/* Create search form */
echo '<p>&nbsp;</p>';
echo '<form name="sform" action="' . $PHP_SELF . '" method="post">' . "\n" ;
echo '<table border="0" width="100%">' . '<tr><td nowrap valign=middle>' . "\n" .
    '  <strong>' . _("Search for name to add") . "</strong>\n" .
    '  <input type="text" name="query" value="' . htmlspecialchars($query) . 
    '" size="26">' ."\n";

/* List all backends to allow the user to choose where to search */
if ($abook->numbackends > 1) {
    echo '<strong>' . _("in") . '</strong>&nbsp;<select name="backend">'."\n".
        '<option value=-1'. (($backend == -1) ? " selected" : "").'>' . _("All address books") . "\n";
    $ret = $abook->get_backend_list();
    while (list($undef,$v) = each($ret)) {
        echo '<option value=' . $v->bnum . (($backend == $v->bnum) ? " selected" : "") . '>' . $v->sname . "\n";
    }
    echo "</select>\n";
} else {
    echo '<input type="hidden" name="backend" value="-1">' . "\n";
}
        
echo '<input type="submit" value="' . _("Search") . '">' .
    '&nbsp;|&nbsp;<input type="submit" value="' . _("List all") .
    '" name="listall">' . "\n" .
    '</td><!--TD ALIGN=right>' . "\n" .
    '<INPUT TYPE=button VALUE="' . _("Close window") .
    '" onclick="parent.close();">' . "\n" .
    '</TD--></TR></TABLE>' . "\n";

/* Add New Group */
if (!empty($addNewGroup)) {
    if ($newGroup){
        if (isset($nickNameBackEnd) && !empty($nickNameBackEnd)){

            $userData = explodeUserArray($nickNameBackEnd);

            $abookGroups->addToGroup($userData, $newGroup, true);
           
            if ($abookGroups->error){
                echo "<P ALIGN=center><STRONG>".$abookGroups->error."</STRONG></P>";
            } else{
                echo '<P ALIGN=center><STRONG>' . _("Add Successful") . '</STRONG></P>';
            }
        }
    } else{
        echo '<P ALIGN=center><STRONG>' . _("New Group Name is Empty") . '</STRONG></P>';
    }
}

/* Add To Group */
elseif (!empty($addToGroup)) {
    if (isset($nickNameBackEnd) && !empty($nickNameBackEnd)){

        $userData = explodeUserArray($nickNameBackEnd);

        $abookGroups->addToGroup($userData, $group);

        if ($abookGroups->error){
            echo "<P ALIGN=center><STRONG>".$abookGroups->error."</STRONG></P>";
        } else{
            echo '<P ALIGN=center><STRONG>' . _("Add Successful") . '</STRONG></P>';
        }
    }
}

/* Show addressbook */
if (!empty($listall)) {
    if($backend != -1 || $show == 'blank') {
        if ($show == 'blank') {
            $backend = $abook->localbackend;
        }
        $res = $abook->list_addr($backend);

        if(is_array($res)) {
            usort($res,'alistcmp');
            display_result($res, false, $javascript_on);
        } else {
            echo '<P ALIGN=center><STRONG>' .
                sprintf(_("Unable to list addresses from %s"),
                        $abook->backends[$backend]->sname) .
                '</STRONG></P>' . "\n";
        }
    } else {
        $res = $abook->list_addr();
        usort($res,'alistcmp');
        display_result($res, true, $javascript_on);
    }
} else {
    /* Empty search */
    if (empty($query) && empty($show) && empty($listall) && empty($addNewGroup) && empty($addToGroup)) {
        echo '<P ALIGN=center><BR>' . _("No persons matching your search was found") . "</P>\n";
    }

    /* Do the search */
    if (!empty($query) && empty($listall)) {
    
        if($backend == -1) {
            $res = $abook->s_search($query);
        } else {
            $res = $abook->s_search($query, $backend);
        }
        
        if (!is_array($res)) {
            echo '<P ALIGN=center><B><BR>' .
                _("Your search failed with the following error(s)") .
                ':<br>' . $abook->error . "</B></P>\n</BODY></HTML>\n";
            exit;
        }
        
        if (sizeof($res) == 0) {
            echo '<P ALIGN=center><BR><B>' .
                _("No persons matching your search was found") .
                ".</B></P>\n</BODY></HTML>\n";
            exit;
        }

        display_result($res, true, $javascript_on);
    }
}
?>   
<p align="center"> 
  
<?php
if ($srch_rslt_lines > 0) {
    echo "<TABLE>";
    $myGroups = $abookGroups->list_group();
    if (count($myGroups) > 0) {
        echo ' <TR><TD>' . _("Add to Existing Group: ") . '<select name="group">';
        for ($i=0;$i<count($myGroups);$i++){
            $groupName = $myGroups[$i]['addressgroup'];
            echo "<option value=\"$groupName\"".(($group == $groupName)? " SELECTED ": "") .">";
            echo $groupName ."</option>";
        }
?>
  </select>
<?php 
        echo ' <input type="submit" name="addToGroup" value="' . _("Add") . '">';
?>
 </TD></TR>
<?php
       echo '<TR><TD>&nbsp; ' . _("OR") . '</TD></TR>';
    }
 ?>
 <TR><TD>
 <?php 
    echo _("Add to New Group (supply group name):");
?>
   <input type="text" name="newGroup">
<?php 
    echo ' <input type="submit" name="addNewGroup" value="' .  _("Add") . '">';
 ?>

 </TD></TR>
 <?php
}
echo "</table></form>\n";

display_abook_group_footer();
?>
</body></html>
