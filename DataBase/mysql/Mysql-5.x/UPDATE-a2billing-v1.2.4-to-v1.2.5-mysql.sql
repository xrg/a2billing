--
-- A2Billing database - update database schema - v1.2.4 to update to v1.2.5
--

/* 

To create the database : 

mysql -u root -p"root password" < UPDATE-a2billing-v1.2.4-to-v1.2.5-mysql.sql

*/

CREATE TABLE cc_alarm (
    id BIGINT NOT NULL AUTO_INCREMENT,
    name text NOT NULL,
    periode INT NOT NULL DEFAULT 1,
    type INT NOT NULL DEFAULT 1,
    maxvalue float NOT NULL,
    minvalue float NOT NULL DEFAULT -1,
    id_trunk INT,
    status INT NOT NULL DEFAULT 0,
    numberofrun INT NOT NULL DEFAULT 0,
    numberofalarm INT NOT NULL DEFAULT 0,    
    datecreate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    datelastrun TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    creationdate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    emailreport VARCHAR(50)
    PRIMARY KEY (id)
);

 CREATE TABLE cc_alarm_report (
    id BIGINT NOT NULL AUTO_INCREMENT,
    cc_alarm_id BIGINT NOT NULL,
    calculatedvalue float NOT NULL,
    daterun TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    PRIMARY KEY (id)
);
