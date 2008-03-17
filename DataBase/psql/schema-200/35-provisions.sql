-- Tables used in the provisioning system

CREATE TABLE provision_group (
	id SERIAL PRIMARY KEY,
	categ TEXT NOT NULL,
	model VARCHAR(40) NOT NULL,
	name TEXT NOT NULL,
	sub_name TEXT,
	args	TEXT,

	options INTEGER NOT NULL DEFAULT 0,
	metric  INTEGER NOT NULL DEFAULT 10
);

CREATE TABLE provisions (
	id BIGSERIAL PRIMARY KEY,
	grp_id INTEGER NOT NULL REFERENCES provision_group(id),
	name	TEXT NOT NULL,
	sub_name TEXT,
	valuef	TEXT,
	options INTEGER NOT NULL DEFAULT 0,
	metric  INTEGER NOT NULL DEFAULT 10
);


--eof
