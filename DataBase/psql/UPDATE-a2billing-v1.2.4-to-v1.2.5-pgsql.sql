CREATE TABLE cc_alarm (
    id bigserial NOT NULL,
    name text NOT NULL,
    periode integer NOT NULL DEFAULT 1,
    type integer NOT NULL DEFAULT 1,
    maxvalue numeric NOT NULL,
    minvalue numeric NOT NULL DEFAULT -1,
    id_trunk integer,
    status integer NOT NULL DEFAULT 0,
    numberofrun integer NOT NULL DEFAULT 0,
    numberofalarm integer NOT NULL DEFAULT 0,    
    datecreate timestamp without time zone DEFAULT now(),
    datelastrun timestamp without time zone DEFAULT now(),
    creationdate timestamp without time zone DEFAULT now(),
    emailreport text
);
ALTER TABLE ONLY cc_alarm
    ADD CONSTRAINT cc_alarm_pkey PRIMARY KEY (id);

CREATE TABLE cc_alarm_report (
    id bigserial NOT NULL,
    cc_alarm_id bigserial NOT NULL,
    calculatedvalue numeric NOT NULL,
    daterun timestamp without time zone DEFAULT now()
);
ALTER TABLE ONLY cc_alarm_report
    ADD CONSTRAINT cc_alarm_report_pkey PRIMARY KEY (id);
