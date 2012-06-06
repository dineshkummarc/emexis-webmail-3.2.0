------------------------------
-- pgDesigner 1.2.17
--
-- Project    : emexiswebmail
-- Date       : 08/24/2011 16:32:58.105
-- Description: 
------------------------------


-- Start Table's declaration
CREATE TABLE "address" (
"owner" character varying NOT NULL,
"nickname" character varying NOT NULL,
"firstname" character varying NOT NULL,
"lastname" character varying NOT NULL,
"email" character varying NOT NULL,
"label" character varying NOT NULL
) WITHOUT OIDS;
ALTER TABLE "address" ADD CONSTRAINT "address_pkey" PRIMARY KEY("owner","nickname");
CREATE UNIQUE INDEX "address_firstname_key" ON "address" USING btree ("firstname","lastname");

CREATE TABLE "addressgroups" (
"owner" character varying,
"nickname" character varying NOT NULL,
"addressgroup" character varying,
"type" character varying
) WITHOUT OIDS;
CREATE UNIQUE INDEX "unique_addressgroups" ON "addressgroups" USING btree ("owner","addressgroup","nickname","type");

CREATE TABLE "dashboard" (
"id" serial NOT NULL,
"html" text
) WITHOUT OIDS;
ALTER TABLE "dashboard" ADD CONSTRAINT "dashboard_pk" PRIMARY KEY("id");

CREATE TABLE "global_abook" (
"owner" character varying NOT NULL,
"nickname" character varying NOT NULL,
"firstname" character varying NOT NULL,
"lastname" character varying NOT NULL,
"email" character varying NOT NULL,
"label" character varying NOT NULL
) WITHOUT OIDS;
ALTER TABLE "global_abook" ADD CONSTRAINT "global_abook_pkey" PRIMARY KEY("owner","nickname");

CREATE TABLE "userprefs" (
"user" character varying NOT NULL,
"prefkey" character varying NOT NULL,
"prefval" text
) WITHOUT OIDS;
ALTER TABLE "userprefs" ADD CONSTRAINT "userprefs_pkey" PRIMARY KEY("prefkey","user");

CREATE TABLE "calendars" (
"calid" serial NOT NULL,
"name" character varying,
"user" character varying,
"color" character varying
) WITHOUT OIDS;
ALTER TABLE "calendars" ADD CONSTRAINT "calendars_pk" PRIMARY KEY("calid");

ALTER TABLE "calendars_events" DROP CONSTRAINT "calendars_events_fkey1" CASCADE;
ALTER TABLE "calendars_events" ADD CONSTRAINT "calendars_events_fkey1" FOREIGN KEY ("calid") REFERENCES "calendars"("calid") ON UPDATE RESTRICT ON DELETE CASCADE;

ALTER TABLE "calendars_members" DROP CONSTRAINT "calendars_members_fkey1" CASCADE;
ALTER TABLE "calendars_members" ADD CONSTRAINT "calendars_members_fkey1" FOREIGN KEY ("eventid") REFERENCES "calendars_events"("eventid") ON UPDATE RESTRICT ON DELETE CASCADE;

CREATE TABLE calendars_events
(
  eventid serial NOT NULL,
  calid serial NOT NULL,
  summary character varying,
  description character varying,
  "location" character varying,
  allday boolean,
  time_ini timestamp without time zone,
  time_end timestamp without time zone,
  frequency character varying, -- daily | weekly | monthly
  alarm boolean,
  alarm_time integer,
  alarm_period character varying, -- minutes | hours | days
  ics text,
  owner_event character varying,
  repeat boolean,
  uid character varying,
  is_notified boolean DEFAULT false,
  date_repeat_end timestamp without time zone,
  CONSTRAINT calendars_events_pk PRIMARY KEY (eventid)
)
WITH (
  OIDS=FALSE
);

COMMENT ON COLUMN calendars_events.frequency IS 'daily | weekly | monthly';
COMMENT ON COLUMN calendars_events.alarm_period IS 'minutes | hours | days';


CREATE TABLE calendars_members
(
  eventid serial NOT NULL,
  emailaddr character varying NOT NULL,
  CONSTRAINT calendars_members_pk PRIMARY KEY (eventid, emailaddr),
  CONSTRAINT calendars_members_fkey1 FOREIGN KEY (eventid)
      REFERENCES calendars_events (eventid) MATCH SIMPLE
      ON UPDATE RESTRICT ON DELETE CASCADE
)
WITH (
  OIDS=FALSE
);

-- End Relation's declaration


--
-- PostgreSQL database dump complete
INSERT INTO userprefs VALUES ('default_pref', 'show_html_default', '1');
INSERT INTO userprefs VALUES ('default_pref', 'language', 'pt_BR');
INSERT INTO userprefs VALUES ('default_pref', 'javascript_on', '1');
INSERT INTO userprefs VALUES ('default_pref', 'hililist', 'a:0:{}');
INSERT INTO userprefs VALUES ('default_pref', 'collapse_folder', '0');
INSERT INTO userprefs VALUES ('default_pref', 'collapse_folder_INBOX', '0');
INSERT INTO userprefs VALUES ('default_pref', 'layout', 'horizontal');
INSERT INTO userprefs VALUES ('default_pref', 'order1', '1');
INSERT INTO userprefs VALUES ('default_pref', 'order2', '5');
INSERT INTO userprefs VALUES ('default_pref', 'order3', '2');
INSERT INTO userprefs VALUES ('default_pref', 'order4', '4');
INSERT INTO userprefs VALUES ('default_pref', 'order5', '3');
INSERT INTO userprefs VALUES ('default_pref', 'order6', '6');
INSERT INTO userprefs VALUES ('default_pref', 'html_mail_aggressive_reply_with_unsafe_images', '1');
INSERT INTO userprefs VALUES ('default_pref', 'html_mail_aggressive_reply', '1');
INSERT INTO userprefs VALUES ('default_pref', 'compose_window_type', 'html');
INSERT INTO userprefs VALUES ('default_pref', 'attachment_common_show_images', '1');
INSERT INTO userprefs VALUES ('default_pref', 'alt_index_colors', '0');
INSERT INTO userprefs VALUES ('default_pref', 'use_signature', '1');
INSERT INTO userprefs VALUES ('default_pref', 'prefix_sig', '1');
INSERT INTO userprefs VALUES ('default_pref', 'select_msg_with_checkbox', '0');
INSERT INTO userprefs VALUES ('default_pref', 'msg_with_checkbox', '1');
INSERT INTO userprefs VALUES ('default_pref', 'chosen_theme', 'default');
