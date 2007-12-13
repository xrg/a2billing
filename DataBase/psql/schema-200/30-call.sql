-- Call table

/** Table holding all "recent" calls.
	The call table will reference the other tables, while archived calls could
	refer to 'names' of other tables */

CREATE TABLE cc_call (
    id bigserial PRIMARY KEY,
    sessionid text NOT NULL,
    uniqueid text NOT NULL,
    cardid BIGINT REFERENCES cc_card(id),
    nasipaddress text,
    starttime TIMESTAMP WITHOUT TIME ZONE DEFAULT NOW(),
    stoptime TIMESTAMP WITHOUT TIME ZONE,
    sessiontime INTEGER,
    calledstation TEXT,
    startdelay INTEGER,
    stopdelay INTEGER,
    terminatecause TEXT,
    srid BIGINT REFERENCES cc_sellrate(id),
    sessionbill numeric(12,4),
    destination text /* cached value from sellrate.name */, 
    brid BIGINT REFERENCES cc_buyrate(id),
    tgid INTEGER REFERENCES cc_tariffgroup(id),
    trunk INTEGER REFERENCES cc_trunk(id),
    src text,
    id_did integer,
    buyrate numeric(15,5) DEFAULT 0,
    buycost numeric(15,5) DEFAULT 0,
    id_card_package_offer integer DEFAULT 0
);


CREATE INDEX cc_call_card_ind ON cc_call USING btree (cardid);
CREATE INDEX cc_call_starttime_ind ON cc_call USING btree (starttime);
-- CREATE INDEX cc_call_terminatecause_ind ON cc_call USING btree (terminatecause); 	
-- CREATE INDEX cc_call_calledstation_ind ON cc_call USING btree (calledstation); 	


-- eof
