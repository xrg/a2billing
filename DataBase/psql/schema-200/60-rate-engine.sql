-- Rate-engine related views, functions
-- Copyright (C) P. Christeas, 2007


/** We have a customer (cc_card), the current time and trunk status and the
    desired destination (dialstring).
    We must come up with a list of dial information (trunk+dialstring),
    selling prices, buying prices etc.

	_The Algorithm_
    [1. First look at the numplan and detect if the dialstring has international prefix]
    2. Locate the tariffgroup -> retail plan
    3. Locate the selling rate etc. from retail plan
    4. Locate the buying plan(s), buing price
    5. Locate trunks
    6. Filter out a few lines, based on trunks availability etc.
    7. Sort by cost or whatever.
*/

-- CREATE OR REPLACE FUNCTION RateEngine(id_card BIGINT, dialstring TEXT) 
-- 	RETURNS SETOF RECORD AS $$
-- 	
-- 	SELECT '1'
-- 	FROM cc_tariffgroup WHERE true;
-- $$ LANGUAGE SQL STRICT VOLATILE;


-- Match the data needed by cc_call, trunks, timeout..

CREATE TYPE reng_result  AS ( 
	     --- Per-call fields
	srid BIGINT,
	dialstring TEXT,
	destination TEXT, /* name of destination */
	tgid INTEGER,
	tmout INTEGER,
	     -- Per-trunk (buyrate) fields
	brid BIGINT,
	trunkid INTEGER, trunkcode TEXT, trunkprefix TEXT, providertech TEXT,
	    trunkfmt INTEGER,
	    providerip TEXT, trunkparm TEXT, provider INTEGER, trunkfree INTEGER,
	prefix TEXT
	);

--devel note: Please keep the indentation, it helps a lot!

DROP FUNCTION IF EXISTS RateEngine2(tgid INTEGER, dialstring TEXT, TIMESTAMP WITH TIME ZONE, NUMERIC(12,4));
CREATE OR REPLACE FUNCTION RateEngine2(s_tgid INTEGER, s_dialstring TEXT, s_curtime TIMESTAMP WITH TIME ZONE, money NUMERIC(12,4)) 
	RETURNS SETOF reng_result AS $$
    -- Final query (outmost): sort the results (buy rates+ sell ones), form result row
SELECT ROW( srid, dialstring, destination, tgid, tmout, brid, 
		trunkid, trunkcode, trunkprefix, providertech,trunkfmt, providerip, trunkparm, provider,
		trunkfree, prefix )::reng_result
  FROM (
  	-- Outer query: Find matching trunk and buy rates for selling rate found in inner query
	SELECT DISTINCT ON (cc_tariffplan.id) allsellrates.*, $2 AS dialstring, $1 AS tgid,
		  cc_buyrate.id AS brid, cc_buy_prefix.dialprefix AS prefix,
		  cc_trunk.id AS trunkid, cc_trunk.trunkcode, cc_trunk.trunkprefix, cc_trunk.providertech,
		  cc_trunk.trunkfmt,
		  cc_trunk.providerip, cc_trunk.addparameter AS trunkparm, cc_trunk.provider, '1'::integer AS trunkfree /*-*/,
		  cc_buyrate.buyrate, (cc_tariffplan.metric + allsellrates.metric) AS sum_metric
		FROM (
		  -- Inner query: match the destination against a retail rate
		   SELECT DISTINCT ON (cc_retailplan.id) cc_retailplan.id AS rpid,
			cc_sellrate.id AS srid, cc_sellrate.destination, cc_retailplan.metric,
			sell_calc_rev(cc_sellrate.*,$4) AS tmout
			FROM cc_sellrate, cc_sell_prefix , cc_retailplan, cc_tariffgroup_plan
			WHERE cc_sell_prefix.dialprefix = ANY(dial_exp_prefix($2))
				AND cc_sellrate.id = cc_sell_prefix.srid
				AND cc_retailplan.id = cc_sellrate.idrp
				AND cc_tariffgroup_plan.tgid = $1
				AND cc_tariffgroup_plan.rtid = cc_retailplan.id
				AND ( $3 BETWEEN cc_retailplan.start_date AND cc_retailplan.stop_date)
			ORDER BY cc_retailplan.id, length(cc_sell_prefix.dialprefix) DESC
		)  AS allsellrates, 
			cc_buyrate, cc_buy_prefix, cc_rtplan_buy, cc_tariffplan, cc_trunk
		WHERE cc_rtplan_buy.rtid = allsellrates.rpid 
			AND cc_rtplan_buy.tpid = cc_tariffplan.id 
			AND cc_buyrate.idtp = cc_tariffplan.id
			AND cc_buyrate.id = cc_buy_prefix.brid
			AND cc_buy_prefix.dialprefix = ANY(dial_exp_prefix($2))
			AND ( $3 BETWEEN cc_tariffplan.start_date AND cc_tariffplan.stop_date)
			AND cc_tariffplan.trunk = cc_trunk.id
		ORDER BY cc_tariffplan.id, length(cc_buy_prefix.dialprefix) DESC
	) AS bothrates
		ORDER BY sum_metric ASC, tmout DESC, buyrate ASC
	;

$$ LANGUAGE SQL STRICT VOLATILE;

--eof
