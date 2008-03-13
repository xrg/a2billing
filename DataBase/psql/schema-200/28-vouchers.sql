-- voucher


CREATE TABLE vouchers (
    id 			BIGSERIAL PRIMARY KEY,
    creationdate 	TIMESTAMP WITHOUT TIME ZONE DEFAULT NOW(),
    usedate 		TIMESTAMP WITHOUT TIME ZONE,
    expirationdate 	TIMESTAMP WITHOUT TIME ZONE,
    card_grp		INTEGER NOT NULL REFERENCES cc_card_group(id), -- only these cards can use it
    voucher 		TEXT NOT NULL,
    card_id 		BIGINT REFERENCES cc_card(id),
    tag 		TEXT,
    credit 		NUMERIC(12,4) NOT NULL,
    activated 		BOOLEAN DEFAULT true NOT NULL,
--    used 		INTEGER DEFAULT 0,
--     currency 		VARCHAR(3) DEFAULT 'USD'::VARCHAR, will use the group's !
    UNIQUE(card_grp,voucher)
);

-- GRANT SELECT ON vouchers TO a2b_group; needed ?

--eof
