-- Create the database..

\set ON_ERROR_STOP

SET default_with_oids = false;

\! cat ./schema-200/*.sql > all.tmp.sql
\i all.tmp.sql
