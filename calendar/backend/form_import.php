<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


define(SM_PATH,'../../');
define('PAGE_NAME', 'form_import');
/* SquirrelMail required files. */
require_once(SM_PATH . 'functions/utils.php');
require_once(SM_PATH . 'functions/global.php');
require_once(SM_PATH . 'include/load_prefs.php');
global $username, $domain, $show_num,$chosen_theme;

?>
<html>
    <head>
        <link rel="stylesheet" href="<?php echo SM_PATH;?>themes/default/webmail.css" type="text/css"/>
        <link rel="stylesheet" href="<?php echo SM_PATH;?>themes/default/<?php echo $chosen_theme;?>.css" type="text/css"/>
    </head>
    <body class="bodyFileUpload">        
        <form id="formImportEvent" method="post" enctype="multipart/form-data" action="upload_ics.php">
            <table>
                <tr>
                    <td colspan="2"><strong style="font-size:14pt"><?php echo _('Select file ICS');?></strong></td>
                </tr>
                <tr>
                    <td><?php echo _('File')?></td><td><input name="file_up" type="file"/></td>
                </tr>
                <tr>
                    <td colspan="2">
                        <input class="btnUpload" value="Upload file" name="file" type="submit"/>
                    </td>
                </tr>
            </table>
        </form>        
    </body>
</html>
