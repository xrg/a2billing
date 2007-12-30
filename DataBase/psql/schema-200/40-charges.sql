-- Charges

/* agentid, from_agent suggest that the charge was set by the agent
   checked shows which system user verified the charge.
*/

CREATE TABLE cc_card_charge (
    id 			BIGSERIAL PRIMARY KEY,
    card 		BIGINT NOT NULL,
    iduser 		INTEGER DEFAULT 0 NOT NULL,
    creationdate 	timestamp without time zone DEFAULT now(),
    amount 		NUMERIC(12,4) NOT NULL,
    chargetype 		INTEGER DEFAULT 0,
    description 	TEXT,
    did 		BIGINT DEFAULT 0,
    subscription_fee 	BIGINT DEFAULT 0,
    invoice_id  BIGINT REFERENCES cc_invoices(id) ON DELETE SET NULL,
    agentid     BIGINT REFERENCES cc_agent(id),
    from_agent  BOOLEAN NOT NULL DEFAULT FALSE,
    checked     BIGINT REFERENCES cc_ui_authen(userid)
);

CREATE TRIGGER cc_charge_check_invoice BEFORE UPDATE OR DELETE ON cc_card_charge
	FOR EACH ROW EXECUTE PROCEDURE cc_invoice_lock_f();

/** Deleted charges.
	Since charges constitute an important money transaction, removed ones
	should be logged, i.e. inserted here
*/
CREATE TABLE cc_card_charge_bk (
    id_cc_card bigint NOT NULL,
    iduser integer DEFAULT 0 NOT NULL,
    creationdate timestamp without time zone,
    deletiondate timestamp WITHOUT time zone DEFAULT now(),
    amount numeric(12,4) NOT NULL,
    chargetype integer DEFAULT 0,
    description text,
    agentid bigint REFERENCES cc_agent(id),
    from_agent boolean NOT NULL DEFAULT FALSE,
    checked bigint REFERENCES cc_ui_authen(userid)
);

--eof
