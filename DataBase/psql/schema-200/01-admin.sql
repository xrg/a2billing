\echo Administration tables

CREATE TABLE cc_ui_authen (
    userid bigserial PRIMARY KEY,
    login text NOT NULL UNIQUE,
    "password" text NOT NULL,
    groupid integer,
    perms integer,
    confaddcust integer,
    name text,
    direction text,
    zipcode text,
    state text,
    phone text,
    fax text,
    datecreation TIMESTAMP without time zone DEFAULT NOW()
);

INSERT INTO cc_ui_authen VALUES (1, 'root', 'myroot', 0, 65535, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2005-02-26 20:33:27.691314-05');
INSERT INTO cc_ui_authen VALUES (2, 'admin', 'mypassword', 0, 65535, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2005-02-26 21:14:05.391501-05');
\echo Created Default admins.

-- CREATE TABLE cc_config_group (
--     id 		SERIAL PRIMARY KEY,
--     title 	VARCHAR(64) NOT NULL UNIQUE,
--     descr 	TEXT NOT NULL
-- );
-- 
-- COPY cc_config_group(title,descr) FROM STDIN;
-- global	This configuration group handles the global settings for application
-- callback	This configuration group handles callback settings.
-- webcustomerui	This configuration group handles Web Customer User Interface.
-- sip-iax-info	SIP & IAX client configuration information.
-- epayment_method	Epayment Methods Configuration.
-- signup	This configuration group handles the signup related settings.
-- backup	This configuration group handles the backup/restore related settings.
-- webui	This configuration group handles the WEBUI and API Configuration.
-- peer_friend	This configuration group define parameters for the friends creation.
-- log-files	This configuration group handles the Log Files Directory Paths.
-- agi-conf1	This configuration group handles the AGI Configuration.
-- \.


-- CREATE TABLE cc_configuration (
--   cid           BIGSERIAL PRIMARY KEY,
--   ctitle        VARCHAR(64) NOT NULL,
--   ckey          VARCHAR(64) NOT NULL,
--   cvalue        VARCHAR(255) NOT NULL,
--   cdescription  VARCHAR(255) NOT NULL,
--   ctype         INTEGER NOT NULL DEFAULT 0,
--   use_function  VARCHAR(255) NULL,
--   set_function  VARCHAR(255) NULL
-- 
-- );

CREATE TABLE cc_sysconf (
	id     SERIAL PRIMARY KEY,
	grp    VARCHAR(64) NOT NULL,
	name   VARCHAR(64) NOT NULL,
	val    TEXT,
	UNIQUE(grp,name)
);

GRANT SELECT ON cc_sysconf TO a2b_group ;

-- INSERT INTO cc_configuration (ctitle, ckey, cvalue, cdescription) VALUES ('Login Username', 'MODULE_PAYMENT_AUTHORIZENET_LOGIN', 'testing', 'The login username used for the Authorize.net service');
-- INSERT INTO cc_configuration (ctitle, ckey, cvalue, cdescription) VALUES ('Transaction Key', 'MODULE_PAYMENT_AUTHORIZENET_TXNKEY', 'Test', 'Transaction Key used for encrypting TP data');
-- INSERT INTO cc_configuration (ctitle, ckey, cvalue, cdescription, set_function) VALUES ('Transaction Mode', 'MODULE_PAYMENT_AUTHORIZENET_TESTMODE', 'Test', 'Transaction mode used for processing orders', 'tep_cfg_select_option(array(\'Test\', \'Production\'), ');
-- INSERT INTO cc_configuration (ctitle, ckey, cvalue, cdescription, set_function) VALUES ('Transaction Method', 'MODULE_PAYMENT_AUTHORIZENET_METHOD', 'Credit Card', 'Transaction method used for processing orders', 'tep_cfg_select_option(array(\'Credit Card\', \'eCheck\'), ');
-- INSERT INTO cc_configuration (ctitle, ckey, cvalue, cdescription, set_function) VALUES ('Customer Notifications', 'MODULE_PAYMENT_AUTHORIZENET_EMAIL_CUSTOMER', 'False', 'Should Authorize.Net e-mail a receipt to the customer?', 'tep_cfg_select_option(array(\'True\', \'False\'), ');
-- INSERT INTO cc_configuration (ctitle, ckey, cvalue, cdescription, set_function) VALUES ('Enable Authorize.net Module', 'MODULE_PAYMENT_AUTHORIZENET_STATUS', 'True', 'Do you want to accept Authorize.net payments?', 'tep_cfg_select_option(array(\'True\', \'False\'), ');
-- 
-- INSERT INTO cc_configuration (ctitle, ckey, cvalue, cdescription, set_function) VALUES ('Enable PayPal Module', 'MODULE_PAYMENT_PAYPAL_STATUS', 'True', 'Do you want to accept PayPal payments?','tep_cfg_select_option(array(\'True\', \'False\'), ');
-- INSERT INTO cc_configuration (ctitle, ckey, cvalue, cdescription) VALUES ('E-Mail Address', 'MODULE_PAYMENT_PAYPAL_ID', 'you@yourbusiness.com', 'The e-mail address to use for the PayPal service');
-- INSERT INTO cc_configuration (ctitle, ckey, cvalue, cdescription, set_function) VALUES ('Transaction Currency', 'MODULE_PAYMENT_PAYPAL_CURRENCY', 'Selected Currency', 'The currency to use for credit card transactions', 'tep_cfg_select_option(array(\'Selected Currency\',\'USD\',\'CAD\',\'EUR\',\'GBP\',\'JPY\'), ');
-- 
-- INSERT INTO cc_configuration (ctitle, ckey, cvalue, cdescription) VALUES ('E-Mail Address', 'MODULE_PAYMENT_MONEYBOOKERS_ID', 'you@yourbusiness.com', 'The eMail address to use for the moneybookers service');
-- INSERT INTO cc_configuration (ctitle, ckey, cvalue, cdescription) VALUES ('Referral ID', 'MODULE_PAYMENT_MONEYBOOKERS_REFID', '989999', 'Your personal Referral ID from moneybookers.com');
-- INSERT INTO cc_configuration (ctitle, ckey, cvalue, cdescription, set_function) VALUES ('Transaction Currency', 'MODULE_PAYMENT_MONEYBOOKERS_CURRENCY', 'Selected Currency', 'The default currency for the payment transactions', 'tep_cfg_select_option(array(\'Selected Currency\',\'EUR\', \'USD\', \'GBP\', \'HKD\', \'SGD\', \'JPY\', \'CAD\', \'AUD\', \'CHF\', \'DKK\', \'SEK\', \'NOK\', \'ILS\', \'MYR\', \'NZD\', \'TWD\', \'THB\', \'CZK\', \'HUF\', \'SKK\', \'ISK\', \'INR\'), ');
-- INSERT INTO cc_configuration (ctitle, ckey, cvalue, cdescription, set_function) VALUES ('Transaction Language', 'MODULE_PAYMENT_MONEYBOOKERS_LANGUAGE', 'Selected Language', 'The default language for the payment transactions', 'tep_cfg_select_option(array(\'Selected Language\',\'EN\', \'DE\', \'ES\', \'FR\'), ');
-- INSERT INTO cc_configuration (ctitle, ckey, cvalue, cdescription, set_function) VALUES ('Enable moneybookers Module', 'MODULE_PAYMENT_MONEYBOOKERS_STATUS', 'True', 'Do you want to accept moneybookers payments?','tep_cfg_select_option(array(\'True\', \'False\'), ');


CREATE TABLE cc_system_log (
    id 		BIGSERIAL PRIMARY KEY,
    iduser 	INTEGER DEFAULT 0,
    loglevel	INTEGER NOT NULL DEFAULT 0,
    action	TEXT NOT NULL,
    description	TEXT,
    data 	TEXT,
    tablename	VARCHAR(255),
    pagename	VARCHAR(255),
    ipaddress	INET,	
    creationdate  TIMESTAMP(0) without time zone DEFAULT NOW()
);

