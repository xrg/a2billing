---

\echo Dropping aggregates
DROP AGGREGATE IF EXISTS into_array (bigint);
DROP AGGREGATE IF EXISTS into_array (integer);
DROP AGGREGATE IF EXISTS into_array  (text);
DROP AGGREGATE IF EXISTS tarray_first50 (text) CASCADE;

--eof
