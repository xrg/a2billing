-- Test the rate engine


-- \i schema-200/60-rate-engine.sql


-- SELECT * FROM RateEngine2('12','45');


/** Return seconds a call can last at that rate, if the customer has so much credit */
CREATE OR REPLACE FUNCTION sell_calc_rev(rate cc_sellrate, dmoney NUMERIC(12,4)) RETURNS INTEGER AS $$
DECLARE
	money NUMERIC(12,4);
	remtime INTEGER;
	plustime INTEGER;
BEGIN
	money := dmoney;
	remtime := 0;

	money := money - rate.connectcharge;
	money := money - rate.disconnectcharge;
	IF rate.rateinitial = 0 THEN
		RETURN 604800; -- One week!
	END IF;
	IF rate.rateinitial < 0 THEN
		RAISE EXCEPTION 'Cannot compute negative rate for id=%', rate.id;
	END IF;
	
	plustime := floor(60.0*money/rate.rateinitial);
	IF plustime < rate.initblock THEN
		RAISE NOTICE 'Plustime: %, initblock % ', plustime, rate.initblock;
		RETURN remtime;
	END IF;
	IF rate.billingblock > 0 THEN
		remtime := remtime + floor(plustime /rate.billingblock)*rate.billingblock;
	ELSE
		remtime := remtime + plustime;
	END IF;
	RETURN remtime;
END;
$$ LANGUAGE plpgsql STRICT IMMUTABLE;

-- SELECT sell_calc_rev(cc_sellrate.*,12.2) FROM cc_sellrate WHERE id = 55659;

-- SELECT *  FROM (
	SELECT DISTINCT ON (cc_retailplan.id) cc_retailplan.id AS rpid, cc_retailplan.name, 
		cc_sellrate.id, srid,cc_sell_prefix.dialprefix, cc_sellrate.destination,
		cc_sellrate.rateinitial, sell_calc_rev(cc_sellrate.*,10.3)
		FROM cc_sellrate, cc_sell_prefix , cc_retailplan
		WHERE cc_sell_prefix.dialprefix = ANY(dial_exp_prefix('88123564'))
			AND cc_sellrate.id = cc_sell_prefix.srid
			AND cc_retailplan.id = cc_sellrate.idrp
		ORDER BY cc_retailplan.id, length(cc_sell_prefix.dialprefix) DESC
-- 	) AS allsellrates
-- 		ORDER BY rateinitial ASC
	;
