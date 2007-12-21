-- Caller id


CREATE TABLE cc_callerid (
    id bigserial PRIMARY KEY,
    cid text NOT NULL UNIQUE,
    cardid bigint NOT NULL REFERENCES cc_card(id),
    activated boolean DEFAULT true NOT NULL
);


GRANT SELECT ON cc_callerid TO a2b_group;
--eof
