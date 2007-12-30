-- Invoices

CREATE TABLE cc_invoices (
    id BIGSERIAL PRIMARY KEY,
    cardid BIGINT REFERENCES cc_card(id),
    agentid INTEGER REFERENCES cc_agent(id),
    orderref TEXT,
    created TIMESTAMP WITHOUT TIME ZONE DEFAULT now(),
    cover_startdate TIMESTAMP WITHOUT TIME ZONE,
    cover_enddate TIMESTAMP WITHOUT TIME ZONE,
    amount NUMERIC(15,5) DEFAULT 0,
    tax NUMERIC(15,5) DEFAULT 0,
    total NUMERIC(15,5) DEFAULT 0,
    invoicetype INTEGER,
    filename TEXT,
    payment_date   TIMESTAMP WITHOUT TIME ZONE,
    payment_status INTEGER DEFAULT 0,
    CHECK( (cardid IS NOT NULL) OR (agentid IS NOT NULL))
);

CREATE INDEX ind_cc_invoices ON cc_invoices USING btree (cover_startdate);


CREATE TABLE cc_invoice_history (
    id BIGSERIAL PRIMARY KEY,
    invoiceid BIGINT NOT NULL,	
    idate TIMESTAMP WITHOUT TIME ZONE DEFAULT now(),
    istatus INTEGER,
    icomment TEXT
);

CREATE INDEX ind_cc_invoice_history ON cc_invoice_history USING btree (idate);

-- This trigger can be set to any item having an invoice_id field
CREATE OR REPLACE FUNCTION cc_invoice_lock_f() RETURNS trigger AS $$
BEGIN
	-- Shortcut: allow clearing of the invoice
	IF TG_OP = 'UPDATE' THEN
		IF NEW.invoice_id IS NULL THEN
			RETURN NEW;
		END IF;
	END IF;
	
	IF OLD.invoice_id IS NOT NULL THEN
		RAISE EXCEPTION 'Item is invoiced in invoice %. Cannot modify',OLD.invoice_id;
	END IF;
	
	IF TG_OP = 'DELETE' THEN
		RETURN OLD;
	ELSE
		RETURN NEW;
	END IF;
END ; $$ LANGUAGE PLPGSQL;

-- ALTER TABLE cc_agentpay ADD invoice_id BIGINT REFERENCES cc_invoices(id) ON DELETE RESTRICT;
