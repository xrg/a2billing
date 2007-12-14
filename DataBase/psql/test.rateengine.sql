-- Test the rate engine


-- \i schema-200/60-rate-engine.sql


-- SELECT * FROM RateEngine2('12','45');


-- SELECT sell_calc_rev(cc_sellrate.*,10.0) FROM cc_sellrate WHERE id = 1;

-- SELECT *  FROM (
-- 	SELECT DISTINCT ON (cc_tariffplan.id) * FROM (
-- 	SELECT DISTINCT ON (cc_retailplan.id) cc_retailplan.id AS rpid, cc_retailplan.name, 
-- 		cc_sellrate.id, srid,cc_sell_prefix.dialprefix, cc_sellrate.destination,
-- 		cc_sellrate.rateinitial, sell_calc_rev(cc_sellrate.*,10.3) AS sell_timeout
-- 		FROM cc_sellrate, cc_sell_prefix , cc_retailplan
-- 		WHERE cc_sell_prefix.dialprefix = ANY(dial_exp_prefix('123564'))
-- 			AND cc_sellrate.id = cc_sell_prefix.srid
-- 			AND cc_retailplan.id = cc_sellrate.idrp
-- 		ORDER BY cc_retailplan.id, length(cc_sell_prefix.dialprefix) DESC
-- 	) AS allsellrates, cc_buyrate, cc_buy_prefix, cc_rtplan_buy, cc_tariffplan
-- 		WHERE cc_rtplan_buy.rtid = allsellrates.rpid AND 
-- 			cc_rtplan_buy.tpid = cc_tariffplan.id AND
-- 			cc_buyrate.idtp = cc_tariffplan.id AND
-- 			cc_buyrate.id = cc_buy_prefix.brid AND
-- 			cc_buy_prefix.dialprefix = ANY(dial_exp_prefix('123654'))
-- 		ORDER BY cc_tariffplan.id, length(cc_buy_prefix.dialprefix) DESC
-- 	) AS bothrates
-- 		ORDER BY sell_timeout DESC
-- 	;


-- SELECT * from sell_calc_rev_part( 0.00, 0.09, 30, 60, 10);

	-- Outer query: Find matching trunk and buy rates for selling rate found in inner query
EXPLAIN ANALYZE	 SELECT * FROM (
	SELECT DISTINCT ON (cc_tariffplan.id) allsellrates.*, '12345' AS dialstring, 109 AS tgid,
		  cc_buyrate.id AS brid, cc_buy_prefix.dialprefix AS prefix,
		  cc_trunk.id AS trunkid, cc_trunk.trunkcode, cc_trunk.trunkprefix, cc_trunk.providertech,
		  cc_trunk.providerip, cc_trunk.addparameter AS trunkparm, cc_trunk.provider, '1'::integer AS trunkfree /*-*/,
		  cc_buyrate.buyrate
		FROM (
		  -- Inner query: match the destination against a retail rate
		   SELECT DISTINCT ON (cc_retailplan.id) cc_retailplan.id AS rpid,
			cc_sellrate.id AS srid, cc_sellrate.destination,
			sell_calc_rev(cc_sellrate.*,10.0) AS tmout
			FROM cc_sellrate, cc_sell_prefix , cc_retailplan, cc_tariffgroup_plan
			WHERE cc_sell_prefix.dialprefix = ANY(dial_exp_prefix('93099753'))
				AND cc_sellrate.id = cc_sell_prefix.srid
				AND cc_retailplan.id = cc_sellrate.idrp
				AND cc_tariffgroup_plan.tgid = 109
				AND cc_tariffgroup_plan.rtid = cc_retailplan.id
				AND ( current_timestamp BETWEEN cc_retailplan.start_date AND cc_retailplan.stop_date)
			ORDER BY cc_retailplan.id, length(cc_sell_prefix.dialprefix) DESC
		)  AS allsellrates, 
			cc_buyrate, cc_buy_prefix, cc_rtplan_buy, cc_tariffplan, cc_trunk
		WHERE cc_rtplan_buy.rtid = allsellrates.rpid 
			AND cc_rtplan_buy.tpid = cc_tariffplan.id 
			AND cc_buyrate.idtp = cc_tariffplan.id
			AND cc_buyrate.id = cc_buy_prefix.brid
			AND cc_buy_prefix.dialprefix = ANY(dial_exp_prefix('93099753'))
-- 			AND ( now() BETWEEN cc_tariffplan.start_date AND cc_tariffplan.stop_date)
			AND cc_tariffplan.trunk = cc_trunk.id
		ORDER BY cc_tariffplan.id, length(cc_buy_prefix.dialprefix) DESC
		) AS outerq ORDER BY tmout DESC;

-- eof
