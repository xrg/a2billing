-- Numbering plans

CREATE TABLE cc_numplan (
	id serial PRIMARY KEY,
	name VARCHAR(30) NOT NULL,
	aliaslen SMALLINT NOT NULL DEFAULT 5,
	peerprefix VARCHAR(10) NOT NULL DEFAULT '55',
	e164prefix VARCHAR(4) NOT NULL DEFAULT '00'
);

INSERT INTO cc_numplan(id,name) VALUES(1,'Default');

--eof
