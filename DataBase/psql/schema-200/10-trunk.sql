
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
    metric INTEGER NOT NULL DEFAULT 10,
    providertech text NOT NULL,
    providerip text NOT NULL,
    secondusedreal integer DEFAULT 0,
    secondusedcarrier integer DEFAULT 0,
    secondusedratecard integer DEFAULT 0,
    creationdate timestamp(0) without time zone DEFAULT now(),
    failover_trunk integer /* Needed? */,
    addparameter text,
    trunkfmt INTEGER NOT NULL DEFAULT 1,
    provider INTEGER REFERENCES cc_provider(id),
    inuse INT DEFAULT 0,
    maxuse INT DEFAULT -1,
    status INT DEFAULT 1,
    if_max_use INT DEFAULT 0
);


