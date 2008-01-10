-- Agent-related views

CREATE OR REPLACE VIEW cc_agent_money_v AS
	SELECT agentid, date, pay_type, descr, NULL::bigint AS card_id, NULL::NUMERIC AS pos_credit, credit AS neg_credit, credit 
		FROM cc_agentpay WHERE credit >=0
UNION SELECT agentid, date, pay_type, descr, NULL::bigint AS card_id, 0-credit AS pos_credit, NULL  AS neg_credit, credit 
		FROM cc_agentpay WHERE credit <0
UNION SELECT agentid, date, pay_type, 'Money from customer' as descr, card_id, credit AS pos_credit, NULL AS neg_credit, 0-credit
		FROM cc_agentrefill WHERE credit >=0 AND carried = false
UNION SELECT agentid, date, pay_type, 'Pay back customer' as descr, card_id, NULL AS pos_credit, 0-credit AS neg_credit, 0-credit
		FROM cc_agentrefill WHERE credit <0 AND carried = false;

/*
CREATE OR REPLACE VIEW cc_agent_money_vi AS
	SELECT agentid, date, pay_type, gettexti(pay_type, cc_agent.locale) AS pay_type_txt,
		descr, NULL::bigint AS card_id, NULL::NUMERIC AS pos_credit, cc_agentpay.credit AS neg_credit, 
		cc_agentpay.credit
		FROM cc_agentpay, cc_agent WHERE cc_agentpay.credit >=0 AND cc_agentpay.agentid = cc_agent.id
UNION SELECT agentid, date, pay_type, gettexti(pay_type, cc_agent.locale) AS pay_type_txt, descr, NULL::bigint AS card_id, 0-cc_agentpay.credit AS pos_credit, NULL  AS neg_credit, cc_agentpay.credit 
		FROM cc_agentpay, cc_agent WHERE cc_agentpay.credit <0 AND cc_agentpay.agentid = cc_agent.id
UNION SELECT agentid, date, pay_type, gettexti(pay_type, cc_agent.locale) AS pay_type_txt, gettext('Money from customer',cc_agent.locale) as descr, card_id, cc_agentrefill.credit AS pos_credit, 
			NULL AS neg_credit, 0-cc_agentrefill.credit
		FROM cc_agentrefill, cc_agent 
		WHERE cc_agentrefill.credit >=0 AND carried = false AND cc_agent.id = agentid
UNION SELECT agentid, date, pay_type, gettexti(pay_type, cc_agent.locale) AS pay_type_txt, gettext('Pay back customer',cc_agent.locale) as descr, card_id, NULL AS pos_credit, 
			0-cc_agentrefill.credit AS neg_credit, 0-cc_agentrefill.credit
		FROM cc_agentrefill, cc_agent 
		WHERE cc_agentrefill.credit <0 AND carried = false AND cc_agent.id = agentid;
*/

CREATE OR REPLACE VIEW cc_agent_daycalls_v AS
SELECT count(*) as num, sum(sessionbill) AS charges , (sum(sessiontime) * interval '1 sec') as totaltime,
	date_trunc('day',starttime) AS day,
	cc_card_group.agentid AS agentid
	FROM cc_call, cc_card, cc_card_group
	WHERE cc_call.cardid = cc_card.id 
	  AND cc_card.grp = cc_card_group.id
	  AND cc_card_group.agentid IS NOT NULL
	GROUP BY agentid,day ORDER BY day;
	
CREATE OR REPLACE FUNCTION cc_calc_daysleft(agentid bigint, curtime timestamp with time zone, backi interval,
	out credit NUMERIC(12,4),out climit NUMERIC(12,4),out avg_time interval,
	out avg_charges NUMERIC(12,4), OUT days_left NUMERIC ) AS $$
SELECT credit, climit, AVG(totaltime) as avg_time,
	AVG(charges) AS avg_charges, 
	trunc((cc_agent.credit +cc_agent.climit) / AVG(charges)) 
	FROM cc_agent_daycalls_v, cc_agent 
	WHERE cc_agent_daycalls_v.agentid = cc_agent.id
		AND cc_agent.id = $1 AND cc_agent_daycalls_v.day <= $2 AND
		cc_agent_daycalls_v.day >= date_trunc('day',$2 - $3)
	GROUP BY agentid, credit, climit  ;
$$ LANGUAGE SQL STABLE STRICT;

--eof