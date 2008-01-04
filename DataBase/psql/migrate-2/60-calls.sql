--- Calls

\echo Migrating call data (may take a while).

INSERT INTO cc_call ( /*id,*/ cmode, sessionid, uniqueid, cardid,
		nasipaddress, starttime, stoptime, sessiontime, calledstation,
		startdelay, stopdelay, tcause,
		attempt, trunk, sessionbill, destination, src, buycost,
		srid, brid, tgid,
		id_did, id_card_package_offer, invoice_id)

	SELECT 'old', sessionid, uniqueid, cc_card.id,
		nasipaddress, starttime, stoptime, sessiontime, calledstation,
		startdelay, stopdelay, terminatecause,
		1, id_trunk, sessionbill, cc_call.destination, src, buycost,
		cc_sellrate.id, cc_buyrate.id, id_tariffgroup,
		id_did, id_card_package_offer, invoice_id
	FROM a2b_old.cc_call LEFT JOIN cc_buyrate ON cc_buyrate.migr_oldid = id_ratecard
		LEFT JOIN cc_sellrate ON cc_buyrate.migr_oldid = id_ratecard
		LEFT JOIN a2b_old.cc_card ON cc_card.username = cc_call.username;

\echo Calls copied to migrated table.

--eof
