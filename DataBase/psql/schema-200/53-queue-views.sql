--- Views/rules that interface the queue tables to asterisk realtime db

CREATE OR REPLACE VIEW realtime_queues AS
	SELECT * FROM ast_queue;

CREATE OR REPLACE VIEW realtime_queue_members AS
	-- First, select all local instances
	SELECT ast_queue_member.id AS uniqueid, 
		COALESCE(cc_card.firstname,'') || COALESCE(' ' || cc_card.lastname,'') AS membername,
		ast_queue.name AS queue_name, 
		(CASE WHEN sipiax = 1 THEN 'SIP'::TEXT WHEN sipiax = 2 THEN 'IAX2'::TEXT ELSE 'NONE'::TEXT END) ||
		'/' || COALESCE(cc_ast_users.peernameb,cc_card.username) AS interface,
		ast_queue_member.penalty, ast_queue_member.paused
	    FROM ast_queue_member,ast_queue,cc_ast_users, cc_ast_instance, cc_card
	    WHERE ast_queue_member.que = ast_queue.id
	      AND ast_queue_member.usr = cc_ast_users.id
	      AND cc_ast_instance.userid = cc_ast_users.id
	      AND cc_card.id = cc_ast_users.card_id
	      -- uncomment below to only allow active peers to appear..
	      -- AND (dyn = false OR ( (sipiax = 2 AND regseconds > 0) OR
	      --		(sipiax = 1 AND regseconds > EXTRACT ('epoch' FROM now()))))
	-- Remote peers? AGI ones?
	
	UNION SELECT 820182 AS uniqueid, 'The test' AS membername,
		ast_queue.name AS queue_name, 'Local/s@sim-agent' AS interface, 
		2 AS penalty, false AS paused
		FROM ast_queue
	;


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
			COALESCE(NEW.parm2,split_part(NEW.data,'|',2)),
			COALESCE(NEW.parm3,split_part(NEW.data,'|',3)));

-- Define a trigger that will parse a queue_log into the appropriate tables, notifications

CREATE OR REPLACE FUNCTION queue_log_insert_t () RETURNS TRIGGER AS $$
DECLARE
	qcall_id BIGINT;
BEGIN
	IF NEW.event = 'ENTERQUEUE' THEN
		-- that is a new caller in queue here..
		INSERT INTO ast_queue_callers(queue,callerid,uniqueid,status,ts_join)
			VALUES( (SELECT id FROM ast_queue WHERE name = NEW.queuename),
				NEW.parm2,NEW.callid,'joined',now());
		IF NOT FOUND THEN
			RAISE WARNING 'Cannot open new queue call';
		END IF;
 		RETURN NEW;
 	END IF;
 	
	SELECT id INTO qcall_id FROM ast_queue_callers
		WHERE uniqueid = NEW.callid
			AND queue = (SELECT id FROM ast_queue WHERE name = NEW.queuename);
	IF NOT FOUND THEN
		RAISE WARNING 'Cannot find existing queue call %',NEW.callid;
		RETURN NEW;
	END IF;

	IF NEW.event = 'ABANDON' THEN
		UPDATE ast_queue_callers SET ts_end = now(), status='abandon',
			holdtime = CAST(NEW.parm3 AS INTEGER)
			WHERE id = qcall_id;
	ELSIF NEW.event = 'AGENTDUMP' THEN
		-- Should we keep another table with dumps, status changes?
		UPDATE ast_queue_callers SET ts_end = now(), status='dump', agent = NEW.agent
			WHERE id = qcall_id;
	
	ELSIF NEW.event = 'CONNECT' THEN
		UPDATE ast_queue_callers SET ts_connect = now(), status='connect',
			holdtime = CAST(NEW.parm1 AS INTEGER), brchannel=NEW.parm2, agent=NEW.agent
			WHERE id = qcall_id;

	ELSIF NEW.event = 'COMPLETEAGENT' THEN
		UPDATE ast_queue_callers SET ts_end = now(), status='agent end',
			holdtime = CAST(NEW.parm1 AS INTEGER),
			talktime= CAST(NEW.parm2 AS INTEGER) /* , origpos = NEW.parm3 */
			WHERE id = qcall_id;
	ELSIF NEW.event = 'COMPLETECALLER' THEN
		UPDATE ast_queue_callers SET ts_end = now(), status='caller end',
			holdtime = CAST(NEW.parm1 AS INTEGER),
			talktime=CAST(NEW.parm2 AS INTEGER) /* , origpos = NEW.parm3 */
			WHERE id = qcall_id;
	ELSE
		RAISE WARNING 'Event % cannot be handled!', NEW.event;
	END IF;
	RETURN NEW;
END; $$ LANGUAGE PLPGSQL VOLATILE SECURITY DEFINER;

DROP TRIGGER IF EXISTS queue_log_insert_tr ON ast_queue_log;

CREATE TRIGGER queue_log_insert_tr AFTER INSERT ON ast_queue_log
	FOR EACH ROW EXECUTE PROCEDURE queue_log_insert_t();

--- Grant privileges
GRANT ALL ON realtime_queues TO a2b_group;
GRANT ALL ON realtime_queue_members TO a2b_group;

GRANT INSERT ON queue_log TO a2b_group;
GRANT UPDATE ON ast_queue_log_id_seq TO a2b_group;

--eof
