-- Test the rate engine


-- \i schema-200/60-rate-engine.sql


-- SELECT * FROM RateEngine2('12','45');


SELECT sell_calc_rev(cc_sellrate.*,10.0) FROM cc_sellrate WHERE id = 1;

SELECT *  FROM (
	SELECT DISTINCT ON (cc_retailplan.id) cc_retailplan.id AS rpid, cc_retailplan.name, 
		cc_sellrate.id, srid,cc_sell_prefix.dialprefix, cc_sellrate.destination,
		cc_sellrate.rateinitial, sell_calc_rev(cc_sellrate.*,10.3) AS sell_timeout
		FROM cc_sellrate, cc_sell_prefix , cc_retailplan
		WHERE cc_sell_prefix.dialprefix = ANY(dial_exp_prefix('123564'))
			AND cc_sellrate.id = cc_sell_prefix.srid
			AND cc_retailplan.id = cc_sellrate.idrp
		ORDER BY cc_retailplan.id, length(cc_sell_prefix.dialprefix) DESC
	) AS allsellrates
		ORDER BY sell_timeout DESC
	;


-- SELECT * from sell_calc_rev_part( 0.00, 0.09, 30, 60, 10);
-- eof
