--- Functions that support the time period engine

CREATE OR REPLACE FUNCTION time_period_active( tpname TEXT, s_now TIMESTAMP WITH TIME ZONE) 
	RETURNS BOOLEAN AS $$
DECLARE
	ret BOOLEAN;
	p_idtp INTEGER;
	p_tpen BOOLEAN;
	p_tpint time_period_interval;
	p_iint INTERVAL;
	p_dtrunc TIMESTAMP WITH TIME ZONE;
	p_dnext TIMESTAMP WITH TIME ZONE;
BEGIN
	IF tpname IS NULL THEN
		RETURN true;
	END IF;
	
	IF s_now IS NULL THEN
		RETURN false;
	END IF;
	
	SELECT id, enabled INTO p_idtp, p_tpen FROM time_periods
		WHERE name = tpname;

	IF NOT FOUND THEN
		RAISE WARNING 'Time period "%" does not exist.',tpname;
		RETURN false;
	END IF;
	
	SELECT status INTO ret 
		FROM time_period_cache
		WHERE idtp = p_idtp AND tend > s_now AND tstart <=s_now;
	
	IF FOUND THEN
		-- RAISE NOTICE 'located in cache';
		RETURN ret;
	END IF;
	
	IF NOT p_tpen THEN
		RAISE NOTICE 'Could not find cached value, but period is disabled.';
		RETURN false;
	END IF;
	
	-- RAISE NOTICE 'Could not find cached value, calculating.';

	p_dnext := NULL;

	FOR p_tpint IN SELECT * FROM time_period_interval 
			WHERE idtp = p_idtp ORDER BY id 
		LOOP
		 -- that will be our reference tstamp
		p_dtrunc := date_trunc( CASE WHEN p_tpint.percity = 'yearly' THEN 'year'
				WHEN p_tpint.percity = 'monthly' THEN 'month'
				WHEN p_tpint.percity = 'weekly' THEN 'week'
				WHEN p_tpint.percity = 'daily' THEN 'day'
				WHEN p_tpint.percity = 'hourly' THEN 'hour'
				ELSE 'day'
				END , s_now);

		p_iint := s_now - p_dtrunc;
		-- RAISE NOTICE 'Truncated time to % : % ', p_tpint.percity, p_iint;
		
		IF p_iint >= p_tpint.iend THEN
			CONTINUE;
		ELSIF p_iint < p_tpint.istart THEN
			-- if the interval is after now, keep its start in mind.
			p_dnext := LEAST(p_dnext, p_dtrunc + p_tpint.istart);
			CONTINUE;
		END IF;
		
		-- Here, the interval is matched!
		-- RAISE NOTICE 'Matched interval, period is from % to %.',
		--	p_dtrunc + p_tpint.istart, p_dtrunc + p_tpint.iend;
		
		INSERT INTO time_period_cache(idtp, tstart, tend, status)
			VALUES (p_idtp, p_dtrunc + p_tpint.istart, p_dtrunc + p_tpint.iend, true);
		RETURN true;
		
	END LOOP;
	
	IF p_dnext IS NOT NULL THEN
		RAISE NOTICE 'Caching negative until %', p_dnext;
		INSERT INTO time_period_cache(idtp, tstart, tend, status)
			VALUES (p_idtp, s_now, p_dnext, false);
	END IF;
	RETURN false;
END;
$$ LANGUAGE plpgsql VOLATILE SECURITY DEFINER;

CREATE OR REPLACE FUNCTION time_period_explain( tpname TEXT, s_now TIMESTAMP WITH TIME ZONE) 
	RETURNS SETOF time_period_cache AS $$
DECLARE
	ret BOOLEAN;
	p_idtp INTEGER;
	p_tpen BOOLEAN;
	p_tpint time_period_interval;
	p_tpret time_period_cache;
	p_iint INTERVAL;
	p_dtrunc TIMESTAMP WITH TIME ZONE;
BEGIN

	SELECT id, enabled INTO p_idtp, p_tpen FROM time_periods
		WHERE name = tpname;

	IF NOT FOUND THEN
		RAISE WARNING 'Time period "%" does not exist.',tpname;
		RETURN ;
	END IF;

	p_tpret.idtp := p_idtp;
	p_tpret.status := true;
	p_tpret.id := 0;
	
	FOR p_tpint IN SELECT * FROM time_period_interval 
			WHERE idtp = p_idtp ORDER BY id 
		LOOP
		 -- that will be our reference tstamp
		p_dtrunc := date_trunc( CASE WHEN p_tpint.percity = 'yearly' THEN 'year'
				WHEN p_tpint.percity = 'monthly' THEN 'month'
				WHEN p_tpint.percity = 'weekly' THEN 'week'
				WHEN p_tpint.percity = 'daily' THEN 'day'
				WHEN p_tpint.percity = 'hourly' THEN 'hour'
				ELSE 'day'
				END , s_now);
		
		
		p_tpret.tstart := p_dtrunc + p_tpint.istart;
		p_tpret.tend := p_dtrunc + p_tpint.iend;
		RETURN NEXT p_tpret;
		
	END LOOP;
END;
$$ LANGUAGE plpgsql STRICT STABLE;

-- eof
