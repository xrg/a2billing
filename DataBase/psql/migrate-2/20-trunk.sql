-- Trunks

INSERT INTO cc_provider(id, provider_name, creationdate, description)
	SELECT id, provider_name, creationdate, description FROM a2b_old.cc_provider;

SELECT pg_catalog.setval('cc_provider_id_seq', (SELECT last_value FROM a2b_old.cc_provider_id_seq));


INSERT INTO cc_trunk(id, trunkcode, trunkprefix, providertech, providerip,
			secondusedreal, secondusedcarrier, secondusedratecard,
			creationdate, addparameter, provider)
	SELECT id_trunk, trunkcode, trunkprefix, providertech, providerip,
			secondusedreal, secondusedcarrier, secondusedratecard,
			creationdate, addparameter, CASE WHEN id_provider > 0 THEN id_provider ELSE NULL END
		FROM a2b_old.cc_trunk;
		
SELECT pg_catalog.setval('cc_trunk_id_seq', (SELECT last_value FROM a2b_old.cc_trunk_id_trunk_seq));

