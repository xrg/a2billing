--- Views/rules that interface the queue tables to asterisk realtime db

CREATE OR REPLACE VIEW realtime_queues AS
	SELECT * FROM ast_queue;

CREATE OR REPLACE VIEW realtime_queue_members AS
	SELECT  123 AS uniqueid, ''::TEXT AS membername,
	''::TEXT AS queue_name, ''::TEXT AS interface,
	 0 AS penalty, false AS paused
 	WHERE false;

GRANT ALL ON realtime_queues TO a2b_group;
GRANT ALL ON realtime_queue_members TO a2b_group;

