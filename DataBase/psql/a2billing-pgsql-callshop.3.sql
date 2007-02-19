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


CREATE OR REPLACE VIEW cc_agent_money_v AS
	SELECT agentid, date, pay_type, descr, NULL::bigint AS card_id, NULL::NUMERIC AS pos_credit, credit AS neg_credit, credit 
		FROM cc_agentpay WHERE credit >=0
UNION SELECT agentid, date, pay_type, descr, NULL::bigint AS card_id, 0-credit AS pos_credit, NULL  AS neg_credit, credit 
		FROM cc_agentpay WHERE credit <0
UNION	SELECT agentid, date, pay_type, 'Money from customer' as descr, card_id, credit AS pos_credit, NULL AS neg_credit, 0-credit
		FROM cc_agentrefill WHERE credit >=0 AND carried = false
UNION	SELECT agentid, date, pay_type, 'Pay back customer' as descr, card_id, NULL AS pos_credit, 0-credit AS neg_credit, 0-credit
		FROM cc_agentrefill WHERE credit <0 AND carried = false;

CREATE OR REPLACE VIEW cc_agent_money_vi AS
	SELECT agentid, date, pay_type, descr, NULL::bigint AS card_id, NULL::NUMERIC AS pos_credit, credit AS neg_credit, credit 
		FROM cc_agentpay WHERE credit >=0
UNION SELECT agentid, date, pay_type, descr, NULL::bigint AS card_id, 0-credit AS pos_credit, NULL  AS neg_credit, credit 
		FROM cc_agentpay WHERE credit <0
UNION SELECT agentid, date, pay_type, gettext('Money from customer',cc_agent.locale) as descr, card_id, cc_agentrefill.credit AS pos_credit, 
			NULL AS neg_credit, 0-cc_agentrefill.credit
		FROM cc_agentrefill, cc_agent 
		WHERE cc_agentrefill.credit >=0 AND carried = false AND cc_agent.id = agentid
UNION SELECT agentid, date, pay_type, gettext('Pay back customer',cc_agent.locale) as descr, card_id, NULL AS pos_credit, 
			0-cc_agentrefill.credit AS neg_credit, 0-cc_agentrefill.credit
		FROM cc_agentrefill, cc_agent 
		WHERE cc_agentrefill.credit <0 AND carried = false AND cc_agent.id = agentid;

	
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
		
 
--eof
