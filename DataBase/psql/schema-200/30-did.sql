-- DID tables

-- This is a typical table that gives a name to groups. 
-- No important data here.
CREATE TABLE cc_didgroup (
    id  	BIGSERIAL NOT NULL PRIMARY KEY,
    name TEXT NOT NULL,
   code TEXT NOT NULL DEFAULT '' -- lets the did be matched against the trunk it comes from
);


-- A batch should contain all settings about a bunch of DIDs
CREATE TABLE did_batch(
	id SERIAL PRIMARY KEY,
	name TEXT NOT NULL,
	pname TEXT,  -- Name, visible to customers
	dmode INTEGER NOT NULL,  -- dmode controls the behaviour of the DID
	status INTEGER DEFAULT 1,
	provider INTEGER REFERENCES cc_provider(id),
	creationdate TIMESTAMP NOT NULL DEFAULT NOW(),
	expiredate   TIMESTAMP,
	secondsused  BIGINT,
		/* The dialhead must match the beginning of the incoming number=DID */
	dialhead     TEXT NOT NULL,
		/* Add these digits in some modes (eg. for useralias ) */
	dialadd      TEXT NOT NULL DEFAULT '',
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
    id 		BIGSERIAL NOT NULL PRIMARY KEY,
    batch	INTEGER NOT NULL REFERENCES did_batch(id),
    did 	TEXT NOT NULL,
    card 	BIGINT NOT NULL REFERENCES cc_card(id),
 --    did 	BIGINT NOT NULL REFERENCES cc_did(id),	
    creationdate TIMESTAMP DEFAULT NOW(),
    expiredate	TIMESTAMP,
    status 	INTEGER DEFAULT 1 NOT NULL,
    secondused 	INTEGER DEFAULT 0,
    target	TEXT -- may be null for auto matching.
);


-- Associate did groups with batches
CREATE TABLE did_group_batch (
    btid INTEGER NOT NULL REFERENCES cc_didgroup(id) ON DELETE CASCADE,
    dbid integer NOT NULL REFERENCES did_batch(id) ON DELETE CASCADE,
    PRIMARY KEY(btid,dbid)
);

-- Define selling rules for DIDs.
-- The rate engine functions will be called against the destination of 
-- the did engine.
-- In fact, the sell plan here can suggest allowed/denied destinations
CREATE TABLE did_group_sell (
    btid INTEGER NOT NULL REFERENCES cc_didgroup(id) ON DELETE CASCADE,
    rtid integer NOT NULL REFERENCES cc_retailplan(id) ON DELETE CASCADE,
    PRIMARY KEY(btid,rtid)
);

--eof
