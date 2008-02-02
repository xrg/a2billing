-- Aggregate calls

-- The aggregate of call attempts needs to be materialized in a table, because
-- it will be called all the time and would otherwise incur significant db load.

CREATE TABLE cc_call_v (
    cmode VARCHAR(10) NOT NULL DEFAULT 'standard',
    sessionid text NOT NULL,
    uniqueid text NOT NULL,
    cardid BIGINT REFERENCES cc_card(id),
    srvid INTEGER REFERENCES cc_a2b_server(id),
    nasipaddress text,
    starttime TIMESTAMP WITHOUT TIME ZONE DEFAULT NOW(),
    stoptime TIMESTAMP WITHOUT TIME ZONE,
    sessiontime INTEGER,
    calledstation TEXT,
    startdelay INTEGER,
    stopdelay INTEGER,
    tcause TEXT,
    hupcause INTEGER,
    cause_ext TEXT,
    attempt INTEGER,
    srid BIGINT REFERENCES cc_sellrate(id),
    sessionbill numeric(12,4),
    destination text,
    brid BIGINT REFERENCES cc_buyrate(id),
    tgid INTEGER REFERENCES cc_tariffgroup(id),
    trunk INTEGER REFERENCES cc_trunk(id),
    qval FLOAT,
    src text,
    id_did integer,
    buycost numeric(15,5),
    id_card_package_offer integer DEFAULT 0,
    invoice_id BIGINT REFERENCES cc_invoices(id) ON DELETE SET NULL,
    PRIMARY KEY(sessionid, uniqueid, cardid)
);

CREATE INDEX cc_call_v_starttime_ind ON cc_call_v USING btree (starttime);
CREATE INDEX cc_call_v_sid_ind ON cc_call_v USING btree (sessionid);
CREATE INDEX cc_call_v_uid_ind ON cc_call_v USING btree (uniqueid);
CREATE INDEX cc_call_v_cid_ind ON cc_call_v USING btree (cardid);

-- I don't think they are needed, cc_call_v is manipulated by the triggers..
-- GRANT SELECT,INSERT,UPDATE ON cc_call TO a2b_group;
-- GRANT SELECT,UPDATE ON cc_call_id_seq TO a2b_group;
