-- voucher


CREATE TABLE vouchers (
    id 								BIGSERIAL NOT NULL,
    creationdate 					TIMESTAMP WITHOUT TIME ZONE DEFAULT NOW(),
    usedate 						TIMESTAMP WITHOUT TIME ZONE,
    expirationdate 					TIMESTAMP WITHOUT TIME ZONE,	
    voucher 						TEXT NOT NULL,
    card_id 						BIGINT REFERENCES cc_card(id),
    tag 							TEXT,
    credit 							NUMERIC(12,4) NOT NULL,    
    activated 						BOOLEAN DEFAULT true NOT NULL,
    used 							INTEGER DEFAULT 0,
    currency 						CHARACTER VARYING(3) DEFAULT 'USD'::CHARACTER varying
);

ALTER TABLE ONLY vouchers    ADD CONSTRAINT vouchers_pkey PRIMARY KEY (id);
ALTER TABLE ONLY vouchers    ADD CONSTRAINT cons_voucher_vouchers UNIQUE (voucher);


GRANT SELECT ON vouchers TO a2b_group;
--eof
