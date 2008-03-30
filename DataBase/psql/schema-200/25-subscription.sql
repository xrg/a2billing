-- Tables for subscription-based features

CREATE TABLE subscription_template(
	id SERIAL PRIMARY KEY,
	name TEXT NOT NULL UNIQUE,
	pubname TEXT NOT NULL,
	invoiced BOOLEAN NOT NULL DEFAULT true
);

CREATE TABLE card_subscription(
	id BIGSERIAL PRIMARY KEY,
	template     INTEGER NOT NULL 
		/*REFERENCES subscription_template(id) can't do that with inherited refs.*/,
	card         BIGINT NOT NULL REFERENCES cc_card(id),
	creationdate TIMESTAMP DEFAULT NOW(),
	activedate   TIMESTAMP,
	expiredate   TIMESTAMP,
	status       INTEGER NOT NULL DEFAULT 1
);

-- Now, create special tables for each kind of subscriptions

CREATE TABLE subscription_feature_templ (
	feature TEXT NOT NULL
	) INHERITS (subscription_template);


/*-* Indexes! */

-- eof
