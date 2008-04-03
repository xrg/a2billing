--- Views/rules that interface the queue tables to asterisk realtime db

CREATE OR REPLACE VIEW realtime_queues AS
	SELECT * FROM ast_queue;

CREATE OR REPLACE VIEW realtime_queue_members AS
	SELECT  123 AS uniqueid, ''::TEXT AS membername,
	''::TEXT AS queue_name, ''::TEXT AS interface,
	 0 AS penalty, false AS paused
 	WHERE false;
 	
CREATE OR REPLACE VIEW queue_log AS
	SELECT EXTRACT(EPOCH FROM tstamp) AS "time", callid, queuename,
		agent, event, 
		COALESCE(parm1,'') ||'|' || COALESCE(parm2,'') ||'|' || COALESCE(parm3,'')  AS "data",
		parm1,parm2,parm3
	   FROM ast_queue_log;

CREATE OR REPLACE RULE queue_log_insert_r AS ON INSERT TO queue_log DO INSTEAD
	INSERT INTO ast_queue_log(tstamp,callid, queuename,agent,event,parm1,parm2,parm3)
		VALUES(to_timestamp(NEW."time"),NEW.callid,NEW.queuename,NEW.agent,NEW.event,
			COALESCE(NEW.parm1,split_part(NEW.data,'|',1)),
			COALESCE(NEW.parm1,split_part(NEW.data,'|',2)),
			COALESCE(NEW.parm1,split_part(NEW.data,'|',3)));

GRANT ALL ON realtime_queues TO a2b_group;
GRANT ALL ON realtime_queue_members TO a2b_group;

