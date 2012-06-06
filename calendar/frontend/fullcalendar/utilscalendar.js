var diasBusy = new Array();
var arrayDate = new Array();

$(window).bind("resize", function(event,ui) {
    resizeCalendar();    
});

function exportIcsButton(){
    $("#export_ics").click(
        function(){
            strMembers = '';
            dataStartStr = $("[name=dateStart]").datepicker("getDate").getFullYear()
            + '-' + ($("[name=dateStart]").datepicker("getDate").getMonth() + 1)
            + '-' + $("[name=dateStart]").datepicker("getDate").getDate()
            + ' ' + $("#hourstart option:selected").text() + ':' + $("#minutesstart option:selected").text();

            dataEndStr = $("[name=dateEnd]").datepicker("getDate").getFullYear()
            + '-' + ($("[name=dateEnd]").datepicker("getDate").getMonth() + 1)
            + '-' + $("[name=dateEnd]").datepicker("getDate").getDate()
            + ' ' + $("#hourend option:selected").text() + ':' + $("#minutesend option:selected").text();

            summary = $('[name=summary]').val();
            remember = $('[name=remember]').val();
            local = $('[name=local]').val();
            
            if($('[name=all_day]').is(':checked'))
                all_day = true;
            else
                all_day = false;

            if($('[name=repeat]').is(':checked'))
                repeat = true;
            else
                repeat = false;

            if($('[name=remember]').is(':checked'))
                remember = true;
            else
                remember = false;

            local = $('[name=local]').val();
            frequency = $('[name=periodRepeat]').val();            
            description = $('#description').val();
            remember_time = $('[name=remember_time]').val();
            alarm_period = $('[name=periodAlert]').val();
            
            $('#guest_selected option').each(
                function(){                    
                    strMembers += $(this).val() + ',';
                }
            );
            dadossend = 'start=' + dataStartStr + '&end=' + dataEndStr + '&summary=' + summary
            + '&remember=' + remember + '&location=' + local + '&all_day=' + all_day + '&repeat=' +
            repeat + '&local=' + local + '&periodRepeat=' + frequency + '&description=' + description
            + '&remember_time=' + remember_time + '&alarm_period=' + alarm_period
            + '&username=' + $("#username").val() + '&members=' + strMembers
            + '&endRepeat=' + $("[name=endRepeat]").val();                   

            $.ajax({
                url: '../calendar/backend/handle_event.php',
                type: 'POST',
                data: 'action=15&' + dadossend,
                dataType: 'text',
                success: function(dados){                    
                    location.href = "../calendar/temp/" + $("#username").val() + '_temp.php';
                }
            });
        }
    );
}

function formatDayEvent(date){
    if(date.getMonth() < 10 ){
       strMonth = '0' + parseInt(date.getMonth() + 1) ;
    }else{
        strMonth = parseInt(date.getMonth() + 1);
    }
    if(date.getDate() < 10 )
        strDay = '0' + date.getDate();
    else
        strDay = date.getDate();

    dateFormat = strDay + '-' + strMonth + '-' + date.getFullYear();
    
    return dateFormat;
}

function formatString(date){
    if(date.getMinutes() < 10){
        minutes = '0' + date.getMinutes();
    }else{
        minutes = date.getMinutes();
    }

    if(date.getHours() < 10){
        hours = '0' + parseInt(date.getHours());
    }else{
        hours = date.getHours();
    }
    
    if(date.getMinutes() < 10){
        minutes = '0' + parseInt(date.getMinutes());
    }else{
        minutes = date.getMinutes();
    }

    if(date.getMonth() < 10 ){
       strMonth = '0' + parseInt(date.getMonth() + 1) ;
    }else{
        strMonth = parseInt(date.getMonth() + 1);
    }
    if(date.getDate() < 10 )
        strDay = '0' + date.getDate();
    else
        strDay = date.getDate();

    dateFormat = date.getFullYear() + '-' + strMonth + '-' + strDay  + ' ' + hours + ':' + minutes;
    return dateFormat;
}

function resizeCalendar(){
    $('#calendar').width($(window).width() - $('.left').width() - 50);
    height = $(".barcontrol").height() - 68;
    if($('#widgetCalendar').size() > 0){
        $('#widgetCalendar').fullCalendar('option', 'contentHeight',height);
    }
        
}

function selectsTimes(time){    
    str = '<select id="hour' + time + '">';
    for(hour = 0;hour < 24;hour++){
        if(hour < 10)
            strHour = '0' + hour;
        else
            strHour = hour;
        str += '<option value="' + hour + '">' + strHour + '</option>\n'
    }
    str += '</select>:';
    str += '<select id="minutes' + time + '">';
    for(minutes = 0;minutes < 60;minutes++){
        if(minutes < 10)
            strMinutes = '0' + minutes;
        else
            strMinutes = minutes;
        str += '<option value="' + minutes + '">' + strMinutes + '</option>\n'
    }
    return str;
}

function dialogCreateEvent(edit,id,start,end){                
    if($('#language').val() == 'pt_BR'){
        getLang = locale.pt_BR[0];
    }else{
        getLang = locale.en_US[0];
    }
    
    if(edit == true){
        if(end == null)
            end = start;
    }        
    
    if(arrayEvent['edit'] == 'false' && edit == true){        
        return;         
    }
    
    dialog = '<div id="dialogEvent" title="' + getLang.new_event + '">'
           +'<form name="event_form"><div>'
           + '<ul class="tabs">'
           + '<li class="activeli"><a href="#tabE1">' + getLang.general  + '</a></li>'
           + '<li><a href="#tabE2">' + getLang.attendee  + '</a></li>'
           + '</ul>'           
           + '<div class="tab_container">'
           + '<div id="tabE1" class="tab_content_event">'
           + '<table>'
           + '<tr>'
           + '<td style="width:130px">' + getLang.summary +'</td>'
           + '<td colspan=2><input type="text" size=35 name="summary"/></td>'
           + '</tr>'
           + '<tr><td>' + getLang.start + '</td>'
           + '<td><input size=10 type="text" name="dateStart"></td>'
           + '<td>' + selectsTimes('start') + '</td>'
           + '</tr>'
           + '<tr><td>' + getLang.end + '</td>'
           + '<td><input size=10 type="text" name="dateEnd"></td>'
           + '<td>' + selectsTimes('end') + '</td>'
           + '</tr>'
           + '<tr>'
           + '<td colspan=2><input type="checkbox" name="all_day"/> '
           + getLang.all_day + '</td>'           
           + '</tr>'
           + '<tr>'
           + '<td><input type="checkbox" name="repeat"/> ' + getLang.repeat + '</td>'
           + '<td><select name="periodRepeat" disabled=disabled>'
           + '<option value="daily">' + getLang.daily +'</option>'
           + '<option value="weekly">' + getLang.weekly +'</option>'
           + '<option value="monthly">' + getLang.monthly +'</option>'
           + '</select></td>'
           + '<td>' + getLang.until
           +': <input name="endRepeat" style="width:80px !important" disabled=disabled type="text"/></td>'
           + '</tr>'
           + '<tr>'
           + '<td><input type="checkbox" name="remember"/> ' + getLang.remember + '</td>'
           + '<td><input type="text" size=5 disabled="disabled" name="remember_time"/>&nbsp'
           + '<select name="periodAlert" disabled="disabled">'
           + '<option value="minutes">' + getLang.minutes +'</option>'
           + '<option value="hours">' + getLang.hours +'</option>'
           + '<option value="days">' + getLang.days +'</option>'
           + '</select></td>'
           + '</tr>'
           + '<tr>'
           + '<td>' + getLang.location + '</td>'
           + '<td colspan=2><input type="text" size=35 name="local"/></td>'
           + '</tr>'
           + '<tr>'
           + '<td colspan=3>'
           + getLang.description
           + '<br><textarea style="height:83px !important;resize:none;width:390px !important;" id="description" rows=5 cols=58/>'
           + '</textarea>'
           + '</td>'
           + '</tr>'
           + '</table>' 
           + '</div>'
           + '<div id="tabE2" class="tab_content_event">'
           + '<div style="width:100%">'
           + '<div style="width:50%;float:left">'
           + '<span class="labelDialog">' + getLang.contacts + '</span>'
           + '<br><select rows="6" cols="26" id="guestContacts" multiple="multiple" name="guestContacts"></select>'
           + '</div>'
           + '<div style="width:50%;float:left;">'
           + '<span class="labelDialog">' + getLang.groups + '</span>'
           + '<br><select style="padding-left:7px;width:217px;" multiple="multiple" id="guestGroups" name="guestGroups"></select>'
           + '</div>'
           + '</div>';
           if($.browser.msie){
                dialog += '<input title="' + getLang.typeemail
                + '" type="text" style="width:430px !important" name="text_guest" id="text_guest"/>'
           }else{
               dialog += '<input title="' + getLang.typeemail
                + '" type="text" style="width:435px !important" name="text_guest" id="text_guest"/>'
           }
           dialog += '<div style="text-align:right"><input type="button" id="addContact" class="btnDialogEvent" value="' + getLang.add + '"/><br></div>'
           + '<div style="width:100%"><br>'
           + '<span class="labelDialog">' + getLang.members + '</span>'
           + '<select name="guest_selected" id="guest_selected" multiple="multiple">'           
           + '</select>'
           + '<div style="text-align:right"><input type="button" id="removeguest" class="btnDialogEvent" value="' + getLang.remove + '"/></div>'
           + '</div>'
           + '</div>'
           //+ '<span class="labelDialog>' + getLang.members + '</span><br>'           '
           + '</div>'
           + '</div>' 
           + '<div style="width:100%"><br>'
           + '<div style="width:50%;float:left;text-align:left">'
           + '<input class="btnDialogEvent" id="print_event" type="button" value="' + getLang.print + '"/>'
           + '<input class="btnDialogEvent" id="export_ics" type="button" value="' + getLang.exports + '"/>'
           + '</div>'
           + '<div style="width:50%;float:right;text-align:right">'
           + '<input class="btnDialogEvent" onclick=$("#dialogEvent").dialog("close") type="button" value="' + getLang.close + '"/>'
           if(edit){
               dialog += '<input class="btnDialogEvent" id="editEvent" type="button" value="' + getLang.save + '"/>';
               dialog += '<input class="btnDialogEvent" id="deleteEvent" type="button" value="' + getLang.strdelete + '"/>';
           }else
               dialog += '<input class="btnDialogEvent" id="saveEvent" type="button" value="' + getLang.save + '"/>';           

           dialog +=  '</div>'
           + '</form></div>';          

    dialog += '</div>';
    
    $(dialog).dialog({
         width: '500',         
         position: 'center',
         resizable:false,
         close: function(event, ui){
            $("#widgetCalendar").fullCalendar('unselect');
            $(this).remove();
         }
    });    

    $('#deleteEvent').click(
        function(){
            deleteEvent(id);
            $("#dialogEvent").dialog("close");
        }
    );
    $('#print_event').click(
        function(){
            if($.browser.msie){
                try{
                    page = window.open('../calendar/backend/handle_event.php?action=19&'
                    + 'id=' + id + '&user=' + $("#username").val(),'_blank',"height=305,width=400,status=yes,toolbar=no,menubar=no,location=no");
                }catch(err){}
            }else{
                page = window.open('../calendar/backend/handle_event.php?action=19&'
                + 'id=' + id + '&user=' + $("#username").val(),'_blank',"height=305,width=400,status=yes,toolbar=no,menubar=no,location=no");
            }
        }
    );
    $('#editEvent').click(
        function(){
            dataStartStr = $("[name=dateStart]").datepicker("getDate").getFullYear()
            + '-' + ($("[name=dateStart]").datepicker("getDate").getMonth() + 1)
            + '-' + $("[name=dateStart]").datepicker("getDate").getDate()
            + ' ' + $("#hourstart option:selected").text() + ':' + $("#minutesstart option:selected").text();

            dataEndStr = $("[name=dateEnd]").datepicker("getDate").getFullYear()
            + '-' + ($("[name=dateEnd]").datepicker("getDate").getMonth() + 1)
            + '-' + $("[name=dateEnd]").datepicker("getDate").getDate()
            + ' ' + $("#hourend option:selected").text() + ':' + $("#minutesend option:selected").text();

            summary = $('[name=summary]').val();
            remember = $('[name=remember]').val();
            local = $('[name=local]').val();
            if($('[name=all_day]').is(':checked'))
                all_day = true;
            else
                all_day = false;

            if($('[name=repeat]').is(':checked'))
                repeat = true;
            else
                repeat = false;

            if($('[name=remember]').is(':checked'))
                remember = true;
            else
                remember = false;

            local = $('[name=local]').val();
            frequency = $('[name=periodRepeat]').val();
            description = $('#description').val();
            remember_time = $('[name=remember_time]').val();
            alarm_period = $('[name=periodAlert]').val();

            updateEvent(id,summary,dataStartStr,dataEndStr,all_day,local
            ,repeat,frequency,remember,alarm_period,remember_time,
            description);
            if($.browser.msie){
                try{
                    $('#dialogEvent').dialog('close');
                }catch(err){}
            }else
                $('#dialogEvent').dialog('close');
        }
    );

    $(".tab_content_event").hide(); //Hide all content
    $("ul.tabs .activeli").addClass("active").show(); //Activate first tab
    $("#tabE1").show(); //Show first tab content

    $("ul.tabs li").click(function() {
        $("ul.tabs li").removeClass("active"); //Remove any "active" class
        $(this).addClass("active"); //Add "active" class to selected tab
        $(".tab_content_event").hide(); //Hide all tab content
        var activeTab = $(this).find("a").attr("href"); //Find the rel attribute value to identify the active tab + content                        
        if($.browser.msie){
            if(activeTab.length > 6){
                tam = activeTab.length - 6;
                activeTab = activeTab.substr(tam);
            }
        }        
        $(activeTab).fadeIn(); //Fade in the active content
        return false;
    });

    $('#addContact').click(
        function(){
            if($('#guestContacts option:selected').size() == 0){
                str  = $('#text_guest').val();
                if(str.indexOf('@') != -1)                    
                    $("#guest_selected").append('<option>' + $('#text_guest').val() + '</option>');
                
            }            
            if($('#guestGroups option:selected').size() > 0){
                $('#guestGroups option:selected').each(
                    function(){
                        dadosSend = 'action=5&group=' + $(this).val();                        
                        $.ajax({                            
                            url: '../plugins/abook_group/handler_group.php',
                            type: 'GET',
                            data: dadosSend,
                            dataType: 'json',
                            success: function(dados) {                                
                                for(i = 0; i < dados.email.length; i++){                                                    
                                    $("#guest_selected").append('<option>' + dados.email[i] + '</option>');
                                }
                            }
                        });                        
                    }
                );
            }            
            $('#guestContacts option:selected').each(
                function(){                    
                    $("#guest_selected").append('<option value="' + $(this).val() +  '">' + $(this).val() + '</option>');
                    $(this).css('color','green')//remove();
                }
            );
        }
    );

    $('#removeguest').click(
        function(){
            $('#guest_selected option:selected').remove();
        }
    );    
    $('[name=alert]').change(
        function(){            
            if($(this).val() == 1){
                $('select[name=minutesBefore]').attr('disabled','');
            }else{                
                $('select[name=minutesBefore]').attr('disabled','disabled');                
            }
        }
    );

    $('#text_guest').keyup(
        function(){
           $("#guestContacts option,#guestGroups option").attr('selected','');
           $("#guestContacts option").show();
           $("#guestGroups option").show();
           $("#guestContacts option:not(:contains('" + $(this).val() + "'))").hide();
           $("#guestGroups option:not(:contains('" + $(this).val() + "'))").hide();
           str = $(this).val();
        }
    );
    $('[name=repeat]').change(
        function(){            
            if($(this).attr('checked')){
                $('select[name=periodRepeat],[name=endRepeat]').attr('disabled','');
            }else{                
                $('select[name=periodRepeat],[name=endRepeat]').attr('disabled','disabled');
            }            
        }
    );
    $('[name=remember]').change(
        function(){
            if($(this).attr('checked')){
                $('select[name=periodAlert]').attr('disabled','');
                $('[name=remember_time]').attr('disabled','');
            }else{
                $('select[name=periodAlert]').attr('disabled','disabled');
                $('[name=remember_time]').attr('disabled','disabled');
            }
        }
    );
    if(edit){
        if(arrayEvent['members'] != undefined){
            for(i=0; i < arrayEvent['members'].length;i++){
                $('#guest_selected').append('<option value="' +
                    arrayEvent['members'][i].emailaddr + '"> '
                    + arrayEvent['members'][i].emailaddr  + '</option>');
            }
        }        
        $('[name=summary]').val(arrayEvent['title']);
        $('[name=local]').val(arrayEvent['location']);
        $('#description').val(arrayEvent['description']);
        $('[name=remember_time]').val(arrayEvent['alarm_time']);
        
        if(arrayEvent['repeat'] == 't'){
            $('[name=repeat]').attr('checked','checked');
            $('[name=periodRepeat]').attr('disabled','');
            $('[name=periodRepeat] option[value=' +  arrayEvent['frequency']  +  ']').attr('selected','selected')
        }
        
        if(arrayEvent['allday'] == 't'){
            $('[name=all_day]').attr('checked','checked');
            $('#minutesstart,#minutesend,#hourstart,#hourend,[name=dateEnd],[name=dateStart]').attr('disabled','disabled');                
        }
        if(arrayEvent['alarm'] == 't'){
            $('[name=remember]').attr('checked','checked');
            $('[name=remember_time],[name=periodAlert]').removeAttr('disabled');
        }
        

    }    
    $(".eventColor").click(
        function(){            
            $("[name=colorEvent]").val($(this).css('background-color'));            
            $(".eventColor b").hide();
            $(this).children('b').show();            
        }
    );
    if(typeof(start) == 'string'){                      
        start = new Date(start);
    }
    if(typeof(end) == 'string')
        end = new Date(end);
    
    diaI = start.getDate();
    mesI = start.getMonth() + 1;
    anoI = start.getFullYear();
    diaF = end.getDate();
    mesF = end.getMonth() + 1;
    anoF = end.getFullYear();
    
    dataI = diaI + ' ' + mesI + ' ' +  anoI;
             
    dataF = diaF + ' ' + mesF + ' ' +  anoF;    
           
    $('#hourstart option[value=' + start.getHours() + ']').attr('selected','selected');
    $('#hourend option[value=' + end.getHours() + ']').attr('selected','selected');
    $('#minutesstart option[value=' + start.getMinutes() + ']').attr('selected','selected');
    $('#minutesend option[value=' + end.getMinutes() + ']').attr('selected','selected');

    /*
    var d1 = new Date(arrayEvent['date_repeat_end']);
    alert(d1.getDate())
    */
    

    $('[name=dateStart]').val(diaI + '/' + mesI + '/' + anoI);
    if(edit && arrayEvent['repeat'] == 't'){
        var strDate = arrayEvent['date_repeat_end'];        
        date = strDate.split(" ");
        arrayDate = date[0].split('-');
        arrayTime = date[1].split(':');
        $('[name=endRepeat]').attr('disabled','');
        //$vevent->setProperty( "rrule", array( "FREQ" => "WEEKLY", "count" => 4));
        //$('').attr('disabled','');
        $('[name=endRepeat]').val(arrayDate[2] + '/' + arrayDate[1] + '/' + arrayDate[0]);
    }else{        
        $('[name=endRepeat]').val((diaI + 1) + '/' + mesI + '/' + anoI);
    }
    $('[name=dateEnd]').val(diaF + '/' + mesF + '/' + anoF);    
    $('[name=all_day]').change(
        function(){
            if($(this).attr('checked')){
                $('#minutesstart,#minutesend,#hourstart,#hourend,[name=dateEnd],[name=dateStart]').attr('disabled','disabled');                
            }else{
                $('#minutesstart,#minutesend,#hourstart,#hourend,[name=dateEnd],[name=dateStart]').removeAttr('disabled');
            }
        }
    );

    if($("#language").val() == 'pt_BR'){
        $("[name=dateStart],[name=dateEnd],[name=endRepeat]").datepicker({
            monthNames: ['Janeiro','Fevereiro','Mar&ccedil;o','Abril','Maio',
                'Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'],
            dayNamesMin: ['D', 'S', 'T', 'Q', 'Q', 'S', 'S'],
            dateFormat:'dd/mm/yy',
            showOn: "button",
            buttonImage: "../images/calendar.gif",
            buttonImageOnly: true
        });
    }else{
        $("[name=endRepeat],[name=dateStart],[name=dateEnd]").datepicker({
            monthNames: ['January','February','March','April','Maj','Juni','Juli'
                ,'August','September','Ocktober','November','December'],
            dayNamesMin: ['S', 'M', 'T', 'W', 'T', 'F', 'S'],
            dateFormat:'dd/mm/yy',
            showOn: "button",
            buttonImage: "../images/calendar.gif",
            buttonImageOnly: true
        });
    }    
    
}

function showBubble(){
    if($('.bubble').is(':hidden')){
        $('.bubble').show();        
    }
}
var timeClose;
var timeStart;

function loadContGroupsEvent(){    
    contact = loadContacts('*');
    dadosGroups = loadGroupsJson();

    for(i = 0;i < dadosGroups.groups.length;i++){
        $("#guestGroups").append('<option value="' +
            dadosGroups.groups[i]  + '">' + dadosGroups.groups[i] +'</option>');
    }
    for(i = 0; i < contact.nome.length; i++){
        $('#guestContacts').append('<option value="' +
            contact.email[i] + '">' + contact.nome[i] +  ' &lt;' + contact.email[i] + '&gt;</option>');
    }
    if(arrayEvent['members'] != undefined){
        for(i=0; i < arrayEvent['members'].length;i++){
            $("#guestContacts option[value=" + arrayEvent['members'][i].emailaddr + "]").remove();
        }
    }
}

function changeView(month,year,day){    
    sdate = ucfirst(month) + ' '+ day + ', ' + year + ' 00:00:00';   
    var d1 = new Date(sdate);    
    
    $("#agenda").show();
    $("#list").hide();
    
    $('#widgetCalendar').fullCalendar( 'gotoDate',d1);
    $('#widgetCalendar').fullCalendar('changeView','agendaDay');        
}

function openDay(lang){    
    if($('#language').val() == 'pt_BR'){
        getLang = locale.pt_BR[0];
    }else{
        getLang = locale.en_US[0];
    }    
    $('.replyMsgIcon,.replyMsgIconEn,.forwardMsgIcon,#controlPage,#search').hide();
    $('#searchEvent,.btnImportEvent,.btnDailyCalendar,.btnWeeklyCalendar,.btnMonthlyCalendar,.btnListCalendar').show();
    $("#agenda").show();
    $("#list").hide();    
    $('.btnListCalendar').click(        
        function(){            
            $("#agenda").hide();
            $("#list").show();            
            $.ajax({
                url: '../calendar/backend/handle_event.php',
                type: 'GET',
                data: 'action=2&user=' + $("#username").val(),
                dataType: 'json',
                success: function(dados){                    
                    $("#list table tr:not(:first)").remove();
                    if(dados.length == 0){
                        $("#list table").append('<tr><td colspan=3>'
                        + getLang.withoutevents +'</td></tr>');
                    }                    
                    for(i = 0;i < dados.length;i++){                       
                        dateE = new Date(dados[i].end);
                        dateS = new Date(dados[i].start);                        
                        
                        //new Date("October 13, 1975 11:13:00")
                        strEnd = dados[i].end;
                        strStart = dados[i].start;
                        dE = strEnd.split(" ");
                        dS = strStart.split(" ");
                        hS = dS[3];
                        hE = dE[3];
                        
                        if(dados[i].allDay){
                            hour = getLang.all_day;
                        }else{
                            if(dateS.getMinutes() < 10){
                                minutesS = dateS.getMinutes() + '0';
                            }else{
                                minutesS = dateS.getMinutes();
                            }
                            if(dateE.getMinutes() < 10){
                                minutesE = dateE.getMinutes() + '0';
                            }else{
                                minutesE = dateE.getMinutes();
                            }
                            hour =  dateS.getHours() + ':' + minutesS
                            + ' - ' + dateE.getHours() + ':' + minutesE
                        }                                             

                        strEnd = dados[i].end;
                        
                        date = new Date(dados[i].start);
                        
                        strStart = dados[i].start;                        
                        dE = strEnd.split(" ");
                        dS = strStart.split(" ");                        
                        hS = dS[3];
                        hE = dE[3];                                                
                        
                        monthStart = dS[0];
                        monthEnd = dE[0];
                        
                        strDateStart = monthStart + ' ' +  dateS.getDate() + ', '
                        + dateS.getFullYear() + ' ' + dateS.getHours() + ':'
                        + dateS.getMinutes();

                        strDateEnd = monthEnd + ' ' +  dateE.getDate() + ', '
                        + dateE.getFullYear() + ' ' + dateE.getHours() + ':'
                        + dateE.getMinutes();                        
                        
                        numberMonth = parseInt(date.getMonth()) + 1;                                                
                        
                        str =
                        '<td style="width:30%">' 
                        + dateS.getDate() + '/' +  numberMonth
                        + '/' + dateS.getFullYear()
                        + '</td>'
                        + '<td><b>'
                        +  '<a href="javascript:loadEventDialog('+dados[i].id
                        +  ',\'' + strDateStart + '\''
                        +  ',\'' + strDateEnd
                        + '\''
                        + ')">' + dados[i].title + '</a></b></td>'
                        + '<td><span class="labelTime">' + hour + '</span></td>';
                        
                        $("#list table").append('<tr>'+str+'</tr>');
                    }                    
                }
            });
        }
    );
    $('.btnMonthlyCalendar').click(
        function(){
            $("#agenda").show();
            $("#list").hide();
            $('.fc-button-month').trigger('click');
        }
    );
    $('.btnWeeklyCalendar').click(
        function(){
            $("#agenda").show();
            $("#list").hide();     
            $('.fc-button-agendaWeek').trigger('click');
        }
    );
    $('.btnDailyCalendar').click(
        function(){
            $("#agenda").show();
            $("#list").hide();     
            $('.fc-button-agendaDay').trigger('click');
        }
    );
    data = $(".miniCalendar");
    month = data.datepicker("getDate").getMonth();
    year = data.datepicker("getDate").getFullYear();
    user = $("[name=owner_event]").val();
    height = $(".barcontrol").height() - 68;
    $("#calendar").show();    
    $(".left,.barcontrol div").height($(window).height() - $(".header").height() - 10);
    $(".right").hide();
    $("#widgetCalendar").empty();    
    if(lang == 'pt_BR'){        
        var date = new Date();
        var d = date.getDate();
        var m = date.getMonth();
        var y = date.getFullYear();
        try{
            $("#widgetCalendar").fullCalendar({
                timeFormat: 'H:mm',
                contentHeight: height,
                selectable: true,
                selectHelper: true,
                unselectAuto:false,
                eventClick: function(calEvent, jsEvent, view){
                    dialogCreateEvent(true,calEvent.id,calEvent.start,calEvent.end);
                    loadContGroupsEvent();
                    exportIcsButton();
                    $('#editEvent').click(
                        function(){
                            dataStartStr = $("[name=dateStart]").datepicker("getDate").getFullYear()
                            + '-' + ($("[name=dateStart]").datepicker("getDate").getMonth() + 1)
                            + '-' + $("[name=dateStart]").datepicker("getDate").getDate()
                            + ' ' + $("#hourstart option:selected").text() + ':' + $("#minutesstart option:selected").text();

                            dataEndStr = $("[name=dateEnd]").datepicker("getDate").getFullYear()
                            + '-' + ($("[name=dateEnd]").datepicker("getDate").getMonth() + 1)
                            + '-' + $("[name=dateEnd]").datepicker("getDate").getDate()
                            + ' ' + $("#hourend option:selected").text() + ':' + $("#minutesend option:selected").text();

                            summary = $('[name=summary]').val();
                            remember = $('[name=remember]').val();
                            local = $('[name=local]').val();
                            if($('[name=all_day]').is(':checked'))
                                all_day = true;
                            else
                                all_day = false;

                            if($('[name=repeat]').is(':checked'))
                                repeat = true;
                            else
                                repeat = false;

                            if($('[name=remember]').is(':checked'))
                                remember = true;
                            else
                                remember = false;

                            local = $('[name=local]').val();
                            frequency = $('[name=periodRepeat]').val();
                            description = $('#description').val();
                            remember_time = $('[name=remember_time]').val();
                            alarm_period = $('[name=periodAlert]').val();

                            updateEvent(calEvent.id,summary,dataStartStr,dataEndStr,all_day,local
                            ,repeat,frequency,remember,alarm_period,remember_time,
                            description);
                        }
                    );

                },
                eventMouseout: function(){
                    if($('.bubble').is(':visible')){
                        timeClose = setTimeout("hideBubble()",3000);
                        timeStart = undefined;
                    }
                    //timeClose = setTimeout("hideBubble()",8000);
                    clearTimeout(timeStart);
                },
                eventMouseover:function( event, jsEvent, view ) {
                    //clearTimeout(timeClose);
                    loadEvent(event.id);
                    if($('.bubble').is(':hidden')){
                        timeStart = setTimeout('showBubble()',2100);
                    }
                    $('.bubble').css({'left':jsEvent.pageX, 'top':jsEvent.pageY - 30});
                },
                eventDrop: function(event,dayDelta,minuteDelta,allDay,revertFunc) {
                    dialog = '<div title="' + getLang.send_notification +'"><div>'
                           + '</div>' + getLang.notify_users + '</div>';
                    user = $("#username").val();
                    dadosSend = 'action=16&id=' + event.id
                    $.ajax({
                        url: '../calendar/backend/handle_event.php',
                        type: 'POST',
                        data: dadosSend,
                        dataType: 'json',
                        success: function(dados){
                            if(dados.length > 0){
                                $(dialog).dialog({
                                    resizable: false,
                                    width: '350',
                                    buttons: {
                                        "Enviar": function() {
                                            updateDate(dayDelta,minuteDelta,allDay,event.id,$("#username").val(),true);
                                            $( this ).dialog( "close" );
                                        },
                                        Cancel: function() {
                                            updateDate(dayDelta,minuteDelta,allDay,event.id,$("#username").val(),'');
                                            $( this ).dialog( "close" );
                                        },
                                        "Não atualizar o evento": function(){
                                            $("#widgetCalendar").fullCalendar('refetchEvents');
                                            $( this ).dialog( "close" );
                                        }
                                    }
                                });
                            }else{
                                updateDate(dayDelta,minuteDelta,allDay,event.id,$("#username").val(),true);
                            }
                        }
                    });
                },
                eventResize: function(event,dayDelta,minuteDelta,revertFunc) {
                    dialog = '<div title="Enviar notificação"><div>'
                           + '</div>' + getLang.notify_users + '</div>';
                    user = $("#username").val();

                    dadosSend = 'action=16&id=' + event.id
                    $.ajax({
                        url: '../calendar/backend/handle_event.php',
                        type: 'POST',
                        data: dadosSend,
                        dataType: 'json',
                        success: function(dados){
                            if(dados.length > 0){
                                $(dialog).dialog({
                                    resizable: false,
                                    width: '350',
                                    buttons: {
                                        "Enviar": function() {
                                            resizeDate(dayDelta,minuteDelta,event.id,user,true);
                                            $( this ).dialog( "close" );
                                        },
                                        Cancel: function() {
                                            resizeDate(dayDelta,minuteDelta,event.id,user);
                                            $( this ).dialog( "close" );
                                        },
                                        "Não atualizar o evento": function(){
                                            $("#widgetCalendar").fullCalendar('refetchEvents');
                                            $( this ).dialog( "close" );
                                        }
                                    }
                                });
                            }else{
                                resizeDate(dayDelta,minuteDelta,event.id,user,true);
                            }
                        }
                    });
                },
                select: function(start, end, allDay) {
                    $("#dialogEvent").remove();
                    //edit,id,start,end
                    dialogCreateEvent(false,'',start,end);
                    contact = loadContacts('*');
                    dadosGroups = loadGroupsJson();
                    for(i = 0;i < dadosGroups.groups.length;i++){
                        $("#guestGroups").append('<option value="' +
                            dadosGroups.groups[i]  + '">' + dadosGroups.groups[i] +'</option>');
                    }
                    for(i = 0; i < contact.nome.length; i++){
                        $('#guestContacts').append('<option value="' +
                            contact.email[i] + '">' + contact.nome[i] +  ' &lt;' + contact.email[i] + '&gt;</option>');
                    }
                    exportIcsButton();

                    $('#editEvent').click(
                        function(){
                            dataStartStr = $("[name=dateStart]").datepicker("getDate").getFullYear()
                            + '-' + ($("[name=dateStart]").datepicker("getDate").getMonth() + 1)
                            + '-' + $("[name=dateStart]").datepicker("getDate").getDate()
                            + ' ' + $("#hourstart option:selected").text() + ':' + $("#minutesstart option:selected").text();

                            dataEndStr = $("[name=dateEnd]").datepicker("getDate").getFullYear()
                            + '-' + ($("[name=dateEnd]").datepicker("getDate").getMonth() + 1)
                            + '-' + $("[name=dateEnd]").datepicker("getDate").getDate()
                            + ' ' + $("#hourend option:selected").text() + ':' + $("#minutesend option:selected").text();

                            summary = $('[name=summary]').val();
                            remember = $('[name=remember]').val();
                            local = $('[name=local]').val();
                            if($('[name=all_day]').is(':checked'))
                                all_day = true;
                            else
                                all_day = false;

                            if($('[name=repeat]').is(':checked'))
                                repeat = true;
                            else
                                repeat = false;

                            if($('[name=remember]').is(':checked'))
                                remember = true;
                            else
                                remember = false;

                            local = $('[name=local]').val();
                            frequency = $('[name=periodRepeat]').val();
                            description = $('#description').val();

                            remember_time = $('[name=remember_time]').val();
                            alarm_period = $('[name=periodAlert]').val();

                            updateEvent(summary,dataStartStr,dataEndStr,all_day,local
                            ,repeat,frequency,remember,alarm_period,remember_time,
                            description);
                        }
                    );
                    $("#saveEvent").click(
                        function(){
                            dataStartStr = $("[name=dateStart]").datepicker("getDate").getFullYear()
                            + '-' + ($("[name=dateStart]").datepicker("getDate").getMonth() + 1)
                            + '-' + $("[name=dateStart]").datepicker("getDate").getDate()
                            + ' ' + $("#hourstart option:selected").text() + ':' + $("#minutesstart option:selected").text();

                            dataEndStr = $("[name=dateEnd]").datepicker("getDate").getFullYear()
                            + '-' + ($("[name=dateEnd]").datepicker("getDate").getMonth() + 1)
                            + '-' + $("[name=dateEnd]").datepicker("getDate").getDate()
                            + ' ' + $("#hourend option:selected").text() + ':' + $("#minutesend option:selected").text();

                            summary = $('[name=summary]').val();
                            remember = $('[name=remember]').val();
                            local = $('[name=local]').val();
                            if($('[name=all_day]').is(':checked'))
                                all_day = true;
                            else
                                all_day = false;

                            if($('[name=repeat]').is(':checked'))
                                repeat = true;
                            else
                                repeat = false;

                            if($('[name=remember]').is(':checked'))
                                remember = true;
                            else
                                remember = false;

                            local = $('[name=local]').val();
                            frequency = $('[name=periodRepeat]').val();
                            description = $('#description').val();

                            remember_time = $('[name=remember_time]').val();
                            alarm_period = $('[name=periodAlert]').val();

                            var strMembers = '';
                            if($('#guest_selected option').size() > 0){
                                $('#guest_selected option').each(
                                    function(){
                                        strMembers += $(this).val() + ',';
                                    }
                                );
                            }
                            insertEvent(summary,dataStartStr,dataEndStr,all_day,local
                            ,repeat,frequency,remember,alarm_period,remember_time,
                            description,strMembers);
                            $("#dialogEvent").dialog("close");
                        }
                    );
                },
                editable: true,
                eventSources: [
                   '../calendar/backend/handle_event.php?action=2&user=' + $("#username").val()
                ],
                allDayText: "O dia todo",
                axisFormat: 'H:mm',
                buttonText:{
                    prev:     '&nbsp;&#9668;&nbsp;',  // left triangle
                    next:     '&nbsp;&#9658;&nbsp;',  // right triangle
                    prevYear: '&nbsp;&lt;&lt;&nbsp;', // <<
                    nextYear: '&nbsp;&gt;&gt;&nbsp;', // >>
                    today:    'hoje',
                    month:    'm&ecirc;s',
                    week:     'semana',
                    day:      'dia'
                },
                columnFormat:{
                    month: 'ddd',
                    week: 'ddd d/M',
                    day: 'dddd d/M'
                },
                titleFormat:{
                    month: 'MMMM yyyy',
                    week: "MMM d[ yyyy]{ '&#8212;'[ MMM] d yyyy}",
                    day: 'dddd, d MMM , yyyy'
                },
                month: month ,
                year: year,
                header: {
                    left: "prev,next today",
                    center: "title",
                    right: "month,agendaWeek,agendaDay"
                },
                monthNamesShort: ["Jan","Fev","Mar","Abr","Maio","Jun","Jul","Ago","Set","Out","Nov","Dez"],
                dayNames: ["Domingo", "Segunda", "Ter&ccedil;a", "Quarta", "Quinta", "Sexta", "S&aacute;bado"],
                monthNames: ["Janeiro","Fevereiro","Mar&ccedil;o","Abril","Maio","Junho","Julho","Agosto","Setembro","Outubro","Novembro","Dezembro"],
                dayNamesShort: ["Dom", "Seg", "Ter", "Qua", "Qui", "Sex", "Sab"]
            });
        }catch(err){ }
    }else{
       var date = new Date();
        var d = date.getDate();
        var m = date.getMonth();
        var y = date.getFullYear();
        $("#widgetCalendar").fullCalendar({
            timeFormat: 'H:mm',
            contentHeight: height,
            selectable: true,
            selectHelper: true,
            unselectAuto:false,
            eventClick: function(calEvent, jsEvent, view){
                dialogCreateEvent(true,calEvent.id,calEvent.start,calEvent.end);
                loadContGroupsEvent();
                exportIcsButton();
                $('#editEvent').click(
                    function(){
                        dataStartStr = $("[name=dateStart]").datepicker("getDate").getFullYear()
                        + '-' + ($("[name=dateStart]").datepicker("getDate").getMonth() + 1)
                        + '-' + $("[name=dateStart]").datepicker("getDate").getDate()
                        + ' ' + $("#hourstart option:selected").text() + ':' + $("#minutesstart option:selected").text();

                        dataEndStr = $("[name=dateEnd]").datepicker("getDate").getFullYear()
                        + '-' + ($("[name=dateEnd]").datepicker("getDate").getMonth() + 1)
                        + '-' + $("[name=dateEnd]").datepicker("getDate").getDate()
                        + ' ' + $("#hourend option:selected").text() + ':' + $("#minutesend option:selected").text();

                        summary = $('[name=summary]').val();
                        remember = $('[name=remember]').val();
                        local = $('[name=local]').val();
                        if($('[name=all_day]').is(':checked'))
                            all_day = true;
                        else
                            all_day = false;

                        if($('[name=repeat]').is(':checked'))
                            repeat = true;
                        else
                            repeat = false;

                        if($('[name=remember]').is(':checked'))
                            remember = true;
                        else
                            remember = false;

                        local = $('[name=local]').val();
                        frequency = $('[name=periodRepeat]').val();
                        description = $('#description').val();
                        remember_time = $('[name=remember_time]').val();
                        alarm_period = $('[name=periodAlert]').val();

                        updateEvent(calEvent.id,summary,dataStartStr,dataEndStr,all_day,local
                        ,repeat,frequency,remember,alarm_period,remember_time,
                        description);
                    }
                );

            },
            eventMouseout: function(){
                if($('.bubble').is(':visible')){
                    timeClose = setTimeout("hideBubble()",3000);
                    timeStart = undefined;
                }
                //timeClose = setTimeout("hideBubble()",8000);
                clearTimeout(timeStart);
            },
            eventMouseover:function( event, jsEvent, view ) {
                //clearTimeout(timeClose);
                loadEvent(event.id);
                if($('.bubble').is(':hidden')){
                    timeStart = setTimeout('showBubble()',2100);
                }
                $('.bubble').css({'left':jsEvent.pageX, 'top':jsEvent.pageY - 30});
            },
            eventDrop: function(event,dayDelta,minuteDelta,allDay,revertFunc) {
                dialog = '<div title="' + getLang.send_notification +'"><div>'
                       + '</div>' + getLang.notify_users + '</div>';
                user = $("#username").val();
                dadosSend = 'action=16&id=' + event.id
                $.ajax({
                    url: '../calendar/backend/handle_event.php',
                    type: 'POST',
                    data: dadosSend,
                    dataType: 'json',
                    success: function(dados){
                        if(dados.length > 0){
                            $(dialog).dialog({
                                resizable: false,
                                width: '350',
                                buttons: {
                                    "Send": function() {
                                        updateDate(dayDelta,minuteDelta,allDay,event.id,$("#username").val(),true);
                                        $( this ).dialog( "close" );
                                    },
                                    Cancel: function() {
                                        updateDate(dayDelta,minuteDelta,allDay,event.id,$("#username").val(),'');
                                        $( this ).dialog( "close" );
                                    },
                                    "Don't update the event": function(){
                                        $("#widgetCalendar").fullCalendar('refetchEvents');
                                        $( this ).dialog( "close" );
                                    }
                                }
                            });
                        }else{
                            updateDate(dayDelta,minuteDelta,allDay,event.id,$("#username").val(),true);
                        }
                    }
                });
            },
            eventResize: function(event,dayDelta,minuteDelta,revertFunc) {
                dialog = '<div title="Enviar notificação"><div>'
                       + '</div>' + getLang.notify_users + '</div>';
                user = $("#username").val();

                dadosSend = 'action=16&id=' + event.id
                $.ajax({
                    url: '../calendar/backend/handle_event.php',
                    type: 'POST',
                    data: dadosSend,
                    dataType: 'json',
                    success: function(dados){
                        if(dados.length > 0){
                            $(dialog).dialog({
                                resizable: false,
                                width: '350',
                                buttons: {
                                    "Send": function() {
                                        resizeDate(dayDelta,minuteDelta,event.id,user,true);
                                        $( this ).dialog( "close" );
                                    },
                                    Cancel: function() {
                                        resizeDate(dayDelta,minuteDelta,event.id,user);
                                        $( this ).dialog( "close" );
                                    },
                                    "Don't update the event": function(){
                                        $("#widgetCalendar").fullCalendar('refetchEvents');
                                        $( this ).dialog( "close" );
                                    }
                                }
                            });
                        }else{
                            resizeDate(dayDelta,minuteDelta,event.id,user,true);
                        }
                    }
                });
            },
            select: function(start, end, allDay) {
                $("#dialogEvent").remove();
                //edit,id,start,end
                dialogCreateEvent(false,'',start,end);
                contact = loadContacts('*');
                dadosGroups = loadGroupsJson();
                for(i = 0;i < dadosGroups.groups.length;i++){
                    $("#guestGroups").append('<option value="' +
                        dadosGroups.groups[i]  + '">' + dadosGroups.groups[i] +'</option>');
                }
                for(i = 0; i < contact.nome.length; i++){
                    $('#guestContacts').append('<option value="' +
                        contact.email[i] + '">' + contact.nome[i] +  ' &lt;' + contact.email[i] + '&gt;</option>');
                }
                exportIcsButton();

                $('#editEvent').click(
                    function(){
                        dataStartStr = $("[name=dateStart]").datepicker("getDate").getFullYear()
                        + '-' + ($("[name=dateStart]").datepicker("getDate").getMonth() + 1)
                        + '-' + $("[name=dateStart]").datepicker("getDate").getDate()
                        + ' ' + $("#hourstart option:selected").text() + ':' + $("#minutesstart option:selected").text();

                        dataEndStr = $("[name=dateEnd]").datepicker("getDate").getFullYear()
                        + '-' + ($("[name=dateEnd]").datepicker("getDate").getMonth() + 1)
                        + '-' + $("[name=dateEnd]").datepicker("getDate").getDate()
                        + ' ' + $("#hourend option:selected").text() + ':' + $("#minutesend option:selected").text();

                        summary = $('[name=summary]').val();
                        remember = $('[name=remember]').val();
                        local = $('[name=local]').val();
                        if($('[name=all_day]').is(':checked'))
                            all_day = true;
                        else
                            all_day = false;

                        if($('[name=repeat]').is(':checked'))
                            repeat = true;
                        else
                            repeat = false;

                        if($('[name=remember]').is(':checked'))
                            remember = true;
                        else
                            remember = false;

                        local = $('[name=local]').val();
                        frequency = $('[name=periodRepeat]').val();
                        description = $('#description').val();

                        remember_time = $('[name=remember_time]').val();
                        alarm_period = $('[name=periodAlert]').val();

                        updateEvent(summary,dataStartStr,dataEndStr,all_day,local
                        ,repeat,frequency,remember,alarm_period,remember_time,
                        description);
                    }
                );
                $("#saveEvent").click(
                    function(){
                        dataStartStr = $("[name=dateStart]").datepicker("getDate").getFullYear()
                        + '-' + ($("[name=dateStart]").datepicker("getDate").getMonth() + 1)
                        + '-' + $("[name=dateStart]").datepicker("getDate").getDate()
                        + ' ' + $("#hourstart option:selected").text() + ':' + $("#minutesstart option:selected").text();

                        dataEndStr = $("[name=dateEnd]").datepicker("getDate").getFullYear()
                        + '-' + ($("[name=dateEnd]").datepicker("getDate").getMonth() + 1)
                        + '-' + $("[name=dateEnd]").datepicker("getDate").getDate()
                        + ' ' + $("#hourend option:selected").text() + ':' + $("#minutesend option:selected").text();

                        summary = $('[name=summary]').val();
                        remember = $('[name=remember]').val();
                        local = $('[name=local]').val();
                        if($('[name=all_day]').is(':checked'))
                            all_day = true;
                        else
                            all_day = false;

                        if($('[name=repeat]').is(':checked'))
                            repeat = true;
                        else
                            repeat = false;

                        if($('[name=remember]').is(':checked'))
                            remember = true;
                        else
                            remember = false;

                        local = $('[name=local]').val();
                        frequency = $('[name=periodRepeat]').val();
                        description = $('#description').val();

                        remember_time = $('[name=remember_time]').val();
                        alarm_period = $('[name=periodAlert]').val();

                        var strMembers = '';
                        if($('#guest_selected option').size() > 0){
                            $('#guest_selected option').each(
                                function(){
                                    strMembers += $(this).val() + ',';
                                }
                            );
                        }
                        insertEvent(summary,dataStartStr,dataEndStr,all_day,local
                        ,repeat,frequency,remember,alarm_period,remember_time,
                        description,strMembers);
                        $("#dialogEvent").dialog("close");
                    }
                );
            },
            editable: true,
            eventSources: [
               '../calendar/backend/handle_event.php?action=2&user=' + $("#username").val()
            ],            
            axisFormat: 'H:mm',
            buttonText:{
                prev:     '&nbsp;&#9668;&nbsp;',  // left triangle
                next:     '&nbsp;&#9658;&nbsp;',  // right triangle
                prevYear: '&nbsp;&lt;&lt;&nbsp;', // <<
                nextYear: '&nbsp;&gt;&gt;&nbsp;' // >>                
            },
            columnFormat:{
                month: 'ddd',
                week: 'ddd d/M',
                day: 'dddd d/M'
            },
            titleFormat:{
                month: 'MMMM yyyy',
                week: "MMM d[ yyyy]{ '&#8212;'[ MMM] d yyyy}",
                day: 'dddd, d MMM , yyyy'
            },
            month: month ,
            year: year,
            header: {
                left: "prev,next today",
                center: "title",
                right: "month,agendaWeek,agendaDay"
            }
        });
                
    }
    $("#widgetCalendar").fullCalendar("gotoDate", data.datepicker("getDate"));
    $("#widgetCalendar").fullCalendar("changeView","agendaDay");
    resizeCalendar();
}

function updateDate(days,minutes,allday,id,user,notify){
    if(notify == null || notify == undefined)
        notify = false;

    $('.miniCalendar').datepicker("refresh");    
    var diasBusy = new Array();

    if(notify == true){        
        str = '&notify=true';
    }else{        
        str = '';
    }        

    dadosSend = 'action=3&minutesUp=' + minutes + '&daysUp=' + days + '&alldayUp=' + allday + '&idUp=' + id
    + '&user=' + user + str;        
    $.ajax({
        url: '../calendar/backend/handle_event.php',
        type: 'POST',
        data: dadosSend,
        dataType: 'text',
        success: function(dados){            
            $("#widgetCalendar").fullCalendar('refetchEvents');
        }
    });
}

function confirmEvent(user,id,field){    
    $.ajax({
        url: '../calendar/backend/handle_event.php',
        type: 'POST',
        data: 'action=12&user=' + user + '&id=' + id,
        dataType: 'text',
        success: function(dados) {
            $(field).parent().parent().find('.close').trigger('click');
        }
    });
}

function cancelEvent(user,id,field){
    $.ajax({
        url: '../calendar/backend/handle_event.php',
        type: 'POST',
        data: 'action=13&user=' + user + '&id=' + id,
        dataType: 'text',
        success: function(dados) {
            $(field).parent().parent().find('.close').trigger('click');
        }
    });
}

function verifyInvites(user){
    $.ajax({
        url: '../calendar/backend/handle_event.php',
        type: 'POST',
        data: 'action=11&user=' + user,
        dataType: 'json',
        success: function(dados) {            
            for(i = 0;i < dados.length;i++){
                inviteNotification(dados[i].title,dados[i].start,dados[i].end,dados[i].id,user);               
            }
        }
    });
}

function eventNotification(title,date_start,date_end){    
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
        + '<div id="deleteM"></div>'
        + '<img src="../images/calendar.png" alt="" />'
        + '<h3>' + title + '</h3>'
        + '<p style="cursor:pointer !important">' + date_start + ' - ' + date_end
        + '</p>'
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

    }
}

function verifyAlert(user){
    $.ajax({
        url: '../calendar/backend/handle_event.php',
        type: 'POST',
        data: 'action=9&user=' + user,
        dataType: 'json',
        success: function(dados) {            
            for(i = 0;i < dados.length;i++){
                eventNotification(dados[i].title,dados[i].date_start,dados[i].date_end);                
                notifiedEvent(dados[i].eventid);
            }
        }
    });    
}

function resizeDate(days,minutes,id,user,notify){
    if(notify == undefined || notify == null){
        
    }
    if(notify == null || notify == undefined)
        notify = false;

    $('.miniCalendar').datepicker("refresh");
    var diasBusy = new Array();

    if(notify == true){
        str = '&notify=true';
    }else{
        str = '';
    }
    
    dadosSend = 'action=4&minutesUp=' + minutes + '&daysUp=' + days + '&idUp=' + id + '&user=' + user + str;

    $.ajax({
        url: '../calendar/backend/handle_event.php',
        type: 'POST',
        data: dadosSend,
        dataType: 'text',
        success: function(dados){
            $("#widgetCalendar").fullCalendar('refetchEvents');
        }
    });
 
}

var arrayEvent = new Array();


function loadEventDialog(id,dateStart,dateEnd){

    dateStart = new Date(dateStart);
    dateEnd = new Date(dateEnd);

    if($('#language').val() == 'pt_BR'){
        getLang = locale.pt_BR[0];
    }else{
        getLang = locale.en_US[0];
    }
    user = $("[name=owner_event]").val();
    dadosSend = 'action=6&user=' + user + '&id=' + id;
    $.ajax({
        url: '../calendar/backend/handle_event.php',
        type: 'POST',
        data: dadosSend,
        dataType: 'json',
        success: function(jsonEvent){
            arrayEvent['alarm'] = jsonEvent[0].alarm;
            arrayEvent['date_repeat_end'] = jsonEvent[0].date_repeat_end;
            arrayEvent['alarm_time'] = jsonEvent[0].alarm_time;
            arrayEvent['alarm_period'] = jsonEvent[0].alarm_period;
            if(jsonEvent[0].title == undefined){
                arrayEvent['title'] = jsonEvent[0].summary;
            }else{
                arrayEvent['title'] = jsonEvent[0].title;
            }
            if(jsonEvent[0].description == undefined)
                arrayEvent['description'] = "";
            else
                arrayEvent['description'] = jsonEvent[0].description;
            arrayEvent['start'] = jsonEvent[0].datestartor;
            arrayEvent['end'] = jsonEvent[0].dateendor;
            arrayEvent['owner'] = jsonEvent[0].owner_event;
            arrayEvent['location'] = jsonEvent[0].location;
            arrayEvent['frequency'] = jsonEvent[0].frequency;
            arrayEvent['repeat'] = jsonEvent[0].repeat;
            arrayEvent['allday'] = jsonEvent[0].allday;
            arrayEvent['alarm'] = jsonEvent[0].alarm;
            arrayEvent['alarm_time'] = jsonEvent[0].alarm_time;
            arrayEvent['alarm_period'] = jsonEvent[0].alarm_period;
            arrayEvent['members'] = jsonEvent[0].members;
            
            dialogCreateEvent(true,id,dateStart,dateEnd);
            loadContGroupsEvent();
        }
    });

}

function loadEvent(id){
    
    if($('#language').val() == 'pt_BR'){
        getLang = locale.pt_BR[0];
    }else{
        getLang = locale.en_US[0];
    }
    user = $("[name=owner_event]").val();
    dadosSend = 'action=6&user=' + user + '&id=' + id;
    $.ajax({
        url: '../calendar/backend/handle_event.php',
        type: 'POST',
        data: dadosSend,
        dataType: 'json',
        success: function(jsonEvent){            
            arrayEvent['alarm'] = jsonEvent[0].alarm;
            arrayEvent['date_repeat_end'] = jsonEvent[0].date_repeat_end;
            arrayEvent['alarm_time'] = jsonEvent[0].alarm_time;
            arrayEvent['alarm_period'] = jsonEvent[0].alarm_period;
            if(jsonEvent[0].title == undefined){
                arrayEvent['title'] = jsonEvent[0].summary;
            }else{
                arrayEvent['title'] = jsonEvent[0].title;
            }
            if(jsonEvent[0].description == undefined)
                arrayEvent['description'] = "";
            else
                arrayEvent['description'] = jsonEvent[0].description;
            arrayEvent['start'] = jsonEvent[0].datestartor;
            arrayEvent['end'] = jsonEvent[0].dateendor;
            arrayEvent['owner'] = jsonEvent[0].owner_event;
            arrayEvent['location'] = jsonEvent[0].location;
            arrayEvent['frequency'] = jsonEvent[0].frequency;
            arrayEvent['repeat'] = jsonEvent[0].repeat;
            arrayEvent['allday'] = jsonEvent[0].allday;
            arrayEvent['alarm'] = jsonEvent[0].alarm;
            arrayEvent['alarm_time'] = jsonEvent[0].alarm_time;
            arrayEvent['alarm_period'] = jsonEvent[0].alarm_period;
            arrayEvent['members'] = jsonEvent[0].members;            
        }
    });
}

function EditEvent(id){
    dialogCreateEvent(true);
    $('.bubble').hide();
}
function deleteEvent(id){    
    dialog = '<div title="' + getLang.send_notification +'"><div>'
           + '</div>' + getLang.notify_users + '</div>';
    user = $("#username").val();
    dadosSend = 'action=16&id=' + id
    $.ajax({
        url: '../calendar/backend/handle_event.php',
        type: 'POST',
        data: dadosSend,
        dataType: 'json',
        success: function(dados){            
            if(dados.length > 0){
                if($('#language').val() == 'en_US'){
                    $(dialog).dialog({
                        resizable: false,
                        width: '350',
                        buttons: {
                            "Send": function() {
                                dadosSend = 'action=7&notify=true&id=' + id
                                    + '&username=' + $("#username").val();                                
                                $.ajax({
                                    url: '../calendar/backend/handle_event.php',
                                    type: 'POST',
                                    data: dadosSend,
                                    dataType: 'text',
                                    success: function(dados) {                                        
                                        $("#widgetCalendar").fullCalendar('refetchEvents');
                                    }
                                });
                                $( this ).dialog( "close" );
                            },
                            "Don't send": function() {
                                dadosSend = 'action=7&notify=false&id=' + id;
                                $.ajax({
                                    url: '../calendar/backend/handle_event.php',
                                    type: 'POST',
                                    data: dadosSend,
                                    dataType: 'text',
                                    success: function(dados) {
                                        $("#widgetCalendar").fullCalendar('refetchEvents');
                                    }
                                });
                                $( this ).dialog( "close" );
                            },
                            "Don't update the event": function(){
                                $("#widgetCalendar").fullCalendar('refetchEvents');
                                $( this ).dialog( "close" );
                            }
                        }
                    });
                }else{
                    $(dialog).dialog({
                        resizable: false,
                        width: '350',
                        buttons: {
                            "Enviar": function() {
                                dadosSend = 'action=7&notify=true&id=' + id
                                    + '&username=' + $("#username").val();
                                $.ajax({
                                    url: '../calendar/backend/handle_event.php',
                                    type: 'POST',
                                    data: dadosSend,
                                    dataType: 'text',
                                    success: function(dados) {                                        
                                        $("#widgetCalendar").fullCalendar('refetchEvents');
                                    }
                                });
                                $( this ).dialog( "close" );
                            },
                            "Não enviar": function() {
                                dadosSend = 'action=7&notify=false&id=' + id;
                                $.ajax({
                                    url: '../calendar/backend/handle_event.php',
                                    type: 'POST',
                                    data: dadosSend,
                                    dataType: 'text',
                                    success: function(dados) {
                                        $("#widgetCalendar").fullCalendar('refetchEvents');
                                    }
                                });
                                $( this ).dialog( "close" );
                            },
                            "Não atualizar o evento": function(){
                                $("#widgetCalendar").fullCalendar('refetchEvents');
                                $( this ).dialog( "close" );
                            }
                        }
                    });
                }
            }else{
                dadosSend = 'action=7&id=' + id;
                $.ajax({
                    url: '../calendar/backend/handle_event.php',
                    type: 'POST',
                    data: dadosSend,
                    dataType: 'text',
                    success: function(dados) {
                        $("#widgetCalendar").fullCalendar('refetchEvents');
                    }
                });
                $( this ).dialog( "close" );
            }
        }
    });
    
}

/*insertEvent(summary,dataStartStr,dataEndStr,all_day,local
                        ,repeat,frequency,remember,alarm_period,remember_time,
                        description);*/
function updateEvent(id,summary,dataStartStr,dataEndStr,allday,local,repeat,frequency,alarm,alarm_period,alarm_time,description){
    var strMembers = '';
    if($('#guest_selected option').size() > 0){
        $('#guest_selected option').each(
            function(){
                strMembers += $(this).val() + ',';
            }
        );
    }
    
    dadosSend = 'id=' + id +'&action=8&summary=' + summary + '&start=' + dataStartStr + '&end=' + dataEndStr
    + '&allDay=' + allday + '&location=' + local + '&frequency=' + frequency + '&alarm=' + alarm + '&alarm_period=' + alarm_period
    + '&alarm_time=' + alarm_time + '&repeat='
    + repeat + '&description=' + description + '&members=' + strMembers  + '&username=' + $("#username").val()
    + '&local=' + local + '&endRepeat=' + $("[name=endRepeat]").val();

    $.ajax({
        url: '../calendar/backend/handle_event.php',
        type: 'POST',
        data: dadosSend,
        dataType: 'text',
        success: function(dados){                        
            $("#widgetCalendar").fullCalendar('refetchEvents');
        }
    });
}

function insertEvent(summary,dataStartStr,dataEndStr,allday,local,repeat,frequency,alarm,alarm_period,alarm_time,description,strMembers){    
    dadosSend = 'action=1&summary=' + summary + '&start=' + dataStartStr + '&end=' + dataEndStr
    + '&allDay=' + allday + '&location=' + local + '&frequency=' + frequency + '&alarm=' + alarm + '&alarm_period=' + alarm_period
    + '&alarm_time=' + alarm_time + '&repeat='
    + repeat + '&description=' + description + '&members=' + strMembers  + '&username=' + $("#username").val()
    + '&local=' + local + '&endRepeat=' + $("[name=endRepeat]").val();    
    $.ajax({
        url: '../calendar/backend/handle_event.php',
        type: 'POST',
        data: dadosSend,
        dataType: 'text',
        success: function(dados){            
            $("#widgetCalendar").fullCalendar('refetchEvents');
        }
    });
}
 

function hideBubble(){    
    $(".bubble" ).hide();    
    clearTimeout(timeStart);    
}

//
function updateEventOld(){
    date = $("[name=dateStart]").val();    
    data = date.split('/');
    dateFormat = data[2] + '-';    
    if(data[1] < 10)
        dateFormat += '0' + data[1] + '-';
    else
        dateFormat += data[1] + '-';

    if(data[0] < 10)
        dateFormat += '0' + data[0];
    else
        dateFormat += data[0];
    
    dateFormatSt = dateFormat + ' ' + $("[name=hourStart]").val();

    date = $("[name=dateEnd]").val();
    
    data = date.split('/');
    dateFormat = data[2] + '-';
    
    if(data[1] < 10)
        dateFormat += '0' + data[1] + '-';
    else
        dateFormat += data[1] + '-';

    if(data[0] < 10)
        dateFormat += '0' + data[0];
    else
        dateFormat += data[0];

    dateFormatEn = dateFormat + ' ' + $("[name=hourEnd]").val();    

    user = $("[name=owner_event]").val();
    alerta = $("[name=alert]:checked").val();
    minutes = $("[name=minutesBefore]").val();    
    color = $("[name=colorEvent]").val();
    text = $("#descriptionEvent").val();
    title = $("#titleEvent").val();
    id = $("#eventID").val();
    var members = new Array();
    $('#listIn option').each(
        function(){
           members.push($(this).val());
        }
    );

    dadosSend = 'action=8&id=' + id + '&text=' + text
    + '&minutes=' + minutes + '&color=' + color
    + '&title=' + title + '&alerta=' + alerta
    + '&date_end=' + dateFormatEn
    + '&date_start=' + dateFormatSt
    + '&members=' + members;
    
    $.ajax({
        url: '../calendar/backend/handle_event.php',
        type: 'POST',
        data: dadosSend,
        dataType: 'text',
        success: function(dados) {
            $(".done").show();
            setTimeout("$('.done').hide()",5000);
            $("#widgetCalendar").fullCalendar('refetchEvents');            
        }
    });
        
}
function newEventMenu(){
    $('.ui-datepicker-current-day').trigger('click');
    dialogCreateEvent(false,'',new Date(),new Date());
    loadContGroupsEvent();
}

function dialogImportEvent(){    
    $("#dialogImportEvent").dialog({
         width: '500',
         height:'148',
         resizable: 'true',
         position: 'center',
         close: function(event, ui){
            $(this).dialog("close");
         }
    });    
}

function resizeGridEvent(){  
    $("#dialogImportEvent").dialog("option", "width", 600);
    $("#dialogImportEvent").dialog("option", "height", 400);
    $("#iframe_event").height('360');
    $("#iframe_event").width('588');
}

$('.btnImportEvent').click(
    function(){
        dialogImportEvent();
        $('#iframe_event').attr('src','../calendar/backend/form_import.php');
        $("#iframe_event").height('108');
        $("#iframe_event").width('450');
     }
);


function notifiedEvent(id){    
    $.ajax({
        url: '../calendar/backend/handle_event.php',
        type: 'POST',
        data: 'action=10&id=' + id + '&mail=' + $("#email").val()
        /*,
        success: function(dados) {
            alert(dados);
        }*/
    });
}

function inputSearchEvent(){
    $(".inputSearchEvent").click(
        function(){
            $(this).val('');
        }
    );
    $('.inputSearchEvent').keyup(
        function(e){
            if(e.keyCode == 13){                
                user = $("#username").val();
                $.ajax({
                    url: '../calendar/backend/handle_event.php',
                    type: 'POST',
                    dataType: 'json',
                    data: 'action=17&str_search=' + $('.inputSearchEvent').val()
                        + '&user=' + user,
                    success: function(dados){                        
                        if($('#language').val() == 'pt_BR'){
                            getLang = locale.pt_BR[0];
                        }else{
                            getLang = locale.en_US[0];
                        }                        
                        month = new Array();
                        month[1] = 'January';
                        month[2] = 'February';
                        month[3] = 'March';
                        month[4] = 'April';
                        month[5] = 'May';
                        month[6] = 'July';
                        month[7] = 'June';
                        month[8] = 'August';
                        month[9] = 'September';
                        month[10] = 'October';
                        month[11] = 'November';
                        month[12] = 'December';
                        
                        $("#agenda").hide();
                        $("#list").show();
                        $("#list table tr:not(:first)").remove();                        
                        for(i=0;i < dados.length; i++){
                            strDataI = dados[i].time_ini;
                            strDataF = dados[i].time_end;                                                    
                            vect = strDataI.split(" ");
                            vect1 = strDataF.split(" ");
                            
                            dateIni = vect[0].split('-');
                            dateFim = vect1[0].split('-');                                                        

                            strDateParamStart = month[dateIni[1]]
                            + ' ' + dateIni[2]
                            + ', ' + dateIni[0] + ' ' + vect[1];

                            strDateParamEnd = month[dateFim[1]]
                            + ' ' + dateFim[2]
                            + ', ' + dateFim[0] + ' ' + vect1[1];                                                        
                            
                            str = '<td>' +dateIni[2]+'/'+dateIni[1]+'/'+dateIni[0] + '</td>'
                            + '<td><b><a href="javascript:loadEventDialog('                            
                            + dados[i].eventid + ',\''
                            + strDateParamStart + '\',\'' + strDateParamEnd + '\')'
                            + '">'
                            + dados[i].summary + '</a></b></td>'
                            + '<td><span class="labelTime">' + vect[1]
                            + '-' + vect1[1] + '</span></td>';
    
                            $("#list table").append('<tr>'
                            + str +'</tr>');                            
                        }
                        
                        if(dados.length == 0){
                            $("#list table").append('<tr><td colspan=3>'
                            + getLang.no_results
                            + '</td></tr>');
                        }
                    }
                });
            }
        }
    );
}
