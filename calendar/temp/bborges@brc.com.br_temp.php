<?php
              define(SM_PATH,"../../");
              require_once(SM_PATH . "include/validate.php");
              if($_SESSION["username"] != "bborges@brc.com.br")
                die("Fail, not permission to access this file");
              header("Content-Type: text/x-vCalendar");
              header("Content-Disposition: inline; filename=bborges@brc.com.br.ics");
            ?>BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//emexiswebmail//NONSGML kigkonsult.se iCalcreator 2.10//
BEGIN:VEVENT
UID:20120123T105114BRST-1845cV2Ibv@emexiswebmail
DTSTAMP:20120123T120114Z
ATTENDEE:
CATEGORIES:MEETING
DESCRIPTION:aadsafs
DTSTART:20120128T123000
DTEND:20120128T130000
LOCATION:fasd
SUMMARY:fasdfa
END:VEVENT
END:VCALENDAR
