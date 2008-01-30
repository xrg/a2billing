-- Charge functions

DROP TRIGGER IF EXISTS cc_charge_check_invoice ON cc_card_charge;
CREATE TRIGGER cc_charge_check_invoice BEFORE UPDATE OR DELETE ON cc_card_charge
	FOR EACH ROW EXECUTE PROCEDURE cc_invoice_lock_f();

/* Policy: The agent IS ALLOWED to exceed his/her credit (-limit) with charges. Since
   we want the agent to be able to charge lost equipment or return money to customers.
   However, the charges are well-logged (I hope) and the admin user will review them
   and waive the charges, where those are not appropriate.
*/
CREATE OR REPLACE FUNCTION cc_card_charge_it() RETURNS trigger AS $$
BEGIN
	IF NEW.agentid IS NOT NULL THEN
		PERFORM card_id FROM cc_agent_cards WHERE card_id= NEW.card
			AND agentid= NEW.agentid;
		IF NOT FOUND THEN 
			RAISE EXCEPTION 'Card does not belong to agent';
		END IF;
	END IF;
	
	IF NEW.amount < 0.0 AND NEW.from_agent = true THEN
		UPDATE cc_agent SET climit = climit + NEW.amount WHERE
			cc_agent.id = NEW.agentid;
		IF NOT FOUND THEN
			RAISE EXCEPTION 'Cannot update agent''s climit';
		END IF;
	END IF;
	UPDATE cc_card SET credit = credit - NEW.amount WHERE id = NEW.card;
	IF NOT FOUND THEN
		RAISE EXCEPTION 'Failed to update card''s credit';
	END IF;
	RETURN NEW;
END ; $$ LANGUAGE plpgsql STRICT;

CREATE OR REPLACE FUNCTION cc_card_charge_itu() RETURNS trigger AS $$
BEGIN
	IF NEW.agentid <> OLD.agentid THEN
		RAISE EXCEPTION 'Change of agents for charges is forbidden!';
	END IF;
	IF NEW.card <> OLD.card THEN
		RAISE EXCEPTION 'Change of cards for charges is forbidden!';
	END IF;
	
	/* Note: two updates follow. We don't want to cross 0.0 boundaries!
	   Therefore, they couldn't be merged into one update. */
	IF OLD.amount < 0.0 AND OLD.from_agent = true THEN
		UPDATE cc_agent SET climit = climit - OLD.amount WHERE
			id = OLD.agentid;
		IF NOT FOUND THEN
			RAISE EXCEPTION 'Cannot update agent''s climit';
		END IF;
	END IF;
	IF NEW.amount < 0.0 AND NEW.from_agent = true THEN
		UPDATE cc_agent SET climit = climit + NEW.amount WHERE
			id = NEW.agentid;
		IF NOT FOUND THEN
			RAISE EXCEPTION 'Cannot update agent''s climit';
		END IF;
	END IF;

	UPDATE cc_card SET credit = credit + OLD.amount - NEW.amount WHERE id = NEW.card;
	IF NOT FOUND THEN
		RAISE EXCEPTION 'Failed to update card''s credit';
	END IF;
	RETURN NEW;
END ; $$ LANGUAGE plpgsql STRICT;

CREATE OR REPLACE FUNCTION cc_card_charge_itd() RETURNS trigger AS $$
BEGIN
	UPDATE cc_card SET credit = credit + OLD.amount WHERE id = OLD.card;
	IF NOT FOUND THEN
		RAISE EXCEPTION 'Failed to update card''s credit';
	END IF;
	IF OLD.amount < 0.0 AND OLD.from_agent = true THEN
		UPDATE cc_agent SET climit = climit - OLD.amount WHERE
			id = OLD.agentid;
		IF NOT FOUND THEN
			RAISE EXCEPTION 'Cannot update agent''s climit';
		END IF;
	END IF;
	INSERT INTO cc_card_charge_bk (card, iduser, creationdate, amount, chargetype,
		description, agentid, from_agent, checked) 
		VALUES (OLD.card, OLD.iduser, OLD.creationdate, OLD.amount, OLD.chargetype, 
		OLD.description, OLD.agentid, OLD.from_agent, OLD.checked);
	RETURN OLD;
END ; $$ LANGUAGE plpgsql STRICT;

DROP TRIGGER IF EXISTS cc_card_charge_it ON cc_card_charge;
DROP TRIGGER IF EXISTS cc_card_charge_itd ON cc_card_charge;
DROP TRIGGER IF EXISTS cc_card_charge_itu ON cc_card_charge;

CREATE TRIGGER cc_card_charge_it BEFORE INSERT ON cc_card_charge
	FOR EACH ROW EXECUTE PROCEDURE cc_card_charge_it();
CREATE TRIGGER cc_card_charge_itu BEFORE UPDATE ON cc_card_charge
	FOR EACH ROW EXECUTE PROCEDURE cc_card_charge_itu();
CREATE TRIGGER cc_card_charge_itd BEFORE DELETE ON cc_card_charge
	FOR EACH ROW EXECUTE PROCEDURE cc_card_charge_itd();

/** Charge some standard fee, based on paytypes */
CREATE OR REPLACE FUNCTION agent_charge_std(charge VARCHAR(30),ssession BIGINT, descr TEXT) RETURNS void AS $$
BEGIN
	INSERT INTO cc_card_charge(card, amount, chargetype, description, agentid, from_agent)
	SELECT cc_shopsessions.card, cc_paytypes.charge , cc_paytypes.id, $3, cc_agent_cards.agentid, true
		FROM cc_shopsessions, cc_paytypes, cc_agent_cards
		WHERE cc_shopsessions.id = $2 AND cc_paytypes.preset = $1 AND cc_agent_cards.card_id = cc_shopsessions.card
		LIMIT 1;
	IF NOT FOUND THEN
		RAISE EXCEPTION 'Cannot find session/type to charge';
	END IF;
END;
$$ LANGUAGE plpgsql STRICT VOLATILE;

/** Create a transparent view for agent-submitted charges. 
    This way, additional functionality will be restricted */
CREATE OR REPLACE VIEW cc_card_charge_av AS
	SELECT * FROM cc_card_charge;
	
CREATE OR REPLACE RULE cc_card_charge_uo AS ON UPDATE TO cc_card_charge_av DO INSTEAD NOTHING;
CREATE OR REPLACE RULE cc_card_charge_do AS ON DELETE TO cc_card_charge_av DO INSTEAD NOTHING;
CREATE OR REPLACE RULE cc_card_charge_io AS ON INSERT TO cc_card_charge_av DO INSTEAD NOTHING;

CREATE OR REPLACE RULE cc_card_charge_da AS ON DELETE TO cc_card_charge_av 
	WHERE agentid IS NOT NULL AND checked IS NULL AND from_agent = true
	DO INSTEAD DELETE FROM cc_card_charge WHERE cc_card_charge.id = OLD.id ;

CREATE OR REPLACE RULE cc_card_charge_ua AS ON UPDATE TO cc_card_charge_av 
	WHERE OLD.agentid IS NOT NULL AND OLD.checked IS NULL AND OLD.from_agent = true
		AND NEW.from_agent = true AND NEW.checked IS NULL
	DO INSTEAD UPDATE cc_card_charge SET amount = NEW.amount, chargetype = NEW.chargetype,
		description = NEW.description
		WHERE cc_card_charge.id = OLD.id ;

CREATE OR REPLACE RULE cc_card_charge_ia AS ON INSERT TO cc_card_charge_av 
	WHERE agentid IS NOT NULL AND checked IS NULL AND from_agent = true
	DO INSTEAD INSERT INTO cc_card_charge (card, iduser, creationdate, amount, chargetype,
		description, agentid, from_agent, checked) 
		VALUES (NEW.card, COALESCE(NEW.iduser,0), COALESCE(NEW.creationdate,now()), NEW.amount, NEW.chargetype, 
		NEW.description, NEW.agentid, NEW.from_agent, NEW.checked);

--eof
