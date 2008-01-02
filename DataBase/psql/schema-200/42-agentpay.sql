-- Agent payments

-- This table will hold the transactions for the agent<->card
-- refills. boothid is optional.
-- 'carried' is important: if set to true, then the transaction
-- will NOT cause cc_agent.credit/cc.card.credit to be updated.
-- That is useful when inserting dummy refills to carry the credit
-- between sessions. 

CREATE TABLE cc_agentrefill (
    id BIGSERIAL NOT NULL,
    date TIMESTAMP WITHOUT TIME ZONE DEFAULT now() NOT NULL,
    credit numeric(12,4) NOT NULL,
    carried BOOLEAN NOT NULL DEFAULT false,
    pay_type INTEGER,
    card_id bigint NOT NULL REFERENCES cc_card(id),
    agentid bigint NOT NULL REFERENCES cc_agent(id),
    boothid bigint REFERENCES cc_booth(id)
);

CREATE TABLE cc_shopsessions (
    id bigserial PRIMARY KEY,
    booth bigint NOT NULL REFERENCES cc_booth(id),
    card bigint NOT NULL REFERENCES cc_card(id),
    starttime timestamp WITHOUT TIME ZONE NOT NULL DEFAULT now(),
    endtime  timestamp WITHOUT TIME ZONE,
    state text NOT NULL -- REFERENCES cc_texts(id)
);


CREATE TABLE cc_agentpay (
    id BIGSERIAL NOT NULL,
    date TIMESTAMP WITHOUT TIME ZONE DEFAULT now() NOT NULL,
    credit NUMERIC(12,4) NOT NULL,
    pay_type INTEGER,
    agentid BIGINT NOT NULL REFERENCES cc_agent(id),
    invoice_id BIGINT REFERENCES cc_invoices(id) ON DELETE RESTRICT,
    descr TEXT 
);

--eof
