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

-- CREATE TABLE queue_member_table (
-- 	uniqueid SERIAL PRIMARY KEY,
-- 	membername VARCHAR(40),
-- 	queue_name varchar(128),
-- 	interface varchar(128),
-- 	penalty INT(11),
-- 	paused BOOL,
-- 	UNIQUE KEY queue_interface (queue_name, interface)
-- );

