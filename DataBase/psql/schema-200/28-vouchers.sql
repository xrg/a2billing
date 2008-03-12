-- voucher


CREATE TABLE vouchers (
    id 			BIGSERIAL PRIMARY KEY,
    creationdate 	TIMESTAMP WITHOUT TIME ZONE DEFAULT NOW(),
    usedate 		TIMESTAMP WITHOUT TIME ZONE,
    expirationdate 	TIMESTAMP WITHOUT TIME ZONE,
    voucher 		TEXT NOT NULL UNIQUE, -- really?
    card_id 		BIGINT REFERENCES cc_card(id),
    tag 		TEXT,
    credit 		NUMERIC(12,4) NOT NULL,
    activated 		BOOLEAN DEFAULT true NOT NULL,
    used 		INTEGER DEFAULT 0,
    currency 		VARCHAR(3) DEFAULT 'USD'::CHARACTER varying
);

-- GRANT SELECT ON vouchers TO a2b_group; needed ?





-- Function: card_use_voucher(s_cardid int8, s_voucher text)
-- DROP FUNCTION card_use_voucher(s_cardid int8, s_voucher text);
CREATE OR REPLACE FUNCTION card_use_voucher(s_cardid int8, s_voucher text)
  RETURNS "numeric" AS
$BODY$
DECLARE
	r_credit NUMERIC;
	r_credit_conv NUMERIC;	
	r_currency varchar(3);
BEGIN
	SELECT credit, currency
		INTO r_credit, r_currency
		FROM vouchers
		WHERE used=0 AND activated=true AND voucher=s_voucher AND (expirationdate IS NULL OR expirationdate < now());

	IF NOT FOUND THEN
		RAISE EXCEPTION 'card_use_voucher|voucher-no-find|%|Cannot find voucher %.',s_voucher, s_voucher;
	END IF;
	
	IF r_credit IS NOT NULL AND r_credit <= 0 THEN
		RAISE EXCEPTION 'card_use_voucher|voucher-zero|%|voucher is null %.',s_voucher, s_voucher;
	END IF;
	
	SELECT * INTO r_credit_conv FROM conv_currency_from(r_credit, r_currency);
	
	IF NOT FOUND THEN
		RAISE EXCEPTION 'card_use_voucher|conv_currency-failed|%|Cannot convert voucher currency %.',s_voucher, s_voucher;
	END IF;
	
	IF r_credit_conv IS NOT NULL AND r_credit_conv <= 0 THEN
		RAISE EXCEPTION 'card_use_voucher|conv_currency-failed-zero|%|Convert voucher currency is 0 %.',s_voucher, s_voucher;
	END IF;

	UPDATE vouchers SET used=1, card_id=s_cardid, usedate=now() WHERE used=0 AND activated=true AND voucher=s_voucher;
	
	UPDATE cc_card SET credit = credit + r_credit_conv WHERE id = s_cardid;

	RETURN r_credit_conv;

END;
$BODY$
  LANGUAGE 'plpgsql' VOLATILE STRICT SECURITY DEFINER;
ALTER FUNCTION card_use_voucher(s_cardid int8, s_voucher text) OWNER TO a2billing;



--eof
