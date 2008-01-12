-- Views for calls

DROP TRIGGER IF EXISTS cc_call_check_invoice ON cc_call;
CREATE TRIGGER cc_call_check_invoice BEFORE UPDATE OR DELETE ON cc_call
	FOR EACH ROW EXECUTE PROCEDURE cc_invoice_lock_f();

CREATE OR REPLACE VIEW cc_call_v AS
SELECT sessionid, uniqueid, cardid,nasipaddress, srvid, cmode,
    starttime,stoptime,
    sessiontime,calledstation,
    startdelay,stopdelay, attempt(la),srid(la),brid(la),tcause(la),hupcause(la),
    cause_ext(la),trunk(la),
    sessionbill,destination,tgid, qval, src, buycost, invoice_id
    FROM (SELECT sessionid, uniqueid, cardid, srvid,
    		MAX(cmode) AS cmode,
		MAX(nasipaddress) AS nasipaddress,
		MIN(starttime) AS starttime, MAX(stoptime) AS stoptime,
		SUM(sessiontime) AS sessiontime, MIN(calledstation) AS calledstation,
		SUM(startdelay) AS startdelay, SUM(stopdelay) AS stopdelay,
		last_attempt(ROW(attempt,srid, brid, tcause, hupcause, cause_ext, trunk)) AS la,
		SUM(sessionbill) as sessionbill, MAX(destination) AS destination, MAX(tgid) AS tgid,
		AVG(qval) AS qval, MAX(src) AS src, SUM(buycost) AS buycost,
		MAX(invoice_id) AS invoice_id
	FROM cc_call
	GROUP BY sessionid, uniqueid, cardid, srvid) AS foo ;


CREATE OR REPLACE VIEW cc_call2_v AS
SELECT sessionid, uniqueid, cardid,nasipaddress, srvid, cmode,
    starttime,stoptime,
    sessiontime,substring(calledstation from '#"%#"___' for '#') || '***' AS calledstation,
    startdelay,stopdelay, attempt(la),srid(la),brid(la),tcause(la),hupcause(la),
    cause_ext(la),trunk(la),
    sessionbill,destination,tgid, qval, src, buycost, invoice_id
    FROM (SELECT sessionid, uniqueid, cardid, srvid, MAX(cmode) AS cmode,
		MAX(nasipaddress) AS nasipaddress,
		MIN(starttime) AS starttime, MAX(stoptime) AS stoptime,
		SUM(sessiontime) AS sessiontime, MIN(calledstation) AS calledstation,
		SUM(startdelay) AS startdelay, SUM(stopdelay) AS stopdelay,
		last_attempt(ROW(attempt,srid, brid, tcause, hupcause, cause_ext, trunk)) AS la,
		SUM(sessionbill) as sessionbill, MAX(destination) AS destination, MAX(tgid) AS tgid,
		AVG(qval) AS qval, MAX(src) AS src, SUM(buycost) AS buycost,
		MAX(invoice_id) AS invoice_id
	FROM cc_call
	GROUP BY sessionid, uniqueid, cardid, srvid) AS foo ;

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


--eof
