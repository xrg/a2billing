-- Functions that generate invoices.

CREATE OR REPLACE FUNCTION agent_create_invoice(s_agentid BIGINT, s_startdate TIMESTAMP, s_stopdate TIMESTAMP) 
	RETURNS bigint AS $$
DECLARE
	sum_charges NUMERIC;
	sum_calls   NUMERIC;
	agent_vat   NUMERIC;
	agent_commission NUMERIC;
	sum_amount  NUMERIC;
	sum_tax     NUMERIC;
	sum_bills   NUMERIC;
	ret_id      BIGINT;
	s_paytype   INTEGER;
BEGIN
	-- Step x: check for overlapping invoices
	PERFORM id FROM cc_invoices WHERE agentid = s_agentid 
		AND ( cover_startdate BETWEEN s_startdate AND s_stopdate
			OR cover_enddate BETWEEN s_startdate AND s_stopdate);
	IF FOUND THEN
		RAISE EXCEPTION 'Invoices already exist for this time period and agent';
	END IF;
	
	-- Step 1: check for unchecked charges
	-- TODO: we MISS charges on agent's cards w/o agentid in table.
	PERFORM id FROM cc_card_charge WHERE agentid = s_agentid AND
		(creationdate BETWEEN s_startdate AND s_stopdate)
		AND from_agent = true AND checked IS NULL;
	IF FOUND THEN
		RAISE EXCEPTION 'Unchecked charges found in that period';
	END IF;
	
	SELECT vat, commission INTO agent_vat,agent_commission FROM cc_agent WHERE id = s_agentid;
	IF NOT FOUND THEN
		RAISE EXCEPTION 'Can''t find VAT for agent %!',s_agentid;
	END IF;
	
	-- Create the invoice. We need its id this early.
	INSERT INTO cc_invoices(agentid, cover_startdate, cover_enddate )
		VALUES(s_agentid, s_startdate,s_stopdate)
		RETURNING id INTO STRICT ret_id;
	
	-- Step x: Sum calls and charges (here goes anything that should be invoiced)
	UPDATE cc_card_charge SET invoice_id = ret_id 
		WHERE agentid = s_agentid AND invoice_id IS NULL
		AND from_agent = true AND checked IS NOT NULL
		AND (creationdate BETWEEN s_startdate AND s_stopdate) ;
		
	-- FIXME: commission on charges? VAT on them? Non-invoiced charges?
	SELECT COALESCE(SUM(amount),0.0) INTO sum_charges FROM cc_card_charge WHERE invoice_id = ret_id;
	
	UPDATE cc_call SET invoice_id = ret_id FROM cc_card, cc_card_group
		WHERE cc_card.grp = cc_card_group.id AND cc_card_group.agentid = s_agentid
		    AND cc_card.id = cc_call.cardid AND cc_call.invoice_id IS NULL
		    AND starttime BETWEEN s_startdate AND s_stopdate;

	-- That view subtracts the commission.
	SELECT COALESCE(sum(agentbill),0.0), SUM(sessionbill) INTO sum_calls, sum_bills FROM cc_agent_calls3_v
		WHERE invoice_id = ret_id;
	-- Create invoice.

	-- Automatically credit the commission to the agent!
	IF sum_bills IS NOT NULL AND sum_bills > 0.0 THEN
		SELECT id INTO s_paytype FROM cc_paytypes WHERE preset = 'auto-commission';
		IF NOT FOUND THEN
			RAISE WARNING 'No preset found for auto-commission, cannot charge.';
		ELSE
			INSERT INTO cc_agentpay(credit,pay_type,agentid,invoice_id)
				VALUES(sum_bills*agent_commission, s_paytype,s_agentid, ret_id);
		END IF;
	END IF;
	
	sum_amount := (sum_calls*100.0)/(100.0 +agent_vat) + sum_charges;
	--RAISE NOTICE 'Sum calls: %, bills: %, amount: %', sum_calls,sum_bills, sum_amount;
	sum_tax :=(sum_calls*agent_vat)/(100.0 + agent_vat);

	UPDATE cc_invoices SET amount = sum_amount, tax = sum_tax, total =sum_amount + sum_tax
		WHERE id = ret_id;
	RETURN ret_id;
END; $$ LANGUAGE PLPGSQL STRICT VOLATILE;


-- SELECT agentid, cover_enddate + interval '0.01sec' FROM cc_invoices WHERE agentid IS NOT NULL;

CREATE OR REPLACE FUNCTION agent_create_all_invoices(s_agentid BIGINT, s_intv INTERVAL) RETURNS void AS $$
DECLARE
	s_time TIMESTAMP;
	e_time TIMESTAMP;
	s_trunc TEXT;
BEGIN
	SELECT MAX(cover_enddate) + interval '0.01 sec' INTO s_time FROM cc_invoices WHERE agentid = s_agentid;
	IF NOT FOUND OR s_time IS NULL THEN
		SELECT date_trunc('day',min(starttime)) INTO s_time 
			FROM cc_call,cc_card_group,cc_card
			WHERE cc_card_group.agentid = s_agentid
				AND cc_call.cardid= cc_card.id AND cc_card.grp = cc_card_group.id;
		IF NOT FOUND OR s_time IS NULL THEN
			SELECT date_trunc('day',now() - s_intv) INTO s_time;
		END IF;
	END IF;
	
	RAISE NOTICE 'First date: %',s_time;
	s_trunc := CASE WHEN s_intv = interval '1 year' THEN 'year'
		WHEN s_intv = interval '1 month' THEN 'month' 
		WHEN s_intv = interval '1 day' THEN 'day' ELSE 'month' END;
	LOOP
		e_time := date_trunc(s_trunc, s_time + s_intv) - interval '0.01 sec';
		IF e_time > now() THEN 
			EXIT;
		END IF;
		
		RAISE DEBUG 'Invoice from % to %',s_time, e_time;
		PERFORM agent_create_invoice(s_agentid, s_time, e_time);
		
		s_time := e_time + interval '0.01 sec';
	END LOOP;
END; $$ LANGUAGE PLPGSQL STRICT VOLATILE;

CREATE OR REPLACE FUNCTION card_create_invoice(s_cardid BIGINT, s_startdate TIMESTAMP, s_stopdate TIMESTAMP) 
	RETURNS bigint AS $$
DECLARE
	sum_charges NUMERIC;
	sum_calls   NUMERIC;
	card_vat   NUMERIC;
	sum_amount  NUMERIC;
	sum_tax     NUMERIC;
	sum_bills   NUMERIC;
	ret_id      BIGINT;
	s_paytype   INTEGER;
	s_agentid   INTEGER;
BEGIN
	-- Step x: check for overlapping invoices
	PERFORM id FROM cc_invoices WHERE cardid = s_cardid 
		AND ( cover_startdate BETWEEN s_startdate AND s_stopdate
			OR cover_enddate BETWEEN s_startdate AND s_stopdate);
	IF FOUND THEN
		RAISE EXCEPTION 'Invoices already exist for this time period and card';
	END IF;
	
	-- Step 1: check for unchecked charges
	PERFORM id FROM cc_card_charge WHERE card = s_cardid AND
		(creationdate BETWEEN s_startdate AND s_stopdate)
		AND from_agent = true AND checked IS NULL;
	IF FOUND THEN
		RAISE EXCEPTION 'Unchecked charges (by agent ?) found in that period';
	END IF;
	
	SELECT vat, agentid INTO card_vat,s_agentid 
		FROM cc_card,cc_card_group 
			WHERE cc_card.id = s_cardid
			  AND cc_card.grp = cc_card_group.id;
	IF NOT FOUND THEN
		RAISE EXCEPTION 'Can''t find card,group, VAT for card %!',s_cardid;
	END IF;
	
	IF s_agentid IS NOT NULL THEN
		RAISE EXCEPTION 'Card belongs to an agent. Cannot have a separate invoice.';
	END IF;
	
	
	-- Create the invoice. We need its id this early.
	INSERT INTO cc_invoices(cardid, cover_startdate, cover_enddate )
		VALUES(s_cardid, s_startdate,s_stopdate)
		RETURNING id INTO STRICT ret_id;
	
	-- Step x: Sum calls and charges (here goes anything that should be invoiced)
	UPDATE cc_card_charge SET invoice_id = ret_id 
		WHERE card = s_cardid AND invoice_id IS NULL
		AND ((from_agent = true AND checked IS NOT NULL) 
		     OR (from_agent = false))
		AND (creationdate BETWEEN s_startdate AND s_stopdate) ;
		
	-- FIXME: commission on charges? VAT on them? Non-invoiced charges?
	SELECT COALESCE(SUM(amount),0.0) INTO sum_charges FROM cc_card_charge 
		WHERE invoice_id = ret_id;
	
	UPDATE cc_call SET invoice_id = ret_id FROM cc_card, cc_card_group
		WHERE cc_call.cardid = s_cardid
		  AND cc_card.grp = cc_card_group.id
		  AND cc_card_group.agentid IS NULL
		  AND cc_card.id = cc_call.cardid AND cc_call.invoice_id IS NULL
		  AND starttime BETWEEN s_startdate AND s_stopdate;

	-- That view subtracts the commission.
	SELECT SUM(sessionbill) INTO sum_calls, sum_bills FROM cc_call
		WHERE invoice_id = ret_id;
	-- Create invoice.

	
	sum_amount := (sum_calls*100.0)/(100.0 +card_vat) + sum_charges;
	--RAISE NOTICE 'Sum calls: %, bills: %, amount: %', sum_calls,sum_bills, sum_amount;
	sum_tax :=(sum_calls*card_vat)/(100.0 + card_vat);

	UPDATE cc_invoices SET amount = sum_amount, tax = sum_tax, total =sum_amount + sum_tax
		WHERE id = ret_id;
	RETURN ret_id;
END; $$ LANGUAGE PLPGSQL STRICT VOLATILE;

CREATE OR REPLACE FUNCTION card_create_all_invoices(s_cardid BIGINT, s_intv INTERVAL) RETURNS void AS $$
DECLARE
	s_time TIMESTAMP;
	e_time TIMESTAMP;
	s_trunc TEXT;
BEGIN
	SELECT MAX(cover_enddate) + interval '0.01 sec' INTO s_time 
		FROM cc_invoices WHERE cardid = s_cardid;
	IF NOT FOUND OR s_time IS NULL THEN
		SELECT date_trunc('day',min(starttime)) INTO s_time 
			FROM cc_call
			WHERE cc_call.cardid= s_cardid;

		IF NOT FOUND OR s_time IS NULL THEN
			SELECT date_trunc('day',now() - s_intv) INTO s_time;
		END IF;
	END IF;
	
	RAISE NOTICE 'First date: %',s_time;
	s_trunc := CASE WHEN s_intv = interval '1 year' THEN 'year'
		WHEN s_intv = interval '1 month' THEN 'month' 
		WHEN s_intv = interval '1 day' THEN 'day' ELSE 'month' END;
	LOOP
		e_time := date_trunc(s_trunc, s_time + s_intv) - interval '0.01 sec';
		IF e_time > now() THEN 
			EXIT;
		END IF;
		
		RAISE DEBUG 'Invoice from % to %',s_time, e_time;
		PERFORM card_create_invoice(s_cardid, s_time, e_time);
		
		s_time := e_time + interval '0.01 sec';
	END LOOP;
END; $$ LANGUAGE PLPGSQL STRICT VOLATILE;

-- SELECT card_create_all_invoices(cc_card.id, '1 year') FROM cc_card, cc_card_group 
--   WHERE cc_card.grp = cc_card_group AND cc_card_group.agentid IS NULL; 
--eof
