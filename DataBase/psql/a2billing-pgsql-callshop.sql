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
	

CREATE OR REPLACE FUNCTION booth_start(booth bigint, agent_id bigint) RETURNS bigint
	AS $$
		UPDATE cc_card SET activated= 't' 
			FROM cc_agent, cc_booth 
			WHERE cc_booth.cur_card_id= cc_card.id AND
				cc_booth.id = $1 AND
				cc_booth.agentid = $2;
		select COUNT(cc_card.id) FROM cc_card,cc_agent, cc_booth 
			WHERE cc_booth.cur_card_id= cc_card.id AND
				cc_booth.id = $1 AND
				cc_booth.agentid = $2;
	$$ LANGUAGE SQL VOLATILE STRICT;
	
CREATE RULE cc_booth_update AS ON UPDATE TO cc_booth_v DO INSTEAD NOTHING;

CREATE OR REPLACE RULE cc_booth_update2 AS ON UPDATE TO cc_booth_v 
	WHERE NEW.state=2
	DO INSTEAD UPDATE cc_card SET activated= 'f' 
			FROM cc_agent, cc_booth 
			WHERE cc_booth.cur_card_id= cc_card.id AND
				cc_booth.id = OLD.id AND
				cc_booth.agentid = OLD.owner;
				
CREATE OR REPLACE RULE cc_booth_update3 AS ON UPDATE TO cc_booth_v WHERE NEW.state=3 
	DO INSTEAD UPDATE cc_card SET activated= 't' 
			FROM cc_agent, cc_booth 
			WHERE cc_booth.cur_card_id= cc_card.id AND
				cc_booth.id = OLD.id AND
				cc_booth.agentid = OLD.owner;

-- TODO: use verification for card owner!
CREATE OR REPLACE RULE cc_booth_update_d AS ON UPDATE TO cc_booth_v WHERE NEW.cur_card_id= OLD.def_card_id 
	AND OLD.def_card_id IS NOT NULL
	DO INSTEAD UPDATE cc_booth SET cur_card_id = def_card_id 
			FROM cc_card 
			WHERE cc_booth.def_card_id= cc_card.id AND
				cc_booth.id = OLD.id AND
				cc_booth.agentid = OLD.owner;

-- eof
