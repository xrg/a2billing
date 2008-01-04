-- Agent payments, refills

INSERT INTO cc_agentrefill ( id, date, credit, carried, pay_type, card_id, agentid, boothid )
	SELECT id, date, credit, carried, pay_type, card_id, agentid, boothid
	FROM a2b_old.cc_agentrefill;

SELECT pg_catalog.setval('cc_agentrefill_id_seq', (SELECT last_value FROM a2b_old.cc_agentrefill_id_seq));


INSERT INTO cc_shopsessions ( id, booth, card, starttime, endtime, state )
	SELECT id, booth, card, starttime, endtime, state
	FROM a2b_old.cc_shopsessions;

SELECT pg_catalog.setval('cc_shopsessions_id_seq', (SELECT last_value FROM a2b_old.cc_shopsessions_id_seq));

INSERT INTO cc_agentpay ( id, date, credit, pay_type, agentid, invoice_id, descr )
	SELECT id, date, credit, pay_type, agentid, invoice_id, descr
	FROM a2b_old.cc_agentpay;

SELECT pg_catalog.setval('cc_agentpay_id_seq', (SELECT last_value FROM a2b_old.cc_agentpay_id_seq));

--eof
