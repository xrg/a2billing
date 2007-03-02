-- Charges
CREATE OR REPLACE FUNCTION cc_charge_it() RETURNS trigger AS $$
BEGIN
	IF NEW.agentid IS NOT NULL THEN
		PERFORM card_id FROM cc_agent_cards WHERE card_id= NEW.id_cc_card
			AND agentid= NEW.agentid;
		IF NOT FOUND THEN 
			RAISE EXCEPTION 'Card does not belong to agent';
		END IF;
	END IF;
	
	UPDATE cc_card SET credit = credit - NEW.amount WHERE id = NEW.id_cc_card;
	IF NOT FOUND THEN
		RAISE EXCEPTION 'Failed to update card''s credit';
	END IF;
	RETURN NEW;
END ; $$ LANGUAGE plpgsql STRICT;

CREATE OR REPLACE FUNCTION cc_charge_itu() RETURNS trigger AS $$
BEGIN
	IF NEW.agentid <> OLD.agentid THEN
		RAISE EXCEPTION 'Change of agents for charges is forbidden!';
	END IF;
	IF NEW.id_cc_card <> OLD.id_cc_card THEN
		RAISE EXCEPTION 'Change of cards for charges is forbidden!';
	END IF;
	
	UPDATE cc_card SET credit = credit + OLD.amount - NEW.amount WHERE id = NEW.id_cc_card;
	IF NOT FOUND THEN
		RAISE EXCEPTION 'Failed to update card''s credit';
	END IF;
	RETURN NEW;
END ; $$ LANGUAGE plpgsql STRICT;

CREATE OR REPLACE FUNCTION cc_charge_itd() RETURNS trigger AS $$
BEGIN
	UPDATE cc_card SET credit = credit + OLD.amount WHERE id = OLD.id_cc_card;
	IF NOT FOUND THEN
		RAISE EXCEPTION 'Failed to update card''s credit';
	END IF;
	INSERT INTO cc_charge_bk (id_cc_card, iduser, creationdate, amount, chargetype,
		description, agentid, from_agent, checked) 
		VALUES (OLD.id_cc_card, OLD.iduser, OLD.creationdate, OLD.amount, OLD.chargetype, 
		OLD.description, OLD.agentid, OLD.from_agent, OLD.checked);
	RETURN OLD;
END ; $$ LANGUAGE plpgsql STRICT;

DROP TRIGGER cc_charge_it ON cc_charge;
DROP TRIGGER cc_charge_itd ON cc_charge;
DROP TRIGGER cc_charge_itu ON cc_charge;

CREATE TRIGGER cc_charge_it BEFORE INSERT ON cc_charge
	FOR EACH ROW EXECUTE PROCEDURE cc_charge_it();
CREATE TRIGGER cc_charge_itu BEFORE UPDATE ON cc_charge
	FOR EACH ROW EXECUTE PROCEDURE cc_charge_itu();
CREATE TRIGGER cc_charge_itd BEFORE DELETE ON cc_charge
	FOR EACH ROW EXECUTE PROCEDURE cc_charge_itd();

/** Charge some standard fee, based on paytypes */
CREATE OR REPLACE FUNCTION agent_charge_std(charge VARCHAR(30),ssession BIGINT, descr TEXT) RETURNS void AS $$
BEGIN
	INSERT INTO cc_charge(id_cc_card, amount, chargetype, description, agentid, from_agent)
	SELECT cc_shopsessions.card, cc_paytypes.charge , cc_paytypes.id, $3, cc_agent_cards.agentid, true
		FROM cc_shopsessions, cc_paytypes, cc_agent_cards
		WHERE cc_shopsessions.id = $2 AND cc_paytypes.preset = $1 AND cc_agent_cards.card_id = cc_shopsessions.card
		LIMIT 1;
	IF NOT FOUND THEN
		RAISE EXCEPTION 'Cannot find session/type to charge';
	END IF;
END;
$$ LANGUAGE plpgsql STRICT VOLATILE;

--eof
