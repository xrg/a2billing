-- Call table

/** Table holding all "recent" calls.
	The call table will reference the other tables, while archived calls could
	refer to 'names' of other tables */

CREATE TABLE cc_call (
    id bigserial PRIMARY KEY,
    sessionid text NOT NULL,
    uniqueid text NOT NULL, /* NOT unique among failovers */
    cardid BIGINT REFERENCES cc_card(id),
    nasipaddress text,
    starttime TIMESTAMP WITHOUT TIME ZONE DEFAULT NOW(),
    stoptime TIMESTAMP WITHOUT TIME ZONE, /* if null, call is in progress */
    sessiontime INTEGER,
    calledstation TEXT,
    startdelay INTEGER,
    stopdelay INTEGER,
    tcause TEXT, /* was: terminatecause, asterisk's DIALSTATUS */
    cause_ext TEXT, /* extended status, if available */
    attempt INTEGER, /* if failover, next call will have =2 etc. */
    srid BIGINT REFERENCES cc_sellrate(id),
    sessionbill numeric(12,4),
    destination text /* cached value from sellrate.name */,
    brid BIGINT REFERENCES cc_buyrate(id),
    tgid INTEGER REFERENCES cc_tariffgroup(id),
    trunk INTEGER REFERENCES cc_trunk(id),
    qval FLOAT, /* arbitrary, for statistics */
    src text,
    id_did integer,
    buycost numeric(15,5),
    id_card_package_offer integer DEFAULT 0
);


CREATE INDEX cc_call_card_ind ON cc_call USING btree (cardid);
CREATE INDEX cc_call_starttime_ind ON cc_call USING btree (starttime);
-- CREATE INDEX cc_call_terminatecause_ind ON cc_call USING btree (terminatecause); 	
-- CREATE INDEX cc_call_calledstation_ind ON cc_call USING btree (calledstation); 	

/* Use a composite type to aggregate over the last attempt for a call */
CREATE TYPE call_result AS(
	attempt INTEGER,
	srid BIGINT,
	brid BIGINT,
	tcause TEXT,
	cause_ext TEXT,
	trunk INTEGER);
	
CREATE OR REPLACE FUNCTION last_call_result_fn(call_result, call_result) RETURNS call_result AS $$
	SELECT CASE WHEN ($1 IS NULL) THEN $2 
		WHEN ($2.attempt > $1.attempt) THEN $2
		ELSE $1 
		END;
$$ LANGUAGE SQL STRICT IMMUTABLE;

CREATE AGGREGATE last_attempt(call_result) (
    sfunc = last_call_result_fn,
    stype = call_result );

--Return type of card_call_lock()
CREATE TYPE card_call_lock_t AS
	(base NUMERIC(12,4), local NUMERIC(12,4), currency CHARACTER(3), language TEXT, inuse INTEGER);

-- eof
