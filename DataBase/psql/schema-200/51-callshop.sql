-- Additional functions/views for callshop feature
-- Copyright (c) 2006-2008 P.Christeas <p_christeas@yahoo.com>
--

/*
	     Always remember "(sth) <> NULL" doesn't work! 
	Use  "(sth) IS DISTINCT FROM (sth->NULL)" ...

*/

-- This file contains elements without data. It is safe to call
-- it on a db loaded with data.

CREATE OR REPLACE VIEW cc_booth_v AS
	SELECT cc_booth.id AS id, cc_booth.agentid AS owner,
		cc_booth.name, cc_booth.location,
		cc_card.credit, cc_card.currency,
		def_card_id, cur_card_id, cc_shopsessions.starttime,
		cc_card.username AS in_now,
		(CASE WHEN def_card_id IS NULL THEN 0
		WHEN cc_booth.disabled THEN 5
		WHEN cur_card_id IS NULL THEN 1
		WHEN cc_card.credit <> 0.0 AND (cc_card.status = 1) THEN 4
		WHEN cc_card.credit <> 0.0 THEN 6
		WHEN (cc_card.status = 1) THEN 3
		ELSE 2
		END) AS state,
		cur_call.destination AS cur_destination,
		cur_call.srvid AS cur_srvid,
		cur_call.starttime AS cur_starttime,
		(SELECT COALESCE(SUM(sessiontime),0) FROM cc_call 
			WHERE cardid = cc_card.id
			AND starttime >= cc_shopsessions.starttime) AS secs
	FROM (cc_booth LEFT OUTER JOIN cc_card ON cc_booth.cur_card_id = cc_card.id)
		LEFT OUTER JOIN cc_shopsessions ON cc_shopsessions.booth=cc_booth.id AND
		cc_shopsessions.endtime IS NULL
		LEFT OUTER JOIN cc_call AS cur_call ON (cur_call.cardid = cc_card.id AND
			cur_call.starttime >= cc_shopsessions.starttime AND 
			cur_call.stoptime IS NULL);


CREATE OR REPLACE RULE cc_booth_update_o AS ON UPDATE TO cc_booth_v DO INSTEAD NOTHING;

CREATE OR REPLACE RULE cc_booth_update2 AS ON UPDATE TO cc_booth_v 
	WHERE NEW.state=2 AND OLD.state <> 2
	DO INSTEAD UPDATE cc_card SET status = 8
			FROM cc_agent, cc_booth 
			WHERE cc_booth.cur_card_id= cc_card.id AND
				cc_booth.id = OLD.id AND
				cc_booth.agentid = OLD.owner;
				
CREATE OR REPLACE RULE cc_booth_update3 AS ON UPDATE TO cc_booth_v WHERE NEW.state=3 AND
	OLD.state <> 3
	DO INSTEAD UPDATE cc_card SET status = 1
			FROM cc_agent, cc_booth 
			WHERE cc_booth.cur_card_id= cc_card.id AND
				cc_booth.id = OLD.id AND
				cc_booth.agentid = OLD.owner;

-------------------------------------------------------
------------ Triggers ------------------



CREATE OR REPLACE FUNCTION cc_booth_set_card() RETURNS trigger AS $$
	DECLARE
		bint bigint;
		money numeric;
		old_def_card BIGINT;
		old_cur_card BIGINT;
		new_sess_id BIGINT;
		ptype INTEGER;
		s_inuse INTEGER;
	BEGIN
		-- Remove old card first
/*	IF TG_OP = 'UPDATE'  THEN
		IF(NEW.def_card_id <> OLD.def_card_id ) THEN
		UPDATE cc_agent_cards SET def = 'f' 
			WHERE OLD.def_card_id IS NOT NULL AND card_id = OLD.def_card_id;
	END IF; END IF;*/
	
	IF TG_OP = 'UPDATE'  THEN
		old_def_card := OLD.def_card_id;
		old_cur_card := OLD.cur_card_id;
	ELSE
		old_def_card := NULL;
		old_cur_card := NULL;
	END IF;
	
	-- Check the case when we alter the Default card for a booth
	IF ( NEW.def_card_id IS NOT NULL AND NEW.def_card_id IS DISTINCT FROM old_def_card) THEN
	
		IF EXISTS (SELECT id FROM cc_booth WHERE id <> NEW.id AND 
				( cur_card_id = NEW.def_card_id OR
				def_card_id = NEW.def_card_id )) THEN
			RAISE EXCEPTION 'Default card already used';
		END IF;
		IF NEW.agentid <> (SELECT agentid FROM cc_card, cc_card_group
				WHERE cc_card.id = NEW.def_card_id AND cc_card_group.id = cc_card.grp
					/* AND agent_role = 1 */ LIMIT 1) THEN
			RAISE EXCEPTION 'Card id does not belong to agent % or not callshop one.', NEW.agentid;
		END IF;
	END IF;
		
	IF NEW.def_card_id IS NULL AND NEW.cur_card_id IS NOT NULL THEN
		RAISE EXCEPTION 'Cannot have a card in a booth without a default one!';
	END IF;
	
		-- If the current card changed, do something about that..
	IF NEW.cur_card_id IS DISTINCT FROM old_cur_card THEN
		
		IF old_cur_card IS NOT NULL THEN -- Some card was removed
			-- End the session
			SELECT id INTO STRICT bint FROM cc_shopsessions
				WHERE card = old_cur_card AND endtime IS NULL
				AND booth = NEW.id;
				
					-- Strict means bint is surely defined.
			SELECT credit, inuse INTO STRICT money, s_inuse FROM cc_card
					WHERE id = old_cur_card;
			IF old_cur_card = old_def_card AND
				(money <> 0.0 OR s_inuse > 0) THEN
				RAISE EXCEPTION 'Cannot clear session % because it contains non-empty, default card %', bint, old_cur_card;
			END IF;
			-- If session has money, close it with 0
			IF (money <> 0.0) THEN 
				SELECT id INTO STRICT ptype FROM cc_paytypes WHERE preset = 'carry';
				INSERT INTO cc_agentrefill(card_id, agentid, credit, carried, pay_type)
					VALUES(old_cur_card, NEW.agentid,0-money, true, ptype);
				IF NOT FOUND THEN
					RAISE EXCEPTION 'Cannot carry session upon close!';
				END IF;
				
			END IF;
			
			-- Close the session
			UPDATE cc_shopsessions SET endtime = now() , state = 'Closed'
				WHERE id = bint;
			IF NOT FOUND THEN
				RAISE EXCEPTION 'Cannot close session!';
			END IF;
			
			-- Update the card
			UPDATE cc_card SET status = 8 WHERE id = old_cur_card;
		END IF;
		
		IF NEW.cur_card_id IS NOT NULL THEN   -- Card was attached to booth
				-- Check if the card is ours (the default is already checked)
			IF NEW.cur_card_id <> NEW.def_card_id AND
				NEW.agentid <> (SELECT agentid FROM cc_card, cc_card_group
					WHERE cc_card.id = NEW.cur_card_id 
						AND cc_card_group.id = cc_card.grp
						/* AND agent_role = 1 */ LIMIT 1) THEN
					RAISE EXCEPTION 'Card id does not belong to agent % or not callshop one.', NEW.agentid;
			END IF;
			
			-- Now, start a session..
			RAISE LOG 'New session for booth %', NEW.id;
			INSERT INTO cc_shopsessions (booth,card,state)
				VALUES (NEW.id, NEW.cur_card_id, 'Open')
				RETURNING id INTO STRICT new_sess_id;
			SELECT credit INTO STRICT money FROM cc_card WHERE id = NEW.cur_card_id;
			
			IF money <> 0.0 THEN
				SELECT id INTO STRICT ptype FROM cc_paytypes WHERE preset = 'carried';
				INSERT INTO cc_agentrefill(card_id, agentid, credit, carried, pay_type)
					VALUES(NEW.cur_card_id, NEW.agentid,money, true, ptype);
				IF NOT FOUND THEN
					RAISE EXCEPTION 'Cannot carry previous amount to new session!';
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


DROP TRIGGER IF EXISTS cc_booth_check_def ON cc_booth;
DROP TRIGGER IF EXISTS cc_booth_check_agent ON cc_booth;

CREATE TRIGGER cc_booth_check_def BEFORE INSERT OR UPDATE ON cc_booth
	FOR EACH ROW EXECUTE PROCEDURE cc_booth_set_card();

CREATE TRIGGER cc_booth_check_agent BEFORE UPDATE ON cc_booth
	FOR EACH ROW EXECUTE PROCEDURE cc_booth_no_agent_update();



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
  	PERFORM 1 FROM cc_card, cc_card_group WHERE
  		cc_card.grp = cc_card_group.id AND
  		cc_card.id = NEW.card_id AND agentid = NEW.agentid;
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
  
DROP TRIGGER IF EXISTS cc_agent_refill_it ON cc_agentrefill;

CREATE TRIGGER cc_agent_refill_it BEFORE INSERT ON cc_agentrefill
	FOR EACH ROW EXECUTE PROCEDURE cc_agent_refill_it();

--eof