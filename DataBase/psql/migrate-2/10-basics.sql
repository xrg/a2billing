-- Standard tables

-- Skip root, admin
INSERT INTO cc_ui_authen (userid, login, "password", groupid, perms, confaddcust, name, direction, zipcode,
    state, phone, fax, datecreation)
SELECT userid, login, "password", groupid, perms, confaddcust, name, direction, zipcode,
    state, phone, fax, datecreation FROM a2b_old.cc_ui_authen 
    WHERE userid > 2 AND login <> 'root' AND login <> 'admin';

UPDATE cc_ui_authen SET "password" = olld."password" 
	FROM a2b_old.cc_ui_authen AS olld 
	WHERE cc_ui_authen.userid <= 2
	AND cc_ui_authen.userid = olld.userid;

SELECT pg_catalog.setval('cc_ui_authen_userid_seq', (SELECT last_value FROM a2b_old.cc_ui_authen_userid_seq));

\echo Skipping syslog
\echo Skipping cc_currencies

INSERT INTO cc_server_group(id, name, description)
	SELECT id, name, description FROM a2b_old.cc_server_group WHERE id > 1;

SELECT pg_catalog.setval('cc_server_group_id_seq', (SELECT last_value FROM a2b_old.cc_server_group_id_seq));


INSERT INTO cc_a2b_server(id,grp,ip,
		host,manager_username,manager_secret,lasttime_used)
	SELECT id, id_group, CASE WHEN server_ip = 'default' THEN '127.0.0.1' ELSE server_ip :: INET END, 
		manager_host, manager_username, manager_secret, lasttime_used
	FROM a2b_old.cc_server_manager;

SELECT pg_catalog.setval('cc_a2b_server_id_seq', (SELECT last_value FROM a2b_old.cc_server_manager_id_seq));
	
