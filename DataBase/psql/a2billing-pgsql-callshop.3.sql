-- Copyright P.Christeas 2006


-- misc stuff for testing..



-- CREATE OR REPLACE FUNCTION simulate_calls( ) RETURNS integer AS $$
-- BEGIN
-- 
-- END; $$ LANGUAGE plpgsql;

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
  	
  	UPDATE cc_agent SET credit = credit - NEW.credit WHERE id = NEW.agentid;
  	IF NOT FOUND THEN
  		RAISE EXCEPTION 'Failed to update agents credit';
  	END IF;
  	UPDATE cc_card SET credit = credit + NEW.credit WHERE id = NEW.card_id;
  	RETURN NEW;
  END;
  $$ LANGUAGE plpgsql;
  
DROP TRIGGER cc_agent_refill_it ON cc_agentrefill;

CREATE TRIGGER cc_agent_refill_it BEFORE INSERT ON cc_agentrefill
	FOR EACH ROW EXECUTE PROCEDURE cc_agent_refill_it();

--eof