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
include_once(SM_PATH . 'functions/utils.php');

define(THEME,get_theme());
echo  "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . SM_PATH . "themes/css/" . THEME . "/options.css\">"
	. "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . SM_PATH . "themes/css/" . THEME . "/links_read.css\">";

global $in,$elements;
/* List search results */
function display_result($res, $includesource = true, $javascript_on=true) {
    global $color, $abook, $srch_rslt_lines;
    if(sizeof($res) <= 0) return;
    $line = 0;
	echo "<div>$str</div>";
	global $in,$elements;
	$in =0;
    while (list($undef, $row) = each($res)) {
		$in++;
	    $elements[$in] = $row['nickname'];
		echo '<div id="' . $row['nickname']  . '">' . $row['name'] . '</div>';

       $line++;
    }
    $srch_rslt_lines = $line;
	?>
	<script type="text/javascript">
	// Custom drop actions for <div id="dropBox"> and <div id="leftColumn">
	function dropItems(idOfDraggedItem,targetId,x,y){
    	if(targetId=='members'){    // Item dropped on <div id="dropBox">
        	var obj = document.getElementById(idOfDraggedItem);
	        if(obj.parentNode.id=='membersContent')return;      
    	    document.getElementById('membersContent').appendChild(obj); // Appending dragged element as child of target box
	    }
	    if(targetId=='notMembers'){ // Item dropped on <div id="leftColumn">
    	    var obj = document.getElementById(idOfDraggedItem);
        	if(obj.parentNode.id=='resSearch')return;   
	        document.getElementById('resSearch').appendChild(obj);  // Appending dragged element as child of target box
	    }
	}
	function onDragFunction(cloneId,origId){
    	self.status = 'Started dragging element with id ' + cloneId;
	    var obj = document.getElementById(cloneId);
    	obj.style.border='1px solid #F00';
	}
</script>
<?php
}

/* ================= End of functions ================= */
    
include_once(SM_PATH . 'functions/addressbook.php');
include_once(SM_PATH . 'functions/utils.php');
include_once(SM_PATH . 'plugins/abook_group/abook_group_database.php');
include_once(SM_PATH . 'plugins/abook_group/abook_group_functions.php');
    
/*  Start Globals  */

sqgetGlobalVar('srch_rslt_lines', $srch_rslt_lines, SQ_POST);
// system vars extracted from post. Incompatible with sm 1.5.1 and 1.4.5 sqgetglobal function.
//sqgetGlobalVar('color', $color, SQ_POST);
sqgetGlobalVar('query', $query, SQ_POST);
sqgetGlobalVar('show', $show, SQ_POST);

//sqgetGlobalVar('listall', $listall, SQ_POST);
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


/*
if(empty($listall))*/
$listall = 'List all';
$backend = -1;


/*  End Globals  */
define(THEME,get_theme());
define(CONTACTS,true);

require_once("conf_database.php");
/*$myparams = array();
$myparams['dsn'] = $addrbook_dsn;
$myparams['table'] = 'addressgroups';
$myparams['owner'] = $username;*/

$abookGroups = new abook_group_database($myparams);
$abookGroups->personal_abook_table = "address";
$abookGroups->global_abook_table = "global_abook";

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


echo '<form name="sform" action="' . $PHP_SELF . '" method="post">' . "\n" ;

/* List all backends to allow the user to choose where to search */
if ($abook->numbackends > 1) {
    echo '<input type="hidden" name="backend" value="-1">' . "\n";
}
        
echo '<div>';
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
		include "../../class/Datacon.class.php";
		global $dsn_pear,$username;
		$objdb = new Datacon($dsn_pear);
        $query =  sprintf("SELECT * FROM %s WHERE owner='%s' and  nickname not in (select nickname from addressgroups where addressgroup = '%s')",
	                       'address', $username,addslashes($_GET['groupname']));
        $res = & $objdb->db->query($query);

        if (DB::isError($res)) {
            return $objdb->db->set_error(sprintf(_("Database error: %s"),
                                            DB::errorMessage($res)));
        }
		$ret = array();
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
            array_push($ret, array('nickname'  => $row['nickname'],
                                   'name'      => "$row[firstname] $row[lastname]",
                                   'firstname' => $row['firstname'],
                                   'lastname'  => $row['lastname'],
                                   'email'     => $row['email'],
                                   'label'     => $row['label'],
                                   'backend'   => '-1',
                                   'source'    => ''));
        }
		
        usort($ret,'alistcmp');
        display_result($ret, true, $javascript_on);
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
</div>
<script type="text/javascript">

// Custom drop actions for <div id="dropBox"> and <div id="leftColumn">

function dropItems(idOfDraggedItem,targetId,x,y){
    if(targetId=='members'){    // Item dropped on <div id="dropBox">
        var obj = document.getElementById(idOfDraggedItem);
        if(obj.parentNode.id=='membersContent')return;      
        document.getElementById('membersContent').appendChild(obj); // Appending dragged element as child of target box

    }

    if(targetId=='notMembers'){ // Item dropped on <div id="leftColumn">
        var obj = document.getElementById(idOfDraggedItem);
        if(obj.parentNode.id=='resSearch')return;   
        document.getElementById('resSearch').appendChild(obj);  // Appending dragged element as child of target box

    }
}

function onDragFunction(cloneId,origId){
    self.status = 'Started dragging element with id ' + cloneId;
    var obj = document.getElementById(cloneId);
    obj.style.border='1px solid #F00';

}

function moveCont(){	
	$("#resSearch div").css("cursor","move");
	var dragDropObj = new DHTMLgoodies_dragDrop();
<?php
	for($i=1;$i<=$in;$i++){
		global $elements;
		$box = $elements[$i];	
		echo "dragDropObj.addSource('$box',true,true,true,false,'onDragFunction');"; 
	}
?>
	dragDropObj.addTarget('members','dropItems');   // Set <div id="dropBox"> as a drop target. Call function dropItems on drop
	dragDropObj.addTarget('notMembers','dropItems'); // Set <div id="leftColumn"> as a drop target. Call function dropItems on drop
	dragDropObj.init();
}
setTimeout("moveCont()",2500);
</script>

</body></html>
