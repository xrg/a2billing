-- Caller IDS

INSERT INTO cc_callerid (id, cid, cardid, activated)
	SELECT id, cid, id_cc_card,activated
	FROM a2b_old.cc_callerid 
	WHERE id_cc_card NOT IN (SELECT card_id FROM a2b_old.cc_agent_cards);

SELECT pg_catalog.setval('cc_callerid_id_seq', (SELECT last_value FROM a2b_old.cc_callerid_id_seq));

--eof
