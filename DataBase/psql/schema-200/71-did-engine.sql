-- Rate-engine related views, functions
-- Copyright (C) P. Christeas, 2008
DROP FUNCTION IF EXISTS DIDEngine(s_dialstring TEXT, s_code TEXT, s_curtime TIMESTAMP WITH TIME ZONE);

DROP TYPE IF EXISTS dideng_result;

CREATE TYPE dideng_result  AS ( 
	card cc_card_dv,
	nplan  INTEGER, -- discovered numplan
	     --- Per-call fields
	dialstring TEXT,
	tgid INTEGER,
	dgid INTEGER, /* DID group ID */
	brid2 BIGINT,
	buyrate2 NUMERIC,
	metric INTEGER
	);


CREATE OR REPLACE FUNCTION DIDEngine(s_dialstring TEXT, s_code TEXT, s_curtime TIMESTAMP WITH TIME ZONE)
	RETURNS SETOF dideng_result AS $$
DECLARE
	p_dbatch RECORD;
	p_card cc_card_dv;
	p_drem  TEXT;
	p_brid2 INTEGER;
	p_brate2 NUMERIC;
	p_res dideng_result;
BEGIN
	-- First, locate the did batch..
	FOR p_dbatch IN SELECT cc_didgroup.id AS dgid, cc_didgroup.tgid, did_batch.*, 
			length(did_batch.dialhead) AS dhlen
		FROM cc_didgroup, did_batch, did_group_batch
		WHERE cc_didgroup.code = s_code AND cc_didgroup.id = did_group_batch.btid
		  AND did_group_batch.dbid = did_batch.id
		  AND did_batch.status = 1 AND did_batch.creationdate < s_curtime
		  AND (did_batch.expiredate IS NULL OR did_batch.expiredate >= s_curtime)
		  AND did_batch.dialhead = ANY (dial_exp_prefix(s_dialstring))
	   LOOP
	   RAISE NOTICE 'Found batch %', p_dbatch.name;
	   p_drem := substr(s_dialstring,p_dbatch.dhlen+1);
	   
	   IF p_dbatch.dmode = 1 THEN
	   	NULL;
	   	
	   ELSIF p_dbatch.dmode = 2 THEN
	   	p_drem := p_dbatch.dialadd || p_drem;
	   		-- Locate buy cost of DID
	   	SELECT cc_buyrate.id, buyrate  INTO p_brid2, p_brate2  FROM cc_buyrate, cc_buy_prefix
	   		WHERE cc_buyrate.idtp = p_dbatch.idtp AND cc_buyrate.id = cc_buy_prefix.brid
	   		  AND cc_buy_prefix.dialprefix = ANY (dial_exp_prefix(p_drem))
	   		  ORDER BY length(cc_buy_prefix.dialprefix) DESC LIMIT 1
	   		  /*AND cc_buyrate.start_date ...*/ ;
	   	IF NOT FOUND THEN
	   		RAISE WARNING 'Cannot match DID buy rate';
	   		CONTINUE;
	   	END IF;
	   	
	   	RAISE NOTICE 'Searching for useralias % in numplan %..',p_drem, p_dbatch.nplan;
	   	FOR p_card IN SELECT * FROM cc_card_dv
	   		WHERE cc_card_dv.numplan = p_dbatch.nplan
	   		  AND cc_card_dv.useralias = p_drem LOOP
			
			-- Automatically format the target string by appending dialfld2 and useralias
	   		SELECT p_card AS card,
	   			p_dbatch.nplan, p_dbatch.dialfld2 ||p_drem AS dialstring,
				p_dbatch.tgid, p_dbatch.dgid,
				p_brid2 AS brid2, p_brate2 AS buyrate2, p_dbatch.metric
	   		    INTO STRICT p_res;
	   		RETURN NEXT p_res;
	   	END LOOP;
	   	IF NOT FOUND THEN
	   		RAISE NOTICE 'No card found by useralias!';
	   	END IF;
	   ELSIF p_dbatch.dmode = 3 THEN /* Charge one, dial other */
	   	p_drem := p_dbatch.dialadd || p_drem;
	   		-- Locate buy cost of DID
	   	SELECT cc_buyrate.id, buyrate  INTO p_brid2, p_brate2  FROM cc_buyrate, cc_buy_prefix
	   		WHERE cc_buyrate.idtp = p_dbatch.idtp AND cc_buyrate.id = cc_buy_prefix.brid
	   		  AND cc_buy_prefix.dialprefix = ANY (dial_exp_prefix(p_drem))
	   		  ORDER BY length(cc_buy_prefix.dialprefix) DESC LIMIT 1
	   		  /*AND cc_buyrate.start_date ...*/ ;
	   	IF NOT FOUND THEN
	   		RAISE WARNING 'Cannot match DID buy rate';
	   		CONTINUE;
	   	END IF;
	   	
	   	RAISE NOTICE 'Searching for useralias % in numplan %..',p_drem, p_dbatch.nplan;
	   	FOR p_card IN SELECT * FROM cc_card_dv
	   		WHERE cc_card_dv.numplan = p_dbatch.nplan
	   		  AND cc_card_dv.useralias = p_drem LOOP
			
			-- Automatically format the target string by appending dialfld2 and useralias
	   		SELECT p_card AS card,
	   			p_dbatch.nplan, p_dbatch.rnplan, p_dbatch.alert_info,
	   			p_dbatch.dialfld2 AS dialstring,
				p_dbatch.tgid, p_dbatch.dgid,
				p_brid2 AS brid2, p_brate2 AS buyrate2, p_dbatch.metric
	   		    INTO STRICT p_res;
	   		RETURN NEXT p_res;
	   	END LOOP;
	   	IF NOT FOUND THEN
	   		RAISE NOTICE 'No card found by useralias!';
	   	END IF;
	   ELSE
	   	RAISE WARNING 'Unknown batch mode %',p_dbatch.dmode;
	   END IF;
	   -- RETURN NEXT ...
	END LOOP; -- loop
	
	RETURN;
END;
$$ LANGUAGE PLPGSQL STRICT VOLATILE SECURITY DEFINER;

--eof
