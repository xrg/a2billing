--- Helper functions for rate engine

-- Copyright (C) P. Christeas, 2007

CREATE OR REPLACE FUNCTION buy_calc_fwd(duration interval, ratecard cc_ratecard) 
	RETURNS NUMERIC(12,4) AS $$
DECLARE
	brnum NUMERIC(12,4);
	iintv INTEGER;	/* The interval in seconds */
	mod_sec INTEGER;
BEGIN
	iintv := GREATEST(ratecard.buyrateinitblock, EXTRACT(EPOCH FROM duration));
	brnum := CAST (ratecard.buyrate AS NUMERIC(12,4));
	/* RAISE NOTICE 'Found card with rate %.',brnum;*/
	IF ratecard.buyrateincrement> 0 THEN
		mod_sec := MOD(iintv,ratecard.buyrateincrement);
		if mod_sec > 0 THEN
			iintv := iintv + (ratecard.buyrateincrement - mod_sec);
		END IF;
	END IF;
	RETURN ((brnum * iintv ) / 60);
END; $$ LANGUAGE PLPGSQL STRICT STABLE;


CREATE OR REPLACE FUNCTION sell_calc_part(INOUT duration INTEGER, maxdur INTEGER,
	rate NUMERIC(12,6), block INTEGER, OUT result NUMERIC(12,4) )
	RETURNS RECORD AS $$
DECLARE
	mod_sec INTEGER;
	iintv INTEGER;
BEGIN
	IF duration < 0 THEN
		RAISE EXCEPTION 'Negative duration in sell_calc_part!';
	END IF;
	
	iintv :=duration;
	IF maxdur > 0 AND iintv > maxdur THEN
		iintv := maxdur;
	END IF;
	
	duration := duration - iintv;
	
	IF block > 0 THEN
		mod_sec := MOD(iintv,block);
		if mod_sec > 0 THEN
			iintv := iintv + (block - mod_sec);
		END IF;
	END IF;
	result := ((rate * iintv ) / 60);
END; $$ LANGUAGE PLPGSQL STRICT STABLE;

CREATE OR REPLACE FUNCTION sell_calc_fwd(duration interval, ratecard cc_ratecard, freetime INTEGER) 
	RETURNS NUMERIC(12,4) AS $$
DECLARE
	dur INTEGER;
	cost NUMERIC(12,4);
	rec RECORD;
BEGIN
	dur := EXTRACT(EPOCH FROM duration);
	IF dur < 0 THEN
		RAISE WARNING 'Negative duration, using 0 !' ;
		dur := 0;
	END IF;
	
	IF dur < ratecard.initblock THEN
		dur := ratecard.initblock;
	END IF;

	IF freetime > dur THEN
		RAISE WARNING 'Freetime supplied > duration';
		dur := 0;
	ELSIF freetime > 0 THEN
		dur := dur - freetime;
	END IF;
	
	cost := ratecard.connectcharge + ratecard.disconnectcharge;
	
	IF ratecard.chargea IS NOT NULL AND ratecard.chargea <> 0 AND 
		ratecard.timechargea IS NOT NULL AND ratecard.timechargea <> 0 THEN
		
		cost := cost + ratecard.stepchargea;
		
		SELECT * INTO rec FROM sell_calc_part(dur, ratecard.timechargea,ratecard.chargea,ratecard.billingblocka);
		cost := cost + rec.result;
		dur := rec.duration;
		
		IF dur > 0 AND ratecard.chargeb IS NOT NULL AND ratecard.chargeb <> 0 AND 
			ratecard.timechargeb IS NOT NULL AND ratecard.timechargeb <> 0 THEN
		
			cost := cost + ratecard.stepchargeb;
		
			SELECT * INTO rec FROM sell_calc_part(dur, ratecard.timechargeb,ratecard.chargeb, ratecard.billingblockb);
			cost := cost + rec.result;
			dur := rec.duration;

			IF dur > 0 AND ratecard.chargec IS NOT NULL AND ratecard.chargec <> 0 AND 
				ratecard.timechargec IS NOT NULL AND ratecard.timechargec <> 0 THEN
			
				cost := cost + ratecard.stepchargec;
			
				SELECT * INTO rec FROM sell_calc_part(dur, ratecard.timechargec,ratecard.chargec,
					ratecard.billingblockc);
				cost := cost + rec.result;
				dur := rec.duration;
			END IF;
		END IF;
	END IF;

	IF dur > 0 THEN
		SELECT * INTO rec FROM sell_calc_part(dur, -1 ,ratecard.rateinitial, ratecard.billingblock);
		cost := cost + rec.result;
		dur := rec.duration;
	END IF;
	
	RETURN cost;
END;  $$ LANGUAGE PLPGSQL STRICT STABLE;

CREATE OR REPLACE FUNCTION sell_calc_fwd(duration interval, ratecard cc_ratecard) 
	RETURNS NUMERIC(12,4) AS $$
BEGIN

	RETURN sell_calc_fwd(duration,ratecard,0);
END; $$ LANGUAGE PLPGSQL STRICT STABLE;

CREATE OR REPLACE FUNCTION dial_exp_prefix(str TEXT) RETURNS TEXT[] AS $$
DECLARE
	slen INTEGER;
	ret TEXT[];
	i INTEGER;
BEGIN
	slen := char_length(str);
	IF (slen > 12) THEN
		slen := 12;
	END IF;
	
	FOR i IN 0..slen LOOP
		ret := array_append(ret, substr(str,1,i));
	END LOOP;
	
	RETURN ret;
END;
$$ LANGUAGE plpgsql STRICT STABLE;
