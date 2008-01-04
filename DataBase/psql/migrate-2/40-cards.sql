-- Agents, cards, card groups


INSERT INTO cc_agent ( id, name, login, passwd, groupid, location, datecreation,
			"language", tariffgroup, options, credit, active, currency, climit,
			email, locale, commission, vat, banner)
	SELECT id, name, login, passwd, groupid, location, datecreation, 
			"language", tariffgroup, options, credit, active, currency, climit,
		 	email, locale, commission, vat, banner
	FROM a2b_old.cc_agent;

SELECT pg_catalog.setval('cc_agent_id_seq', (SELECT last_value FROM a2b_old.cc_agent_id_seq));


-- Now create the various card groups.

-- One for all default cards of each callshop
INSERT INTO cc_card_group( name, agentid, uname_pattern, simultaccess, typepaid,
			tariffgroup, agent_role)
		SELECT   'Default cards for ' || agent.login || ' (' || tariff || ')', agent.id,
			'Booth%1.%2',1,0,
			tariff, 1
		FROM a2b_old.cc_card AS card, a2b_old.cc_agent_cards, a2b_old.cc_agent AS agent
		WHERE card.id = cc_agent_cards.card_id AND agent.id = cc_agent_cards.agentid
		  AND cc_agent_cards.def = true
		GROUP BY agent.id, agent.login, tariff;

-- One for all regulars of each callshop
INSERT INTO cc_card_group( name, agentid, uname_pattern, simultaccess, typepaid,
			tariffgroup, agent_role)
		SELECT   'Regulars of ' || agent.login || ' (' || tariff || ')', agent.id, 'Phone%1.%2',1,0,
			tariff, 2
		FROM a2b_old.cc_card AS card, a2b_old.cc_agent_cards, a2b_old.cc_agent AS agent
		WHERE card.id = cc_agent_cards.card_id AND agent.id = cc_agent_cards.agentid
		  AND cc_agent_cards.def = false
		GROUP BY agent.id, agent.login, tariff;

-- Create groups for all other cards

INSERT INTO cc_card_group (name, 
		simultaccess, typepaid, tariffgroup, invoiceday, 
		expiretype, expiredays, autorefill)
	SELECT   'Group '  || MIN(card.id),
		simultaccess, typepaid, tariff, invoiceday,
		enableexpire, expiredays, autorefill
	FROM a2b_old.cc_card AS card
	WHERE card.id NOT IN (SELECT card_id FROM a2b_old.cc_agent_cards)
	GROUP BY simultaccess,typepaid,tariff, invoiceday, 
		enableexpire, expiredays, autorefill ;

\echo 'Created card groups, now migrating cards'

INSERT INTO cc_card( id, grp, creationdate, firstusedate, expirationdate,
		username, useralias, userpass, credit, 
		status,
		lastname, firstname, address, city, state, country, zipcode,
		phone, email, fax,
		inuse, currency, lastuse, nbused, creditlimit, "language",
		redial, nbservice, id_campaign, num_trials_done, callback,
		servicelastrun, loginkey)
	SELECT  card.id, cgroup.id, creationdate, firstusedate, expirationdate,
		username, useralias, userpass, card.credit, 
		CASE WHEN card.activated THEN 1 ELSE 8 END,
		lastname, firstname, address, city, state, country, zipcode,
		phone, card.email, fax,
		inuse, card.currency, lastuse, nbused, creditlimit, card."language",
		redial, nbservice, id_campaign, num_trials_done, callback,
		servicelastrun, loginkey
		FROM a2b_old.cc_card AS card, a2b_old.cc_agent_cards AS cac,
			a2b_old.cc_agent AS agent, cc_card_group AS cgroup
		WHERE card.id = cac.card_id AND agent.id = cac.agentid
		  AND cgroup.agentid = agent.id
		  AND ((cgroup.agent_role = 1 AND cac.def = true) OR (cgroup.agent_role = 2 AND cac.def = false))
		  AND cgroup.tariffgroup IS NOT DISTINCT FROM card.tariff;

\echo Migrating non-agent cards..

INSERT INTO cc_card( id, grp, creationdate, firstusedate, expirationdate,
		username, useralias, userpass, credit, 
		status,
		lastname, firstname, address, city, state, country, zipcode,
		phone, email, fax,
		currency, lastuse, nbused, creditlimit, "language",
		redial, nbservice, id_campaign, num_trials_done, callback,
		servicelastrun, loginkey)
	SELECT  card.id, cgroup.id, creationdate, firstusedate, expirationdate,
		username, useralias, userpass, card.credit, 
		CASE WHEN card.activated THEN 1 ELSE 0 END,
		lastname, firstname, address, city, state, country, zipcode,
		phone, card.email, fax,
		card.currency, lastuse, nbused, creditlimit, card."language",
		redial, nbservice, id_campaign, num_trials_done, callback,
		servicelastrun, loginkey
		FROM a2b_old.cc_card AS card, cc_card_group AS cgroup
		WHERE card.id NOT IN (SELECT card_id FROM a2b_old.cc_agent_cards)
		  AND cgroup.agentid IS NULL
		  AND cgroup.tariffgroup IS NOT DISTINCT FROM card.tariff
		  AND cgroup.simultaccess IS NOT DISTINCT FROM card.simultaccess
		  AND cgroup.typepaid IS NOT DISTINCT FROM card.typepaid
		  AND cgroup.invoiceday IS NOT DISTINCT FROM card.invoiceday
		  AND cgroup.expiretype IS NOT DISTINCT FROM card.enableexpire
		  AND cgroup.expiredays IS NOT DISTINCT FROM card.expiredays
		  AND cgroup.autorefill IS NOT DISTINCT FROM card.autorefill;

SELECT pg_catalog.setval('cc_card_id_seq', (SELECT last_value FROM a2b_old.cc_card_id_seq));

\echo Migrating booths

INSERT INTO cc_booth ( id, name, location, agentid,
		datecreation, last_activation,
		disabled, cur_card_id, def_card_id,
		callerid)
	SELECT id, name, location, agentid,
		datecreation, last_activation,
		disabled, cur_card_id, def_card_id,
		name
		FROM a2b_old.cc_booth;

SELECT pg_catalog.setval('cc_booth_id_seq', (SELECT last_value FROM a2b_old.cc_booth_id_seq));

UPDATE cc_booth SET peername = booth.callerid, peerpass = buddy.secret
	FROM a2b_old.cc_booth AS booth, a2b_old.cc_sip_buddies AS buddy
		WHERE cc_booth.id = booth.id
		  AND booth.callerid = buddy.username;

\echo Please check this result to verify that all cards have been migrated:
SELECT
	(SELECT count(*) FROM cc_card) AS new_cards,
	(SELECT count(*) FROM a2b_old.cc_card) AS old_cards;
	

\echo Cards migrated (I hope).
-- eof
