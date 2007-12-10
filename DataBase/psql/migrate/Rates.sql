--- Migrate Rates..
 
 
-- \set ON_ERROR_STOP

BEGIN;
DELETE FROM cc_tariffgroup_plan;
DELETE FROM cc_rtplan_buy;
DELETE FROM cc_buy_prefix;
DELETE FROM cc_sell_prefix;
DELETE FROM cc_buyrate;
DELETE FROM cc_sellrate;
DELETE FROM cc_tariffplan;
DELETE FROM cc_tariffgroup;

ALTER TABLE cc_tariffgroup ADD COLUMN migr_oldid INTEGER;

ALTER TABLE cc_tariffplan ADD COLUMN migr_oldid INTEGER;
ALTER TABLE cc_retailplan ADD COLUMN migr_oldid INTEGER;

ALTER TABLE cc_buyrate ADD COLUMN migr_oldid BIGINT;
ALTER TABLE cc_sellrate ADD COLUMN migr_oldid BIGINT;

-- ALTER TABLE cc_tariffgroup ADD COLUMN migr_oldid INTEGER;

INSERT INTO cc_tariffgroup (name,lcrtype,package_offer,migr_oldid)
	SELECT tariffgroupname, lcrtype, id_cc_package_offer, id
		FROM a2b_old.cc_tariffgroup;

INSERT INTO cc_tariffplan( tariffname, creationdate, start_date, stop_date,
    description, /*id_trunk,*/
    secondusedreal, secondusedcarrier, secondusedratecard,
--     idowner,
    dnidprefix,
    calleridprefix,
    neg_currency, migr_oldid )
    SELECT tariffname, creationdate, startingdate, expirationdate,
    	description, /*id_trunk,*/
    	secondusedreal, secondusedcarrier, secondusedratecard,
--     idowner,
    	dnidprefix,
    	calleridprefix,
    	1, 
    	id
    	FROM a2b_old.cc_tariffplan;

INSERT INTO cc_retailplan( name, creationdate, start_date, stop_date,
    description, /*id_trunk,*/
--     idowner,
    dnidprefix,
    migr_oldid )
    SELECT tariffname, creationdate, startingdate, expirationdate,
    	description, /*id_trunk,*/
--     idowner,
    	dnidprefix,
    	id
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

\echo Migrate cc_ratecard.prefix into cc_buy_prefix (wait..)
INSERT INTO cc_buy_prefix (brid,dialprefix)
	SELECT DISTINCT cc_buyrate.id,CASE WHEN dialprefix = 'defaultprefix' THEN '' ELSE dialprefix END
		FROM cc_buyrate,a2b_old.cc_ratecard
		WHERE cc_buyrate.migr_oldid = a2b_old.cc_ratecard.id
			OR cc_buyrate.migr_oldid = a2b_old.cc_ratecard.parent_card;

\echo Migrate cc_ratecard.prefix into cc_sell_prefix (wait..)
INSERT INTO cc_sell_prefix (srid,dialprefix)
	SELECT DISTINCT cc_sellrate.id,CASE WHEN dialprefix = 'defaultprefix' THEN '' ELSE dialprefix END
		FROM cc_sellrate,a2b_old.cc_ratecard
		WHERE cc_sellrate.migr_oldid = a2b_old.cc_ratecard.id
			OR cc_sellrate.migr_oldid = a2b_old.cc_ratecard.parent_card;


INSERT INTO cc_tariffgroup_plan (tgid, rtid)
	SELECT cc_tariffgroup.id, cc_retailplan.id
		FROM cc_tariffgroup, cc_retailplan, a2b_old.cc_tariffgroup_plan
		WHERE idtariffgroup = cc_tariffgroup.migr_oldid AND idtariffplan = cc_retailplan.migr_oldid;

INSERT INTO cc_rtplan_buy(rtid,tpid)
	SELECT cc_retailplan.id, cc_tariffplan.id
		FROM cc_retailplan, cc_tariffplan
		WHERE cc_retailplan.migr_oldid= cc_tariffplan.migr_oldid;

ALTER TABLE cc_tariffgroup DROP COLUMN migr_oldid;
ALTER TABLE cc_tariffplan DROP COLUMN migr_oldid;
ALTER TABLE cc_retailplan DROP COLUMN migr_oldid;

ALTER TABLE cc_buyrate DROP COLUMN migr_oldid;
ALTER TABLE cc_sellrate DROP COLUMN migr_oldid;

\echo Migration finished!
END;

--eof
