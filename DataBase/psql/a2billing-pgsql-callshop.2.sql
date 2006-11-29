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
		WHEN cur_card_id IS NULL THEN 1
		WHEN cc_booth.disabled THEN 5
		WHEN cc_card.lastuse > cc_shopsessions.starttime AND cc_card.activated THEN 4
		WHEN cc_card.lastuse > cc_shopsessions.starttime THEN 6
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

DROP view cc_session_invoice;
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
		-- Session start
	UNION SELECT starttime, 'Session start' AS descr,  id AS sid,
		booth AS boothid, NULL AS f2, NULL as cnum,
		NULL AS pos_charge, NULL AS neg_charge, NULL AS duration
		FROM cc_shopsessions
	UNION SELECT endtime, 'Session end' AS descr,  id AS sid,
		booth AS boothid, NULL AS f2, NULL as cnum,
		NULL AS pos_charge, NULL AS neg_charge, NULL AS duration
		FROM cc_shopsessions WHERE endtime IS NOT NULL
		-- Refills
	UNION SELECT cc_agentrefill.date AS starttime, 'Credit' AS descr, cc_shopsessions.id AS sid,
		booth AS boothid, NULL AS f2, NULL as cnum,
		cc_agentrefill.credit AS pos_charge, NULL as neg_charge,
		NULL as duration
		FROM cc_shopsessions, cc_agentrefill
		WHERE cc_shopsessions.card = cc_agentrefill.card_id AND
			( cc_agentrefill.boothid IS NULL OR cc_shopsessions.booth = cc_agentrefill.boothid) AND
			cc_agentrefill.credit > 0.0 AND
			cc_shopsessions.starttime <= cc_agentrefill.date AND
			(cc_shopsessions.endtime IS NULL OR cc_shopsessions.endtime >= cc_agentrefill.date)
		-- Payments
	UNION SELECT cc_agentrefill.date AS starttime, 'Payment' AS descr, cc_shopsessions.id AS sid,
		booth AS boothid, NULL AS f2, NULL as cnum,
		NULL AS pos_charge, (0- cc_agentrefill.credit) AS neg_charge,
		NULL as duration
		FROM cc_shopsessions, cc_agentrefill
		WHERE cc_shopsessions.card = cc_agentrefill.card_id AND
			( cc_agentrefill.boothid IS NULL OR cc_shopsessions.booth = cc_agentrefill.boothid) AND
			cc_agentrefill.credit < 0.0 AND
			cc_shopsessions.starttime <= cc_agentrefill.date AND
			(cc_shopsessions.endtime IS NULL OR cc_shopsessions.endtime >= cc_agentrefill.date);


CREATE OR REPLACE FUNCTION conv_currency(money_sum NUMERIC, from_cur CHAR(3), to_cur CHAR(3)) RETURNS NUMERIC
	AS $$
	SELECT  (($1 * from_rate) / to_rate)
	FROM 	(SELECT DISTINCT ON (b.currency) a.value AS from_rate,  b.value AS to_rate
		FROM cc_currencies AS a, cc_currencies AS b
		WHERE a.currency = $2 AND b.currency = $3 AND a.basecurrency = b.basecurrency ) AS foo
		;
	$$
	LANGUAGE SQL STABLE STRICT;
-- eof
