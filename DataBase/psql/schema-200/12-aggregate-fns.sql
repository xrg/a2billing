-- Aggregate helpers

-- Copyright (C) P. Christeas, 2007,2008

CREATE AGGREGATE into_array (TEXT) (
    sfunc = array_append,
    stype = TEXT[],
    initcond = '{}'
    );

CREATE AGGREGATE into_array (INTEGER) (
    sfunc = array_append,
    stype = INTEGER[],
    initcond = '{}'
    );

CREATE AGGREGATE into_array (BIGINT) (
    sfunc = array_append,
    stype = BIGINT[],
    initcond = '{}'
    );

--eof