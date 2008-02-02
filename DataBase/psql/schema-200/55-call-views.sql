-- Views for calls

DROP TRIGGER IF EXISTS cc_call_check_invoice ON cc_call;
CREATE TRIGGER cc_call_check_invoice BEFORE UPDATE OR DELETE ON cc_call
	FOR EACH ROW EXECUTE PROCEDURE cc_invoice_lock_f();

CREATE OR REPLACE FUNCTION rebuild_call_v () RETURNS VOID AS $$
BEGIN
	DELETE FROM cc_call_v;
	INSERT INTO cc_call_v (sessionid, uniqueid, cardid,nasipaddress, srvid, cmode,
		starttime,stoptime,
		sessiontime,calledstation,
		startdelay,stopdelay, attempt,srid,brid,tcause,hupcause,
		cause_ext,trunk,
    		sessionbill,destination,tgid, qval, src, buycost, invoice_id)
	    SELECT sessionid, uniqueid, cardid,nasipaddress, srvid, cmode,
			starttime,stoptime,
			sessiontime,calledstation,
			startdelay,stopdelay, attempt(la),srid(la),brid(la),tcause(la),hupcause(la),
			cause_ext(la),trunk(la),
			sessionbill,destination,tgid, qval, src, buycost, invoice_id
		FROM (SELECT sessionid, uniqueid, cardid, srvid, cmode,
				MAX(nasipaddress) AS nasipaddress,
				MIN(starttime) AS starttime, MAX(stoptime) AS stoptime,
				SUM(sessiontime) AS sessiontime, MIN(calledstation) AS calledstation,
				SUM(startdelay) AS startdelay, SUM(stopdelay) AS stopdelay,
				last_attempt(ROW(attempt,srid, brid, tcause, hupcause, cause_ext, trunk)) AS la,
				SUM(sessionbill) as sessionbill, MAX(destination) AS destination, MAX(tgid) AS tgid,
				AVG(qval) AS qval, MAX(src) AS src, SUM(buycost) AS buycost,
				MAX(invoice_id) AS invoice_id
		FROM cc_call
		GROUP BY sessionid, uniqueid, cardid, srvid, cmode) AS foo ;
END; $$ LANGUAGE plpgsql VOLATILE;

\echo Rebuilding materialized view cc_call_v
SELECT rebuild_call_v();

CREATE OR REPLACE VIEW cc_call2_v AS
SELECT sessionid, uniqueid, cardid,nasipaddress, srvid, cmode,
    starttime,stoptime,
    sessiontime,substring(calledstation from '#"%#"___' for '#') || '***' AS calledstation,
    startdelay,stopdelay, attempt,srid,brid,tcause,hupcause,
    cause_ext,trunk,
    sessionbill,destination,tgid, qval, src, buycost, invoice_id
    FROM cc_call_v ;

-- FAT NOTE: this view includes failed attempts!

CREATE OR REPLACE VIEW cc_agent_calls3_v AS
	SELECT cc_card_group.agentid, starttime, stoptime-starttime AS duration, tcause,
		sessionbill, invoice_id,
		substring(calledstation from '#"%#"___' for '#') || '***' AS calledstation,
		CASE WHEN cc_agent.id IS NOT NULL THEN
			(sessionbill * (1 -cc_agent.commission))
			ELSE NULL END AS agentbill
		FROM cc_call, cc_card, cc_card_group 
			LEFT OUTER JOIN  cc_agent ON cc_agent.id = cc_card_group.agentid
	WHERE cc_card.id = cc_call.cardid AND cc_card_group.id = cc_card.grp;


CREATE OR REPLACE FUNCTION cc_call_v_i () RETURNS trigger AS $$
BEGIN
	UPDATE cc_call_v SET nasipaddress = NEW.nasipaddress,
		starttime = LEAST(starttime,NEW.starttime),stoptime = (CASE WHEN NEW.stoptime IS NULL THEN NULL ELSE GREATEST(stoptime,NEW.stoptime) END),
		sessiontime = sessiontime + NEW.sessiontime,
		startdelay = startdelay + NEW.startdelay, stopdelay = stopdelay + NEW.stopdelay,
		attempt = NEW.attempt, srid = NEW.srid, brid = NEW.srid, tcause = NEW.tcause, hupcause = NEW.hupcause,
		cause_ext = NEW.cause_ext, trunk = NEW.trunk,
    		sessionbill = sessionbill + NEW.sessionbill,
    		buycost = buycost + NEW.buycost, invoice_id = COALESCE(invoice_id, NEW.invoice_id)
    	WHERE  sessionid = NEW.sessionid AND uniqueid = NEW.uniqueid AND cardid = NEW.cardid AND
    		cmode = NEW.cmode AND
    		srvid IS NOT DISTINCT FROM NEW.srvid
    		AND attempt < NEW.attempt;

	IF NOT FOUND THEN
    		INSERT INTO cc_call_v(sessionid, uniqueid, cardid,nasipaddress, srvid, cmode,
			starttime,stoptime,
			sessiontime,calledstation,
			startdelay,stopdelay, attempt,srid,brid,tcause,hupcause,
			cause_ext,trunk,
			sessionbill,destination,tgid, qval, src, buycost, invoice_id)
		  VALUES (NEW.sessionid,NEW. uniqueid,NEW. cardid,NEW.nasipaddress,NEW. srvid,NEW. cmode,
		  	NEW.starttime,NEW.stoptime,
		  	NEW.sessiontime,NEW.calledstation,
			NEW.startdelay,NEW.stopdelay,NEW. attempt,NEW.srid,NEW.brid,NEW.tcause,NEW.hupcause,
			NEW.cause_ext,NEW.trunk,
			NEW.sessionbill,NEW.destination,NEW.tgid,NEW. qval,NEW. src,NEW. buycost,NEW.invoice_id);
	END IF;
	
	RETURN NEW;
END;
$$ LANGUAGE plpgsql SECURITY DEFINER;

CREATE OR REPLACE FUNCTION d_diff(INTEGER,INTEGER,INTEGER) RETURNS INTEGER AS $$
	SELECT CASE WHEN COALESCE($1,$2,$3) IS NULL THEN NULL ELSE
		COALESCE($1,0) + COALESCE($2,0) - COALESCE($3,0) END;
$$ LANGUAGE SQL IMMUTABLE;
		
CREATE OR REPLACE FUNCTION d_diff(NUMERIC,NUMERIC,NUMERIC) RETURNS NUMERIC AS $$
	SELECT CASE WHEN COALESCE($1,$2,$3) IS NULL THEN NULL ELSE
		COALESCE($1,0) + COALESCE($2,0) - COALESCE($3,0) END;
$$ LANGUAGE SQL IMMUTABLE;

CREATE OR REPLACE FUNCTION cc_call_v_u () RETURNS trigger AS $$
BEGIN
	-- First, assume it's the last call, 
	UPDATE cc_call_v SET nasipaddress = NEW.nasipaddress,
		starttime = LEAST(starttime,NEW.starttime), stoptime = (CASE WHEN NEW.stoptime IS NULL THEN NULL ELSE GREATEST(stoptime,NEW.stoptime) END),
		sessiontime = d_diff(sessiontime,NEW.sessiontime,OLD.sessiontime),
		startdelay =  d_diff(startdelay,NEW.startdelay,OLD.startdelay),
		stopdelay = d_diff(stopdelay,NEW.stopdelay,OLD.stopdelay),
		srid = NEW.srid, brid = NEW.srid, tcause = NEW.tcause, hupcause = NEW.hupcause,
		cause_ext = NEW.cause_ext, trunk = NEW.trunk,
    		sessionbill = d_diff(sessionbill,NEW.sessionbill,OLD.sessionbill),destination = NEW.destination,
    		buycost = d_diff(buycost,NEW.buycost,OLD.buycost), invoice_id = NEW.invoice_id
    	WHERE  sessionid = NEW.sessionid AND uniqueid = NEW.uniqueid AND cardid = NEW.cardid AND
    		cmode = NEW.cmode AND
    		srvid IS NOT DISTINCT FROM NEW.srvid
    		AND attempt = NEW.attempt;
	
	-- if it didn't match the attempt, go the long way..
	IF NOT FOUND THEN
	DELETE FROM cc_call_v WHERE sessionid = NEW.sessionid AND uniqueid = NEW.uniqueid AND cardid = NEW.cardid AND
    		cmode = NEW.cmode AND
    		srvid IS NOT DISTINCT FROM NEW.srvid;
    	
    	INSERT INTO cc_call_v (sessionid, uniqueid, cardid,nasipaddress, srvid, cmode,
		starttime,stoptime,
		sessiontime,calledstation,
		startdelay,stopdelay, attempt,srid,brid,tcause,hupcause,
		cause_ext,trunk,
    		sessionbill,destination,tgid, qval, src, buycost, invoice_id)
	    SELECT sessionid, uniqueid, cardid,nasipaddress, srvid, cmode,
			starttime,stoptime,
			sessiontime,calledstation,
			startdelay,stopdelay, attempt(la),srid(la),brid(la),tcause(la),hupcause(la),
			cause_ext(la),trunk(la),
			sessionbill,destination,tgid, qval, src, buycost, invoice_id
		FROM (SELECT sessionid, uniqueid, cardid, srvid, cmode,
				MAX(nasipaddress) AS nasipaddress,
				MIN(starttime) AS starttime, MAX(stoptime) AS stoptime,
				SUM(sessiontime) AS sessiontime, MIN(calledstation) AS calledstation,
				SUM(startdelay) AS startdelay, SUM(stopdelay) AS stopdelay,
				last_attempt(ROW(attempt,srid, brid, tcause, hupcause, cause_ext, trunk)) AS la,
				SUM(sessionbill) as sessionbill, MAX(destination) AS destination, MAX(tgid) AS tgid,
				AVG(qval) AS qval, MAX(src) AS src, SUM(buycost) AS buycost,
				MAX(invoice_id) AS invoice_id
		FROM cc_call
		WHERE sessionid = NEW.sessionid AND uniqueid = NEW.uniqueid AND cardid = NEW.cardid AND
			cmode = NEW.cmode AND
			srvid IS NOT DISTINCT FROM NEW.srvid
		GROUP BY sessionid, uniqueid, cardid, srvid, cmode) AS foo ;
	END IF;
	RETURN NEW;
END;
$$ LANGUAGE plpgsql SECURITY DEFINER;

DROP TRIGGER IF EXISTS call_v_ut ON cc_call;
CREATE TRIGGER call_v_ut AFTER UPDATE ON cc_call
	FOR EACH ROW EXECUTE PROCEDURE cc_call_v_u();

DROP TRIGGER IF EXISTS call_v_it ON cc_call;
CREATE TRIGGER call_v_it AFTER INSERT ON cc_call
	FOR EACH ROW EXECUTE PROCEDURE cc_call_v_i();

--eof
