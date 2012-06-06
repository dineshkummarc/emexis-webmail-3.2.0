<?php
/* 
 * Manipulação das ações dos eventos
 */

require_once 'DB.php';
require_once '../class/DatabaseCon.php';
require_once '../class/Events.php';

define('SM_PATH','../../');

require_once(SM_PATH . 'functions/global.php');

DEFINE(ADD,1);
DEFINE(SELECT_ALL_EVENTS,2);
DEFINE(UPDATE,3);
DEFINE(RESIZE,4);
DEFINE(EVENTS_JSON,5);
DEFINE(SELECT_ID,6);
DEFINE(DELETE,7);
DEFINE(EDIT,8);
DEFINE(ALERT,9);
DEFINE(NOTIFIED,10);
DEFINE(INVITES,11);
DEFINE(CONFIRM_EVENT,12);
DEFINE(CANCEL_EVENT,13);
DEFINE(NOTIFIED_MAIL,14);
DEFINE(EXPORT,15);
DEFINE(SELECT_MEMBERS,16);
DEFINE(SEARCH_EVENT,17);
DEFINE(SELECT_MEMBERS_ARRAY,18);
DEFINE(PRINT_EVENT,19);
DEFINE(SELECT_MAIL_OPTION,20);

if(ctype_digit($_POST['action']))
    $action = $_POST['action'];

if(ctype_digit($_GET['action']) && isset($_GET['action']))
    $action = $_GET['action'];

$title_event = $_POST['title_event'];
$date_start = $_POST['date_start'];
$date_end = $_POST['date_end'];
$description = $_POST['description'];
$allday = $_POST['allday'];
$alert = $_POST['alert'];
$minutes = $_POST['alert_before_minutes'];
$username = $_POST['owner_event'];
$color = $_POST['color'];
$members  = $_POST['members'];

global $username;

bindtextdomain('squirrelmail', SM_PATH . 'locale');
textdomain('squirrelmail');

switch($action){    
    case ADD:
        $username = $_POST['username'];
        if(empty($_POST['alarm_time']))
            $alarm = 0;
        else
            $alarm = $_POST['alarm_time'];
        $members = explode(',',$_POST['members']);        
        $evento = new Events($dsn_pear);        
        $evento->addEvent(1,$_POST['summary'],$_POST['description'],$_POST['location'],
                $_POST['allDay'],$_POST['start'],$_POST['end'],$_POST['repeat'],
                $_POST['frequency'],$_POST['alarm'],$alarm,$_POST['alarm_period'],
                $ics,$username,$members,'',$_POST['endRepeat']);       
    break;
    case SELECT_ALL_EVENTS:        
        $evento = new Events($dsn_pear);        
        echo $evento->selectEvents($_GET['user']);
    break;
    case UPDATE:        
        $evento = new Events($dsn_pear);
        $evento->updateEvent($_POST['minutesUp'],$_POST['daysUp'],$_POST['idUp'],$_POST['alldayUp'],$_POST['user'],$_POST['notify']);
    break;
    case EVENTS_JSON:
        $events = new Events($dsn_pear);
        echo implode(',',$events->selecDaysBusy());
    break;
    case RESIZE:
        $evento = new Events($dsn_pear);
        $evento->resizeEvent($_POST['minutesUp'],$_POST['daysUp'],$_POST['idUp'],$_POST['user'],$_POST['notify']);
    break;
    case SELECT_ID:
        $evento = new Events($dsn_pear);
        $evento->selectEvent($_POST['id'],$_POST['user'],true);
    break;
    case DELETE:
        $evento = new Events($dsn_pear);        
        if($_POST['notify'] == 'true'){            
            $evento = new Events($dsn_pear);
            $members = $evento->selectMembers($_POST['id'],false);            
            foreach($members as $m){                
                $evento->mailNotificationDelete($m['emailaddr'],$_POST['id'],$_POST['username']);
            }            
        }
        $evento->deleteEvent($_POST['id']);
        $evento->deleteMembers($_POST['id']); 
    break;
    case EDIT:
        $evento = new Events($dsn_pear);        
        $evento->editEvent($_POST['id'],$_POST['summary'],$_POST['start'],
                $_POST['end'],$_POST['allDay'],$_POST['location'],
            $_POST['frequency'],$_POST['alarm'],$_POST['alarm_period'], $_POST['alarm_time'],
                $_POST['repeat'],$_POST['description'],$_POST['members'],$_POST['username'],$_POST['local'],
                $_POST['endRepeat']
                );                
    break;
    CASE SELECT_MAIL_OPTION:
        $evento = new Events($dsn_pear);
        $evento->selectEmailOptions($_POST['user']);
        break;
    CASE ALERT:
        $evento = new Events($dsn_pear);
        $evento->alertEvents($_POST['user']);
    break;
    CASE NOTIFIED:
        $evento = new Events($dsn_pear);
        $evento->notified($_POST['id'],$_POST['mail']);
    break;
    case INVITES:
        $evento = new Events($dsn_pear);
        $evento->selectInvitesEvents($_POST['user']);
    break;
    case CONFIRM_EVENT:
        $evento = new Events($dsn_pear);
        $evento->confirmEvent($_POST['user'],$_POST['id']);
    break;
    case CANCEL_EVENT:
        $evento = new Events($dsn_pear);
        $evento->cancelEvent($_POST['user'],$_POST['id']);
    break;    
    case EXPORT:
        $vetor['members'] = explode(',',$_POST['members']);
        $vetor['summary'] = $_POST['summary'];
        $vetor['description'] = $_POST['description'];
        $vetor['start'] = $_POST['start'];
        $vetor['end'] = $_POST['end'];
        $vetor['location'] = $_POST['location'];
        $vetor['username'] = $_POST['username'];
        $vetor['alarm'] = $_POST['remember'];
        $vetor['alarm_period'] = $_POST['alarm_period'];
        $vetor['alarm_time'] = $_POST['remember_time'];
        $vetor['repeat'] = $_POST['repeat'];
        $vetor['periodRepeat'] = $_POST['periodRepeat'];
        $vetor['endRepeat'] = $_POST['endRepeat'];
        $vetor['frequency'] = $_POST['frequency'];
        $vetor['endRepeat'] = $_POST['endRepeat'];                
        $evento = new Events($dsn_pear);
        $evento->generateIcs($vetor,true);
    break;
    case SEARCH_EVENT:
        $evento = new Events($dsn_pear);        
        $evento->searchEvents($_POST['str_search'],$_POST['user']);        
        break;        
    case SELECT_MEMBERS:
        $id = $_POST['id'];
        $evento = new Events($dsn_pear);
        echo $evento->selectMembers($id,'true');
        break;
    case SELECT_MEMBERS_ARRAY:
        $id = $_POST['id'];
        $evento = new Events($dsn_pear);
        print_r($evento->selectMembers($id,false));        
        break;
    case PRINT_EVENT:
        $evento = new Events($dsn_pear);
        $res = $evento->selectEvent($_GET['id'],$_GET['user'],false);
        bindtextdomain('squirrelmail', SM_PATH . 'locale');
        textdomain('squirrelmail');
        $lang = strtolower($squirrelmail_default_language);
        require_once(SM_PATH . 'calendar/backend/mail_translate.php');
        ?>
        <html>
            <head>
                <title>
                    <?php
                        echo $words['lang']['print_event'];
                    ?>
                </title>
                <style type="text/css">
                    .label{
                        height:30px;
                        color: gray;
                        font-weight:bold;
                    }
                    .textValue{
                        height:30px;
                        font-style: italic;
                        color: silver;
                    }
                    td.button{
                        height: 50px;
                        text-align: right;
                    }
                    td.button input{
                        height: 30px;
                        background-color: gray;
                        color: white;
                    }
                </style>
            </head>
            <body>
                <table id="printEvent">
                    <tr>
                        <td colspan="2">
                            <h1><?php echo utf8_decode($res[0]['title']);?></h1>
                            <hr></hr>
                        </td>
                    </tr>
                    <tr>
                        <td class="label"><?php echo utf8_decode($words[$lang]['description']);?>:</td>
                        <td class="textValue"><?php echo utf8_decode($res[0]['description']);?></td>
                    </tr>
                    <tr>
                        <td class="label"><?php echo $words[$lang]['location'];?>:</td>
                        <td class="textValue"><?php echo utf8_decode($res[0]['location']);?></td>
                    </tr>
                    <tr>
                        <td class="label"><?php echo utf8_decode($words[$lang]['start']); ?>:</td>
                        <td class="textValue"><?php echo $res[0]['start'];?></td>
                    </tr>
                    <tr>
                        <td class="label"><?php echo $words[$lang]['end'];?></td>
                        <td class="textValue"><?php echo $res[0]['end'];?></td>
                    </tr>
                    <tr>
                        <td class="label"><?php echo $words[$lang]['members'];?></td>
                        <td class="textValue">
                    <?php
                        foreach($res[0]['members'] as $k => $v){
                            if((count($res[0]['members']) - 1) != $k){
                                echo $v["emailaddr"] . ', ';
                            }else{
                                echo $v["emailaddr"];
                            }
                        }
                    ?>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" class="button">
                            <input type="button" value="Imprimir" onclick="window.print()"/>
                        </td>
                    </tr>
                </table>
            </body>
        </html>
        <?php
        break;
}

?>
