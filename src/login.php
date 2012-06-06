<?php

/**
 * login.php -- simple login screen
 *
 * This a simple login screen. Some housekeeping is done to clean
 * cookies and find language.
 *
 * @copyright 1999-2010 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id: login.php 13946 2010-06-21 00:43:54Z pdontthink $
 * @package squirrelmail
 */

/** This is the login page */
define('PAGE_NAME', 'login');

/**
 * Path for SquirrelMail required files.
 * @ignore
 */
define('SM_PATH','../');

/* SquirrelMail required files. */
require_once(SM_PATH . 'functions/global.php');
require_once(SM_PATH . 'functions/i18n.php');
require_once(SM_PATH . 'functions/plugin.php');
require_once(SM_PATH . 'functions/constants.php');
require_once(SM_PATH . 'functions/page_header.php');
require_once(SM_PATH . 'functions/html.php');
require_once(SM_PATH . 'functions/forms.php');

/**
 * $squirrelmail_language is set by a cookie when the user selects
 * language and logs out
 */
set_up_language($squirrelmail_language, TRUE, TRUE);

/**
 * In case the last session was not terminated properly, make sure
 * we get a new one, but make sure we preserve session_expired_*
 */
$sep = '';
$sel = '';
sqGetGlobalVar('session_expired_post', $sep, SQ_SESSION);
sqGetGlobalVar('session_expired_location', $sel, SQ_SESSION);

/* blow away session */
sqsession_destroy();

/**
 * in some rare instances, the session seems to stick
 * around even after destroying it (!!), so if it does,
 * we'll manually flatten the $_SESSION data
 */
if (!empty($_SESSION)) {
    $_SESSION = array();
}

/**
 * Allow administrators to define custom session handlers
 * for SquirrelMail without needing to change anything in
 * php.ini (application-level).
 *
 * In config_local.php, admin needs to put:
 *
 *     $custom_session_handlers = array(
 *         'my_open_handler',
 *         'my_close_handler',
 *         'my_read_handler',
 *         'my_write_handler',
 *         'my_destroy_handler',
 *         'my_gc_handler',
 *     );
 *     session_module_name('user');
 *     session_set_save_handler(
 *         $custom_session_handlers[0],
 *         $custom_session_handlers[1],
 *         $custom_session_handlers[2],
 *         $custom_session_handlers[3],
 *         $custom_session_handlers[4],
 *         $custom_session_handlers[5]
 *     );
 * 
 * We need to replicate that code once here because PHP has
 * long had a bug that resets the session handler mechanism
 * when the session data is also destroyed.  Because of this
 * bug, even administrators who define custom session handlers
 * via a PHP pre-load defined in php.ini (auto_prepend_file)
 * will still need to define the $custom_session_handlers array 
 * in config_local.php.
 */
global $custom_session_handlers;
if (!empty($custom_session_handlers)) {
    $open    = $custom_session_handlers[0];
    $close   = $custom_session_handlers[1];
    $read    = $custom_session_handlers[2];
    $write   = $custom_session_handlers[3];
    $destroy = $custom_session_handlers[4];
    $gc      = $custom_session_handlers[5];
    session_module_name('user');
    session_set_save_handler($open, $close, $read, $write, $destroy, $gc);
}

/* put session_expired_* variables back in session */
sqsession_is_active();
if (!empty($sel)) {
    sqsession_register($sel, 'session_expired_location');
    if (!empty($sep)) 
        sqsession_register($sep, 'session_expired_post');
}

// Disable Browser Caching
//
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: Sat, 1 Jan 2000 00:00:00 GMT');

do_hook('login_cookie');
?>

<link rel="stylesheet" href="../themes/default/login.css" type="text/css">

<?php
$loginname_value = (sqGetGlobalVar('loginname', $loginname) ? htmlspecialchars($loginname) : '');

/* Output the javascript onload function. */


$custom_css = 'none';

// Load default theme if possible
if (@file_exists($theme[$theme_default]['PATH']))
   @include ($theme[$theme_default]['PATH']);

if (! isset($color) || ! is_array($color)) {
    // Add default color theme, if theme loading fails
    $color = array();
    $color[0]  = '#dcdcdc';  /* light gray    TitleBar               */
    $color[1]  = '#800000';  /* red                                  */
    $color[2]  = '#cc0000';  /* light red     Warning/Error Messages */
    $color[4]  = '#ffffff';  /* white         Normal Background      */
    $color[7]  = '#0000cc';  /* blue          Links                  */
    $color[8]  = '#000000';  /* black         Normal text            */
}
?>
<script src="../js/jquery-1.4.2.min.js"></script>
<?php
echo "<body text=\"$color[8]\" bgcolor=\"$color[4]\" link=\"$color[7]\" vlink=\"$color[7]\" alink=\"$color[7]\" >" .
     "\n" . addForm('redirect.php', 'post', 'login_form');

$username_form_name = 'login_username';
$password_form_name = 'secretkey';
do_hook('login_top');

if(sqgetGlobalVar('mailtodata', $mailtodata)) {
    $mailtofield = addHidden('mailtodata', $mailtodata);
} else {
    $mailtofield = '';
}

/* If they don't have a logo, don't bother.. */
if (isset($org_logo) && $org_logo) {
    /* Display width and height like good little people */
    $width_and_height = '';
    if (isset($org_logo_width) && is_numeric($org_logo_width) &&
     $org_logo_width>0) {
        $width_and_height = " width=\"$org_logo_width\"";
    }
    if (isset($org_logo_height) && is_numeric($org_logo_height) &&
     $org_logo_height>0) {
        $width_and_height .= " height=\"$org_logo_height\"";
    }
}
?>
<div class="topLogin">
    <img class="logoTop" src="../images/mail.png"><div class="messageLogin">
        <?php echo _('Welcome to Emexis-Webmail');?>
    </div>
</div>
<center style="height:100%">
    <div class="screenLogin">
        <div>
            <span><?php echo _('ACCESS WEBMAIL');?></span>
            <table>
                <tr> 
                    <td><?php echo _('Name');?></td>
                    <td>
                    <?php
                        echo addInput($username_form_name, $loginname_value, 0, 0, 'class="mailInput" onfocus="alreadyFocused=true;"');?>
                    </td>
                </tr>
                <tr>
                    <td><?php echo _('Password');?>&nbsp;&nbsp;</td>
                    <td><?php echo addPwField($password_form_name, null, 'class="passInput" onfocus="alreadyFocused=true;"');?></td>
                </tr>
                <tr>
                    <?php
                        do_hook('login_form');
                    ?>
                </tr>
                <tr>
                    <td colspan="2"  align="right" class="rowSubmit"><br>
                    <?php                     
                        echo addSubmit(_("Login"));
                    ?>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</center>

<?php

addHidden('just_logged_in', '1');

echo '</form>' . "\n";

do_hook('login_bottom');
?>

</body>

</html>
