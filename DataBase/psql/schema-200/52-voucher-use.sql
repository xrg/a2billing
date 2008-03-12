-- Voucher use function


-- Function: card_use_voucher(s_cardid int8, s_voucher text)
-- return: the new credit of the card

CREATE OR REPLACE FUNCTION card_use_voucher(s_cardid BIGINT, s_voucher TEXT) 
	RETURNS numeric AS $$
DECLARE
	r_vid BIGINT;
	r_card_grp INTEGER;
	r_cause TEXT;
	r_credit NUMERIC;
	r_new_credit NUMERIC;
BEGIN
	SELECT grp INTO r_card_grp FROM cc_card WHERE id = s_cardid;
	IF NOT FOUND THEN
		RAISE EXCEPTION 'card_use_voucher|invalid-card|%|Card % not found.',s_voucher,s_cardid;
	END IF;
	
	-- find the voucher, even if it's not usable..
	SELECT vouchers.id,
		(CASE WHEN cc_card_group.def_currency IS NULL THEN credit
		ELSE conv_currency_from(credit, def_currency) END),
		(CASE WHEN (card_id IS NOT NULL OR usedate IS NOT NULL) THEN 'used'
		      WHEN activated = FALSE THEN 'inactive' :: TEXT
		      WHEN (expirationdate IS NOT NULL AND expirationdate <= now()) THEN 'expired'
		      ELSE NULL END) AS cause
	    INTO r_vid,r_credit, r_cause
		FROM vouchers, cc_card_group
		WHERE vouchers.voucher = s_voucher
		  AND cc_card_group.id = r_card_grp
		  AND vouchers.card_grp = cc_card_group.id;

	IF NOT FOUND THEN
		RAISE EXCEPTION 'card_use_voucher|voucher-no-find|%|Cannot find voucher % in group %.',s_voucher, s_voucher, r_card_grp;
	END IF;
	
	IF r_cause IS NOT NULL THEN
		RAISE EXCEPTION 'card_use_voucher|voucher-%|%|Voucher % is %.',r_cause, s_voucher, r_vid, r_cause;
	END IF;
	
	IF r_credit IS NULL OR r_credit <= 0 THEN
		RAISE EXCEPTION 'card_use_voucher|voucher-zero|%|voucher has no credit = %.',s_voucher, r_credit;
	END IF;
	
	UPDATE vouchers SET card_id = s_cardid, usedate=now()
		WHERE id = r_vid;
	
	UPDATE cc_card SET credit = credit + r_credit
		WHERE id = s_cardid AND grp = r_card_grp
		RETURNING credit INTO STRICT r_new_credit;

	-- TODO Is this considered a "payment"? Shouldn't this transaction be marked in
	-- card payments?
	
	RETURN r_new_credit;

END; 
$$ LANGUAGE plpgsql VOLATILE STRICT SECURITY DEFINER;

-- ALTER FUNCTION card_use_voucher(s_cardid int8, s_voucher text) OWNER TO a2billing;

-- TODO: forbid updates to vouchers when they are used!
