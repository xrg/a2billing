\echo "Currency tables"

CREATE TABLE cc_currencies (
    id serial PRIMARY KEY,
    currency char(3) default '' NOT NULL UNIQUE,
    name character varying(30) default '' NOT NULL,
    value numeric(12,5) default '0.00000' NOT NULL,
    lastupdate timestamp without time zone DEFAULT now(),	
    csign VARCHAR(6),
    sign_pre boolean DEFAULT 'f' NOT NULL,
    cformat VARCHAR(20) DEFAULT 'FM99G999G999G990D00' NOT NULL,
    cformat2 VARCHAR(26) DEFAULT 'FM99G999G999G990D0099' NOT NULL
);

--eof
