	
	
CREATE OR REPLACE FUNCTION format_currency(money_sum NUMERIC, from_cur CHAR(3), to_cur CHAR(3)) RETURNS text
	AS $$
	SELECT CASE WHEN sign_pre THEN 
			csign || ' ' || to_char( ($1 * from_rate) / to_rate, cformat)
		ELSE
			to_char( ($1 * from_rate) / to_rate, cformat) || ' ' || csign
		END
	FROM 	(SELECT DISTINCT ON (b.currency) a.value AS from_rate,  b.value AS to_rate, b.cformat, 
			COALESCE(b.csign,b.currency) AS csign , b.sign_pre 
		FROM cc_currencies AS a, cc_currencies AS b
		WHERE a.currency = $2 AND b.currency = $3 AND a.basecurrency = b.basecurrency ) AS foo
		;
	$$
	LANGUAGE SQL STABLE STRICT;
	
/*WHEN abs(($1 * from_rate) / to_rate) <= 0.10 AND sign_pre THEN
			csign || 'c ' || to_char( ($1 * from_rate*100.0) / to_rate, cformat)*/
		
CREATE OR REPLACE FUNCTION format_currency2(money_sum NUMERIC, from_cur CHAR(3), to_cur CHAR(3)) RETURNS text
	AS $$
	SELECT CASE WHEN sign_pre THEN 
			csign || ' ' || to_char( ($1 * from_rate) / to_rate, cformat2)
		ELSE
			to_char( ($1 * from_rate) / to_rate, cformat2) || ' ' || csign
		END
	FROM (SELECT DISTINCT ON (b.currency) a.value AS from_rate,  b.value AS to_rate, b.cformat, b.cformat2, 
			COALESCE(b.csign,b.currency) AS csign , b.sign_pre 
		FROM cc_currencies AS a, cc_currencies AS b
		WHERE a.currency = $2 AND b.currency = $3 AND a.basecurrency = b.basecurrency ) AS foo
		;
$$ LANGUAGE SQL STABLE STRICT;


CREATE OR REPLACE FUNCTION format_currency(money_sum DOUBLE PRECISION, from_cur CHAR(3), to_cur CHAR(3)) RETURNS text
	AS $$
	SELECT CASE WHEN sign_pre THEN 
			csign || ' ' || to_char( ($1 * from_rate) / to_rate, cformat)
		ELSE
			to_char( ($1 * from_rate) / to_rate, cformat) || ' ' || csign
		END
	FROM 	(SELECT DISTINCT ON (b.currency) a.value AS from_rate,  b.value AS to_rate, b.cformat, 
			COALESCE(b.csign,b.currency) AS csign , b.sign_pre 
		FROM cc_currencies AS a, cc_currencies AS b
		WHERE a.currency = $2 AND b.currency = $3 AND a.basecurrency = b.basecurrency ) AS foo
		;
	$$
	LANGUAGE SQL STABLE STRICT;

-- CREATE OR REPLACE FUNCTION booth_start(booth bigint, agent_id bigint) RETURNS bigint
-- 	AS $$
-- 		UPDATE cc_card SET activated= 't' 
-- 			FROM cc_agent, cc_booth 
-- 			WHERE cc_booth.cur_card_id= cc_card.id AND
-- 				cc_booth.id = $1 AND
-- 				cc_booth.agentid = $2;
-- 		select COUNT(cc_card.id) FROM cc_card,cc_agent, cc_booth 
-- 			WHERE cc_booth.cur_card_id= cc_card.id AND
-- 				cc_booth.id = $1 AND
-- 				cc_booth.agentid = $2;
-- 	$$ LANGUAGE SQL VOLATILE STRICT;
	

-- TODO: use verification for card owner!
-- CREATE OR REPLACE RULE cc_booth_update_d AS ON UPDATE TO cc_booth_v WHERE NEW.cur_card_id= OLD.def_card_id 
-- 	AND OLD.def_card_id IS NOT NULL
-- 	DO INSTEAD UPDATE cc_booth SET cur_card_id = def_card_id 
-- 			FROM cc_card, cc_agent_cards
-- 			WHERE cc_booth.def_card_id= cc_card.id AND
-- 				cc_booth.id = OLD.id AND
-- 				cc_booth.agentid = OLD.owner AND
-- 				cc_agent_cards.card_id = cc_card.id AND
-- 				cc_agent_cards.agentid = OLD.owner AND
-- 				cc_agent_cards.def = 't' ;

---- TODO: set the caller id !

-- CREATE OR REPLACE RULE cc_booth_update_d_fill_booth AS ON UPDATE TO cc_booth_v 
-- 	WHERE NEW.cur_card_id IS NOT NULL
-- 		AND OLD.cur_card_id IS NULL
-- 	DO INSTEAD UPDATE cc_booth SET cur_card_id = NEW.cur_card_id 
-- 			FROM cc_card, cc_agent_cards
-- 			WHERE NEW.cur_card_id= cc_card.id AND
-- 				(OLD.def_card_id IS NULL OR NEW.cur_card_id <> OLD.def_card_id ) AND
-- 				cc_booth.id = OLD.id AND
-- 				cc_booth.agentid = OLD.owner AND
-- 				cc_agent_cards.card_id = cc_card.id AND
-- 				cc_agent_cards.agentid = OLD.owner AND
-- 				cc_agent_cards.def = 'f' ;

/*CREATE OR REPLACE RULE cc_booth_update_d_empty_booth AS ON UPDATE TO cc_booth_v 
	WHERE NEW.cur_card_id IS NULL
		AND OLD.cur_card_id IS NOT NULL
	DO INSTEAD UPDATE cc_booth SET cur_card_id = NULL ;*/
	
-- 			FROM cc_card, cc_agent_cards
-- 			WHERE NEW.cur_card_id= cc_card.id AND
-- 				(OLD.def_card_id IS NULL OR NEW.cur_card_id <> OLD.def_card_id ) AND
-- 				cc_booth.id = OLD.id AND
-- 				cc_booth.agentid = OLD.owner AND
-- 				cc_agent_cards.card_id = cc_card.id AND
-- 				cc_agent_cards.agentid = OLD.owner AND
-- 				cc_agent_cards.def = 'f' ;

-- Not all the fields appear in this view:
-- It could be adjusted to service a different user that will not have
-- access to all the fields.


CREATE OR REPLACE VIEW cc_card_agent_v AS
	SELECT cc_card.id, expirationdate, username, useralias, firstname, lastname, address,
		credit, activated, runservice, autorefill, initialbalance, typepaid, firstusedate,
		inuse , currency, lastuse, language, creditlimit, vat,
		cc_agent_cards.agentid, cc_agent_cards.def,
		cc_booth.id AS now_id , booth2.id AS def_id, cc_booth.name AS now_name, booth2.name AS def_name
		FROM (cc_card  LEFT OUTER JOIN cc_booth ON cc_booth.cur_card_id = cc_card.id) 
			LEFT OUTER JOIN cc_booth AS booth2 ON cc_card.id = booth2.def_card_id,
			cc_agent_cards
		WHERE cc_card.id = cc_agent_cards.card_id;
		
		
		
CREATE OR REPLACE RULE cc_card_agent_v_upd AS ON UPDATE TO cc_card_agent_v 
	DO INSTEAD UPDATE cc_card SET username = NEW.username, useralias = NEW.useralias,
		firstname=NEW.firstname, lastname = NEW.lastname, address = NEW.address,
		activated= NEW.activated, language = NEW.language, typepaid= NEW.typepaid,
		runservice = NEW.runservice, autorefill = NEW.autorefill, creditlimit = NEW.creditlimit,
		vat = NEW.vat, currency = NEW.currency 
	WHERE cc_card.id = NEW.id AND OLD.id = NEW.id;
	
CREATE OR REPLACE RULE cc_card_agent_v_upd2 AS ON UPDATE TO cc_card_agent_v DO INSTEAD NOTHING;
-- CREATE OR REPLACE FUNCTION set_booth_defcard ( booth bigint, card bigint) RETURNS boolean AS $$
-- 	BEGIN -- do not!
-- 	UPDATE cc_booth SET def_card_id = $2 FROM cc_agent_cards  WHERE
-- 		cc_booth.id = $1 AND
-- 		cc_agent_cards.def='f' AND
-- 		def_card_id IS NULL AND
-- 		cc_agent_cards.card_id = $2 AND
-- 		cc_agent_cards.agentid = agent_id AND 
-- 		NOT EXISTS (SELECT 1 FROM cc_booth WHERE cur_card_id = $2 ) AND
-- 		NOT EXISTS (SELECT 1 FROM cc_booth WHERE def_card_id = $2 );
-- 	IF NOT FOUND THEN
-- 		RAISE NOTICE 'Cannot set default card';
-- 		RETURN false;
-- 	END IF;
-- 	UPDATE cc_agent_cards SET def = 't' WHERE card_id = $2;
-- 	RETURN true;
-- 	END;
-- $$ LANGUAGE plpgsql VOLATILE;





-- One view for all: have all the session transactions in one table.

-- DROP view cc_session_invoice;
CREATE OR REPLACE VIEW cc_session_invoice AS
		-- Calls
	SELECT cc_call.starttime, 'Call' AS descr, cc_shopsessions.id AS sid,
		cc_shopsessions.booth AS boothid,
		cc_call.destination AS f2,
		cc_call.calledstation AS cnum,
		NULL :: numeric AS pos_charge, sessionbill :: numeric AS neg_charge,
		(sessiontime) AS duration
		FROM cc_call,cc_card, cc_shopsessions 
		WHERE cc_call.username = cc_card.username AND cc_shopsessions.card = cc_card.id
			AND cc_call.starttime >= cc_shopsessions.starttime AND (cc_shopsessions.endtime IS NULL OR cc_call.starttime <= cc_shopsessions.endtime)
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
	UNION SELECT cc_charge.creationdate AS starttime, cc_texts.txt AS descr, cc_shopsessions.id AS sid,
		booth AS boothid,cc_charge.description AS f2, NULL as cnum,
		NULL AS pos_charge, cc_charge.amount as neg_charge,
		NULL as duration
		FROM cc_shopsessions, cc_charge
			LEFT JOIN cc_texts ON cc_texts.id = cc_charge.chargetype AND cc_texts.lang = 'C'
		WHERE cc_shopsessions.card = cc_charge.id_cc_card AND
			cc_shopsessions.starttime <= cc_charge.creationdate AND
			(cc_shopsessions.endtime IS NULL OR cc_shopsessions.endtime >= cc_charge.creationdate);

CREATE OR REPLACE FUNCTION conv_currency(money_sum NUMERIC, from_cur CHAR(3), to_cur CHAR(3)) RETURNS NUMERIC
	AS $$
	SELECT  (($1 * from_rate) / to_rate)
	FROM 	(SELECT DISTINCT ON (b.currency) a.value AS from_rate,  b.value AS to_rate
		FROM cc_currencies AS a, cc_currencies AS b
		WHERE a.currency = $2 AND b.currency = $3 AND a.basecurrency = b.basecurrency ) AS foo
		;
	$$
	LANGUAGE SQL STABLE STRICT;
	


CREATE OR REPLACE FUNCTION pay_session( sid bigint, agentid_p bigint, do_close boolean, do_carry boolean) RETURNS NUMERIC
	AS $$
	DECLARE
		ssum NUMERIC;
		cid bigint;
		bid bigint;
		ptype integer;
	BEGIN
		*-* sth is wrong..
		SELECT cc_card.credit, cc_card.id, cc_shopsessions.booth INTO ssum, cid, bid FROM cc_card, cc_shopsessions, cc_agent_cards
			WHERE cc_card.id = cc_shopsessions.card AND
				cc_agent_cards.card_id = cc_card.id AND cc_agent_cards.agentid = agentid_p AND
				cc_shopsessions.id = sid ;
		IF NOT FOUND THEN
			RAISE EXCEPTION 'No such session for agent';
		END IF;
		IF do_carry THEN
			SELECT id INTO ptype FROM cc_paytypes WHERE preset = 'carry';
		ELSE	
			SELECT id INTO ptype FROM cc_paytypes WHERE preset = 'settle';
		END IF;
		INSERT INTO cc_agentrefill(card_id, agentid, credit, carried, pay_type)
			VALUES(cid, agentid_p,0-ssum, do_carry, ptype);
		IF do_close THEN
			--UPDATE cc_shopsessions SET endtime = now() , state = 'Closed' WHERE
			--	card = cid AND id = sid;
			--UPDATE cc_card SET activated = 'f' WHERE id = cid;
			UPDATE cc_booth SET cur_card_id = NULL WHERE id = bid;
		END IF;
	RETURN ssum;
	END; $$
LANGUAGE plpgsql STRICT;

-- Modified version of the pay_session() to use when crediting the new session with the sum
-- carried from a previous use of the card
CREATE OR REPLACE FUNCTION carry_session( sid bigint, agentid_p bigint) RETURNS NUMERIC
	AS $$
	DECLARE
		ssum NUMERIC;
		cid bigint;
		bid bigint;
	BEGIN
		SELECT cc_card.credit, cc_card.id, cc_shopsessions.booth INTO ssum, cid, bid FROM cc_card, cc_shopsessions, cc_agent_cards
			WHERE cc_card.id = cc_shopsessions.card AND
				cc_agent_cards.card_id = cc_card.id AND cc_agent_cards.agentid = agentid_p AND
				cc_shopsessions.id = sid ;
		IF NOT FOUND THEN
			RAISE EXCEPTION 'No such session for agent';
		END IF;
		INSERT INTO cc_agentrefill(card_id, agentid, credit, carried, pay_type)
			VALUES(cid, agentid_p,ssum, true, 3); /* *-* why '3' ? */
	RETURN ssum;
	END; $$
	LANGUAGE plpgsql STRICT;

CREATE OR REPLACE VIEW cc_closed_sessions AS
	SELECT cc_shopsessions.id AS sid, cc_shopsessions.card, (SUM(cc_session_invoice.pos_charge) - SUM(cc_session_invoice.neg_charge)) AS ssum
		FROM cc_shopsessions, cc_session_invoice WHERE
		cc_shopsessions.endtime IS NOT NULL AND
		cc_shopsessions.id = cc_session_invoice.sid 
		GROUP by cc_shopsessions.id,cc_shopsessions.card;

CREATE OR REPLACE VIEW cc_session_problems AS
	SELECT cc_closed_sessions.sid, cc_closed_sessions.card, cc_agent_cards.agentid, cc_agent_cards.def, 'Imbalance'::text AS Problem
		FROM  cc_closed_sessions, cc_agent_cards WHERE
			cc_agent_cards.card_id = cc_closed_sessions.card
			AND cc_closed_sessions.ssum <> 0 
	UNION SELECT cc_shopsessions.id, cc_shopsessions.card, cc_agent_cards.agentid, cc_agent_cards.def, 'Hanging open'::text AS Problem
		FROM cc_shopsessions,cc_agent_cards, cc_booth
		WHERE cc_shopsessions.card = cc_agent_cards.card_id
			AND cc_booth.id = cc_shopsessions.booth
			AND cc_shopsessions.endtime IS NULL
			AND (cc_booth.cur_card_id IS NULL OR cc_booth.cur_card_id <> cc_shopsessions.card)
	UNION SELECT cc_shopsessions.id, cc_shopsessions.card, cc_agent_cards.agentid, cc_agent_cards.def, 'Overlap with '  || ss2.id::text AS Problem
		FROM cc_shopsessions, cc_shopsessions AS ss2, cc_agent_cards
		WHERE cc_shopsessions.card = cc_agent_cards.card_id
			AND cc_shopsessions.booth = ss2.booth
			AND cc_shopsessions.id <> ss2.id
			AND ss2.starttime >= cc_shopsessions.starttime
			AND cc_shopsessions.endtime > ss2.starttime
	UNION SELECT cc_shopsessions.id, cc_shopsessions.card, cc_agent_cards.agentid, cc_agent_cards.def, 'End before start'::text AS Problem
		FROM cc_shopsessions, cc_agent_cards
		WHERE cc_shopsessions.card = cc_agent_cards.card_id
		AND starttime > endtime;


CREATE OR REPLACE FUNCTION divide_time(div1 INTERVAL, div2 INTERVAL) RETURNS FLOAT AS $$
	SELECT ( EXTRACT(EPOCH FROM $1) / EXTRACT(EPOCH FROM $2)) ;
	$$ LANGUAGE SQL IMMUTABLE STRICT;

----- Compatibility note:
--- We could use 'TMDay' to show the localized day eg. This however will require Postgres >= 8.2.0

CREATE OR REPLACE FUNCTION fmt_date( date TIMESTAMP) RETURNS TEXT AS $$
	SELECT to_char($1, 'DD/MM/YYYY HH24:MM');
	$$ LANGUAGE SQL IMMUTABLE STRICT;
	
CREATE OR REPLACE VIEW cc_tariffrates_v AS SELECT cc_tariffgroup.id AS tg_id, cc_tariffgroup.tariffgroupname AS tg_name, 
	cc_tariffplan.id AS tp_id, cc_tariffplan.tariffname AS tp_name,
	cc_tariffplan.startingdate AS tp_start, cc_tariffplan.expirationdate AS tp_end,
	cc_ratecard.id AS rc_id,
	cc_ratecard.dialprefix, cc_ratecard.destination, cc_ratecard.rateinitial, 
	(cc_ratecard.connectcharge + cc_ratecard.disconnectcharge) AS charge_once,
	cc_ratecard.billingblock

	FROM cc_tariffgroup, cc_tariffgroup_plan, cc_tariffplan, cc_ratecard
	
	WHERE cc_tariffgroup.id = cc_tariffgroup_plan.idtariffgroup AND
		cc_tariffplan.id = cc_tariffgroup_plan.idtariffplan AND
		cc_ratecard.idtariffplan = cc_tariffplan.id;

--------------------

-- Rm'ed: charges

CREATE OR REPLACE VIEW cc_agent_daycalls_v AS
SELECT count(*) as num, sum(sessionbill) AS charges , sum(stoptime-starttime) as totaltime,
	date_trunc('day',starttime) AS day,
	cc_agent_cards.agentid AS agentid
	FROM cc_call, cc_card, cc_agent_cards
	WHERE cc_call.username = cc_card.username AND cc_card.id = cc_agent_cards.card_id
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

CREATE OR REPLACE VIEW cc_agent_money_v AS
	SELECT agentid, date, pay_type, descr, NULL::bigint AS card_id, NULL::NUMERIC AS pos_credit, credit AS neg_credit, credit 
		FROM cc_agentpay WHERE credit >=0
UNION SELECT agentid, date, pay_type, descr, NULL::bigint AS card_id, 0-credit AS pos_credit, NULL  AS neg_credit, credit 
		FROM cc_agentpay WHERE credit <0
UNION SELECT agentid, date, pay_type, 'Money from customer' as descr, card_id, credit AS pos_credit, NULL AS neg_credit, 0-credit
		FROM cc_agentrefill WHERE credit >=0 AND carried = false
UNION SELECT agentid, date, pay_type, 'Pay back customer' as descr, card_id, NULL AS pos_credit, 0-credit AS neg_credit, 0-credit
		FROM cc_agentrefill WHERE credit <0 AND carried = false;

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




-- eof
