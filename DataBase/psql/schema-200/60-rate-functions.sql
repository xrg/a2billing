--- Helper functions for rate engine

-- Copyright (C) P. Christeas, 2007

CREATE OR REPLACE FUNCTION buy_calc_fwd(duration interval, ratecard cc_buyrate) 
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

CREATE OR REPLACE FUNCTION sell_calc_fwd(duration interval, ratecard cc_sellrate, freetime INTEGER) 
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

CREATE OR REPLACE FUNCTION sell_calc_fwd(duration interval, ratecard cc_sellrate) 
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

/* Same as above, but don't expand the empty string
   This will match at least one char */
CREATE OR REPLACE FUNCTION dial_exp_prefix1(str TEXT) RETURNS TEXT[] AS $$
DECLARE
	slen INTEGER;
	ret TEXT[];
	i INTEGER;
BEGIN
	slen := char_length(str);
	IF (slen > 13) THEN
		slen := 13;
	END IF;
	
	FOR i IN 1..slen LOOP
		ret := array_append(ret, substr(str,1,i));
	END LOOP;
	
	RETURN ret;
END;
$$ LANGUAGE plpgsql STRICT STABLE;

/** Calculate the time,money allowed for a period of rating.
    rate: charge per 60 sec
    money: available money to spend
    minblock: minimum billing period (sec)
    maxblock: maximum length of this period (sec), 0 means skip period, -1 means no limit
    bblock:  increments of this period (sec) 0 means 1, per second.
 */
CREATE OR REPLACE FUNCTION sell_calc_rev_part(rate NUMERIC, money NUMERIC, maxblock INTEGER, bblock INTEGER,
	OUT plustime INTEGER, OUT minusmoney NUMERIC(12,4)) AS $$
BEGIN
	IF maxblock = 0 THEN
		plustime := 0;
		minusmoney := 0;
		RETURN;
	END IF;
	
	IF rate <= 0 THEN
		plustime := maxblock ;
		minusmoney := 0;
		RETURN;
	END IF;
	
/*	IF rate < 0 THEN
		RAISE EXCEPTION 'Cannot compute negative rate.';
	END IF;*/
	
	-- Compute maximum time
	plustime := floor(60.0*money/rate);
	
	-- Block (round) that time
	IF bblock > 1 THEN
		plustime := plustime - (plustime % bblock);
	END IF;
	
/*	IF plustime < minblock THEN
		RAISE NOTICE 'Plustime: %, initblock % ', plustime, minblock;
		plustime := 0;
		RETURN ;
	END IF;*/
	
	IF (maxblock >= 0) AND plustime > maxblock THEN
		-- Question: what if maxblock doesn't round to bblock?
		plustime := maxblock;
	END IF;
	
	minusmoney := plustime * rate /60.0;
	
	IF minusmoney > money THEN
		RAISE EXCEPTION 'Why do we end up here (minusmoney > money) ?';
	END IF;
	
	RETURN ;
END;
$$ LANGUAGE plpgsql STRICT IMMUTABLE;

/* NOTE
         WE DO NOW ALLOW charge a/b/c : 0.0, so that periods can be free !
*/

/** Return seconds a call can last at that rate, if the customer has so much credit */
CREATE OR REPLACE FUNCTION sell_calc_rev(rate cc_sellrate, dmoney NUMERIC(12,4)) RETURNS INTEGER AS $$
DECLARE
	money NUMERIC(12,4);
	remtime INTEGER;
	
	plustime INTEGER;
	minusmoney NUMERIC(12,4);
BEGIN
	money := dmoney;
	remtime := 0;

	money := money - rate.connectcharge;
	money := money - rate.disconnectcharge;
	
	-- Period A
	IF rate.timechargea >0 THEN
		SELECT * INTO plustime, minusmoney 
			FROM sell_calc_rev_part(rate.chargea, money - rate.stepchargea, rate.timechargea, rate.billingblocka);
		IF minusmoney IS NULL THEN
			RAISE WARNING 'Not enough money for A.';
			RETURN NULL; -- no call available;
		END IF;
		remtime := remtime + plustime;
		money := money - minusmoney - rate.stepchargea;
	END IF;
	
	-- Period B
	IF rate.timechargeb >0 THEN
		SELECT * INTO plustime, minusmoney 
			FROM sell_calc_rev_part(rate.chargeb, money - rate.stepchargeb, rate.timechargeb, rate.billingblockb);
		IF minusmoney IS NULL THEN
			RAISE WARNING 'Not enough money for B.';
			RETURN NULL; -- no call available;
		END IF;
		remtime := remtime + plustime;
		money := money - minusmoney -rate.stepchargeb;
	END IF;
	
	-- Period C
	IF rate.timechargec >0 THEN
		SELECT * INTO plustime, minusmoney 
			FROM sell_calc_rev_part(rate.chargec, money - rate.stepchargec, rate.timechargec, rate.billingblockc);
		IF minusmoney IS NULL THEN
			RAISE WARNING 'Not enough money for C.';
			RETURN NULL; -- no call available;
		END IF;
		remtime := remtime + plustime;
		money := money - minusmoney - rate.stepchargec;
	END IF;

	SELECT * INTO plustime, minusmoney 
		FROM sell_calc_rev_part(rate.rateinitial, money, -1, rate.billingblock);
	
	IF plustime = -1 THEN
		RETURN 604800; -- One week!
	END IF;
		
	IF minusmoney IS NULL THEN
		RAISE WARNING 'minus money null! ';
		RETURN NULL; -- no call available;
	END IF;
	
	IF money < 0 THEN
		RAISE WARNING 'Money < 0, why?';
		RETURN NULL;
	END IF;
	
	remtime := remtime + plustime;
	
	-- Initblock is applied at the end: it has to cover all the periods.
	IF remtime < rate.initblock THEN
		RAISE NOTICE 'Cannot bill the minimum block';
		RETURN NULL;
	END IF;

	
	RETURN remtime;
END;
$$ LANGUAGE plpgsql STRICT IMMUTABLE;


--eof