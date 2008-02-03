--- Views related to the callshop sessions

CREATE OR REPLACE VIEW cc_session_calls AS
	SELECT call.starttime, 'Call'::TEXT AS descr, cc_shopsessions.id AS sid,
		cc_shopsessions.booth AS boothid,
		call.destination AS f2,
		call.calledstation AS cnum,
		NULL :: numeric AS pos_charge, sessionbill :: numeric AS neg_charge,
		(sessiontime) AS duration
		FROM cc_call2_v AS call, cc_shopsessions 
		WHERE call.cardid = cc_shopsessions.card
		  AND call.starttime >= cc_shopsessions.starttime AND (cc_shopsessions.endtime IS NULL OR call.starttime <= cc_shopsessions.endtime);

CREATE OR REPLACE VIEW cc_session_invoice AS
	SELECT * FROM cc_session_calls
		-- Calls
		-- Session start
		-- Note: at the start, we indicate charge/credit of 0 so that SUMs always
		-- have a non-null element
	UNION SELECT starttime, 'Session start' AS descr,  id AS sid,
		booth AS boothid, NULL AS f2, NULL as cnum,
		0 AS pos_charge, 0 AS neg_charge, NULL AS duration
		FROM cc_shopsessions

	UNION SELECT endtime, 'Session end' AS descr,  id AS sid,
		booth AS boothid, NULL AS f2, NULL as cnum,
		NULL AS pos_charge, NULL AS neg_charge, NULL AS duration
		FROM cc_shopsessions WHERE endtime IS NOT NULL
		-- Refills
	UNION SELECT cc_agentrefill.date AS starttime, cc_texts.txt AS descr, cc_shopsessions.id AS sid,
		booth AS boothid,
		(CASE WHEN carried THEN 'Carried from past credit'
			ELSE 'Money received' END) AS f2, NULL as cnum,
		cc_agentrefill.credit AS pos_charge, NULL as neg_charge,
		NULL as duration
		FROM cc_shopsessions, cc_agentrefill 
			LEFT JOIN cc_texts ON cc_texts.id = cc_agentrefill.pay_type AND cc_texts.lang = 'C'
		WHERE cc_shopsessions.card = cc_agentrefill.card_id AND
			( cc_agentrefill.boothid IS NULL OR cc_shopsessions.booth = cc_agentrefill.boothid) AND
			cc_agentrefill.credit > 0.0 AND
			cc_shopsessions.starttime <= cc_agentrefill.date AND
			(cc_shopsessions.endtime IS NULL OR cc_shopsessions.endtime >= cc_agentrefill.date)
		-- Payments
	UNION SELECT cc_agentrefill.date AS starttime, cc_texts.txt AS descr, cc_shopsessions.id AS sid,
		booth AS boothid, 
		(CASE WHEN carried THEN 'Carried forward'
			ELSE 'Money paid back' END) AS f2, NULL as cnum,
		NULL AS pos_charge, (0- cc_agentrefill.credit) AS neg_charge,
		NULL as duration
		FROM cc_shopsessions, cc_agentrefill
			LEFT JOIN cc_texts ON cc_texts.id = cc_agentrefill.pay_type AND cc_texts.lang = 'C'
		WHERE cc_shopsessions.card = cc_agentrefill.card_id AND
			( cc_agentrefill.boothid IS NULL OR cc_shopsessions.booth = cc_agentrefill.boothid) AND
			cc_agentrefill.credit < 0.0 AND
			cc_shopsessions.starttime <= cc_agentrefill.date AND
			(cc_shopsessions.endtime IS NULL OR cc_shopsessions.endtime >= cc_agentrefill.date)
	UNION SELECT charge.creationdate AS starttime, cc_texts.txt AS descr, cc_shopsessions.id AS sid,
		booth AS boothid,charge.description AS f2, NULL as cnum,
		NULL AS pos_charge, charge.amount as neg_charge,
		NULL as duration
		FROM cc_shopsessions, cc_card_charge AS charge
			LEFT JOIN cc_texts ON cc_texts.id = charge.chargetype AND cc_texts.lang = 'C'
		WHERE cc_shopsessions.card = charge.card AND
			cc_shopsessions.starttime <= charge.creationdate AND
			(cc_shopsessions.endtime IS NULL OR cc_shopsessions.endtime >= charge.creationdate);

CREATE OR REPLACE VIEW cc_shopsession_status_v AS
	SELECT (CASE WHEN ss.endtime IS NULL THEN true ELSE false END) AS is_open,
		ss.id AS sid, cc_booth.agentid, ss.booth, ss.card, (cc_card.inuse > 0)  AS is_inuse,
		(CASE WHEN ss.endtime IS NOT NULL THEN null ELSE cc_card.credit END) AS credit,
		 (COALESCE(ss.endtime,now()) - ss.starttime) AS duration
		FROM cc_shopsessions AS ss, cc_booth, cc_card 
		WHERE cc_card.id = ss.card AND cc_booth.id = ss.booth;

CREATE OR REPLACE VIEW cc_closed_sessions AS
	SELECT cc_shopsessions.id AS sid, cc_shopsessions.card, cc_shopsessions.starttime, (SUM(cc_session_invoice.pos_charge) - SUM(cc_session_invoice.neg_charge)) AS ssum
		FROM cc_shopsessions, cc_session_invoice WHERE
		cc_shopsessions.endtime IS NOT NULL AND
		cc_shopsessions.id = cc_session_invoice.sid 
		GROUP by cc_shopsessions.id, cc_shopsessions.card, cc_shopsessions.starttime;

CREATE OR REPLACE VIEW cc_session_problems AS
	SELECT cc_closed_sessions.sid, cc_closed_sessions.starttime, cc_closed_sessions.card, cc_card_group.agentid, cc_card_group.agent_role, 'Imbalance'::text AS Problem
		FROM  cc_closed_sessions, cc_card_group, cc_card
		WHERE cc_card.id = cc_closed_sessions.card
			AND cc_card_group.id = cc_card.grp
			AND cc_closed_sessions.ssum <> 0 
	UNION SELECT cc_shopsessions.id, cc_shopsessions.starttime, cc_shopsessions.card, cc_card_group.agentid, cc_card_group.agent_role, 'Hanging open'::text AS Problem
		FROM cc_shopsessions,cc_card_group,cc_card, cc_booth
		WHERE cc_shopsessions.card = cc_card.id
			AND cc_card_group.id = cc_card.grp
			AND cc_booth.id = cc_shopsessions.booth
			AND cc_shopsessions.endtime IS NULL
			AND (cc_booth.cur_card_id IS NULL OR cc_booth.cur_card_id <> cc_shopsessions.card)
	UNION SELECT cc_shopsessions.id, cc_shopsessions.starttime, cc_shopsessions.card, cc_card_group.agentid, cc_card_group.agent_role, 'Overlap with '  || ss2.id::text AS Problem
		FROM cc_shopsessions, cc_shopsessions AS ss2, cc_card_group, cc_card
		WHERE cc_shopsessions.card = cc_card.id
			AND cc_card_group.id = cc_card.grp
			AND cc_shopsessions.booth = ss2.booth
			AND cc_shopsessions.id <> ss2.id
			AND ss2.starttime >= cc_shopsessions.starttime
			AND cc_shopsessions.endtime > ss2.starttime
	UNION SELECT cc_shopsessions.id, cc_shopsessions.starttime, cc_shopsessions.card, cc_card_group.agentid, cc_card_group.agent_role, 'End before start'::text AS Problem
		FROM cc_shopsessions, cc_card_group, cc_card
		WHERE cc_shopsessions.card = cc_card.id
			AND cc_card_group.id = cc_card.grp
			AND starttime > endtime;

--eof
