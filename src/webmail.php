<?php

/**
 * webmail.php 
 *
 *
 * @copyright 1999-2010 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id: webmail.php 13893 2010-01-25 02:47:41Z pdontthink $
 * @package squirrelmail
 */

/** This is the webmail page */
define('PAGE_NAME', 'webmail');

/**
 * Path for SquirrelMail required files.
 * @ignore
 */
define('SM_PATH','../');
define('SM_BOX_UNCOLLAPSED', 0);
define('SM_BOX_COLLAPSED',   1);
/* SquirrelMail required files. */
require_once(SM_PATH . 'include/validate.php');
require_once(SM_PATH . 'calendar/class/Calendar.php');
require_once(SM_PATH . 'calendar/class/Events.php');
require_once(SM_PATH . 'functions/imap.php');
require_once(SM_PATH . 'functions/utils.php');
require_once(SM_PATH . 'functions/forms.php');

sqgetGlobalVar('username', $username, SQ_SESSION);
sqgetGlobalVar('delimiter', $delimiter, SQ_SESSION);
sqgetGlobalVar('onetimepad', $onetimepad, SQ_SESSION);
sqgetGlobalVar('right_frame', $right_frame, SQ_GET);

//LEFT-Main
sqgetGlobalVar('key', $key, SQ_COOKIE);
sqgetGlobalVar('fold', $fold, SQ_GET);
sqgetGlobalVar('unfold', $unfold, SQ_GET);
sqgetGlobalVar('auto_create_done',$auto_create_done,SQ_SESSION);

$imapConnection = sqimap_login($username, $key, $imapServerAddress, $imapPort, 10);

if (sqgetGlobalVar('sort', $sort)) {
    $sort = (int) $sort;
}

if (sqgetGlobalVar('startMessage', $startMessage)) {
    $startMessage = (int) $startMessage;
}

if (!sqgetGlobalVar('mailbox', $mailbox)) {
    $mailbox = 'INBOX';
}

if(sqgetGlobalVar('mailtodata', $mailtodata)) {
    $mailtourl = 'mailtodata='.urlencode($mailtodata);
} else {
    $mailtourl = '';
}

// this value may be changed by a plugin, but initialize
// it first to avoid register_globals headaches
//
do_hook('webmail_top');

/**
 * We'll need this to later have a noframes version
 *
 * Check if the user has a language preference, but no cookie.
 * Send him a cookie with his language preference, if there is
 * such discrepancy.
 */
$my_language = getPref($data_dir, $username, 'language');
if ($my_language != $squirrelmail_language) {
    sqsetcookie('squirrelmail_language', $my_language, time()+2592000, $base_uri);
}

//set_up_language($squirrelmail_language, TRUE, TRUE);
//echo $squirrelmail_language;
set_up_language($my_language);


global $color,$mailbox,$data_dir,$username,$org_name;
$horizontal = true;

$align = getPref($data_dir, $username, 'layout');


global $load_listmail, $select_checkbox;


define("WEBMAIL_ALIGN",$align);

global $username, $domain, $show_num,$chosen_theme;
	//echo $chosen_theme;

?>
<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 //EN\">
<html>
    <head>
          <meta name=\"robots\" content=\"noindex,nofollow\">
          <link rel="shortcut icon" href="../images/favicon.ico" type="image/x-icon" />
          <link rel='stylesheet' type='text/css' href='../calendar/frontend/fullcalendar/fullcalendar.css' />
          <link rel='stylesheet' type='text/css' href='../calendar/frontend/fullcalendar/fullcalendar.print.css' media='print' />
          <link rel='stylesheet' type='text/css' href='../calendar/frontend/fullcalendar/fullcalendar.print.css' media='print' />
          <title><?php echo $org_title;?></title>
          <?php
            loadCss($chosen_theme);
            loadCss('webmail');
            loadCss("jquery-ui");
	    	loadCss("notification");          
          ?>                                           
    </head>     
    <body>
        <input type="hidden" id="select_checkbox" value="<?php echo $msg_with_checkbox;?>">
        <input type="hidden" id="alignWebmail" value="<?php echo $align;?>">
        <input type="hidden" id="language" value="<?php echo $my_language;?>">
        <input type="hidden" id="show_num" value="<?php echo $show_num;?>">
        <input type="hidden" id="email" value="<?php echo $username . '@' . $domain;?>">
        <input type="hidden" id="username" value="<?php echo $username;?>">
        <div id="loader" class="loader">
            <img src="../images/load.gif"><div class="text"><?php echo _("Loading...");?></div>
        </div>
        <div class="header">
        <?php
            displayPageHeader($color, $mailbox);
        ?>            
        </div>
        <div class="body">
            <div class="left" onselectstart='return false'>
            </div>
            <div id="divisorLeft">&nbsp;</div>
            <div class="pagelink"></div>
            <?php 
                if(strcasecmp($align,'horizontal') == 0){
                    //Layout - Horizontal            ?>
            <div id="calendar">
                <div id="agenda">
                    <div id="widgetCalendar"></div>
                </div>
                <?php
                     echo listAgenda();
                ?>
            </div>
            <div class="right">
                <!--<div id="labelBox">Caixa de entrada</div>-->
                <?php
                global $mailbox, $srt, $color,$thread_sort_messages;                
                echo printHeader($mailbox, $srt, '', !$thread_sort_messages);                
                ?>
                <div id="listmails" class="listmailHorizontal" onselectstart='return false'>
                    <div class="listmail">
                        <center> 
                            <div class="divTable">
                                <div class="divRow">
                                    <div class="divCol">
                                        <a class="gobox"><?php echo _('Go to inbox');?></a>
                                    </div>
                                </div>
                            </div>
                        </center>
                    </div>                 
                </div>                
                <div id="divisorHorizontal"></div>
                <div id="read" class="readHorizontal">
                <?php
                    echo dashBoard();
                ?>
                </div>
            </div>
            <?php
                }elseif(strcasecmp($align,'vertical') == 0){
                    //Layout - Vertical
            ?>
            <div id="calendar">
                <div id="agenda">
                    <div id="widgetCalendar"></div>
                </div>
                <?php
                     echo listAgenda();
                ?>
            </div>
            <div class="right">                
                <div style="width:300px" class="headerMsgVer">
                    <?php
                       echo printHeader($mailbox, $srt, '', !$thread_sort_messages);
                     ?>
                    <!-- <div class="headerTeste" style="width:300px;background-color:silver;color:white">DE FROM DATA</div>        -->
                </div>
                <?php
                /*    global $mailbox, $srt, $color,$thread_sort_messages;
                    echo printHeader($mailbox, $srt, $color, !$thread_sort_messages);*/
                 ?>
                <div class="listmailVertical">                     
                    <div class="listmail">
                        <div class="divTable">
                            <div class="divRow">
                                <div class="divCol">
                                    <center><a  href="#" class="gobox"><?php echo _('Go to inbox');?></a></center>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="divisorVertical"></div>
                <div id="read" class="readVertical">
                   Dashboard
                </div>
            </div>
            <?php
                }else{
                //Layout em nova janela
            ?>
            <div id="calendar">
                <div id="agenda">
                    <div id="widgetCalendar"></div>
                </div>
                <?php
                     echo listAgenda();
                ?>
            </div>
            <div class="right">               
                <?php
                    global $mailbox, $srt, $color,$thread_sort_messages;
                    echo printHeader($mailbox, $srt,'', !$thread_sort_messages);
                 ?>
                <div id="listmail" class="listmailWindow">
                    <div class="listmail">
                        <div class="divTable">
                            <div class="divRow">
                                <div class="divCol">
                                    <center><a  href="#" class="gobox"><?php echo _('Go to inbox');?></a></center>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="read" class="readHorizontal">
                   DASHBOARD
                </div>
            </div>
            <?php
                }
            ?>
            <div class="barcontrol">
                <div></div>
            </div> 
        </div> 
        <!-- Menu Context -->
        <ul id="myMenu" class="contextMenu">
            <li class="open"><a href="#open"><?php echo _("Open");?></a></li>
            <li class="reply"><a href="#reply"><?php echo _("Reply");?></a></li>
            <li class="replyall"><a href="#replyall"><?php echo _("Reply all");?></a></li>
            <li class="forward"><a href="#forward"><?php echo _("Forward");?></a></li> 
            <li class="forward"><a href="#forward_as_attachment"><?php echo _("Forward as attachment");?></a></li>
            <li class="msgunread"><a href="#msgunread"><?php echo _("Mark as unread");?></a></li>
            <li class="msgread"><a href="#msgread"><?php echo _("Mark as read");?></a></li>
            <li class="editmsgnew"><a href="#editmsg"><?php echo _("Edit Message as New");?></a></li>
            <li class="delete"><a href="#delete"><?php echo _("Delete");?></a></li>
            <li class="quit"><a href="#quit"><?php echo _("Quit");?></a></li>
        </ul>
        <!-- Menu Context Folders -->
        <ul id="myMenuFolder" class="contextMenu">
            <li class="open"><a href="#open"><?php echo _("Open");?></a></li>
            <li class="reply"><a href="#rename"><?php echo _("Rename folder");?></a></li>
            <li class="delete"><a href="#delete"><?php echo _("Delete folder");?></a></li>
            <!--<li class="forward"><a href="#forward"><?php echo _("Move folder");?></a></li>            -->
            <li class="addfolder"><a href="#addfolder"><?php echo _("Add folder");?></a></li>
            <li class="quit"><a href="#quit"><?php echo _("Quit");?></a></li>
        </ul>
        <!-- Menu Context Inbox,Sent -->
        <ul id="myMenuInbox" class="contextMenu">
            <li class="open"><a href="#open"><?php echo _("Open");?></a></li>
            <li class="refresh"><a href="#refresh"><?php echo _("Refresh");?></a></li>
            <li class="addfolder"><a href="#addfolder"><?php echo _("Add folder");?></a></li>
            <li class="quit"><a href="#quit"><?php echo _("Quit");?></a></li>
        </ul>
        <!-- Menu Context Inbox,Sent -->
        <ul id="myMenuOutr" class="contextMenu">
            <li class="open"><a href="#open"><?php echo _("Open");?></a></li>
            <li class="addfolder"><a href="#addfolder"><?php echo _("Add folder");?></a></li>
            <li class="quit"><a href="#quit"><?php echo _("Quit");?></a></li>
        </ul>
         <!-- Menu Context Trash -->
        <ul id="myMenuTrash" class="contextMenu">
            <li class="open"><a href="#create"><?php echo _("Open");?></a></li>
            <li class="delete"><a href="#empty"><?php echo _("Empty");?></a></li>
            <li class="quit"><a href="#quit"><?php echo _("Quit");?></a></li>
        </ul>
                
        <!-- Dialog confirmar deletar mensagens -->
        <div id="dialogConfirmDelete" title="<?php echo _("Delete messages");?>?">
        	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
                    <?php echo _('Do you want to delete messages selected?');?>
                </p>
        </div>
        <div id="importEvent" title="<?php echo _("Import");?>">
            <div>
                <form id="formImportEvent" action="../calendar/backend/teste.php">
                    <table>
                        <tr>
                            <td><span class="fileLabel"><?php echo _('File')?></span></td>
                            <td><input id="fileToUpload" type="file"/></td>
                        </tr>
                        <tr>
                            <td colspan="2"><input value="Upload file" type="submit"/></td>
                        </tr>
                    </table>
                </form>
            </div>
        </div>
        <!-- Dialog leitura de emails -->
        <div id="dialog1" title="<?php echo _("Read");?>">
            <div id="readDialog"></div>
        </div>
        <!-- Helper quando é feito o drag das mensagens -->
        <div id="boxAjuda">
            <ul></ul>
        </div>
        <div id="dialogPrint" title="<?php echo _("Print");?>">
            <div></div>
        </div>        
        <div id="dialogConfirmDeleteFolder" title="<?php echo _("Delete folder");?>?">
            <p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
                <?php echo _('Do you want to delete folder selected?');?>
            </p>
        </div>        
        <div id="dialogCreate" title="<?php echo _("Create folder");?>">
            <div>
                 <?php echo _("Name") . ': '. addInput('folder_name', '', 25);?>
                 <br /><?php echo "\n". _("as a subfolder of"). '<br />';?>
                 <div style="float:left;width:100%" id="selectfolder"><?php echo _("Wait");?>...&nbsp;&nbsp;</div><br>
                 <div style="text-align:right;width:100%"><input type="button" value="Criar"></div>
            </div>            
        </div>
        <div class="dialogOptions" title="<?php echo _("Options");?>">
            <div>
                <ul class="tabs">
                    <li class="activeli"><a href="#tab1"><?php echo _("Personal Information");?></a></li>
                    <li><a href="#tab2"><?php echo _("Display Preferences");?></a></li>
                    <li><a href="#tab3"><?php echo _('Filters');?></a></li>
                </ul>
                <!-- single pane. it is always visible -->
                <div class="tab_container">
                    <div id="tab1" class="tab_content"><?php echo _("Loading");?></div>
                    <div id="tab2" class="tab_content"><?php echo _("Loading");?></div>
                    <div id="tab3" class="tab_content">
						<iframe  style="border:0px;width:750px;height:380px;" src="<?php echo SM_PATH . 'plugins/filters/options.php';?>"></iframe>
					</div>
                </div>
                <div class="divSaveOption">
                   <input type="button" onclick="$('.dialogOptions').dialog('close')" value="<?php echo _('Close');?>" class="btnCancelOption">
                   <input type="button" onclick=saveOptions() value="<?php echo _('Save');?>" class="btnSaveOption">
                </div>
            </div>
        </div>
        <div class="dialogFolders" title="<?php echo _("Folders");?>">
            <div>
                <ul class="tabs">
                    <li><a href="#tabfolder"><?php echo _("Folders");?></a></li>
                </ul>

                <!-- single pane. it is always visible -->
               <div class="tab_container">
                    <div id="tabfolder" class="tab_content"><?php echo _("Loading");?></div>
               </div>
               <!--<div class="divSaveOption">
                   <input type="button" onclick="$('.dialogOptions').dialog('close')" value="<?php echo _('Cancel');?>" class="btnCancelOption">
                   <input type="button" onclick=saveOptions() value="<?php echo _('Save');?>" class="btnSaveOption">
               </div>-->
            </div>
        </div>
        <div class="dialogRename" title="<?php echo _("Rename");?>">
            <input type="text" name="newfolder">
        </div>
        <div class="dialogContacts" title="<?php echo _("Address");?>" >
               <!--<ul class="tabs">
                    <li><a href="#tabfolder"><?php echo _("Address");?></a></li>
               </ul>-->               
              <div class="menuContact">
                <div><a href="javascript:showFormAddContacts()"><img src="../images/contact.png"><?php echo _('Add contact');?>&nbsp;</a>&nbsp;</div>
                <div><a href="javascript:addGroup()"><img src="../images/group.png"><?php echo _('Add group');?>&nbsp;</a>&nbsp;</div>
                <div><a href="javascript:deleteContats()"><img src="../images/deleteContact.png"><?php echo _('Delete contact');?>&nbsp;</a>&nbsp;</div>
                <div><a href="javascript:loadFormImport()"><img class="figImport" src="../images/import.png"><?php echo _('Import contacts');?>&nbsp;</a>&nbsp;</div>
                <div><a href="javascript:loadFormExport()"><img class="figExport" src="../images/export.png"><?php echo _('Export contacts');?>&nbsp;</a>&nbsp;</div>
              </div>
              <div class="contentContacts">
                 <div class="ContGroup">
                     <div class="label">Grupo</div>
                     <div class="content" onselectstart='return false' ></div>
                 </div>
                 <div class="ContContact">
                     <div class="label">Contatos</div>
                     <div class="content" onselectstart='return false' ></div>
                     <div class="label" id="rodapeCont">
                        <img class="btnRemoveContGroup" title="<?php echo _('Remove contact of group');?>" src="../images/icon-minus.png">
                     </div>
                 </div>
                 <div class="ContInfo">
                     <div class="msgContact">
                         <?php echo _('Select the contacts to be erased');?>
                     </div>
                     <div class="label"><?php echo utf8_decode(_('Description'));?></div>
                     <div class="divAddGroup">
                         <?php echo _("Name");?>: <input type="text" id="nameGroup" name="nameGroup">
                         <input type="button" class="btnAddGroup" value="<?php echo _('Add');?>">
                         <input type="button" class="btnRenameGroup" value="<?php echo _('Rename');?>">
                         <input type="button" class="btnDeleteGroup" value="<?php echo _('Delete');?>">
                         <input type="hidden" id="selectGroup" name="selectGroup" value="">
                     </div>
                     <div class="infoCont">
                        <div class="photoContact"></div>
                        <div class="fnContact"></div>
                        <div class="lnContact"></div>
                        <div class="nameContact"></div>
                        <div class="nickContact"></div>
                        <div class="emailContact"></div>
                        <div class="infoContact"></div>
                        <input type="button" name="edit" value="<?php echo _('Edit');?>">
                     </div>
                     <div class="content">
                     </div>
                 </div>
              </div>               
        </div>
        <div id="importWin" title="<?php echo utf8_decode(_("Import contacts"));?>" style="display:none;"></div>
        <div id="dialogOpMail" title="teste">
            <div></div>
        </div>
        <div style="z-index:5px" id="dialogOpIdentities" title="<?php echo _("Advanced Identities")?>">
            <div></div>
        </div>
        <div id="dialogImportEvent">
            <iframe id="iframe_event" frameborder="0" width="450" height="108"
                    border="0" src=""></iframe>
        </div>
        <div id="closeall"><?php echo _("Close all");?></div>
        <script src="../js/jquery-1.4.2.min.js"></script>
        <script src="../js/jquery-ui-1.8.11.min.js"></script>
        <script src="../js/interface.js"></script>        
        <?php  if($my_language == 'pt_BR')
                    echo '<script src="../js/utils_ptbr.js"></script>';
               elseif($my_language == 'en_US')
                    echo '<script src="../js/utils_en.js"></script>';
               else
                    echo '<script src="../js/utils_en.js"></script>';
        ?>                         
        <script src="../js/jquery.cookie.js"></script>
        <script src="../js/jquery.treeview.js"></script>
        <script src="../js/jquery.contextMenu.js"></script>        
        <script src="../js/jquery.purr.js"></script>
        <script src="../js/jquery.corner.js"></script>
        <script src="../js/urlEncode.js"></script>        
        <script src="../plugins/html_mail/ckeditor/ckeditor.js"></script>
        <script src="../plugins/html_mail/ckeditor/adapters/jquery.js"></script>
        <script src="../js/jquery.tools.min.js"></script>
        <script src="../js/jquery.toggle.js"></script>
        <script type='text/javascript' charset="utf-8" src='../calendar/frontend/fullcalendar/fullcalendar.min.js'></script>
        <script type='text/javascript' charset="utf-8" src='../calendar/frontend/fullcalendar/utilscalendar.js'></script>
        <script type='text/javascript' charset="utf-8" src='../calendar/frontend/fullcalendar/langs.js'></script>
        <script type='text/javascript' charset="utf-8" src='../calendar/frontend/fullcalendar/gcal.js'></script>        
        <script>
            //flag para fixar problema incomum no Internet explorer em dadas resolucoes e monitores            
            var correctionIE;
            
            $(function(){
                if($.browser.msie){
                    $(".tableHeader").css({'position':'relative','left':'-3px'});
                }
                heightHalf = $("#listmails").height() / 2;                
                try{
                    $('.gobox:hover').css("text-decoration","underline");                    
                }catch(err){
                    //Correcao para monitores menores
                    correctionIE = true;
                    $('.menuDrop').width('140');
                    $('.menuDrop ul').css('{position:relative,left:-30px}');
                    $('.tableHeader').css("left","-4px");
                    
                }
                if(parseInt($.browser.version,10) == 9){
                    $('.tableHeader').css("left","-4px");
                    //$('.listmailHorizontal').width($('#read').width());
                }

                var timerNot;

                timerNot = setInterval('verifiyNew()',1000);

                $("#closeall").click(
                    function(){
                        $('.labelNumMSg,.labelNumCont').css("float","right"); 
                        $('.close').trigger('click');
                        $(this).fadeOut('normal');
                    }
                );
                preloadAjax('<?php echo WEBMAIL_ALIGN;?>',true);
                //loadFolders();
                initFolders();
                <?php if($load_listmail){?>
                    loadListMails('<?php echo WEBMAIL_ALIGN;?>','INBOX',0,0);
                <?php } ?>
                $('body').mousemove(
                    function(){
                       try{
                        if(totalMsg > 0){
                           clearInterval(timerNot);
                           $(document).attr("title",'<?php echo $org_name;?>');
                            timerNot = setInterval('verifiyNew()',1000);
                            totalMsg = 0;
                        }
                       }catch(err){}
                    }
                );

                $(window).bind("resize", function(event,ui) {
                     resizeMail('<?php echo WEBMAIL_ALIGN;?>');
                     correctBarIE();
                     $('.right').width($(window).width() - $('.left').width() - 50);
                     resizeWidth();
                     //resizeHeaderMail();
                    });
                $(".gobox").click(
                    function(){
                        $('span[name=INBOX]').addClass('selFolder'); //""
                        $(document).attr("title","<?php echo _("INBOX") . ':::' .  $username . '@' . $domain;?>")
                        loadListMails('<?php echo WEBMAIL_ALIGN;?>');
                    }
                );
                resizeLeft();
                linksHeader();
                <?php
                switch($align){
                    case "vertical":
                        echo 'resizeVertical();' . PHP_EOL . 'correctBarIE();';
                        break;
                    case "horizontal":
                        echo 'resizeHorizontal();';
                        break;
                    case "window":
                        echo 'resizeHorizontal();';
                        break;
                }
                ?>

                $('.barcontrol').height(window.innerHeight);
                $(document).keyup(function (e) {
                    if(e.which == 17 || e.which == 224)
                        pressedCtrl = false;
                });
                $(document).keydown(function (e) {                    
                    if(e.which == 17 || e.which == 224 )
                        pressedCtrl = true; 
                    if(e.which == 13 && pressedCtrl == true  && $('input:focus').size() == 0 && $('textarea:focus').size() == 0) {
                        loadMailWin($("input:hidden[name=mailbox]").val(),$(".readMailBack input:checkbox").val(),$("input:hidden[name=startMessage]").val(),true);
                    }
                });
                if($.browser.mozilla){
                    $(document).keypress(
                        function(e){                                                                                    
                            if($('textarea:focus,input:focus').size() == 0)
                                return captureKey(e);
                        });
                }else{
                    if($.browser.webkit){
                        $(document).keydown(
                            function(e){
                                if($('textarea:focus,input:focus').size() == 0)
                                    return captureKey(e);
                            }
                        );
                    }else
                        $(document).keyup(
                            function(e){
                                if($('textarea:focus,input:focus').size() == 0)
                                    return captureKey(e);
                            }
                        );
                }
              
                try{
                    setInterval("verifyAlert('<?php echo$username;?>')",180000);
                    setInterval("showNotification()",180000);
                    setInterval("correctionFF()",1000);                    
                    sort();
                }catch(err){}
                
                inputSearch();
                inputSearchEvent();
                sortDate();
                sortFrom();
                sortSub();
                sortSize();                
                
          });
         </script>
    </body>
</html>
