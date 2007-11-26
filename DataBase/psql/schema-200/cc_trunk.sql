
CREATE TABLE cc_provider(
    id serial PRIMARY KEY,
    provider_name text NOT NULL UNIQUE,
    creationdate timestamp without time zone DEFAULT now(),
    description text
);


CREATE TABLE cc_trunk (
    id serial PRIMARY KEY,
    trunkcode text NOT NULL,
    trunkprefix text,
    providertech text NOT NULL,
    providerip text NOT NULL,
    removeprefix text,
    secondusedreal integer DEFAULT 0,
    secondusedcarrier integer DEFAULT 0,
    secondusedratecard integer DEFAULT 0,
    creationdate timestamp(0) without time zone DEFAULT now(),
    failover_trunk integer /* Needed? */,
    addparameter text,
    provider INTEGER REFERENCES cc_provider(id)
);


ALTER TABLE cc_trunk ADD COLUMN inuse INT DEFAULT 0;
ALTER TABLE cc_trunk ADD COLUMN maxuse INT DEFAULT -1;
ALTER TABLE cc_trunk ADD COLUMN status INT DEFAULT 1;
ALTER TABLE cc_trunk ADD COLUMN if_max_use INT DEFAULT 0;
