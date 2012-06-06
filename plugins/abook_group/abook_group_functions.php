<?php
/**
 * abook_group_functions.php
 *
 * @copyright (c) 2002 Kelvin Ho
 * @copyright (c) 2002-2004 Jon Nelson <quincy at linuxnotes.net>
 * @copyright (c) 2004-2007 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id: abook_group_functions.php,v 1.8 2007/01/20 08:27:48 tokul Exp $
 * @package sm-plugins
 * @subpackage abook_group
 */

/* Function to include JavaScript code */
/* This script is from addrbook_search.php*/

function insert_javascript() {
    ?>
    <script language="Javascript"><!--

    function to_and_close($addr) {
        to_address($addr);
        parent.close();
    }

    function to_address($addr) {
        var prefix    = "";
        var pwintype = typeof parent.opener.document.compose;

        $addr = $addr.replace(/ {1,35}$/, "");

        if (pwintype != "undefined") {
            if (parent.opener.document.compose.send_to.value) {
                prefix = ", ";
                parent.opener.document.compose.send_to.value =
                    parent.opener.document.compose.send_to.value + ", " + $addr;
            } else {
                parent.opener.document.compose.send_to.value = $addr;
            }
        }
    }

    function cc_address($addr) {
        var prefix    = "";
        var pwintype = typeof parent.opener.document.compose;

        $addr = $addr.replace(/ {1,35}$/, "");

        if (pwintype != "undefined") {
            if (parent.opener.document.compose.send_to_cc.value) {
                prefix = ", ";
                parent.opener.document.compose.send_to_cc.value =
                    parent.opener.document.compose.send_to_cc.value + ", " + $addr;
            } else {
                parent.opener.document.compose.send_to_cc.value = $addr;
            }
        }
    }

    function bcc_address($addr) {
        var prefix    = "";
        var pwintype = typeof parent.opener.document.compose;

        $addr = $addr.replace(/ {1,35}$/, "");

        if (pwintype != "undefined") {
            if (parent.opener.document.compose.send_to_bcc.value) {
                prefix = ", ";
                parent.opener.document.compose.send_to_bcc.value =
                    parent.opener.document.compose.send_to_bcc.value + ", " + $addr;
            } else {
                parent.opener.document.compose.send_to_bcc.value = $addr;
            }
        }
    }

// --></script>
<?php
} /* End of included JavaScript */

/* Gets nickNameBackEnd Array and prepares into userdata array */
function explodeUserArray ($nickNameBackEnd) {
           $ret = array();
           for ($i=0;$i<count($nickNameBackEnd);$i++){
               $userArray = explode(",",$nickNameBackEnd[$i]);
               $ret[$i]['nickName'] = $userArray[0];
               $ret[$i]['backEndSName'] = $userArray[1];
           }
           return $ret;
}

/**
 *
 */
function display_abook_group_footer () {
    bindtextdomain('abook_group', SM_PATH . 'plugins/abook_group/locale');
    textdomain('abook_group');

    echo '<p align="center">'
        .'<a href="addrbook_search.php">' . _("Add New Members") . '</a> | '
        .'<a href="list_abook_group.php">' . _("List All Groups") . '</a></p>';

    bindtextdomain('squirrelmail', SM_PATH . 'locale');
    textdomain('squirrelmail');
}

/**
 * Temporally function used to check address book backends configuration
 * @param boolean $save_in_session (since 0.51.1) controls saving results of 
 *  tests in session. Variable is used to disable use of session, when session 
 *  is already closed (for example in src/compose.php)
 * @return boolean false if environment is mixed
 * @since 0.51
 */
function agroup_check_backends($save_in_session=true) {

    if (! sqgetGlobalVar('agroup_mixed',$ret,SQ_SESSION)) {
        include_once(SM_PATH . 'functions/addressbook.php');

        $abook = addressbook_init(false,true);
        if ($abook->numbackends > 1) {
            // more than one backend
            $backends=$abook->get_backend_list();
            $ret = true;
            $used_backend = '';

            foreach ($backends as $backend) {
                if ($used_backend == '') {
                    // detect first backend type.
                    $used_backend = $backend->bname;
                } elseif ($backend->bname != $used_backend &&
                          $backend->bname != 'global_' . $used_backend) {
                    $ret = false;
                }
            }
        } elseif($abook->numbackends == 0) {
            // no backends
            $ret = false;
        } elseif (! isset($abook->backends[1]) ||
                  ($abook->backends[1]->bname != 'local_file' &&
                  $abook->backends[1]->bname != 'database')) {
            $ret = false;
        } else {
            $ret = true;
        }
        /**
         * In some cases session is already closed in compose_button_row hook
         */
        if ($save_in_session) {
            sqsession_register($ret,'agroup_mixed');
        }
    } else {
        $ret = (bool) $ret;
    }
    return $ret;
}
