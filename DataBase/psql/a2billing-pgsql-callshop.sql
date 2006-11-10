-- Additional tables for callshop feature
-- Copyright (c) 2006 P.Christeas <p_christeas@yahoo.com>
--

CREATE TABLE cc_agent (
    id bigserial NOT NULL PRIMARY KEY,
    name text NOT NULL,
    active boolean NOT NULL DEFAULT true,
    login VARCHAR(20) NOT NULL,
    passwd VARCHAR(40) NOT NULL,
    groupid integer,
    location text,
    datecreation timestamp without time zone DEFAULT now(),
    "language" text DEFAULT 'en'::text,
    tariffgroup integer REFERENCES cc_tariffgroup(id),
    options integer NOT NULL DEFAULT 0,
    credit NUMERIC(12,4) NOT NULL DEFAULT 0
    );

-- one way: put the agent inside the card:
-- ALTER TABLE cc_card ADD agentid bigint REFERENCES cc_agent(id) ON DELETE RESTRICT;
-- CREATE INDEX cc_card_agent_idx ON cc_card(agentid);

-- second way: A different table
CREATE TABLE cc_agent_cards (
	card_id bigint NOT NULL PRIMARY KEY REFERENCES cc_card(id) ON DELETE CASCADE,
	agentid bigint NOT NULL REFERENCES cc_agent(id) ON DELETE RESTRICT,
	def boolean NOT NULL DEFAULT 'f') ;
	
CREATE INDEX cc_agent_cards_agent ON cc_agent_cards(agentid);

CREATE TABLE cc_booth (
	id bigserial NOT NULL PRIMARY KEY,
	name text NOT NULL,
	location text,
	agentid bigint NOT NULL REFERENCES cc_agent(id),
	datecreation timestamp without time zone DEFAULT now(),
	last_activation timestamp without time zone,
	disabled boolean NOT NULL DEFAULT 'f',
	cur_card_id bigint REFERENCES cc_card(id),
	def_card_id bigint REFERENCES cc_card(id)
);


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
	
CREATE TABLE cc_currencies (
    id serial NOT NULL,
    currency char(3) default '' NOT NULL,
    name character varying(30) default '' NOT NULL,
    value numeric(12,5) default '0.00000' NOT NULL,
    lastupdate timestamp without time zone DEFAULT now(),	
    basecurrency char(3) default 'USD' NOT NULL,
    csign VARCHAR(6),
    sign_pre boolean DEFAULT 'f' NOT NULL,
    cformat VARCHAR(20) DEFAULT 'FM99G999G999G990D00' NOT NULL
);
	
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
	
CREATE RULE cc_booth_update_o AS ON UPDATE TO cc_booth_v DO INSTEAD NOTHING;

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
CREATE OR REPLACE RULE cc_booth_update_d AS ON UPDATE TO cc_booth_v WHERE NEW.cur_card_id= OLD.def_card_id 
	AND OLD.def_card_id IS NOT NULL
	DO INSTEAD UPDATE cc_booth SET cur_card_id = def_card_id 
			FROM cc_card, cc_agent_cards
			WHERE cc_booth.def_card_id= cc_card.id AND
				cc_booth.id = OLD.id AND
				cc_booth.agentid = OLD.owner AND
				cc_agent_cards.card_id = cc_card.id AND
				cc_agent_cards.agentid = OLD.owner AND
				cc_agent_cards.def = 't' ;

---- TODO: set the caller id !

CREATE OR REPLACE RULE cc_booth_update_d_fill_booth AS ON UPDATE TO cc_booth_v 
	WHERE NEW.cur_card_id IS NOT NULL
		AND OLD.cur_card_id IS NULL
	DO INSTEAD UPDATE cc_booth SET cur_card_id = NEW.cur_card_id 
			FROM cc_card, cc_agent_cards
			WHERE NEW.cur_card_id= cc_card.id AND
				(OLD.def_card_id IS NULL OR NEW.cur_card_id <> OLD.def_card_id ) AND
				cc_booth.id = OLD.id AND
				cc_booth.agentid = OLD.owner AND
				cc_agent_cards.card_id = cc_card.id AND
				cc_agent_cards.agentid = OLD.owner AND
				cc_agent_cards.def = 'f' ;

CREATE OR REPLACE RULE cc_booth_update_d_empty_booth AS ON UPDATE TO cc_booth_v 
	WHERE NEW.cur_card_id IS NULL
		AND OLD.cur_card_id IS NOT NULL
	DO INSTEAD UPDATE cc_booth SET cur_card_id = NULL ;
	
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
		
		
-- eof
