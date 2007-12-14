----

CREATE TABLE cc_tariffgroup (
    id serial NOT NULL PRIMARY KEY,
    iduser integer DEFAULT 0 NOT NULL,
--     idtariffplan integer DEFAULT 0 NOT NULL,
    name text NOT NULL UNIQUE,
    lcrtype integer DEFAULT 0 NOT NULL,
--     creationdate timestamp without time zone DEFAULT now(),
--     removeinterprefix integer DEFAULT 0 NOT NULL,
    package_offer bigint not null default 0
);

/** neg_currency is the currency these rates are negotiated at.
The credit at a tariffplan is stored in neg_currency units!
*/

CREATE TABLE cc_tariffplan (
    id serial NOT NULL PRIMARY KEY,
--     iduser integer DEFAULT 0 NOT NULL,
    tariffname text NOT NULL,
    creationdate timestamp without time zone DEFAULT now(),
    start_date timestamp without time zone DEFAULT now(),
    stop_date timestamp without time zone,
    starttime integer NOT NULL DEFAULT 0,
    endtime integer NOT NULL DEFAULT 10079,
    description text,
    trunk INTEGER REFERENCES cc_trunk(id) NOT NULL,
    secondusedreal integer DEFAULT 0,
    secondusedcarrier integer DEFAULT 0,
    secondusedratecard integer DEFAULT 0,
--     reftariffplan integer DEFAULT 0,
    idowner integer DEFAULT 0,
    dnidprefix text NOT NULL DEFAULT 'all'::text,
    calleridprefix text NOT NULL DEFAULT 'all'::text,
    neg_currency integer REFERENCES cc_currencies(id),
    credit NUMERIC(12,4) NOT NULL DEFAULT 0.0
);

CREATE TABLE cc_retailplan (
    id serial NOT NULL PRIMARY KEY,
--     iduser integer DEFAULT 0 NOT NULL,
    name text NOT NULL,
    creationdate timestamp without time zone DEFAULT now(),
    start_date timestamp without time zone DEFAULT now(),
    stop_date timestamp without time zone,
    starttime integer NOT NULL DEFAULT 0,
    endtime integer NOT NULL DEFAULT 10079,
    description text,
    idowner integer DEFAULT 0,
    dnidprefix text NOT NULL DEFAULT 'all'::text
/*    neg_currency integer REFERENCES cc_currency(id),
    credit NUMERIC(12,4) NOT NULL DEFAULT 0.0*/
);

-- CREATE TABLE 
/** Sell rates: these could be per-buy, or even groupped ones,
   so that editing the rates is easier. In addition, same provider
   could be used with different retail prices to each tariff group
 */
CREATE TABLE cc_sellrate(
    id serial NOT NULL PRIMARY KEY,
    idrp INTEGER NOT NULL REFERENCES cc_retailplan(id),
    destination text NOT NULL,
    rateinitial NUMERIC(12,4) DEFAULT 0 NOT NULL,
    initblock integer DEFAULT 0 NOT NULL,
    billingblock integer DEFAULT 0 NOT NULL,
    connectcharge NUMERIC(12,4) DEFAULT 0 NOT NULL,
    disconnectcharge NUMERIC(12,4) DEFAULT 0 NOT NULL,
    stepchargea NUMERIC(12,4) DEFAULT 0 NOT NULL,
    chargea NUMERIC(12,4) DEFAULT 0 NOT NULL,
    timechargea integer DEFAULT 0 NOT NULL,
    billingblocka integer DEFAULT 0 NOT NULL,
    stepchargeb NUMERIC(12,4) DEFAULT 0 NOT NULL,
    chargeb NUMERIC(12,4) DEFAULT 0 NOT NULL,
    timechargeb integer DEFAULT 0 NOT NULL,
    billingblockb integer DEFAULT 0 NOT NULL,
    stepchargec NUMERIC(12,4) DEFAULT 0 NOT NULL,
    chargec NUMERIC(12,4) DEFAULT 0 NOT NULL,
    timechargec integer DEFAULT 0 NOT NULL,
    billingblockc integer DEFAULT 0 NOT NULL,
/*    startdate timestamp(0) without time zone DEFAULT now(),
    stopdate timestamp(0) without time zone,*/
/*    starttime integer NOT NULL DEFAULT 0,
    endtime integer NOT NULL DEFAULT 10079,*/
--     id_trunk integer DEFAULT -1,	
--     musiconhold character varying(100),
    freetimetocall_package_offer INTEGER NOT NULL DEFAULT 0
--     id_outbound_cidgroup INTEGER NOT NULL DEFAULT -1

);


CREATE TABLE cc_buyrate (
    id serial NOT NULL PRIMARY KEY,
    idtp integer NOT NULL REFERENCES cc_tariffplan(id),
--     dialprefix VARCHAR(12) NOT NULL,
    destination text NOT NULL,
    buyrate NUMERIC(8,4) DEFAULT 0 NOT NULL,
    buyrateinitblock integer DEFAULT 0 NOT NULL,
    buyrateincrement integer DEFAULT 0 NOT NULL,
    quality float NOT NULL DEFAULT 1.0,
    qual_tstamp TIMESTAMP NOT NULL DEFAULT NOW()
);


CREATE TABLE cc_tariffgroup_plan (
    tgid integer NOT NULL REFERENCES cc_tariffgroup(id),
    rtid integer NOT NULL REFERENCES cc_retailplan(id),
    PRIMARY KEY(tgid,rtid)
);

CREATE TABLE cc_rtplan_buy (
    rtid integer NOT NULL REFERENCES cc_retailplan(id),
    tpid INTEGER NOT NULL REFERENCES cc_tariffplan(id),
    PRIMARY KEY(tpid,rtid)
);

/** The type of dialprefix ensures that the prefix will not exceed the length
    matched by the algorithm! An empty "" prefix matches all. */

CREATE TABLE cc_buy_prefix (
	id BIGSERIAL PRIMARY KEY,
	brid INTEGER NOT NULL REFERENCES cc_buyrate(id) ON DELETE CASCADE,
	dialprefix VARCHAR(12) NOT NULL,
	UNIQUE(brid, dialprefix)
);

CREATE TABLE cc_sell_prefix (
	id BIGSERIAL PRIMARY KEY,
	srid INTEGER NOT NULL REFERENCES cc_sellrate(id) ON DELETE CASCADE,
	dialprefix VARCHAR(12) NOT NULL,
	UNIQUE(srid, dialprefix)
);

-- CREATE INDEX ind_cc_ratecard_dialprefix ON cc_ratecard USING btree (dialprefix);
