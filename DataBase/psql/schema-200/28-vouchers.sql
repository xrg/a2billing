-- voucher


CREATE TABLE cc_voucher (
    id 								BIGSERIAL NOT NULL,
    creationdate 						TIMESTAMP WITHOUT TIME ZONE DEFAULT NOW(),
    usedate 							TIMESTAMP WITHOUT TIME ZONE,
    expirationdate 						TIMESTAMP WITHOUT TIME ZONE,	
    voucher 							TEXT NOT NULL,
    cardid 							bigint REFERENCES cc_card(id),
    tag 							TEXT,
    credit 							NUMERIC(12,4) NOT NULL,    
    activated 							BOOLEAN DEFAULT true NOT NULL,
    used 							INTEGER DEFAULT 0,
    currency 							CHARACTER VARYING(3) DEFAULT 'USD'::CHARACTER varying
);

ALTER TABLE ONLY cc_voucher    ADD CONSTRAINT cc_voucher_pkey PRIMARY KEY (id);
ALTER TABLE ONLY cc_voucher    ADD CONSTRAINT cons_voucher_cc_voucher UNIQUE (voucher);


GRANT SELECT ON cc_voucher TO a2b_group;
--eof

