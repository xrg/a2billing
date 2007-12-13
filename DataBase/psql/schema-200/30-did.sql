CREATE TABLE cc_didgroup (
    id  	BIGSERIAL NOT NULL PRIMARY KEY,
    iduser 	INTEGER DEFAULT 0 NOT NULL,	
    creationdate TIMESTAMP WITHOUT TIME ZONE DEFAULT NOW(),
    didgroupname TEXT NOT NULL
);

CREATE TABLE cc_did (
    id BIGSERIAL 	NOT NULL PRIMARY KEY,
    didgroup 		BIGINT NOT NULL REFERENCES cc_didgroup(id),
    country 		INTEGER NOT NULL,
    activated 		INTEGER DEFAULT 1 NOT NULL,
    reserved 		INTEGER DEFAULT 0,
    iduser 		BIGINT DEFAULT 0 NOT NULL,
    did 		TEXT NOT NULL UNIQUE,
    creationdate 	TIMESTAMP WITHOUT TIME ZONE DEFAULT NOW(),	
    startingdate 	TIMESTAMP WITHOUT TIME ZONE DEFAULT NOW(),
    expirationdate 	TIMESTAMP WITHOUT TIME ZONE,
    description 	TEXT,
    secondusedreal 	INTEGER DEFAULT 0,
    billingtype 	INTEGER DEFAULT 0,
    fixrate 		NUMERIC(12,4) NOT NULL
);
-- billtype: 0 = fix per month + dialoutrate, 1= fix per month, 2 = dialoutrate, 3 = free

-- ALTER TABLE cc_did RENAME id_cc_didgroup TO didgroup;
-- ALTER TABLE cc_did RENAME id_cc_country TO country;
	


CREATE TABLE cc_did_destination (
    id 			BIGSERIAL NOT NULL PRIMARY KEY,
    destination 	TEXT NOT NULL,
    priority 		INTEGER DEFAULT 0 NOT NULL,
    card 		BIGINT NOT NULL REFERENCES cc_card(id),
    did 		BIGINT NOT NULL REFERENCES cc_did(id),	
    creationdate 	TIMESTAMP WITHOUT TIME ZONE DEFAULT NOW(),
    activated 		INTEGER DEFAULT 1 NOT NULL,
    secondusedreal 	INTEGER DEFAULT 0,
    voip_call 		INTEGER DEFAULT 0
);


-- ALTER TABLE cc_did_destination RENAME id_cc_card TO card;
-- ALTER TABLE cc_did_destination RENAME id_cc_did TO did;
