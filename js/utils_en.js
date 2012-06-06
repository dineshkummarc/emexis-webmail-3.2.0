/**
 * Rotinas e funções do Webmails
 * Autor 2010 Bruno Borges <bruno.borges@brc.com.br>
 *
 */

var flagWidth = false;
//var arraySelectMsg = new Array();
var pressedCtrl = false;
var mailId = new Array();
//Defina qual o loader será usado na requisição
var loader = "#loader";
//Proporção das janelas de leitura e compose
var winP = 0.80;

function objNewMsg(subject,from){
    this.subject = subject;
    this.from = from;
}

/* Objeto de mensagens do mailbox*/
function objMsg(mailbox,smtoken,moveButton,startMessage,locate){    
    this.mailbox = mailbox;
    this.smtoken = smtoken;
    this.moveButton = moveButton;
    this.startMessage = startMessage;
    this.locate = locate;
}

var mail = new Array();

/* Carrega e-mail em uma nova 
 *
 * mailbox = Mailbox onde está a mensagem
 * passed_id = Id da mensagem
 * startMessage = Mensagem inicial default:1
 *
 * */
function loadMailWin(mailbox,passed_id,startMessage,popup){    
    alt = $(window).height() * winP;
    larg =  $(window).width() * winP;

    id = "readerId" + passed_id;
    if($("#" + id).size() != 0)
        return;
    dialog = '<div id="' + id + '" title="Read"><div></div></div>';
   
    $(dialog).dialog({
         height: alt,
         dialogClass: 'windowRead',
         width:larg,
         close: function(event, ui){
            $(this).remove();
         }
    });
    
    improvementDialog();
    minimizeWindow();
    maximizeWindow();
    if(mailbox == null)
        mailbox = "INBOX";
    $.ajax({
        url: 'read_body.php',
        type: 'GET',
        data: {'mailbox': mailbox,'passed_id':passed_id,'startMessage':startMessage,'view_unsafe_images':1,'win':true},
        dataType: 'text',
        success: function(dados) {            
            $('#' + id + '>div').html(dados);
            str = $('#' + id + '>div .subjectMsg td').html();
            //subj = str.match(/[a-zA-z]+:&nbsp;&nbsp;.+/).toString();
            subj = str.match(/:&nbsp;&nbsp;.+/).toString();
            subj = subj.replace(/:&nbsp;&nbsp;/,'').toString();
            $('#ui-dialog-title-' + id).html(subj);
            $("#msg" + passed_id).parent().siblings().css('font-weight','lighter');
            verifyUnread(mailbox);            
        }
    });    
}

function selectMsgForId(passed_id){    
    $('input:checkbox[value=' + passed_id  + ']').parent()
                                                 .siblings()
                                                 .addClass('readMailBack')
                                                 .end()
                                                 .parent()
                                                 .addClass('readMailBack');
    $('input:checkbox:not(input:checkbox:[value=' + passed_id  + '])')
                                                 .parent()
                                                 .siblings()
                                                 .removeClass('readMailBack')
                                                 .end()
                                                 .parent()
                                                 .removeClass('readMailBack');
    $('input:checkbox[value=' + passed_id  + ']').parent().addClass('readMailBack');
    $('input:checkbox:not(input:checkbox:[value=' + passed_id  + '])').parent().removeClass("readMailBack");
    if($("#alignWebmail").val() == "vertical"){
        $('input:checkbox[value=' + passed_id  + ']').parent().parent().next().children().addClass('readMailBack');
        $('input:checkbox:not(input:checkbox:[value=' + passed_id  + '])').parent().parent().next().children().removeClass('readMailBack');
        $('input:checkbox:not(input:checkbox:[value=' + passed_id  + '])').parent().removeClass('readMailBack');        
    }
}


/* Carrega e-mail
 *
 * mailbox = Mailbox onde está a mensagem
 * passed_id = Id da mensagem
 * startMessage = Mensagem inicial default:1
 *
 * */
function loadMail(mailbox,passed_id,startMessage,popup,e,keyboard){        
    if(e.ctrlKey)
        return false;        

    if(mailbox == null)
        mailbox = "INBOX";
    $.ajax({
        url: 'read_body.php',
        type: 'GET',
        cache:false,
        data: {'mailbox': mailbox,'passed_id':passed_id,'startMessage':startMessage,'view_unsafe_images':1},
        dataType: 'text',
        success: function(dados) {            
            $('#read').html(dados);
            if($("#alignWebmail").val() != 'vertical'){
                $("#msg" + passed_id).parent().siblings().css('font-weight','lighter');                
                if($("#msg" + passed_id).siblings().filter('div').hasClass('flagMsgUnread')){
                    $("#msg" + passed_id).siblings().filter('div').removeClass('flagMsgUnread');
                    $("#msg" + passed_id).siblings().filter('div').addClass('flagMsgRead');
                    verifyUnread(mailbox);                    
                }
            }else{
                $("#msg" + passed_id).parent().siblings().css('font-weight','lighter');
                $("#msg" + passed_id).parent().parent().next().children().css('font-weight','lighter');                                
            }                                 
        }
    });    
    
    if(keyboard != undefined)
        return;
     selectMsgForId(passed_id);
    
}

function highLightFolder(ele){
    $(".left li .folder").css("color","black");
    if(ele == undefined)
        return false;
    ele.style.color = "red";
}

function contextMenuTrash(){
     $(".left .folder[name=INBOX.Trash]").contextMenu({
            menu: 'myMenuTrash'
    }, function(action, el, pos) {
            switch(action){
                case "open":
                    loadListMails($("#alignWebmail").val(),el.attr("name"),0,0,null,6,1);
                    break;
                case "empty":
                    $.ajax({
                        url: 'empty_trash.php',
                        type: 'GET',
                        cache: false,
                        data: {},
                        dataType: 'text',
                        success: function(dados) {
                            setTimeout("loadFolders(true)",900);
                        }
                    });
                    break;                
                case "quit":
                   break;
            }

    });
}

function contextMenuInb(){
     $(".left,.left .folder[name=INBOX]").contextMenu({
            menu: 'myMenuInbox'
    }, function(action, el, pos) {
            switch(action){
                case "open":
                    loadListMails($("#alignWebmail").val(),el.attr("name"),0,0,null,6,1);
                    break;
                case "refresh":
                    loadFolders(true);
                    break;
                case "addfolder":
                    $('#dialogCreate input[name=folder_name]').val("");
                    constructSelect(el.attr("name"));
                    $("#dialogCreate").dialog({
                        resizable: false,
                        height: 160
                    });
                    break;
                case "quit":
                   break;
            }

    });
}
function contextMenuOut(){
     $(".left .folder[name=INBOX.Drafts],.left .folder[name=INBOX.Sent]").contextMenu({
            menu: 'myMenuOutr'
    }, function(action, el, pos) {
            switch(action){
                case "open":
                    loadListMails($("#alignWebmail").val(),el.attr("name"),0,0,null,6,1);
                    break;
                case "addfolder":
                    $('#dialogCreate input[name=folder_name]').val("");
                    constructSelect(el.attr("name"));
                    $("#dialogCreate").dialog({
                        resizable: false,
                        height: 160
                    });                                                            
                    break;
                case "quit":
                   break;
            }

    });
}
function contextMenuFolder(){
    $(".left .folder:not(.folder[name=INBOX.Trash],.folder[name=INBOX],.folder[name=INBOX.Sent],.folder[name=INBOX.Drafts])").contextMenu({
            menu: 'myMenuFolder'                        
    }, function(action, el, pos) {            
            switch(action){
                case "open":
                    loadListMails($("#alignWebmail").val(),el.attr("name"),0,0,null,6,1);
                    break;
                case "addfolder":
                    $('#dialogCreate input[name=folder_name]').val("");
                    constructSelect(el.attr("name"));
                    $("#dialogCreate").dialog({
                        resizable: false,
                        height: 160
                    });                   
                    //createFolder(nameFolder,subFolder);
                    break;
                case "rename":
                    name = el.children('span:first').text();
                    nameFull = el.attr("name");
                    $(el).empty();
                    $('<input type="text" size="10" value="' + name + '"style="font-size:8pt;height:22px;">').appendTo(el);
                    $(el).children().focus();
                    $(".folder input").blur(
                        function(){
                            $(el).empty();
                            $(el).text(name);
                        }
                    );
                    $(".folder input").keydown(
                        function(e){
                            if(e.keyCode == 13){
                                newName = $(".folder input").val();
                                if(name != newName && newName != ""){
                                    renameFoldersDialog(newName,name,nameFull);
                                    $(el).empty();
                                    $(el).text(newName);
                                    $(el).attr("name",newName);
                                    setTimeout("loadFolders(true)",5000);
                                }else{
                                    $(el).empty();
                                    $(el).text(name);
                                }
                            }                            
                        }
                    );
                    $(".folder input").change(
                        function(){                                                                                        
                            newName = $(".folder input").val();
                            if(newName == ""){
                                $(el).empty();
                                $(el).text(name);                                
                            }else{
                                renameFoldersDialog(newName,name,nameFull);
                                $(el).empty();
                                $(el).text(newName);
                                $(el).attr("name",newName);
                                setTimeout("loadFolders(true)",5000);
                            }
                        }
                    );
                    break;
                case "delete":
                    confirmDeleteFolder(el.attr("name"));
                    /*$("#dialogConfirmDeleteFolder").dialog({
                        resizable: false,
                        height: 160,
                        modal: true,
                        buttons: {
                        Yes: function() {
                            deleteFoldersdelete(el.attr("name"));                            
                            $(this).dialog('close');
                            },
                            Cancel: function() {
                                $(this).dialog('close');
                            }
                        }
                    });
                    */
                   break;                
                case "quit":
                   highLightFolder();
                   break;
            }

    });      
}
/*
 * Menu de contexto
 *
 **/
function contextMenu(){
    
    $(".gridmail td").contextMenu({
            menu: 'myMenu'
    }, function(action, el, pos) {        
            $(el).siblings().addClass('highlight').end().addClass('highlight');
            msg = new Array();

            $(el).siblings().children("#listmails input[type=checkbox]").each(
                function(){
                    msg[0] = $(this).val();
                }
            );                
            $(el).siblings().children("input[type=checkbox]").attr('checked', true);
            id = $(el).siblings().children("input[type=checkbox]").attr("value");
            box = $("input:hidden[name=mailbox]").val();
            start = $("input:hidden[name=startMessage]").val();
            smtoken = $("input:hidden[name=smtoken]").val();
            targetMailbox = $('select[name=targetMailbox]').val();
            locate = $("input:hidden[name=location]").val();

            if(id == undefined){
                $(el).parent().prev().children().children("input[type=checkbox]").attr('checked', true);
                id = $(el).parent().prev().children().children("input[type=checkbox]").attr("value");
            }
            
            switch(action){
                case "open":
                    loadMailWin(box,id,start,'');
                    break;
                case "reply":
                    openCompose(id,box,start,0,'reply');
                    break;
                case "replyall":
                    openCompose(id,box,start,0,'reply_all');
                    break;
                case "forward":
                    openCompose(id,box,start,0,'forward');
                    break;
                case "editmsg":
                    openCompose(id,box,start,0,'edit_as_new');
                    break;
                case "forward_as_attachment":
                    openCompose(id,box,start,0,'forward_as_attachment');
                    break;
                case "delete":
                    if($('#listmails input[type=checkbox]:checked').size() > 0){
                        $("#dialogConfirmDelete").dialog({
                            resizable: false,
                            height: 160,
                            modal: true,
                            buttons: {
                                Sim: function() {
                                    selectMultipleAction('delete');                    
                                    $(this).dialog('close');
                                },
                                Cancelar: function() {
                                    $(this).dialog('close');
                                }
                            }
                        });
                    }else{
                        confirmDelete(smtoken,box,targetMailbox,true,msg, start,this);
                    }
                    break;
                case "msgunread": //
                    if($('#listmails input[type=checkbox]:checked').size() > 0){
                        selectMultipleAction("unread");
                    }else{
                        markUnread(smtoken,box,box,"unread",msg,start,locate);
                    }
                    break;
                case "msgread":
                    if($('#listmails input[type=checkbox]:checked').size() > 0){
                        selectMultipleAction("read");
                    }else{
                        markRead(smtoken,box,box,"read",msg,start,locate);
                    }
                    break;
                case "quit":
                   break;
            }
            

    });
}
/*
 * Altera a cor das células
 **/

function highlight(align){
    $(".gridmail td input[type=checkbox]").click(
        function(){
            if($(this).attr("checked")){
                $(this).parent().siblings().addClass('highlight');
                $(this).parent().addClass('highlight');
                if(align == 'vertical')
                    $(this).parent().parent().next().children().addClass('highlight');
            }else{
                $(this).parent().siblings().removeClass('highlight');
                $(this).parent().removeClass('highlight');
                if(align == 'vertical')
                    $(this).parent().parent().next().children().removeClass('highlight');
            }
        }
    );
}

/*
 * Exibe ajuda ao arrastar uma mensagem
 *
 **/
var flagMove = true;
function showAjuda(event,titulo,descricao){  
  if(!flagMove){
      $("#boxAjuda").hide();
      return;
  }

  caixa = document.all? document.all.boxAjuda : document.getElementById("boxAjuda");
  if(parseInt($.browser.version,10) == 9){
        $("#boxAjuda").css('visibility','visible');
        $("#boxAjuda").css('top',event.clientY - 10);
        $("#boxAjuda").css('left',event.clientX + 15);
    }else{
        caixa.style.visibility = "visible";
        caixa.style.top = event.clientY + document.body.scrollTop - 10;
        caixa.style.left = event.clientX + document.body.scrollLeft + 15;
    }
}

function MoverAjuda(event){    
  if($("#boxAjuda").is(":visible")){
    caixa.style.top = event.clientY + document.body.scrollTop - 10;
    caixa.style.left = event.clientX + document.body.scrollLeft + 15;
  }
}

/*
 * Esconde a ajuda ao soltar uma mensagem
 *
 **/
function hideAjuda(){
    try{
        mousecaixa = document.all? document.all.boxAjuda : document.getElementById("boxAjuda");
        caixa.style.visibility = "hidden";
        $(".move").draggable( "disable" );
        $(".move").draggable( "enable" );
    }catch(e){}
}

/*
 * Soltar mensagens
 **/
function dropMsg(){
    $(".folder").droppable({
        accept: '.gridmail td div',
        cursor:'move',
        out: function(){
           $(this).removeClass('moveMouseFolder');
        },
        over: function(){
           $(this).addClass('moveMouseFolder');
           /*if($(this).next().is(":hidden"))
               $(this).next().show();*/
                      
        },
        drop: function(event, ui) {
            $(this).removeClass('moveMouseFolder');
            //$("#listmails input[type=checkbox]:checked").parent().siblings().removeClass("dragMsg");
            $(".listmailWindow input[type=checkbox]:checked,#listmails input[type=checkbox]:checked").each(
                function(index){
                    mailId[index] = $(this).val();
                }
            );
            
            moveMsg(mail[0].smtoken,mail[0].mailbox,$(this).attr('name'),'Mover',mailId,mail[0].startMessage,mail[0].locate);
            //$("#listmails input[type=checkbox]:checked").parent().siblings().removeClass("dragMsg");
            hideAjuda();
            //verifyUnread();
        }
    });
}

/*
 *Rotina de arrastar mensagens
 **/
function buildDrag(){  
    $(".move div").draggable({
        scroll: false,
        helper:'',
        start:function(e){            
            $(this).parent().siblings().children("input[type=checkbox]").attr('checked', true);
            $(this).parent().addClass("highlight");
            $(this).parent().siblings().addClass("highlight");
            if($('#alignWebmail').val() == 'vertical'){
                $(this).parent().parent().next().children().addClass("highlight");
            }
            mail[0] = new objMsg($("input:hidden[name=mailbox]").val(),$("input:hidden[name=smtoken]").val(),"moveButton", $("input:hidden[name=startMessage]").val(),$("input:hidden[name=locate]").val());
            //showAjuda(event,'titulo','descricao');
            /*$("#listmails input[type=checkbox]:checked").parent().siblings().addClass("dragMsg");
            if($("#alignWebmail").val() == 'vertical')
                $("#listmails input[type=checkbox]:checked").parent().parent().next().find('.tdSubject').addClass("dragMsg");
            $("#listmails input[type=checkbox]:checked").parent().addClass("dragMsg");*/
            $("#boxAjuda ul").empty();
            $(".listmailVertical input[type=checkbox]:checked,.listmailWindow input[type=checkbox]:checked,#listmails input[type=checkbox]:checked").each(
                function(){
                    if($('#alignWebmail').val() == 'vertical'){
                        $("<li style='margin: 0;' >" + $(this).parent().parent().find(".textSubject").text() + "&nbsp;&nbsp;</li>").appendTo("#boxAjuda ul");
                    }else{
                        $("<li style='margin: 0;'>" + $(this).parent().parent().find(".textSubject").text() + "&nbsp;&nbsp;</li>").appendTo("#boxAjuda ul");
                    }
                }
            );
        },
        drag: function(event, ui) {
            if(flagMove){                
                showAjuda(event ,'titulo','description');                
                //$('.gridmail td').removeClass("dragMsg");
            }            
           
        },
        stop: function(){;
            hideAjuda();
            
            /*if($("#alignWebmail").val() == 'vertical')
                $("#listmails input[type=checkbox]:checked").parent().parent().next().find('.tdSubject').removeClass("dragMsg");
            $("#listmails input[type=checkbox]:checked").parent().siblings().removeClass("dragMsg");
            $("#listmails input[type=checkbox]:checked").parent().removeClass("dragMsg");*/

            if($(".highlight").size() == 0){
                $('.listmails input:checkbox').attr("checked",false);
            }
            /*$(this).siblings()
                   .removeClass("dragMsg")
                   .end()
                   .removeClass("dragMsg");*/
        }
    });
}
//var isDrag;
function dragMsg(){
    buildDrag();

}

/* Selecionar múltiplas mensagens*/
function selectMultiple(){
    $('.gridmail tr:not(.gridmail tr:empty)').toggle(function(e) {        
        if(e.ctrlKey || e.metaKey){
            if($('#alignWebmail').val() == 'vertical'){
                if($(this).children().hasClass('readMailBack')){
                    if($(this).children().hasClass('tdSubject')){
                        $(this).children().removeClass('readMailBack');
                        $(this).prev().children().removeClass('readMailBack');
                        $(this).prev().removeClass('readMailBack');
                    }else{
                        $(this).children().removeClass('readMailBack');
                        $(this).prev().removeClass('readMailBack');
                        $(this).next().children().removeClass('readMailBack');
                    }
                }
                if($(this).children().hasClass('tdSubject')){
                    $(this).prev().children().addClass('highlight');
                    $(this).children().addClass('highlight');
                    $(this).prev().children().find("input[type=checkbox]").attr("checked",true);
                }else{
                   if($(this).children().hasClass('fromVertical')){
                        $(this).children().addClass('highlight');
                        $(this).prev().children().addClass('highlight');
                        $(this).prev().find('.tdCheck').find("input[type=checkbox]").attr("checked",true);
                    }else{
                        $(this).children().addClass('highlight');
                        $(this).next().children().addClass('highlight');
                        $(this).children().find("input[type=checkbox]").attr("checked",true);
                    }
                }
            }else{
                if($(this).children().hasClass('readMailBack')){
                    $('.gridmail tr').children().removeClass('readMailBack');
                }
                $(this).children().addClass('highlight');
                $(this).children().find("input[type=checkbox]").attr("checked",true);
            }
        }else{            
            $('.gridmail tr').children().removeClass('highlight');
            $('.gridmail td').removeClass("dragMsg");
            $('.listmails input:checkbox').attr("checked",false);
        }
    $(this).focus();
        },function(e){
            if(e.ctrlKey || e.metaKey){
                if($('#alignWebmail').val() == 'vertical'){
                    if($(this).children().hasClass('tdSubject')){
                        $(this).prev().children().removeClass('highlight');
                        $(this).children().removeClass('highlight');
                        $(this).prev().children().find("input[type=checkbox]").attr("checked",false);
                    }else{
                        $(this).children().removeClass('highlight');
                        $(this).next().children().removeClass('highlight');
                        $(this).children().find("input[type=checkbox]").attr("checked",false);
                    }
                }else{
                    $(this).children().removeClass('highlight');
                    $(this).children().find("input[type=checkbox]").attr("checked",false);
                }
            }else{
                $('.gridmail tr').children().removeClass('highlight');
                $('.gridmail tr').children().find("input[type=checkbox]").attr("checked",false);
            }
            $(this).focus();

        }
    );
}

/* Carrega lista de e-mails
 *
 * align = Alinhamento do webmail
 * mailbox = Nome do mailbox
 * showall = Exibe todas as mensagens
 * cache = Cache para carregar as mensagens já lidas
 * passed_id Id da mensagem a ser selecionada
 *
 * */
function loadListMails(align,mailbox,showall,cache,passed_id,sort,startMessage){
    $('#read').html('');
    //alert($("#widgetCalendar").is(':visible'));
    //$("#calendar").hide();
    //$(".right,.barcontrol").hide();
    if(mailbox == 'INBOX.Sent'){
        $('.labelTo').show();
        $('.labelFrom').hide();
    }else{
        $('.labelTo').hide();
        $('.labelFrom').show();
    }
    if($("#calendar").is(':visible')){
        $('.right').show();
        $("#calendar").hide();
    }
    if(passed_id == undefined || passed_id == "")
        passed_id = null;

    if(sort == undefined)
        sort = 6;
    if(startMessage == undefined){
        if($("#numPage").val() == 1 || $("#numPage").size() == 0 )
            startMessage = 1;
        else
            startMessage =  1 + ($("#numPage").val() * $("#show_num").val() - $("#show_num").val());            
    }    
    if($('.right').is(":hidden")){        
       // $(".pagelink").hide();
       // $(".right").show();
    }
    
    if (mailbox == null)
        mailbox = 'INBOX';
    if(showall == null)
        showall = 0;
    if(cache == null)
        cache = 0;
    $.ajax({
        url: 'right_main.php',
        type: 'GET',
        data: {'startMessage': startMessage,'mailbox':mailbox,'newsort':sort,'PG_SHOWALL':showall,'use_mailbox_cache':cache},
        dataType: 'text',
        success: function(dados) {
            $(".tableHeader").show();
            $('.listmail').html(dados);            
            resizeMail(align);
            contextMenu();            
            dragMsg(); 
            if($('.tableHeader').width() ==  $('.listmailHorizontal').width())
                $('.tableHeader').width($('.tableHeader').width() + 2);
            dropMsg();
            check(align);
            $(".gridmail, .gridmail td").css("cursor:pointer;border-color,white");
            $(".headerMsg,.headerMsgVer,#labelBox").show();            
            $(".divSubject").width($(".tdSubject").width());
            $(".divFrom").width($(".tdFrom").width());
            $(".divDate").width($(".tdDate").width());
            var size = 0;                        
            resizeHeaderMail();
            
            if($("#select_checkbox").val() == 0){
                selectMultiple();
                highlight(align);                                
            }else{                
                $('.tdCheck input').click(
                    function(){
                        if($(this).attr("checked")){
                            $(this).parent().siblings().addClass('highlight');
                            $(this).parent().addClass('highlight');
                        }else{
                            $(this).parent().siblings().removeClass('highlight');
                            $(this).parent().removeClass('highlight');
                        }
                    }
                );                                  
            }
            $(".gridmail td:not(.tdCheck,.folderEmpty)").click(
                function(event){
                    if(align == 'vertical'){
                        if($(this).parent().children().find("input:checkbox").val() == undefined)
                            loadMail($("input:hidden[name=mailbox]").val(),$(this).parent().prev().children().find("input:checkbox").val(),$("input:hidden[name=startMessage]").val(),'',event);                          
                        else
                            loadMail($("input:hidden[name=mailbox]").val(),$(this).parent().children().find("input:checkbox").val(),$("input:hidden[name=startMessage]").val(),'',event);
                    }else
                        loadMail($("input:hidden[name=mailbox]").val(),$(this).parent().children().find("input:checkbox").val(),$("input:hidden[name=startMessage]").val(),'',event);
                }
            );
            $(".gridmail td:not(.tdCheck)").dblclick(
                function(event){
                    if(align == 'vertical'){
                        if($(this).parent().children().find("input:checkbox").val() == undefined)
                            loadMailWin($("input:hidden[name=mailbox]").val(),$(this).parent().prev().children().find("input:checkbox").val(),$("input:hidden[name=startMessage]").val(),'');
                        else
                            loadMailWin($("input:hidden[name=mailbox]").val(),$(this).parent().children().find("input:checkbox").val(),$("input:hidden[name=startMessage]").val(),'');
                    }else{                        
                        loadMailWin($("input:hidden[name=mailbox]").val(),$(this).parent().children().find("input:checkbox").val(),$("input:hidden[name=startMessage]").val(),'');
                    }
                }
            );
            if(passed_id != null){
                $("#msg" + passed_id).parent().siblings().addClass('readMailBack');
                $("#msg" + passed_id).parent().addClass('readMailBack');
                if(align == 'vertical'){                    
                    $("#msg" + passed_id).parent().parent().next().children().addClass("readMailBack");                    
                }
            }
            createControlPaginator(startMessage,mailbox);
            color = $('.listmailHorizontal').css('border-bottom-color');
            if(align != 'vertical'){
                $('.tableHeader th:first').css("border-left","1pt solid " + color);
                $('.tableHeader th:last').css("border-right","1pt solid " + color);
            }
            /*$('#check').toggle(
                function(){
                    $('.listmail input:checkbox').attr('checked',true);                    
                },
                function(){                    
                    $('.listmail input:checkbox').attr('checked',false);
                }
            );*/
             /* $('#check').click(
                function(){
                   check($("#alignWebmail").val());
                }
              );*/
            /*$(".gridmail td input:checkbox").click(
                function(){
                    alert("teste");
                }
            );*/
        }
    });
}
function constructSelect(selected){
    $.ajax({
        url: 'select_folder.php',
        type: 'POST',
        data: {},
        dataType: 'text',
        success: function(dados) {
            $("#selectfolder").html(dados);            
            $('#dialogCreate select option[value="' + selected + '"]').attr("selected","selected");
            $("#dialogCreate input:button").click(
                function(){                    
                    createFolder($('#dialogCreate input[name=folder_name]').val(),$("#dialogCreate select option:selected").val());
                    $('#dialogCreate input[name=folder_name]').val("");

                    $("#dialogCreate").dialog('close');                    
                }
            );
        }
    });
}
function renameFoldersDialog(newName,nameOld,folder){    
    $.ajax({
        url: 'folders_rename_do.php',
        type: 'POST',
        data: {'new_name':newName,'orig':folder,'old_name':nameOld,'smtoken':$('input[name=smtoken]').val(),'flagAjax':true},
        dataType: 'text',
        success: function(dados) {
            loadFolders(true);
          //reloadFolder(2000);
        }
    });
}
function deleteFoldersdelete(mailbox){
    $.ajax({
        url: 'folders_delete.php',
        type: 'POST',
        data: {'mailbox':mailbox,'confirmed':true},
        dataType: 'text',
        success: function(dados) {            
            setTimeout("loadFolders(true)",2000);
        }
    });
}

function createFolder(nameFolder,subFolder){
    $.ajax({
        url: 'folders_create.php',
        type: 'POST',
        data: {'folder_name':nameFolder,'subfolder':subFolder,'flagToken':true},
        dataType: 'text',
        success: function(dados) {            
            loadFolders(true);           
        }
    });
}

// Não carrega todas as pastas ao carregar o webmail
function initFolders(){
     $.ajax({
        url: 'left_main.php',
        type: 'GET',
        data: {'initfolder' : 1},
        dataType: 'text',
        success: function(dados) {
            $('.left').html(dados);
        }
     });
}


function loadFolders(reConstruct){
    $.ajax({
        url: 'left_main.php',        
        type: 'GET',
        data: {},
        dataType: 'text',
        success: function(dados){            
            $('.left').html(dados);            
            $('.folder').each(
                function(){                    
                    $(this).click(
                        function(){
                            $('.replyMsgIcon,.forwardMsgIcon,#controlPage,#search').show();
                            $('#searchEvent,.btnImportEvent,.btnDailyCalendar,.btnWeeklyCalendar,.btnMonthlyCalendar,.btnListCalendar').hide();
                            $('.folder').removeClass('selFolder');
                            strFolder = $(this).attr("name");
                            pos = strFolder.lastIndexOf(".");
                            newTitle = strFolder.substr(pos + 1) + ' :::: ' + $("#email").val();
                            $(this).addClass('selFolder');
                            document.title = newTitle;
                            loadListMails($("#alignWebmail").val(),$(this).attr("name"),0,0,null,6,1);
                            verifyUnread(strFolder);                            
                        }
                    );                    
                }
            );
            if(reConstruct)
                buildFileTree();
            
            contextMenuFolder();
            contextMenuTrash();
            contextMenuInb();
            contextMenuOut();
            dropMsg();
            $(".labelNumMSg").corner();
            if(correctionIE)
                $(".labelNumMSg").css('float','none');
            verifyUnread('INBOX');
        }
    });
}

/*Constrói árvore de diretórios*/
function buildFileTree(){
    if($("#browserFake").size() > 0){
        $("#browserFake").treeview({
            collapsed: true,
            animated: "medium",
            control:"#sidetreecontrol"
        });
    }
    $("#browser").treeview({
        collapsed: true,
        persist: "cookie",
        animated: "medium",
        control:"#sidetreecontrol"
    });
    if($("#browser .root-hitarea").hasClass("expandable-hitarea")){
       $("#browser .root-hitarea").trigger('click');       
    }    

}

/* Rotina do pre-load Ajax
 *
 * String align = Alinhamento do webmail
 * */
function preloadAjax(align){
    
    $(loader).ajaxStart(function() {
        if($('.background').is(':visible'))
            $('#preloadMini').css('visibility','visible');
        else
            $(this).fadeIn(500);
    });
    $(loader).ajaxStop(function() {
        if($('.background').is(':visible')){
            $('#preloadMini').css('visibility','hidden');
        }else{
            $(this).fadeOut('slow');
            $(loader,'*').hide();
            
        }
        
       // $("body").css("background-color","silver");
        if(!$(".left").is(":visible")){
            $('.miniCalendar,.left,.header,.right,.barcontrol,#divisorLeft,#divisorVertical').show();
            $('.left').width($('.left').width() - 6);

            $(".tableHeader").width($(".readHorizontal").width());

            if($('.tableHeader').width() ==  $('.listmailHorizontal').width())
                $('.tableHeader').width($('.tableHeader').width() + 2);

           
            buildFileTree();

            $('.newOption').dblclick(
                function(){
                    openCompose();
                }
            );
                
            $('.newOption').mouseover(
                function(){
                    if($('#menuReply').is(":visible"))
                        $('#menuReply').hide();
                    showDropMenu($('#menuNew'),$(this));
                }
            );

            $('.replyAllMenu a').click(
                function(){
                    replyDropMenu(true);
                }
            );
            $('.left,.read,.forwardMsgIcon,.printMsgIcon').mouseover(
                function(){
                    $('.menuDrop').hide();
                }
            );
            $('.replyMsgIcon a,.replyMsgIconEn a').mouseover(
                function(){
                    if($('#menuNew').is(":visible"))
                        $('#menuNew').hide();
                    showDropMenu($('#menuReply'),$(this));
                }
            );            
            $('.replyMenu a').click(
                function(){
                    replyDropMenu(false);
                }
            );                
            $('.forwardMsgIcon a').click(
                function(){
                    forwardDropMenu();
                }
            );
            $('.deleteMsgIcon').click(
                function(){
                    dropDelMgs();
                }
            );
            $('.printMsgIcon').click(
                function(){
                    id = $("#read #idMsgRead").val();
                    box = $("input:hidden[name=mailbox]").val();
                    print(id,box);
                }
            );
            resizeMail(align);
            space = $(".right").height() - $(".tableHeader").height() - 8;
            $("#listmails").height(space * 0.5);
            $("#read").height(space * 0.5);
            if($.browser.msie)
                $('.barcontrol').height();
            return;

        }
        resizeMail(align);
                
    });
    /*if($('#browser').is(":hidden"))
       $('#browser').show();*/
    if($.browser.msie)
        $('.barcontrol').height();
    return;
}
/* Faz a correção no redimensionamento do div na horizontal*/
function resizeWidth(){    
    $(".listmailHorizontal,.tableHeader").width($(".readHorizontal").width());
    if($.browser.msie)
        $(".listmailHorizontal").width($(".listmailHorizontal").width() - 2);
    if($('.tableHeader').width() ==  $('.listmailHorizontal').width())
        $('.tableHeader').width($('.tableHeader').width() + 2);
    
}

/* Faz o redimensionamento da div de leitura de emails e da listagem*/
function resizeMail(align){
    if(!flagWidth){
       //Dimensiona a barra de controle
       $(".right").width($(".right").width() - 44);
       flagWidth = true;
    }

    if(parseInt($.browser.version,10) == 9){
        $('.tableHeader').css("left","-4px");
        $('.tableHeader').width($('#read').width() + 2);
        $('.listmailHorizontal').width($('#read').width());
    }

    if($.browser.msie)
        $("#divisorLeft").height($(window).height() - $(".header").height());
    if($.browser.mozilla){
        $(".left,.barcontrol div").height(window.innerHeight - $(".header").height() - 10);
        if($('.labelQuota').size() != 0){
            $("#browser,#browserFake").height($(".left").height() - 240);
        }else{
            $("#browser,#browserFake").height($(".left").height() - 180);
        }
    }else{
        $(".left,.barcontrol div").height($(window).height() - $(".header").height() - 10);
        if($('.labelQuota').size() != 0){
            $("#browser,#browserFake").height($(".left").height() - 200);
        }else{
            $("#browser,#browserFake").height($(".left").height() - 200);
        }
    }
    if(align == "horizontal"){
        if($.browser.msie || $.browser.webkit){            
            altura = $(window).height() - $(".header").height() - $(".listmailHorizontal").height() - 15;
            if($('#search').is(':visible'))
                $(".readHorizontal").height(altura - 18);
            else
                $(".readHorizontal").height(altura);
            return;
        }
        altura = window.innerHeight - $(".header").height() - $(".listmailHorizontal").height() - 15;
        if($('#search').is(':visible'))
            $(".readHorizontal").height(altura - 18);
        else
            $(".readHorizontal").height(altura);
        return;
    }else if(align == "vertical"){
        if($.browser.msie){
            $('.listmailVertical').height($(window).height() - $(".header").height() - 8);
            $('.readVertical').height($(window).height() - $(".header").height() - 5);
            correctBarIE();
            width = $(window).width() - $('.listmailVertical').width() - $(".left").width() - 55;
            $('.readVertical').width(width);
        }else{
            $('.readVertical').height(window.innerHeight - $(".header").height() - 5);
            $('.listmailVertical').height(window.innerHeight - $(".header").height() - 8);
            width = window.innerWidth - $('.listmailVertical').width() - $(".left").width() - 55;
            $('.readVertical').width(width);
        }
        return;
    }else{
        if($.browser.msie){
            $('.listmailWindow').height($(window).height() - $(".header").height() - 20);
        }else{
            $('.listmailWindow').height(window.innerHeight - $(".header").height() - 20);
        }        
    }
}

/* Permite o redimensionamento da listagem do webmail na horizontal*/
function resizeHorizontal(){
    $('.listmailHorizontal').Resizable({
        minWidth: 50,
        minHeight: 50,
        maxWidth: '',
        maxHeight: '',
        minTop: '',
        minLeft: '',
        maxRight: '',
        maxBottom: '',
        handlers: {
            s: '#divisorHorizontal'
        },
        onStop: function(size){
             resizeMail("horizontal");
             resizeWidth();
            //$('textarea', this).css('height', size.height - 6 + 'px');
        }
    });
    return;
}

/* Corrige o problema com a barra no IE na vertical*/
function correctBarIE(){
    if($.browser.msie){
        heightBar = $(window).height() - $('.header').height();
        $("#divisorVertical").height(heightBar);
    }
    return;
}

/* Permite o redimensionamento da listagem do webmail na Vertical*/
function resizeVertical(){
    $('.listmailVertical').Resizable({
        minWidth: '100',
        minHeight: '100',
        maxWidth: '830',
        maxHeight: '',
        minTop: '',
        minLeft: '',
        maxRight: '',
        maxBottom: '',
        handlers: {
            e: '#divisorVertical'
        },
        onResize: function(size){
          $('.headerMsgVer').width($('.listmailVertical').width());
        },
        onStop: function(size){
            resizeMail("vertical");
            $('.headerMsgVer').width($('.listmailVertical').width());
        }
    });
    return;
}

/* Permite o redimensionamento do div esquerdo*/
function resizeLeft(){
    $('.left').Resizable({
        minWidth: '120',
        minHeight: 50,
        maxWidth: '220',
        maxHeight: '',
        minTop: '',
        minLeft: '',
        maxRight: '',
        maxBottom: '',
        handlers: {
            e: '#divisorLeft'
        },
        onStop: function(size){
            $(".right").width($(window).width() - $('.left').width() - 42);
            $(".listmails").width($(window).width() - $('.left').width() - 42);
            resizeHeaderMail();
            resizeWidth();
            resizeMail($('#alignWebmail').val());
        }
    });
    return;
}

/* Abre link em um div interno*/
function openLink(link,ele){
    if(link == 'options.php' && ele == ''){                
        user = $('#username').val();
        $.ajax({
            url: 'email_option.php',
            type: 'POST',
            data: {'user': user},
            dataType: 'text',
            success: function(dados) {
                if(dados == 0){                    
                    $('.dialogOptions').dialog({
                        height: 512,
                        dialogClass: 'windowOptions',
                        width:800,
                        resizable:false,
                        modal: true,
                        close: function() {$(this).hide();}
                    });
                    improvementDialog();
                    minimizeWindow();
                    $(".tab_content").hide(); //Hide all content
                    $("ul.tabs .activeli").addClass("active").show(); //Activate first tab
                    $("#tab1").load('options.php?optpage=personal');
                    $("#tab2").load('options.php?optpage=display');
                    $("#tab3").load('options.php?optpage=display');
                    $("#tab1").show(); //Show first tab content
                    $("ul.tabs li").click(function() {
                        $("ul.tabs li").removeClass("active"); //Remove any "active" class
                        $(this).addClass("active"); //Add "active" class to selected tab
                        $(".tab_content").hide(); //Hide all tab content
                        var activeTab = $(this).find("a").attr("href"); //Find the rel attribute value to identify the active tab + content
                        $(activeTab).fadeIn(); //Fade in the active content
                        return false;
                    });
                    return;
                }else{
                    return;
                }
            }
        });
    }

    if($(ele).parent().attr('class') == 'options' || $(ele).parent().attr('class') == 'optionsWebmail'){
        $('.dialogOptions').dialog({            
            height: 512,
            dialogClass: 'windowOptions',
            width:800,
            resizable:false,
            close: function() {$(this).hide();}
        });

        improvementDialog();
        minimizeWindow();
    
        $(".tab_content").hide(); //Hide all content
        $("ul.tabs .activeli").addClass("active").show(); //Activate first tab        
        $("#tab1").load('options.php?optpage=personal');
        $("#tab2").load('options.php?optpage=display');         
        $("#tab1").show(); //Show first tab content
               
        $("ul.tabs li").click(function() {        
            $("ul.tabs li").removeClass("active"); //Remove any "active" class
            $(this).addClass("active"); //Add "active" class to selected tab
            $(".tab_content").hide(); //Hide all tab content
            var activeTab = $(this).find("a").attr("href"); //Find the rel attribute value to identify the active tab + content
            $(activeTab).fadeIn(); //Fade in the active content
            return false;
        });
    } 
    if($(ele).parent().attr('class') == 'folders' || $(ele).parent().attr('class') == 'liNewFolder' ){
        $('.dialogFolders').dialog({
            stack:true,
            height: 500,
            width:1000,
            dialogClass: 'windowFolder',
            resizable:false,
            close: function() {$(this).hide();}
        });
        improvementDialog();
        minimizeWindow();
        $("#tabfolder").load('folders.php');
        
        /*$("ul.tabs li").click(function() {
            $("ul.tabs li").removeClass("active"); //Remove any "active" class
            $(this).addClass("active"); //Add "active" class to selected tab
            $(".tab_content").hide(); //Hide all tab content
            var activeTab = $(this).find("a").attr("href"); //Find the rel attribute value to identify the active tab + content
            $(activeTab).fadeIn(); //Fade in the active content
            return false;
        });*/
    }
    if($(ele).children().attr('class') == 'iconContact' || $(ele).parent().attr('class') == 'contact' || $(ele).parent().attr('class') == 'liOpenContact'
        || $(ele).parent().attr('class') == 'liOpenGroup'){
        $('.dialogContacts').dialog({
            stack:true,
            height: 500,
            dialogClass: 'windowContacts',
            width:800,
            resizable:false,
            close: function() {$(this).hide();}
        });
        improvementDialog();
        minimizeWindow();
       
        loadAllContacts()
        loadGroups();

        $('input[name=edit]').click(
            function(){
                showFormAddContacts(true);               
            }
        );
        $('.btnRemoveContGroup').click(
            function(){                
                $(".ContContact input:checked").each(
                    function(){                        
                        removeContactGroup($(this).siblings('.nick').val(),$("input[name=selectGroup]").val());
                        loadGroups();
                    }
                );
                
            }
        );

        if($(ele).parent().attr('class') == 'liOpenContact')
            showFormAddContacts();

        if($(ele).parent().attr('class') == 'liOpenGroup')
            addGroup();
        

    }

    $('.menuDrop ').hide();
}


function showRequest(formData, jqForm, options) {   
    //.rowAttach td *
    /*var queryString = $.param(formData);
  
    
     */
    return true; 
} 


function constructForm(id){
    form = $('#' + id + ' form[name=compose]');        
     
    $('input[name=attach]').click(function() {
       if($('.compose input[type=text]:focus').size() > 0){
           return;
       }       
       body = $.URLEncode($(form).children().find('textarea').val());       
       //alert($(form).serialize());       
       $.ajax({
            url: 'compose.php',
            type: 'POST',
            data:  'body=' + body + '&' + 'attach=true&' + $(form).serialize(),
            dataType: 'text',
            success: function(dados) {
            }
        });        
    });    
    $('a.send').click(function(){       
        body = $.URLEncode($(form).children().find('textarea').val());
        $.ajax({
            url: 'compose.php',
            type: 'POST',
            data:  'body=' + body + '&' + 'send=true&' + $(form).serialize(),
            dataType: 'text',
            success: function(dados){               
               str = dados;               
               if(str.indexOf('not sent') != -1){
                    $('.sendFail').html(dados);
                    $(".sendFail").slideDown('slow');
                    setTimeout('$(".sendFail").slideUp("slow")',3000);
               }else{
                    $('#listAttach').empty();
                    $(".rowAttach td *").hide();
                    $(".sendSuccess").slideDown('slow');
                    $('[name=send_to],[name=send_to_cc],[name=send_to_bcc],[name=subject],textarea').val('');                                        
                    setTimeout('$(".sendSuccess").slideUp("slow")',3000);
               }
            }
        });
    });
    $('a.draft').click(function() {       
       body = $.URLEncode($(form).children().find('textarea').val());
       $.ajax({
            url: 'compose.php',
            type: 'POST',
            data:  'body=' + body + '&' + 'draft=true&' + $(form).serialize(),
            dataType: 'text',
            success: function(dados) {               
               $(".draftSuccess").slideDown('slow');
               setTimeout('$(".draftSuccess").slideUp("slow")',3000);
            }
        });
    });
}

function showResponse(responseText){    
    if($("#listAttach div").size() > 0){
        $(".rowAttach td *").show();
        $('#uploadSucess').show();
        setTimeout("$('#uploadSucess').fadeOut()",5000);
    }else
        $(".rowAttach td *").hide();
}
function removeAttach(ele){
    ///form = $(ele).parent().parent().parent().parent().parent().parent().parent().attr("action");
    $(ele).siblings().filter("input:checkbox").attr("checked",true);
    //form = $('#' + id + ' form[name=compose]');
}
/*
 * Abrir o compose
 **/
var instanceCompose = 0;
function openCompose(passed_id,mailbox,startMessage,passed_ent_id,smaction){
     if($('[id^=composeInstance]').size() > 0)
        return;

    alt = $(window).height() * winP;
    larg =  $(window).width() * winP;
    
    instanceCompose++;
    id = "composeInstance" + instanceCompose;    
    dialog = '<div style="width:105% !important;" id="' + id +'"title="Compose"><div></div></div>';
    $(dialog).dialog({
        stack:true,
        height: alt,
        dialogClass: 'windowCompose',
        width: larg,
        close: function() {$(this).remove();},
        resize: function(e,ui){            
            resizeCompose($("#" + id));            
        }
    });
                
    improvementDialog();
    minimizeWindow();
    maximizeWindow();
 
    //minimizeWindow()
    $("#" + id).children().empty();
    
    if(passed_id == null && mailbox == null && startMessage == null && passed_ent_id == null && smaction == null){
         $.ajax({
            url:'compose.php',
            type: 'GET',
            data: {},
            dataType: 'text',
            success: function(dados) {
                $("#" + id).children().html(dados);
                constructForm(id);
                initCompose();
                resizeCompose($("#" + id));                
                $('form[name=compose]').ajaxForm({
                    target: '#listAttach',
                    beforeSubmit:showRequest,
                    success:showResponse                    
                });
                
                $(".btnAttach,#cancelAttach").click(
                   function(){                      
                      $("#inputAttach").toggle();                      
                   }
                );
                $("#inputAttach").draggable({cursor: 'move'});
            }
        });
    }else{
         $.ajax({
            url:'compose.php',
            type: 'GET',
            data: {'passed_id':passed_id,'mailbox':mailbox,'startMessage':startMessage,'passed_ent_id':passed_ent_id,'smaction':smaction},
            dataType: 'text',
            success: function(dados) {
                $("#" + id).children().html(dados);
                constructForm(id);
                initCompose();
                $("#inputAttach").draggable({cursor: 'move'});
                resizeCompose($("#" + id));
                $('form[name=compose]').ajaxForm({
                    target: '#listAttach',
                    beforeSubmit:showRequest,
                    success:showResponse
                });
                $(".btnAttach,#cancelAttach").click(
                   function(){                      
                      $("#inputAttach").toggle();
                   }
                );
                $("#inputAttach").draggable({cursor: 'move'});
            }

        });
    }
    $('.menuDrop ').hide();
    return;
}

/* Deletar mensagem */

function deleteMsg(smtoken,mailbox,targetMailbox,strdelete,locate,msg,startmessage,el){    
    $.ajax({
        url: 'move_messages.php',
        type: 'POST',
        data: {'smtoken':smtoken,'mailbox':mailbox,'targetMailbox':targetMailbox,'delete':strdelete,'location':locate,msg:[msg.toString()],'startMessage':startmessage},
        dataType: 'text',
        success: function(dados) {                        
            if($('input[name=flagSearch]').val() == 'true'){
                search($("input:hidden[name=smtoken]").val(),mailbox,$('input[name=whatHidden]').val(),'TEXT');
            }else{                
                loadListMails($("#alignWebmail").val(),mailbox,0,0);
            }
            verifyUnread(mailbox);
            $("#read").html('');
        }
    });
}

/* Mover mensagem*/
function moveMsg(smtoken,mailbox,targetMailbox,strmove,msg,startmessage,locate){
    $.ajax({
        url: 'move_messages.php',
        type: 'POST',
        data: {'smtoken':smtoken,'mailbox':mailbox,'targetMailbox':targetMailbox,'moveButton':strmove,'location':locate,msg:[msg.toString()],'startMessage':startmessage},
        dataType: 'text',
        success: function(dados) {
            loadListMails($("#alignWebmail").val(),mailbox,0,0,null,6,1);
            verifyUnread(mailbox);
        }
    });
}
/* Marcar mensagem como não lida*/
function markUnread(smtoken,mailbox,targetMailbox,markunread,msg,startmessage,locate){    
    $.ajax({
        url: 'move_messages.php',
        type: 'POST',
        data: {'smtoken':smtoken,'mailbox':mailbox,'targetMailbox':targetMailbox,'markUnread':markunread,'location':locate,msg:[msg.toString()],'startMessage':startmessage},
        dataType: 'text',
        success: function(dados) {          
          if($('input[name=flagSearch]').val() == true){              
              search(smtoken,mailbox,$('input[name=whatHidden]').val(),'TEXT');
          }else
              loadListMails($("#alignWebmail").val(),mailbox,0,0,null,6,1);
          verifyUnread(mailbox);
        }
    });
}
/* Marcar mensagem como lida*/
function markRead(smtoken,mailbox,targetMailbox,markread,msg,startmessage,locate){
    $.ajax({
        url: 'move_messages.php',
        type: 'POST',
        data: {'smtoken':smtoken,'mailbox':mailbox,'targetMailbox':targetMailbox,'markRead':markread,'location':locate,msg:[msg.toString()],'startMessage':startmessage},
        dataType: 'text',
        success: function(dados) {
          if($('input[name=flagSearch]').val() == true)
            search(smtoken,mailbox,$('input[name=whatHidden]').val(),'TEXT');
          else
            loadListMails($("#alignWebmail").val(),mailbox,0,0,null,6,1);
          verifyUnread(mailbox);
        }
    });
}

/* Check */
function check(align){
     $("#check").change(
        function(){
           if($(this).attr('checked')){ 
               $("#listmails input[type=checkbox]")
                .attr("checked",true)
                .parent()
                .siblings()
                .addClass('highlight');
                if(align == 'vertical')
                    $("#listmails input[type=checkbox]").parent().parent().next().children().addClass('highlight');
                $("#listmails input[type=checkbox]")
                    .parent()
                    .addClass('highlight');
           }else{
                $("#listmails input[type=checkbox]")
                .attr("checked",false)
                .parent()
                .siblings()
                .removeClass('highlight');
                 $("#listmails input[type=checkbox]")
                .parent()
                .removeClass('highlight');
                if(align == 'vertical')
                    $("#listmails input[type=checkbox]").parent().parent().next().children().removeClass('highlight');
           }
        }

     );
}

/*Resize header mail*/
function resizeHeaderMail(){
    /*if(!$.browser.msie){
        size = 26 + $(".divSubject").width() + $(".divFrom").width() + $(".divDate").width() + $(".divFlag").width();
        $(".divSubject").width($(".divSubject").width() + ($("#labelBox").width() - size));
    }else{
        $(".divSubject").width($(".divSubject").width() - 10);
    }*/
}

function linksHeader(){
    $('.openCompose').click(
        function(){            
            openCompose();
        }
    );
}

function selectMultipleAction(action){
    msg = new Array();
    box = $("input:hidden[name=mailbox]").val();
    start = $("input:hidden[name=startMessage]").val();
    smtoken = $("input:hidden[name=smtoken]").val();
    targetMailbox = $('select[name=targetMailbox]').val();
    locate = $("input:hidden[name=location]").val();
    $('#listmails input:checkbox:checked').each(
        function(index){
            msg[index] = $(this).val();
        }
    );
    switch(action){
        case 'delete':
            deleteMsg(smtoken,box,box,'delete',locate,msg,start,'');            
            break;
         case 'unread':
            markUnread(smtoken,box,box,"unread",msg,start,locate);
            break;
         case 'read':
            markRead(smtoken,box,box,"unread",msg,start,locate);
            break;
    }       
}

function captureKey(e){

    if(e.keyCode == 13 && pressedCtrl == false){        
        loadMail($("input:hidden[name=mailbox]").val(),$(".readMailBack input:checkbox").val(),$("input:hidden[name=startMessage]").val(),"","",true);        
        return false;
    }
    if(e.keyCode == 40){ //Avança a seleção das mensagens
        if($("#alignWebmail").val() == 'vertical'){            
            idMsg = $(".readMailBack input:checkbox").parent().parent().next().next().children().find('input:checkbox').val();
        }else{
            idMsg = $(".readMailBack input:checkbox").parent().parent().next().children().find('input:checkbox').val();
        }

        if(idMsg == undefined){
            idMsg = $(".readMailBack input:checkbox").val();
        }
        selectMsgForId(idMsg);
        $(".gridmail tr").each(
            function(index){
                if($(this).children().hasClass('readMailBack'))
                    ind = index;
            }
        );
        if($("#alignWebmail").val() == 'vertical'){            
            alt = ($(".gridmail tr:lt("+ ind + ")").size()) * 20;
            scroll = $('.listmailVertical').scrollTop();
            if(alt > $('.listmailVertical').height() - 80){
                $('.listmailVertical').scrollTop(scroll + 42);
            }
        }else{
            alt = ($(".gridmail tr:lt("+ ind + ")").size()) * 20;
            scroll = $('.listmailHorizontal').scrollTop();
            if(alt > $('.listmailHorizontal').height() - 20){
                $('.listmailHorizontal').scrollTop(scroll + 20);
            }
        }
        //loadMail($("input:hidden[name=mailbox]").val(),id,$("input:hidden[name=startMessage]").val(),'',e);
        return false;
    }
    if(e.keyCode == 38){ //Volta a seleção das mensagens
         if($("#alignWebmail").val() == 'vertical'){
            idMsg = $(".readMailBack input:checkbox").parent().parent().prev().prev().children().find('input:checkbox').val();
         }else{
             idMsg = $(".readMailBack input:checkbox").parent().parent().prev().children().find('input:checkbox').val();
         }

         if(idMsg == undefined){
             idMsg = $(".readMailBack input:checkbox").val();
         }
         $(".gridmail tr").each(
            function(index){
                if($(this).children().hasClass('readMailBack'))
                    ind = index;
            }
        )
        selectMsgForId(idMsg);
        if($("#alignWebmail").val() == 'vertical'){
            alt = ($(".gridmail tr:gt("+ ind + ")").size()) * 20;
            scroll = $('.listmailVertical').scrollTop();
            altd = $('.listmailVertical').height() - 90;
            if(alt > altd){
                $('.listmailVertical').scrollTop(scroll - 42);
            }
        }else{
            alt = ($(".gridmail tr:gt("+ ind + ")").size()) * 20;
            scroll = $('.listmailHorizontal').scrollTop();
            altd = $('.listmailHorizontal').height() - 20;
            if(alt > altd){
                $('.listmailHorizontal').scrollTop(scroll - 20);
            }
        }
        //loadMail($("input:hidden[name=mailbox]").val(),id,$("input:hidden[name=startMessage]").val(),'',e);
       return false;
    }
    //Pressionar delete - Inicio    
    if(e.keyCode == 46 || e.keyCode == 8 && $('input:focus').size() == 0){
        if($.browser.webkit){
            if($("#autoCompl").is(':visible') || $('input:focus').size() > 0)
                return;
        }

        if($('.readMailBack input:checkbox').val() != undefined)
            msgA = new Array($('.readMailBack input:checkbox').val());

        if($('input:checkbox:checked').size() == 0 ){            
            if($('.tdCheck').hasClass('readMailBack')){                
                $("#dialogConfirmDelete").dialog({
                    resizable: false,
                    height: 160,
                    modal: true,
                        buttons: {
                        Sim: function() {
                            deleteMsg($("input:hidden[name=smtoken]").val(),
                            $("input:hidden[name=mailbox]").val(),
                            $('select[name=targetMailbox]').val(),
                            true,
                            $("input:hidden[name=location]").val(),
                            msgA,
                            $("input:hidden[name=startMessage]").val(),'');
                            $(this).dialog('close');
                        },
                        Cancelar: function() {
                            $(this).dialog('close');
                        }
                    }
                });
            }
        }
        
        if($('input:checkbox:checked').size() > 0){            
            $("#dialogConfirmDelete").dialog({
                resizable: false,
                height: 160,
                modal: true,
                    buttons: {
                    Sim: function() {
                        selectMultipleAction('delete');
                        $(this).dialog('close');
                    },
                    Cancelar: function() {
                        $(this).dialog('close');
                    }
                }
            });
            //$("#dialogConfirmDelete").val(str);
        }
        if(e.keyCode == 8)
            return false;
    }
}

function confirmDelete(smtoken,box,targetMailbox,locate,id, start,notice){      
      $("#dialogConfirmDelete").dialog({
            resizable: false,
            height: 160,
            modal: true,
            buttons: {
            Sim: function() {
                deleteMsg(smtoken,box,targetMailbox,"Apagar",locate,id, start, '');
                //loadListMails($("#alignWebmail").val(),"INBOX",0,0);
                $(this).dialog('close');               
                /*if($("input:hidden[name=mailbox]").val() != undefined)
                    loadListMails($("#alignWebmail").val(),$("input:hidden[name=mailbox]").val(),0);*/
                $(notice).parent().parent().find('.close').trigger('click');
                reloadWebmail();
                },
                Cancelar: function() {
                    $(this).dialog('close');
                }
            }
        });
}
//confirmDelete(smtoken,box,targetMailbox,locate,id, start) + '
/*Notificação de recebimento de mensagens*/
function popupNotification(subject,from,id){
    if($(".notice").size() <= 2)
        $("#closeall").hide();
    if($('#not' + id).size() == 0 ){        
        box = $("input:hidden[name=mailbox]").val();
        if(box == undefined)
            box = "INBOX";
        start = $("input:hidden[name=startMessage]").val();
        smtoken = $("input:hidden[name=smtoken]").val();
        targetMailbox = $('select[name=targetMailbox]').val();
        locate = $("input:hidden[name=location]").val();

        if($('.notice').size() == 5)
            return;
        
        var notice = '<div style="z-index:2700" id="not' + id + '" class="notice">'
        + '<div class="notice-body">'
        + '<div onclick=confirmDelete("' + smtoken + '","' + box + '","' + targetMailbox + '","' + locate + '",' + id
        + ','+ start + ',this) id="deleteM"></div>'
        + '<img src="../images/envelope.gif" alt="" />'
        + '<h3>' + from + '</h3>'
        + '<p style="cursor:pointer !important" onclick=loadMailWin("INBOX",' + id + ',"' + $("input:hidden[name=startMessage]").val() + '",true)>' + subject + '</p>'
        + '</div>'
        + '<div class="notice-bottom">'
        + '</div>'
        + '</div>';
        
        $(notice).purr(
            {
                usingTransparentPNG: true,
                isSticky: true
            }
        );

        $('.notice #deleteM').attr("title","Delete message");
    }
} 

var timerNewMsg;
var isNotification = false;
function showNotification(){
    if($(".left").is(":visible") && isNotification == false && isUnreadCycle == false){
        isNotification = true;
        $("#loader").addClass("hideLoad");
        $.ajax({
            url:'notification.php',
            type: 'POST',
            data: {},
            dataType: 'script',
            success: function(dados) {
                $("#loader").removeClass("hideLoad");
                if($('.selFolder').attr('name') == "INBOX"){
                    loadListMails($("#alignWebmail").val(),'INBOX',0,0,null,6,1);
                }
                //verifyUnread();
                isNotification = false;
                //timerNot = setInterval('verifiyNew()',1000);
            }
        });
    }
}
function verifiyNew(){
    try{
        if(totalMsg != undefined && totalMsg > 0){
            if(totalMsg == 1){
                obj = newEmail[0];
                changeTitle("New message of " + obj.from,obj.subject);
            }else
                changeTitle("New messages  ","Total: " + totalMsg);
        }
    }catch(err){}
}
var flagTitle = 0;
function changeTitle(from,sub){
    if(flagTitle == 0){
        $(document).attr("title",from);
        flagTitle = 1;
    }else{
        $(document).attr("title",sub);
        flagTitle = 0;
    }
}

function improvementDialog(){
    
    $('.ui-widget-header:not(.ui-datepicker-header)').append("<a role='button' class='ui-dialog-titlebar-maxi ui-corner-all'><span class='ui-icon ui-icon-extlink maximize'></span></a>");
    $('.ui-dialog-titlebar-maxi:not(.ui-datepicker-header)').hover( function() {$(this).addClass('ui-state-hover');} , function() {$(this).removeClass('ui-state-hover');});
    $('.ui-dialog-titlebar-maxi:not(.ui-datepicker-header)').focus( function() {$(this).addClass('ui-state-focus');} , function() {$(this).removeClass('ui-state-focus');});

    $('.ui-widget-header:not(.ui-datepicker-header)').append("<a role='button' class='ui-dialog-titlebar-min ui-corner-all' ><span class='ui-icon ui-icon-minus'></span></a>");
    $('.ui-dialog-titlebar-min:not(.ui-datepicker-header)').hover( function() {$(this).addClass('ui-state-hover');} , function() {$(this).removeClass('ui-state-hover');});
    $('.ui-dialog-titlebar-min:not(.ui-datepicker-header)').focus( function() {$(this).addClass('ui-state-focus');} , function() {$(this).removeClass('ui-state-focus');})
    
}

function restore(id){
    $(".hideMsg" + id).show();
    $(this).parent().parent().parent().removeClass('hideMsg' + id);
    $(".iconMinimize" + id).remove();
}

var contMinimize = 0;
function maximizeWindow(){
     var widthOrig;
     var heightOrig;
     var leftOrig;
     var topOrig;  
     $('.maximize').toggle(
        function(){
            widthOrig =  $(this).parent().parent().parent().width();
            heightOrig =  $(this).parent().parent().parent().height();
            leftOrig = $(this).parent().parent().parent().position().left;
            topOrig = $(this).parent().parent().parent().position().top;
            
            $(this).parent().parent().parent().find("div[id^=composeInstance]").height($(window).height());
            $(this).parent().parent().parent().find("div[id^=composeInstance]").width($(window).width());
            $(this).parent().parent().parent().find('[id^=resInstance]').height($(window).height() - 60);
            $('div[id^=composeInstance] .compose input:text').width($(window).width() - 160);
            if($(this).hasClass('ui-icon-extlink')){
                $(this).removeClass('ui-icon-extlink')
                   .addClass('ui-icon-newwin');
            }
            $(this).parent().parent().parent().width($(window).width());
            $(this).parent().parent().parent().height($(window).height());
            $(this).parent().parent().parent().css({"top":"0","left":"0"});
            resizeEditor();
            if($(".tab_content").is("not(:visible)")){             
               resizeCompose($(this).parent().parent().parent().find("div[id^=composeInstance]"));               
            }                        
        },
        function(){            
            if($(this).hasClass('ui-icon-newwin')){
                $(this).removeClass('ui-icon-newwin')
                   .addClass('ui-icon-extlink');
            }
            $(this).parent().parent().parent().width(widthOrig);
            $(this).parent().parent().parent().height(heightOrig);
            $(this).parent().parent().parent().find("div[id^=composeInstance]").height(heightOrig);
            $(this).parent().parent().parent().find('[id^=resInstance]').height(heightOrig - 60);
            $(this).parent().parent().parent().find("div[id^=composeInstance]").width(widthOrig);
            $('div[id^=composeInstance] .compose input:text').width(widthOrig - 170);
            $(this).parent().parent().parent().css({"top":topOrig,"left":leftOrig});
            resizeEditor();
            if($(".tab_content").is("not(:visible)")){
               resizeCompose($(this).parent().parent().parent().find("div[id^=composeInstance]"));
            }
        }
    );
}

function minimizeWindow(){
     $('.ui-icon-minus').click(        
        function(){
            //.minicompose,.miniread,.minioptions,.minifolder,.minicontacts,.minipic{
            if($(this).parent().parent().parent().hasClass('windowCompose'))
                nameClass = 'minicompose';
            if($(this).parent().parent().parent().hasClass('windowRead'))
                nameClass = 'miniread';
            if($(this).parent().parent().parent().hasClass('windowOptions'))
                nameClass = 'minioptions';
            if($(this).parent().parent().parent().hasClass('windowFolder'))
                nameClass = 'minifolder';
            if($(this).parent().parent().parent().hasClass('windowContacts'))
                nameClass = 'minicontacts';
            if($(this).parent().parent().parent().hasClass('windowPic'))
                nameClass = 'minipic';
            contMinimize++;
            if($(this).parent().parent().parent().find('.subjectMsg td').size() > 0){
                str = $(this).parent().parent().parent().find('.subjectMsg td').html();                
                title = str.match(/:&nbsp;&nbsp;.+/).toString();
                title = title.replace(/:&nbsp;&nbsp;/,'').toString();                                
            }else
                title = $(this).parent().parent().next().children().find("input:hidden").val();
            $("<div title='" + title  + "' class='" + nameClass +" iconMinimize" + contMinimize + "' onclick=restore(" + contMinimize  + ")></div>").appendTo('.barcontrol>div');
            $(this).parent().parent().parent().addClass('hideMsg' + contMinimize);
            $(this).parent().parent().parent().addClass('teste');
            $(this).parent().parent().parent().hide();
        }
    );    
}

var isUnreadCycle = false;
function verifyUnread(folder){ 
    if($(".left").is(":visible") && isNotification == false && isUnreadCycle == false && $('#browserFake').size() == 0){
        isUnreadCycle = true;
        $.ajax({
            url:'unread_ajax.php',
            type: 'POST',
            data: {'folder': folder},
            dataType: 'script',
            success: function(dados) {
                try{
                    $(".labelNumMSg").corner();
                }catch(err){

                }
                isUnreadCycle = false;
            }
        });
    }
}

function sort(){
    $(".ascSub").click(
        function(){
            loadListMails($("#alignWebmail").val(),$("input:hidden[name=mailbox]").val(),$("input:hidden[name=startMessage]").val(),0,'',5);
        }
    );
}

function resizeEditor(){
    $('iframe[id^=mce]').width($('.windowCompose').width() - 5);
    altWindow = $('.windowCompose').height();
    altEditor =  altWindow - 200;
    $('iframe[id^=mce]').height(altEditor);    
}


function resizeCompose(id){
    $(id).width($(id).parent().width());    
    $('.compose input:text').width($(id).parent().width() - 170);    
    $('#inputAttach').css('top',$('input[name=send_to_cc]').position().top + 90);    
    resizeEditor();
}

function initCompose(){
    //rowBcc rowCc
    $('.ShowHideCC').click(
        function(){            
            $(this).parent().parent().siblings().filter('.rowCc').children().children().slideToggle('fast');            
        }
    );
    $('.hideBcc').click(
        function(){
            $(this).parent().parent().siblings().filter('.rowBcc').children().children().slideToggle('fast');         
        }
    );    
   
}


function initEditor(input){
   /* var config = {
        resize_enabled:false,
        height: 400,
        toolbar:
        [Editor
            ['FontName','FontSize','Bold','Italic','Underline','-','OrderedList','UnorderedList','-','JustifyLeft','JustifyCenter','JustifyRight','JustifyFull','-','Link','Unlink','TextColor','BGColor','Smiley','Image','Source']

        ]
    };
    $(input).ckeditor(config);*/

    
     $().ready(function() {

        $(input).tinymce({
            // Location of TinyMCE script
            width:'100%',
            height:'100%',
            script_url : '../js/tinymce/jscripts/tiny_mce//tiny_mce.js',
            plugins : "safari,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",
            // General options
            theme : "advanced",
            // Theme options
            theme_advanced_buttons1 : "cut,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,formatselect,fontselect,fontsizeselect,paste,pastetext,pastword,image,undo,redo,link,unlink,forecolor,backcolor,code",
            theme_advanced_buttons2 : "",
            theme_advanced_buttons3 : "",
            theme_advanced_toolbar_location : "top",
            theme_advanced_toolbar_align : "left",
            theme_advanced_statusbar_location : "bottom",
            theme_advanced_resizing : false,

            // Example content CSS (should be your site CSS)
            content_css : "css/content.css",

            // Drop lists for link/image/media/template dialogs
            template_external_list_url : "lists/template_list.js",
            external_link_list_url : "lists/link_list.js",
            external_image_list_url : "lists/image_list.js",
            media_external_list_url : "lists/media_list.js",

            // Replace values for the template plugin
            template_replace_values : {
                    username : "Some User",
                    staffid : "991234"
            }
        });
        setTimeout('resizeEditor()',1000);
    });

}

function createControlPaginator(startmessage,mailbox){
    $.ajax({
        url:'get_paginator.php',
        type: 'POST',
        data: {'startmessage':startmessage,'mailbox':mailbox},
        dataType: 'text',
        success: function(dados) {
            $("#barmenucontrol #controlPage").html(dados);
            $('#numPage').keydown(
                 function(e){                    
                    if(e.keyCode == 13){
                       var flagPage = true;
                       if($(this).val() <  parseInt($("#TotalPages").text()) && $(this).val() > 0){
                          msg = ($(this).val() - 1) * $('input[name=show_num]').val() + 1;
                          loadListMails($("#alignWebmail").val(),$('input[name=mailbox]').val(),0,0,null,6,msg);
                       }else{
                           return;
                       }
                    }
                 }
            );
        }
    });
}


function search(smtoken,mailbox,what,where){    
    align = $("#alignWebmail").val();    
    $.ajax({
        url:'search.php',
        type: 'GET',
        data: {'smtoken':smtoken,'mailbox':mailbox,'where':where,'what':what},
        dataType: 'text',
        success: function(dados) {           
            $(".listmail").html(dados);            
            resizeMail(align);
            contextMenu();
            highlight(align);
            dragMsg(); //
            if($('.tableHeader').width() ==  $('.listmailHorizontal').width())
                $('.tableHeader').width($('.tableHeader').width() + 2);
            dropMsg();
            check(align);
            $(".gridmail, .gridmail td").css("cursor:pointer;border-color,white");
            $(".headerMsg,.headerMsgVer,#labelBox").show();
            $(".divSubject").width($(".tdSubject").width());
            $(".divFrom").width($(".tdFrom").width());
            $(".divDate").width($(".tdDate").width());
            var size = 0;
            resizeHeaderMail(); 
            if($("#select_checkbox").val() == 0){
                selectMultiple();
            }

            $(".gridmail td:not(.folderEmpty)").click(
                function(event){                    
                    if(align == 'vertical'){
                        if($(this).parent().children().find("input:checkbox").val() == undefined)
                            loadMail($("input:hidden[name=mailbox]").val(),$(this).parent().prev().children().find("input:checkbox").val(),$("input:hidden[name=startMessage]").val(),'',event);
                        else
                            loadMail($("input:hidden[name=mailbox]").val(),$(this).parent().children().find("input:checkbox").val(),$("input:hidden[name=startMessage]").val(),'',event);
                    }else
                        loadMail($("input:hidden[name=mailbox]").val(),$(this).parent().children().find("input:checkbox").val(),$("input:hidden[name=startMessage]").val(),'',event);
                }
            );
            $(".gridmail td").dblclick(
                function(event){
                    if(align == 'vertical'){
                        if($(this).parent().children().find("input:checkbox").val() == undefined)
                            loadMailWin($("input:hidden[name=mailbox]").val(),$(this).parent().prev().children().find("input:checkbox").val(),$("input:hidden[name=startMessage]").val(),'');
                        else
                            loadMailWin($("input:hidden[name=mailbox]").val(),$(this).parent().children().find("input:checkbox").val(),$("input:hidden[name=startMessage]").val(),'');
                    }else{
                        loadMailWin($("input:hidden[name=mailbox]").val(),$(this).parent().children().find("input:checkbox").val(),$("input:hidden[name=startMessage]").val(),'');
                    }
                }
            );
            
            color = $('.listmailHorizontal').css('border-bottom-color');
            if(align != 'vertical'){
                $('.tableHeader th:first').css("border-left","1pt solid " + color);
                $('.tableHeader th:last').css("border-right","1pt solid " + color);
            }
        }
    });
}

function inputSearch(){    
    $('#search').show();
    $('input[name=where]').change(
        function(){
            $('#optionSearch').hide();
        }
    );
    $('#search input:text').keyup(
        function(e){
            if(e.keyCode == 13){
                search($("input[name=smtoken]").val(),
                $("input[name=mailbox]").val(),
                $("input[name=what]").val(),
                $("input[name=where]:checked").val());
            }
        }
    );
    $('#search input:text').click(
        function(){            
            if($(this).hasClass('searchEmpty')){
                $(this).val('');
                $(this).removeClass('searchEmpty');
            }
            $(this).css("color","black");
            $('#optionSearch').hide();
        }
    );
    $('#search img').click(
        function(el){        
            elemento = $("#barmenucontrol");
        
            $('#optionSearch').css('top',elemento.position().top + 19);
            $('#optionSearch').css('left',elemento.position().left + 16);
            $('#optionSearch').show();
        }
    );
}
function saveOptions(){
    if($('input.fieldRequired').is(':empty')){
        $('input.fieldRequired').focus();
        return;
    }
    str ="";    
    $.ajax({
        url:'options.php',
        type: 'POST',
        data: $('.tab_container form:visible').serialize(),
        dataType: 'text',
        success: function(dados) {
            $.get('ajax.php',{'optage': 'personal'},function(dados) {
                $('#tab1').html(dados);
            },'text');
            $.get('ajax.php',{'optage': 'display'},function(dados) {
                $('#tab2').html(dados);
            },'text');
            str = dados;            
            if(str.search('Successfully Saved Options') != -1){
                $(".saveSuccess").slideDown('slow');
                setTimeout("$('.saveSuccess').slideUp('slow')",4000);
                setTimeout("location.href = 'webmail.php'",4300);
            }else{
                $(".saveError").slideDown('slow');
                setTimeout("$('.saveError').slideUp('slow')",8000);
            }            
            //$("#tab1").load('options.php?optpage=personal');
            //$("#tab2").load('options.php?optpage=display');
        }
    });
}

function confirmDeleteFolder(name){
    $("#dialogConfirmDeleteFolder").dialog({
        resizable: false,
        height: 160,
        modal: true,
        buttons: {
            Sim: function() {
                deleteFoldersdelete(name);
                $(this).dialog('close');
            },
            Cancelar: function() {
                $(this).dialog('close');
            }
        }
    });
}
function unSubFolders(){
    str = "";
    $(".selectUnsub option:checked").each(
        function(){
            str += '&mailbox[]=' + $(this).val();
        }
    );        
    $.ajax({
        url: 'folders_subscribe.php?method=unsub',
        type: 'POST',
        data: 'smtoken=' + $("input:hidden[name=smtoken]").val() + str ,
        dataType: 'text',
        success: function(dados) {
            $("#tabfolder").load('folders.php');
        }
    });

}

function subFolders(){
    str = "";
    $(".selectSub option:checked").each(
        function(){
            str += '&mailbox[]=' + $(this).val();
        }
    );
    $.ajax({
        url: 'folders_subscribe.php?method=sub',
        type: 'POST',
        data: 'smtoken=' + $("input:hidden[name=smtoken]").val() + str ,
        dataType: 'text',
        success: function(dados) {
            $("#tabfolder").load('folders.php');
        }
    });

}

//dialogRename
function renameFolder(){
    $(".dialogRename").dialog({
        resizable: false,
        height: 160,
        modal: true,
        buttons: {
            Sim: function() {
                str = $('select[name=old]').val();
                name = str.substr(str.lastIndexOf('.') + 1);                
                renameFoldersDialog($('.dialogRename input[name=newfolder]').val(),name,str);                
                $(this).dialog('close');
            },
            Cancelar: function() {
                $(this).dialog('close');
            }
        }
    });
}

function changeBackGroup(field){
    $(field).addClass('backGroup');
}
function changeBackContact(field){
    $(field).addClass('backContact');
}

function showInfoContact(field){
   $('.ContContact div').css('font-weight','lighter');
   $(field).css('font-weight','bold');
   //$(".ContInfo .content,.divAddGroup").hide();
   $('.infoCont,.divAddGroup').hide();
   $(".ContInfo .content").show()
   //$('.infoCont').show();
   
   $('.nameContact').text($(field).text());
   $(".nickContact").text($(field).children().filter(".nick").val());
   $(".emailContact").text($(field).children().filter(".email").val());
   $(".infoContact").text($(field).children().filter(".extra").val());   
   $(".fnContact").text($(field).children().filter(".firstname").val());
   $(".lnContact").text($(field).children().filter(".lastname").val());
   $(".ContInfo .content").load('addressbook.php?showForm=true&editContato=true');   
}

function showFormAddContacts(edit){
    $('.infoCont,.divAddGroup').hide();
    $(".ContInfo .content").show()
    if(edit)
        $(".ContInfo .content").load('addressbook.php?showForm=true&editContato=true');
    else
        $(".ContInfo .content").load('addressbook.php?showForm=true');
}
//dress ) [oldnick] => fadf [backend] => 1 [doedit] => 1 )

function editContact(){
    //Array ( [smtoken] => zVaPuzmPjvyt [addaddr] => Array
    //( [nickname] => fadf [email] => asda [firstname] => asdafds [lastname] => asdfasd
    //[label] => fasdf [SUBMIT] => Add address ) [backend] => 1 )
    //editaddr
     
    nickname = $('.ContInfo .addrnickname').val();
    strEmail = $('.ContInfo .addremail').val();
    firstname = $('.ContInfo .addrfirstname').val();
    lastname = $('.ContInfo .addrlastname').val();
    label = $('.ContInfo .addrlabel').val();
    
    str = '&editaddr[nickname]=' + nickname + '&editaddr[email]=' + strEmail + '&editaddr[firstname]=' + firstname
    + '&editaddr[lastname]=' + lastname + '&editaddr[label]=' + label + '&oldnick=' + $(".nickContact").text() +'&doedit=1&backend=1&SUBMIT=Update address';        
    $.ajax({
        url: 'addressbook.php',
        type: 'POST',
        data: 'smtoken=' + $("input:hidden[name=smtoken]").val() + str ,
        dataType: 'text',
        success: function(dados) {

            $(".ContContact .content").load('addressbook.php?showListContact=true');
        }
    });
}

function addContact(){
    
    //Array ( [smtoken] => zVaPuzmPjvyt [addaddr] => Array
    //( [nickname] => fadf [email] => asda [firstname] => asdafds [lastname] => asdfasd
    //[label] => fasdf [SUBMIT] => Add address ) [backend] => 1 )
    nickname = $('.ContInfo .addrnickname').val();
    strEmail = $('.ContInfo .addremail').val();
    firstname = $('.ContInfo .addrfirstname').val();
    lastname = $('.ContInfo .addrlastname').val();
    label = $('.ContInfo .addrlabel').val();

    str = '&addaddr[nickname]=' + nickname + '&addaddr[email]=' + strEmail + '&addaddr[firstname]=' + firstname
    + '&addaddr[lastname]=' + lastname + '&addaddr[label]=' + label;
    
    $.ajax({
        url: 'addressbook.php',
        type: 'POST',
        data: 'smtoken=' + $("input:hidden[name=smtoken]").val() + str ,
        dataType: 'text',
        success: function(dados) {
            $(".ContContact .content").load('addressbook.php?showListContact=true');            
            //$(".ContInfo input:text").val('');
        }
    });
}
function changeGroup(name,field,all){
    $(".nameGroup").css('font-weight','lighter');
    $(field).css('font-weight','bold');
    $('.infoCont,.ContInfo .content').hide();
    $(".divAddGroup").show();
    $('.Continfo input[type=text]').val(name);

    $("input[name=selectGroup]").val(name);

    $('.btnAddGroup').hide();
    $('.btnRenameGroup,.btnDeleteGroup').show();
    
    if(all){
        loadAllContacts();
    }else{
        $.ajax({
            url: '../plugins/abook_group/listMembers.php',
            type: 'GET',
            data: 'groupName=' + $("input[name=selectGroup]").val(),
            dataType: 'text',
            success:function(dados){
                $(".ContContact .content").html(dados);
                $(".ContContact .content input:checkbox").change(
                    function(){
                        if($(this).is(":checked"))
                            $(this).parent().addClass('backContactCheck');
                        else
                            $(this).parent().removeClass('backContactCheck');
                    }
                );
            }
        });
    }

    $(".divAddGroup .btnDeleteGroup").click(
        function(){
            removeGroup($("input[name=selectGroup]").val());
            return;
        }        
    );
    

    $(".btnRenameGroup").click(
        function(){
            $.ajax({
                url: '../plugins/abook_group/handler_group.php',
                type: 'GET',
                data: 'action=4&group=' + $("#selectGroup").val() + '&newName=' + $('#nameGroup').val(),
                dataType: 'text',
                success:function(dados){                                        
                    loadGroups();
                    $("input[name=selectGroup]").val($('input[type=text]').val());
                    return;
                }
            });
            return;
        }        
    );    
}

function addGroup(){
    $('.infoCont,.ContInfo .content').hide();
    $(".divAddGroup").show();

    $('input[type=text]').val('');
    $('.btnAddGroup').show();
    $('.btnRenameGroup,.btnDeleteGroup').hide();

    $(".divAddGroup .btnAddGroup").click(
        function(){            
            $.ajax({
                url: '../plugins/abook_group/creategroup.php',
                type: 'GET',
                data: 'group=' + $(".divAddGroup input:text").val(),
                dataType: 'text',
                success: function(dados) {
                    loadGroups();
                    $('input[type=text]').val('');
                }
            });
        }
    );
}

function deleteContats(){
    str = '';
    $(".ContContact input[name^⁼sel]:checked").each(
        function(){
            str =  str + '&' + $(this).attr('name') + '=' + $(this).val();
        }
    );
    if($(".ContContact input[name^⁼sel]:checked").size() > 0){
        $.ajax({
            url: 'addressbook.php',
            type: 'POST',
            data: 'smtoken=' + $("input:hidden[name=smtoken]").val() + str + '&deladdr=true',
            dataType: 'text',
            success: function(dados) {                
                 $(".ContContact .content").load('addressbook.php?showListContact=true');
                 loadGroups();
            }
        });
    }else{
        $(".msgContact").show();
        setTimeout('$(".msgContact").hide()',4000);
    }

}
function removeGroup(name){    
    $.ajax({
        url: '../plugins/abook_group/handler_group.php',
        type: 'GET',
        data: 'action=3&group=' + name ,
        dataType: 'text',
        success: function(dados) {
            loadGroups();
            $('input[type=text]').val('');
        }
    });
    return;
}

function moveContacts(){
    $(".contactPerson").draggable({
        cursor: "move",
        delay: 300,        
        cursorAt: {left: -20},
        start: function(){
            $(".ContGroup .content .nameGroup").droppable({
            accept: '.contactPerson',
            hoverClass: 'backGroup',
            drop: function(){
                el = $(this);                
                $(".ContContact input:checked").each(
                    function(){
                        $(".nameGroup").css('font-weight','lighter');
                        $(el).css('font-weight','bold');
                        addUserGroup($(this).siblings(".nick").val(),el.children('.spanNameGroup').text());
                        loadGroups();
                    }
                );
            }
        });
        },
        helper: function(event){
            $(this).children().filter(":checkbox").attr("checked",true);
            if($(".ContContact input:checked").size() > 1){                
                return $('<div id="moveHelperContacts"><img src="../images/group.png">\n\
                        <span>Total Contatos: ' + $(".ContContact input:checked").size() + '</span></div>');
            }
            if($(".ContContact input:checked").size() == 1){                
                return $('<div id="moveHelperContacts"><img src="../images/contact.png"><span>Total Contatos: ' + $(".ContContact input:checked").size() + '</span></div>');
            }
        }
    });
}

function addUserGroup(user,group){
    $.ajax({
        url: '../plugins/abook_group/handler_group.php',
        type: 'GET',
        data: 'action=1&group=' + group + '&user=' + user,
        dataType: 'text',
        success: function(dados) {            
            $.ajax({
                url: '../plugins/abook_group/listMembers.php',
                type: 'GET',
                data: 'groupName=' + group,
                dataType: 'text',
                success:function(dados){
                    $(".ContContact .content").html(dados);
                    loadGroups();
                }
            });
        }
    });
}
function loadGroups(){
    $.ajax({
        url: '../plugins/abook_group/list_abook_group.php',
        type: 'GET',
        data: '',
        dataType: 'text',
        success:function(dados){
            $(".ContGroup .content").html(dados);
            $('.nameGroup:first').css('font-weight','bold');
            $(".labelNumCont").corner();
            if(correctionIE)
                $(".labelNumMSg").css('float','none');
        }
    });
}

function removeContactGroup(user,group){
    $.ajax({
        url: '../plugins/abook_group/handler_group.php',
        type: 'GET',
        data: 'action=2&group=' + group + '&user=' + user,
        dataType: 'text',
        success:function(dados){
            $.ajax({
                url: '../plugins/abook_group/listMembers.php',
                type: 'GET',
                data: 'groupName=' + group,
                dataType: 'text',
                success:function(dados){
                    $(".ContContact .content").html(dados);
                }
            })
        }
    });
}

function loadAllContacts(){
    $.ajax({
        url: 'addressbook.php',
        type: 'GET',
        data: 'showListContact=true',
        dataType: 'text',
        success:function(dados){
            $(".ContContact .content").html(dados);
            $(".ContContact .content input:checkbox").change(
                function(){
                    if($(this).is(":checked"))
                        $(this).parent().addClass('backContactCheck');
                    else
                        $(this).parent().removeClass('backContactCheck');
                }
            );
        }
    });
}

function showDropMenu(menu,el){
	if(correctionIE){
            $('.menuDrop ul').css({'position' : 'relative', 'left' : '-30px'});
    }

    if($(menu).is(":visible"))
        $(menu).hide();
    else
        $(menu).show();
	
    $(menu).css('top',$(el).position().top + 19);
    $(menu).css('left',$(el).position().left);
    
}

function reloadWebmail(){
    loadFolders(true);
    loadListMails($("#alignWebmail").val(),"INBOX",0,0,null,6,1);

}
function replyDropMenu(replyAll){
    box = $("input:hidden[name=mailbox]").val();
    start = $("input:hidden[name=startMessage]").val();
    id = $("#read #idMsgRead").val();
    if(id != undefined){
        if(replyAll){
            openCompose(id,box,start,0,'reply_all');
        }else{
            openCompose(id,box,start,0,'reply');
        }
    }
}
function forwardDropMenu(){
    box = $("input:hidden[name=mailbox]").val();
    start = $("input:hidden[name=startMessage]").val();
    id = $("#read #idMsgRead").val();
    if(id != undefined)
        openCompose(id,box,start,0,'forward');
}

function dropDelMgs(){
    msg = new Array();
    msg[0] = $("#read #idMsgRead").val();
            
    box = $("input:hidden[name=mailbox]").val();
    start = $("input:hidden[name=startMessage]").val();
    smtoken = $("input:hidden[name=smtoken]").val();
    targetMailbox = $('select[name=targetMailbox]').val();
    locate = $("input:hidden[name=location]").val();

    if($('#listmails input[type=checkbox]:checked').size() > 0){
        $("#dialogConfirmDelete").dialog({
            resizable: false,
            height: 160,
            modal: true,
            buttons: {
            Sim: function() {
                selectMultipleAction('delete');
                $(this).dialog('close');
            },
            Cancelar: function() {
                $(this).dialog('close');
            }
        }
        });
    }else{
        confirmDelete(smtoken,box,targetMailbox,true,msg, start,this);
    }
}
function print(id,box){
    if(id== undefined)
        return;
    window.open("../src/printer_friendly_main.php?passed_ent_id=0&mailbox=" + box + "&passed_id="+ id +"&view_unsafe_images=","Print","width=800,height=600");
}

function showInfoMsg(el){
    if($(el).find('.hideSub').text() == '+'){
        $(el).siblings().slideDown();
        //$('.formatHeader .row:not(.formatHeader .row:first)').slideDown();
        $(el).find('.hideSub').text('-');
    }else{
        $(el).siblings().slideUp();        
        $(el).find('.hideSub').text('+');
    }   
}

/*
 *Carrega a lista de contatos em memória
 * return Objeto Json com nome e email
 *  **/
function loadContacts(query){
    var jsonContacts;
    $.ajax({
        url:'search_ajax.php',
        type: 'POST',
        async: false,
        data: {'addrquery': query},
        dataType: 'json',
        success: function(dados) {
            jsonContacts = dados;
        }        
    });
    return jsonContacts;
}

function search_contact(query,field){
    $("#autoCompl").show();
    $("#autoCompl").css("top",field.position().top + field.height() + 4);
    $("#autoCompl").css("left",field.position().left);
    $("#autoCompl ul").empty();

    cont = 0;
    if(field.attr("name") == 'send_to')
        idText = 0;
    if(field.attr("name") == 'send_to_cc')
        idText = 1;            
    if(field.attr("name") == 'send_to_bcc')    
        idText = 2;

    //dadosCon Varíavel global com todos os contatos
    //dadosGroups Varíavel global com todos os grupos
    //dadosGroups
    for(i = 0; i <  dadosGroups.groups.length ; i++ ){
        group = dadosGroups.groups[i];
        groupComp = group.toLowerCase();
        if(groupComp.indexOf(query.toLowerCase()) != -1){
            $("#autoCompl ul").append('<li class="listGroup" onclick=addGroupInput("' + group + '",' + idText + ') onmouseout=highlightLi(this,"remove") onmouseover=highlightLi(this,"add")>' + group + ' (GRUPO)</li>');
        }
    }
    for(i = 0; i < dadosCon.nome.length ; i++ ){
        if($.browser.msie){
            name = dadosCon.nome[i].toLowerCase();            
            if(name.indexOf(query.toLowerCase()) != -1){
                $("#autoCompl ul").append('<li class="listContact" onclick=addMailInput(this,idText)  onmouseout=highlightLi(this,"remove") onmouseover=highlightLi(this,"add")>' + name + ' &lt;' + dadosCon.email[i].toLowerCase() + '&gt; </li>');
                cont++;
            }            
        }else{
            name = dadosCon.nome[i].toLowerCase();
            email = dadosCon.email[i].toLowerCase();
            if(name.indexOf(query.toLowerCase()) != -1 || email.indexOf(query.toLowerCase()) != -1){
                $("#autoCompl ul").append('<li class="listContact" onclick=addMailInput(this,idText)  onmouseout=highlightLi(this,"remove") onmouseover=highlightLi(this,"add")>' + name + ' &lt;' + email  + '&gt; </li>');
                cont++;
            }
        }
    }

}

function addMailInput(field,campo){
    $("#autoCompl").hide();
    campoStr = $('.compose input:text').eq(campo).val();
    str = $(field).text();
    stremail = str.substring(str.indexOf('<') + 1 ,str.indexOf('>'));
    if(campoStr.indexOf(',') == -1)
        $('.compose input:text').eq(campo).val(stremail);
    else{        
        $('.compose input:text').eq(campo).val(campoStr.substring(0,campoStr.lastIndexOf(',') + 1) + ' ' + stremail);
    }

}

function highlightLi(field,action){
    if(action == 'add')
        $(field).addClass('selCont');
    else
        $(field).removeClass('selCont');
}

/*
 * Retorna um objeto JSON com os nomes dos grupos do usuário para o autocomplete
 *
 **/
function loadGroupsJson(){
    var jsonGroups;
    $.ajax({
        url:'../plugins/abook_group/handler_group.php',
        type: 'GET',
        async: false,
        data: 'action=6',
        dataType: 'json',
        success: function(dados) {
            jsonGroups = dados;
        }
    });
    return jsonGroups;
}
/*
 * Adicionar o grupo ao input no autocomplete
 **/
function addGroupInput(group,id){
    var jsonGroups;
    strMail = "";
    $.ajax({
        url:'../plugins/abook_group/handler_group.php',
        type: 'GET',
        async: false,
        data: {'action':5,'group':group},
        dataType: 'json',
        success: function(dados) {
            if(dados.email.length > 0){
                for(i=0; i < dados.email.length; i++){
                    if(dados.email.length - 1 == i)
                        strMail = strMail + dados.email[i];
                    else
                        strMail = strMail + dados.email[i] + ', ';
                }
            }
            campoStr = $('.compose input:text').eq(id).val();
            if(campoStr.indexOf(',') == -1){
                $('.compose input:text').eq(id).val(strMail);
            }else{
                $('.compose input:text').eq(id).val(campoStr.substring(0,campoStr.lastIndexOf(',') + 1) + ' ' + strMail);
            }
        }
    });
    return jsonGroups;
}

function loadFormExport(){
    $('.infoCont,.divAddGroup').hide();
    $(".ContInfo .content").show();
    $(".ContInfo .content").load('addressbook.php?export=1');
}

function dialogImport(){
    $("#importWin").dialog({
        width:980,
        height:300
    });
    $('#formImportConfirm').ajaxForm({
        success:loadAllContacts
    });
 }
 
function loadFormImport(){
    $('.infoCont,.divAddGroup').hide();
    $(".ContInfo .content").show();    
    $.ajax({
        url:'addressbook.php',
        type: 'GET',
        data: 'import=1',
        dataType: 'text',
        success: function(dados) {
            $(".ContInfo .content").html(dados);            
            $('#importForm').ajaxForm({
                target: '#importWin',
                success:dialogImport
            });           
        }
    });
    
}
/*function dialogImgTexMail(url){
    $('#dialogOpMail').dialog({
        width:800,
        height:600
    });
    improvementDialog();    
    maximizeWindow();
    $("#dialogOpMail div").load(url);
    
  
}*/
var instanceResource = 0;

function dialogConfirmCalendar(url){
    instanceResource++;
    id = "resInstance" + instanceResource;
    dialog = '<div title="Success" id="' + id +'"><div>Waiting...</div></div>';
    height = $(window).height() * 0.60;

    $(dialog).dialog({
        dialogClass: 'windowCalendar',
        width:400,
        resizable:false,
        height:170
    });
    $("#" + id + " div").load(url);
}

function dialogImgTexMail(url){
    instanceResource++;
    id = "resInstance" + instanceResource;
    dialog = '<div id="' + id +'"><div></div></div>';

    width = $(window).width() * 0.60;
    height = $(window).height() * 0.60;
    
    $(dialog).dialog({
        dialogClass: 'windowPic',
        width:width,
        height:height
    });
    
    improvementDialog();
    maximizeWindow();
    minimizeWindow();
    $("#" + id + " div").load(url);
}

function changeGroupCompose(group){    
    $.ajax({
        url: '../plugins/abook_group/handler_group.php',
        type: 'GET',
        data: 'action=7&group=' + group,
        dataType: 'json',
        success:function(dados){
            $("#listComposeContacts").empty();
            for(i = 0; i < dados.name.length; i++){
                $("#listComposeContacts").append('<li onclick=populaInputContact("' + dados.email[i] + '",0)>' + dados.name[i]  + '</li>');
            }                        
        }
    });
}

function populaInputGroup(group){    
    $.ajax({ 
        url: '../plugins/abook_group/handler_group.php',   
        type: 'GET',
        data: 'action=7&group=' + group, 
        dataType: 'json',
        success:function(dados){            
            for(i = 0; i < dados.email.length; i++){                
                if($('textarea:visible').is(':empty'))
                    $('textarea:visible').text(dados.email[i]);
                else
                    $('textarea:visible').text($('textarea:visible').text() + ', ' + dados.email[i]);                
            }
        }
    });
}


function populaInputContact(contact){
    ind = $('input[name=radDest]:checked').index();
    if($('.compose input:text').eq(ind).val() == "")
        $('.compose input:text').eq(ind).val(contact);
    else
        $('.compose input:text').eq(ind).val($('.compose input:text').eq(ind).val() + ', ' + contact);
}


function showDialogIdentities(){
    $("#dialogOpIdentities").dialog({
        resizable: false,
        zIndex:3997,
        stack: true,
        height: 500,
        width:700
    });
    $("#dialogOpIdentities div").load('../src/options_identities.php');
}

//Correção falha do firefox
function correctionFF(){    
    if($.browser.mozilla || $.browser.webkit){
        if($('.listmailHorizontal').is(':visible') && $('.listmailHorizontal').width() == 0);
            resizeWidth();
        if($('#listmails').position().left == 0)
            resizeWidth();
        if($('.gobox').position() != null){
            if($('.gobox').position().left == 0){
                
            }
        }
    }
}

function testeLocal(){
    $('.notice').each(
        function(index){$
            if($.browser.msie)
                $(this).show();
        }
    );
}

function errorPage(){
    $(".body").css({"margin":0 , "overflow": "hidden","padding": 0});
    $(".right,#listmails"). width("100%").height("100%").css("overflow", "hidden");
    $(".left,.barcontrol,.header,#read,.tableheader,#divisorleft,.pagelink,#divisorHorizontal").remove();
}

function sortDate(){
    $('.orderDate').toggle(
        function(){
            $(".dscOrderSub,.ascOrderSub,.dscOrderFrom,.ascOrderFrom,.dscOrderDate,.ascOrderDate").hide();
            $(".dscOrderDate").show();
            //$('.orderDate div').addClass('iconOrderAsc');
            loadListMails($("#alignWebmail").val(),$("input[name=mailbox]").val(),0,0,null,0)
        },
        function(){
            $(".dscOrderSub,.ascOrderSub,.dscOrderFrom,.ascOrderFrom,.dscOrderDate,.ascOrderDate").hide();
            $(".ascOrderDate").show();
            loadListMails($("#alignWebmail").val(),$("input[name=mailbox]").val(),0,0,null,1)
        },
        function(){
            orderNormal();
        }
    );
 }


 function sortFrom(){
     $('.orderFrom').toggle(
        function(){
            $(".dscOrderSub,.ascOrderSub,.dscOrderFrom,.ascOrderFrom,.dscOrderDate,.ascOrderDate").hide();
            $(".dscOrderFrom").show();
            //$('.orderDate div').addClass('iconOrderAsc');
            loadListMails($("#alignWebmail").val(),$("input[name=mailbox]").val(),0,0,null,2);
        },
        function(){
            $(".dscOrderFrom").hide();
            $(".ascOrderFrom").show();
            loadListMails($("#alignWebmail").val(),$("input[name=mailbox]").val(),0,0,null,3);
        },
        function(){
            orderNormal();
        }
     );
 }
 function sortSub(){
     $('.orderSubject').toggle(
        function(){
            $(".ascOrderSub,.dscOrderFrom,.ascOrderFrom,.dscOrderDate,.ascOrderDate").hide();
            $(".dscOrderSub").show();
            loadListMails($("#alignWebmail").val(),$("input[name=mailbox]").val(),0,0,null,4);
        },
        function(){
            $(".dscOrderSub").hide();
            $(".ascOrderSub").show();
            loadListMails($("#alignWebmail").val(),$("input[name=mailbox]").val(),0,0,null,5);
        },
        function(){
            orderNormal();
        }
     );
 }
 function sortSize(){
     $('.orderSize').toggle(
        function(){
            $(".ascOrderSub,.dscOrderFrom,.ascOrderFrom,.dscOrderDate,.ascOrderDate").hide();
            $(".dscOrderSize").show();
            loadListMails($("#alignWebmail").val(),$("input[name=mailbox]").val(),0,0,null,8);
        },
        function(){
            $(".dscOrderSize").hide();
            $(".ascOrderSize").show();
            loadListMails($("#alignWebmail").val(),$("input[name=mailbox]").val(),0,0,null,9);
        },
        function(){
            orderNormal();
        }
     );
 }
 function orderNormal(){
     $(".ascOrderSub,.dscOrderFrom,.ascOrderFrom,.dscOrderDate,.ascOrderDate").hide();
     loadListMails($("#alignWebmail").val(),$("input[name=mailbox]").val(),0,0,null,6);
 }

function ucfirst(str){
    var firstLetter = str.substr(0, 1);
    return firstLetter.toUpperCase() + str.substr(1);
}
