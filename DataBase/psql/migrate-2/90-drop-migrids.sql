---

-- Until others are ok
ERROR;

\echo Dropping the migration id columns
-- ALTER TABLE cc_tariffgroup DROP COLUMN migr_oldid;
ALTER TABLE cc_tariffplan DROP COLUMN migr_oldid;
ALTER TABLE cc_retailplan DROP COLUMN migr_oldid;

DROP INDEX  cc_sellrate_migr_idx;
DROP INDEX  cc_buyrate_migr_idx;

ALTER TABLE cc_buyrate DROP COLUMN migr_oldid;
ALTER TABLE cc_sellrate DROP COLUMN migr_oldid;
