<?php

define('SM_PATH','../../');

require_once 'DB.php';
require_once '../class/DatabaseCon.php';
require_once '../class/Events.php';
require_once(SM_PATH . 'functions/utils.php');
require_once(SM_PATH . 'functions/global.php');
require_once(SM_PATH . 'include/load_prefs.php');

$i = 0;
?>
<html>
    <head>
        <style tyle="text/css">
        .gridTitleMail{
            font-weight:bold;
            font-size:18pt;
        }
        .labelGrid{
            /*font-weight:*/
            font-size:10pt;
            font-style:italic;
            color: gray;
        }
        </style>
    </head>
<body>
    <div style="height:315px;overflow:auto">
       <form>
<?php
if($_FILES['file_up']['error'] == 0){
    if(strcmp($_FILES['file_up']['type'],'text/calendar') == 0){
        $file = '../temp/' . $_FILES["file_up"]["name"];
        if(move_uploaded_file($_FILES["file_up"]["tmp_name"],$file)){      
            $config = array( "unique_id" => "emexiswebmail.com", "filename" => $file);
            $vcalendar = new vcalendar( $config );
            $vcalendar->parse();
            while( $vevent = $vcalendar->getComponent( "vevent" )) {
                $description = $vevent->getProperty("description");
                $summary = $vevent->getProperty("summary");
                $location = $vevent->getProperty("location");
                $dtstart = $vevent->getProperty("DTSTART");
                $dtend = $vevent->getProperty("DTEND");
                $organizer = $vevent->getProperty("ORGANIZER");
                $organizer = str_replace('MAILTO:','',$organizer);
                $description = str_replace('\n','<br>',$description);
                $start =  date("Y-m-d H:i:s",gmmktime($dtstart['hour'],$dtstart['min'],$dtstart['sec'],
                        $dtstart['month'],$dtstart['day'],$dtstart['year']));

                $end =  date("Y-m-d H:i:s",gmmktime($dtend['hour'],$dtend['min'],$dtend['sec'],
                        $dtend['month'],$dtend['day'],$dtend['year']));

                while($mail = $vevent->getProperty( "ATTENDEE" )){
                    $members[] =  str_replace('MAILTO:','',$mail);
                }
                if( ($dtend['day'] - $dtstart['day']) == 1 and !isset($dtstart['hour'])
                        and !isset($dtend['hour'])){
                    $allday = 'true';
                }else{
                    $start =  date("Y-m-d H:i:s",gmmktime($dtstart['hour'],$dtstart['min'],$dtstart['sec'],
                    $dtstart['month'],$dtstart['day'],$dtstart['year']));

                    $end =  date("Y-m-d H:i:s",gmmktime($dtend['hour'],$dtend['min'],$dtend['sec'],
                    $dtend['month'],$dtend['day'],$dtend['year']));
                    $allday = 'false';
                }
                ?>
                <input type="hidden" name="event[]" value="<?php echo $i++;?>"/>
                <input type="hidden" name="summary[]" value="<?php echo $summary?>"/>
                <input type="hidden" name="start[]" value="<?php echo $start?>"/>
                <input type="hidden" name="allday[]" value="<?php echo $allday?>"/>
                <input type="hidden" name="end[]" value="<?php echo $end?>"/>
                <input type="hidden" name="location[]" value="<?php echo $location?>"/>
                <input type="hidden" name="members[]" value="<?php echo (is_array($members)?implode(',',$members):"");?>"/>
                <table width="450">
                    <tr>
                        <td coslpan="2"><?php _('Event');?></td>
                    </tr>
                    <tr>
                        <td colspan="2" class="gridTitleMail">
                        <?php echo utf8_decode($summary);?></td>
                    </tr>
                    <tr>
                        <td class="labelGrid"><?php echo utf8_decode(_('Description'));?>:</td>
                        <td><?php echo utf8_decode($description);?> </td>
                    </tr>
                    <tr>
                        <td class="labelGrid"><?php echo _('All day');?>: </td>
                        <td><?php echo $allday;?></td>
                    </tr>
                    <tr>
                        <td class="labelGrid"><?php echo _('Start');?>: </td>
                        <td><?php echo $start;?></td>
                    </tr>
                    <tr>
                        <td class="labelGrid"><?php echo _('End');?>: </td>
                        <td><?php echo $end;?></td>
                    </tr>
                    <tr>
                        <td class="labelGrid"><?php echo _('Location');?>: </td>
                        <td><?php echo utf8_decode($location);?></td>
                    </tr>                    
                    <?php
                    if(!is_null($members)){
                    ?>
                    <tr>
                        <td class="labelGrid"><?php echo _('Members');?>: </td>
                        <td style="width:400px"><?php echo implode(',',$members);?></td>
                    </tr>
                    <tr>
                        <td colspan="2"></td>
                    </tr>
                    <?php
                    }
                    ?>
              
                <?php                
                /**global $dsn_pear;
                $event = new Events($dsn_pear);
                $event->addEvent(1,$summary,$description,$location,$allday,$start,$end,
                        'false','','false',0,'diarily',$body,$owner,$members);   */
            }

            ?>
                  <tr>
                        <td></td>
                        <!-- <td><input id="insert" type="button" value="Cadastrar evento"/></td> -->
                    </tr>
                </table>
                <script src="../../js/jquery-1.4.2.min.js"></script>
                <script type='text/javascript' charset="utf-8" src='../frontend/fullcalendar/utilscalendar.js'></script>
                <script>                    
                    $(function(){
                        parent.resizeGridEvent();
                        $('#insert').click(                            
                            function(){    				

                                $('[name^=event]').each(
                                    function(index){
                                        summary = $('[name^=summary]:eq(' + index
                                            + ')').val();
                                        dataStartStr = $('[name^=start]:eq(' + index
                                            + ')').val();
                                        dataEndStr = $('[name^=end]:eq(' + index
                                            + ')').val();
                                        allday = $('[name^=allday]:eq(' + index
                                            + ')').val();
                                        local = $('[name^=location]:eq(' + index
                                            + ')').val();
                                        description = $('[name^=description]:eq(' + index
                                            + ')').val();
                                        strMembers = $('[name^=members]:eq(' + index
                                            + ')').val();
                                        frequency = '';
                                        alarm = false;
                                        alarm_time = '';
                                        repeat = false;
                                        alarm_period = ''
                                        username = 'bborges'; // window.opener.document.getElementById('username').value;

                                        dadosSend = 'action=1&summary=' + summary + '&start=' + dataStartStr + '&end=' + dataEndStr
                                            + '&allDay=' + allday + '&location=' + local + '&frequency=' + frequency + '&alarm=' + alarm + '&alarm_period=' + alarm_period
                                            + '&alarm_time=' + alarm_time + '&repeat='
                                            + repeat + '&description=' + description + '&members=' + strMembers  + '&username=' + username
                                            + '&local=' + local;

                                        $.ajax({
                                            url: 'handle_event.php',
                                            type: 'POST',
                                            data: dadosSend,
                                            dataType: 'text',
                                            success: function(dados){
                                                parent.$("#dialogImportEvent").dialog("close");
                                            }
                                        });

                                        /*insertEvent(summary,dataStartStr,dataEndStr,
                                        allday,local,false,'',false,
                                        '','',description,strMembers);*/
                                    }
                                );
                            }
                        );
                    });
                </script>
            </form>
        </div>        
        <input id="insert" type="button" value="Cadastrar evento"/>
    </body>
</html>
           <?php

        }
    }else{
        echo _('Invalid File Format');
    }
}else{
    echo "Error";
}

	
?>
