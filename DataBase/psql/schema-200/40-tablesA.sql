----


CREATE TABLE cc_campaign (
    id bigserial NOT NULL PRIMARY KEY,
    campaign_name text NOT NULL UNIQUE,
    creationdate timestamp without time zone DEFAULT now(),
    startingdate timestamp without time zone DEFAULT now(),
    expirationdate timestamp without time zone,
    description text,
    id_trunk bigint NOT NULL REFERENCES cc_trunk(id),
    secondusedreal integer DEFAULT 0,
    nb_callmade integer DEFAULT 0,
    enable integer DEFAULT 0 NOT NULL
);

CREATE TABLE cc_phonelist (
    id BIGSERIAL NOT NULL PRIMARY KEY,
    campaign BIGINT DEFAULT 0 NOT NULL REFERENCES cc_campaign(id),
    card BIGINT DEFAULT 0 NOT NULL REFERENCES cc_card(id),
    numbertodial TEXT NOT NULL,
    name TEXT NOT NULL,
    inuse INTEGER DEFAULT 0,
    enable INTEGER DEFAULT 1 NOT NULL,
    num_trials_done INTEGER DEFAULT 0,
    creationdate TIMESTAMP WITHOUT TIME ZONE DEFAULT NOW(),
    last_attempt TIMESTAMP WITHOUT TIME ZONE DEFAULT NOW(),
    secondusedreal INTEGER DEFAULT 0,
    additionalinfo TEXT NOT NULL	
);
	
