-- Servers for a2billing:
-- An a2billing installation could cover several asterisk boxen.

INSERT INTO cc_server_manager (id_group, server_ip, manager_host, manager_username, manager_secret) VALUES (1, 'localhost', 'localhost', 'myasterisk', 'mycode');


CREATE TABLE cc_server_group (
	id	SERIAL PRIMARY KEY,
	name	TEXT ,
	description	TEXT
);

INSERT INTO cc_server_group (id, name, description) VALUES (1, 'default', 'default group of server');

/** Server entry: used for callback, realtime entries and numplans. 
   The 'host' field may not be null: each host should have a name. If the 'ip' one is filled,
   the ip will be preferred over the DNS entry of the 'host' field.
*/

CREATE TABLE cc_a2b_server (
    id 		SERIAL PRIMARY KEY,
    group	INTEGER NOT NULL REFERENCES cc_server_group(id),
    ip 		inet ,
    host 	TEXT NOT NULL,
    manager_username 	TEXT ,
    manager_secret 	TEXT ,
    lasttime_used	TIMESTAMP WITHOUT TIME ZONE DEFAULT NOW()
);

