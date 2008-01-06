--- Views related to the callshop sessions

CREATE OR REPLACE VIEW cc_session_invoice AS
		-- Calls
	SELECT call.starttime, 'Call' AS descr, cc_shopsessions.id AS sid,
		cc_shopsessions.booth AS boothid,
		call.destination AS f2,
		call.calledstation AS cnum,
		NULL :: numeric AS pos_charge, sessionbill :: numeric AS neg_charge,
		(sessiontime) AS duration
		FROM cc_call_v AS call, cc_shopsessions 
		WHERE call.cardid = cc_shopsessions.card
		  AND call.starttime >= cc_shopsessions.starttime AND (cc_shopsessions.endtime IS NULL OR call.starttime <= cc_shopsessions.endtime)
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
