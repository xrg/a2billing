-- Additional tables for callshop feature
-- Copyright (c) 2006 P.Christeas <p_christeas@yahoo.com>
--

/*CREATE TABLE cc_agent_cards (
	card_id bigint NOT NULL PRIMARY KEY REFERENCES cc_card(id) ON DELETE CASCADE,
	agentid bigint NOT NULL REFERENCES cc_agent(id) ON DELETE RESTRICT,
	def boolean NOT NULL DEFAULT 'f') ;*/
	
CREATE TABLE cc_booth (
	id bigserial NOT NULL PRIMARY KEY,
	name text NOT NULL,
	location text,
	agentid bigint NOT NULL REFERENCES cc_agent(id),
	datecreation timestamp without time zone DEFAULT now(),
	last_activation timestamp without time zone,
	disabled boolean NOT NULL DEFAULT 'f',
	cur_card_id bigint REFERENCES cc_card(id),
	def_card_id bigint REFERENCES cc_card(id),
	callerid TEXT

);


-- This table will hold the transactions for the agent<->card
-- refills. boothid is optional.
-- 'carried' is important: if set to true, then the transaction
-- will NOT cause cc_agent.credit/cc.card.credit to be updated.
-- That is useful when inserting dummy refills to carry the credit
-- between sessions. 

CREATE TABLE cc_agentrefill (
    id bigserial NOT NULL,
    date timestamp without time zone DEFAULT now() NOT NULL,
    credit numeric(12,4) NOT NULL,
    carried boolean NOT NULL DEFAULT false,
    pay_type integer,
    card_id bigint NOT NULL REFERENCES cc_card(id),
    agentid bigint NOT NULL REFERENCES cc_agent(id),
    boothid bigint REFERENCES cc_booth(id)
);


CREATE TABLE cc_shopsessions (
	id bigserial PRIMARY KEY,
	booth bigint NOT NULL REFERENCES cc_booth(id),
	card bigint NOT NULL REFERENCES cc_card(id),
	starttime timestamp NOT NULL DEFAULT now(),
	endtime  timestamp,
	state text NOT NULL -- REFERENCES cc_texts(id)
);


CREATE TABLE cc_agentpay (
    id bigserial NOT NULL,
    date timestamp without time zone DEFAULT now() NOT NULL,
    credit numeric(12,4) NOT NULL,
    pay_type integer,
    agentid bigint NOT NULL REFERENCES cc_agent(id),
    descr text 
);

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
	
-- Charges could now come from the agents. If so, a a2b admin should better
-- confirm those.
	
ALTER TABLE cc_charge ADD agentid bigint references cc_agent(id);
ALTER TABLE cc_charge ADD from_agent boolean NOT NULL DEFAULT FALSE;
ALTER TABLE cc_charge ADD checked bigint REFERENCES cc_ui_authen(userid);

CREATE TABLE cc_texts (
	id integer NOT NULL,
	lang VARCHAR(10) NOT NULL DEFAULT 'C',
	txt text NOT NULL,
	src int NOT NULL DEFAULT 1,
	CONSTRAINT cc_texts_pkey PRIMARY KEY (id,lang) );


/** cc_paytypes Define rules (texts) for pay/charge types to be used in combos etc.
	id is the text, as seen in cc_texts. This is better than using text, since
		pay_type would be used many times in reports and it has to be both
		matched and translated. Hence an integer field.
	side is an arbitrary enum like:
		1 company to agent
		2 agent to company
		3 charges from agent to customer
		4 bonuses from agent to customer
	preset is a text field so that the php code can automatically select and
		apply one of those charges. eg. 'print-cust-invoice'
*/

CREATE TABLE cc_paytypes (
	id integer NOT NULL,
	side smallint NOT NULL,
	charge NUMERIC(12,4),
	preset VARCHAR(30) UNIQUE
);

/* Hint: use sth like the following to introduce a new charge
 INSERT INTO cc_paytypes (id, side, charge) values (gettext_ri('Cactus renting charges'), 3, 0.0);
 */

/** Deleted charges.
	Since charges constitute an important money transaction, removed ones
	should be logged, i.e. inserted here
*/
CREATE TABLE cc_charge_bk (
    id_cc_card bigint NOT NULL,
    iduser integer DEFAULT 0 NOT NULL,
    creationdate timestamp without time zone,
    deletiondate timestamp WITHOUT time zone DEFAULT now(),
    amount numeric(12,4) NOT NULL,
    chargetype integer DEFAULT 0,
    description text,
    agentid bigint REFERENCES cc_agent(id),
    from_agent boolean NOT NULL DEFAULT FALSE,
    checked bigint REFERENCES cc_ui_authen(userid)
);

ALTER TABLE cc_invoices ALTER cardid DROP NOT NULL;
ALTER TABLE cc_invoices ADD agentid bigint references cc_agent(id);
ALTER TABLE cc_invoices ADD CHECK( (cardid IS NOT NULL) OR (agentid IS NOT NULL));

-- eof
