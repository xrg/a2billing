-- Additional functions/views for callshop feature
-- Copyright (c) 2006 P.Christeas <p_christeas@yahoo.com>
--

-- This file contains elements without data. It is safe to call
-- it on a db loaded with data.

CREATE OR REPLACE VIEW cc_booth_v AS
	SELECT cc_booth.id AS id, cc_booth.agentid AS owner,
		cc_booth.name, cc_booth.location,
		cc_card.credit, 0::numeric AS mins,
		def_card_id, cur_card_id,
		(CASE WHEN def_card_id IS NULL THEN 0
		WHEN cur_card_id IS NULL THEN 1
		WHEN cc_booth.disabled THEN 5
		WHEN cc_card.lastuse > cc_booth.last_activation THEN 4
		WHEN cc_card.activated THEN 3
		ELSE 2
		END) AS state
	FROM cc_booth LEFT OUTER JOIN cc_card ON cc_booth.cur_card_id = cc_card.id;
	
	
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
	SELECT cc_card.id,expirationdate,username, firstname, lastname, address,
		credit, activated,
		inuse , currency, lastuse, language, creditlimit, vat,
		cc_agent_cards.agentid, cc_agent_cards.def,
		cc_booth.id AS now_id , booth2.id AS def_id, cc_booth.name AS now_name, booth2.name AS def_name
		FROM (cc_card  LEFT OUTER JOIN cc_booth ON cc_booth.cur_card_id = cc_card.id) 
			LEFT OUTER JOIN cc_booth AS booth2 ON cc_card.id = booth2.def_card_id,
			cc_agent_cards
		WHERE cc_card.id = cc_agent_cards.card_id;
		
		
		
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


-- eof
