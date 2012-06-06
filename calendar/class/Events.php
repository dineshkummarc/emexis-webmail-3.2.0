<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Events
 *
 * @author bruno
 */

define('SM_PATH','../../');
require_once(SM_PATH . 'functions/global.php');
require_once(SM_PATH . 'functions/utils.php');
require_once('DatabaseCon.php');
require_once 'iCalcreator.class.php';
require_once(SM_PATH . 'calendar/backend/mail_translate.php');

class Events extends DatabaseCon{
    
    public $ics;
    private $id;
    private $summary;
    private $description;
    private $location;
    private $allday;
    private $start;
    private $end;
    private $repeat;
    private $frequency;
    private $alarm;
    private $alarm_time;
    private $alarm_period;
    private $owner;
    private $members;
    private $endRepeat;
    
    public function generateIcs($vetor, $export = false){
                        
        $v = new vcalendar( array( 'unique_id' => 'emexiswebmail' ));
        $e = & $v->newComponent( 'vevent' );        
        $e->setProperty('summary',utf8_decode($vetor['summary']));
        $e->setProperty('description',utf8_decode($vetor['description']));
        $e->setProperty( 'categories', 'MEETING' );
        $e->setProperty('dtstart',
            date("Y",strtotime($vetor['start'])),
            date("n",strtotime($vetor['start'])),
            date("j",strtotime($vetor['start'])),
            date("G",strtotime($vetor['start'])),
            date("i",strtotime($vetor['start'])),
            date("s",strtotime($vetor['start']))
        );
        $e->setProperty('dtend',
            date("Y",strtotime($vetor['end'])),
            date("n",strtotime($vetor['end'])),
            date("j",strtotime($vetor['end'])),
            date("G",strtotime($vetor['end'])),
            date("i",strtotime($vetor['end'])),
            date("s",strtotime($vetor['end']))
        );

        if(strcmp($vetor['status'],'cancel') == 0){
            $e->setProperty('status','CANCELLED');
        }
        
        if($vetor['alarm'] == 'true'){            
            $a2 = & $e->newComponent('valarm');            
            if($vetor['alarm_period'] == 'minutes'){
                $m = $vetor['alarm_time'];
                $strTrigger = "-PT" . $m . 'M';
            }
            if($vetor['alarm_period'] == 'hours'){
                $h = $vetor['alarm_time'];
                $strTrigger = "-P0DT" . $h . "H0M0S";                
            }
            if( $vetor['alarm_period'] == 'days'){
                $days = $vetor['alarm_time'];
                $strTrigger = "-P" . $days . "DT0H0M0S";
            }            
            $a2->setProperty('ACTION', 'DISPLAY');
            $a2->setProperty('trigger',$strTrigger);
        }
        if($vetor['repeat'] == 'true'){
            $frequence =  strtoupper($vetor['periodRepeat']);
            $arrayVector = explode("/",$vetor['endRepeat']);
            $dia = $arrayVector[0];
            $mes = $arrayVector[1];
            $ano = $arrayVector[2];
            $date = $ano . $mes . $dia . 'T000000Z';                        
            $e->setProperty( "rrule",
                    array( "FREQ" => "$frequence",
                        "UNTIL" => $date));
        }        

        $e->setProperty('location',utf8_decode($vetor['location']));        
        if(is_array($vetor['members']) and count($vetor['members']) > 0){
            foreach($vetor['members'] as $m){                
                $e->setProperty('attendee',"$m");
            }
        }
        
        if($export){
            $name = $vetor['username'];
            if(file_exists("../temp/{$name}_temp.php"))
                unlink("../temp/{$name}_temp.php");
            $config = array("ics" => "", "filename" => "../temp/{$name}_temp.php");
            $v->setConfig($config);
            $content = '<?php
              define(SM_PATH,"../../");
              require_once(SM_PATH . "include/validate.php");
              if($_SESSION["username"] != "' . $name . '")
                die("Fail, not permission to access this file");
              header("Content-Type: text/x-vCalendar");
              header("Content-Disposition: inline; filename=' . $name . '.ics");
            ';
            $content .= "?>";
            $content .=  $v->createCalendar();
            $handle = fopen("../temp/{$name}_temp.php", "w");
            fwrite($handle, $content);
            fclose($handle);
            
        }elseif(strcmp($vetor['status'],'cancel') == 0){
            $name = $vetor['username'];
            if(file_exists("../temp/{$name}_temp.ics"))
                unlink("../temp/{$name}_temp.ics");
            $config = array("ics" => "", "filename" => "../temp/{$name}_temp.ics");            
            $v->setConfig($config);
            $v->saveCalendar();
        }else{
            
            if(file_exists("../temp/{$name}_temp.ics"))
                unlink("../temp/{$name}_temp.ics");
            $config = array("ics" => "", "filename" => "../temp/{$name}_temp.ics");
            $v->setConfig($config);
            return $v->createCalendar();
        }
    }
    
    public function addEvent($id,$summary,$description,$location,$allday,$start,$end,$repeat,
        $frequency,$alarm,$alarm_time,$alarm_period,$ics,$owner,$members,$uid,$endRepeat){
        $arg = func_get_args();
        
        $vetor['repeat'] = $repeat;
        $vetor['periodRepeat'] = $frequency;
        $vetor['endRepeat'] = $endRepeat;
        $vetor['summary'] = $summary;
        $vetor['description'] = $description;
        $vetor['start'] = $start;
        $vetor['end'] = $end;
        $vetor['location'] = $location;
        $vetor['members'] = $arg[15];
        $vetor['alarm'] = $alarm;
        $vetor['alarm_time'] = $alarm_time;
        $vetor['alarm_period'] = $alarm_period;		

        $this->id  = $id;
        $this->summary = $summary;
        $this->description = $description;
        $this->location = $location;
        $this->allday = $allday;
        $this->start = $start;
        $this->end = $end;
        $this->repeat = $repeat;
        $this->frequency = $frequency;
        $this->alarm = $alarm;
        $this->alarm_time = $alarm_time;
        $this->alarm_period = $alarm_period;
        $this->owner = $owner;
        $this->members = $members;
        $this->endRepeat = $endRepeat;

        $ics = $this->generateIcs($vetor);
        
        if($allday == 'true'){
            $s = explode(' ',$start);
            $e = explode(' ',$end);            
            $start = $s[0] . ' ' . '00:00';
            $arrEnd = explode('-',$e[0]);
            $end = $arrEnd[0] . '-' . $arrEnd[1] . '-' . ($arrEnd[2]) . ' 00:00';            
        }
        
        if(empty($this->endRepeat))
            $this->endRepeat = $start;
        else{
            $arrRepeat = explode('/',$this->endRepeat);
            $this->endRepeat = $arrRepeat[2] . '-' . $arrRepeat[1]
            . '-' . ($arrRepeat[0]) . ' 00:00';
        }

        if(!empty($uid)){
            $this->sql = "select count(*) from calendars_events where uid = '$uid'";
            $res = $this->db->getAll($this->sql,array(),DB_FETCHMODE_ASSOC);
        }
        
        if($res[0]['count'] == 0 || empty($uid)){
            $sql = "insert into calendars_events (calid,summary,description,location,allday,time_ini,time_end,repeat,frequency,alarm,
                    alarm_time, alarm_period,ics,owner_event,uid,date_repeat_end)
            values ($id,'$summary','$description','$location','$allday','$start','$end','$repeat','$frequency','$alarm',
                    $alarm_time,'$alarm_period','$ics','$owner','$uid','$this->endRepeat')";
            $res = $this->db->query($sql);            
            if(is_array($arg[14])){
                foreach($arg[14] as $m){
                    if(!empty($m)){
                        $this->addMembersEvent($m);
                        $this->mailNotification($m);
                    }
                }
            }
            return 1; //Inserido
        }else{            
            $sql = "update calendars_events set "
            . "time_ini = '$start', time_end = '$end',"
            . "summary = '$summary', description = '$description',"
            . "location = '$location', allday = '$allday',"
            . "repeat = '$repeat', frequency = '$frequency',"
            . "alarm = '$alarm', alarm_time = '$alarm_time',"
            . "alarm_period = '$alarm_period', owner_event = '$owner'"
            . "where uid = '$uid'";
            $res = $this->db->query($sql);
            return 2; //Atualizado
        }

    }
    
    public function addMembersEvent($member,$id = null){        
        if(is_null($id))
            $this->sql = "insert into calendars_members (emailaddr,eventid) values('$member',(select last_value from calendars_events_eventid_seq))";
        else
            $this->sql = "insert into calendars_members (emailaddr,eventid) values('$member',$id)";
        $this->db->query($this->sql);
    }

    /*
     * Esse método atualiza o perído do evento quando o mesmo é arrastado pelo calendário
     */
    public function updateEvent($minutes,$day,$id,$allday,$user,$notify= false){
        $this->sql = sprintf("update calendars_events set
        is_notified = false,
        time_ini = time_ini + interval '%d minute' + interval '%d day',
        time_end = time_end + interval '%d minute' + interval '%d day',allday = '%s'
        where owner_event = '%s' and eventid = %d",$minutes,$day,$minutes,$day,$allday,$user,$id);
        
        $res = $this->db->query($this->sql);
        if($notify){            
            $evento = $this->selectEvent($id, $user, $json);
            $this->start = $evento[0]["datestartor"];
            $this->end = $evento[0]["dateendor"];
            $this->description =  $evento[0]["description"];
            $this->location = $evento[0]['location'];
            $this->allday = $evento[0]['allday'];
            $this->summary = $evento[0]['title'];
            $this->summary = $evento[0]['title'];
            $this->members =  $this->selectMembers($id,false);
            if(count($this->members) > 0){
                foreach($this->members as $m){
                    $this->mailNotification($m['emailaddr'],true);
                }
            }
        }
    }

    public function resizeEvent($minutes,$day,$id,$username,$notify= false){
        $this->sql = sprintf("update calendars_events set
        is_notified = false,
        time_end = time_end + interval '%d minute' + interval '%d day'
        where owner_event = '%s' and eventid = %d",$minutes,$day,$username,$id);        
        $res = $this->db->query($this->sql);
        if($notify){
            $evento = $this->selectEvent($id, $user, $json);
            $this->start = $evento[0]["datestartor"];
            $this->end = $evento[0]["dateendor"];
            $this->description =  $evento[0]["description"];
            $this->location = $evento[0]['location'];
            $this->allday = $evento[0]['allday'];
            $this->summary = $evento[0]['title'];
            $this->summary = $evento[0]['title'];
            $this->members =  $this->selectMembers($id,false);
            if(count($this->members) > 0){
                foreach($this->members as $m){
                    $this->mailNotification($m['emailaddr'],true);
                }
            }
        }
    }
    
    public function selectEvent($id,$user,$json=false){
   
        $this->sql = "select frequency,repeat,date_repeat_end,alarm_time,
                alarm_period,allday,alarm,description,frequency,location,summary
                as title,to_char(time_ini,'Month DD, YYYY HH24:MI:SS')
                as start,time_end as dateEndOr, time_ini as dateStartOr,
                to_char(time_end,'Month DD, YYYY HH24:MI:SS') as end
                from calendars_events where eventid = $id";

        
        $res = $this->db->getAll($this->sql,array(),DB_FETCHMODE_ASSOC);
        
        $this->sql = "select emailaddr from
                calendars_members where eventid = $id";

        $res2 = $this->db->getAll($this->sql,array(),DB_FETCHMODE_ASSOC);
        
        if(strrpos($res2[0]["emailaddr"],'Warning: implode()') == 1)
            $res[0]['members'] = array();
        else
            $res[0]['members'] = $res2;
            
                        
        if(empty($res)){            
        }else{
            $res[0]['edit'] = "true";            
            if($json)
                echo json_encode($res);
            else
                return $res;
        }    
    }
    public function notified($id,$mail){
        $this->db->query("update calendars_events set is_notified = true where eventid = $id");
        $this->mailAlertNotification($id,$mail);        
    }

    public function exportIcs($id){                
        $v = new vcalendar( array( 'unique_id' => 'emexiswebmail' ));                                   
        $e = & $v->newComponent( 'vevent' );           // initiate a new EVENT
        
        $e->setProperty('summary',utf8_decode($vetor[0]['title']));
        $e->setProperty('description',utf8_decode($vetor[0]['description']));                        
        $e->setProperty( 'categories', 'FAMILY' );                   // catagorize        
        $e->setProperty('dtstart'
           ,date("Y",strtotime($vetor[0]['datestartor'])),
            date("n",strtotime($vetor[0]['datestartor'])),
            date("j",strtotime($vetor[0]['datestartor'])),
            date("G",strtotime($vetor[0]['datestartor'])),
            date("i",strtotime($vetor[0]['datestartor'])),
            date("s",strtotime($vetor[0]['datestartor']))
        );  
        
        $e->setProperty('dtend'
           ,date("Y",strtotime($vetor[0]['dateendor'])),
            date("n",strtotime($vetor[0]['dateendor'])),
            date("j",strtotime($vetor[0]['dateendor'])),
            date("G",strtotime($vetor[0]['dateendor'])),
            date("i",strtotime($vetor[0]['dateendor'])),
            date("s",strtotime($vetor[0]['dateendor']))
        );
        
        $e->setProperty('location',utf8_decode($vetor[0]['location']));
        $config = array( "ics" => "", "filename" => "../temp/calendar.ics" );
        $v->setConfig($config); // set output directory and file name $v->saveCalendar();]        
        return $v->createCalendar();        
    
    }

    public function returnIcsDataBase($id = false){        
        if(!$id)
            $this->sql = 'select ics from calendars_events where eventid = (select last_value from calendars_events_eventid_seq)';
        
        $res = $this->db->getAll($this->sql,array(),DB_FETCHMODE_ASSOC);
        return $res[0]['ics'];
    }

    public function createIcsTemp($strIcs,$username){
        $file = '../temp/' . $username . '_temp.ics';
        if(file_exists($file)){
            if(@unlink($file)){
                $handle = fopen($file, 'w+');
                fwrite($handle,$strIcs);
                fclose($handle);
                return true;
            }else{                
                return false;
            }
        }else{
            $handle = fopen($file, 'w+');
            fwrite($handle,$strIcs);
            fclose($handle);
            return true;
        }

    }

    public function mailAlertNotification($id,$mail){
        global $smtpServerAddress,$smtpPort,$username,
                $squirrelmail_default_language;

        include_once('Mail.php');
        include_once('Mail/mime.php');

        bindtextdomain('squirrelmail', SM_PATH . 'locale');
        textdomain('squirrelmail');

        global $username,$words;
        $lang = strtolower($squirrelmail_default_language);

        $event = $this->selectEvent($id,$username,false);        
        $html = "<html>
                <body>"
                . '<table>'
                . '<tr>'
                . '<td colspan=2><b><span style="font-size:16pt">'
                . utf8_decode($event[0]['title'])
                . ' ' . $username .  ' </span></b></td>'
                . '</tr>'
                . '<tr>'
                . '<td style=" font-family: Arial,Sans-serif;  color: rgb(136, 136, 136); white-space: nowrap;" valign="top">'
                . utf8_decode($words[$lang]['start'])
                . '</td><td>' . $event[0]['start'] . '</td>'
                . '</tr>'
                . '<tr>'
                . '<td style=" font-family: Arial,Sans-serif; color: rgb(136, 136, 136); white-space: nowrap;" valign="top">'
                . utf8_decode($words[$lang]['end']) . ': </td>'
                . '<td>' . $event[0]['end'] . '</td>'
                . '</tr>'
                . '<tr>'
                . '<td style="font-family: Arial,Sans-serif; color: rgb(136, 136, 136); white-space: nowrap;" valign="top">' 
                . utf8_decode($words[$lang]['description']) . ': </td>'
                . '<td>' . utf8_decode($event[0]['description']) . '</td>'
                . '</tr>'
                . '<tr>'
                . '<td style="font-family: Arial,Sans-serif; color: rgb(136, 136, 136); white-space: nowrap;" valign="top">' 
                . utf8_decode($words[$lang]['location']) . ': </td>'
                . '<td>' . utf8_decode($event[0]['location'])                 
                . '</td>'
                . '</tr>';
                if(count($event[0]['members'][0]) > 0){
                    $html .= '<tr><td style="font-family: Arial,Sans-serif; color: rgb(136, 136, 136); white-space: nowrap;" valign="top">'
                    . $words[$lang]['members'] . ': </td>';
                    $html .= '<td>'
                    . implode(', ', $event[0]['members'][0])
                    . '</td></tr>';
                }                
                $html .= '</table>'
                . "</body>
            </html>";


        $title =  ucfirst($words[$lang]['remember']) . ': ' . $event[0]['title'];

        $this->generateIcs($event[0],true);
                        
        global $domain,$dsn_pear;

        if(returnCountEmailOptions($dsn_pear,$username) == 1){
            $mail_admin =returnEmailOptions($dsn_pear,$username);
        }else{
            $mail_admin = $username; // . '@' . $domain;
        }
        
        $hdrs = array(
            'From'    => $mail_admin,
            'Subject' => $title
        );        
        $mime = new Mail_mime();
        $mime->setTXTBody($text);
        $mime->setHTMLBody($html);        
        $body = $mime->get();
        $hdrs = $mime->headers($hdrs);

        $smtpinfo["host"] = $smtpServerAddress;
        $smtpinfo["port"] = $smtpPort;
        $smtpinfo["auth"] = false;

        $mail_object = & Mail::factory("smtp", $smtpinfo);
        $mail_object->send($mail, $hdrs, $body);
    }

    public function mailNotificationDelete($mail,$id,$user){
        global $smtpServerAddress,$smtpPort,$username,$squirrelmail_default_language,$words;
        
        include_once('Mail.php');
        include_once('Mail/mime.php');
        
        $lang = strtolower($squirrelmail_default_language);                
        
        $event = $this->selectEvent($id,$username,false);
        
        $event[0]['status'] = 'cancel';
        $event[0]['username'] = $user;
        
        $html = "<html>
                <body>"
                . '<table>'
                . '<tr>'
                . '<td colspan=2><b><span style="font-size:14pt">'
                . utf8_decode($event[0]['title'])
                . ' ' . $username .  ' </span></b></td>'
                . '</tr>'
                . '<tr>'
                . '<td>' . utf8_decode($words[$lang]['start']) . ': </td><td>'
                . $event[0]['start'] . '</td>'
                . '</tr>'
                . '<tr>'
                . '<td>' . $words[$lang]['end'] . ': </td><td>'
                . $event[0]['end'] . '</td>'
                . '</tr>'
                . '<tr>'
                . '<td>' . utf8_decode($words[$lang]['description']) . ': </td><td>'
                . utf8_decode($event[0]['description']) . '</td>'
                . '</tr>'
                . '<tr>'
                . '<td>' . $words[$lang]['location']  . ': </td><td>'
                . utf8_decode($event[0]['location']) . '</td>'
                . '</tr>'
                . '<tr>'
                . '<td>' . $words[$lang]['members']  . ': </td><td>'
                . implode(', ', $event[0]['members'][0])
                . '</td>'
                . '</tr>'
                . '<tr>'
                . '<td>' . _("Status") . ': </td><td>'
                . $words[$lang]['canceled'] . '</td>'
                . '</tr>'
                . '</table>'                
                . "</body>
            </html>";
        

        $title =  _('Cancel') . ': ' . $event[0]['title'];
        $this->generateIcs($event[0]);
                
        global $domain,$dsn_pear;

        if(returnCountEmailOptions($dsn_pear,$user) == 1){
            $mail_admin =returnEmailOptions($dsn_pear,$user);
        }else{
            $mail_admin = $user; // . '@' . $domain;
        }
        
        $hdrs = array(
            "From"    => $mail_admin ,
            'Subject' => $title
        );        
        
        $smtpinfo["host"] = $smtpServerAddress;
        $smtpinfo["port"] = $smtpPort;
        $smtpinfo["auth"] = true;

        $file =  "../temp/" . $user . "_temp.ics";
        
        $mime = new Mail_mime();
        $mime->setTXTBody($text);
        $mime->setHTMLBody($html);
        $mime->addAttachment($file,'text/calendar');
        $body = $mime->get();
        $hdrs = $mime->headers($hdrs);

        $smtpinfo["host"] = $smtpServerAddress;
        $smtpinfo["port"] = $smtpPort;
        $smtpinfo["auth"] = false;

        $mail_object = & Mail::factory("smtp", $smtpinfo);
        $mail_object->send($mail, $hdrs, $body);

        unlink($file);
    }

    public function mailNotification($mail,$update = false){
        global $smtpServerAddress,$smtpPort,$username,$squirrelmail_default_language,$words;
        include_once ('Mail.php');
        include_once ('Mail/mime.php');               

        if(is_null($this->ics)){
            $this->ics = $this->returnIcsDataBase();
            $this->createIcsTemp($this->ics,$username);
        }      

        $lang = strtolower($squirrelmail_default_language);
        
        //$lang = strtolower($squirrelmail_default_language);
        //$words[$lang]['location']

        $timeStart = explode(" ",$this->start);
        $dateStart = explode("-",$timeStart[0]);
        $strDateStart = $dateStart[2] . '-' . $dateStart[1] . '-' . $dateStart[0];

        $timeEnd = explode(" ",$this->end);
        $dateEnd = explode("-",$timeEnd[0]);
        $strDateEnd = $dateEnd[2] . '-' . $dateEnd[1] . '-' . $dateEnd[0];
        $file = '../temp/' . $username . '_temp.ics';

        if($this->allday == 'true'){
            $date = $strDateStart;
        }else{
            $date = ucfirst(_('from')) . ' ' .  $timeStart[1] . ', ' .
            $strDateStart . ' ' .  _('to') . ' ' .  $timeEnd[1] . ', '
            . $strDateEnd; 
        }

        $strMembers = implode(', ',$this->members);
        
        bindtextdomain('squirrelmail', SM_PATH . 'locale');
        textdomain('squirrelmail');

        $html = '<table>'
        . "<b style='font-size:24px'>"
        . utf8_decode($this->summary) .  "</b><br><br>"
        . '<tr>'
        . '<td><span style="color:silver">'
        . $words[$lang]['when'] . ': </span></td>'
        . '<td>' . $date . '</td>'
        . '</tr>'
        . '<tr>'
        . '<td><span style="color:silver">' . $words[$lang]['where'] . ':</span></td>'
        . '<td>' . utf8_decode($this->location) .'</td>'
        . '</tr>'
        . '<tr>'
        . '<td><span style="color:silver">' . $words[$lang]['atendees'] . ': </span></td>'
        . '<td>' . $strMembers . ' ' . '</td>'
        . '</tr>'
        . '</table>';
        
        if($update){
            $title =  ucfirst($words[$lang]['update']) . ': ';
        }else{
            $title =  ucfirst($words[$lang]['invite']) . ': ';
        }
        
        $title .=  utf8_decode($this->summary) . ' '
        . ucfirst($words[$lang]['date']) . ': ' . $date;
        global $domain,$dsn_pear;
        
        if(returnCountEmailOptions($dsn_pear,$username) == 1){
            $mail_admin =returnEmailOptions($dsn_pear,$username);
        }else{
            $mail_admin = $username; // . '@' . $domain;
        }
        
        $hdrs = array(
            'From'    => $mail_admin,
            'Subject' => $title
        );
        $mime = new Mail_mime();
        $mime->setTXTBody($text);
        $mime->setHTMLBody($html);
        $mime->addAttachment($file,'text/calendar');
        $body = $mime->get();
        $hdrs = $mime->headers($hdrs);
                
        $smtpinfo["host"] = $smtpServerAddress;
        $smtpinfo["port"] = $smtpPort;
        $smtpinfo["auth"] = false;

        $mail_object = & Mail::factory("smtp", $smtpinfo);
        $mail_object->send($mail, $hdrs, $body);        
        unlink($file);
    }
    
    public function alertEvents($user){
        $this->sql = "SELECT eventid, summary as title, description, location, allday,
            to_char(time_ini,'HH24:MI  DD/MM/YYYY') as date_start,
            to_char(time_end,'HH24:MI  DD/MM/YYYY') as date_end,
            frequency, alarm, alarm_time, alarm_period,
			owner_event, repeat, uid from calendars_events
			where owner_event = '$user' and is_notified = 'f' and alarm = 't' and
			(time_ini - (alarm_time || ' '  || alarm_period)::interval) < now()";        
        $res = $this->db->getAll($this->sql,array(),DB_FETCHMODE_ASSOC);        
        $events = Array();
        $id = 0;
        foreach($res as $ev){            
            if($ev['alert_time'] >= $ev['now']){
                $events[$id]['id'] = $ev['id'];
                $events[$id]['title'] = $ev['title'];
                $events[$id]['start'] = $ev['date_start'];
                $events[$id]['end'] = $ev['date_end'];
            }
            $id++;
        }        
        echo json_encode($res);
    }

    public function confirmEvent($user,$id){
        $this->sql = "update members set response = 'C' where username = '$user' and fk_event = $id";        
        $res = $this->db->query($this->sql);   
    }
    
    public function cancelEvent($user,$id){
        $this->sql = "update members set response = 'D' where username = '$user' and fk_event = $id";        
        $res = $this->db->query($this->sql);
    }

    public function selectInvitesEvents($user){       
        $this->sql = "SELECT  id,title,to_char(date_start,'HH24:MI  DD/MM/YYYY') as start,to_char(date_end,'HH24:MI  DD/MM/YYYY')
            as end,allDay,color from calendar_events,members where calendar_events.id = members.fk_event
            and members.username = '$user' and response = 'U'";

        $res = $this->db->getAll($this->sql,array(),DB_FETCHMODE_ASSOC);
        $events = Array();
        $id = 0;
        foreach($res as $ev){         
            if($ev['alert_time'] >= $ev['now']){
                $events[$id]['id'] = $ev['id'];
                $events[$id]['title'] = $ev['title'];
                $events[$id]['start'] = $ev['date_start'];
                $events[$id]['end'] = $ev['date_end'];
            }
            $id++;
        }
        echo json_encode($res);
    }

    public function selectEvents($user){
        global $domain;
        if(strpos($user,'@') !== false){
            $mail = $user;
        }else{
            $mail =  $user . '@' . $domain;
        }
        
        /* Necessita refactor */        
        $this->sql = "select distinct calendars_events.eventid as id,
                repeat, frequency, date_repeat_end, time_ini, time_end, allday,location,summary as title,to_char(time_ini,'Month DD, YYYY HH24:MI:SS')
                as start, to_char(time_end,'Month DD, YYYY HH24:MI:SS') as end from calendars_events 
                where calendars_events.owner_event = '$user'";     
        
        $res = $this->db->getAll($this->sql,array(),DB_FETCHMODE_ASSOC);        

        $this->sql = "select distinct calendars_events.eventid as id, repeat, frequency, date_repeat_end, time_ini, time_end, allday,location,summary as title,to_char(time_ini,'Month DD, YYYY HH24:MI:SS')
                as start, to_char(time_end,'Month DD, YYYY HH24:MI:SS') as end from calendars_events, calendars_members
                where calendars_members.eventid = calendars_events.eventid and calendars_members.emailaddr = '$mail'";

        $res2 = $this->db->getAll($this->sql,array(),DB_FETCHMODE_ASSOC);
        
        $res = array_merge($res,$res2);               

        foreach($res as $k => &$v){
            foreach($v as $k1 => &$v2){
                if($k1 == 'allday'){
                    if($v2 == 'f'){
                        $v['allDay'] = false;
                        unset($v['allday']);
                    }else{
                        $v['allDay'] = true;
                        unset($v['allday']);
                    }
                }
                if($k1 == 'repeat'){
                    if($v2 == 't'){                        
                        if($v['allday']  == 'f'){
                            $v['allDay'] = false;
                            unset($v['allday']);
                        }else{
                            $v['allDay'] = true;
                            unset($v['allday']);
                        }
                        if(isset($v['clone'])){
                            continue;
                        }
                        
                        $timeEndRepeat = strtotime($v['date_repeat_end']);
                        $timeIni = strtotime($v['time_ini']);
                        
                        switch($v['frequency']){
                            case 'daily':                                                                                             
                               for($i = 1; $timeIni < $timeEndRepeat;$i++){
                                    $a = $v;
                                    $a['start'] = strftime("%B %d, %Y %H:%M:%S",
                                            strtotime("+$i day",strtotime($v['time_ini'])));
                                    $a['end'] = strftime("%B %d, %Y %H:%M:%S",
                                            strtotime("+$i day",strtotime($v['time_end'])));
                                    $timeIni = strtotime("+$i day",strtotime($v['time_end']));                                    
                                    $a['clone'] = 'true';
                                    //echo 'Time ini : ' . $timeIni . '<br>';
                                    $res[] = $a;
                                }
                                break;
                            case 'weekly':
                                for($i = 1; $timeIni < $timeEndRepeat;$i++){
                                    $a = $v;
                                    $a['start'] = strftime("%B %d, %Y %H:%M:%S",
                                            strtotime("+$i week",strtotime($v['time_ini'])));
                                    $a['end'] = strftime("%B %d, %Y %H:%M:%S",
                                            strtotime("+$i week",strtotime($v['time_end'])));
                                    $timeIni = strtotime("+$i week",strtotime($v['time_end']));
                                    $a['clone'] = 'true';
                                    $res[] = $a;
                                }
                                break;
                            case 'monthly':                                
                                for($i = 1; $timeIni < $timeEndRepeat;$i++){
                                    $a = $v;
                                    $a['start'] = strftime("%B %d, %Y %H:%M:%S",
                                            strtotime("+$i month",strtotime($v['time_ini'])));
                                    $a['end'] = strftime("%B %d, %Y %H:%M:%S",
                                            strtotime("+$i month",strtotime($v['time_end'])));
                                    $timeIni = strtotime("+$i month",strtotime($v['time_end']));
                                    $a['clone'] = 'true';
                                    $res[] = $a;
                                }
                                break;
                        }
                    }
                }


            }
        }
        
        echo json_encode($res);
                
    }
    
    public function deleteEvent($id){
        $this->sql = "delete from calendars_events where eventid = $id";        
        $res = $this->db->query($this->sql);        
    }
    
    public function editEvent($id,$summary,$start,$end,$allDay,$location,
            $frequency,$alarm,$alarm_period,$alarm_time,$repeat,$description,$members,$username,$local,$endRepeat){
        $arrMembers = explode(',',$members);

        $vetor['repeat'] = $repeat;
        $vetor['periodRepeat'] = $frequency;
        $vetor['endRepeat'] = $endRepeat;

        $vetor['summary'] = $summary;
        $vetor['description'] = $description;
        $vetor['start'] = $start;
        $vetor['end'] = $end;
        $vetor['location'] = $location;
        $vetor['members'] = $arg[15];
        $vetor['alarm'] = $alarm;

        $ics = $this->generateIcs($vetor);

        if(count($arrMembers) > 0){
            $this->deleteMembers($id);
            foreach($arrMembers as $m){
                if(!empty($m))
                    $this->addMembersEvent($m,$id);
            }
        }

        $this->sql = "update calendars_events set
        is_notified = false,
        description = '$description', summary = '$summary',location = '$location',
        allday = '$allDay', time_ini = '$start', time_end = '$end',
        frequency = '$frequency', alarm = '$alarm', alarm_time = '$alarm_time',
        alarm_period = '$alarm_period',ics = '$ics',repeat = '$repeat'
        where eventid = $id";  //,$minutes,$day,$username,$id);        
        $res = $this->db->query($this->sql);

    }
    public function deleteMembers($id,$all = false){        
        $this->sql = "delete from calendars_members where eventid = $id";
        $res = $this->db->query($this->sql);
    }

    
    public function selectMembers($id,$json=false){
        $this->sql = "select * from calendars_members where eventid = $id";        
        $res = $this->db->getAll($this->sql,array(),DB_FETCHMODE_ASSOC);
        if($json)
            return json_encode($res);
        else
            return $res;
    }
    //or calendar_events.id = members.fk_event and members.username = '$user'";

    public function searchEvents($strSearch,$user){        
        $this->sql = "select * from calendars_events where lower(summary) like
                lower('%$strSearch%')
            or lower(description) like lower('%$strSearch%') and owner_event = '$user'";                
        $res = $this->db->getAll($this->sql,array(),DB_FETCHMODE_ASSOC);        
        echo json_encode($res);
        
    }    

    public function selecDaysBusy($user,$json = false){        
        /*
         * $this->sql = "select date_start,date_end from calendar_events,members where owner_event = '$user'
                or calendar_events.id = members.fk_event and members.username = '$user' and response = 'C'";
         */             
        global $domain;

        $this->sql = "select distinct(calendars_events.eventid),
                time_ini as date_start,time_end as date_end
                from calendars_events
                INNER JOIN calendars_members
                ON calendars_members.eventid = calendars_events.eventid
                where emailaddr = '$user@$domain';
            ";
        
        $res = $this->db->getAll($this->sql,array(),DB_FETCHMODE_ASSOC);

        $this->sql = "select distinct(calendars_events.eventid),
            time_ini as date_start,time_end as date_end
            from calendars_events
            where calendars_events.owner_event = '$user'";
            
        $res2 = $this->db->getAll($this->sql,array(),DB_FETCHMODE_ASSOC);

        $arrayCombined = array_merge($res,$res2);

        $dias = array();
        
        foreach($arrayCombined as &$v){
            $dateFim = date('d-m-Y',strtotime($v['date_end']));
            $dateIn = date('d-m-Y',strtotime($v['date_start']));
            if(date('d-m-Y',strtotime($v['date_end'])) - $dateIn = date('d-m-Y',strtotime($v['date_start'])) > 1){
                $diasC = date('d-m-Y',strtotime($v['date_end'])) - $dateIn = date('d-m-Y',strtotime($v['date_start']));
                for($i = 1; $i < $diasC;$i++){
                    $diaAdd = date('d-m-Y',strtotime($v['date_start']) + (86400 * $i));
                    if(!in_array($diaAdd,$dias)){
                        $dias[] = $diaAdd;
                    }
                }
            }
            if(!in_array($dateFim,$dias)){
                $dias[] = $dateFim;
            }
            if(!in_array($dateIn,$dias)){
                $dias[] = $dateIn;
            }           
        }
        if($json == true)
            return json_encode($dias);
        else
            return $dias;
    }
    
}


?>
