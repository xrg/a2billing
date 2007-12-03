-- This file tests the integrity of the database wrt. asterisk

-- Usage: Connect to the db as the user asterisk will use, and
-- call this file.
-- eg. 'psql -U a2b_localhost -f ./asterisk-test.sql a2billing'


-- If any of these queries fail, fix permissions etc..!

SELECT * FROM ast_sip_peers LIMIT 1;

