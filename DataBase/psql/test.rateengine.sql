-- Test the rate engine


-- \i schema-200/60-rate-engine.sql


-- SELECT * FROM RateEngine2('12','45');

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
	
	IF rate = 0 THEN
		plustime := maxblock ;
		minusmoney := 0;
		RETURN;
	END IF;
	
	IF rate < 0 THEN
		RAISE EXCEPTION 'Cannot compute negative rate.';
	END IF;
	
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
	
	-- Initblock is applied at the end: it has to cover all the periods.
	IF remtime < rate.initblock THEN
		RAISE NOTICE 'Cannot bill the minimum block';
		RETURN NULL;
	END IF;

	remtime := remtime + plustime;
	
	RETURN remtime;
END;
$$ LANGUAGE plpgsql STRICT IMMUTABLE;

-- SELECT sell_calc_rev(cc_sellrate.*,12.2) FROM cc_sellrate WHERE id = 55659;

SELECT *  FROM (
	SELECT DISTINCT ON (cc_retailplan.id) cc_retailplan.id AS rpid, cc_retailplan.name, 
		cc_sellrate.id, srid,cc_sell_prefix.dialprefix, cc_sellrate.destination,
		cc_sellrate.rateinitial, sell_calc_rev(cc_sellrate.*,10.3) AS sell_timeout
		FROM cc_sellrate, cc_sell_prefix , cc_retailplan
		WHERE cc_sell_prefix.dialprefix = ANY(dial_exp_prefix('88123564'))
			AND cc_sellrate.id = cc_sell_prefix.srid
			AND cc_retailplan.id = cc_sellrate.idrp
		ORDER BY cc_retailplan.id, length(cc_sell_prefix.dialprefix) DESC
	) AS allsellrates
		ORDER BY sell_timeout DESC
	;


-- SELECT * from sell_calc_rev_part( 0.00, 0.09, 30, 60, 10);
-- eof
