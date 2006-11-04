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
	state int NOT NULL DEFAULT 0,
	datecreation timestamp without time zone DEFAULT now(),
	last_activation timestamp without time zone,
	cur_card_id bigint REFERENCES cc_card(id),
	def_card_id bigint REFERENCES cc_card(id)
);


CREATE OR REPLACE VIEW cc_booth_v AS
	SELECT cc_booth.id AS id, cc_booth.agentid AS owner,cc_booth.state,
		cc_booth.name, cc_booth.location,
		cc_card.credit, 0::numeric AS mins 
	FROM cc_booth LEFT OUTER JOIN cc_card ON cc_booth.cur_card_id = cc_card.id
	
	;
	
-- eof
