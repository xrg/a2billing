-- Copyright P.Christeas 2006


-- misc stuff for testing..


			
-- CREATE FUNCTION divide_time(div1 INTERVAL, div2 INTERVAL) RETURNS FLOAT AS $$
-- 	SELECT ( EXTRACT(EPOCH FROM $1) / EXTRACT(EPOCH FROM $2)) ;
-- 	$$ LANGUAGE SQL IMMUTABLE STRICT;
-- 
-- SELECT divide_time(INTERVAL '100sec', (endtime - starttime)) FROM cc_shopsessions;

/*SELECT date_trunc('sec', cc_shopsessions.starttime)AS session_start, 
	date_trunc('sec',(cc_shopsessions.endtime - cc_shopsessions.starttime)) AS session_time, 
	SUM(cc_call.stoptime - cc_call.starttime) AS sum_calls,
	to_char((divide_time(SUM(cc_call.stoptime - cc_call.starttime), (cc_shopsessions.endtime - cc_shopsessions.starttime)) * 100), '990D0000%') AS usage_pc
	FROM cc_call,cc_card, cc_shopsessions 
		WHERE cc_call.username = cc_card.username AND cc_shopsessions.card = cc_card.id
			AND cc_call.starttime >= cc_shopsessions.starttime AND (cc_shopsessions.endtime IS NULL OR cc_call.starttime <= cc_shopsessions.endtime)
		GROUP BY cc_shopsessions.id,cc_shopsessions.starttime, cc_shopsessions.endtime;*/
-- One view for all: have all the session transactions in one table.

		


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
	
	

--eof
