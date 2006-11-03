-- Additional tables for callshop feature

CREATE TABLE cc_agent (
    id bigserial NOT NULL,
    name text NOT NULL,
    login VARCHAR(20) NOT NULL,
    passwd VARCHAR(40) NOT NULL,
    groupid integer,
    location text,
    datecreation timestamp without time zone DEFAULT now(),
    "language" text DEFAULT 'en'::text,
    tariffgroup integer REFERENCES cc_tariffgroup(id),
    options integer NOT NULL DEFAULT 0
    );
