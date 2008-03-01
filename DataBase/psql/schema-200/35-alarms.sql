-- Alarm tables
-- Copyright (C) P. Christeas, 2008

/** Alarm definitions: each row will define something that needs
   to run, a check.
   name: Human name of the alarm
   period: An arbitrary name of the period like 'daily','20min','manual'
   atype: Type of the alarm, will match php include files!
   asubtype: may distinguish "modes" of the alarm
   aparams: url-encoded string of data passed to alarm
   status: 0=inactive, 1=active, 2=...
   tomail: if set, an email should be set to that person. The mail template
           will be chosen by the alarm class.
*/
CREATE TABLE cc_alarm (
    id serial PRIMARY KEY,
    name text NOT NULL,
    atype VARCHAR(30) NOT NULL,
    asubtype TEXT,
    aparams TEXT,
    period VARCHAR(30) NOT NULL,
    status integer NOT NULL DEFAULT 0,
    tomail TEXT
);


/** Data/results of alarms.
   This table may have resulting data of alarms run, the plain fact that
   they run at some time, or even data *before* they run (async requests
   from other SQL logic).

   status: 0=unknown, 1=run, 2=failed-to-run, 3=raised-error,
       10=request-to-run
*/

CREATE TABLE cc_alarm_run (
    id bigserial PRIMARY KEY,
    alid INTEGER NOT NULL REFERENCES cc_alarm(id),
    tstamp TIMESTAMP NOT NULL DEFAULT now(),
    status INTEGER NOT NULL DEFAULT 1,
    params TEXT
);

-- eof
