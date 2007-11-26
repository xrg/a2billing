----

CREATE TABLE cc_tariffgroup (
    id serial NOT NULL PRIMARY KEY,
    iduser integer DEFAULT 0 NOT NULL,
--     idtariffplan integer DEFAULT 0 NOT NULL,
    tariffgroupname text NOT NULL UNIQUE,
    lcrtype integer DEFAULT 0 NOT NULL,
    creationdate timestamp without time zone DEFAULT now(),
    removeinterprefix integer DEFAULT 0 NOT NULL,
    package_offer bigint not null default 0
);

CREATE TABLE cc_tariffgroup_plan (
    tgid integer NOT NULL REFERENCES cc_tariffgroup(id),
    tpid integer NOT NULL REFERENCES cc_tariffplan(id)
    PRIMARY KEY(tgid,tpid)
);

/** neg_currency is the currency these rates are negotiated at.
The credit at a tariffplan is stored in neg_currency units!
*/

CREATE TABLE cc_tariffplan (
    id serial NOT NULL,
--     iduser integer DEFAULT 0 NOT NULL,
    tariffname text NOT NULL,
    creationdate timestamp without time zone DEFAULT now(),
    start_date timestamp without time zone DEFAULT now(),
    stop_date timestamp without time zone,
    starttime integer NOT NULL DEFAULT 0,
    endtime integer NOT NULL DEFAULT 10079,
    description text,
    id_trunk integer DEFAULT 0,
    secondusedreal integer DEFAULT 0,
    secondusedcarrier integer DEFAULT 0,
    secondusedratecard integer DEFAULT 0,
--     reftariffplan integer DEFAULT 0,
    idowner integer DEFAULT 0,
    dnidprefix text NOT NULL DEFAULT 'all'::text,
    calleridprefix text NOT NULL DEFAULT 'all'::text,
    neg_currency integer REFERENCES cc_currency(id),
    credit NUMERIC(12,4) NOT NULL DEFAULT 0.0
);

CREATE TABLE 


/** The type of dialprefix ensures that the prefix will not exceed the length
    matched by the algorithm! An empty "" prefix matches all. */

CREATE TABLE cc_ratecard (
    id serial NOT NULL,
    idtp integer NOT NULL REFERENCES cc_tariffplan(id),
    dialprefix VARCHAR(12) NOT NULL,
    destination text NOT NULL,
    buyrate NUMERIC(7,4) DEFAULT 0 NOT NULL,
    buyrateinitblock integer DEFAULT 0 NOT NULL,
    buyrateincrement integer DEFAULT 0 NOT NULL,
    rateinitial NUMERIC(7,4) DEFAULT 0 NOT NULL,
    initblock integer DEFAULT 0 NOT NULL,
    billingblock integer DEFAULT 0 NOT NULL,
    connectcharge NUMERIC(7,4) DEFAULT 0 NOT NULL,
    disconnectcharge NUMERIC(7,4) DEFAULT 0 NOT NULL,
    stepchargea NUMERIC(7,4) DEFAULT 0 NOT NULL,
    chargea NUMERIC(7,4) DEFAULT 0 NOT NULL,
    timechargea integer DEFAULT 0 NOT NULL,
    billingblocka integer DEFAULT 0 NOT NULL,
    stepchargeb NUMERIC(7,4) DEFAULT 0 NOT NULL,
    chargeb NUMERIC(7,4) DEFAULT 0 NOT NULL,
    timechargeb integer DEFAULT 0 NOT NULL,
    billingblockb integer DEFAULT 0 NOT NULL,
    stepchargec NUMERIC(7,4) DEFAULT 0 NOT NULL,
    chargec NUMERIC(7,4) DEFAULT 0 NOT NULL,
    timechargec integer DEFAULT 0 NOT NULL,
    billingblockc integer DEFAULT 0 NOT NULL,
/*    startdate timestamp(0) without time zone DEFAULT now(),
    stopdate timestamp(0) without time zone,*/
/*    starttime integer NOT NULL DEFAULT 0,
    endtime integer NOT NULL DEFAULT 10079,*/
--     id_trunk integer DEFAULT -1,	
--     musiconhold character varying(100),
    freetimetocall_package_offer INTEGER NOT NULL DEFAULT 0,
--     id_outbound_cidgroup INTEGER NOT NULL DEFAULT -1
    quality float NOT NULL DEFAULT 1.0,
    qual_tstamp TIMESTAMP NOT NULL DEFAULT NOW
);

CREATE INDEX ind_cc_ratecard_dialprefix ON cc_ratecard USING btree (dialprefix);
