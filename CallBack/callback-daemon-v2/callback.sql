

CREATE TABLE callback_spool (
 id							bigserial NOT NULL,
 uniqueid					text,
 entry_time				timestamp without time zone DEFAULT now(),

 status						text,
 server_ip				text,

 num_attempt			int,
 last_attempt_time	timestamp without time zone,
 manager_result		text,
 agi_result				text,
 callback_time		timestamp without time zone,

 channel					text,
 exten						text,
 context					text,
 priority					text,
 application				text,
 data						text,
 timeout					text,
 callerid					text,
 variable					text,
 account					text,
 async						text,
 actionid					text,
 id_server				integer,
 id_server_group	integer
) WITH OIDS;

ALTER TABLE ONLY callback_spool
    ADD CONSTRAINT callback_spool_uniqueid_key UNIQUE (uniqueid);


CREATE TABLE server_group (
 id						bigserial NOT NULL,
 name					text,
 description			text
) WITH OIDS;
INSERT INTO server_group (name, description) VALUES ('default', 'default group of server');

CREATE TABLE server_manager (
 id								bigserial NOT NULL,
 id_group						integer DEFAULT 1,
 server_ip					text,
 manager_host			text,
 manager_username	text,
 manager_secret		text,
 lasttime_used			timestamp without time zone DEFAULT current_timestamp
) WITH OIDS;

INSERT INTO server_manager (server_ip, manager_host, manager_username, manager_secret, lasttime_used) VALUES ('127.0.0.1',  'localhost', 'myasterisk',  'mycode', current_timestamp);
