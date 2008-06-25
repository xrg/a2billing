--- Time period structures

-- Copyright (C) P. Christeas, 2008




CREATE TABLE time_periods (
	id	SERIAL PRIMARY KEY,
	name	TEXT	 NOT NULL UNIQUE,
	enabled	BOOLEAN  NOT NULL DEFAULT true, /* enables caching */
	comment TEXT
);


CREATE TABLE time_period_interval (
	id      SERIAL PRIMARY KEY,
	idtp    INTEGER NOT NULL REFERENCES time_periods(id),
	percity VARCHAR(25) NOT NULL DEFAULT 'weekly',
	istart	INTERVAL NOT NULL,
	iend	INTERVAL
);


CREATE TABLE time_period_cache (
	id   BIGSERIAL PRIMARY KEY,
	idtp INTEGER NOT NULL REFERENCES time_periods(id),
	tstart TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT now(),
	tend TIMESTAMP WITH TIME ZONE NOT NULL,
	status BOOLEAN NOT NULL
);

CREATE INDEX time_period_c_idtp_ind ON time_period_cache USING btree (idtp);
CREATE INDEX time_period_c_tend_ind ON time_period_cache USING btree (tend);

--eof
