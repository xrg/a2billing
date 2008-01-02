-- Texts, pay types


CREATE TABLE cc_texts (
	id integer NOT NULL,
	lang VARCHAR(10) NOT NULL DEFAULT 'C',
	txt text NOT NULL,
	src int NOT NULL DEFAULT 1,
	CONSTRAINT cc_texts_pkey PRIMARY KEY (id,lang) );


/** cc_paytypes Define rules (texts) for pay/charge types to be used in combos etc.
	id is the text, as seen in cc_texts. This is better than using text, since
		pay_type would be used many times in reports and it has to be both
		matched and translated. Hence an integer field.
	side is an arbitrary enum like:
		1 company to agent
		2 agent to company
		3 charges from agent to customer
		4 bonuses from agent to customer
		5 normal transactions between agent and customer
	preset is a text field so that the php code can automatically select and
		apply one of those charges. eg. 'print-cust-invoice'
*/

CREATE TABLE cc_paytypes (
	id integer NOT NULL,
	side smallint NOT NULL,
	charge NUMERIC(12,4),
	preset VARCHAR(30) UNIQUE
);

INSERT INTO cc_paytypes(id,side,preset) VALUES(gettext_ri('Carry to next session'),5,'carry');
INSERT INTO cc_paytypes(id,side,preset) VALUES(gettext_ri('Carried from previous session'),5,'carried');
INSERT INTO cc_paytypes(id,side,preset) VALUES(gettext_ri('Pay the account'),5,'settle');

INSERT INTO cc_paytypes(id,side,preset) VALUES(gettext_ri('Manual commission credit'),1,'manual-commission');
INSERT INTO cc_paytypes(id,side,preset) VALUES(gettext_ri('Auto commission credit'),1,'auto-commission');
INSERT INTO cc_paytypes(id,side,preset) VALUES(gettext_ri('Payment from agent'),2,'agent-pay');
