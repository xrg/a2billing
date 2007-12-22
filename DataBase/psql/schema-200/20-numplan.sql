-- Numbering plans

CREATE TABLE cc_numplan (
	id serial PRIMARY KEY,
	name VARCHAR(30) NOT NULL,
	aliaslen SMALLINT NOT NULL DEFAULT 5
);

INSERT INTO cc_numplan(id,name) VALUES(1,'Default');

CREATE TABLE cc_numplan_pattern (
	id SERIAL PRIMARY KEY,
	nplan INTEGER NOT NULL REFERENCES cc_numplan(id),
	find  VARCHAR(6) NOT NULL,
	repl  VARCHAR(8) NOT NULL DEFAULT '',
	nick  TEXT NOT NULL DEFAULT ''
);

-- Sample value
INSERT INTO cc_numplan_pattern(nplan,find,repl,nick) VALUES (1,'00','+','International');
INSERT INTO cc_numplan_pattern(nplan,find,repl,nick) VALUES (1,'55','h','On-net peer');
GRANT SELECT ON cc_numplan TO a2b_group ;
GRANT SELECT ON cc_numplan_pattern TO a2b_group ;

--eof
