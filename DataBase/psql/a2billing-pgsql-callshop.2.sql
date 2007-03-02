-- Additional functions/views for callshop feature
-- Copyright (c) 2006 P.Christeas <p_christeas@yahoo.com>
--

-- This file contains elements without data. It is safe to call
-- it on a db loaded with data.

DROP VIEW cc_booth_v;
CREATE OR REPLACE VIEW cc_booth_v AS
	SELECT cc_booth.id AS id, cc_booth.agentid AS owner,
		cc_booth.name, cc_booth.location,
		cc_card.credit, cc_card.currency,
		def_card_id, cur_card_id, cc_shopsessions.starttime,
		cc_card.username AS in_now,
		(CASE WHEN def_card_id IS NULL THEN 0
		WHEN cc_booth.disabled THEN 5
		WHEN cur_card_id IS NULL THEN 1
		WHEN cc_card.credit <> 0.0 AND cc_card.activated THEN 4
		WHEN cc_card.credit <> 0.0 THEN 6
		WHEN cc_card.activated THEN 3
		ELSE 2
		END) AS state,
		(SELECT COALESCE(SUM(sessiontime),0) FROM cc_call 
			WHERE username = cc_card.username
			AND starttime >= cc_shopsessions.starttime) AS secs
	FROM (cc_booth LEFT OUTER JOIN cc_card ON cc_booth.cur_card_id = cc_card.id)
		LEFT OUTER JOIN cc_shopsessions ON cc_shopsessions.booth=cc_booth.id AND
		cc_shopsessions.endtime IS NULL;
	
	
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
	
CREATE OR REPLACE RULE cc_booth_update_o AS ON UPDATE TO cc_booth_v DO INSTEAD NOTHING;

CREATE OR REPLACE RULE cc_booth_update2 AS ON UPDATE TO cc_booth_v 
	WHERE NEW.state=2 AND OLD.state <> 2
	DO INSTEAD UPDATE cc_card SET activated= 'f' 
			FROM cc_agent, cc_booth 
			WHERE cc_booth.cur_card_id= cc_card.id AND
				cc_booth.id = OLD.id AND
				cc_booth.agentid = OLD.owner;
				
CREATE OR REPLACE RULE cc_booth_update3 AS ON UPDATE TO cc_booth_v WHERE NEW.state=3 AND
	OLD.state <> 3
	DO INSTEAD UPDATE cc_card SET activated= 't' 
			FROM cc_agent, cc_booth 
			WHERE cc_booth.cur_card_id= cc_card.id AND
				cc_booth.id = OLD.id AND
				cc_booth.agentid = OLD.owner;

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

-------------------------------------------------------
------------ Triggers ------------------



CREATE OR REPLACE FUNCTION cc_booth_set_card() RETURNS trigger AS $$
	DECLARE
		bint bigint;
		money numeric;
	BEGIN
		-- Remove old card first
	IF TG_OP = 'UPDATE'  THEN
		IF(NEW.def_card_id <> OLD.def_card_id ) THEN
		UPDATE cc_agent_cards SET def = 'f' 
			WHERE OLD.def_card_id IS NOT NULL AND card_id = OLD.def_card_id;
	END IF; END IF;
	
	PERFORM id FROM cc_booth WHERE 
		NEW.def_card_id IS NOT NULL AND 
		NEW.id <> id  AND 
		( cur_card_id = NEW.def_card_id OR
		def_card_id = NEW.def_card_id );
	IF FOUND THEN
		RAISE EXCEPTION 'Default card already used';
	END IF;
	
	IF NEW.def_card_id IS NOT NULL THEN
		PERFORM  card_id FROM cc_agent_cards 
			WHERE card_id = NEW.def_card_id AND
				agentid = NEW.agentid;
		IF NOT FOUND THEN
			RAISE EXCEPTION 'Card id does not belong to agent %.', NEW.agentid;
		END IF;
	END IF;
	
		-- Then, update the card
	IF NEW.def_card_id = NULL THEN
		NEW.cur_card_id = NULL;
	ELSE
		UPDATE cc_agent_cards SET def = 't' WHERE card_id = NEW.def_card_id;
		
		IF NEW.cur_card_id IS NOT NULL THEN
			PERFORM  card_id FROM cc_agent_cards 
			WHERE card_id = NEW.cur_card_id AND
				agentid = NEW.agentid;
			IF NOT FOUND THEN
				RAISE EXCEPTION 'Card id % does not belong to agent %.', NEW.cur_card_id, NEW.agentid;
			END IF;
			PERFORM id FROM cc_shopsessions WHERE
				booth = NEW.id AND card = NEW.cur_card_id AND
				endtime IS NULL;
			IF NOT FOUND THEN
				RAISE LOG 'New session for booth %', NEW.id;
				INSERT INTO cc_shopsessions (booth,card,state)
					VALUES (NEW.id, NEW.cur_card_id, 'Open');
				SELECT credit INTO money FROM cc_card WHERE id = NEW.cur_card_id;
				IF money <> 0 THEN
					PERFORM carry_session(currval('cc_shopsessions_id_seq'),NEW.agentid);
				END IF;
			END IF;
		ELSE
			IF TG_OP = 'UPDATE' THEN
				SELECT id INTO bint FROM cc_shopsessions
					WHERE card = OLD.cur_card_id AND endtime IS NULL
					AND booth = NEW.id;
				IF FOUND THEN -- Must clear the session.
					SELECT credit INTO money FROM cc_card
						WHERE id = OLD.cur_card_id;
					IF OLD.cur_card_id = NEW.def_card_id AND
						money <> 0 THEN
						RAISE EXCEPTION 'Cannot clear session % because it contains non-empty, default card %', bint, OLD.cur_card_id;
					END IF;
					-- If session has money, close it with 0
					PERFORM pay_session(bint,NEW.agentid,true,true);
				END IF;
			END IF;
		END IF;

	END IF;
	RETURN NEW;
	END; $$
LANGUAGE plpgsql ;

CREATE OR REPLACE FUNCTION cc_booth_no_agent_update() RETURNS trigger AS $$
BEGIN
	IF (NEW.agentid <> OLD.agentid ) THEN
		RAISE EXCEPTION 'The agentid of a booth can NOT change!' ;
	END IF;
	RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION cc_booth_remove_def_card() RETURNS trigger AS $$
BEGIN
	UPDATE cc_agent_cards SET def = 'f' 
		WHERE OLD.def_card_id IS NOT NULL AND card_id = OLD.def_card_id;
	RETURN OLD;
END; $$
LANGUAGE plpgsql;


CREATE OR REPLACE FUNCTION cc_booth_upd_callerid() RETURNS trigger AS $$
	BEGIN
		-- Remove old card first
	IF TG_OP = 'UPDATE'  THEN
		IF (OLD.callerid IS NOT NULL AND OLD.callerid <> '') AND 
			(NEW.cur_card_id IS NULL OR (NEW.cur_card_id <> OLD.cur_card_id) OR (NEW.callerid <> OLD.callerid))
		THEN
			DELETE FROM cc_callerid WHERE cid = OLD.callerid 
				AND id_cc_card = OLD.cur_card_id;
			IF NOT FOUND THEN
				RAISE WARNING 'Caller id "%" not found for card %', OLD.callerid, OLD.cur_card_id;
			END IF;
		END IF;
	ELSIF TG_OP = 'DELETE' THEN
		IF (OLD.callerid IS NOT NULL AND OLD.callerid <> '') AND 
			(OLD.cur_card_id IS NOT NULL)
		THEN
			DELETE FROM cc_callerid WHERE cid = OLD.callerid 
				AND id_cc_card = OLD.cur_card_id;
		END IF;
	END IF;
	
	IF TG_OP = 'INSERT' OR TG_OP = 'UPDATE' THEN
		IF (NEW.cur_card_id IS NOT NULL) AND NEW.callerid IS NOT NULL THEN
			PERFORM 1 FROM cc_callerid WHERE cid = NEW.callerid AND id_cc_card = NEW.cur_card_id;
			
			IF NOT FOUND THEN
				INSERT INTO cc_callerid (cid, id_cc_card) VALUES (NEW.callerid, NEW.cur_card_id);
			END IF;
		END IF;
	END IF;
	RETURN NEW;
END; $$
LANGUAGE plpgsql;

DROP TRIGGER cc_booth_check_def ON cc_booth;
DROP TRIGGER cc_booth_rm_card ON cc_booth;
DROP TRIGGER cc_booth_check_agent ON cc_booth;
DROP TRIGGER cc_booth_upd_callerid_t ON cc_booth;

CREATE TRIGGER cc_booth_check_def BEFORE INSERT OR UPDATE ON cc_booth
	FOR EACH ROW EXECUTE PROCEDURE cc_booth_set_card();

CREATE TRIGGER cc_booth_rm_card BEFORE DELETE ON cc_booth
	FOR EACH ROW EXECUTE PROCEDURE cc_booth_remove_def_card();

CREATE TRIGGER cc_booth_check_agent BEFORE UPDATE ON cc_booth
	FOR EACH ROW EXECUTE PROCEDURE cc_booth_no_agent_update();

CREATE TRIGGER cc_booth_upd_callerid_t AFTER INSERT OR UPDATE OR DELETE ON cc_booth
	FOR EACH ROW EXECUTE PROCEDURE cc_booth_upd_callerid();


-------------------------
--     Refill
-------------------------

CREATE OR REPLACE FUNCTION cc_agent_refill_it() RETURNS trigger AS $$
  BEGIN
  	IF NEW.agentid IS NULL THEN
  		RAISE EXCEPTION 'agentid cannot be NULL';
  	END IF;
  	
  	IF NEW.boothid IS NOT NULL THEN
  		SELECT INTO NEW.card_id cur_card_id FROM cc_booth WHERE 
  			id = NEW.boothid AND
  			cur_card_id IS NOT NULL;
  		IF NOT FOUND THEN
  			RAISE EXCEPTION 'No such booth with loaded card ';
  		END IF;
	END IF;
  	
  	IF NEW.card_id IS NULL THEN
  		RAISE EXCEPTION 'card_id cannot be NULL';
  	END IF;
  	PERFORM card_id FROM cc_agent_cards WHERE
  		card_id = NEW.card_id AND agentid = NEW.agentid;
  	IF NOT FOUND THEN
  		RAISE EXCEPTION 'No such card for this agent';
  	END IF;
  	
  	PERFORM id FROM cc_agent
  		WHERE id = NEW.agentid AND credit + climit >= NEW.credit ;
  	IF NOT FOUND THEN
  		RAISE EXCEPTION 'Agent does not have enough credit';
  	END IF;
  	
  	IF NEW.carried = FALSE THEN
		UPDATE cc_agent SET credit = credit - NEW.credit WHERE id = NEW.agentid;
		IF NOT FOUND THEN
			RAISE EXCEPTION 'Failed to update agents credit';
		END IF;
		UPDATE cc_card SET credit = credit + NEW.credit WHERE id = NEW.card_id;
	END IF;
  	RETURN NEW;
  END;
  $$ LANGUAGE plpgsql;
  
DROP TRIGGER cc_agent_refill_it ON cc_agentrefill;

CREATE TRIGGER cc_agent_refill_it BEFORE INSERT ON cc_agentrefill
	FOR EACH ROW EXECUTE PROCEDURE cc_agent_refill_it();


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
			UPDATE cc_shopsessions SET endtime = now() , state = 'Closed' WHERE
				card = cid AND id = sid;
			UPDATE cc_card SET activated = 'f' WHERE id = cid;
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
			VALUES(cid, agentid_p,ssum, true, 3);
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


-- Gettext
-- Convert some text from 'C' to another language..

CREATE OR REPLACE FUNCTION gettext( ptxt TEXT, plang VARCHAR(10)) RETURNS TEXT AS $$
DECLARE
	res TEXT;
BEGIN
	IF (plang = 'C' ) OR (plang = 'en') THEN
		RETURN ptxt;
	END IF;
	SELECT cc_texts.txt INTO res FROM cc_texts 
		WHERE cc_texts.lang = plang AND cc_texts.id IN 
			(SELECT id FROM cc_texts WHERE cc_texts.lang = 'C' AND cc_texts.txt = ptxt);
	IF FOUND THEN
		RETURN res;
	ELSE
		RETURN ptxt;
	END IF;
END; $$ LANGUAGE plpgsql STRICT STABLE;

CREATE OR REPLACE FUNCTION gettexti( pid INTEGER, plang VARCHAR(10)) RETURNS TEXT AS $$
DECLARE
	res TEXT;
BEGIN
	SELECT cc_texts.txt INTO res FROM cc_texts 
		WHERE cc_texts.lang = plang AND cc_texts.id  = pid;
	IF FOUND THEN
		RETURN res;
	END IF;
	SELECT cc_texts.txt INTO res FROM cc_texts
		WHERE cc_texts.lang = 'C' AND cc_texts.id = pid;
	RETURN res;
END; $$ LANGUAGE plpgsql STRICT STABLE;

-- The opposite of gettext! Insert some new text into the database and get its id..
-- The text MUST be in English = 'C' and this function should always return a valid
-- id as long as ptxt IS NOT NULL..
CREATE OR REPLACE FUNCTION gettext_ri(ptxt TEXT) returns integer AS $$
	DECLARE res integer;
BEGIN
	SELECT id INTO res FROM cc_texts WHERE lang = 'C' AND txt = ptxt;
	IF FOUND THEN RETURN res; END IF;
	
	SELECT INTO res MAX(id) FROM cc_texts;
	IF res IS NULL THEN res := 0; END IF;
	res := res + 1;
	
	INSERT INTO cc_texts(id,txt,lang,src) VALUES(res,ptxt,'C',2);
	RETURN res;
END; $$ LANGUAGE plpgsql STRICT VOLATILE;

-- Optimized version of gettext_r.
-- Note: this function may be faster, but will raise an exception for non-existent
-- strings..

CREATE OR REPLACE FUNCTION gettext_r(ptxt TEXT) returns integer AS $$
	DECLARE res integer;
BEGIN
	SELECT id INTO res FROM cc_texts WHERE lang = 'C' AND txt = ptxt;
	IF NOT FOUND THEN 
		RAISE EXCEPTION 'Text ''%'' not found in database!',ptxt;
	END IF;
	
	RETURN res; 
END; $$ LANGUAGE plpgsql STRICT STABLE;


CREATE OR REPLACE FUNCTION gettext_add_missing(lang VARCHAR(10)) RETURNS void AS  $$
	INSERT INTO cc_texts (id, txt, src, lang) SELECT id, txt, 0 AS src, $1 AS lang FROM cc_texts 
		WHERE lang = 'C' AND  id NOT IN (SELECT id FROM cc_texts WHERE lang = $1 );
$$ LANGUAGE SQL STRICT;

--------------------

CREATE OR REPLACE FUNCTION cc_agentpay_it() RETURNS trigger AS $$
BEGIN
	UPDATE cc_agent SET credit = credit + NEW.credit WHERE id = NEW.agentid;
	IF NOT FOUND THEN
		RAISE EXCEPTION 'Failed to update agent''s credit';
	END IF;
	RETURN NEW;
END ; $$ LANGUAGE plpgsql STRICT;

CREATE OR REPLACE FUNCTION cc_agentpay_itu() RETURNS trigger AS $$
BEGIN
	IF NEW.agentid <> OLD.agentid THEN
		RAISE EXCEPTION 'Change of agents for payments is forbidden!';
	END IF;
	UPDATE cc_agent SET credit = credit + NEW.credit - OLD.credit WHERE id = NEW.agentid;
	IF NOT FOUND THEN
		RAISE EXCEPTION 'Failed to update agent''s credit';
	END IF;
	RETURN NEW;
END ; $$ LANGUAGE plpgsql STRICT;

CREATE OR REPLACE FUNCTION cc_agentpay_itd() RETURNS trigger AS $$
BEGIN
	UPDATE cc_agent SET credit = credit - OLD.credit WHERE id = OLD.agentid;
	IF NOT FOUND THEN
		RAISE EXCEPTION 'Failed to update agent''s credit';
	END IF;
	RETURN OLD;
END ; $$ LANGUAGE plpgsql STRICT;

DROP TRIGGER cc_agent_pay_it ON cc_agentpay;
DROP TRIGGER cc_agent_pay_itd ON cc_agentpay;
DROP TRIGGER cc_agent_pay_itu ON cc_agentpay;

CREATE TRIGGER cc_agent_pay_it BEFORE INSERT ON cc_agentpay
	FOR EACH ROW EXECUTE PROCEDURE cc_agentpay_it();
CREATE TRIGGER cc_agent_pay_itu BEFORE UPDATE ON cc_agentpay
	FOR EACH ROW EXECUTE PROCEDURE cc_agentpay_itu();
CREATE TRIGGER cc_agent_pay_itd BEFORE DELETE ON cc_agentpay
	FOR EACH ROW EXECUTE PROCEDURE cc_agentpay_itd();

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
UNION	SELECT agentid, date, pay_type, 'Money from customer' as descr, card_id, credit AS pos_credit, NULL AS neg_credit, 0-credit
		FROM cc_agentrefill WHERE credit >=0 AND carried = false
UNION	SELECT agentid, date, pay_type, 'Pay back customer' as descr, card_id, NULL AS pos_credit, 0-credit AS neg_credit, 0-credit
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
