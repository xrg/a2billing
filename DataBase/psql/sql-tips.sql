-- Migration
ALTER TABLE cc_table SET SCHEMA a2b_old;

"current_user" function

CREATE ROLE a2b_group INHERIT;
create role a2b_localhost LOGIN ;
GRANT a2b_group to a2b_localhost;

SELECT pg_catalog.setval('cc_agent_id_seq', 3, true);
