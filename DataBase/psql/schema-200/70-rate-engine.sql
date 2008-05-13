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


-- Carefully drop the functions we are about to redefine
DROP FUNCTION IF EXISTS RateEngine2(s_tgid INTEGER, s_dialstring TEXT, s_curtime TIMESTAMP WITH TIME ZONE, money NUMERIC(12,4));
DROP FUNCTION IF EXISTS RateEngine2(s_tgid INTEGER, s_dialstring TEXT, s_numplan INTEGER, s_curtime TIMESTAMP WITH TIME ZONE, money NUMERIC(12,4));
DROP FUNCTION IF EXISTS RateEngine3(s_tgid INTEGER, s_dialstring TEXT, s_numplan INTEGER, s_curtime TIMESTAMP WITH TIME ZONE, money NUMERIC(12,4));
DROP TYPE IF EXISTS reng_result;

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
	metric INTEGER,
	trunkid INTEGER, trunkcode TEXT, trunkprefix TEXT, providertech TEXT,
	    trunkfmt INTEGER, stripdigits INTEGER,
	    providerip TEXT, trunkparm TEXT, provider INTEGER, trunkfree INTEGER,
	    trunkfeat TEXT,
	    clidreplace TEXT,
	prefix TEXT
	);

--devel note: Please keep the indentation, it helps a lot!

/** Translate (format) dialstring according to Numplan patterns */
CREATE OR REPLACE FUNCTION NumplanMatch(s_dialstring TEXT,s_numplan INTEGER)
	RETURNS TEXT AS $$
	SELECT dialstr FROM (
		SELECT repl || substr($1,length(find)+1) AS dialstr, length(find) AS len
			FROM cc_numplan_pattern 
			WHERE substr($1,1,length(find)) = find
			AND nplan = $2
		UNION SELECT $1,0 )
	 AS foo
	ORDER BY len DESC LIMIT 1;
$$ LANGUAGE SQL STRICT STABLE;

-- DROP FUNCTION IF EXISTS RateEngine2(tgid INTEGER, dialstring TEXT, TIMESTAMP WITH TIME ZONE, NUMERIC(12,4));
CREATE OR REPLACE FUNCTION RateEngine2(s_tgid INTEGER, s_dialstring TEXT, s_numplan INTEGER, s_curtime TIMESTAMP WITH TIME ZONE, money NUMERIC(12,4)) 
	RETURNS SETOF reng_result AS $$
    -- Final query (outmost): sort the results (buy rates+ sell ones), form result row
SELECT ROW( srid, dialstring, destination, tgid, tmout, brid, sum_metric,
		trunkid, trunkcode, trunkprefix, providertech,trunkfmt,stripdigits, providerip, trunkparm, provider,
		trunkfree, trunkfeat, clidreplace, prefix )::reng_result
  FROM (
  	-- Outer query: Find matching trunk and buy rates for selling rate found in inner query
	SELECT DISTINCT ON (cc_tariffplan.id) allsellrates.*, $2 AS dialstring, $1 AS tgid,
		  cc_buyrate.id AS brid, cc_buy_prefix.dialprefix AS prefix,
		  cc_trunk.id AS trunkid, cc_trunk.trunkcode, cc_trunk.trunkprefix, cc_trunk.providertech,
		  cc_trunk.trunkfmt, cc_trunk.stripdigits, cc_re_numplan_pattern.repl AS clidreplace,
		  cc_trunk.providerip, cc_trunk.addparameter AS trunkparm, cc_trunk.provider, '1'::integer AS trunkfree /*-*/,
		  cc_trunk.feature AS trunkfeat,
		  cc_buyrate.buyrate, (cc_tariffplan.metric + allsellrates.metric + cc_trunk.metric) AS sum_metric
		FROM (
		  -- Inner query: match the destination against a retail rate
		   SELECT DISTINCT ON (cc_retailplan.id) cc_retailplan.id AS rpid,
			cc_sellrate.id AS srid, cc_sellrate.destination, cc_retailplan.metric,
			sell_calc_rev(cc_sellrate.*,$5) AS tmout
			FROM cc_sellrate, cc_sell_prefix , cc_retailplan, cc_tariffgroup_plan
			WHERE cc_sell_prefix.dialprefix = ANY(dial_exp_prefix1($2))
				AND cc_sellrate.id = cc_sell_prefix.srid
				AND cc_retailplan.id = cc_sellrate.idrp
				AND cc_tariffgroup_plan.tgid = $1
				AND cc_tariffgroup_plan.rtid = cc_retailplan.id
				AND ( $4 BETWEEN cc_retailplan.start_date AND cc_retailplan.stop_date)
			ORDER BY cc_retailplan.id, length(cc_sell_prefix.dialprefix) DESC
		)  AS allsellrates, 
			cc_buyrate, cc_buy_prefix, cc_rtplan_buy, cc_tariffplan, cc_trunk
				LEFT JOIN cc_re_numplan_pattern ON (cc_trunk.rnplan = cc_re_numplan_pattern.nplan
					AND ( cc_re_numplan_pattern.fplan IS NULL OR cc_re_numplan_pattern.fplan = $3)
					AND ( $2 LIKE cc_re_numplan_pattern.find || '%') -- need to optimize here?
						)
		WHERE cc_rtplan_buy.rtid = allsellrates.rpid 
			AND cc_rtplan_buy.tpid = cc_tariffplan.id 
			AND cc_buyrate.idtp = cc_tariffplan.id
			AND cc_buyrate.id = cc_buy_prefix.brid
			AND cc_buy_prefix.dialprefix = ANY(dial_exp_prefix1($2))
			AND ( $4 BETWEEN cc_tariffplan.start_date AND cc_tariffplan.stop_date)
			AND cc_tariffplan.trunk = cc_trunk.id
		ORDER BY cc_tariffplan.id, length(cc_buy_prefix.dialprefix) DESC, length(cc_re_numplan_pattern.find) DESC
	) AS bothrates
		ORDER BY sum_metric ASC, tmout DESC, buyrate ASC
	;

$$ LANGUAGE SQL STRICT VOLATILE SECURITY DEFINER;

-- Helper function, do numplan match in one go..
CREATE OR REPLACE FUNCTION RateEngine3(s_tgid INTEGER, s_dialstring TEXT, s_numplan INTEGER, s_curtime TIMESTAMP WITH TIME ZONE, money NUMERIC(12,4)) 
	RETURNS SETOF reng_result AS $$
	SELECT * FROM RateEngine2($1,NumplanMatch($2,$3),$3,$4,$5);
$$ LANGUAGE SQL STRICT VOLATILE;

CREATE OR REPLACE FUNCTION card_call_lock(s_cardid BIGINT) RETURNS card_call_lock_t AS $$
DECLARE
	ret RECORD;
	ret2 card_call_lock_t;
BEGIN
	SELECT (CASE WHEN typepaid = 0 THEN cc_card.credit
		  WHEN typepaid = 1 THEN (cc_card.credit +  cc_card.creditlimit)
		  ELSE -1.0 END ) AS ccredit,
		cc_card.currency, cc_card.language, cc_card.status, cc_card.inuse,
		cc_card_group.simultaccess
		INTO ret
		FROM cc_card, cc_card_group
		WHERE cc_card.id = s_cardid AND cc_card.grp = cc_card_group.id;

	IF NOT FOUND THEN
		RAISE EXCEPTION 'call_lock|no-find|%|Cannot find card %.',s_cardid, s_cardid;
	END IF;
	
	IF ret.status <>1 THEN
		RAISE EXCEPTION 'call_lock|wrong-status|%|Card has status %.',ret.status, ret.status;
	END IF;

	IF (ret.simultaccess <> 0) AND (ret.inuse >= ret.simultaccess) THEN
		RAISE EXCEPTION 'call_lock|in-use|%|Card is in use %/%.',ret.inuse, ret.inuse,ret.simultaccess;
	END IF;

		-- This query should better not fail..
	UPDATE cc_card SET inuse = inuse + 1 , lastuse = now()
		WHERE cc_card.id = s_cardid ;
	SELECT ret.ccredit AS base, conv_currency_to(ret.ccredit, ret.currency) AS local, 
		ret.currency, ret.language, ret.inuse INTO ret2;
	RETURN ret2;
END;
$$ LANGUAGE plpgsql STRICT VOLATILE SECURITY DEFINER;

CREATE OR REPLACE FUNCTION card_call_release(s_cardid BIGINT) RETURNS void AS $$
	UPDATE cc_card SET inuse = inuse - 1, nbused = nbused+1
		WHERE id = $1;
$$ LANGUAGE SQL STRICT VOLATILE SECURITY DEFINER;

CREATE OR REPLACE FUNCTION card_activate(s_cardid BIGINT) RETURNS void AS $$
	UPDATE cc_card SET status = 1, firstusedate = now()
		WHERE id = $1 AND status = 2;
$$ LANGUAGE SQL STRICT VOLATILE SECURITY DEFINER;

--- TODO: insert rule to limit trunk use on cc_call insert.

CREATE OR REPLACE FUNCTION call_bill() RETURNS trigger AS $$
DECLARE
	new_sbill NUMERIC(12,4);
	new_bbill NUMERIC(12,4);
BEGIN
	IF OLD.stoptime IS NOT NULL AND NEW.stoptime IS NULL THEN
		RAISE EXCEPTION 'Cannot re-open call!';
	END IF;
		-- Ignore updates on already closed calls
	IF OLD.stoptime IS NOT NULL THEN
		RETURN NEW;
	END IF;
	
		-- Also ignore updates where sessiontime doesn't get defined
	IF OLD.sessiontime IS NOT NULL OR NEW.sessiontime IS NULL THEN
		RETURN NEW;
	END IF;
	
	IF OLD.sessionbill IS NULL AND NEW.sessionbill IS NULL 
		AND ( NEW.tcause = 'ANSWER' OR NEW.tcause = 'ANSWERED'
			OR  NEW.tcause = 'SUCCESS') THEN
		-- Now, fetch the session bill!
		SELECT sell_calc_fwd(NEW.sessiontime * INTERVAL '1 sec',cc_sellrate.*) INTO new_sbill
			FROM cc_sellrate WHERE id = COALESCE(NEW.srid, OLD.srid);
		IF FOUND THEN
			NEW.sessionbill := new_sbill;
		ELSE
			RAISE WARNING 'Cannot find sellrate to bill call!';
		END IF;
	END IF;
	IF OLD.sessionbill IS NULL AND NEW.sessionbill IS NOT NULL THEN
		-- Update card
		UPDATE cc_card SET credit = credit - NEW.sessionbill 
			WHERE id = COALESCE(NEW.cardid, OLD.cardid);
		IF NOT FOUND THEN
			RAISE WARNING 'Cannot locate card to update credit!';
		END IF;
	END IF;
	
	IF OLD.buycost IS NULL AND NEW.buycost IS NULL THEN
		-- Fetch the buy cost
		SELECT buy_calc_fwd(NEW.sessiontime * INTERVAL '1 sec', cc_buyrate.*) INTO new_bbill
			FROM cc_buyrate WHERE id = COALESCE(NEW.brid,OLD.brid);
		IF FOUND THEN
			NEW.buycost := new_bbill;
		ELSE
			RAISE WARNING 'Cannot find buyrate to bill call!';
		END IF;
	END IF;
	
	IF OLD.buycost IS NULL AND NEW.buycost IS NOT NULL THEN
		-- Update retailplan
		UPDATE cc_tariffplan SET credit = credit - ( CASE WHEN neg_currency IS NULL THEN NEW.buycost
				ELSE conv_currency_to(NEW.buycost, neg_currency) END),
			secondusedreal = secondusedreal + NEW.sessiontime
			FROM cc_buyrate
			WHERE cc_buyrate.id = COALESCE(NEW.brid,OLD.brid)
			    AND cc_buyrate.idtp = cc_tariffplan.id;
		IF NOT FOUND THEN
			RAISE WARNING 'Cannot update tariffplan with new credit, seconds!';
		END IF;
	END IF;
	
	IF COALESCE(NEW.trunk,OLD.trunk) IS NOT NULL THEN
		UPDATE cc_trunk SET secondusedreal = secondusedreal + NEW.sessiontime,
			inuse = inuse - 1
			WHERE id = COALESCE(NEW.trunk,OLD.trunk);
		IF NOT FOUND THEN
			RAISE WARNING 'Cannot update trunk after call!';
		END IF;
	END IF;

	RETURN NEW;
END;
$$ LANGUAGE plpgsql STRICT SECURITY DEFINER;

CREATE OR REPLACE FUNCTION call_insert() RETURNS trigger AS $$
DECLARE
	t_inuse INTEGER;
	t_maxuse INTEGER;
	t_status INTEGER;
BEGIN
	IF NEW.srvid IS NULL THEN
		-- Much scorchery in here: since this function is SECURITY DEFINER
		-- the effective uid is changed to the owner. Still, session_user
		-- should remain set to the calling username.
		SELECT id INTO NEW.srvid from cc_a2b_server 
			WHERE db_username = session_user LIMIT 1;
		IF NOT FOUND THEN
			RAISE WARNING 'No server found for uname % at %',session_user, COALESCE(inet_client_addr()::TEXT,'unix');
		END IF;
		-- INSERT INTO cc_a2b_server(grp,ip,host,db_username)
		-- 	VALUES(1,inet_client_addr(),inet_client_addr(),session_user);
	END IF;
	
	IF NEW.trunk IS NULL THEN
		RETURN NEW;
	END IF;
	
	UPDATE cc_trunk SET inuse = inuse + 1
		WHERE id = NEW.trunk 
		RETURNING inuse, status, maxuse 
		     INTO t_inuse, t_status, t_maxuse;
	IF NOT FOUND THEN
		RAISE WARNING 'In call_insert() could not locate trunk.';
	END IF;
	
	IF t_status != 1 THEN
		RAISE EXCEPTION 'Cannot use trunk: status %',t_status;
	END IF;
	
	-- Don't worry: we have increased 'inuse' but will rollback if we raise exception.
	IF t_maxuse <> -1 AND t_inuse > t_maxuse THEN
		RAISE EXCEPTION 'Trunk % reached max use.', NEW.trunk;
	END IF;
	
	RETURN NEW;
END; $$ LANGUAGE plpgsql STRICT SECURITY DEFINER ;


DROP TRIGGER IF EXISTS call_bill_trigger ON cc_call;
CREATE TRIGGER call_bill_trigger BEFORE UPDATE ON cc_call
	FOR EACH ROW EXECUTE PROCEDURE call_bill();

DROP TRIGGER IF EXISTS call_insert_trigger ON cc_call;
CREATE TRIGGER call_insert_trigger BEFORE INSERT ON cc_call
	FOR EACH ROW EXECUTE PROCEDURE call_insert();

--eof
