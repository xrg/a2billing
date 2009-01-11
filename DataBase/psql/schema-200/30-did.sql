-- DID tables

-- This is a typical table that gives a name to groups. 
-- No important data here.
CREATE TABLE cc_didgroup (
    id  	BIGSERIAL NOT NULL PRIMARY KEY,
    name TEXT NOT NULL,
   code TEXT NOT NULL DEFAULT '', -- lets the did be matched against the trunk it comes from
   tgid INTEGER REFERENCES cc_tariffgroup(id),
   rnplan INTEGER REFERENCES cc_re_numplan(id),
   alert_info TEXT  -- Ring pattern
);


-- A batch should contain all settings about a bunch of DIDs
CREATE TABLE did_batch(
	id SERIAL PRIMARY KEY,
	name TEXT NOT NULL,
	pname TEXT,  -- Name, visible to customers
	dmode INTEGER NOT NULL,  -- dmode controls the behaviour of the DID
	status INTEGER DEFAULT 1,
	metric INTEGER NOT NULL DEFAULT 10,
	provider INTEGER REFERENCES cc_provider(id),
	creationdate TIMESTAMP NOT NULL DEFAULT NOW(),
	expiredate   TIMESTAMP,
	secondsused  BIGINT,
		/* The dialhead must match the beginning of the incoming number=DID */
	dialhead     TEXT NOT NULL,
		/* Add these digits in some modes (eg. for useralias ) */
	dialadd      TEXT NOT NULL DEFAULT '',
	dialfld2     TEXT,
		/* optionally, length of remaining digits */
	diallen	     INTEGER,
		/* The numplan */
	nplan	     INTEGER REFERENCES cc_numplan(id),
		/* Here we specify how the did will be billed.
		   the entries in the tariffplan could further specify
		   pricing options per DID number-pattern. The tariffplan
		   will also hold the accumulated cost reg. these DIDs */
	idtp      INTEGER NOT NULL REFERENCES cc_tariffplan(id),
	flags	  INTEGER
);


CREATE TABLE did_reservation (
    batch 	INTEGER NOT NULL REFERENCES did_batch(id),
    did 	TEXT NOT NULL,
    secondused 	INTEGER DEFAULT 0,
    target 	TEXT, -- may be null for auto matching.
    	-- This constraint will prevent did_reservation from ref'ing an inherited templ.
    CONSTRAINT did_reservation_template_fkey 
    	FOREIGN KEY (template) REFERENCES subscription_template(id)
) INHERITS (card_subscription);


-- Associate did groups with batches
CREATE TABLE did_group_batch (
    btid INTEGER NOT NULL REFERENCES cc_didgroup(id) ON DELETE CASCADE,
    dbid integer NOT NULL REFERENCES did_batch(id) ON DELETE CASCADE,
    PRIMARY KEY(btid,dbid)
);

CREATE TABLE did_phonebook(
    id  SERIAL PRIMARY KEY,
    name  TEXT NOT NULL,
    code  VARCHAR(64),
    rnplan INTEGER REFERENCES cc_re_numplan(id),
    card_group INTEGER REFERENCES cc_card_group(id),
    cardid    BIGINT REFERENCES cc_card(id)
);

CREATE INDEX did_phonebook_rnplan_index ON did_phonebook(rnplan);
CREATE INDEX did_phonebook_cgroup_index ON did_phonebook(card_group);
CREATE INDEX did_phonebook_cardid_index ON did_phonebook(cardid);

CREATE TABLE did_pb_entry(
	id BIGSERIAL PRIMARY KEY,
	pb   INTEGER REFERENCES did_phonebook(id) NOT NULL,
	dnum VARCHAR(64) NOT NULL,
	name TEXT NOT NULL
);

CREATE INDEX did_phonebook_entry_index ON did_pb_entry(pb,dnum);

GRANT SELECT ON did_phonebook TO a2b_group;
GRANT SELECT ON did_pb_entry TO a2b_group;
-- moved into a dedicated tariffgroup..
-- -- Define selling rules for DIDs.
-- -- The rate engine functions will be called against the destination of 
-- -- the did engine.
-- -- In fact, the sell plan here can suggest allowed/denied destinations
-- CREATE TABLE did_group_sell (
--     btid INTEGER NOT NULL REFERENCES cc_didgroup(id) ON DELETE CASCADE,
--     rtid integer NOT NULL REFERENCES cc_retailplan(id) ON DELETE CASCADE,
--     PRIMARY KEY(btid,rtid)
-- );

--eof
