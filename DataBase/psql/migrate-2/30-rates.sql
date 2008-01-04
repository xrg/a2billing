-- Rates: buy, sell plans etc

-- Tariffgroup is migrated with its old id. Other tables alter their ids.

ALTER TABLE cc_tariffplan ADD COLUMN migr_oldid INTEGER;
ALTER TABLE cc_retailplan ADD COLUMN migr_oldid INTEGER;

ALTER TABLE cc_buyrate ADD COLUMN migr_oldid BIGINT;
ALTER TABLE cc_sellrate ADD COLUMN migr_oldid BIGINT;

INSERT INTO cc_tariffgroup (id,iduser,name,lcrtype,package_offer)
	SELECT id,iduser,tariffgroupname, lcrtype, id_cc_package_offer
		FROM a2b_old.cc_tariffgroup;

SELECT pg_catalog.setval('cc_tariffplan_id_seq', (SELECT last_value FROM a2b_old.cc_tariffplan_id_seq));

INSERT INTO cc_tariffplan( tariffname, creationdate, start_date, stop_date,
			description, trunk,
			secondusedreal, secondusedcarrier, secondusedratecard,
			dnidprefix, calleridprefix, migr_oldid )
		SELECT tariffname, creationdate, startingdate, expirationdate,
			description, id_trunk,
			secondusedreal, secondusedcarrier, secondusedratecard,
			dnidprefix, calleridprefix, id
    		FROM a2b_old.cc_tariffplan;


INSERT INTO cc_retailplan( name, creationdate, start_date, stop_date,
			description, dnidprefix, migr_oldid )
		SELECT tariffname, creationdate, startingdate, expirationdate,
			description, dnidprefix, id
    	FROM a2b_old.cc_tariffplan;

INSERT INTO cc_buyrate(idtp, destination, buyrate, buyrateinitblock, buyrateincrement,migr_oldid)
	SELECT cc_tariffplan.id,destination,buyrate,buyrateinitblock,buyrateincrement, a2b_old.cc_ratecard.id
		FROM a2b_old.cc_ratecard, cc_tariffplan
		WHERE a2b_old.cc_ratecard.parent_card IS NULL AND 
		    a2b_old.cc_ratecard.idtariffplan = cc_tariffplan.migr_oldid;

INSERT INTO cc_sellrate(idrp, destination, rateinitial, initblock, billingblock,
		connectcharge,disconnectcharge,
		stepchargea,chargea,timechargea,billingblocka,
		stepchargeb,chargeb,timechargeb,billingblockb,
		stepchargec,chargec,timechargec,billingblockc,
		/*freetimetocall_package_offer,*/
		migr_oldid)
	SELECT cc_retailplan.id,destination, rateinitial, initblock, billingblock,
		connectcharge,disconnectcharge,
		stepchargea,chargea,timechargea,billingblocka,
		stepchargeb,chargeb,timechargeb,billingblockb,
		stepchargec,chargec,timechargec,billingblockc,
		/*freetimetocall_package_offer,*/
		 a2b_old.cc_ratecard.id
		FROM a2b_old.cc_ratecard, cc_retailplan
		WHERE a2b_old.cc_ratecard.parent_card IS NULL AND 
		    a2b_old.cc_ratecard.idtariffplan = cc_retailplan.migr_oldid;

CREATE INDEX cc_sellrate_migr_idx ON cc_sellrate USING btree(migr_oldid);
CREATE INDEX cc_buyrate_migr_idx ON cc_buyrate USING btree(migr_oldid);
ANALYZE cc_sellrate;
ANALYZE cc_buyrate;

-- Assume all old prefixes are in e164 format!

\echo Migrate cc_ratecard.prefix into cc_buy_prefix (wait..)
INSERT INTO cc_buy_prefix (brid,dialprefix)
	SELECT DISTINCT cc_buyrate.id,CASE WHEN dialprefix = 'defaultprefix' THEN '+' ELSE  '+' || dialprefix END
		FROM cc_buyrate,a2b_old.cc_ratecard
		WHERE cc_buyrate.migr_oldid = a2b_old.cc_ratecard.id
			OR cc_buyrate.migr_oldid = a2b_old.cc_ratecard.parent_card;

\echo Migrate cc_ratecard.prefix into cc_sell_prefix (wait more..)
INSERT INTO cc_sell_prefix (srid,dialprefix)
	SELECT DISTINCT cc_sellrate.id,CASE WHEN dialprefix = 'defaultprefix' THEN '+' ELSE '+' || dialprefix END
		FROM cc_sellrate,a2b_old.cc_ratecard
		WHERE cc_sellrate.migr_oldid = a2b_old.cc_ratecard.id
			OR cc_sellrate.migr_oldid = a2b_old.cc_ratecard.parent_card;

\echo Tariffplan relations
INSERT INTO cc_tariffgroup_plan (tgid, rtid)
	SELECT idtariffgroup, cc_retailplan.id
		FROM cc_retailplan, a2b_old.cc_tariffgroup_plan
		WHERE idtariffplan = cc_retailplan.migr_oldid;

INSERT INTO cc_rtplan_buy(rtid,tpid)
	SELECT cc_retailplan.id, cc_tariffplan.id
		FROM cc_retailplan, cc_tariffplan
		WHERE cc_retailplan.migr_oldid= cc_tariffplan.migr_oldid;
