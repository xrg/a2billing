--
-- A2Billing database - update database schema - v1.2.2 to update to v1.2.3
--

/* 

To create the database : 

mysql -u root -p"root password" < UPDATE-a2billing-v1.2.2-to-v1.2.3-mysql.sql

*/


 

ALTER TABLE cc_charge ADD COLUMN id_cc_did bigint ;
ALTER TABLE cc_charge ALTER COLUMN id_cc_did SET DEFAULT 0;

CREATE TABLE cc_did_use (
    id BIGINT NOT NULL AUTO_INCREMENT,
    id_cc_card BIGINT,
    id_did BIGINT NOT NULL,
    reservationdate TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    releasedate TIMESTAMP,
    activated INT DEFAULT 0,
    month_payed INT DEFAULT 0
)