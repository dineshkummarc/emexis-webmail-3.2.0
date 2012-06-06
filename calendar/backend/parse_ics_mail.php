<?php

/**
 * parse_ics_mail.php -- Analisa o icalendar de um email
 *  
 */

/**
 * Path for SquirrelMail required files.
 * @ignore
 */
define('SM_PATH','../../');

/* SquirrelMail required files. */
require_once(SM_PATH . 'include/validate.php');
require_once(SM_PATH . 'functions/global.php');
require_once(SM_PATH . 'functions/imap.php');
require_once(SM_PATH . 'functions/mime.php');
require_once(SM_PATH . 'functions/html.php');
require_once 'DB.php';
require_once '../class/DatabaseCon.php';
require_once '../class/iCalcreator.class.php';
require_once '../class/Events.php';

sqgetGlobalVar('key',        $key,          SQ_COOKIE);
sqgetGlobalVar('username',   $username,     SQ_SESSION);
sqgetGlobalVar('onetimepad', $onetimepad,   SQ_SESSION);
sqgetGlobalVar('delimiter',  $delimiter,    SQ_SESSION);
sqgetGlobalVar('QUERY_STRING', $QUERY_STRING, SQ_SERVER);
sqgetGlobalVar('messages', $messages,       SQ_SESSION);
sqgetGlobalVar('passed_id', $passed_id, SQ_GET);

if ( sqgetGlobalVar('mailbox', $temp, SQ_GET) ) {
  $mailbox = $temp;
}
if ( !sqgetGlobalVar('ent_id', $ent_id, SQ_GET) ) {
  $ent_id = '';
}
if ( !sqgetGlobalVar('passed_ent_id', $passed_ent_id, SQ_GET) ) {
  $passed_ent_id = '';
}

$imapConnection = sqimap_login($username, $key, $imapServerAddress, $imapPort, 0);
$mbx_response = sqimap_mailbox_select($imapConnection, $mailbox);
$message = $messages[$mbx_response['UIDVALIDITY']][$passed_id];
$message_ent = $message->getEntity($ent_id);
if ($passed_ent_id) {
    $message = &$message->getEntity($passed_ent_id);
}
$header   = $message_ent->header;
$type0    = $header->type0;
$type1    = $header->type1;
$charset  = $header->getParameter('charset');
$encoding = strtolower($header->encoding);

$msg_url   = 'read_body.php?' . $QUERY_STRING;
$msg_url   = set_url_var($msg_url, 'ent_id', 0);
$dwnld_url = '../src/download.php?' . $QUERY_STRING . '&amp;absolute_dl=true';
$unsafe_url = 'view_text.php?' . $QUERY_STRING;
$unsafe_url = set_url_var($unsafe_url, 'view_unsafe_images', 1);

$body = mime_fetch_body($imapConnection, $passed_id, $ent_id);
$body = decodeBody($body, $encoding);

if (isset($languages[$squirrelmail_language]['XTRA_CODE']) &&
    function_exists($languages[$squirrelmail_language]['XTRA_CODE'])) {
    if (mb_detect_encoding($body) != 'ASCII') {
        $body = $languages[$squirrelmail_language]['XTRA_CODE']('decode', $body);
    }
}

if ($type1 == 'html' || (isset($override_type1) &&  $override_type1 == 'html')){
    $ishtml = TRUE;    
    if (! empty($charset))
        $body = charset_decode($charset,$body,false,true);
    $body = magicHTML( $body, $passed_id, $message, $mailbox);
} else {
    $ishtml = FALSE;
    translateText($body, $wrap_at, $charset);
}
global $dsn_pear;
$event = new Events($dsn_pear);
$body = str_replace(array('<pre>','</pre>'),'',$body);

$pattern = '/<\/?a\s?[a-z|.]*>/';
$replacement = '';
$body = strip_tags($body); 
//// preg_replace($pattern, $replacement, $body);

if($event->createIcsTemp($body, $username)){
    $fn = '../temp/' . $username . '_temp.ics';
    $config = array( "unique_id" => "emexiswebmail.com", "filename" => $fn);
    $vcalendar = new vcalendar( $config );
    $vcalendar->parse();

    while( $vevent = $vcalendar->getComponent( "vevent" )) { //
        $description = utf8_encode(html_entity_decode($vevent->getProperty("description")));
        $summary = utf8_encode(html_entity_decode($vevent->getProperty("summary")));
        $location = utf8_encode(html_entity_decode($vevent->getProperty("location")));
        $dtstart = $vevent->getProperty("DTSTART");
        $dtend = $vevent->getProperty("DTEND");
        $organizer = $vevent->getProperty("ORGANIZER");
        $uid = $vevent->getProperty('UID');
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
        
        if($event->addEvent(1,$summary,$description,$location,$allday,$start,$end,
                'false','','false',0,'diarily',$body,$owner,
                $members,$uid,$start) == 1){
        ?>

        <table>
            <tr>
                <td><img src="../images/calendar.png"></td>
                <td>
                    <span style="color:green;font-weight:bold">
                    <?php echo _('Event added to the calendar');?>
                    </span>
                </td>
            </tr>
        </table>
        <?php
                }else{
        ?>
        <table>
            <tr>
                <td><img src="../images/calendar.png"></td>
                <td>
                    <span style="color:green;font-weight:bold">
                    <?php echo _('Event updated');?>
                    </span>
                </td>
            </tr>
        </table>
        <?php
                }
    }
    
}
    

?>

