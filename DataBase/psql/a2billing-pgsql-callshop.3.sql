-- Copyright P.Christeas 2006


-- misc stuff for testing..

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
--eof