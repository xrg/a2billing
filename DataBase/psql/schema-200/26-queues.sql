-- Tables to be used with asterisk's queue mechanism

CREATE TABLE ast_queue (
	id SERIAL PRIMARY KEY,
	name VARCHAR(128) NOT NULL UNIQUE,
	musiconhold VARCHAR(128),
	announce VARCHAR(128),
	"context" VARCHAR(128),
	"timeout" INTEGER,
	autopause BOOLEAN,
	autofill BOOLEAN,
	monitor_join BOOLEAN,
	monitor_format VARCHAR(128),
	queue_youarenext VARCHAR(128),
	queue_thereare VARCHAR(128),
	queue_callswaiting VARCHAR(128),
	queue_holdtime VARCHAR(128),
	queue_minutes VARCHAR(128),
	queue_seconds VARCHAR(128),
	queue_lessthan VARCHAR(128),
	queue_thankyou VARCHAR(128),
	queue_reporthold VARCHAR(128),
	announce_frequency INTEGER,
	announce_round_seconds INTEGER,
	announce_holdtime VARCHAR(128),
	retry INTEGER,
	wrapuptime INTEGER,
	maxlen INTEGER,
	servicelevel INTEGER,
	strategy VARCHAR(128),
	joinempty VARCHAR(128),
	leavewhenempty VARCHAR(128),
	eventmemberstatus BOOLEAN,
	eventwhencalled BOOLEAN,
	reportholdtime BOOLEAN,
	memberdelay INTEGER,
	weight INTEGER,
	timeoutrestart BOOLEAN,
	periodic_announce VARCHAR(50),
	periodic_announce_frequency INTEGER,
	ringinuse BOOLEAN,
	setinterfacevar BOOLEAN
);

CREATE TABLE ast_queue_log (
	id BIGSERIAL PRIMARY KEY,
	tstamp TIMESTAMP NOT NULL DEFAULT now(),
	callid TEXT,
	queuename TEXT,
	agent TEXT,
	event TEXT,
	parm1 TEXT,
	parm2 TEXT,
	parm3 TEXT
);

CREATE TABLE ast_queue_callers (
	id BIGSERIAL PRIMARY KEY,
	queue INTEGER NOT NULL REFERENCES ast_queue(id),
	callerid TEXT,
	uniqueid TEXT NOT NULL,
	status VARCHAR(20) NOT NULL,
	hupcause TEXT,
	ts_join TIMESTAMP,
	ts_connect TIMESTAMP,
	ts_end    TIMESTAMP,
	holdtime INTEGER,
	talktime INTEGER,
	brchannel TEXT,
	agent	TEXT
);

-- CREATE TABLE queue_member_table (
-- 	uniqueid SERIAL PRIMARY KEY,
-- 	membername VARCHAR(40),
-- 	queue_name varchar(128),
-- 	interface varchar(128),
-- 	penalty INT(11),
-- 	paused BOOL,
-- 	UNIQUE KEY queue_interface (queue_name, interface)
-- );

