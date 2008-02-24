-- Mails and alerts
-- Copyright P. Christeas 2008


CREATE TABLE cc_templatemail (
    id SERIAL PRIMARY KEY,
    mtype VARCHAR(30) NOT NULL,
    lang VARCHAR(10) NOT NULL DEFAULT 'C',
    fromemail text NOT NULL,
    fromname text,
    subject text,
    message text NOT NULL,
    defargs text,
    UNIQUE (mtype,lang)
);

/** This table will hold any attempts to notify somebody with e-mail
    Even after the mail is sent, the table will still hold it, for
    logging purposes.
    States are:
    	1: new, queueable
    	2: new, holded (should not be sent)
    	3: sent
    	4: failed-to-send
    	5: wait-resend
    icomments: a field where the system could mark comments for internal
    	usage.
*/
CREATE TABLE cc_mailings (
    id BIGSERIAL PRIMARY KEY,
    tmail_id INTEGER NOT NULL REFERENCES cc_templatemail(id),
    state INTEGER NOT NULL DEFAULT 1,
    tomail TEXT NOT NULL,
    args  TEXT,
    icomments TEXT
);

--eof
