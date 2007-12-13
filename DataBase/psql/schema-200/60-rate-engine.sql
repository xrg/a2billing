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
	    providerip TEXT, trunkparm TEXT, provider INTEGER, trunkfree INTEGER,
	prefix TEXT
	);

DROP FUNCTION IF EXISTS RateEngine2(tgid BIGINT, dialstring TEXT);
CREATE OR REPLACE FUNCTION RateEngine2(tgid BIGINT, dialstring TEXT) 
	RETURNS SETOF reng_result AS $$
	
	SELECT srid,dialprefix FROM cc_sell_prefix WHERE dialprefix = $2;
$$ LANGUAGE SQL STRICT VOLATILE;

--eof
