-- Copyright P.Christeas 2006


-- misc stuff for testing..


			
-- CREATE FUNCTION divide_time(div1 INTERVAL, div2 INTERVAL) RETURNS FLOAT AS $$
-- 	SELECT ( EXTRACT(EPOCH FROM $1) / EXTRACT(EPOCH FROM $2)) ;
-- 	$$ LANGUAGE SQL IMMUTABLE STRICT;
-- 
-- SELECT divide_time(INTERVAL '100sec', (endtime - starttime)) FROM cc_shopsessions;

/*SELECT date_trunc('sec', cc_shopsessions.starttime)AS session_start, 
	date_trunc('sec',(cc_shopsessions.endtime - cc_shopsessions.starttime)) AS session_time, 
	SUM(cc_call.stoptime - cc_call.starttime) AS sum_calls,
	to_char((divide_time(SUM(cc_call.stoptime - cc_call.starttime), (cc_shopsessions.endtime - cc_shopsessions.starttime)) * 100), '990D0000%') AS usage_pc
	FROM cc_call,cc_card, cc_shopsessions 
		WHERE cc_call.username = cc_card.username AND cc_shopsessions.card = cc_card.id
			AND cc_call.starttime >= cc_shopsessions.starttime AND (cc_shopsessions.endtime IS NULL OR cc_call.starttime <= cc_shopsessions.endtime)
		GROUP BY cc_shopsessions.id,cc_shopsessions.starttime, cc_shopsessions.endtime;*/
-- One view for all: have all the session transactions in one table.
	
CREATE OR REPLACE VIEW cc_agentcard_debt_v AS
	SELECT agentid, SUM(credit) as credit, 'Positive' AS typ  
		FROM cc_card,cc_agent_cards WHERE cc_agent_cards.card_id = cc_card.id
		AND cc_card.credit >0 GROUP BY cc_agent_cards.agentid
UNION
	SELECT agentid, SUM(credit) as credit, 'Negative' AS typ  
		FROM cc_card,cc_agent_cards WHERE cc_agent_cards.card_id = cc_card.id
		AND cc_card.credit <0 GROUP BY cc_agent_cards.agentid
UNION
	SELECT agentid, SUM(creditlimit) as credit, 'Limit' AS typ
		FROM cc_card,cc_agent_cards WHERE cc_agent_cards.card_id = cc_card.id
		GROUP BY cc_agent_cards.agentid;
		
 
/*CREATE OR REPLACE VIEW cc_texts_v AS
	SELECT id, txt AS txt_C FROM cc_texts AS t1 RIGHT OUTER JOIN cc_texts AS t2 ON t1.id = t2.id;*/

CREATE OR REPLACE VIEW cc_session_calls AS
SELECT cc_shopsessions.id AS sid,
	SUM(cc_call.stoptime - cc_call.starttime) AS duration, SUM(cc_call.sessionbill) AS bill,
	SUM(cc_call.buycost) AS buy_cost
	FROM cc_shopsessions, cc_call, cc_card
		WHERE cc_call.username = cc_card.username AND cc_shopsessions.card = cc_card.id
			AND cc_call.starttime >= cc_shopsessions.starttime AND (cc_shopsessions.endtime IS NULL OR cc_call.starttime <= cc_shopsessions.endtime)
		GROUP BY cc_shopsessions.id;

CREATE OR REPLACE VIEW cc_session_usage_v AS
SELECT  cc_shopsessions.id, cc_shopsessions.booth, cc_shopsessions.card,
	date_trunc('sec', cc_shopsessions.starttime) AS session_start, 
	date_trunc('sec',(cc_shopsessions.endtime - cc_shopsessions.starttime)) AS session_time, 
	calls.*,
	(divide_time(COALESCE(calls.duration,interval '0 min'), (cc_shopsessions.endtime - cc_shopsessions.starttime)) * 100) AS usage_pc
	FROM cc_shopsessions LEFT OUTER JOIN cc_session_calls AS calls ON cc_shopsessions.id = calls.sid
	 ;

SELECT booth, date_trunc('day',session_start) as dday,COUNT(id) AS sessions, COUNT(sid) AS sessions_act, SUM(session_time), AVG(session_time) AS session_time,
	SUM(duration) AS duration, SUM(bill) AS bill, AVG(usage_pc) AS usage
	FROM cc_session_usage_v
	GROUP BY booth, dday;
	
--	 (bill/EXTRACT(EPOCH FROM session_time))*3600
-- for percent: to_char('990D0000%')
--eof
